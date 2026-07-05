<?php
/**
 * Template Name: Contact (V4)
 *
 * V4 port of pages/about-contact-docs.jsx → Contact. Form posts to
 * admin-post.php?action=cb_contact (existing handler in inc/forms.php).
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_status = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<main class="page-fade">

	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Contact', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Contact the Chatbotistic team.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Have a question about plans, support, partnerships, or white-label deployment? Send the details and we’ll reply with the right next step.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<div class="container">
		<div class="contact-grid">
			<div class="glass glass-edge" style="padding:32px;border-radius:20px;">
				<?php if ( 'success' === $cb_status ) : ?>
					<div style="text-align:center;padding:40px 20px;">
						<div class="result-ico ok" style="margin:0 auto 18px;">✓</div>
						<h3 class="h-3"><?php esc_html_e( 'Message received', 'chatbotistic' ); ?></h3>
						<p style="color:var(--text-soft);margin:8px 0 18px;"><?php esc_html_e( 'We’ll get back within an hour during business hours.', 'chatbotistic' ); ?></p>
						<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Send another', 'chatbotistic' ); ?></a>
					</div>
				<?php else : ?>
					<form class="contact-form cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
						<input type="hidden" name="action" value="cb_contact">
						<?php wp_nonce_field( 'cb_contact', 'cb_contact_nonce' ); ?>
						<input type="text" name="cb_company_alt" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">

						<div class="field-row">
							<div class="field"><label><?php esc_html_e( 'First name', 'chatbotistic' ); ?></label><input name="cb_first" placeholder="Sarah" required></div>
							<div class="field"><label><?php esc_html_e( 'Last name', 'chatbotistic' ); ?></label><input name="cb_last" placeholder="Kim"></div>
						</div>
						<div class="field"><label><?php esc_html_e( 'Work email', 'chatbotistic' ); ?></label><input type="email" name="cb_email" placeholder="you@company.com" required></div>
						<div class="field"><label><?php esc_html_e( 'Phone / WhatsApp', 'chatbotistic' ); ?></label><input name="cb_phone" placeholder="+1 555 000 0000"></div>
						<div class="field"><label><?php esc_html_e( 'Company', 'chatbotistic' ); ?></label><input name="cb_company" placeholder="Acme Inc."></div>
						<div class="field"><label><?php esc_html_e( 'Website URL', 'chatbotistic' ); ?></label><input type="url" name="cb_website" placeholder="https://acme.com"></div>
						<div class="field"><label><?php esc_html_e( 'Reason for contact', 'chatbotistic' ); ?></label>
							<select name="cb_reason">
								<option><?php esc_html_e( 'Sales question', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Support request', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Partnership', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'White-label / Agency', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Billing', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Technical issue', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Other', 'chatbotistic' ); ?></option>
							</select>
						</div>
						<div class="field"><label><?php esc_html_e( 'Tell us more', 'chatbotistic' ); ?></label><textarea name="cb_message" placeholder="<?php esc_attr_e( 'What are you trying to automate?', 'chatbotistic' ); ?>" required></textarea></div>
						<div class="field"><label><?php esc_html_e( 'Preferred contact method', 'chatbotistic' ); ?></label>
							<select name="cb_contact_method">
								<option><?php esc_html_e( 'Email', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Phone', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'WhatsApp', 'chatbotistic' ); ?></option>
							</select>
						</div>
						<button class="btn btn-primary" type="submit" style="justify-content:center;margin-top:8px;"><?php esc_html_e( 'Send message', 'chatbotistic' ); ?></button>
						<p style="font-size:12px;color:var(--text-dim);margin:8px 0 0;"><?php esc_html_e( 'By submitting you agree to our privacy policy. We never share your email.', 'chatbotistic' ); ?></p>
					</form>
				<?php endif; ?>
			</div>

			<div class="contact-side">
				<div class="contact-card">
					<h4><?php esc_html_e( 'Sales inquiry', 'chatbotistic' ); ?></h4>
					<p><?php esc_html_e( 'Pricing, demos, custom plans for agencies and enterprise.', 'chatbotistic' ); ?></p>
					<a class="accent" href="mailto:hello@chatbotistic.com" style="display:block;margin-top:8px;">hello@chatbotistic.com</a>
				</div>
				<div class="contact-card">
					<h4><?php esc_html_e( 'Support', 'chatbotistic' ); ?></h4>
					<p><?php esc_html_e( 'Existing customers: help docs, integrations, billing.', 'chatbotistic' ); ?></p>
					<a class="accent" href="<?php echo esc_url( home_url( '/support/' ) ); ?>" style="display:block;margin-top:8px;"><?php esc_html_e( 'Open the support center', 'chatbotistic' ); ?></a>
				</div>
				<div class="contact-card">
					<h4><?php esc_html_e( 'Ecosystem', 'chatbotistic' ); ?></h4>
					<p><?php esc_html_e( 'Chatbotistic is part of the WordPressistic AI Business Automation Ecosystem.', 'chatbotistic' ); ?></p>
					<a class="accent" href="https://www.wordpressistic.com" target="_blank" rel="noopener" style="display:block;margin-top:8px;">www.wordpressistic.com</a>
				</div>
				<div class="contact-card" style="background:linear-gradient(135deg,rgba(160,112,255,0.12),rgba(79,139,255,0.08));border-color:rgba(160,112,255,0.25);">
					<div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
						<span style="width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 10px var(--green);"></span>
						<span style="font-size:11px;font-family:var(--font-mono);letter-spacing:0.1em;text-transform:uppercase;color:var(--text-soft);"><?php esc_html_e( 'Live now', 'chatbotistic' ); ?></span>
					</div>
					<h4><?php esc_html_e( 'Skip the form — book a demo', 'chatbotistic' ); ?></h4>
					<p><?php esc_html_e( 'Tell us about your business and we’ll prepare a 14-day demo environment.', 'chatbotistic' ); ?></p>
					<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>" style="margin-top:10px;"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</div>

</main>

<?php
get_footer();
