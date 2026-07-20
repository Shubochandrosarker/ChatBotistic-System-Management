<?php
/**
 * Agent discovery — HTTP Link headers (RFC 8288), the RFC 9727 API catalog,
 * an OpenAPI description of the one public API this domain exposes
 * (Licenseistic), and an honest auth.md.
 *
 * Deliberately NOT included here: OAuth/OIDC discovery metadata. Licenseistic
 * authenticates with license keys and admin-issued API key pairs, not OAuth —
 * publishing a fake authorization_endpoint would send agents into a dead end.
 * auth.md says so explicitly instead of staying silent.
 *
 * Routing for the new virtual endpoints (/.well-known/api-catalog,
 * /openapi.json, /auth.md) lives in inc/sitemap.php next to the sitemap and
 * llms.txt rewrite rules; this file renders their output.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * HTTP Link response headers — the machine-readable path to the API catalog
 * and docs, for agents that read headers rather than parsing <head> markup.
 */
function cb_agent_link_headers() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || get_query_var( 'cb_feed' ) ) {
		return;
	}

	$links = (array) apply_filters( 'cb_agent_link_headers', array(
		array(
			'href' => home_url( '/.well-known/api-catalog' ),
			'rel'  => 'api-catalog',
			'type' => 'application/linkset+json',
		),
		array(
			'href' => home_url( '/docs/' ),
			'rel'  => 'service-doc',
			'type' => 'text/html',
		),
	) );

	foreach ( $links as $link ) {
		if ( empty( $link['href'] ) || empty( $link['rel'] ) ) {
			continue;
		}
		$value = '<' . esc_url_raw( $link['href'] ) . '>; rel="' . esc_attr( $link['rel'] ) . '"';
		if ( ! empty( $link['type'] ) ) {
			$value .= '; type="' . esc_attr( $link['type'] ) . '"';
		}
		header( 'Link: ' . $value, false );
	}
}
add_action( 'send_headers', 'cb_agent_link_headers' );

/**
 * Advertise that the response varies by Accept — this theme serves markdown
 * to the same URL when Accept: text/markdown is sent (see inc/markdown.php).
 * Without this, a cache sitting in front of the site could serve the wrong
 * representation to the next visitor.
 */
function cb_agent_vary_accept() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	header( 'Vary: Accept', false );
}
add_action( 'send_headers', 'cb_agent_vary_accept' );

/**
 * /.well-known/api-catalog (RFC 9727) — application/linkset+json.
 *
 * Only catalogs APIs that actually exist and actually run on this domain.
 * Licenseistic is the one public REST API chatbotistic.com exposes; the
 * other plugins in this system are either customer-site installs or
 * admin-only internal endpoints, not something an external agent would call.
 */
function cb_render_api_catalog() {
	header( 'Content-Type: application/linkset+json; charset=UTF-8' );

	$entries = array();

	if ( defined( 'WPISTIC_LSI_VERSION' ) && defined( 'WPISTIC_LSI_REST_NAMESPACE' ) ) {
		$entries[] = array(
			'anchor'       => home_url( '/wp-json/' . WPISTIC_LSI_REST_NAMESPACE . '/' ),
			'service-desc' => array(
				array(
					'href'  => home_url( '/openapi.json' ),
					'type'  => 'application/json',
					'title' => 'Licenseistic API (OpenAPI 3.0)',
				),
			),
			'service-doc'  => array(
				array(
					'href'  => home_url( '/docs/' ),
					'type'  => 'text/html',
					'title' => get_bloginfo( 'name' ) . ' documentation',
				),
			),
		);
	}

	$entries = (array) apply_filters( 'cb_api_catalog_entries', $entries );

	echo wp_json_encode(
		array( 'linkset' => array_values( $entries ) ),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
	);
}

/**
 * /openapi.json — OpenAPI 3.0 description of the Licenseistic REST API.
 *
 * Documents the routes as they actually behave: the six client-facing
 * routes are public and rate-limited (the license_key itself is the
 * credential — see /auth.md), the /licenses management routes require an
 * admin session or an API key pair.
 */
function cb_render_openapi() {
	header( 'Content-Type: application/json; charset=UTF-8' );

	if ( ! defined( 'WPISTIC_LSI_VERSION' ) || ! defined( 'WPISTIC_LSI_REST_NAMESPACE' ) ) {
		echo wp_json_encode( array(
			'openapi' => '3.0.3',
			'info'    => array(
				'title'       => get_bloginfo( 'name' ) . ' API',
				'version'     => '0.0.0',
				'description' => 'No public API is active on this site right now.',
			),
			'paths'   => new stdClass(),
		) );
		return;
	}

	$license_params = array(
		array(
			'name'        => 'license_key',
			'in'          => 'query',
			'required'    => true,
			'schema'      => array( 'type' => 'string' ),
			'description' => 'The customer license key issued at purchase.',
		),
		array(
			'name'     => 'product_id',
			'in'       => 'query',
			'required' => false,
			'schema'   => array( 'type' => 'integer' ),
		),
	);
	$license_params_site = array_merge( $license_params, array(
		array(
			'name'        => 'site_url',
			'in'          => 'query',
			'required'    => true,
			'schema'      => array(
				'type'   => 'string',
				'format' => 'uri',
			),
			'description' => 'The site the license is being activated/deactivated/heartbeat-ed for.',
		),
		array(
			'name'     => 'instance_id',
			'in'       => 'query',
			'required' => false,
			'schema'   => array( 'type' => 'string' ),
		),
	) );

	$success_schema = array(
		'type'       => 'object',
		'properties' => array(
			'success' => array( 'type' => 'boolean' ),
			'message' => array( 'type' => 'string' ),
			'data'    => array( 'type' => 'object' ),
		),
	);
	$error_schema = array(
		'type'       => 'object',
		'properties' => array(
			'success' => array(
				'type' => 'boolean',
				'enum' => array( false ),
			),
			'message' => array( 'type' => 'string' ),
			'code'    => array( 'type' => 'string' ),
			'data'    => array( 'type' => 'object' ),
		),
	);
	$responses = array(
		'200' => array(
			'description' => 'Success.',
			'content'     => array( 'application/json' => array( 'schema' => $success_schema ) ),
		),
		'400' => array(
			'description' => 'Invalid request.',
			'content'     => array( 'application/json' => array( 'schema' => $error_schema ) ),
		),
		'404' => array(
			'description' => 'License not found or expired.',
			'content'     => array( 'application/json' => array( 'schema' => $error_schema ) ),
		),
		'409' => array(
			'description' => 'Activation limit reached, or the license is already active on this site.',
			'content'     => array( 'application/json' => array( 'schema' => $error_schema ) ),
		),
		'429' => array(
			'description' => 'Rate limited — too many requests from this client.',
			'content'     => array( 'application/json' => array( 'schema' => $error_schema ) ),
		),
	);

	$admin_security = array(
		array(
			'lsiPublicKey' => array(),
			'lsiSecretKey' => array(),
		),
		array( 'lsiBasicAuth' => array() ),
	);

	$paths = array(
		'/activate'   => array(
			'post' => array(
				'summary'     => 'Activate a license for a site.',
				'operationId' => 'activateLicense',
				'parameters'  => $license_params_site,
				'responses'   => $responses,
			),
		),
		'/deactivate' => array(
			'post' => array(
				'summary'     => 'Release a license activation for a site.',
				'operationId' => 'deactivateLicense',
				'parameters'  => $license_params_site,
				'responses'   => $responses,
			),
		),
		'/validate'   => array(
			'post' => array(
				'summary'     => 'Check whether a license key is currently valid, without activating it.',
				'operationId' => 'validateLicense',
				'parameters'  => $license_params,
				'responses'   => $responses,
			),
		),
		'/heartbeat'  => array(
			'post' => array(
				'summary'     => 'Periodic liveness ping from an already-activated site.',
				'operationId' => 'heartbeatLicense',
				'parameters'  => $license_params_site,
				'responses'   => $responses,
			),
		),
		'/license'    => array(
			'get' => array(
				'summary'     => "Read a license's current status.",
				'operationId' => 'getLicenseStatus',
				'parameters'  => $license_params,
				'responses'   => $responses,
			),
		),
		'/entitlements' => array(
			'get' => array(
				'summary'     => 'Read the plan entitlements (widget/agent/domain caps, white-label) a license grants.',
				'operationId' => 'getEntitlements',
				'parameters'  => $license_params,
				'responses'   => $responses,
			),
		),
		'/licenses'     => array(
			'get'  => array(
				'summary'     => 'List licenses.',
				'operationId' => 'listLicenses',
				'security'    => $admin_security,
				'responses'   => $responses,
			),
			'post' => array(
				'summary'     => 'Create a license.',
				'operationId' => 'createLicense',
				'security'    => $admin_security,
				'responses'   => $responses,
			),
		),
		'/licenses/{id}' => array(
			'parameters' => array(
				array(
					'name'     => 'id',
					'in'       => 'path',
					'required' => true,
					'schema'   => array( 'type' => 'integer' ),
				),
			),
			'get'    => array(
				'summary'     => 'Read a single license.',
				'operationId' => 'getLicense',
				'security'    => $admin_security,
				'responses'   => $responses,
			),
			'put'    => array(
				'summary'     => 'Update a license.',
				'operationId' => 'updateLicense',
				'security'    => $admin_security,
				'responses'   => $responses,
			),
			'delete' => array(
				'summary'     => 'Delete a license.',
				'operationId' => 'deleteLicense',
				'security'    => $admin_security,
				'responses'   => $responses,
			),
		),
	);

	$spec = array(
		'openapi' => '3.0.3',
		'info'    => array(
			'title'       => 'Licenseistic API',
			'version'     => WPISTIC_LSI_VERSION,
			'description' => 'License activation, validation and entitlement lookups for Chatbotistic-family '
				. 'products. /activate, /deactivate, /validate, /heartbeat, /license and /entitlements are '
				. 'public and rate-limited — the license_key itself is the credential, no separate token is '
				. 'issued. /licenses management routes require an API key pair. See ' . home_url( '/auth.md' ) . '.',
		),
		'servers'    => array(
			array( 'url' => home_url( '/wp-json/' . WPISTIC_LSI_REST_NAMESPACE ) ),
		),
		'paths'      => $paths,
		'components' => array(
			'securitySchemes' => array(
				'lsiPublicKey' => array(
					'type'        => 'apiKey',
					'in'          => 'header',
					'name'        => 'x-lsi-public-key',
					'description' => 'Paired with x-lsi-secret-key below — both headers are required together. '
						. 'Issued by a site admin in wp-admin (Licenseistic → API Keys), not self-service.',
				),
				'lsiSecretKey' => array(
					'type' => 'apiKey',
					'in'   => 'header',
					'name' => 'x-lsi-secret-key',
				),
				'lsiBasicAuth' => array(
					'type'        => 'http',
					'scheme'      => 'basic',
					'description' => 'Alternative to the two headers: HTTP Basic with the public key as username '
						. 'and the secret key as password.',
				),
			),
		),
	);

	$spec = (array) apply_filters( 'cb_openapi_spec', $spec );

	echo wp_json_encode( $spec, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
}

/**
 * /auth.md — how the real auth on this site works. Says outright that
 * OAuth/OIDC isn't implemented so agents stop probing for it.
 */
function cb_render_auth_md() {
	header( 'Content-Type: text/markdown; charset=UTF-8' );

	$L   = array();
	$L[] = '# Agent & API authentication — ' . get_bloginfo( 'name' );
	$L[] = '';
	$L[] = 'This site does not implement OAuth 2.0 or OpenID Connect and has no self-service ' .
		'client-registration endpoint. `/.well-known/oauth-authorization-server` and ' .
		'`/.well-known/openid-configuration` correctly 404 — that is not a misconfiguration.';
	$L[] = '';
	$L[] = 'Two real credential types exist.';
	$L[] = '';
	$L[] = '## 1. License keys — product / customer use';
	$L[] = '';
	$L[] = 'Chatbotistic products authenticate with a **license key**, issued automatically when a ' .
		'customer subscribes to a plan at ' . home_url( '/pricing/' ) . '. The key itself is the ' .
		'credential: pass it as the `license_key` parameter to the public, rate-limited routes in ' .
		home_url( '/openapi.json' ) . ' (`/activate`, `/deactivate`, `/validate`, `/heartbeat`, ' .
		'`/license`, `/entitlements`). There is no separate token exchange and no registration step.';
	$L[] = '';
	$L[] = '## 2. API key pairs — admin / management use';
	$L[] = '';
	$L[] = 'The license-management routes (`/licenses`, `/licenses/{id}`) require an API key pair: the ' .
		'`x-lsi-public-key` and `x-lsi-secret-key` request headers (or HTTP Basic, public key as ' .
		'username, secret key as password). These are **not self-service** — a site administrator ' .
		'generates them from wp-admin (Licenseistic → API Keys) and shares the secret out of band. An ' .
		'agent cannot register itself for one; ask the site owner.';
	$L[] = '';
	$L[] = '## Resources';
	$L[] = '';
	$L[] = '- API catalog: ' . home_url( '/.well-known/api-catalog' );
	$L[] = '- OpenAPI description: ' . home_url( '/openapi.json' );
	$L[] = '- Documentation: ' . home_url( '/docs/' );
	$L[] = '- Support: ' . home_url( '/support/' );

	echo implode( "\n", $L ) . "\n";
}
