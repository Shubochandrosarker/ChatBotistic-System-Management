<?php
/**
 * Minimal <head> opener for auth pages — emits the same wp_head() the
 * site header uses (so the cb-v4 / cb-v4-portal / cb-bridge stylesheets
 * load) without the marketing site header nav. The MiniTop is rendered
 * by auth-layout.php / auth-result.php.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#05070e">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'cb-auth-body' ); ?>>
<?php wp_body_open(); ?>
<div class="cb-site">
