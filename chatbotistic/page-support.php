<?php
/**
 * Template Name: Support Page
 *
 * Dedicated Get Support page — self-serve paths, a structured request form
 * that arrives pre-triaged, transparent response times, and a clear
 * "what happens next" timeline.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_status = isset( $_GET['support'] ) ? sanitize_key( wp_unslash( $_GET['support'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$cb_paths = array(
	array( 'page',  __( 'Documentation', 'chatbotistic' ), __( 'Setup guides and how-tos for every module.', 'chatbotistic' ), home_url( '/docs/' ) ),
	array( 'chat',  __( 'FAQs', 'chatbotistic' ),          __( 'Quick answers to the most common questions.', 'chatbotistic' ), home_url( '/faqs/' ) ),
	array( 'play',  __( 'Tutorials', 'chatbotistic' ),     __( 'Step-by-step walkthroughs from signup to launch.', 'chatbotistic' ), home_url( '/tutorials/' ) ),
	array( 'users', __( 'Member portal', 'chatbotistic' ), __( 'Manage your plan, licenses and account.', 'chatbotistic' ), home_url( '/account/' ) ),
);

$cb_sla = array(
	array( __( 'Urgent', 'chatbotistic' ), __( 'Service is down or unusable', 'chatbotistic' ), __( 'Within 4 business hours', 'chatbotistic' ) ),
	array( __( 'High', 'chatbotistic' ),   __( 'A key feature is broken', 'chatbotistic' ),     __( 'Within 1 business day', 'chatbotistic' ) ),
	array( __( 'Normal', 'chatbotistic' ), __( 'A question or minor issue', 'chatbotistic' ),   __( '1–2 business days', 'chatbotistic' ) ),
	array( __( 'Low', 'chatbotistic' ),    __( 'Feedback or a suggestion', 'chatbotistic' ),    __( '2–3 business days', 'chatbotistic' ) ),
);

$cb_steps = array(
	array( __( 'You submit', 'chatbotistic' ),    __( 'Your request arrives pre-tagged with product area, type and priority — no back-and-forth to explain it.', 'chatbotistic' ) ),
	array( __( 'We triage', 'chatbotistic' ),     __( 'It routes straight to the right specialist, who reviews it against the response time for its priority.', 'chatbotistic' ) ),
	array( __( 'You get a fix', 'chatbotistic' ), __( 'You receive a real answer or a clear next step by email — and a ticket reference to follow up.', 'chatbotistic' ) ),
);

$cb_faqs = array(
	array( 'q' => __( 'How fast will I hear back?', 'chatbotistic' ), 'a' => __( 'It depends on the priority you select. Urgent issues are answered within 4 business hours; normal questions within 1–2 business days. The full table is shown next to the form.', 'chatbotistic' ) ),
	array( 'q' => __( 'Do I need an account to get support?', 'chatbotistic' ), 'a' => __( 'No — anyone can submit a request here. If you are a member, signing in first lets us see your plan and resolve billing or license issues faster.', 'chatbotistic' ) ),
	array( 'q' => __( 'What if my issue is urgent?', 'chatbotistic' ), 'a' => __( 'Set the priority to Urgent and describe the impact. Urgent requests are escalated immediately and answered within 4 business hours.', 'chatbotistic' ) ),
	array( 'q' => __( 'Can I request a new feature?', 'chatbotistic' ), 'a' => __( 'Yes. Choose “Feature request” as the request type — every suggestion is logged and reviewed by the product team.', 'chatbotistic' ) ),
	array( 'q' => __( 'Where do I manage billing?', 'chatbotistic' ), 'a' => __( 'Billing, invoices and plan changes live in your member portal. For anything the portal cannot resolve, submit a billing request here.', 'chatbotistic' ) ),
);
cb_add_faq_schema( $cb_faqs );

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<nav class="cb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'chatbotistic' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php esc_html_e( 'Support', 'chatbotistic' ); ?></span>
			</nav>
			<span class="cb-eyebrow"><?php esc_html_e( 'Get Support', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Real help, fast — and you always know what happens next', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Start with self-serve answers, or send a structured request that reaches the right specialist already triaged. No ticket black holes.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-shead cb-reveal cb-center">
				<span class="cb-eyebrow"><?php esc_html_e( 'Fastest first', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Many answers are one click away', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-grid cb-grid--4">
				<?php foreach ( $cb_paths as $cb_p ) : ?>
					<a class="cb-feature cb-reveal" href="<?php echo esc_url( $cb_p[3] ); ?>">
						<div class="cb-feature__ico"><?php cb_icon( $cb_p[0], 20 ); ?></div>
						<h3><?php echo esc_html( $cb_p[1] ); ?></h3>
						<p><?php echo esc_html( $cb_p[2] ); ?></p>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-grid cb-grid--2" style="margin-top:0;align-items:start;">

				<div class="cb-card cb-glass cb-glass-edge cb-reveal">
					<span class="cb-eyebrow"><?php esc_html_e( 'Submit a request', 'chatbotistic' ); ?></span>
					<h2 class="cb-h3" style="margin:12px 0 4px;"><?php esc_html_e( 'Tell us what you need', 'chatbotistic' ); ?></h2>
					<p class="cb-soft" style="font-size:14px;"><?php esc_html_e( 'The more specific you are, the faster we resolve it.', 'chatbotistic' ); ?></p>

					<?php if ( 'success' === $cb_status ) : ?>
						<div class="cb-note cb-note--ok" style="margin-top:16px;"><?php esc_html_e( 'Got it — your request is in. We will reply by email within the response time for its priority.', 'chatbotistic' ); ?></div>
					<?php elseif ( 'error' === $cb_status ) : ?>
						<div class="cb-note cb-note--err" style="margin-top:16px;"><?php esc_html_e( 'Please add your name, a valid email, a subject and a message.', 'chatbotistic' ); ?></div>
					<?php endif; ?>

					<form class="cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:18px;">
						<input type="hidden" name="action" value="cb_support_request">
						<?php wp_nonce_field( 'cb_support_request', 'cb_support_req_nonce' ); ?>

						<div class="cb-field-row">
							<div class="cb-field">
								<label for="cb-s-name"><?php esc_html_e( 'Your name', 'chatbotistic' ); ?></label>
								<input id="cb-s-name" type="text" name="cb_name" required>
							</div>
							<div class="cb-field">
								<label for="cb-s-email"><?php esc_html_e( 'Email', 'chatbotistic' ); ?></label>
								<input id="cb-s-email" type="email" name="cb_email" required>
							</div>
						</div>

						<div class="cb-field-row">
							<div class="cb-field">
								<label for="cb-s-area"><?php esc_html_e( 'Product area', 'chatbotistic' ); ?></label>
								<select id="cb-s-area" name="cb_area">
									<option value="AI Chatbot"><?php esc_html_e( 'AI Chatbot', 'chatbotistic' ); ?></option>
									<option value="WhatsApp Automation"><?php esc_html_e( 'WhatsApp Automation', 'chatbotistic' ); ?></option>
									<option value="Booking Forms"><?php esc_html_e( 'Booking Forms', 'chatbotistic' ); ?></option>
									<option value="WordPress Plugin"><?php esc_html_e( 'WordPress Plugin', 'chatbotistic' ); ?></option>
									<option value="Billing &amp; account"><?php esc_html_e( 'Billing & account', 'chatbotistic' ); ?></option>
									<option value="Other"><?php esc_html_e( 'Other', 'chatbotistic' ); ?></option>
								</select>
							</div>
							<div class="cb-field">
								<label for="cb-s-type"><?php esc_html_e( 'Request type', 'chatbotistic' ); ?></label>
								<select id="cb-s-type" name="cb_type">
									<option value="How-to question"><?php esc_html_e( 'How-to question', 'chatbotistic' ); ?></option>
									<option value="Something is broken"><?php esc_html_e( 'Something is broken', 'chatbotistic' ); ?></option>
									<option value="Billing &amp; payments"><?php esc_html_e( 'Billing & payments', 'chatbotistic' ); ?></option>
									<option value="Feature request"><?php esc_html_e( 'Feature request', 'chatbotistic' ); ?></option>
									<option value="Account access"><?php esc_html_e( 'Account access', 'chatbotistic' ); ?></option>
								</select>
							</div>
						</div>

						<div class="cb-field">
							<label for="cb-s-priority"><?php esc_html_e( 'Priority', 'chatbotistic' ); ?></label>
							<select id="cb-s-priority" name="cb_priority">
								<option value="normal"><?php esc_html_e( 'Normal — a question or minor issue', 'chatbotistic' ); ?></option>
								<option value="low"><?php esc_html_e( 'Low — feedback or a suggestion', 'chatbotistic' ); ?></option>
								<option value="high"><?php esc_html_e( 'High — a key feature is broken', 'chatbotistic' ); ?></option>
								<option value="urgent"><?php esc_html_e( 'Urgent — service is down or unusable', 'chatbotistic' ); ?></option>
							</select>
						</div>

						<div class="cb-field">
							<label for="cb-s-subject"><?php esc_html_e( 'Subject', 'chatbotistic' ); ?></label>
							<input id="cb-s-subject" type="text" name="cb_subject" required>
						</div>
						<div class="cb-field">
							<label for="cb-s-url"><?php esc_html_e( 'Related URL (optional)', 'chatbotistic' ); ?></label>
							<input id="cb-s-url" type="url" name="cb_url" placeholder="https://">
						</div>
						<div class="cb-field">
							<label for="cb-s-message"><?php esc_html_e( 'Describe what is happening', 'chatbotistic' ); ?></label>
							<textarea id="cb-s-message" name="cb_message" rows="5" required></textarea>
						</div>
						<div class="cb-field">
							<label for="cb-s-tried"><?php esc_html_e( 'What have you already tried? (optional)', 'chatbotistic' ); ?></label>
							<textarea id="cb-s-tried" name="cb_tried" rows="3"></textarea>
						</div>
						<div>
							<button type="submit" class="cb-btn cb-btn--primary cb-btn--lg"><?php esc_html_e( 'Send request', 'chatbotistic' ); ?></button>
						</div>
					</form>
				</div>

				<div class="cb-stack cb-reveal">
					<div class="cb-card cb-glass">
						<h3 class="cb-h3"><?php esc_html_e( 'Response times', 'chatbotistic' ); ?></h3>
						<p class="cb-soft" style="font-size:13.5px;margin-top:4px;"><?php esc_html_e( 'No guesswork — here is exactly what to expect.', 'chatbotistic' ); ?></p>
						<div style="margin-top:14px;display:flex;flex-direction:column;gap:10px;">
							<?php foreach ( $cb_sla as $cb_row ) : ?>
								<div style="display:flex;justify-content:space-between;gap:14px;align-items:baseline;padding-bottom:10px;border-bottom:1px solid var(--cb-line);">
									<div>
										<b style="font-size:14px;"><?php echo esc_html( $cb_row[0] ); ?></b>
										<div class="cb-dim" style="font-size:12.5px;"><?php echo esc_html( $cb_row[1] ); ?></div>
									</div>
									<span class="cb-mono" style="font-size:12px;color:var(--cb-indigo-2);white-space:nowrap;"><?php echo esc_html( $cb_row[2] ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="cb-card cb-glass">
						<h3 class="cb-h3"><?php esc_html_e( 'What happens next', 'chatbotistic' ); ?></h3>
						<ol class="cb-checklist" style="margin-top:12px;">
							<?php foreach ( $cb_steps as $cb_s ) : ?>
								<li><strong><?php echo esc_html( $cb_s[0] ); ?></strong> — <?php echo esc_html( $cb_s[1] ); ?></li>
							<?php endforeach; ?>
						</ol>
					</div>

					<div class="cb-card cb-glass">
						<h3 class="cb-h3"><?php esc_html_e( 'Already a member?', 'chatbotistic' ); ?></h3>
						<p class="cb-soft" style="font-size:14px;margin-top:6px;"><?php esc_html_e( 'Sign in and use the portal’s support tab — we will see your plan and licenses for a faster fix.', 'chatbotistic' ); ?></p>
						<div style="margin-top:14px;">
							<?php cb_button( __( 'Open member portal', 'chatbotistic' ), home_url( '/account/' ), 'ghost', array( 'size' => 'sm', 'icon' => 'arrow-r' ) ); ?>
						</div>
					</div>
				</div>

			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'Support FAQ', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Before you ask', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-faq" style="text-align:left;">
				<?php foreach ( $cb_faqs as $cb_faq ) : ?>
					<div class="cb-faq__item">
						<button type="button" class="cb-faq__q">
							<span><?php echo esc_html( $cb_faq['q'] ); ?></span>
							<?php cb_icon( 'plus', 16 ); ?>
						</button>
						<div class="cb-faq__a"><p><?php echo esc_html( $cb_faq['a'] ); ?></p></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
