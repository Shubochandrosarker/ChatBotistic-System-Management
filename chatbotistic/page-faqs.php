<?php
/**
 * Template Name: FAQs Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_faqs = array(
	array( 'q' => __( 'What is Chatbotistic?', 'chatbotistic' ), 'a' => __( 'Chatbotistic is an AI WhatsApp Agent and chat-widget platform. It puts a smart agent on your website that answers questions, qualifies leads, and books appointments 24/7 — across WhatsApp, web chat, and email.', 'chatbotistic' ) ),
	array( 'q' => __( 'Do I need WhatsApp Business API?', 'chatbotistic' ), 'a' => __( 'For click-to-chat widgets, no. For the AI Agent that sends and receives messages automatically, you connect a WhatsApp Business API number — we guide you through it, or provision one for you on higher plans.', 'chatbotistic' ) ),
	array( 'q' => __( 'Does it work with WordPress?', 'chatbotistic' ), 'a' => __( 'Yes. Install the free Chatbotistic Widget plugin, activate it with your license key, and your widgets appear on the site — no code snippet required.', 'chatbotistic' ) ),
	array( 'q' => __( 'Can I use my own branding?', 'chatbotistic' ), 'a' => __( 'Paid plans remove Chatbotistic branding. The Agency plan adds full white label — your logo, your domain, and client sub-accounts.', 'chatbotistic' ) ),
	array( 'q' => __( 'How is my data handled?', 'chatbotistic' ), 'a' => __( 'Conversations are encrypted in transit, stored securely, and never sold. You can export or delete your data at any time. See our Security and Privacy pages for details.', 'chatbotistic' ) ),
	array( 'q' => __( 'Can I cancel any time?', 'chatbotistic' ), 'a' => __( 'Yes. Monthly plans are month-to-month with no contract. Manage or cancel your plan directly from the member portal.', 'chatbotistic' ) ),
	array( 'q' => __( 'Do you offer support?', 'chatbotistic' ), 'a' => __( 'All plans include email support. Growth and Agency plans get priority response within one business day.', 'chatbotistic' ) ),
);
cb_add_faq_schema( $cb_faqs );

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<span class="cb-eyebrow"><?php esc_html_e( 'FAQs', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Frequently asked questions', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Everything you need to know before you start. Still stuck? Talk to the team.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-faq">
				<?php foreach ( $cb_faqs as $cb_faq ) : ?>
					<div class="cb-faq__item">
						<button type="button" class="cb-faq__q">
							<span><?php echo esc_html( $cb_faq['q'] ); ?></span>
							<?php cb_icon( 'plus', 16 ); ?>
						</button>
						<div class="cb-faq__a"><p><?php echo esc_html( $cb_faq['a'] ); ?></p></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="cb-center" style="margin-top:34px;">
				<?php cb_button( __( 'Contact support', 'chatbotistic' ), home_url( '/contact/' ), 'ghost', array( 'icon' => 'arrow-r' ) ); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
