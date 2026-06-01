<?php
/**
 * Plugin deactivation handler.
 *
 * @package Licenseistic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPistic_LSI_Deactivator
 */
class WPistic_LSI_Deactivator {

	/**
	 * Runs on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
		do_action( 'wpistic_lsi_deactivated' );
	}
}
