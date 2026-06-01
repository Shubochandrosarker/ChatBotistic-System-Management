<?php
/**
 * Template Name: Pricing Page
 *
 * Pricing cards are rendered by the theme (full design control) but their
 * data — name, prices, benefits, featured flag — is pulled live from
 * Memberistic via cb_memberistic_plans(), so prices never drift. Each CTA
 * links to the Memberistic checkout for that plan. If Memberistic has no
 * plans yet, a built-in default set keeps the page looking complete.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_plans = cb_memberistic_plans();

/* Fallback so the page is never empty before plans are published. Mirrors the
   canonical Chatbotistic plan set (Free / Pro / Agency / Lifetime). */
if ( ! $cb_plans ) {
	$cb_fallback = cb_plans_url();
	$cb_plans    = array(
		array( 'name' => __( 'Free Forever', 'chatbotistic' ), 'slug' => 'free', 'description' => __( 'Try it on one small site.', 'chatbotistic' ), 'monthly' => 0.0, 'annual_total' => 0.0, 'annual_monthly' => 0.0, 'saving' => 0.0, 'featured' => false, 'contact_only' => false, 'billing_cycle' => 'forever', 'price_display' => '', 'checkout' => $cb_fallback,
			'benefits' => array( __( '1 AI chatbot widget', 'chatbotistic' ), __( '1 WhatsApp agent', 'chatbotistic' ), __( '1 website domain', 'chatbotistic' ), __( 'Email notifications', 'chatbotistic' ), __( 'Chatbotistic branding', 'chatbotistic' ) ) ),
		array( 'name' => __( 'Pro', 'chatbotistic' ), 'slug' => 'pro', 'description' => __( 'For a solo business going hands-free.', 'chatbotistic' ), 'monthly' => 9.0, 'annual_total' => 90.0, 'annual_monthly' => 7.5, 'saving' => 18.0, 'featured' => true, 'contact_only' => false, 'billing_cycle' => 'monthly', 'price_display' => '', 'checkout' => $cb_fallback,
			'benefits' => array( __( '5 AI chatbot widgets', 'chatbotistic' ), __( '15 WhatsApp agents', 'chatbotistic' ), __( '10 website domains', 'chatbotistic' ), __( 'Custom landing pages & chat forms', 'chatbotistic' ), __( 'CRM integrations & webhooks', 'chatbotistic' ), __( 'No Chatbotistic branding', 'chatbotistic' ) ) ),
		array( 'name' => __( 'Agency', 'chatbotistic' ), 'slug' => 'agency', 'description' => __( 'For agencies reselling under their brand.', 'chatbotistic' ), 'monthly' => 99.0, 'annual_total' => 990.0, 'annual_monthly' => 82.5, 'saving' => 198.0, 'featured' => false, 'contact_only' => false, 'billing_cycle' => 'monthly', 'price_display' => '', 'checkout' => $cb_fallback,
			'benefits' => array( __( '30 AI chatbot widgets', 'chatbotistic' ), __( '100 WhatsApp agents', 'chatbotistic' ), __( '50 website domains', 'chatbotistic' ), __( 'White-label dashboard & widgets', 'chatbotistic' ), __( 'API, webhooks & Stripe', 'chatbotistic' ), __( 'Team agents & priority support', 'chatbotistic' ) ) ),
		array( 'name' => __( 'Lifetime', 'chatbotistic' ), 'slug' => 'lifetime', 'description' => __( 'Everything in Agency, billed once.', 'chatbotistic' ), 'monthly' => 0.0, 'annual_total' => 0.0, 'annual_monthly' => 0.0, 'saving' => 0.0, 'featured' => false, 'contact_only' => true, 'billing_cycle' => 'lifetime', 'price_display' => '***', 'checkout' => cb_ltd_inquiry_url( 'lifetime' ),
			'benefits' => array( __( 'Everything in Agency', 'chatbotistic' ), __( 'Unlimited widgets, agents & domains', 'chatbotistic' ), __( 'Lifetime updates', 'chatbotistic' ), __( 'Founder support', 'chatbotistic' ), __( 'Custom contract & invoicing', 'chatbotistic' ) ) ),
	);
}

/* Plan-benefits matrix: [ feature, Free, Starter, Growth, Agency ]. Editorial — keep aligned with your Memberistic plans. */
$cb_matrix = array(
	array( __( 'AI chatbot widgets', 'chatbotistic' ), '1', '3', __( 'Unlimited', 'chatbotistic' ), __( 'Unlimited', 'chatbotistic' ) ),
	array( __( 'Conversations / month', 'chatbotistic' ), '200', '2,000', '10,000', __( 'Unlimited', 'chatbotistic' ) ),
	array( __( 'WhatsApp automation', 'chatbotistic' ), '—', '✓', '✓', '✓' ),
	array( __( 'Booking forms & payments', 'chatbotistic' ), '—', '✓', '✓', '✓' ),
	array( __( 'Team seats', 'chatbotistic' ), '1', '3', __( 'Unlimited', 'chatbotistic' ), __( 'Unlimited', 'chatbotistic' ) ),
	array( __( 'Remove branding', 'chatbotistic' ), '—', '✓', '✓', '✓' ),
	array( __( 'CRM integrations', 'chatbotistic' ), '—', '✓', '✓', '✓' ),
	array( __( 'API & webhooks', 'chatbotistic' ), '—', '—', '✓', '✓' ),
	array( __( 'Full white label', 'chatbotistic' ), '—', '—', '—', '✓' ),
	array( __( 'Client workspaces', 'chatbotistic' ), '—', '—', '—', '✓' ),
	array( __( 'Support', 'chatbotistic' ), __( 'Community', 'chatbotistic' ), __( 'Email', 'chatbotistic' ), __( 'Priority', 'chatbotistic' ), __( 'Partner', 'chatbotistic' ) ),
);

/* Competitor comparison: [ feature, Chatbotistic, Intercom, Tidio, ManyChat ] */
$cb_vs = array(
	array( __( 'Starting paid price', 'chatbotistic' ), __( '$19 / mo', 'chatbotistic' ), __( '~$39+ / seat', 'chatbotistic' ), __( '~$29+ / mo', 'chatbotistic' ), __( '~$15+ / mo', 'chatbotistic' ) ),
	array( __( 'Per-seat pricing', 'chatbotistic' ), __( 'No', 'chatbotistic' ), __( 'Yes', 'chatbotistic' ), __( 'Yes', 'chatbotistic' ), __( 'Varies', 'chatbotistic' ) ),
	array( __( 'AI chatbot', 'chatbotistic' ), '✓', '✓', '✓', __( 'Limited', 'chatbotistic' ) ),
	array( __( 'WhatsApp automation', 'chatbotistic' ), '✓', __( 'Add-on', 'chatbotistic' ), __( 'Limited', 'chatbotistic' ), '✓' ),
	array( __( 'Booking & payments', 'chatbotistic' ), '✓', '—', '—', __( 'Add-on', 'chatbotistic' ) ),
	array( __( 'White label / reseller', 'chatbotistic' ), '✓', '—', '—', '—' ),
	array( __( 'Native WordPress plugin', 'chatbotistic' ), '✓', __( 'Limited', 'chatbotistic' ), '✓', '—' ),
	array( __( 'All-in-one (chat + WhatsApp + booking)', 'chatbotistic' ), '✓', '—', '—', __( 'Partial', 'chatbotistic' ) ),
);

$cb_faqs = array(
	array( 'q' => __( 'How is monthly billing different from annual?', 'chatbotistic' ), 'a' => __( 'Monthly plans are charged every month. Annual plans are charged once a year and work out cheaper. Use the Monthly / Annual toggle above — each card shows the equivalent monthly cost, with the real yearly charge and your saving right below it.', 'chatbotistic' ) ),
	array( 'q' => __( 'What counts as a conversation?', 'chatbotistic' ), 'a' => __( 'A conversation is a 24-hour messaging window with one contact, in line with WhatsApp Business pricing. Unused conversations do not roll over to the next month.', 'chatbotistic' ) ),
	array( 'q' => __( 'Do you charge per team member?', 'chatbotistic' ), 'a' => __( 'No. The Growth and Agency plans include unlimited team seats at no extra cost — unlike most enterprise chat tools that bill per seat.', 'chatbotistic' ) ),
	array( 'q' => __( 'Can I change plans later?', 'chatbotistic' ), 'a' => __( 'Yes. Upgrade or downgrade any time from your member portal — changes are prorated automatically.', 'chatbotistic' ) ),
	array( 'q' => __( 'Is the Free plan really free?', 'chatbotistic' ), 'a' => __( 'Yes — Free is free forever, with no credit card required. It is capped at one widget and 200 conversations a month, which is enough to try the platform on a small site.', 'chatbotistic' ) ),
	array( 'q' => __( 'Can I cancel any time?', 'chatbotistic' ), 'a' => __( 'Yes. There are no contracts. Monthly plans are month-to-month, and you can cancel from the portal whenever you like.', 'chatbotistic' ) ),
	array( 'q' => __( 'How does Chatbotistic compare to Intercom or Tidio?', 'chatbotistic' ), 'a' => __( 'Chatbotistic bundles AI chat, WhatsApp automation and booking into one platform at small-business pricing, with no per-seat tax. Tools like Intercom are powerful but enterprise-priced and bill per seat; others cover only part of the stack. See the comparison table above.', 'chatbotistic' ) ),
);
cb_add_faq_schema( $cb_faqs );

$cb_cols5 = 'grid-template-columns:1.7fr repeat(4,1fr);';

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<nav class="cb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'chatbotistic' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php esc_html_e( 'Pricing', 'chatbotistic' ); ?></span>
			</nav>
			<span class="cb-eyebrow"><?php esc_html_e( 'Pricing', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Simple pricing — start free, scale when it pays for itself', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'No contracts. No per-seat tax. One platform that replaces several tools, priced for real businesses.', 'chatbotistic' ); ?></p>
			<div class="cb-toggle" data-pricing-toggle role="group" aria-label="<?php esc_attr_e( 'Billing period', 'chatbotistic' ); ?>">
				<button type="button" class="is-active" data-mode="monthly"><?php esc_html_e( 'Monthly', 'chatbotistic' ); ?></button>
				<button type="button" data-mode="yearly"><?php esc_html_e( 'Annual', 'chatbotistic' ); ?><span class="cb-toggle__save"><?php esc_html_e( 'SAVE 20%', 'chatbotistic' ); ?></span></button>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-pricing" data-pricing>
				<?php
				foreach ( $cb_plans as $p ) :
					$contact_only = ! empty( $p['contact_only'] );
					$has_annual   = $p['annual_total'] > 0;
					$y_price      = $has_annual ? $p['annual_monthly'] : $p['monthly'];
					// Free is a genuine $0 plan that is NOT contact-only. Lifetime is
					// priced at 0 in the DB but is contact-only, so it is excluded here
					// and never renders as "free forever" or routes to free checkout.
					$is_free      = ! $contact_only && $p['monthly'] <= 0 && $p['annual_total'] <= 0;
					if ( $contact_only ) {
						$cta_label = __( 'Contact for LTD Pricing', 'chatbotistic' );
					} elseif ( $is_free ) {
						$cta_label = __( 'Get started free', 'chatbotistic' );
					} else {
						/* translators: %s: plan name. */
						$cta_label = sprintf( __( 'Choose %s', 'chatbotistic' ), $p['name'] );
					}
					$price_display = $p['price_display'] ?? '';
					?>
					<div class="cb-plan <?php echo $p['featured'] ? 'cb-plan--featured' : ''; ?> <?php echo $contact_only ? 'cb-plan--ltd' : ''; ?> cb-reveal">
						<?php if ( $p['featured'] ) : ?>
							<span class="cb-plan__badge"><?php esc_html_e( 'Most popular', 'chatbotistic' ); ?></span>
						<?php endif; ?>
						<div class="cb-plan__name"><?php echo esc_html( $p['name'] ); ?></div>
						<?php if ( $p['description'] ) : ?>
							<p class="cb-plan__desc"><?php echo esc_html( $p['description'] ); ?></p>
						<?php endif; ?>

						<?php if ( $contact_only ) : ?>
							<div class="cb-plan__price cb-plan__price--ltd"><?php echo esc_html( $price_display ?: '***' ); ?><sub><?php esc_html_e( '/ one-time', 'chatbotistic' ); ?></sub></div>
							<div class="cb-plan__billing">
								<span><?php esc_html_e( 'Contact for LTD Pricing', 'chatbotistic' ); ?></span>
							</div>
						<?php else : ?>
						<div class="cb-plan__price cb-plan__price--m"><?php echo esc_html( cb_price( $p['monthly'] ) ); ?><sub><?php esc_html_e( '/mo', 'chatbotistic' ); ?></sub></div>
						<div class="cb-plan__price cb-plan__price--y"><?php echo esc_html( cb_price( $y_price ) ); ?><sub><?php esc_html_e( '/mo', 'chatbotistic' ); ?></sub></div>

						<div class="cb-plan__billing cb-plan__billing--m">
							<?php echo $is_free ? esc_html__( 'free forever', 'chatbotistic' ) : esc_html__( 'billed monthly', 'chatbotistic' ); ?>
						</div>
						<div class="cb-plan__billing cb-plan__billing--y">
							<?php if ( $is_free ) : ?>
								<span><?php esc_html_e( 'free forever', 'chatbotistic' ); ?></span>
							<?php elseif ( $has_annual ) : ?>
								<span>
									<?php
									/* translators: %s: total annual price. */
									printf( esc_html__( '%s billed yearly', 'chatbotistic' ), esc_html( cb_price( $p['annual_total'] ) ) );
									?>
								</span>
								<?php if ( $p['saving'] > 0 ) : ?>
									<span class="cb-plan__save">
										<?php
										/* translators: %s: amount saved per year. */
										printf( esc_html__( 'Save %s', 'chatbotistic' ), esc_html( cb_price( $p['saving'] ) ) );
										?>
									</span>
								<?php endif; ?>
							<?php else : ?>
								<span><?php esc_html_e( 'billed monthly', 'chatbotistic' ); ?></span>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<ul class="cb-plan__feats">
							<?php foreach ( $p['benefits'] as $feat ) : ?>
								<li><?php cb_icon( 'check', 16 ); ?> <span><?php echo esc_html( $feat ); ?></span></li>
							<?php endforeach; ?>
						</ul>
						<div class="cb-plan__cta">
							<?php cb_button( $cta_label, $p['checkout'], $p['featured'] ? 'primary' : 'ghost' ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="cb-center cb-dim" style="margin-top:22px;font-size:13px;">
				<?php esc_html_e( 'All plans include SSL, GDPR-ready data handling and core integrations. Prices in USD, excluding local taxes.', 'chatbotistic' ); ?>
			</p>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-shead cb-reveal cb-center">
				<span class="cb-eyebrow"><?php esc_html_e( 'Compare plans', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Every plan, feature by feature', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-compare cb-reveal">
				<div class="cb-compare__scroll">
					<div class="cb-compare__row cb-compare__row--head" style="<?php echo esc_attr( $cb_cols5 ); ?>">
						<span><?php esc_html_e( 'Feature', 'chatbotistic' ); ?></span>
						<span class="cb-compare__cell"><?php esc_html_e( 'Free', 'chatbotistic' ); ?></span>
						<span class="cb-compare__cell"><?php esc_html_e( 'Starter', 'chatbotistic' ); ?></span>
						<span class="cb-compare__cell cb-compare__own"><?php esc_html_e( 'Growth', 'chatbotistic' ); ?></span>
						<span class="cb-compare__cell"><?php esc_html_e( 'Agency', 'chatbotistic' ); ?></span>
					</div>
					<?php foreach ( $cb_matrix as $row ) : ?>
						<div class="cb-compare__row" style="<?php echo esc_attr( $cb_cols5 ); ?>">
							<span class="cb-compare__cell--feat"><?php echo esc_html( $row[0] ); ?></span>
							<?php foreach ( array_slice( $row, 1 ) as $ci => $val ) : ?>
								<span class="cb-compare__cell <?php echo 2 === $ci ? 'cb-compare__own' : ''; ?>">
									<?php
									if ( '✓' === $val ) {
										echo '<span class="cb-compare__yes">';
										cb_icon( 'check', 15 );
										echo '</span>';
									} elseif ( '—' === $val ) {
										echo '<span class="cb-compare__no">—</span>';
									} else {
										echo esc_html( $val );
									}
									?>
								</span>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-shead cb-reveal cb-center">
				<span class="cb-eyebrow"><?php esc_html_e( 'Chatbotistic vs the big players', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'The whole stack, without the enterprise price tag', 'chatbotistic' ); ?></span></h2>
				<p class="cb-lead"><?php esc_html_e( 'Most chat tools make you choose: powerful but enterprise-priced, or cheap but partial. Chatbotistic gives you the full stack at small-business pricing.', 'chatbotistic' ); ?></p>
			</div>
			<div class="cb-compare cb-reveal">
				<div class="cb-compare__scroll">
					<div class="cb-vs__head" style="<?php echo esc_attr( $cb_cols5 ); ?>">
						<span></span>
						<span class="cb-vs__brand cb-vs__brand--own"><b><?php esc_html_e( 'Chatbotistic', 'chatbotistic' ); ?></b><span><?php esc_html_e( 'All-in-one', 'chatbotistic' ); ?></span></span>
						<span class="cb-vs__brand"><b><?php esc_html_e( 'Intercom', 'chatbotistic' ); ?></b><span><?php esc_html_e( 'Enterprise', 'chatbotistic' ); ?></span></span>
						<span class="cb-vs__brand"><b><?php esc_html_e( 'Tidio', 'chatbotistic' ); ?></b><span><?php esc_html_e( 'SMB chat', 'chatbotistic' ); ?></span></span>
						<span class="cb-vs__brand"><b><?php esc_html_e( 'ManyChat', 'chatbotistic' ); ?></b><span><?php esc_html_e( 'Social DMs', 'chatbotistic' ); ?></span></span>
					</div>
					<?php foreach ( $cb_vs as $row ) : ?>
						<div class="cb-compare__row" style="<?php echo esc_attr( $cb_cols5 ); ?>">
							<span class="cb-compare__cell--feat"><?php echo esc_html( $row[0] ); ?></span>
							<?php foreach ( array_slice( $row, 1 ) as $ci => $val ) : ?>
								<span class="cb-compare__cell <?php echo 0 === $ci ? 'cb-compare__own' : ''; ?>">
									<?php
									if ( '✓' === $val ) {
										echo '<span class="cb-compare__yes">';
										cb_icon( 'check', 15 );
										echo '</span>';
									} elseif ( '—' === $val ) {
										echo '<span class="cb-compare__no">—</span>';
									} else {
										echo esc_html( $val );
									}
									?>
								</span>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="cb-verdict cb-reveal">
				<?php cb_icon( 'spark', 20 ); ?>
				<div>
					<b><?php esc_html_e( 'The bottom line', 'chatbotistic' ); ?></b>
					<p style="margin-top:4px;"><?php esc_html_e( 'With Chatbotistic you get AI chat, WhatsApp automation, booking and a shared inbox in one plan from $19/month — no per-seat billing. Buying those capabilities separately, or from an enterprise suite, typically costs several times more.', 'chatbotistic' ); ?></p>
				</div>
			</div>
			<p class="cb-center cb-dim" style="margin-top:18px;font-size:12.5px;">
				<?php esc_html_e( 'Competitor capabilities and pricing models reflect commonly published information and may change — please verify current details with each vendor.', 'chatbotistic' ); ?>
			</p>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'Pricing FAQ', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Billing questions, answered', 'chatbotistic' ); ?></span></h2>
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
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Still deciding? Start on Free — no card needed', 'chatbotistic' ); ?></span></h2>
				<div class="cb-cta__actions">
					<?php
					cb_button( __( 'Start free', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
					cb_button( __( 'Talk to sales', 'chatbotistic' ), home_url( '/contact/' ), 'ghost', array( 'size' => 'lg' ) );
					?>
				</div>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
