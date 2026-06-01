<?php
/**
 * Front page — homepage.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_signup = cb_plans_url();

$cb_problems = array(
	array( __( 'Missed leads', 'chatbotistic' ),    __( 'Visitors will not fill long forms. They leave, and you never know they were there.', 'chatbotistic' ) ),
	array( __( 'Slow replies', 'chatbotistic' ),    __( 'By the time you answer from your inbox, the buyer has already messaged a competitor.', 'chatbotistic' ) ),
	array( __( 'Scattered tools', 'chatbotistic' ), __( 'WhatsApp, web chat, email and forms — context spread across five disconnected apps.', 'chatbotistic' ) ),
);

$cb_features = array(
	array( 'ai',    __( 'AI WhatsApp Agent', 'chatbotistic' ), __( 'A trained agent that answers, qualifies and books — 24/7, in your tone.', 'chatbotistic' ), '/ai-chatbot/' ),
	array( 'wa',    __( 'WhatsApp Widgets', 'chatbotistic' ),  __( 'Click-to-chat with department routing and pre-chat lead capture.', 'chatbotistic' ), '/whatsapp-automation/' ),
	array( 'cal',   __( 'Booking Forms', 'chatbotistic' ),     __( 'Consultations, demos and services with calendar sync, inside the chat.', 'chatbotistic' ), '/booking-forms/' ),
	array( 'form',  __( 'Lead Capture', 'chatbotistic' ),      __( 'Conversion-optimised inline, popup and sidebar forms.', 'chatbotistic' ), '/features/' ),
	array( 'inbox', __( 'Unified Inbox', 'chatbotistic' ),     __( 'One shared team inbox for WhatsApp, web chat and email.', 'chatbotistic' ), '/features/' ),
	array( 'chart', __( 'Analytics', 'chatbotistic' ),         __( 'Conversation funnels, response times and conversion reporting.', 'chatbotistic' ), '/features/' ),
	array( 'plug',  __( 'Integrations', 'chatbotistic' ),      __( 'WordPress, CRMs, Stripe, Google Sheets and webhooks out of the box.', 'chatbotistic' ), '/features/' ),
	array( 'tag',   __( 'White Label', 'chatbotistic' ),       __( 'Your brand, your domain. Resell to clients with full ownership.', 'chatbotistic' ), '/agency-white-label/' ),
);

$cb_steps = array(
	array( __( 'Create your widget', 'chatbotistic' ), __( 'Pick chat, WhatsApp or booking. Match your brand in seconds.', 'chatbotistic' ) ),
	array( __( 'Connect WhatsApp', 'chatbotistic' ),   __( 'Link your number or provision a new business line.', 'chatbotistic' ) ),
	array( __( 'Embed anywhere', 'chatbotistic' ),     __( 'One snippet, or install the WordPress plugin — done.', 'chatbotistic' ) ),
	array( __( 'Capture & automate', 'chatbotistic' ), __( 'Leads flow into your inbox, CRM and follow-ups automatically.', 'chatbotistic' ) ),
);

$cb_uses = array(
	array( 'users',  __( 'Agencies', 'chatbotistic' ),       __( 'Resell to clients', 'chatbotistic' ), 'agencies' ),
	array( 'store',  __( 'Local business', 'chatbotistic' ), __( 'Capture walk-ins', 'chatbotistic' ), 'local-business' ),
	array( 'cal',    __( 'Clinics & spas', 'chatbotistic' ), __( 'Appointment intake', 'chatbotistic' ), 'clinics-spas' ),
	array( 'home',   __( 'Real estate', 'chatbotistic' ),    __( 'Property leads', 'chatbotistic' ), 'real-estate' ),
	array( 'cart',   __( 'eCommerce', 'chatbotistic' ),      __( 'Cart recovery', 'chatbotistic' ), 'ecommerce' ),
	array( 'rocket', __( 'Coaches', 'chatbotistic' ),        __( 'Discovery flows', 'chatbotistic' ), 'coaches' ),
	array( 'wp',     __( 'WordPress sites', 'chatbotistic' ),__( 'Native plugin', 'chatbotistic' ), 'wordpress-sites' ),
	array( 'spark',  __( 'SaaS founders', 'chatbotistic' ),  __( 'Onboarding bots', 'chatbotistic' ), 'saas-founders' ),
);

$cb_integrations = array( 'WordPress', 'WhatsApp', 'WooCommerce', 'HubSpot', 'Stripe', 'PayPal', 'Zoho', 'Pipedrive', 'Google Sheets', 'Webhooks' );

$cb_faqs = array(
	array( 'q' => __( 'What exactly is Chatbotistic?', 'chatbotistic' ), 'a' => __( 'An AI WhatsApp Agent and chat-widget platform. It puts a smart agent on your website that answers questions, qualifies leads and books appointments 24/7 — across WhatsApp, web chat and email.', 'chatbotistic' ) ),
	array( 'q' => __( 'How long does setup take?', 'chatbotistic' ), 'a' => __( 'Most businesses are live in under an hour. Describe your business, pick a widget, embed one snippet or install the WordPress plugin — no developer required.', 'chatbotistic' ) ),
	array( 'q' => __( 'Do I need WhatsApp Business API?', 'chatbotistic' ), 'a' => __( 'Not for click-to-chat widgets. For the automated AI agent you connect a WhatsApp Business API number — we guide you through it, or provision one on higher plans.', 'chatbotistic' ) ),
	array( 'q' => __( 'Is there a free plan?', 'chatbotistic' ), 'a' => __( 'Yes — Free is free forever, with one widget and 200 conversations a month. Paid plans start at $19/month. See the pricing page for details.', 'chatbotistic' ) ),
	array( 'q' => __( 'Does it work with my CRM and WordPress?', 'chatbotistic' ), 'a' => __( 'Yes. There is a native WordPress plugin, and integrations for HubSpot, Zoho, Pipedrive, Stripe, Google Sheets and webhooks.', 'chatbotistic' ) ),
);
cb_add_faq_schema( $cb_faqs );
?>

<section class="cb-section cb-hero">
	<div class="cb-container">
		<div class="cb-hero__grid">
			<div class="cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'AI WhatsApp Agent Platform', 'chatbotistic' ); ?></span>
				<h1 class="cb-display" style="margin-top:20px;">
					<span class="cb-grad"><?php esc_html_e( 'Turn website visitors into ', 'chatbotistic' ); ?></span>
					<span class="cb-grad cb-grad--brand"><?php esc_html_e( 'real conversations.', 'chatbotistic' ); ?></span>
				</h1>
				<p class="cb-lead" style="margin-top:20px;max-width:540px;">
					<?php esc_html_e( 'Chatbotistic puts an AI WhatsApp Agent on your site that answers questions, qualifies leads and books appointments — 24/7. Chat, WhatsApp, booking and a shared inbox in one platform.', 'chatbotistic' ); ?>
				</p>
				<div style="display:flex;gap:12px;margin-top:30px;flex-wrap:wrap;">
					<?php
					cb_button( __( 'Start free', 'chatbotistic' ), $cb_signup, 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
					cb_button( __( 'See features', 'chatbotistic' ), home_url( '/features/' ), 'ghost', array( 'size' => 'lg' ) );
					?>
				</div>
				<div class="cb-hero__meta">
					<span><?php cb_icon( 'check', 15 ); ?> <?php esc_html_e( 'No credit card required', 'chatbotistic' ); ?></span>
					<span><?php cb_icon( 'check', 15 ); ?> <?php esc_html_e( 'Setup in under an hour', 'chatbotistic' ); ?></span>
				</div>
				<div class="cb-trust">
					<span class="cb-stars">
						<?php for ( $cb_i = 0; $cb_i < 5; $cb_i++ ) : ?>
							<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2 14.5 9H22l-6 4.5 2.3 7.5L12 16.5 5.7 21l2.3-7.5L2 9h7.5z"/></svg>
						<?php endfor; ?>
					</span>
					<span><?php esc_html_e( '4.9 / 5 — loved by growing service businesses', 'chatbotistic' ); ?></span>
				</div>
			</div>

			<div class="cb-reveal cb-mock" aria-hidden="true">
				<div class="cb-mock__head">
					<div class="cb-mock__avatar">C</div>
					<div>
						<b><?php esc_html_e( 'Chatbotistic Agent', 'chatbotistic' ); ?></b>
						<span><?php esc_html_e( 'Online · replies instantly', 'chatbotistic' ); ?></span>
					</div>
				</div>
				<div class="cb-mock__body">
					<div class="cb-bubble cb-bubble--bot"><?php esc_html_e( 'Hi! I can give you a quote or book a call. What do you need?', 'chatbotistic' ); ?></div>
					<div class="cb-bubble cb-bubble--user"><?php esc_html_e( 'A quote for a new website', 'chatbotistic' ); ?></div>
					<div class="cb-bubble cb-bubble--bot"><?php esc_html_e( 'Great — what is your budget range?', 'chatbotistic' ); ?></div>
					<div class="cb-mock__quick">
						<span><?php esc_html_e( 'Under $5k', 'chatbotistic' ); ?></span>
						<span><?php esc_html_e( '$5k–$15k', 'chatbotistic' ); ?></span>
						<span><?php esc_html_e( '$15k+', 'chatbotistic' ); ?></span>
					</div>
				</div>
				<div class="cb-mock__input">
					<?php cb_icon( 'bolt', 15 ); ?>
					<span><?php esc_html_e( 'Type a message…', 'chatbotistic' ); ?></span>
					<span class="cb-mock__send"><?php cb_icon( 'arrow-r', 15 ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-statband">
			<?php
			$cb_stats = array(
				array( '24/7', __( 'always-on lead capture', 'chatbotistic' ) ),
				array( '< 1 min', __( 'average first reply', 'chatbotistic' ) ),
				array( '6-in-1', __( 'tools replaced by one platform', 'chatbotistic' ) ),
				array( '$19', __( 'a month to start — no per-seat fee', 'chatbotistic' ) ),
			);
			foreach ( $cb_stats as $cb_s ) :
				?>
				<div class="cb-reveal">
					<div class="cb-statband__num cb-grad cb-grad--brand"><?php echo esc_html( $cb_s[0] ); ?></div>
					<div class="cb-statband__label"><?php echo esc_html( $cb_s[1] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'The problem', 'chatbotistic' ); ?></span>
			<h2 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Most websites leak leads. Quietly.', 'chatbotistic' ); ?></span></h2>
			<p class="cb-lead"><?php esc_html_e( 'You spent months on the site and weeks on the ads — and visitors still bounce. Here is where the leak happens.', 'chatbotistic' ); ?></p>
		</div>
		<div class="cb-grid cb-grid--3">
			<?php foreach ( $cb_problems as $cb_i => $cb_p ) : ?>
				<div class="cb-feature cb-reveal">
					<div class="cb-mono cb-dim" style="font-size:12px;letter-spacing:0.12em;"><?php echo esc_html( sprintf( '%02d', $cb_i + 1 ) ); ?></div>
					<h3 style="margin-top:8px;"><?php echo esc_html( $cb_p[0] ); ?></h3>
					<p><?php echo esc_html( $cb_p[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container cb-center">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'Everything you need', 'chatbotistic' ); ?></span>
			<h2 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'One platform that plugs every leak', 'chatbotistic' ); ?></span></h2>
			<p class="cb-lead"><?php esc_html_e( 'Eight tightly integrated modules. Start with what you need, scale into the rest.', 'chatbotistic' ); ?></p>
		</div>
		<div class="cb-grid cb-grid--4" style="text-align:left;">
			<?php foreach ( $cb_features as $cb_f ) : ?>
				<a class="cb-feature cb-reveal" href="<?php echo esc_url( home_url( $cb_f[3] ) ); ?>">
					<div class="cb-feature__ico"><?php cb_icon( $cb_f[0], 20 ); ?></div>
					<h3><?php echo esc_html( $cb_f[1] ); ?></h3>
					<p><?php echo esc_html( $cb_f[2] ); ?></p>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container cb-center">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'How it works', 'chatbotistic' ); ?></span>
			<h2 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Live in four steps. Really.', 'chatbotistic' ); ?></span></h2>
		</div>
		<div class="cb-steps" style="text-align:left;">
			<?php foreach ( $cb_steps as $cb_n => $cb_s ) : ?>
				<div class="cb-step cb-reveal">
					<div class="cb-step__num"><?php echo esc_html( sprintf( '%02d', $cb_n + 1 ) ); ?></div>
					<h3><?php echo esc_html( $cb_s[0] ); ?></h3>
					<p><?php echo esc_html( $cb_s[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'Built for', 'chatbotistic' ); ?></span>
			<h2 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Service brands. Agencies. Storefronts.', 'chatbotistic' ); ?></span></h2>
			<p class="cb-lead"><?php esc_html_e( 'However you win customers, there is a Chatbotistic playbook for it.', 'chatbotistic' ); ?></p>
		</div>
		<div class="cb-grid cb-grid--4">
			<?php foreach ( $cb_uses as $cb_u ) : ?>
				<a class="cb-use cb-reveal" href="<?php echo esc_url( home_url( '/' . $cb_u[3] . '/' ) ); ?>">
					<span class="cb-use__ico"><?php cb_icon( $cb_u[0], 17 ); ?></span>
					<span><b><?php echo esc_html( $cb_u[1] ); ?></b><span><?php echo esc_html( $cb_u[2] ); ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-split">
			<div class="cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'Why teams switch', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2" style="margin-top:14px;"><span class="cb-grad"><?php esc_html_e( 'Replace six subscriptions with one', 'chatbotistic' ); ?></span></h2>
				<p class="cb-lead" style="margin-top:14px;"><?php esc_html_e( 'Chat tool, WhatsApp tool, booking tool, form builder, inbox and CRM — each with its own bill and its own login. Chatbotistic is all of it, working as one.', 'chatbotistic' ); ?></p>
				<ul class="cb-ticklist">
					<li><?php cb_icon( 'check', 16 ); ?> <span><?php esc_html_e( 'One bill, one login, one contact record', 'chatbotistic' ); ?></span></li>
					<li><?php cb_icon( 'check', 16 ); ?> <span><?php esc_html_e( 'No per-seat pricing — invite the whole team', 'chatbotistic' ); ?></span></li>
					<li><?php cb_icon( 'check', 16 ); ?> <span><?php esc_html_e( 'Native WordPress plugin and a clean REST API', 'chatbotistic' ); ?></span></li>
				</ul>
				<div style="margin-top:22px;">
					<?php cb_button( __( 'Compare pricing', 'chatbotistic' ), home_url( '/pricing/' ), 'ghost', array( 'icon' => 'arrow-r' ) ); ?>
				</div>
			</div>
			<div class="cb-quote cb-glass-edge cb-reveal">
				<p>“<?php esc_html_e( 'We replaced our live chat, a booking app and a separate WhatsApp tool with Chatbotistic. Leads go up, the stack got simpler, and the bill got smaller.', 'chatbotistic' ); ?>”</p>
				<footer>
					<span class="cb-quote__avatar">N</span>
					<div>
						<b><?php esc_html_e( 'Naomi R.', 'chatbotistic' ); ?></b>
						<span><?php esc_html_e( 'Founder, a service-business studio', 'chatbotistic' ); ?></span>
					</div>
				</footer>
			</div>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container cb-center">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'Integrations', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Connects with the tools you already use', 'chatbotistic' ); ?></span></h2>
		</div>
		<div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-top:34px;">
			<?php foreach ( $cb_integrations as $cb_int ) : ?>
				<span class="cb-reveal" style="padding:10px 18px;border-radius:999px;background:var(--cb-glass);border:1px solid var(--cb-line);font-size:14px;color:var(--cb-soft);"><?php echo esc_html( $cb_int ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="cb-section cb-section--tight">
	<div class="cb-container cb-center">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Questions, answered', 'chatbotistic' ); ?></span></h2>
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

<section class="cb-section">
	<div class="cb-container">
		<div class="cb-cta cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'Ready when you are', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Launch your AI WhatsApp Agent today', 'chatbotistic' ); ?></span></h2>
			<p class="cb-lead" style="max-width:520px;margin:0 auto;"><?php esc_html_e( 'Start free, upgrade when it pays for itself. No contracts, no per-seat tax.', 'chatbotistic' ); ?></p>
			<div class="cb-cta__actions">
				<?php
				cb_button( __( 'Start free', 'chatbotistic' ), $cb_signup, 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
				cb_button( __( 'View pricing', 'chatbotistic' ), home_url( '/pricing/' ), 'ghost', array( 'size' => 'lg' ) );
				?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
