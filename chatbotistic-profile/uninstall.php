<?php
/**
 * Uninstall — only removes our own option, never the Memberistic data.
 *
 * @package Chatbotistic\Profile
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'cbp_version' );
delete_transient( 'cbp_admin_notice' );
