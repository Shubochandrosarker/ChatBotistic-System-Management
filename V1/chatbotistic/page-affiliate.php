<?php
/**
 * Template Name: Affiliate Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_perks = array(
	array( 'card',  __( 'Recurring commission', 'chatbotistic' ), __( 'Earn every month for as long as your referral stays subscribed.', 'chatbotistic' ) ),
	array( 'chart', __( 'Real-time tracking', 'chatbotistic' ),   __( 'A clear dashboard for clicks, signups, and payouts.', 'chatbotistic' ) ),
	array( 'spark', __( 'Assets that convert', 'chatbotistic' ),  __( 'Banners, copy, and demo links that do the selling for you.', 'chatbotistic' ) ),
);

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<span class="cb-eyebrow"><?php esc_html_e( 'Affiliate Program', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Earn recurring income with Chatbotistic', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Recommend a tool people actually love, and get paid every month they stay. Built for creators, agencies, and consultants.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-grid cb-grid--3">
				<?php foreach ( $cb_perks as $cb_p ) : ?>
					<div class="cb-feature cb-reveal">
						<div class="cb-feature__ico"><?php cb_icon( $cb_p[0], 20 ); ?></div>
						<h3><?php echo esc_html( $cb_p[1] ); ?></h3>
						<p><?php echo esc_html( $cb_p[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Affiliate questions', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-faq" style="text-align:left;">
				<?php
				$cb_aff_faqs = array(
					array( 'q' => __( 'How much can I earn?', 'chatbotistic' ), 'a' => __( 'You earn a recurring commission on every paying customer you refer, for as long as they stay subscribed — not just a one-off payout.', 'chatbotistic' ) ),
					array( 'q' => __( 'When and how do I get paid?', 'chatbotistic' ), 'a' => __( 'Commissions are tracked in real time in your affiliate dashboard and paid out on a regular monthly schedule once you pass the minimum threshold.', 'chatbotistic' ) ),
					array( 'q' => __( 'Who is the program for?', 'chatbotistic' ), 'a' => __( 'Creators, agencies, consultants and WordPress professionals — anyone with an audience of small or growing businesses that need conversation automation.', 'chatbotistic' ) ),
					array( 'q' => __( 'Do I need to be a customer?', 'chatbotistic' ), 'a' => __( 'No, but it helps — knowing the product first-hand makes your recommendations far more convincing.', 'chatbotistic' ) ),
				);
				cb_add_faq_schema( $cb_aff_faqs );
				foreach ( $cb_aff_faqs as $cb_faq ) :
					?>
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
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Apply to become an affiliate', 'chatbotistic' ); ?></span></h2>
				<p class="cb-lead" style="max-width:480px;margin:0 auto;"><?php esc_html_e( 'Tell us a little about your audience and we will get you set up.', 'chatbotistic' ); ?></p>
				<div class="cb-cta__actions">
					<?php cb_button( __( 'Apply now', 'chatbotistic' ), home_url( '/contact/' ), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
