<?php
/**
 * Template Name: Contact Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_status = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$cb_plan   = isset( $_GET['plan'] ) ? sanitize_key( wp_unslash( $_GET['plan'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$cb_is_ltd = ( 'lifetime' === $cb_plan || 'ltd' === $cb_plan );

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<span class="cb-eyebrow"><?php esc_html_e( 'Contact', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Talk to the team', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Questions about plans, white label, or the WhatsApp API? We usually reply within one business day.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-grid cb-grid--2" style="margin-top:0;align-items:start;">

				<div class="cb-card cb-glass cb-reveal">
					<?php if ( 'success' === $cb_status ) : ?>
						<div class="cb-note cb-note--ok" style="margin-bottom:16px;"><?php esc_html_e( 'Thanks — your message is on its way. We will reply by email.', 'chatbotistic' ); ?></div>
					<?php elseif ( 'error' === $cb_status ) : ?>
						<div class="cb-note cb-note--err" style="margin-bottom:16px;"><?php esc_html_e( 'Please check your name, a valid email, and a message.', 'chatbotistic' ); ?></div>
					<?php endif; ?>

					<?php if ( $cb_is_ltd ) : ?>
						<div class="cb-note cb-note--ok" style="margin-bottom:16px;"><?php esc_html_e( 'You\'re enquiring about the Lifetime plan. Tell us a little about your use case and we\'ll send LTD pricing.', 'chatbotistic' ); ?></div>
					<?php endif; ?>

					<form class="cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="cb_contact">
						<?php if ( $cb_plan ) : ?>
							<input type="hidden" name="cb_plan" value="<?php echo esc_attr( $cb_plan ); ?>">
						<?php endif; ?>
						<?php wp_nonce_field( 'cb_contact', 'cb_contact_nonce' ); ?>
						<div class="cb-field-row">
							<div class="cb-field">
								<label for="cb-c-name"><?php esc_html_e( 'Your name', 'chatbotistic' ); ?></label>
								<input id="cb-c-name" type="text" name="cb_name" required>
							</div>
							<div class="cb-field">
								<label for="cb-c-email"><?php esc_html_e( 'Email', 'chatbotistic' ); ?></label>
								<input id="cb-c-email" type="email" name="cb_email" required>
							</div>
						</div>
						<div class="cb-field">
							<label for="cb-c-topic"><?php esc_html_e( 'Topic', 'chatbotistic' ); ?></label>
							<select id="cb-c-topic" name="cb_topic">
								<option value="sales" <?php selected( $cb_is_ltd ); ?>><?php esc_html_e( 'Sales & pricing', 'chatbotistic' ); ?></option>
								<option value="support"><?php esc_html_e( 'Support', 'chatbotistic' ); ?></option>
								<option value="white-label"><?php esc_html_e( 'White label / agency', 'chatbotistic' ); ?></option>
								<option value="general"><?php esc_html_e( 'General', 'chatbotistic' ); ?></option>
							</select>
						</div>
						<div class="cb-field">
							<label for="cb-c-msg"><?php esc_html_e( 'Message', 'chatbotistic' ); ?></label>
							<textarea id="cb-c-msg" name="cb_message" rows="6" required></textarea>
						</div>
						<div><button type="submit" class="cb-btn cb-btn--primary cb-btn--lg"><?php esc_html_e( 'Send message', 'chatbotistic' ); ?></button></div>
					</form>
				</div>

				<div class="cb-stack cb-reveal">
					<div class="cb-card cb-glass">
						<div class="cb-feature__ico"><?php cb_icon( 'mail', 20 ); ?></div>
						<h3 class="cb-h3"><?php esc_html_e( 'Email us', 'chatbotistic' ); ?></h3>
						<p class="cb-soft" style="margin-top:6px;"><a href="mailto:hello@chatbotistic.com" style="color:var(--cb-indigo-2);">hello@chatbotistic.com</a></p>
					</div>
					<div class="cb-card cb-glass">
						<div class="cb-feature__ico"><?php cb_icon( 'play', 20 ); ?></div>
						<h3 class="cb-h3"><?php esc_html_e( 'Book a live demo', 'chatbotistic' ); ?></h3>
						<p class="cb-soft" style="margin-top:6px;"><?php esc_html_e( 'See the AI WhatsApp Agent run on a real flow.', 'chatbotistic' ); ?></p>
						<div style="margin-top:14px;"><?php cb_button( __( 'Schedule a demo', 'chatbotistic' ), home_url( '/demo/' ), 'ghost', array( 'size' => 'sm' ) ); ?></div>
					</div>
					<div class="cb-card cb-glass">
						<div class="cb-feature__ico"><?php cb_icon( 'page', 20 ); ?></div>
						<h3 class="cb-h3"><?php esc_html_e( 'Browse the docs', 'chatbotistic' ); ?></h3>
						<p class="cb-soft" style="margin-top:6px;"><?php esc_html_e( 'Setup guides and answers to common questions.', 'chatbotistic' ); ?></p>
						<div style="margin-top:14px;"><?php cb_button( __( 'Open docs', 'chatbotistic' ), home_url( '/docs/' ), 'ghost', array( 'size' => 'sm' ) ); ?></div>
					</div>
				</div>

			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
