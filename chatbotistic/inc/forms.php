<?php
/**
 * Front-end form handlers — contact, demo, newsletter, profile, support.
 *
 * All routed through admin-post.php with nonce + capability checks.
 * Membership, billing and licensing are NOT handled here — those belong to
 * Memberistic / Licenseistic. The theme only handles its own marketing forms.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * Push a submission into WPistic Contact Form when that plugin is active.
 *
 * @param string $form_name Form label.
 * @param array  $payload   Normalized fields.
 */
function cb_capture_wpistic_contact_form( $form_name, array $payload ) {
	if ( ! class_exists( 'WPISTIC_CF_Database' ) || ! method_exists( 'WPISTIC_CF_Database', 'insert_submission' ) ) {
		return;
	}

	$fields = $payload;
	unset( $fields['name'], $fields['email'], $fields['phone'], $fields['subject'], $fields['message'] );

	WPISTIC_CF_Database::insert_submission(
		array(
			'form_name'    => $form_name,
			'sender_name'  => (string) ( $payload['name'] ?? '' ),
			'sender_email' => (string) ( $payload['email'] ?? '' ),
			'sender_phone' => (string) ( $payload['phone'] ?? '' ),
			'subject'      => (string) ( $payload['subject'] ?? $form_name ),
			'message'      => (string) ( $payload['message'] ?? '' ),
			'fields'       => $fields,
			'source_url'   => (string) wp_get_referer(),
			// Store an anonymised IP (last octet zeroed) for safe abuse tracking
			// without retaining the visitor's full address.
			'ip_address'   => isset( $_SERVER['REMOTE_ADDR'] )
				? wp_privacy_anonymize_ip( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) )
				: '',
		)
	);
}

/**
 * Safe redirect target — the referring page, or a fallback.
 *
 * @param string $fallback Fallback path.
 * @return string
 */
function cb_form_redirect( $fallback = '/' ) {
	$ref = wp_get_referer();
	return $ref ? $ref : home_url( $fallback );
}

/**
 * Contact form.
 */
function cb_handle_contact() {
	$back = cb_form_redirect( '/contact/' );

	if ( ! isset( $_POST['cb_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cb_contact_nonce'] ) ), 'cb_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $back ) );
		exit;
	}

	$name    = isset( $_POST['cb_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_name'] ) ) : '';
	$email   = isset( $_POST['cb_email'] ) ? sanitize_email( wp_unslash( $_POST['cb_email'] ) ) : '';
	$topic   = isset( $_POST['cb_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_topic'] ) ) : 'general';
	$message = isset( $_POST['cb_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cb_message'] ) ) : '';
	$plan    = isset( $_POST['cb_plan'] ) ? sanitize_key( wp_unslash( $_POST['cb_plan'] ) ) : '';

	if ( ! $name || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $back ) );
		exit;
	}

	// A plan-specific inquiry (Lifetime / LTD) routes to its own form bucket so
	// it lands as a sales lead rather than a generic contact message.
	$is_ltd    = ( 'lifetime' === $plan || 'ltd' === $plan );
	$form_name = $is_ltd ? 'Chatbotistic Lifetime Inquiry' : 'Chatbotistic Contact';
	$subject   = $is_ltd ? 'Lifetime / LTD pricing inquiry' : 'Contact: ' . $topic;

	$body = sprintf( "Name: %s\nEmail: %s\nTopic: %s\n", $name, $email, $topic );
	if ( $plan ) {
		$body .= 'Plan: ' . $plan . "\n";
	}
	$body .= "\n" . $message;
	wp_mail(
		get_option( 'admin_email' ),
		$is_ltd
			? sprintf( '[Chatbotistic] Lifetime pricing inquiry from %s', $name )
			: sprintf( '[Chatbotistic] %s enquiry from %s', $topic, $name ),
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);
	cb_capture_wpistic_contact_form(
		$form_name,
		array(
			'name'          => $name,
			'email'         => $email,
			'subject'       => $subject,
			'message'       => $message,
			'topic'         => $topic,
			'selected_plan' => $plan,
		)
	);

	if ( $is_ltd ) {
		// Acknowledge the lead so they know the inquiry landed and what happens next.
		$ack_subject = __( 'We received your Chatbotistic Lifetime inquiry', 'chatbotistic' );
		$ack_body    = sprintf( __( 'Hi %s,', 'chatbotistic' ), $name ) . "\n\n"
			. __( 'Thanks for your interest in the Chatbotistic Lifetime plan. Our team will reply within one business day with LTD pricing and next steps.', 'chatbotistic' ) . "\n\n"
			. __( 'In the meantime you can explore the plans here: ', 'chatbotistic' ) . cb_plans_url() . "\n\n"
			. __( '— The Chatbotistic team', 'chatbotistic' );
		wp_mail( $email, $ack_subject, $ack_body );

		do_action( 'cb_ltd_inquiry_submitted', compact( 'name', 'email', 'plan', 'message' ) );
	}
	do_action( 'cb_contact_submitted', compact( 'name', 'email', 'topic', 'message', 'plan' ) );

	wp_safe_redirect( add_query_arg( 'contact', 'success', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_cb_contact', 'cb_handle_contact' );
add_action( 'admin_post_cb_contact', 'cb_handle_contact' );

/**
 * Demo / sales request form.
 */
function cb_handle_demo() {
	$back = cb_form_redirect( '/demo/' );

	if ( ! isset( $_POST['cb_demo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cb_demo_nonce'] ) ), 'cb_demo' ) ) {
		wp_safe_redirect( add_query_arg( 'demo', 'error', $back ) );
		exit;
	}

	$name    = isset( $_POST['cb_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_name'] ) ) : '';
	$email   = isset( $_POST['cb_email'] ) ? sanitize_email( wp_unslash( $_POST['cb_email'] ) ) : '';
	$company = isset( $_POST['cb_company'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_company'] ) ) : '';
	$message = isset( $_POST['cb_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cb_message'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'demo', 'error', $back ) );
		exit;
	}

	wp_mail(
		get_option( 'admin_email' ),
		'[Chatbotistic] Demo request — ' . $name,
		sprintf( "Name: %s\nEmail: %s\nCompany: %s\n\n%s", $name, $email, $company, $message ),
		array( 'Reply-To: ' . $email )
	);
	cb_capture_wpistic_contact_form(
		'Chatbotistic Demo Request',
		array(
			'name'    => $name,
			'email'   => $email,
			'subject' => 'Demo request',
			'message' => $message,
			'company' => $company,
		)
	);
	do_action( 'cb_demo_requested', compact( 'name', 'email', 'company', 'message' ) );

	wp_safe_redirect( add_query_arg( 'demo', 'success', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_cb_demo', 'cb_handle_demo' );
add_action( 'admin_post_cb_demo', 'cb_handle_demo' );

/**
 * Demo qualification request (V4 23-field form, posted from
 * page-demo.php). Distinct from the legacy 4-field cb_demo handler
 * above so existing integrations don't break.
 *
 * Fields are captured into WPistic Contact Form (when present) and
 * emailed to the admin for manual review per the docx 14-day-demo
 * approval policy. Honeypot + nonce protect against bots.
 */
function cb_handle_demo_request() {
	$back = home_url( '/book-demo/' );

	// Nonce
	if ( ! isset( $_POST['cb_demo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cb_demo_nonce'] ) ), 'cb_demo_request' ) ) {
		wp_safe_redirect( add_query_arg( 'demo', 'error', $back ) );
		exit;
	}
	// Honeypot — bots fill it, humans ignore it.
	if ( ! empty( $_POST['cb_company_alt'] ) ) {
		wp_safe_redirect( add_query_arg( 'demo', 'submitted', $back ) );
		exit;
	}
	// Consent must be present.
	if ( empty( $_POST['consent'] ) ) {
		wp_safe_redirect( add_query_arg( 'demo', 'error', $back ) );
		exit;
	}

	$fields = array(
		'full_name'      => 'sanitize_text_field',
		'work_email'     => 'sanitize_email',
		'phone'          => 'sanitize_text_field',
		'company'        => 'sanitize_text_field',
		'website'        => 'esc_url_raw',
		'market'         => 'sanitize_text_field',
		'biz_type'       => 'sanitize_text_field',
		'visitors'       => 'sanitize_text_field',
		'lead_source'    => 'sanitize_text_field',
		'wa_usage'       => 'sanitize_text_field',
		'wp'             => 'sanitize_text_field',
		'woo'            => 'sanitize_text_field',
		'wl'             => 'sanitize_text_field',
		'current_tools'  => 'sanitize_text_field',
		'need'           => 'sanitize_text_field',
		'domains'        => 'sanitize_text_field',
		'team_size'      => 'sanitize_text_field',
		'problem'        => 'sanitize_textarea_field',
		'expected'       => 'sanitize_textarea_field',
		'preferred_time' => 'sanitize_text_field',
		'budget'         => 'sanitize_text_field',
		'notes'          => 'sanitize_textarea_field',
	);
	$data = array();
	foreach ( $fields as $key => $sanitizer ) {
		$raw          = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$data[ $key ] = call_user_func( $sanitizer, $raw );
	}

	if ( ! is_email( $data['work_email'] ) || '' === $data['full_name'] || '' === $data['company'] ) {
		wp_safe_redirect( add_query_arg( 'demo', 'error', $back ) );
		exit;
	}

	$body  = "New Chatbotistic demo request — manual review required.\n\n";
	$body .= "Reviewed against the 14-day demo policy.\n\n";
	foreach ( $data as $k => $v ) {
		$body .= str_pad( $k, 16, ' ', STR_PAD_RIGHT ) . ': ' . $v . "\n";
	}

	wp_mail(
		get_option( 'admin_email' ),
		'[Chatbotistic] Demo request — ' . $data['full_name'] . ' (' . $data['company'] . ')',
		$body,
		array( 'Reply-To: ' . $data['work_email'] )
	);

	cb_capture_wpistic_contact_form(
		'Chatbotistic Demo Request (qualified)',
		array_merge(
			$data,
			array(
				'name'    => $data['full_name'],
				'email'   => $data['work_email'],
				'subject' => 'Qualified demo request',
				'message' => trim( $data['problem'] . "\n\nExpected:\n" . $data['expected'] . "\n\nNotes:\n" . $data['notes'] ),
			)
		)
	);

	do_action( 'cb_demo_request_received', $data );

	wp_safe_redirect( add_query_arg( 'demo', 'submitted', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_cb_demo_request', 'cb_handle_demo_request' );
add_action( 'admin_post_cb_demo_request', 'cb_handle_demo_request' );

/**
 * Newsletter opt-in.
 *
 * Subscribers land in WPISTIC_CF_Newsletter's dedicated table so the admin
 * can see, export, and unsubscribe them from one dashboard. Falls back to a
 * fire-and-forget action for sites where the contact-form plugin isn't
 * active.
 */
function cb_handle_newsletter() {
	$back = cb_form_redirect();

	if ( ! isset( $_POST['cb_news_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cb_news_nonce'] ) ), 'cb_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'news', 'error', $back ) );
		exit;
	}

	$email  = isset( $_POST['cb_email'] ) ? sanitize_email( wp_unslash( $_POST['cb_email'] ) ) : '';
	$source = isset( $_POST['cb_source'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_source'] ) ) : 'footer';
	$status = 'error';

	if ( is_email( $email ) ) {
		if ( class_exists( 'WPISTIC_CF_Newsletter' ) && method_exists( 'WPISTIC_CF_Newsletter', 'process' ) ) {
			$res = WPISTIC_CF_Newsletter::process( $email, $source );
			$ok  = isset( $res['status'] ) && in_array( $res['status'], array( 'subscribed', 'duplicate', 'reactivated' ), true );
			$status = $ok ? 'success' : ( $res['status'] ?? 'error' );
		} else {
			$status = 'success';
		}
		do_action( 'cb_newsletter_subscribe', $email );
	}
	wp_safe_redirect( add_query_arg( 'news', $status, $back ) );
	exit;
}
add_action( 'admin_post_nopriv_cb_newsletter', 'cb_handle_newsletter' );
add_action( 'admin_post_cb_newsletter', 'cb_handle_newsletter' );

/**
 * Member profile update (name, email, password) from the portal.
 */
function cb_handle_profile() {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/login/' ) );
		exit;
	}
	check_admin_referer( 'cb_profile', 'cb_profile_nonce' );

	$user_id = get_current_user_id();
	$data    = array( 'ID' => $user_id );

	if ( isset( $_POST['first_name'] ) ) {
		$data['first_name'] = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
	}
	if ( isset( $_POST['last_name'] ) ) {
		$data['last_name'] = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );
	}
	$email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
	if ( $email && is_email( $email ) ) {
		$data['user_email'] = $email;
	}
	$pass    = isset( $_POST['user_pass'] ) ? (string) $_POST['user_pass'] : '';
	$confirm = isset( $_POST['user_pass_confirm'] ) ? (string) $_POST['user_pass_confirm'] : '';
	$flag    = 'updated';
	if ( '' !== $pass ) {
		if ( $pass === $confirm && strlen( $pass ) >= 8 ) {
			$data['user_pass'] = $pass;
		} else {
			$flag = 'passfail';
		}
	}

	if ( count( $data ) > 1 ) {
		wp_update_user( $data );
	}
	wp_safe_redirect( add_query_arg( array( 'view' => 'profile', 'profile' => $flag ), home_url( '/account/' ) ) );
	exit;
}
add_action( 'admin_post_cb_profile', 'cb_handle_profile' );

/**
 * Member support ticket from the portal.
 */
function cb_handle_support() {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/login/' ) );
		exit;
	}
	check_admin_referer( 'cb_support', 'cb_support_nonce' );

	$user    = wp_get_current_user();
	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	if ( $subject && $message ) {
		wp_mail(
			get_option( 'admin_email' ),
			'[Chatbotistic Support] ' . $subject,
			sprintf( "From: %s <%s>\nUser ID: %d\n\n%s", $user->display_name, $user->user_email, $user->ID, $message ),
			array( 'Reply-To: ' . $user->user_email )
		);
		cb_capture_wpistic_contact_form(
			'Member Portal Support Ticket',
			array(
				'name'    => $user->display_name,
				'email'   => $user->user_email,
				'subject' => $subject,
				'message' => $message,
				'user_id' => $user->ID,
				'source'  => 'member_portal',
			)
		);
		do_action( 'cb_support_ticket', $user->ID, $subject, $message );
	}
	wp_safe_redirect( add_query_arg( array( 'view' => 'support', 'support' => 'sent' ), home_url( '/account/' ) ) );
	exit;
}
add_action( 'admin_post_cb_support', 'cb_handle_support' );

/**
 * Structured support request from the public Support page.
 *
 * Captures product area, request type and priority so tickets arrive
 * pre-triaged rather than as a generic "contact us" blob.
 */
function cb_handle_support_request() {
	$back = cb_form_redirect( '/support/' );

	if ( ! isset( $_POST['cb_support_req_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cb_support_req_nonce'] ) ), 'cb_support_request' ) ) {
		wp_safe_redirect( add_query_arg( 'support', 'error', $back ) );
		exit;
	}

	$fields = array(
		'name'     => isset( $_POST['cb_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_name'] ) ) : '',
		'email'    => isset( $_POST['cb_email'] ) ? sanitize_email( wp_unslash( $_POST['cb_email'] ) ) : '',
		'area'     => isset( $_POST['cb_area'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_area'] ) ) : '',
		'type'     => isset( $_POST['cb_type'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_type'] ) ) : '',
		'priority' => isset( $_POST['cb_priority'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_priority'] ) ) : 'normal',
		'subject'  => isset( $_POST['cb_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['cb_subject'] ) ) : '',
		'url'      => isset( $_POST['cb_url'] ) ? esc_url_raw( wp_unslash( $_POST['cb_url'] ) ) : '',
		'message'  => isset( $_POST['cb_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cb_message'] ) ) : '',
		'tried'    => isset( $_POST['cb_tried'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cb_tried'] ) ) : '',
	);

	if ( ! $fields['name'] || ! is_email( $fields['email'] ) || ! $fields['subject'] || ! $fields['message'] ) {
		wp_safe_redirect( add_query_arg( 'support', 'error', $back ) );
		exit;
	}

	$body  = sprintf( "Name: %s\nEmail: %s\n", $fields['name'], $fields['email'] );
	$body .= sprintf( "Product area: %s\nRequest type: %s\nPriority: %s\n", $fields['area'], $fields['type'], $fields['priority'] );
	if ( $fields['url'] ) {
		$body .= 'URL: ' . $fields['url'] . "\n";
	}
	$body .= "\n--- Message ---\n" . $fields['message'] . "\n";
	if ( $fields['tried'] ) {
		$body .= "\n--- Already tried ---\n" . $fields['tried'] . "\n";
	}

	wp_mail(
		get_option( 'admin_email' ),
		sprintf( '[Chatbotistic Support · %s] %s', ucfirst( $fields['priority'] ), $fields['subject'] ),
		$body,
		array( 'Reply-To: ' . $fields['name'] . ' <' . $fields['email'] . '>' )
	);
	cb_capture_wpistic_contact_form(
		'Chatbotistic Support Request',
		array(
			'name'     => $fields['name'],
			'email'    => $fields['email'],
			'subject'  => $fields['subject'],
			'message'  => $fields['message'],
			'priority' => $fields['priority'],
			'area'     => $fields['area'],
			'type'     => $fields['type'],
			'url'      => $fields['url'],
			'tried'    => $fields['tried'],
		)
	);
	do_action( 'cb_support_request_submitted', $fields );

	wp_safe_redirect( add_query_arg( 'support', 'success', $back ) );
	exit;
}
add_action( 'admin_post_nopriv_cb_support_request', 'cb_handle_support_request' );
add_action( 'admin_post_cb_support_request', 'cb_handle_support_request' );

/**
 * Send members to the branded portal after login.
 *
 * @param string           $redirect Default redirect.
 * @param string           $request  Requested redirect.
 * @param WP_User|WP_Error $user     User or error.
 * @return string
 */
function cb_login_redirect( $redirect, $request, $user ) {
	if ( $user instanceof WP_User && ! is_wp_error( $user ) ) {
		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			return $redirect ?: admin_url();
		}
		return home_url( '/account/' );
	}
	return $redirect;
}
add_filter( 'login_redirect', 'cb_login_redirect', 10, 3 );

/**
 * Send failed logins back to the branded /login/ page with an error flag
 * instead of leaving the visitor on the stock wp-login.php form. The admin
 * back-door (cb_admin_login / login-w-hub) keeps the default behaviour so
 * administrators still see WordPress's own login screen.
 *
 * @param string $username Submitted username (unused).
 */
function cb_login_failed( $username ) {
	// Only intervene for an actual browser login on wp-login.php. REST,
	// XML-RPC and Application Password auth failures also fire this hook and
	// must be left alone — redirecting them would break API authentication.
	if ( 'wp-login.php' !== ( $GLOBALS['pagenow'] ?? '' ) || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
		return;
	}

	$request_uri   = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	$is_admin_gate = ( isset( $_REQUEST['cb_admin_login'] ) && '1' === (string) $_REQUEST['cb_admin_login'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		|| false !== strpos( $request_uri, '/login-w-hub' );
	if ( $is_admin_gate ) {
		return;
	}

	$args = array( 'login' => 'failed' );
	if ( ! empty( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$args['redirect_to'] = rawurlencode( esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) );
	}
	wp_safe_redirect( add_query_arg( $args, home_url( '/login/' ) ) );
	exit;
}
add_action( 'wp_login_failed', 'cb_login_failed', 20 );

/**
 * Guard against an empty-submission edge case: if the login form is POSTed
 * with no username or password, wp-login.php would normally re-render itself.
 * Route that back to the branded page too so the experience stays consistent.
 */
function cb_login_empty_guard() {
	if ( 'wp-login.php' !== ( $GLOBALS['pagenow'] ?? '' ) ) {
		return;
	}
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		return;
	}
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( 'login' !== $action ) {
		return;
	}
	$has_user = ! empty( $_POST['log'] );
	$has_pass = ! empty( $_POST['pwd'] );
	if ( $has_user && $has_pass ) {
		return;
	}
	$is_admin_gate = ( isset( $_REQUEST['cb_admin_login'] ) && '1' === (string) $_REQUEST['cb_admin_login'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		|| false !== strpos( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), '/login-w-hub' );
	if ( $is_admin_gate ) {
		return;
	}
	wp_safe_redirect( add_query_arg( 'login', 'failed', home_url( '/login/' ) ) );
	exit;
}
add_action( 'login_init', 'cb_login_empty_guard' );
