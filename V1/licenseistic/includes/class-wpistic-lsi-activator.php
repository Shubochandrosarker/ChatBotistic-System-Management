<?php
/**
 * Plugin activation handler.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Activator
 */
class WPistic_LSI_Activator {

	/**
	 * Runs on plugin activation.
	 *
	 * @return void
	 */
	public static function activate() {
		WPistic_LSI_Install::install();
		flush_rewrite_rules();
		do_action( 'wpistic_lsi_activated' );
	}
}
