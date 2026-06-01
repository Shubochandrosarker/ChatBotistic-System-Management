<?php
/**
 * Chatbotistic theme bootstrap.
 *
 * Standalone theme. No parent. One design system, one script.
 * Business logic lives in plugins (Memberistic, Licenseistic, SaaS Connector,
 * Bookingistic) — the theme only handles presentation and SEO.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

define( 'CB_DIR', get_template_directory() );
define( 'CB_URI', get_template_directory_uri() );
define( 'CB_VERSION', wp_get_theme()->get( 'Version' ) ?: '1.0.0' );

require_once CB_DIR . '/inc/setup.php';
require_once CB_DIR . '/inc/helpers.php';
require_once CB_DIR . '/inc/enqueue.php';
require_once CB_DIR . '/inc/landing-data.php';
require_once CB_DIR . '/inc/seo.php';
require_once CB_DIR . '/inc/sitemap.php';
require_once CB_DIR . '/inc/llms.php';
require_once CB_DIR . '/inc/forms.php';
require_once CB_DIR . '/inc/memberistic.php';
require_once CB_DIR . '/inc/login-branding.php';
require_once CB_DIR . '/inc/hardening.php';
require_once CB_DIR . '/inc/pages.php';
