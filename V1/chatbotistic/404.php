<?php
/**
 * 404 — page not found.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="cb-section" style="min-height:54vh;display:grid;place-items:center;">
	<div class="cb-container cb-center">
		<span class="cb-eyebrow"><?php esc_html_e( 'Error 404', 'chatbotistic' ); ?></span>
		<h1 class="cb-display" style="margin:18px 0;"><span class="cb-grad"><?php esc_html_e( 'Page not found', 'chatbotistic' ); ?></span></h1>
		<p class="cb-lead cb-narrow" style="margin:0 auto 26px;"><?php esc_html_e( 'That page has moved or never existed. Let’s get you back on track.', 'chatbotistic' ); ?></p>
		<div class="cb-cta__actions">
			<?php
			cb_button( __( 'Back to home', 'chatbotistic' ), home_url( '/' ), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
			cb_button( __( 'View pricing', 'chatbotistic' ), home_url( '/pricing/' ), 'ghost', array( 'size' => 'lg' ) );
			?>
		</div>
	</div>
</section>
<?php
get_footer();
