<?php
/**
 * Template Name: FAQs (V4)
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_groups = array(
	array( __( 'Getting started', 'chatbotistic' ), array(
		array( __( 'How do I sign up?', 'chatbotistic' ), __( 'Create a free account at /register/. The free plan stays free, no card required.', 'chatbotistic' ) ),
		array( __( 'How long does setup take?', 'chatbotistic' ), __( 'About 5 minutes from signup to live widget. Install the WordPress plugin, paste your license, pick a widget.', 'chatbotistic' ) ),
		array( __( 'Do I need a credit card?', 'chatbotistic' ), __( 'No — only when upgrading to Pro, Agency, or Lifetime.', 'chatbotistic' ) ),
	) ),
	array( __( 'WordPress + widgets', 'chatbotistic' ), array(
		array( __( 'Is there a WordPress plugin?', 'chatbotistic' ), __( 'Yes — the Chatbotistic Widget addon. Download it from your account, paste your license, pick a widget.', 'chatbotistic' ) ),
		array( __( 'Does it work with WooCommerce?', 'chatbotistic' ), __( 'Yes. Native order lookup, cart recovery, and product Q&A.', 'chatbotistic' ) ),
		array( __( 'Can I use it on multiple domains?', 'chatbotistic' ), __( 'Free covers 1 domain, Pro covers 10, Agency covers 50, Lifetime is unlimited.', 'chatbotistic' ) ),
	) ),
	array( __( 'WhatsApp + leads', 'chatbotistic' ), array(
		array( __( 'Do I need the WhatsApp Business API?', 'chatbotistic' ), __( 'No — a standard WhatsApp number works. We handle the routing.', 'chatbotistic' ) ),
		array( __( 'Where do leads land?', 'chatbotistic' ), __( 'In your Chatbotistic inbox, exportable to CSV, with CRM integrations on Pro and above.', 'chatbotistic' ) ),
		array( __( 'Can I set business hours?', 'chatbotistic' ), __( 'Yes, with different replies for open hours, after hours, and holidays.', 'chatbotistic' ) ),
	) ),
	array( __( 'Billing + licensing', 'chatbotistic' ), array(
		array( __( 'How does billing work?', 'chatbotistic' ), __( 'Monthly or annual, in USD. Annual saves ~17%. Cancel anytime.', 'chatbotistic' ) ),
		array( __( 'What happens if my plan expires?', 'chatbotistic' ), __( 'A 3-day grace window keeps your widget live during transient issues. After that, premium features pause but your account and data stay safe.', 'chatbotistic' ) ),
		array( __( 'Can I get a refund?', 'chatbotistic' ), __( 'Yes — see /refund-policy/ for the full terms.', 'chatbotistic' ) ),
	) ),
);
?>

<main class="page-fade">
	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'FAQs', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php esc_html_e( 'Common questions, plainly answered.', 'chatbotistic' ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;"><?php esc_html_e( 'Can’t find what you’re looking for? Open a ticket and a human replies.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="section">
		<div class="container" style="display:flex;flex-direction:column;gap:48px;max-width:820px;margin:0 auto;">
			<?php foreach ( $cb_groups as $g ) : ?>
				<div>
					<h2 class="h-2 text-grad"><?php echo esc_html( $g[0] ); ?></h2>
					<div class="faq-list" style="margin-top:20px;">
						<?php foreach ( $g[1] as $q ) : ?>
							<details class="faq-item">
								<summary><?php echo esc_html( $q[0] ); ?></summary>
								<p><?php echo esc_html( $q[1] ); ?></p>
							</details>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="big-cta">
				<h2 class="text-grad"><?php esc_html_e( 'Still have a question?', 'chatbotistic' ); ?></h2>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact support', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><?php esc_html_e( 'Browse docs', 'chatbotistic' ); ?></a>
				</div>
			</div>
		</div>
	</section>
</main>

<?php get_footer();
