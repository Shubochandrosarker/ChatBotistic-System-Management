<?php
/**
 * Site footer — V4 animated operational footer.
 *
 * Uses V4's exact class names so v4-styles.css applies directly with no
 * bridge translation: .footer-op, .footer-cta, .footer-status,
 * .footer-maps + .sysmap, .footer-op-grid, .footer-col, .footer-op-bottom.
 * All op-* motion primitives respect prefers-reduced-motion via the bridge.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$cb_product = array(
	array( '/features/',                __( 'Features', 'chatbotistic' ) ),
	array( '/whatsapp-automation/',     __( 'WhatsApp Automation', 'chatbotistic' ) ),
	array( '/ai-chatbot/',              __( 'AI Chatbot', 'chatbotistic' ) ),
	array( '/booking-forms/',           __( 'Booking Forms', 'chatbotistic' ) ),
	array( '/wordpress-plugin/',        __( 'WordPress Plugin', 'chatbotistic' ) ),
	array( '/pricing/',                 __( 'Pricing', 'chatbotistic' ) ),
);
$cb_resources = array(
	array( '/blog/',         __( 'Blog', 'chatbotistic' ) ),
	array( '/docs/',         __( 'Documentation', 'chatbotistic' ) ),
	array( '/tutorials/',    __( 'Tutorials', 'chatbotistic' ) ),
	array( '/faqs/',         __( 'FAQs', 'chatbotistic' ) ),
	array( '/support/',      __( 'Get Support', 'chatbotistic' ) ),
	array( '/book-demo/',    __( 'Book a Demo', 'chatbotistic' ) ),
	array( '/affiliate/',    __( 'Affiliate Program', 'chatbotistic' ) ),
);
$cb_company = array(
	array( '/about/',                __( 'About', 'chatbotistic' ) ),
	array( '/use-cases/',            __( 'Use Cases', 'chatbotistic' ) ),
	array( '/agency-white-label/',   __( 'Agency & White Label', 'chatbotistic' ) ),
	array( '/contact/',              __( 'Contact', 'chatbotistic' ) ),
	array( '/account/',              __( 'Member Login', 'chatbotistic' ) ),
);
$cb_ecosystem = array(
	array( 'https://www.wordpressistic.com/',              __( 'WordPressistic', 'chatbotistic' ) ),
	array( 'https://www.wordpressistic.com/wpistic/',      __( 'WPistic', 'chatbotistic' ) ),
	array( 'https://www.wordpressistic.com/memberistic/',  __( 'Memberistic', 'chatbotistic' ) ),
	array( 'https://www.wordpressistic.com/licenseistic/', __( 'Licenseistic', 'chatbotistic' ) ),
	array( 'https://www.wordpressistic.com/bookingistic/', __( 'Bookingistic', 'chatbotistic' ) ),
	array( 'https://www.wordpressistic.com/insightistic/', __( 'Insightistic', 'chatbotistic' ) ),
);
$cb_legal = array(
	array( '/privacy-policy/', __( 'Privacy', 'chatbotistic' ) ),
	array( '/terms/',          __( 'Terms', 'chatbotistic' ) ),
	array( '/refund-policy/',  __( 'Refunds', 'chatbotistic' ) ),
	array( '/security/',       __( 'Security', 'chatbotistic' ) ),
);

$cb_public_map   = array( __( 'Visitor', 'chatbotistic' ), __( 'Website', 'chatbotistic' ), __( 'Chatbotistic Widget', 'chatbotistic' ), __( 'WhatsApp', 'chatbotistic' ), __( 'Lead Captured', 'chatbotistic' ) );
$cb_internal_map = array( __( 'Plan', 'chatbotistic' ), __( 'Member Portal', 'chatbotistic' ), __( 'License', 'chatbotistic' ), __( 'WordPress Addon', 'chatbotistic' ), __( 'Live Widget', 'chatbotistic' ) );
?>
</main><!-- #cb-main -->

<?php
if ( ! is_page_template( 'page-account.php' ) && ! is_404() ) {
	get_template_part( 'template-parts/wordpressistic-ecosystem' );
}
?>

<footer class="footer-op" role="contentinfo">

	<div class="footer-op-bg" aria-hidden="true">
		<div class="glow"></div>
		<div class="grid"></div>
		<div class="footer-scan op-scan"></div>
		<svg class="rails" viewBox="0 0 1200 460" preserveAspectRatio="none">
			<path class="op-rail-line" d="M0 64 H420 V150 H780 V64 H1200" />
			<path class="op-rail-line" d="M0 232 H300 V300 H900 V214 H1200" />
			<path class="op-rail-line" d="M0 392 H560 V322 H1200" />
			<path class="op-rail-flow op-flow" d="M0 64 H420 V150 H780 V64 H1200"   style="animation-duration:1.4s;animation-delay:0s" />
			<path class="op-rail-flow op-flow" d="M0 232 H300 V300 H900 V214 H1200" style="animation-duration:1.8s;animation-delay:.4s" />
			<path class="op-rail-flow op-flow" d="M0 392 H560 V322 H1200"           style="animation-duration:1.6s;animation-delay:.8s" />
			<circle class="op-node-svg" cx="420" cy="64"  r="4" vector-effect="non-scaling-stroke" />
			<circle class="op-node-svg" cx="780" cy="150" r="4" vector-effect="non-scaling-stroke" />
			<circle class="op-node-svg" cx="300" cy="232" r="4" vector-effect="non-scaling-stroke" />
			<circle class="op-node-svg" cx="900" cy="300" r="4" vector-effect="non-scaling-stroke" />
			<circle class="op-node-svg" cx="560" cy="392" r="4" vector-effect="non-scaling-stroke" />
		</svg>
	</div>

	<div class="container">

		<!-- Primary CTA block -->
		<div class="footer-cta">
			<div class="scan op-scan"></div>
			<div>
				<span class="footer-eco-badge"><span class="wb">W</span><?php esc_html_e( 'A WordPressistic Product', 'chatbotistic' ); ?></span>
				<h2><?php esc_html_e( 'Launch your WhatsApp chatbot system.', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Create your widget, connect your WordPress site, and start turning visitors into conversations.', 'chatbotistic' ); ?></p>
				<span class="footer-status" style="margin-top:14px"><span class="sd op-pulse"></span><?php esc_html_e( 'Chatbotistic System · Online', 'chatbotistic' ); ?></span>
			</div>
			<div class="footer-cta-actions">
				<a class="btn btn-primary btn-lg" href="<?php echo esc_url( cb_free_checkout_url() ); ?>"><?php esc_html_e( 'Start free', 'chatbotistic' ); ?></a>
				<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
			</div>
		</div>

		<!-- System maps: public + internal -->
		<div class="footer-maps">
			<div class="sysmap">
				<div class="sysmap-label"><span class="ld"></span><?php esc_html_e( 'Public flow', 'chatbotistic' ); ?></div>
				<div class="sysmap-track">
					<?php
					$cb_last = count( $cb_public_map ) - 1;
					foreach ( $cb_public_map as $cb_i => $cb_node ) :
						$cb_live = ( $cb_i === $cb_last );
						?>
						<div class="sysmap-node<?php echo $cb_live ? ' live' : ''; ?>">
							<span class="nd<?php echo $cb_live ? ' op-pulse' : ''; ?>"></span><?php echo esc_html( $cb_node ); ?>
						</div>
						<?php if ( $cb_i < $cb_last ) : ?>
							<div class="sysmap-rail"><span class="op-flow op-signal" style="animation-delay:<?php echo esc_attr( $cb_i * 0.5 ); ?>s"></span></div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="sysmap">
				<div class="sysmap-label"><span class="ld"></span><?php esc_html_e( 'Internal flow', 'chatbotistic' ); ?></div>
				<div class="sysmap-track">
					<?php
					$cb_last = count( $cb_internal_map ) - 1;
					foreach ( $cb_internal_map as $cb_i => $cb_node ) :
						$cb_live = ( $cb_i === $cb_last );
						?>
						<div class="sysmap-node<?php echo $cb_live ? ' live' : ''; ?>">
							<span class="nd<?php echo $cb_live ? ' op-pulse' : ''; ?>"></span><?php echo esc_html( $cb_node ); ?>
						</div>
						<?php if ( $cb_i < $cb_last ) : ?>
							<div class="sysmap-rail"><span class="op-flow op-signal" style="animation-delay:<?php echo esc_attr( 0.25 + $cb_i * 0.5 ); ?>s"></span></div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Five-column link grid -->
		<div class="footer-op-grid">

			<div class="footer-col footer-op-brand">
				<?php echo cb_logo( array( 'class' => 'cb-logo--footer' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper. ?>
				<p><?php esc_html_e( 'A WordPress-first WhatsApp AI chatbot platform. Turn every website visit into a conversation, then into a lead.', 'chatbotistic' ); ?></p>
				<address class="footer-legal-address">
					<strong><?php esc_html_e( 'Chatbotistic is a product of WordPressistic LLC.', 'chatbotistic' ); ?></strong><br>
					<?php esc_html_e( '1209 MOUNTAIN ROAD PL NE STE N, ALBUQUERQUE, NM 87110, USA', 'chatbotistic' ); ?><br>
					<?php esc_html_e( 'Part of the WPISTIC Ecosystem.', 'chatbotistic' ); ?>
				</address>
			</div>

			<?php
			$cb_cols = array(
				__( 'Product', 'chatbotistic' )   => $cb_product,
				__( 'Resources', 'chatbotistic' ) => $cb_resources,
				__( 'Company', 'chatbotistic' )   => $cb_company,
				__( 'Ecosystem', 'chatbotistic' ) => $cb_ecosystem,
			);
			foreach ( $cb_cols as $cb_heading => $cb_links ) :
				?>
				<div class="footer-col">
					<h2 class="footer-col__heading"><?php echo esc_html( $cb_heading ); ?></h2>
					<?php
					foreach ( $cb_links as $cb_link ) :
						$cb_external = 0 === strpos( $cb_link[0], 'http' );
						$cb_href     = $cb_external ? $cb_link[0] : home_url( $cb_link[0] );
						?>
						<a href="<?php echo esc_url( $cb_href ); ?>"<?php echo $cb_external ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $cb_link[1] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Bottom row -->
		<div class="footer-op-bottom">
			<div>
				<?php
				printf(
					/* translators: 1: current year, 2: site name */
					esc_html__( '© %1$s %2$s — A WordPressistic product. All rights reserved.', 'chatbotistic' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</div>
			<nav class="legal" aria-label="<?php esc_attr_e( 'Legal', 'chatbotistic' ); ?>">
				<?php foreach ( $cb_legal as $cb_link ) : ?>
					<a href="<?php echo esc_url( home_url( $cb_link[0] ) ); ?>"><?php echo esc_html( $cb_link[1] ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>
	</div>
</footer>

</div><!-- .cb-site -->
<?php wp_footer(); ?>
</body>
</html>
