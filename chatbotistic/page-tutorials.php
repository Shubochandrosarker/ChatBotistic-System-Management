<?php
/**
 * Template Name: Tutorials Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_tracks = array(
	array( 'play',  __( 'Quick start', 'chatbotistic' ),       __( 'Build and embed your first widget in under five minutes.', 'chatbotistic' ) ),
	array( 'ai',    __( 'Train your agent', 'chatbotistic' ),  __( 'Feed content, set tone, and tune your AI WhatsApp Agent.', 'chatbotistic' ) ),
	array( 'cal',   __( 'Booking flows', 'chatbotistic' ),     __( 'Create appointment flows with calendar sync.', 'chatbotistic' ) ),
	array( 'chart', __( 'Read your analytics', 'chatbotistic' ),__( 'Understand conversation funnels and conversion data.', 'chatbotistic' ) ),
	array( 'tag',   __( 'White-label setup', 'chatbotistic' ), __( 'Brand the dashboard and onboard your first client.', 'chatbotistic' ) ),
	array( 'plug',  __( 'Integrations', 'chatbotistic' ),      __( 'Wire Chatbotistic into your CRM and tools.', 'chatbotistic' ) ),
);

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<span class="cb-eyebrow"><?php esc_html_e( 'Tutorials', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Learn Chatbotistic, step by step', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Short, practical walkthroughs that get you from signup to revenue.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-grid cb-grid--3">
				<?php foreach ( $cb_tracks as $cb_t ) : ?>
					<div class="cb-feature cb-reveal">
						<div class="cb-feature__ico"><?php cb_icon( $cb_t[0], 20 ); ?></div>
						<h3><?php echo esc_html( $cb_t[1] ); ?></h3>
						<p><?php echo esc_html( $cb_t[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
