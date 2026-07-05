<?php
/**
 * Template Name: Book a Demo (V4)
 *
 * 23-field qualification form for manual demo approval (14-day demo
 * environment with paid-plan features). Posts to admin-post.php with a
 * nonce so the request is captured server-side and forwarded to ops.
 * Renders an inline confirmation screen on `?demo=submitted`.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_submitted = isset( $_GET['demo'] ) && 'submitted' === $_GET['demo']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<main class="page-fade">

	<?php if ( $cb_submitted ) : ?>

		<section class="section section-tight" style="padding-bottom:0;">
			<div class="container" style="text-align:center;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Demo request received', 'chatbotistic' ); ?></span>
				<h1 class="h-1 text-grad" style="margin:18px auto 0;max-width:760px;"><?php esc_html_e( 'We’re reviewing your business details.', 'chatbotistic' ); ?></h1>
				<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'Demo access is approved manually. We’ll prepare a 14-day test setup using the most relevant paid-plan features for your use case.', 'chatbotistic' ); ?></p>
			</div>
		</section>

		<div class="container" style="padding-bottom:100px;">
			<div class="glass glass-edge" style="max-width:620px;margin:40px auto 0;padding:40px;border-radius:22px;text-align:center;">
				<div class="result-ico ok" style="margin:0 auto 22px;">✓</div>
				<h3 class="h-3"><?php esc_html_e( 'What happens next', 'chatbotistic' ); ?></h3>
				<div style="text-align:left;margin-top:20px;display:flex;flex-direction:column;gap:4px;">
					<div class="demo-step"><span class="n">1</span><span><?php esc_html_e( 'We review your website, workflow, and goals (usually within 1 business day).', 'chatbotistic' ); ?></span></div>
					<div class="demo-step"><span class="n">2</span><span><?php esc_html_e( 'If Chatbotistic is a fit, we build a 14-day demo environment around your use case.', 'chatbotistic' ); ?></span></div>
					<div class="demo-step"><span class="n">3</span><span><?php esc_html_e( 'You receive login details and a guided onboarding walkthrough.', 'chatbotistic' ); ?></span></div>
				</div>
				<div style="display:flex;gap:12px;justify-content:center;margin-top:26px;flex-wrap:wrap;">
					<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Submit another', 'chatbotistic' ); ?></a>
					<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'View pricing', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>

	<?php else : ?>

		<section class="section section-tight" style="padding-bottom:0;">
			<div class="container" style="text-align:center;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></span>
				<h1 class="h-1 text-grad" style="margin:18px auto 0;max-width:860px;"><?php esc_html_e( 'Book a Chatbotistic demo built around your business.', 'chatbotistic' ); ?></h1>
				<p class="lead" style="margin:18px auto 0;max-width:760px;"><?php esc_html_e( 'Tell us about your business, website, current workflow, and expectations. If Chatbotistic is a fit, we’ll prepare a 14-day demo environment using the most relevant paid-plan features for your use case.', 'chatbotistic' ); ?></p>
			</div>
		</section>

		<section class="section">
			<div class="container">
				<div class="demo-layout">
					<form class="cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
						<input type="hidden" name="action" value="cb_demo_request">
						<?php wp_nonce_field( 'cb_demo_request', 'cb_demo_nonce' ); ?>
						<input type="text" name="cb_company_alt" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true">

						<div class="form-section-label"><?php esc_html_e( 'About you', 'chatbotistic' ); ?></div>
						<div class="grid-2">
							<div class="field"><label><?php esc_html_e( 'Full name', 'chatbotistic' ); ?> <span class="req">*</span></label><input name="full_name" placeholder="Sarah Kim" required></div>
							<div class="field"><label><?php esc_html_e( 'Work email', 'chatbotistic' ); ?> <span class="req">*</span></label><input type="email" name="work_email" placeholder="you@company.com" required></div>
						</div>
						<div class="grid-2">
							<div class="field"><label><?php esc_html_e( 'WhatsApp / phone', 'chatbotistic' ); ?> <span class="req">*</span></label><input name="phone" placeholder="+1 555 000 0000" required></div>
							<div class="field"><label><?php esc_html_e( 'Company name', 'chatbotistic' ); ?> <span class="req">*</span></label><input name="company" placeholder="Acme Inc." required></div>
						</div>
						<div class="grid-2">
							<div class="field"><label><?php esc_html_e( 'Company website', 'chatbotistic' ); ?></label><input type="url" name="website" placeholder="https://acme.com"></div>
							<div class="field"><label><?php esc_html_e( 'Country / target market', 'chatbotistic' ); ?></label><input name="market" placeholder="United States"></div>
						</div>
						<div class="field"><label><?php esc_html_e( 'Business type', 'chatbotistic' ); ?></label>
							<select name="biz_type">
								<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Agency', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Local business', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Clinic / Spa', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'eCommerce', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'SaaS', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Real estate', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Coach / Consultant', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Travel agency', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Other', 'chatbotistic' ); ?></option>
							</select>
						</div>

						<div class="form-section-label"><?php esc_html_e( 'Your current setup', 'chatbotistic' ); ?></div>
						<div class="grid-2">
							<div class="field"><label><?php esc_html_e( 'Monthly website visitors', 'chatbotistic' ); ?></label>
								<select name="visitors">
									<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( 'Under 1,000', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( '1,000 – 10,000', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( '10,000 – 50,000', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( '50,000 – 200,000', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( '200,000+', 'chatbotistic' ); ?></option>
								</select>
							</div>
							<div class="field"><label><?php esc_html_e( 'Current lead source', 'chatbotistic' ); ?></label><input name="lead_source" placeholder="<?php esc_attr_e( 'Forms, ads, referrals…', 'chatbotistic' ); ?>"></div>
						</div>
						<div class="field"><label><?php esc_html_e( 'Current WhatsApp usage', 'chatbotistic' ); ?></label>
							<select name="wa_usage">
								<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Not using WhatsApp yet', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Personal WhatsApp manually', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'WhatsApp Business app', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'WhatsApp Business API', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Another chatbot tool', 'chatbotistic' ); ?></option>
							</select>
						</div>
						<div class="grid-3">
							<?php foreach ( array( 'wp' => __( 'Use WordPress?', 'chatbotistic' ), 'woo' => __( 'Use WooCommerce?', 'chatbotistic' ), 'wl' => __( 'Need white-label / agency?', 'chatbotistic' ) ) as $cb_key => $cb_label ) : ?>
								<div class="field"><label><?php echo esc_html( $cb_label ); ?></label>
									<select name="<?php echo esc_attr( $cb_key ); ?>">
										<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
										<option><?php esc_html_e( 'Yes', 'chatbotistic' ); ?></option>
										<option><?php esc_html_e( 'No', 'chatbotistic' ); ?></option>
										<option><?php esc_html_e( 'Not sure', 'chatbotistic' ); ?></option>
									</select>
								</div>
							<?php endforeach; ?>
						</div>
						<div class="field"><label><?php esc_html_e( 'Current tools you’re using', 'chatbotistic' ); ?></label><input name="current_tools" placeholder="<?php esc_attr_e( 'CRM, helpdesk, booking, ad platforms…', 'chatbotistic' ); ?>"></div>

						<div class="form-section-label"><?php esc_html_e( 'What you need', 'chatbotistic' ); ?></div>
						<div class="field"><label><?php esc_html_e( 'What do you need?', 'chatbotistic' ); ?></label>
							<select name="need">
								<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'AI chatbot only', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'WhatsApp widget only', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Booking form only', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Lead capture only', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'CRM integration', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Agency white-label', 'chatbotistic' ); ?></option>
								<option><?php esc_html_e( 'Full system (chat + WhatsApp + booking)', 'chatbotistic' ); ?></option>
							</select>
						</div>
						<div class="grid-2">
							<div class="field"><label><?php esc_html_e( 'Websites / domains to connect', 'chatbotistic' ); ?></label>
								<select name="domains">
									<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
									<option>1</option><option>2 – 5</option><option>6 – 10</option><option>11 – 50</option><option>50+</option>
								</select>
							</div>
							<div class="field"><label><?php esc_html_e( 'Team members on the inbox', 'chatbotistic' ); ?></label>
								<select name="team_size">
									<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( 'Just me', 'chatbotistic' ); ?></option>
									<option>2 – 5</option><option>6 – 15</option><option>15+</option>
								</select>
							</div>
						</div>
						<div class="field"><label><?php esc_html_e( 'Main problem you want to solve', 'chatbotistic' ); ?></label><textarea name="problem" placeholder="<?php esc_attr_e( 'e.g. We miss leads after hours and reply too slowly on WhatsApp.', 'chatbotistic' ); ?>"></textarea></div>
						<div class="field"><label><?php esc_html_e( 'Expected result from Chatbotistic', 'chatbotistic' ); ?></label><textarea name="expected" placeholder="<?php esc_attr_e( 'e.g. Capture every lead 24/7 and route booking requests automatically.', 'chatbotistic' ); ?>"></textarea></div>

						<div class="form-section-label"><?php esc_html_e( 'Logistics', 'chatbotistic' ); ?></div>
						<div class="grid-2">
							<div class="field"><label><?php esc_html_e( 'Preferred demo date / time', 'chatbotistic' ); ?></label><input type="datetime-local" name="preferred_time"></div>
							<div class="field"><label><?php esc_html_e( 'Budget / preferred plan', 'chatbotistic' ); ?></label>
								<select name="budget">
									<option value=""><?php esc_html_e( 'Select…', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( 'Free Forever', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( 'Pro ($9/mo)', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( 'Agency ($99/mo)', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( 'Lifetime / LTD', 'chatbotistic' ); ?></option>
									<option><?php esc_html_e( 'Not sure yet', 'chatbotistic' ); ?></option>
								</select>
							</div>
						</div>
						<div class="field"><label><?php esc_html_e( 'Additional notes', 'chatbotistic' ); ?></label><textarea name="notes" placeholder="<?php esc_attr_e( 'Anything else we should know before the demo.', 'chatbotistic' ); ?>" style="min-height:90px;"></textarea></div>

						<label class="check-line" style="margin-top:8px;">
							<input type="checkbox" name="consent" required>
							<?php
							printf(
								/* translators: %s: Privacy Policy link */
								wp_kses(
									__( 'I agree to be contacted about my demo request and accept the %s.', 'chatbotistic' ),
									array( 'a' => array( 'href' => array(), 'style' => array() ) )
								),
								'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" style="color:var(--blue-bright);">' . esc_html__( 'Privacy Policy', 'chatbotistic' ) . '</a>'
							);
							?>
						</label>
						<button class="btn btn-primary btn-lg" type="submit" style="justify-content:center;margin-top:10px;"><?php esc_html_e( 'Request demo access', 'chatbotistic' ); ?></button>
					</form>

					<aside style="display:flex;flex-direction:column;gap:18px;">
						<div class="glass glass-edge demo-aside-card">
							<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( '14-day demo', 'chatbotistic' ); ?></span>
							<h3 class="h-3" style="margin-top:14px;"><?php esc_html_e( 'How demo access works', 'chatbotistic' ); ?></h3>
							<p style="color:var(--text-soft);font-size:13.5px;line-height:1.6;margin:8px 0 16px;"><?php esc_html_e( 'Demo access is approved manually. We review your business details first, then prepare a 14-day test setup using the most relevant paid-plan features for your use case.', 'chatbotistic' ); ?></p>
							<div style="display:flex;flex-direction:column;gap:2px;">
								<div class="demo-step"><span class="n">1</span><span><?php esc_html_e( 'Tell us about your business', 'chatbotistic' ); ?></span></div>
								<div class="demo-step"><span class="n">2</span><span><?php esc_html_e( 'We review your fit (≈1 business day)', 'chatbotistic' ); ?></span></div>
								<div class="demo-step"><span class="n">3</span><span><?php esc_html_e( 'We build your 14-day environment', 'chatbotistic' ); ?></span></div>
								<div class="demo-step"><span class="n">4</span><span><?php esc_html_e( 'Guided onboarding walkthrough', 'chatbotistic' ); ?></span></div>
							</div>
						</div>
						<div class="contact-card" style="background:linear-gradient(135deg,rgba(160,112,255,0.12),rgba(79,139,255,0.06));border:1px solid rgba(160,112,255,0.25);border-radius:18px;padding:24px;">
							<h4 style="margin-bottom:4px;"><?php esc_html_e( 'Not ready for a demo?', 'chatbotistic' ); ?></h4>
							<p style="font-size:13.5px;color:var(--text-soft);"><?php esc_html_e( 'Start free in 60 seconds — no card, no review needed.', 'chatbotistic' ); ?></p>
							<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( function_exists( 'cb_free_checkout_url' ) ? cb_free_checkout_url() : home_url( '/register/?plan=free' ) ); ?>" style="margin-top:12px;"><?php esc_html_e( 'Create free account', 'chatbotistic' ); ?></a>
						</div>
					</aside>
				</div>
			</div>
		</section>

	<?php endif; ?>

</main>

<?php
get_footer();
