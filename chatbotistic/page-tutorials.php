<?php
/**
 * Template Name: Tutorials (V4)
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_tutorials = array(
	array( __( 'Install the WordPress addon in 60 seconds', 'chatbotistic' ),  __( '5 min', 'chatbotistic' ), __( 'Install & activate', 'chatbotistic' ),  home_url( '/docs/' ) ),
	array( __( 'Activate your license + connect a domain', 'chatbotistic' ),    __( '4 min', 'chatbotistic' ), __( 'License & domain', 'chatbotistic' ),   home_url( '/docs/' ) ),
	array( __( 'Create your first WhatsApp widget', 'chatbotistic' ),           __( '6 min', 'chatbotistic' ), __( 'WhatsApp', 'chatbotistic' ),            home_url( '/whatsapp-automation/' ) ),
	array( __( 'Train the AI on your site content', 'chatbotistic' ),           __( '8 min', 'chatbotistic' ), __( 'AI', 'chatbotistic' ),                 home_url( '/ai-chatbot/' ) ),
	array( __( 'Set up a booking flow with deposits', 'chatbotistic' ),         __( '7 min', 'chatbotistic' ), __( 'Booking', 'chatbotistic' ),            home_url( '/booking-forms/' ) ),
	array( __( 'Capture leads to HubSpot / Zoho via webhooks', 'chatbotistic' ), __( '9 min', 'chatbotistic' ), __( 'Integrations', 'chatbotistic' ),       home_url( '/docs/' ) ),
	array( __( 'Recover abandoned carts on WooCommerce', 'chatbotistic' ),      __( '6 min', 'chatbotistic' ), __( 'WooCommerce', 'chatbotistic' ),        home_url( '/ecommerce/' ) ),
	array( __( 'White-label the dashboard for an agency client', 'chatbotistic' ), __( '10 min', 'chatbotistic' ), __( 'Agency', 'chatbotistic' ),         home_url( '/agency-white-label/' ) ),
	array( __( 'Build a strategy-call qualifier flow', 'chatbotistic' ),        __( '7 min', 'chatbotistic' ), __( 'Qualifier', 'chatbotistic' ),          home_url( '/coaches-consultants/' ) ),
);
?>

<main class="page-fade">
	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Tutorials', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Short, focused walkthroughs.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Quick lessons for the things you’ll do most often. Each one ≤ 10 minutes.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="features-grid" style="grid-template-columns:repeat(3,1fr);">
				<?php foreach ( $cb_tutorials as $cb_t ) : ?>
					<a class="f-card" href="<?php echo esc_url( $cb_t[3] ); ?>" style="text-decoration:none;">
						<div class="f-ico"></div>
						<div style="display:flex;align-items:center;gap:8px;font-family:var(--font-mono);font-size:10.5px;letter-spacing:0.08em;text-transform:uppercase;color:var(--text-dim);">
							<span><?php echo esc_html( $cb_t[2] ); ?></span><span>·</span><span><?php echo esc_html( $cb_t[1] ); ?></span>
						</div>
						<h3 style="margin-top:8px;"><?php echo esc_html( $cb_t[0] ); ?></h3>
						<span class="btn btn-ghost btn-sm" style="margin-top:14px;align-self:flex-start;"><?php esc_html_e( 'Open tutorial →', 'chatbotistic' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="big-cta">
				<h2 class="text-grad"><?php esc_html_e( 'Looking for the full docs?', 'chatbotistic' ); ?></h2>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><?php esc_html_e( 'Browse docs', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/book-demo/' ) ); ?>"><?php esc_html_e( 'Book a demo', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</section>
</main>

<?php get_footer();
