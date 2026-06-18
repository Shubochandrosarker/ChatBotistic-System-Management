<?php
/**
 * Template Name: Checkout (V4)
 *
 * Branded Chatbotistic checkout. Plan selection arrives via
 * ?plan=pro|agency + ?cycle=monthly|annual. The form posts to
 * admin-post.php?action=cb_checkout (Memberistic wires the actual payment
 * processing — this template collects billing details and shows a branded
 * order summary).
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

$cb_plan_slug  = isset( $_GET['plan'] ) ? sanitize_key( wp_unslash( $_GET['plan'] ) ) : 'pro'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$cb_annual_get = isset( $_GET['cycle'] ) && 'monthly' !== $_GET['cycle']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$cb_plan_meta = array(
	'pro'    => array( 'name' => __( 'Chatbotistic Pro', 'chatbotistic' ),    'monthly' => '9.00',  'annual' => '90.00',  'feats' => array( __( '5 AI ChatBot Widgets', 'chatbotistic' ), __( '15 WhatsApp Agents', 'chatbotistic' ), __( '10 Website Domains', 'chatbotistic' ), __( 'Chat Forms, CRM, Webhooks', 'chatbotistic' ), __( 'WordPress widget plugin', 'chatbotistic' ) ) ),
	'agency' => array( 'name' => __( 'Chatbotistic Agency', 'chatbotistic' ), 'monthly' => '99.00', 'annual' => '990.00', 'feats' => array( __( '30 AI ChatBot Widgets', 'chatbotistic' ), __( '100 WhatsApp Agents', 'chatbotistic' ), __( '50 Website Domains', 'chatbotistic' ), __( 'White-label dashboard & widgets', 'chatbotistic' ), __( 'Team agents on your account', 'chatbotistic' ) ) ),
);
$cb_meta    = $cb_plan_meta[ $cb_plan_slug ] ?? $cb_plan_meta['pro'];
$cb_display = $cb_annual_get ? number_format( (float) $cb_meta['annual'] / 12, 2 ) : $cb_meta['monthly'];
$cb_total   = $cb_annual_get ? $cb_meta['annual'] : $cb_meta['monthly'];
?>

<div class="cbk-top">
	<?php echo cb_logo(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
	<span class="cbk-secure">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
		<?php esc_html_e( 'Secure checkout', 'chatbotistic' ); ?>
	</span>
</div>

<main class="cbk" id="cb-checkout-page">
	<div class="cbk-wrap">

		<div class="cbk-main">
			<a class="cbk-back" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
				<?php esc_html_e( 'Back to pricing', 'chatbotistic' ); ?>
			</a>
			<h1><?php esc_html_e( 'Complete your upgrade', 'chatbotistic' ); ?></h1>
			<p class="cbk-sub">
				<?php printf( wp_kses( __( 'You’re upgrading to %s. Cancel anytime.', 'chatbotistic' ), array( 'b' => array() ) ), '<b>' . esc_html( $cb_meta['name'] ) . '</b>' ); ?>
			</p>

			<form class="cb-form cbk-card" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="cb_checkout">
				<?php wp_nonce_field( 'cb_checkout', '_wpnonce' ); ?>
				<input type="hidden" name="plan" value="<?php echo esc_attr( $cb_plan_slug ); ?>">
				<input type="hidden" name="cycle" id="cb-checkout-cycle" value="<?php echo $cb_annual_get ? 'annual' : 'monthly'; ?>">

				<div class="cbk-seclabel"><?php esc_html_e( 'Billing details', 'chatbotistic' ); ?></div>
				<div class="grid-2">
					<div class="field"><label><?php esc_html_e( 'Full name', 'chatbotistic' ); ?></label><input name="full_name" placeholder="Sarah Kim" required autocomplete="name"></div>
					<div class="field"><label><?php esc_html_e( 'Company', 'chatbotistic' ); ?></label><input name="company" placeholder="Acme Inc." autocomplete="organization"></div>
				</div>
				<div class="field"><label><?php esc_html_e( 'Billing email', 'chatbotistic' ); ?></label><input type="email" name="billing_email" placeholder="billing@company.com" required autocomplete="email"></div>
				<div class="grid-2">
					<div class="field"><label><?php esc_html_e( 'Country', 'chatbotistic' ); ?></label>
						<select name="country" autocomplete="country-name">
							<option>United States</option><option>United Kingdom</option><option>Germany</option><option>India</option><option>Australia</option><option>Canada</option><option>Other</option>
						</select>
					</div>
					<div class="field"><label><?php esc_html_e( 'VAT / Tax ID (optional)', 'chatbotistic' ); ?></label><input name="tax_id" placeholder="—"></div>
				</div>

				<div class="cbk-seclabel"><?php esc_html_e( 'Payment', 'chatbotistic' ); ?></div>
				<div class="field">
					<label><?php esc_html_e( 'Card number', 'chatbotistic' ); ?></label>
					<div class="cbk-card-input">
						<input name="card" placeholder="4242 4242 4242 4242" required autocomplete="cc-number" inputmode="numeric">
						<span class="cbk-cardbrands" aria-hidden="true">
							<span class="visa">VISA</span><span class="mc">MC</span><span class="amex">AMEX</span>
						</span>
					</div>
				</div>
				<div class="grid-2">
					<div class="field"><label><?php esc_html_e( 'Expiry', 'chatbotistic' ); ?></label><input name="exp" placeholder="MM / YY" required autocomplete="cc-exp" inputmode="numeric"></div>
					<div class="field"><label><?php esc_html_e( 'CVC', 'chatbotistic' ); ?></label><input name="cvc" placeholder="123" required autocomplete="cc-csc" inputmode="numeric"></div>
				</div>

				<button class="btn btn-primary btn-lg cbk-pay" type="submit" id="cb-checkout-submit">
					<?php esc_html_e( 'Pay', 'chatbotistic' ); ?> $<span data-checkout-total><?php echo esc_html( $cb_total ); ?></span> →
				</button>
				<p class="cbk-finenote">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
					<?php esc_html_e( 'Payments secured & encrypted. Powered by Stripe.', 'chatbotistic' ); ?>
				</p>
			</form>

			<div class="cbk-trust">
				<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><?php esc_html_e( 'SSL encrypted', 'chatbotistic' ); ?></span>
				<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php esc_html_e( 'PCI-DSS compliant', 'chatbotistic' ); ?></span>
				<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0z"/><path d="M12 7v5l3 2"/></svg><?php esc_html_e( 'Cancel anytime', 'chatbotistic' ); ?></span>
			</div>
		</div>

		<aside class="cbk-summary">
			<div class="cbk-plan">
				<span class="ico">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg>
				</span>
				<div>
					<div class="tag"><?php esc_html_e( 'Your plan', 'chatbotistic' ); ?></div>
					<div class="nm"><?php echo esc_html( $cb_meta['name'] ); ?></div>
				</div>
			</div>

			<div class="cbk-toggle" role="group" aria-label="<?php esc_attr_e( 'Billing cycle', 'chatbotistic' ); ?>">
				<button type="button" class="<?php echo $cb_annual_get ? '' : 'active'; ?>" data-checkout-mode="monthly" aria-pressed="<?php echo $cb_annual_get ? 'false' : 'true'; ?>"><?php esc_html_e( 'Monthly', 'chatbotistic' ); ?></button>
				<button type="button" class="<?php echo $cb_annual_get ? 'active' : ''; ?>" data-checkout-mode="annual" aria-pressed="<?php echo $cb_annual_get ? 'true' : 'false'; ?>"><?php esc_html_e( 'Annual', 'chatbotistic' ); ?><span class="save"><?php esc_html_e( '–17%', 'chatbotistic' ); ?></span></button>
			</div>

			<div class="cbk-price">
				<span class="amt">$<span data-checkout-price><?php echo esc_html( $cb_display ); ?></span></span>
				<span class="per"><?php esc_html_e( '/mo', 'chatbotistic' ); ?></span>
			</div>
			<div class="cbk-note" data-checkout-note style="color:<?php echo $cb_annual_get ? 'var(--cyan)' : 'var(--text-dim)'; ?>;">
				<?php echo $cb_annual_get
					? esc_html( sprintf( __( 'Billed $%s/year — save 17%%', 'chatbotistic' ), $cb_meta['annual'] ) )
					: esc_html__( 'Billed monthly', 'chatbotistic' ); ?>
			</div>

			<ul class="cbk-feats">
				<?php foreach ( $cb_meta['feats'] as $cb_f ) : ?>
					<li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo esc_html( $cb_f ); ?></li>
				<?php endforeach; ?>
			</ul>

			<div class="cbk-rows">
				<div class="cbk-row"><span class="k"><?php esc_html_e( 'Subtotal', 'chatbotistic' ); ?></span><span class="v">$<span data-checkout-total><?php echo esc_html( $cb_total ); ?></span></span></div>
				<div class="cbk-row"><span class="k"><?php esc_html_e( 'Tax', 'chatbotistic' ); ?></span><span class="v">$0.00</span></div>
				<div class="cbk-row total"><span class="k"><?php esc_html_e( 'Total due today', 'chatbotistic' ); ?></span><span class="v">$<span data-checkout-total><?php echo esc_html( $cb_total ); ?></span></span></div>
			</div>

			<div class="cbk-guarantee">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
				<span><b><?php esc_html_e( '14-day money-back guarantee', 'chatbotistic' ); ?></b><?php esc_html_e( 'Not a fit? Get a full refund, no questions asked.', 'chatbotistic' ); ?></span>
			</div>
		</aside>

	</div>
</main>

<script>
(function () {
	var page = document.getElementById('cb-checkout-page');
	if (!page) return;
	var prices = {
		monthly: '<?php echo esc_js( $cb_meta['monthly'] ); ?>',
		annual:  '<?php echo esc_js( $cb_meta['annual'] ); ?>'
	};
	function set(mode) {
		page.querySelectorAll('[data-checkout-mode]').forEach(function (b) {
			var on = (b.getAttribute('data-checkout-mode') === mode);
			b.classList.toggle('active', on);
			b.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		var cycle = page.querySelector('#cb-checkout-cycle');
		if (cycle) cycle.value = mode;
		var display = (mode === 'annual') ? (parseFloat(prices.annual) / 12).toFixed(2) : prices.monthly;
		var total   = (mode === 'annual') ? prices.annual : prices.monthly;
		var pv = page.querySelector('[data-checkout-price]');
		if (pv) pv.textContent = display;
		page.querySelectorAll('[data-checkout-total]').forEach(function (el) { el.textContent = total; });
		var note = page.querySelector('[data-checkout-note]');
		if (note) {
			if (mode === 'annual') {
				note.textContent = 'Billed $' + prices.annual + '/year — save 17%';
				note.style.color = 'var(--cyan)';
			} else {
				note.textContent = 'Billed monthly';
				note.style.color = 'var(--text-dim)';
			}
		}
	}
	page.querySelectorAll('[data-checkout-mode]').forEach(function (b) {
		b.addEventListener('click', function () { set(b.getAttribute('data-checkout-mode')); });
	});
})();
</script>

<?php get_template_part( 'template-parts/auth-foot' );
