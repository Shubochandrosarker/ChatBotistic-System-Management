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
		'wordpress-sites'     => array( 'title' => 'Chatbots for WordPress Sites', 'template' => 'page-usecase.php' ),
		'saas-founders'       => array( 'title' => 'Chatbots for SaaS Founders', 'template' => 'page-usecase.php' ),
		'whatsapp-automation' => array( 'title' => 'WhatsApp Automation', 'template' => 'page-whatsapp-automation.php' ),
		'ai-chatbot'          => array( 'title' => 'AI Chatbot', 'template' => 'page-ai-chatbot.php' ),
		'booking-forms'       => array( 'title' => 'Booking Forms', 'template' => 'page-booking-forms.php' ),
		'wordpress-plugin'    => array( 'title' => 'WordPress Plugin', 'template' => 'page-wordpress-plugin.php' ),
		'agency-white-label'  => array( 'title' => 'Agency & White Label', 'template' => 'page-agency-white-label.php' ),
		'docs'                => array( 'title' => 'Documentation', 'template' => 'page-docs.php' ),
		'tutorials'           => array( 'title' => 'Tutorials', 'template' => 'page-tutorials.php' ),
		'faqs'                => array( 'title' => 'FAQs', 'template' => 'page-faqs.php' ),
		'demo'                => array( 'title' => 'Book a Demo', 'template' => 'page-demo.php' ),
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
 * Route wp-login / register links to branded pages when they exist.
 */
function cb_login_url( $url ) {
	$login = get_page_by_path( 'login' ) ?: get_page_by_path( 'memberistic-login' );
	return $login ? get_permalink( $login ) : $url;
}
add_filter( 'login_url', 'cb_login_url', 20 );
