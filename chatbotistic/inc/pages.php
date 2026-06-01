<?php
/**
 * Auto-provision the site structure on theme activation.
 *
 * Creates every marketing, portal and legal page with the correct template
 * assigned, sets the static front page and blog page. Existing pages are
 * left untouched — only their template is corrected if missing.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/**
 * The full page map: slug => [title, template].
 *
 * @return array<string,array{title:string,template:string}>
 */
function cb_page_map() {
	return array(
		'home'                => array( 'title' => 'Home', 'template' => '' ),
		'features'            => array( 'title' => 'Features', 'template' => 'page-features.php' ),
		'pricing'             => array( 'title' => 'Pricing', 'template' => 'page-pricing.php' ),
		'use-cases'           => array( 'title' => 'Use Cases', 'template' => 'page-use-cases.php' ),
		'agencies'            => array( 'title' => 'Chatbots for Agencies', 'template' => 'page-usecase.php' ),
		'local-business'      => array( 'title' => 'Chatbots for Local Businesses', 'template' => 'page-usecase.php' ),
		'clinics-spas'        => array( 'title' => 'Chatbots for Clinics & Spas', 'template' => 'page-usecase.php' ),
		'real-estate'         => array( 'title' => 'Chatbots for Real Estate', 'template' => 'page-usecase.php' ),
		'ecommerce'           => array( 'title' => 'Chatbots for eCommerce', 'template' => 'page-usecase.php' ),
		'coaches'             => array( 'title' => 'Chatbots for Coaches & Consultants', 'template' => 'page-usecase.php' ),
		'coaches-consultants' => array( 'title' => 'Chatbots for Coaches & Consultants', 'template' => 'page-usecase.php' ),
		'wordpress-sites'     => array( 'title' => 'Chatbots for WordPress Sites', 'template' => 'page-usecase.php' ),
		'saas-founders'       => array( 'title' => 'Chatbots for SaaS Founders', 'template' => 'page-usecase.php' ),
		'travel-agencies'     => array( 'title' => 'Chatbots for Travel Agencies', 'template' => 'page-usecase.php' ),
		'whatsapp-automation' => array( 'title' => 'WhatsApp Automation', 'template' => 'page-whatsapp-automation.php' ),
		'ai-chatbot'          => array( 'title' => 'AI Chatbot', 'template' => 'page-ai-chatbot.php' ),
		'booking-forms'       => array( 'title' => 'Booking Forms', 'template' => 'page-booking-forms.php' ),
		'wordpress-plugin'    => array( 'title' => 'WordPress Plugin', 'template' => 'page-wordpress-plugin.php' ),
		'agency-white-label'  => array( 'title' => 'Agency & White Label', 'template' => 'page-agency-white-label.php' ),
		'docs'                => array( 'title' => 'Documentation', 'template' => 'page-docs.php' ),
		'tutorials'           => array( 'title' => 'Tutorials', 'template' => 'page-tutorials.php' ),
		'faqs'                => array( 'title' => 'FAQs', 'template' => 'page-faqs.php' ),
		'demo'                => array( 'title' => 'Book a Demo', 'template' => 'page-demo.php' ),
		'book-demo'           => array( 'title' => 'Book a Demo', 'template' => 'page-demo.php' ),
		'login'               => array( 'title' => 'Sign in', 'template' => 'page-login.php' ),
		'register'            => array( 'title' => 'Create account', 'template' => 'page-register.php' ),
		'forgot-password'     => array( 'title' => 'Reset your password', 'template' => 'page-forgot-password.php' ),
		'reset-password'      => array( 'title' => 'Choose a new password', 'template' => 'page-reset-password.php' ),
		'verify-email'        => array( 'title' => 'Verify your email', 'template' => 'page-verify-email.php' ),
		'checkout'            => array( 'title' => 'Checkout', 'template' => 'page-checkout.php' ),
		'payment-success'    => array( 'title' => 'Payment successful', 'template' => 'page-payment-success.php' ),
		'payment-failed'     => array( 'title' => 'Payment failed', 'template' => 'page-payment-failed.php' ),
		'cancel-plan'        => array( 'title' => 'Cancel plan', 'template' => 'page-cancel-plan.php' ),
		'upgrade'            => array( 'title' => 'Upgrade plan', 'template' => 'page-upgrade.php' ),
		'account-required'   => array( 'title' => 'Sign in to continue', 'template' => 'page-account-required.php' ),
		'affiliate'           => array( 'title' => 'Affiliate Program', 'template' => 'page-affiliate.php' ),
		'about'               => array( 'title' => 'About', 'template' => 'page-about.php' ),
		'contact'             => array( 'title' => 'Contact', 'template' => 'page-contact.php' ),
		'support'             => array( 'title' => 'Get Support', 'template' => 'page-support.php' ),
		'account'             => array( 'title' => 'Account', 'template' => 'page-account.php' ),
		'blog'                => array( 'title' => 'Blog', 'template' => '' ),
		'privacy-policy'      => array( 'title' => 'Privacy Policy', 'template' => 'page-legal.php' ),
		'terms'               => array( 'title' => 'Terms of Service', 'template' => 'page-legal.php' ),
		'refund-policy'       => array( 'title' => 'Refund Policy', 'template' => 'page-legal.php' ),
		'security'            => array( 'title' => 'Security', 'template' => 'page-legal.php' ),
	);
}

/**
 * Create missing pages and wire up the front + blog pages on theme switch.
 */
function cb_provision_pages() {
	$ids = array();

	foreach ( cb_page_map() as $slug => $info ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$ids[ $slug ] = (int) $existing->ID;
			if ( $info['template'] && ! get_post_meta( $existing->ID, '_wp_page_template', true ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', $info['template'] );
			}
			continue;
		}

		$page_id = wp_insert_post( array(
			'post_title'   => $info['title'],
			'post_name'    => $slug,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		) );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			$ids[ $slug ] = (int) $page_id;
			if ( $info['template'] ) {
				update_post_meta( $page_id, '_wp_page_template', $info['template'] );
			}
		}
	}

	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}
	if ( ! empty( $ids['blog'] ) ) {
		update_option( 'page_for_posts', $ids['blog'] );
	}
}
add_action( 'after_switch_theme', 'cb_provision_pages' );

/**
 * Auto re-provision after a theme update so any new entries added to
 * cb_page_map() in a newer release are created without the admin needing
 * to switch themes off and on. Compares the version we last provisioned
 * for against the current theme version on every admin page load (cheap)
 * and re-runs the idempotent provisioner if it has drifted.
 */
function cb_maybe_reprovision_on_update() {
	$current  = defined( 'CB_VERSION' ) ? CB_VERSION : '0';
	$last_run = (string) get_option( 'cb_pages_version', '' );
	if ( '' === $last_run || version_compare( $last_run, $current, '<' ) ) {
		cb_provision_pages();
		update_option( 'cb_pages_version', $current );
	}
}
add_action( 'admin_init', 'cb_maybe_reprovision_on_update', 5 );

/**
 * Route wp-login / register links to branded pages when they exist.
 */
function cb_login_url( $url ) {
	$login = get_page_by_path( 'login' ) ?: get_page_by_path( 'memberistic-login' );
	return $login ? get_permalink( $login ) : $url;
}
add_filter( 'login_url', 'cb_login_url', 20 );
