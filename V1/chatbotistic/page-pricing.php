<?php
/**
 * Template Name: Pricing (V4)
 *
 * Pricing page port from V4 features-pricing.jsx. Four plans (Free / Pro
 * / Agency / Lifetime), monthly + annual toggle (vanilla JS), feature-by-
 * feature compare, plan recommendation, FAQ, CTA.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Build plan CTA links from the Memberistic helpers so every button lands on a
// real checkout / inquiry URL (no 404). Paid plans go to the mapped checkout
// page as /checkout/?memberistic_plan=<slug>; the Free plan reuses the
// dedicated free-onboarding checkout URL; Lifetime is contact-only and routes
// to the LTD inquiry flow.
$cb_free_url = function_exists( 'cb_free_checkout_url' )
	? cb_free_checkout_url()
	: home_url( '/checkout/?memberistic_plan=free' );
$cb_pro_url = function_exists( 'cb_memberistic_checkout_url' )
	? cb_memberistic_checkout_url( 'pro' )
	: home_url( '/checkout/?memberistic_plan=pro' );
$cb_ag_url = function_exists( 'cb_memberistic_checkout_url' )
	? cb_memberistic_checkout_url( 'agency' )
	: home_url( '/checkout/?memberistic_plan=agency' );
$cb_ltd_url = function_exists( 'cb_ltd_inquiry_url' )
	? cb_ltd_inquiry_url( 'lifetime' )
	: home_url( '/contact/?plan=lifetime' );

$cb_plans = array(
	array(
		'name' => __( 'Free Forever', 'chatbotistic' ), 'desc' => __( 'Try the platform, no credit card.', 'chatbotistic' ),
		'monthly' => '$0', 'annual' => '$0', 'sub' => '/mo',
		'note_m' => __( 'free forever', 'chatbotistic' ), 'note_a' => __( 'free forever', 'chatbotistic' ), 'save' => '',
		'feats' => array( __( '1 AI ChatBot Widget', 'chatbotistic' ), __( '1 WhatsApp Agent', 'chatbotistic' ), __( '1 Website Domain', 'chatbotistic' ), __( 'Email notifications', 'chatbotistic' ), __( 'Includes Chatbotistic branding', 'chatbotistic' ) ),
		'cta' => __( 'Get started free', 'chatbotistic' ), 'href' => $cb_free_url, 'flavor' => '',
	),
	array(
		'name' => __( 'Pro', 'chatbotistic' ), 'desc' => __( 'For growing businesses.', 'chatbotistic' ),
		'monthly' => '$9', 'annual' => '$7.50', 'sub' => '/mo',
		'note_m' => __( 'billed monthly', 'chatbotistic' ), 'note_a' => __( '$90 billed yearly', 'chatbotistic' ), 'save' => __( 'Save $18', 'chatbotistic' ),
		'feats' => array( __( '5 AI ChatBot Widgets', 'chatbotistic' ), __( '15 WhatsApp Agents', 'chatbotistic' ), __( '10 Website Domains', 'chatbotistic' ), __( 'Custom landing page per widget', 'chatbotistic' ), __( 'Chat Forms, CRM integrations, Webhooks', 'chatbotistic' ), __( 'WordPress widget plugin', 'chatbotistic' ) ),
		'cta' => __( 'Choose Pro', 'chatbotistic' ), 'href' => $cb_pro_url, 'flavor' => 'featured',
	),
	array(
		'name' => __( 'Agency', 'chatbotistic' ), 'desc' => __( 'White-label for agencies & teams.', 'chatbotistic' ),
		'monthly' => '$99', 'annual' => '$82.50', 'sub' => '/mo',
		'note_m' => __( 'billed monthly', 'chatbotistic' ), 'note_a' => __( '$990 billed yearly', 'chatbotistic' ), 'save' => __( 'Save $198', 'chatbotistic' ),
		'feats' => array( __( '30 AI ChatBot Widgets', 'chatbotistic' ), __( '100 WhatsApp Agents', 'chatbotistic' ), __( '50 Website Domains', 'chatbotistic' ), __( 'White-label dashboard & widgets', 'chatbotistic' ), __( 'WhatsApp priority support', 'chatbotistic' ), __( 'API & Webhooks (HubSpot, Zoho)', 'chatbotistic' ), __( 'Stripe integration for payments', 'chatbotistic' ), __( 'Team agents on your account', 'chatbotistic' ), __( 'Custom landing pages', 'chatbotistic' ) ),
		'cta' => __( 'Choose Agency', 'chatbotistic' ), 'href' => $cb_ag_url, 'flavor' => '',
	),
	array(
		'name' => __( 'Lifetime', 'chatbotistic' ), 'desc' => __( 'Pay once. Own it forever.', 'chatbotistic' ),
		'monthly' => '✳✳✳', 'annual' => '✳✳✳', 'sub' => '/one-time',
		'note_m' => __( 'Contact for LTD Pricing', 'chatbotistic' ), 'note_a' => __( 'Contact for LTD Pricing', 'chatbotistic' ), 'save' => '',
		'feats' => array( __( 'Everything in Agency', 'chatbotistic' ), __( 'Unlimited Widgets / Agents / Domains', 'chatbotistic' ), __( 'White-label with custom domain', 'chatbotistic' ), __( 'Lifetime updates', 'chatbotistic' ), __( 'Priority roadmap input', 'chatbotistic' ), __( 'Founder-direct support channel', 'chatbotistic' ), __( 'Custom contract & invoicing', 'chatbotistic' ) ),
		'cta' => __( 'Contact for LTD Pricing', 'chatbotistic' ), 'href' => $cb_ltd_url, 'flavor' => 'lifetime',
	),
);

$cb_compare_rows = array(
	array( 'AI ChatBot Widgets', '1', '5', '30', __( 'Unlimited', 'chatbotistic' ) ),
	array( 'WhatsApp Agents',     '1', '15', '100', __( 'Unlimited', 'chatbotistic' ) ),
	array( 'Website Domains',     '1', '10', '50',  __( 'Unlimited', 'chatbotistic' ) ),
	array( __( 'Email notifications', 'chatbotistic' ), '✓', '✓', '✓', '✓' ),
	array( __( 'Custom landing pages', 'chatbotistic' ), '—', __( 'Per widget', 'chatbotistic' ), '✓', '✓' ),
	array( __( 'Chat Forms', 'chatbotistic' ),           '—', '✓', '✓', '✓' ),
	array( __( 'CRM integrations', 'chatbotistic' ),     '—', '✓', '✓', '✓' ),
	array( __( 'Webhook connections', 'chatbotistic' ),  '—', '✓', '✓', '✓' ),
	array( __( 'WordPress plugin', 'chatbotistic' ),     '—', '✓', '✓', '✓' ),
	array( __( 'White-label dashboard', 'chatbotistic' ), '—', '—', '✓', '✓' ),
	array( __( 'WhatsApp priority support', 'chatbotistic' ), '—', '—', '✓', '✓' ),
	array( __( 'API access (HubSpot, Zoho)', 'chatbotistic' ), '—', '—', '✓', '✓' ),
	array( __( 'Stripe payments', 'chatbotistic' ),      '—', '—', '✓', '✓' ),
	array( __( 'Team agents', 'chatbotistic' ),          '—', '—', '✓', '✓' ),
	array( __( 'Custom-domain white-label', 'chatbotistic' ), '—', '—', '—', '✓' ),
	array( __( 'Lifetime updates', 'chatbotistic' ),     '—', '—', '—', '✓' ),
	array( __( 'Founder-direct support', 'chatbotistic' ), '—', '—', '—', '✓' ),
	array( __( 'Branding', 'chatbotistic' ),             __( 'Chatbotistic', 'chatbotistic' ), __( 'Chatbotistic', 'chatbotistic' ), __( 'Removable', 'chatbotistic' ), __( 'Custom', 'chatbotistic' ) ),
);

$cb_recs = array(
	array( __( 'Just getting started', 'chatbotistic' ), __( 'Free Forever', 'chatbotistic' ), __( 'Solo founders and small sites testing conversational lead capture on one domain.', 'chatbotistic' ), __( 'Get started free', 'chatbotistic' ), $cb_free_url, false, 'rocket' ),
	array( __( 'Growing business', 'chatbotistic' ), __( 'Pro', 'chatbotistic' ), __( 'Teams that need multiple widgets, WhatsApp agents, CRM sync, and the WordPress plugin.', 'chatbotistic' ), __( 'Choose Pro', 'chatbotistic' ), $cb_pro_url, true, 'bolt' ),
	array( __( 'Agencies & resellers', 'chatbotistic' ), __( 'Agency / Lifetime', 'chatbotistic' ), __( 'White-label the dashboard and widgets, manage clients, and resell under your own brand.', 'chatbotistic' ), __( 'Talk to us', 'chatbotistic' ), home_url( '/book-demo/' ), false, 'users' ),
);

$cb_faqs = array(
	array( __( 'Can I use Chatbotistic on WordPress?', 'chatbotistic' ), __( 'Yes — install our native WordPress plugin (included on Pro and above), paste your account key, and manage every widget directly from your WP dashboard.', 'chatbotistic' ) ),
	array( __( 'Can I connect WhatsApp?', 'chatbotistic' ), __( 'Yes. WhatsApp Agents are included on every plan, with priority support on Agency and Lifetime.', 'chatbotistic' ) ),
	array( __( 'Can I capture leads?', 'chatbotistic' ), __( 'Every widget — chat, WhatsApp, forms, landing pages — captures leads automatically into your inbox.', 'chatbotistic' ) ),
	array( __( 'Does it support bookings?', 'chatbotistic' ), __( 'Yes. Booking flows ship inside the chat widget and landing pages, with calendar sync and Stripe deposits on Agency.', 'chatbotistic' ) ),
	array( __( 'Can agencies use it?', 'chatbotistic' ), __( 'Absolutely — the Agency plan adds unlimited widgets, white-label dashboards, team agents, and API access.', 'chatbotistic' ) ),
	array( __( 'Is white label available?', 'chatbotistic' ), __( 'Yes, on Agency and Lifetime. Your logo, your domain, your customer login.', 'chatbotistic' ) ),
	array( __( 'Can I connect payment gateways?', 'chatbotistic' ), __( 'Stripe is native on Agency. Take deposits, full payments, or subscriptions inside the chat flow.', 'chatbotistic' ) ),
	array( __( 'Can I use it for multiple websites?', 'chatbotistic' ), __( 'Free covers 1 domain, Pro covers 10, Agency covers 50, and Lifetime is unlimited.', 'chatbotistic' ) ),
	array( __( 'How does the Lifetime deal work?', 'chatbotistic' ), __( 'Pay once, own it forever — limited to the first 50 founders. Includes everything in Agency plus lifetime updates and founder-direct support. Email hello@chatbotistic.com to claim a seat.', 'chatbotistic' ) ),
);

// Emit FAQPage JSON-LD so Google + AI engines render this page's FAQs as
// rich answers in the SERP / answer cards.
if ( function_exists( 'cb_add_faq_schema' ) ) {
	cb_add_faq_schema( array_map(
		static fn ( $q ) => array( 'question' => $q[0], 'answer' => $q[1] ),
		$cb_faqs
	) );
}
?>

<main class="page-fade" id="cb-pricing-page">

	<section class="section section-tight" style="padding-bottom:0;">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Pricing', 'chatbotistic' ); ?></span>
			<h1 class="h-1 text-grad" style="margin:18px auto 0;max-width:760px;"><?php esc_html_e( 'Honest pricing. Outrageous value.', 'chatbotistic' ); ?></h1>
			<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'Start free forever. Upgrade when you’re ready. Lifetime deals for the first 50 founders.', 'chatbotistic' ); ?></p>
			<div class="toggle-wrap" style="margin-top:36px;" role="group" aria-label="<?php esc_attr_e( 'Billing cycle', 'chatbotistic' ); ?>">
				<button class="active" type="button" data-pricing-mode="monthly" aria-pressed="true"><?php esc_html_e( 'Monthly', 'chatbotistic' ); ?></button>
				<button type="button" data-pricing-mode="annual" aria-pressed="false"><?php esc_html_e( 'Annual', 'chatbotistic' ); ?> <span class="save-pill" style="margin-left:6px;"><?php esc_html_e( 'Save 20%', 'chatbotistic' ); ?></span></button>
			</div>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<div class="pricing-grid" style="grid-template-columns:repeat(4,1fr);">
				<?php foreach ( $cb_plans as $cb_p ) : ?>
					<div class="price-card<?php echo $cb_p['flavor'] ? ' ' . esc_attr( $cb_p['flavor'] ) : ''; ?>" style="padding:24px;">
						<?php if ( 'featured' === $cb_p['flavor'] ) : ?>
							<div class="best-badge"><?php esc_html_e( 'Most popular', 'chatbotistic' ); ?></div>
						<?php elseif ( 'lifetime' === $cb_p['flavor'] ) : ?>
							<div class="lifetime-badge"><?php esc_html_e( '★ Limited · 50 seats', 'chatbotistic' ); ?></div>
						<?php endif; ?>
						<div class="plan-name"><?php echo esc_html( $cb_p['name'] ); ?></div>
						<div class="plan-desc" style="min-height:42px;"><?php echo esc_html( $cb_p['desc'] ); ?></div>
						<div class="price-amount" style="font-size:36px;" data-monthly="<?php echo esc_attr( $cb_p['monthly'] ); ?>" data-annual="<?php echo esc_attr( $cb_p['annual'] ); ?>">
							<span class="price-value"><?php echo esc_html( $cb_p['monthly'] ); ?></span><sub style="font-size:12px;"><?php echo esc_html( $cb_p['sub'] ); ?></sub>
						</div>
						<div style="display:flex;align-items:center;gap:8px;margin-top:8px;min-height:24px;flex-wrap:wrap;">
							<span class="price-note" style="font-size:12.5px;color:var(--text-dim);" data-monthly="<?php echo esc_attr( $cb_p['note_m'] ); ?>" data-annual="<?php echo esc_attr( $cb_p['note_a'] ); ?>"><?php echo esc_html( $cb_p['note_m'] ); ?></span>
							<?php if ( $cb_p['save'] ) : ?>
								<span class="save-pill price-save" hidden><?php echo esc_html( $cb_p['save'] ); ?></span>
							<?php endif; ?>
						</div>
						<ul class="price-feats">
							<?php foreach ( $cb_p['feats'] as $cb_f ) : ?>
								<li><span class="check">✓</span><?php echo esc_html( $cb_f ); ?></li>
							<?php endforeach; ?>
						</ul>
						<a class="btn <?php echo 'featured' === $cb_p['flavor'] ? 'btn-primary' : ( 'lifetime' === $cb_p['flavor'] ? 'btn-primary btn-lifetime' : 'btn-ghost' ); ?>" href="<?php echo esc_url( $cb_p['href'] ); ?>" style="justify-content:center;"><?php echo esc_html( $cb_p['cta'] ); ?></a>
					</div>
				<?php endforeach; ?>
			</div>
			<p style="text-align:center;color:var(--text-dim);font-size:12.5px;margin-top:24px;"><?php esc_html_e( 'All plans include SSL, GDPR-ready data handling, and core integrations. Prices in USD, excluding local taxes.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<h2 class="h-2 text-grad" style="text-align:center;"><?php esc_html_e( 'Every plan, feature by feature', 'chatbotistic' ); ?></h2>
			<div class="compare-table" style="grid-template-columns:1.6fr repeat(4,1fr);margin-top:40px;">
				<div class="compare-row head" style="grid-template-columns:1.6fr repeat(4,1fr);">
					<span><?php esc_html_e( 'Feature', 'chatbotistic' ); ?></span>
					<span><?php esc_html_e( 'Free', 'chatbotistic' ); ?></span>
					<span><?php esc_html_e( 'Pro', 'chatbotistic' ); ?></span>
					<span><?php esc_html_e( 'Agency', 'chatbotistic' ); ?></span>
					<span><?php esc_html_e( 'Lifetime', 'chatbotistic' ); ?></span>
				</div>
				<?php foreach ( $cb_compare_rows as $cb_r ) : ?>
					<div class="compare-row" style="grid-template-columns:1.6fr repeat(4,1fr);">
						<span class="col-feat"><?php echo esc_html( $cb_r[0] ); ?></span>
						<?php for ( $cb_ci = 1; $cb_ci <= 4; $cb_ci++ ) :
							$cb_val = $cb_r[ $cb_ci ];
							$cb_cls = '✓' === $cb_val ? 'yes' : ( '—' === $cb_val ? 'no' : '' ); ?>
							<span class="<?php echo esc_attr( $cb_cls ); ?>"><?php echo esc_html( $cb_val ); ?></span>
						<?php endfor; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<div style="text-align:center;max-width:680px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Not sure?', 'chatbotistic' ); ?></span>
				<h2 class="h-2 text-grad" style="margin-top:16px;"><?php esc_html_e( 'Pick the plan that fits where you are.', 'chatbotistic' ); ?></h2>
			</div>
			<div class="rec-grid">
				<?php foreach ( $cb_recs as $cb_r ) : ?>
					<div class="rec-card"<?php echo $cb_r[5] ? ' style="border-color:rgba(160,112,255,0.35);background:linear-gradient(180deg,rgba(160,112,255,0.08),rgba(79,139,255,0.03));"' : ''; ?>>
						<div class="ico"><?php cb_icon( isset( $cb_r[6] ) ? $cb_r[6] : 'bolt', 20 ); ?></div>
						<div class="pick"><?php esc_html_e( 'Recommended ·', 'chatbotistic' ); ?> <b><?php echo esc_html( $cb_r[1] ); ?></b></div>
						<h3><?php echo esc_html( $cb_r[0] ); ?></h3>
						<p><?php echo esc_html( $cb_r[2] ); ?></p>
						<a class="btn <?php echo $cb_r[5] ? 'btn-primary' : 'btn-ghost'; ?> btn-sm" href="<?php echo esc_url( $cb_r[4] ); ?>" style="justify-content:center;"><?php echo esc_html( $cb_r[3] ); ?></a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div style="text-align:center;max-width:680px;margin:0 auto;">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></span>
				<h2 class="h-1 text-grad" style="margin-top:18px;"><?php esc_html_e( 'Pricing questions, answered.', 'chatbotistic' ); ?></h2>
			</div>
			<div class="faq-list" style="max-width:760px;margin:40px auto 0;">
				<?php foreach ( $cb_faqs as $cb_q ) : ?>
					<details class="faq-item">
						<summary><?php echo esc_html( $cb_q[0] ); ?></summary>
						<p><?php echo esc_html( $cb_q[1] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="big-cta">
				<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Ready when you are', 'chatbotistic' ); ?></span>
				<h2 class="text-grad" style="margin-top:18px;"><?php esc_html_e( 'Start free today. Upgrade when you grow.', 'chatbotistic' ); ?></h2>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( $cb_free_url ); ?>"><?php esc_html_e( 'Start free', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</section>

</main>

<script>
(function () {
	var page = document.getElementById('cb-pricing-page');
	if ( ! page ) return;
	var btns = page.querySelectorAll('[data-pricing-mode]');
	function setMode(mode) {
		btns.forEach(function (b) {
			var on = b.getAttribute('data-pricing-mode') === mode;
			b.classList.toggle('active', on);
			b.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		page.querySelectorAll('.price-amount').forEach(function (el) {
			var v  = el.getAttribute('data-' + mode);
			var pv = el.querySelector('.price-value');
			if ( v && pv ) pv.textContent = v;
		});
		page.querySelectorAll('.price-note').forEach(function (el) {
			var v = el.getAttribute('data-' + mode);
			if ( v ) el.textContent = v;
		});
		page.querySelectorAll('.price-save').forEach(function (el) {
			el.hidden = ( mode !== 'annual' );
		});
	}
	btns.forEach(function (b) {
		b.addEventListener('click', function () { setMode(b.getAttribute('data-pricing-mode')); });
	});
})();
</script>

<?php
get_footer();
