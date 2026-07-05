<?php
/**
 * Template Name: Pricing (V4)
 *
 * Pricing page port from V4 features-pricing.jsx. Five plans (Free Forever /
 * Starter / Growth / Agency / Lifetime — the last one legacy/contact-only),
 * monthly + annual toggle (vanilla JS), feature-by-feature compare, plan
 * recommendation, FAQ, CTA.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Build plan CTA links from the Memberistic helpers so every button lands on a
// real checkout / inquiry URL (no 404). Paid plans go to the mapped checkout
// page as /checkout/?memberistic_plan=<slug>; the Free plan reuses the
// dedicated free-onboarding checkout URL; Lifetime is legacy/contact-only and
// routes to the LTD inquiry flow instead of Stripe checkout.
$cb_free_url = function_exists( 'cb_free_checkout_url' )
	? cb_free_checkout_url()
	: home_url( '/checkout/?memberistic_plan=free' );
$cb_starter_url = function_exists( 'cb_memberistic_checkout_url' )
	? cb_memberistic_checkout_url( 'starter' )
	: home_url( '/checkout/?memberistic_plan=starter' );
$cb_growth_url = function_exists( 'cb_memberistic_checkout_url' )
	? cb_memberistic_checkout_url( 'growth' )
	: home_url( '/checkout/?memberistic_plan=growth' );
$cb_ag_url = function_exists( 'cb_memberistic_checkout_url' )
	? cb_memberistic_checkout_url( 'agency' )
	: home_url( '/checkout/?memberistic_plan=agency' );
$cb_ltd_url = function_exists( 'cb_ltd_inquiry_url' )
	? cb_ltd_inquiry_url( 'lifetime' )
	: home_url( '/contact/?plan=lifetime' );

$cb_plans = array(
	array(
		'name' => __( 'Free Forever', 'chatbotistic' ), 'desc' => __( 'Full access to the Chatbotistic Dashboard — no credit card.', 'chatbotistic' ),
		'monthly' => '$0', 'annual' => '$0', 'sub' => '/mo',
		'note_m' => __( 'free forever', 'chatbotistic' ), 'note_a' => __( 'free forever', 'chatbotistic' ), 'save' => '',
		'feats' => array( __( '1 AI ChatBot Widget', 'chatbotistic' ), __( '1 WhatsApp Agent', 'chatbotistic' ), __( '1 Website Domain', 'chatbotistic' ), __( '100 campaign messages / mo', 'chatbotistic' ), __( 'Leads captured to your personal WhatsApp', 'chatbotistic' ), __( 'Includes Chatbotistic branding', 'chatbotistic' ) ),
		'cta' => __( 'Get started free', 'chatbotistic' ), 'href' => $cb_free_url, 'flavor' => '',
	),
	array(
		'name' => __( 'Starter', 'chatbotistic' ), 'desc' => __( 'Full access to the Chatbotistic Dashboard for small teams.', 'chatbotistic' ),
		'monthly' => '$19', 'annual' => '$15.83', 'sub' => '/mo',
		'note_m' => __( 'billed monthly', 'chatbotistic' ), 'note_a' => __( '$190 billed yearly', 'chatbotistic' ), 'save' => __( 'Save $38', 'chatbotistic' ),
		'feats' => array( __( '3 AI ChatBot Widgets', 'chatbotistic' ), __( '5 WhatsApp Agents', 'chatbotistic' ), __( '3 Website Domains', 'chatbotistic' ), __( '2 Team Seats', 'chatbotistic' ), __( '1,000 campaign messages / mo', 'chatbotistic' ), __( 'Booking forms', 'chatbotistic' ), __( 'Shared team inbox', 'chatbotistic' ) ),
		'cta' => __( 'Choose Starter', 'chatbotistic' ), 'href' => $cb_starter_url, 'flavor' => '',
	),
	array(
		'name' => __( 'Growth', 'chatbotistic' ), 'desc' => __( 'Full access to the Chatbotistic Dashboard, built to scale.', 'chatbotistic' ),
		'monthly' => '$49', 'annual' => '$40.83', 'sub' => '/mo',
		'note_m' => __( 'billed monthly', 'chatbotistic' ), 'note_a' => __( '$490 billed yearly', 'chatbotistic' ), 'save' => __( 'Save $98', 'chatbotistic' ),
		'feats' => array( __( '10 AI ChatBot Widgets', 'chatbotistic' ), __( '20 WhatsApp Agents', 'chatbotistic' ), __( '10 Website Domains', 'chatbotistic' ), __( '5 Team Seats', 'chatbotistic' ), __( '5,000 campaign messages / mo', 'chatbotistic' ), __( 'Landing page editor', 'chatbotistic' ), __( 'AI replies', 'chatbotistic' ), __( 'Chatbotistic branding removed', 'chatbotistic' ), __( 'Ad attribution & ROAS dashboard', 'chatbotistic' ) ),
		'cta' => __( 'Choose Growth', 'chatbotistic' ), 'href' => $cb_growth_url, 'flavor' => 'featured',
	),
	array(
		'name' => __( 'Agency', 'chatbotistic' ), 'desc' => __( 'Full access to the Chatbotistic Dashboard, white-labeled for your agency.', 'chatbotistic' ),
		'monthly' => '$149', 'annual' => '$124.17', 'sub' => '/mo',
		'note_m' => __( 'billed monthly', 'chatbotistic' ), 'note_a' => __( '$1,490 billed yearly', 'chatbotistic' ), 'save' => __( 'Save $298', 'chatbotistic' ),
		'feats' => array( __( '30 AI ChatBot Widgets', 'chatbotistic' ), __( 'Unlimited WhatsApp Agents', 'chatbotistic' ), __( '50 Website Domains', 'chatbotistic' ), __( '15 Team Seats', 'chatbotistic' ), __( '25,000 campaign messages / mo', 'chatbotistic' ), __( 'Full white-label + custom domain', 'chatbotistic' ), __( '10 client sub-accounts', 'chatbotistic' ), __( 'Branded client reports', 'chatbotistic' ) ),
		'cta' => __( 'Choose Agency', 'chatbotistic' ), 'href' => $cb_ag_url, 'flavor' => '',
	),
	array(
		'name' => __( 'Lifetime — Contact Us', 'chatbotistic' ), 'desc' => __( 'Legacy plan, limited availability. Full access to the Chatbotistic Dashboard, custom-priced.', 'chatbotistic' ),
		'monthly' => '✳✳✳', 'annual' => '✳✳✳', 'sub' => '/custom',
		'note_m' => __( 'Contact for pricing', 'chatbotistic' ), 'note_a' => __( 'Contact for pricing', 'chatbotistic' ), 'save' => '',
		'feats' => array( __( 'Legacy plan — limited availability', 'chatbotistic' ), __( 'Everything in Agency', 'chatbotistic' ), __( 'Unlimited Widgets / Agents / Domains', 'chatbotistic' ), __( 'White-label with custom domain', 'chatbotistic' ), __( 'Founder-direct support channel', 'chatbotistic' ), __( 'Custom contract & invoicing', 'chatbotistic' ) ),
		'cta' => __( 'Contact us', 'chatbotistic' ), 'href' => $cb_ltd_url, 'flavor' => 'lifetime',
	),
);

$cb_compare_rows = array(
	array( 'AI ChatBot Widgets', '1', '3', '10', '30' ),
	array( 'WhatsApp Agents',     '1', '5', '20', __( 'Unlimited', 'chatbotistic' ) ),
	array( 'Website Domains',     '1', '3', '10',  '50' ),
	array( __( 'Team seats', 'chatbotistic' ),           '1', '2', '5', '15' ),
	array( __( 'Campaign messages / mo', 'chatbotistic' ), '100', '1,000', '5,000', '25,000' ),
	array( __( 'Lead capture', 'chatbotistic' ), __( 'Personal WhatsApp', 'chatbotistic' ), '✓', '✓', '✓' ),
	array( __( 'Booking forms', 'chatbotistic' ), '—', '✓', '✓', '✓' ),
	array( __( 'Shared team inbox', 'chatbotistic' ),    '—', '✓', '✓', '✓' ),
	array( __( 'Landing page editor', 'chatbotistic' ), '—', '—', '✓', '✓' ),
	array( __( 'AI replies', 'chatbotistic' ),           '—', '—', '✓', '✓' ),
	array( __( 'Ad attribution & ROAS dashboard', 'chatbotistic' ), '—', '—', '✓', '✓' ),
	array( __( 'Full white-label + custom domain', 'chatbotistic' ), '—', '—', '—', '✓' ),
	array( __( 'Client sub-accounts', 'chatbotistic' ),  '—', '—', '—', '10' ),
	array( __( 'Branded client reports', 'chatbotistic' ), '—', '—', '—', '✓' ),
	array( __( 'Branding', 'chatbotistic' ),             __( 'Chatbotistic shown', 'chatbotistic' ), __( 'Chatbotistic shown', 'chatbotistic' ), __( 'Removed', 'chatbotistic' ), __( 'Custom', 'chatbotistic' ) ),
);

$cb_recs = array(
	array( __( 'Just getting started', 'chatbotistic' ), __( 'Free Forever', 'chatbotistic' ), __( 'Solo founders and small sites testing conversational lead capture on one domain.', 'chatbotistic' ), __( 'Get started free', 'chatbotistic' ), $cb_free_url, false, 'rocket' ),
	array( __( 'Growing & running ads', 'chatbotistic' ), __( 'Growth', 'chatbotistic' ), __( 'Teams that need AI replies, a landing page editor, and an ad attribution / ROAS dashboard.', 'chatbotistic' ), __( 'Choose Growth', 'chatbotistic' ), $cb_growth_url, true, 'bolt' ),
	array( __( 'Agencies & resellers', 'chatbotistic' ), __( 'Agency', 'chatbotistic' ), __( 'Full white-label with a custom domain, client sub-accounts, and branded client reports.', 'chatbotistic' ), __( 'Choose Agency', 'chatbotistic' ), $cb_ag_url, false, 'users' ),
);

$cb_faqs = array(
	array( __( 'Can I use Chatbotistic on WordPress?', 'chatbotistic' ), __( 'Yes — install our native WordPress plugin (included on Starter and above), paste your account key, and manage every widget directly from your WP dashboard.', 'chatbotistic' ) ),
	array( __( 'Can I connect WhatsApp?', 'chatbotistic' ), __( 'Yes. WhatsApp Agents are included on every plan, from a single agent on Free Forever up to unlimited on Agency.', 'chatbotistic' ) ),
	array( __( 'Can I capture leads?', 'chatbotistic' ), __( 'Every widget — chat, WhatsApp, forms, landing pages — captures leads automatically. On Free Forever, leads are also sent straight to your personal WhatsApp.', 'chatbotistic' ) ),
	array( __( 'Does it support bookings?', 'chatbotistic' ), __( 'Yes. Booking forms are included from Starter and up, with a full landing page editor and AI replies on Growth and Agency.', 'chatbotistic' ) ),
	array( __( 'What are campaign messages?', 'chatbotistic' ), __( 'Every outbound WhatsApp campaign send counts against your plan’s monthly message allowance — 100/mo on Free Forever, scaling up to 25,000/mo on Agency.', 'chatbotistic' ) ),
	array( __( 'Can agencies use it?', 'chatbotistic' ), __( 'Absolutely — the Agency plan adds full white-label with a custom domain, 10 client sub-accounts, and branded client reports.', 'chatbotistic' ) ),
	array( __( 'Is white label available?', 'chatbotistic' ), __( 'Chatbotistic branding is removed from Growth up. Full white-label with your own domain is available on Agency (and the legacy Lifetime plan).', 'chatbotistic' ) ),
	array( __( 'Can I use it for multiple websites?', 'chatbotistic' ), __( 'Free Forever covers 1 domain, Starter covers 3, Growth covers 10, and Agency covers 50.', 'chatbotistic' ) ),
	array( __( 'What happened to the Lifetime plan?', 'chatbotistic' ), __( 'Lifetime is a legacy plan with limited availability — it is no longer sold on the pricing grid. Existing lifetime members keep their plan; email hello@chatbotistic.com for custom pricing.', 'chatbotistic' ) ),
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
			<p class="lead" style="margin:18px auto 0;"><?php esc_html_e( 'Start free forever. Upgrade when you’re ready — every plan includes full access to the Chatbotistic Dashboard.', 'chatbotistic' ); ?></p>
			<div class="toggle-wrap" style="margin-top:36px;" role="group" aria-label="<?php esc_attr_e( 'Billing cycle', 'chatbotistic' ); ?>">
				<button class="active" type="button" data-pricing-mode="monthly" aria-pressed="true"><?php esc_html_e( 'Monthly', 'chatbotistic' ); ?></button>
				<button type="button" data-pricing-mode="annual" aria-pressed="false"><?php esc_html_e( 'Annual', 'chatbotistic' ); ?> <span class="save-pill" style="margin-left:6px;"><?php esc_html_e( 'Save 20%', 'chatbotistic' ); ?></span></button>
			</div>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<div class="pricing-grid" style="grid-template-columns:repeat(5,1fr);">
				<?php foreach ( $cb_plans as $cb_p ) : ?>
					<div class="price-card<?php echo $cb_p['flavor'] ? ' ' . esc_attr( $cb_p['flavor'] ) : ''; ?>" style="padding:24px;">
						<?php if ( 'featured' === $cb_p['flavor'] ) : ?>
							<div class="best-badge"><?php esc_html_e( 'Most popular', 'chatbotistic' ); ?></div>
						<?php elseif ( 'lifetime' === $cb_p['flavor'] ) : ?>
							<div class="lifetime-badge"><?php esc_html_e( '★ Legacy · Limited availability', 'chatbotistic' ); ?></div>
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
					<span><?php esc_html_e( 'Free Forever', 'chatbotistic' ); ?></span>
					<span><?php esc_html_e( 'Starter', 'chatbotistic' ); ?></span>
					<span><?php esc_html_e( 'Growth', 'chatbotistic' ); ?></span>
					<span><?php esc_html_e( 'Agency', 'chatbotistic' ); ?></span>
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
