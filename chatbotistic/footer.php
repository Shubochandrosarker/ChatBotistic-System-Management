<?php
/**
 * Site footer.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$cb_product = array(
	array( '/features/', __( 'Features', 'chatbotistic' ) ),
	array( '/whatsapp-automation/', __( 'WhatsApp Automation', 'chatbotistic' ) ),
	array( '/ai-chatbot/', __( 'AI Chatbot', 'chatbotistic' ) ),
	array( '/booking-forms/', __( 'Booking Forms', 'chatbotistic' ) ),
	array( '/wordpress-plugin/', __( 'WordPress Plugin', 'chatbotistic' ) ),
	array( '/pricing/', __( 'Pricing', 'chatbotistic' ) ),
);
$cb_resources = array(
	array( '/docs/', __( 'Documentation', 'chatbotistic' ) ),
	array( '/tutorials/', __( 'Tutorials', 'chatbotistic' ) ),
	array( '/faqs/', __( 'FAQs', 'chatbotistic' ) ),
	array( '/support/', __( 'Get Support', 'chatbotistic' ) ),
	array( '/demo/', __( 'Book a Demo', 'chatbotistic' ) ),
	array( '/affiliate/', __( 'Affiliate Program', 'chatbotistic' ) ),
);
$cb_company = array(
	array( '/about/', __( 'About', 'chatbotistic' ) ),
	array( '/use-cases/', __( 'Use cases', 'chatbotistic' ) ),
	array( '/agency-white-label/', __( 'White Label', 'chatbotistic' ) ),
	array( '/contact/', __( 'Contact', 'chatbotistic' ) ),
	array( '/account/', __( 'Member Login', 'chatbotistic' ) ),
);
$cb_social = array(
	'twitter'  => array( 'https://twitter.com/wordpressistic', 'M22 5.8c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.2-.8.5-1.7.8-2.6 1-1.5-1.6-4-1.7-5.6-.2-1 1-1.5 2.4-1.2 3.8C8.4 8.5 5.4 6.9 3.4 4.4c-1 1.7-.5 3.9 1.1 5C4 9.4 3.4 9.2 2.8 9c0 1.9 1.4 3.6 3.3 4-.6.2-1.2.2-1.8.1.5 1.7 2.1 2.8 3.9 2.8a8 8 0 0 1-5.7 1.6 11.3 11.3 0 0 0 6 1.8c7.3 0 11.4-6.2 11.1-11.7.8-.6 1.5-1.3 2-2.1z' ),
	'linkedin' => array( 'https://www.linkedin.com/company/wordpressistic', 'M8 10v8M8 7v.01M12 18v-5a2 2 0 1 1 4 0v5' ),
);
?>
</main><!-- #cb-main -->

<?php
/**
 * WordPressistic ecosystem promo — site-wide above the footer,
 * except inside the member portal where it would be a distraction.
 */
if ( ! is_page_template( 'page-account.php' ) && ! is_404() ) {
	get_template_part( 'template-parts/wordpressistic-ecosystem' );
}
?>

<footer class="cb-footer" role="contentinfo">
	<div class="cb-container">
		<div class="cb-footer__grid">

			<div class="cb-footer__brand">
				<a class="cb-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="cb-logo__mark" aria-hidden="true">
						<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6.5C4 5 5 4 6.5 4h11C19 4 20 5 20 6.5v8c0 1.5-1 2.5-2.5 2.5H13l-4 3v-3H6.5C5 17 4 16 4 14.5z"/></svg>
					</span>
					<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				</a>
				<p><?php esc_html_e( 'AI WhatsApp Agents, chat widgets, and booking automation for service businesses. Turn every visitor into a conversation — and every conversation into revenue.', 'chatbotistic' ); ?></p>
				<div class="cb-social">
					<?php foreach ( $cb_social as $cb_label => $cb_s ) : ?>
						<a href="<?php echo esc_url( $cb_s[0] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $cb_label ) ); ?>">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?php echo esc_attr( $cb_s[1] ); ?>"/></svg>
						</a>
					<?php endforeach; ?>
				</div>
				<div class="cb-footer__pill"><?php esc_html_e( 'Part of the WordPressistic ecosystem', 'chatbotistic' ); ?></div>
			</div>

			<?php
			$cb_cols = array(
				__( 'Product', 'chatbotistic' )   => $cb_product,
				__( 'Resources', 'chatbotistic' ) => $cb_resources,
				__( 'Company', 'chatbotistic' )   => $cb_company,
			);
			foreach ( $cb_cols as $cb_heading => $cb_links ) :
				?>
				<div class="cb-footer__col">
					<h5><?php echo esc_html( $cb_heading ); ?></h5>
					<?php foreach ( $cb_links as $cb_link ) : ?>
						<a href="<?php echo esc_url( home_url( $cb_link[0] ) ); ?>"><?php echo esc_html( $cb_link[1] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<div class="cb-footer__col">
				<h5><?php esc_html_e( 'Get started', 'chatbotistic' ); ?></h5>
				<p style="color:var(--cb-soft);font-size:14px;margin-bottom:14px;"><?php esc_html_e( 'Launch your AI WhatsApp Agent today.', 'chatbotistic' ); ?></p>
				<?php cb_button( __( 'Create account', 'chatbotistic' ), cb_free_checkout_url(), 'primary', array( 'icon' => 'arrow-r', 'class' => 'cb-btn--block' ) ); ?>
			</div>
		</div>

		<div class="cb-footer__bottom">
			<div>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?> — <?php esc_html_e( 'A WordPressistic product.', 'chatbotistic' ); ?></div>
			<nav aria-label="<?php esc_attr_e( 'Legal', 'chatbotistic' ); ?>">
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'chatbotistic' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'chatbotistic' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/refund-policy/' ) ); ?>"><?php esc_html_e( 'Refunds', 'chatbotistic' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/security/' ) ); ?>"><?php esc_html_e( 'Security', 'chatbotistic' ); ?></a>
			</nav>
		</div>
	</div>
</footer>

</div><!-- .cb-site -->
<?php wp_footer(); ?>
</body>
</html>
