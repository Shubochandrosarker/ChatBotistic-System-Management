<?php
/**
 * Template Name: Checkout (V4)
 *
 * Plan selection comes in via ?plan=pro|agency|lifetime + ?cycle=monthly|annual.
 * Form posts to admin-post.php?action=cb_checkout (Memberistic will wire
 * the actual payment processing — this template just collects details).
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/auth-head' );

$cb_plan_slug  = isset( $_GET['plan'] ) ? sanitize_key( wp_unslash( $_GET['plan'] ) ) : 'pro'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$cb_annual_get = isset( $_GET['cycle'] ) && 'monthly' !== $_GET['cycle']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$cb_plan_meta = array(
	'pro'      => array( 'name' => __( 'Chatbotistic Pro', 'chatbotistic' ),    'monthly' => '9.00',    'annual' => '90.00',    'feats' => array( __( '5 AI ChatBot Widgets', 'chatbotistic' ), __( '15 WhatsApp Agents', 'chatbotistic' ), __( '10 Website Domains', 'chatbotistic' ), __( 'Chat Forms, CRM, Webhooks', 'chatbotistic' ), __( 'WordPress widget plugin', 'chatbotistic' ) ) ),
	'agency'   => array( 'name' => __( 'Chatbotistic Agency', 'chatbotistic' ), 'monthly' => '99.00',   'annual' => '990.00',   'feats' => array( __( '30 AI ChatBot Widgets', 'chatbotistic' ), __( '100 WhatsApp Agents', 'chatbotistic' ), __( '50 Website Domains', 'chatbotistic' ), __( 'White-label dashboard & widgets', 'chatbotistic' ), __( 'Team agents on your account', 'chatbotistic' ) ) ),
);
$cb_meta = $cb_plan_meta[ $cb_plan_slug ] ?? $cb_plan_meta['pro'];
?>

<div class="auth-top" style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 32px;border-bottom:1px solid var(--line-soft);position:sticky;top:0;z-index:30;background:rgba(4,6,12,0.7);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);">
	<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:inline-flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-family:var(--font-display);font-weight:600;">
		<span class="logo-mark"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg></span>
		Chatbotistic
	</a>
	<span class="top-status" style="display:inline-flex;align-items:center;gap:7px;font-family:var(--font-mono);font-size:11px;color:var(--text-soft);"><?php esc_html_e( '🔒 Secure checkout', 'chatbotistic' ); ?></span>
</div>

<main class="page-fade" id="cb-checkout-page">
	<div class="form-page" style="padding:48px 32px 80px;">
		<div class="demo-layout" style="padding:0;grid-template-columns:1fr 360px;">
			<div>
				<h1 style="font-size:28px;letter-spacing:-0.02em;"><?php esc_html_e( 'Complete your upgrade', 'chatbotistic' ); ?></h1>
				<p style="color:var(--text-soft);margin-top:8px;">
					<?php printf( wp_kses( __( 'You’re upgrading to %s. Cancel anytime.', 'chatbotistic' ), array( 'b' => array( 'style' => array() ) ) ), '<b style="color:var(--text);">' . esc_html( $cb_meta['name'] ) . '</b>' ); ?>
				</p>

				<form class="cb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:28px;">
					<input type="hidden" name="action" value="cb_checkout">
					<?php wp_nonce_field( 'cb_checkout', '_wpnonce' ); ?>
					<input type="hidden" name="plan" value="<?php echo esc_attr( $cb_plan_slug ); ?>">
					<input type="hidden" name="cycle" id="cb-checkout-cycle" value="<?php echo $cb_annual_get ? 'annual' : 'monthly'; ?>">

					<div class="form-section-label"><?php esc_html_e( 'Billing details', 'chatbotistic' ); ?></div>
					<div class="grid-2">
						<div class="field"><label><?php esc_html_e( 'Full name', 'chatbotistic' ); ?></label><input name="full_name" placeholder="Sarah Kim" required></div>
						<div class="field"><label><?php esc_html_e( 'Company', 'chatbotistic' ); ?></label><input name="company" placeholder="Acme Inc."></div>
					</div>
					<div class="field"><label><?php esc_html_e( 'Billing email', 'chatbotistic' ); ?></label><input type="email" name="billing_email" placeholder="billing@company.com" required></div>
					<div class="grid-2">
						<div class="field"><label><?php esc_html_e( 'Country', 'chatbotistic' ); ?></label>
							<select name="country">
								<option>United States</option><option>United Kingdom</option><option>Germany</option><option>India</option><option>Australia</option><option>Canada</option><option>Other</option>
							</select>
						</div>
						<div class="field"><label><?php esc_html_e( 'VAT / Tax ID (optional)', 'chatbotistic' ); ?></label><input name="tax_id" placeholder="—"></div>
					</div>

					<div class="form-section-label"><?php esc_html_e( 'Payment', 'chatbotistic' ); ?></div>
					<div class="field"><label><?php esc_html_e( 'Card number', 'chatbotistic' ); ?></label><input name="card" placeholder="4242 4242 4242 4242" required autocomplete="cc-number"></div>
					<div class="grid-2">
						<div class="field"><label><?php esc_html_e( 'Expiry', 'chatbotistic' ); ?></label><input name="exp" placeholder="MM / YY" required autocomplete="cc-exp"></div>
						<div class="field"><label><?php esc_html_e( 'CVC', 'chatbotistic' ); ?></label><input name="cvc" placeholder="123" required autocomplete="cc-csc"></div>
					</div>

					<button class="btn btn-primary btn-lg" type="submit" id="cb-checkout-submit" style="justify-content:center;margin-top:8px;">
						<?php esc_html_e( 'Pay', 'chatbotistic' ); ?> <span data-checkout-total>$<?php echo esc_html( $cb_annual_get ? $cb_meta['annual'] : $cb_meta['monthly'] ); ?></span> →
					</button>
					<p style="font-size:12px;color:var(--text-dim);text-align:center;margin:8px 0 0;">🔒 <?php esc_html_e( 'Payments secured & encrypted. Powered by Stripe.', 'chatbotistic' ); ?></p>
				</form>
			</div>

			<aside class="glass glass-edge demo-aside-card">
				<div class="toggle-wrap" style="margin-top:0;margin-bottom:20px;display:flex;width:100%;">
					<button type="button" class="<?php echo $cb_annual_get ? '' : 'active'; ?>" style="flex:1;" data-checkout-mode="monthly"><?php esc_html_e( 'Monthly', 'chatbotistic' ); ?></button>
					<button type="button" class="<?php echo $cb_annual_get ? 'active' : ''; ?>" style="flex:1;" data-checkout-mode="annual"><?php esc_html_e( 'Annual', 'chatbotistic' ); ?></button>
				</div>
				<div style="display:flex;align-items:baseline;gap:6px;">
					<span class="price-amount" style="font-size:38px;font-family:var(--font-display);font-weight:600;">
						$<span data-checkout-price><?php echo esc_html( $cb_annual_get ? '7.50' : $cb_meta['monthly'] ); ?></span>
					</span>
					<span style="color:var(--text-soft);font-size:14px;">/mo</span>
				</div>
				<div data-checkout-note style="font-size:12.5px;color:<?php echo $cb_annual_get ? 'var(--cyan)' : 'var(--text-dim)'; ?>;margin-top:4px;">
					<?php echo $cb_annual_get
						? esc_html( sprintf( __( 'Billed $%s/year — save 17%%', 'chatbotistic' ), $cb_meta['annual'] ) )
						: esc_html__( 'Billed monthly', 'chatbotistic' ); ?>
				</div>

				<div class="receipt" style="margin-top:20px;border:0;background:transparent;padding:0;">
					<?php foreach ( $cb_meta['feats'] as $cb_f ) : ?>
						<div class="receipt-row" style="border:0;padding:5px 0;color:var(--text-soft);">
							<span style="display:flex;gap:9px;align-items:center;font-size:13px;"><span style="color:var(--green);">✓</span> <?php echo esc_html( $cb_f ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="receipt" style="margin-top:0;">
					<div class="receipt-row"><span class="k"><?php esc_html_e( 'Subtotal', 'chatbotistic' ); ?></span><span class="v">$<span data-checkout-total><?php echo esc_html( $cb_annual_get ? $cb_meta['annual'] : $cb_meta['monthly'] ); ?></span></span></div>
					<div class="receipt-row"><span class="k"><?php esc_html_e( 'Tax', 'chatbotistic' ); ?></span><span class="v">$0.00</span></div>
					<div class="receipt-row" style="font-weight:700;"><span class="k" style="color:var(--text);"><?php esc_html_e( 'Total due today', 'chatbotistic' ); ?></span><span class="v" style="color:var(--text);font-size:15px;">$<span data-checkout-total><?php echo esc_html( $cb_annual_get ? $cb_meta['annual'] : $cb_meta['monthly'] ); ?></span></span></div>
				</div>
			</aside>
		</div>
	</div>
</main>

<script>
(function () {
	var page = document.getElementById('cb-checkout-page');
	if (!page) return;
	var prices = {
		monthly:  '<?php echo esc_js( $cb_meta['monthly'] ); ?>',
		annual:   '<?php echo esc_js( $cb_meta['annual'] ); ?>',
		display:  '<?php echo esc_js( $cb_annual_get ? '7.50' : $cb_meta['monthly'] ); ?>'
	};
	var annualTotal = '<?php echo esc_js( $cb_meta['annual'] ); ?>';
	function set(mode) {
		page.querySelectorAll('[data-checkout-mode]').forEach(function (b) {
			b.classList.toggle('active', b.getAttribute('data-checkout-mode') === mode);
		});
		page.querySelector('#cb-checkout-cycle').value = mode;
		var price = (mode === 'annual') ? (parseFloat(annualTotal)/12).toFixed(2) : prices.monthly;
		var total = (mode === 'annual') ? annualTotal : prices.monthly;
		var pv = page.querySelector('[data-checkout-price]');
		if (pv) pv.textContent = price;
		page.querySelectorAll('[data-checkout-total]').forEach(function (el) { el.textContent = total; });
		var note = page.querySelector('[data-checkout-note]');
		if (note) {
			if (mode === 'annual') {
				note.textContent = 'Billed $' + annualTotal + '/year — save 17%';
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
