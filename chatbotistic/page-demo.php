<?php
/**
 * Template Name: Demo Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_status = isset( $_GET['demo'] ) ? sanitize_key( wp_unslash( $_GET['demo'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<span class="cb-eyebrow"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'See your AI WhatsApp Agent in action', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Tell us about your business and we will walk you through a live flow tailored to how you win customers.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container" style="max-width:620px;">
			<div class="cb-card cb-glass cb-reveal">
				<?php if ( 'success' === $cb_status ) : ?>
					<div class="cb-note cb-note--ok" style="margin-bottom:16px;"><?php esc_html_e( 'Thanks — we have your request and will email you to schedule.', 'chatbotistic' ); ?></div>
				<?php elseif ( 'error' === $cb_status ) : ?>
					<div class="cb-note cb-note--err" style="margin-bottom:16px;"><?php esc_html_e( 'Please enter a valid email address.', 'chatbotistic' ); ?></div>
				<?php endif; ?>

				<form class="cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="cb_demo">
					<?php wp_nonce_field( 'cb_demo', 'cb_demo_nonce' ); ?>
					<div class="cb-field-row">
						<div class="cb-field">
							<label for="cb-d-name"><?php esc_html_e( 'Your name', 'chatbotistic' ); ?></label>
							<input id="cb-d-name" type="text" name="cb_name" required>
						</div>
						<div class="cb-field">
							<label for="cb-d-email"><?php esc_html_e( 'Work email', 'chatbotistic' ); ?></label>
							<input id="cb-d-email" type="email" name="cb_email" required>
						</div>
					</div>
					<div class="cb-field">
						<label for="cb-d-company"><?php esc_html_e( 'Company / website', 'chatbotistic' ); ?></label>
						<input id="cb-d-company" type="text" name="cb_company">
					</div>
					<div class="cb-field">
						<label for="cb-d-msg"><?php esc_html_e( 'What do you want to automate?', 'chatbotistic' ); ?></label>
						<textarea id="cb-d-msg" name="cb_message" rows="5"></textarea>
					</div>
					<div><button type="submit" class="cb-btn cb-btn--primary cb-btn--lg"><?php esc_html_e( 'Request my demo', 'chatbotistic' ); ?></button></div>
				</form>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
