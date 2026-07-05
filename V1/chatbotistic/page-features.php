<?php
/**
 * Template Name: Features Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

/* Outcome-led reasons buyers choose Chatbotistic. */
$cb_reasons = array(
	array( 'bolt',  __( 'Capture more leads', 'chatbotistic' ),  __( 'Every visitor gets a reply in seconds and becomes a tracked lead — not a bounce you never knew about.', 'chatbotistic' ) ),
	array( 'spark', __( 'Replace six tools', 'chatbotistic' ),   __( 'AI chat, WhatsApp, booking, forms, inbox and CRM in one platform — one bill, one login, no glue code.', 'chatbotistic' ) ),
	array( 'cal',   __( 'Book while you sleep', 'chatbotistic' ),__( 'Qualified buyers self-book and pay, so your calendar fills without a single back-and-forth email.', 'chatbotistic' ) ),
	array( 'tag',   __( 'Pay small-business prices', 'chatbotistic' ), __( 'The full stack from $19/month with no per-seat tax — a fraction of enterprise chat suites.', 'chatbotistic' ) ),
);

/* 16 modules in 4 groups. Linked modules point at their deep landing page. */
$cb_groups = array(
	array(
		'cat'   => __( 'Conversation', 'chatbotistic' ),
		'items' => array(
			array( 'ai',   __( 'AI Chatbot Builder', 'chatbotistic' ),     __( 'Train on your site, docs and FAQs. A visual flow builder with branching, conditions and AI fallback.', 'chatbotistic' ), '/ai-chatbot/' ),
			array( 'wa',   __( 'WhatsApp Widget Builder', 'chatbotistic' ), __( 'Pre-chat capture, business hours, auto-replies, department routing and templated quick-starts.', 'chatbotistic' ), '/whatsapp-automation/' ),
			array( 'chat', __( 'Chat Forms', 'chatbotistic' ),             __( 'Conversational forms that feel like a chat — field-by-field prompts with smart validation.', 'chatbotistic' ), '' ),
			array( 'cal',  __( 'Booking Forms', 'chatbotistic' ),          __( 'Service, consultation and strategy-call booking with calendar sync and deposit collection.', 'chatbotistic' ), '/booking-forms/' ),
		),
	),
	array(
		'cat'   => __( 'Capture & CRM', 'chatbotistic' ),
		'items' => array(
			array( 'form',  __( 'Lead Capture', 'chatbotistic' ),     __( 'Popup, inline, exit-intent and sidebar forms with branching logic that qualifies before notifying.', 'chatbotistic' ), '' ),
			array( 'inbox', __( 'Multi-Agent Inbox', 'chatbotistic' ),__( 'A shared team inbox for AI chat, WhatsApp and email — assignment, mentions and internal notes.', 'chatbotistic' ), '' ),
			array( 'users', __( 'Team Management', 'chatbotistic' ),  __( 'Roles, seats and permissions, with workspace-level access for agency teams and clients.', 'chatbotistic' ), '' ),
			array( 'star',  __( 'Customer Profiles', 'chatbotistic' ),__( 'Auto-built profiles with conversation history, source, tags, value and last touch.', 'chatbotistic' ), '' ),
		),
	),
	array(
		'cat'   => __( 'Payments & Pages', 'chatbotistic' ),
		'items' => array(
			array( 'mail', __( 'Email Notifications', 'chatbotistic' ), __( 'Instant pings, daily digests and assignment emails so your team never misses a lead.', 'chatbotistic' ), '' ),
			array( 'card', __( 'Payment Forms', 'chatbotistic' ),       __( 'Stripe and PayPal collection in chat — deposits, full payment or recurring.', 'chatbotistic' ), '' ),
			array( 'plug', __( 'CRM Integrations', 'chatbotistic' ),    __( 'HubSpot, Zoho, Pipedrive, Sheets and Notion, with two-way sync where supported.', 'chatbotistic' ), '' ),
			array( 'code', __( 'API & Webhooks', 'chatbotistic' ),      __( 'A clean REST API and outbound webhooks for every event — bring your own backend.', 'chatbotistic' ), '' ),
		),
	),
	array(
		'cat'   => __( 'Brand & Insights', 'chatbotistic' ),
		'items' => array(
			array( 'page',  __( 'Landing Pages', 'chatbotistic' ),       __( 'Spin up branded micro-pages with chat embedded — perfect for ad campaigns.', 'chatbotistic' ), '' ),
			array( 'tag',   __( 'White Label Dashboard', 'chatbotistic' ),__( 'Your logo, your domain, your customer login — a full reseller experience.', 'chatbotistic' ), '/agency-white-label/' ),
			array( 'chart', __( 'Analytics', 'chatbotistic' ),           __( 'Funnels, agent metrics, ROI and channel performance, in export-ready reports.', 'chatbotistic' ), '' ),
			array( 'wp',    __( 'WordPress Connector', 'chatbotistic' ),  __( 'A native plugin to manage widgets, sync leads and embed via shortcode or block.', 'chatbotistic' ), '/wordpress-plugin/' ),
		),
	),
);

$cb_steps = array(
	array( __( 'Create your widget', 'chatbotistic' ), __( 'Pick AI chat, WhatsApp or booking and match it to your brand in seconds.', 'chatbotistic' ) ),
	array( __( 'Add your logic', 'chatbotistic' ),     __( 'Train the agent on your content or build a flow with the visual editor.', 'chatbotistic' ) ),
	array( __( 'Embed anywhere', 'chatbotistic' ),     __( 'Drop in one snippet, or install the WordPress plugin — no theme edits.', 'chatbotistic' ) ),
	array( __( 'Capture & convert', 'chatbotistic' ),  __( 'Leads flow into your inbox, CRM and follow-ups automatically.', 'chatbotistic' ) ),
);

$cb_faqs = array(
	array( 'q' => __( 'Do I have to use every module?', 'chatbotistic' ), 'a' => __( 'No. Start with the one you need — AI chat, WhatsApp or booking — and switch the others on as you grow. Everything shares one inbox and one contact record.', 'chatbotistic' ) ),
	array( 'q' => __( 'Can I use Chatbotistic without WordPress?', 'chatbotistic' ), 'a' => __( 'Yes. Embed any widget with a one-line snippet on any platform. On WordPress, the native plugin makes it even simpler.', 'chatbotistic' ) ),
	array( 'q' => __( 'How is the AI agent trained?', 'chatbotistic' ), 'a' => __( 'Point it at your website, upload documents or paste FAQs. It answers strictly from your content, in your tone of voice.', 'chatbotistic' ) ),
	array( 'q' => __( 'Does it integrate with my CRM?', 'chatbotistic' ), 'a' => __( 'Yes — HubSpot, Zoho, Pipedrive, Google Sheets and Notion connect out of the box, with a REST API and webhooks for anything else.', 'chatbotistic' ) ),
	array( 'q' => __( 'Is there a free plan to try the features?', 'chatbotistic' ), 'a' => __( 'Yes. The Free plan lets you build a widget and handle 200 conversations a month at no cost — see the pricing page for details.', 'chatbotistic' ) ),
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
				<span><?php esc_html_e( 'Features', 'chatbotistic' ); ?></span>
			</nav>
			<span class="cb-eyebrow"><?php esc_html_e( 'Features', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Everything you need to capture, convert and close — in one platform', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Sixteen tightly integrated modules across conversation, capture, payments and insight. Built for speed, designed for revenue.', 'chatbotistic' ); ?></p>
			<div class="cb-cta__actions" style="justify-content:center;margin-top:26px;">
				<?php
				cb_button( __( 'Start free', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
				cb_button( __( 'See pricing', 'chatbotistic' ), home_url( '/pricing/' ), 'ghost', array( 'size' => 'lg' ) );
				?>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-shead cb-reveal cb-center">
				<span class="cb-eyebrow"><?php esc_html_e( 'Why teams buy', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'It is not about features — it is about outcomes', 'chatbotistic' ); ?></span></h2>
				<p class="cb-lead"><?php esc_html_e( 'Businesses do not switch to Chatbotistic for a feature list. They switch for these four results.', 'chatbotistic' ); ?></p>
			</div>
			<div class="cb-grid cb-grid--4">
				<?php foreach ( $cb_reasons as $r ) : ?>
					<div class="cb-feature cb-reveal">
						<div class="cb-feature__ico"><?php cb_icon( $r[0], 20 ); ?></div>
						<h3><?php echo esc_html( $r[1] ); ?></h3>
						<p><?php echo esc_html( $r[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php foreach ( $cb_groups as $gi => $g ) : ?>
		<section class="cb-section cb-section--tight">
			<div class="cb-container">
				<div class="cb-shead cb-reveal">
					<span class="cb-eyebrow"><?php echo esc_html( sprintf( '%02d', $gi + 1 ) . ' / ' . $g['cat'] ); ?></span>
					<h2 class="cb-h2"><span class="cb-grad"><?php echo esc_html( $g['cat'] ); ?></span></h2>
				</div>
				<div class="cb-grid cb-grid--4">
					<?php foreach ( $g['items'] as $f ) : ?>
						<?php $tag = $f[3] ? 'a' : 'div'; ?>
						<<?php echo $tag; ?> class="cb-feature cb-reveal"<?php echo $f[3] ? ' href="' . esc_url( home_url( $f[3] ) ) . '"' : ''; ?>>
							<div class="cb-feature__ico"><?php cb_icon( $f[0], 20 ); ?></div>
							<h3><?php echo esc_html( $f[1] ); ?></h3>
							<p><?php echo esc_html( $f[2] ); ?></p>
							<?php if ( $f[3] ) : ?>
								<span class="cb-btn cb-btn--link" style="padding-left:0;margin-top:10px;font-size:13px;"><?php esc_html_e( 'Explore', 'chatbotistic' ); ?> &rarr;</span>
							<?php endif; ?>
						</<?php echo $tag; ?>>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'How it works', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'From signup to live in four steps', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-steps" style="text-align:left;">
				<?php foreach ( $cb_steps as $si => $s ) : ?>
					<div class="cb-step cb-reveal">
						<div class="cb-step__num"><?php echo esc_html( sprintf( '%02d', $si + 1 ) ); ?></div>
						<h3><?php echo esc_html( $s[0] ); ?></h3>
						<p><?php echo esc_html( $s[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Questions about the platform', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-faq" style="text-align:left;">
				<?php foreach ( $cb_faqs as $faq ) : ?>
					<div class="cb-faq__item">
						<button type="button" class="cb-faq__q">
							<span><?php echo esc_html( $faq['q'] ); ?></span>
							<?php cb_icon( 'plus', 16 ); ?>
						</button>
						<div class="cb-faq__a"><p><?php echo esc_html( $faq['a'] ); ?></p></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-cta cb-reveal">
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Stop stitching tools together. Start closing leads.', 'chatbotistic' ); ?></span></h2>
				<div class="cb-cta__actions">
					<?php
					cb_button( __( 'Start free', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
					cb_button( __( 'Book a demo', 'chatbotistic' ), home_url( '/demo/' ), 'ghost', array( 'size' => 'lg' ) );
					?>
				</div>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
