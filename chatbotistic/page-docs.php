<?php
/**
 * Template Name: Documentation Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_start = array(
	array( __( 'Create your account', 'chatbotistic' ), __( 'Sign up free — no card needed — and land in your member portal.', 'chatbotistic' ) ),
	array( __( 'Build a widget', 'chatbotistic' ),      __( 'Pick AI chat, WhatsApp or booking and train it on your content.', 'chatbotistic' ) ),
	array( __( 'Embed it', 'chatbotistic' ),            __( 'Add one snippet to any site, or install the WordPress plugin.', 'chatbotistic' ) ),
	array( __( 'Go live', 'chatbotistic' ),             __( 'Watch conversations and leads land in your shared inbox.', 'chatbotistic' ) ),
);

$cb_topics = array(
	array( 'rocket', __( 'Getting started', 'chatbotistic' ), __( 'Create your account, build your first widget and go live.', 'chatbotistic' ), '' ),
	array( 'wa',     __( 'WhatsApp setup', 'chatbotistic' ),  __( 'Connect a number and configure your AI WhatsApp Agent.', 'chatbotistic' ), '/whatsapp-automation/' ),
	array( 'wp',     __( 'WordPress plugin', 'chatbotistic' ),__( 'Install, license and configure the Chatbotistic plugin.', 'chatbotistic' ), '/wordpress-plugin/' ),
	array( 'cal',    __( 'Booking forms', 'chatbotistic' ),   __( 'Set up appointments, calendar sync and reminders.', 'chatbotistic' ), '/booking-forms/' ),
	array( 'plug',   __( 'Integrations', 'chatbotistic' ),    __( 'Connect CRMs, Google Sheets, Stripe and webhooks.', 'chatbotistic' ), '' ),
	array( 'code',   __( 'API reference', 'chatbotistic' ),   __( 'Authenticate and push leads with the REST API.', 'chatbotistic' ), '' ),
);

$cb_faqs = array(
	array( 'q' => __( 'Where do I get my embed snippet?', 'chatbotistic' ), 'a' => __( 'After you build a widget, the embed snippet appears in your member portal under the widget’s settings. Paste it before the closing body tag, or use the WordPress plugin to skip the snippet entirely.', 'chatbotistic' ) ),
	array( 'q' => __( 'How do I activate the WordPress plugin?', 'chatbotistic' ), 'a' => __( 'Install the free Chatbotistic plugin, then paste the license key from your account. Your widgets and inbox sync automatically.', 'chatbotistic' ) ),
	array( 'q' => __( 'How do I connect WhatsApp?', 'chatbotistic' ), 'a' => __( 'The WhatsApp setup guide walks through linking a WhatsApp Business API number. On higher plans we can provision a number for you.', 'chatbotistic' ) ),
	array( 'q' => __( 'Do you have an API?', 'chatbotistic' ), 'a' => __( 'Yes — a REST API plus outbound webhooks for every event. See the API reference section to authenticate and start pushing leads.', 'chatbotistic' ) ),
	array( 'q' => __( 'I am stuck — how do I get help?', 'chatbotistic' ), 'a' => __( 'Head to the Support page to submit a structured request, or use the support tab inside your member portal for faster, account-aware help.', 'chatbotistic' ) ),
);
cb_add_faq_schema( $cb_faqs );

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<nav class="cb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'chatbotistic' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php esc_html_e( 'Documentation', 'chatbotistic' ); ?></span>
			</nav>
			<span class="cb-eyebrow"><?php esc_html_e( 'Documentation', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Everything you need to ship', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Guides, setup walkthroughs and API references for the whole platform — from first widget to advanced automation.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'Quick start', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'From zero to live in four steps', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-steps" style="text-align:left;">
				<?php foreach ( $cb_start as $cb_i => $cb_s ) : ?>
					<div class="cb-step cb-reveal">
						<div class="cb-step__num"><?php echo esc_html( sprintf( '%02d', $cb_i + 1 ) ); ?></div>
						<h3><?php echo esc_html( $cb_s[0] ); ?></h3>
						<p><?php echo esc_html( $cb_s[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'Browse by topic', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Documentation topics', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-grid cb-grid--3">
				<?php
				foreach ( $cb_topics as $cb_t ) :
					$cb_tag = $cb_t[3] ? 'a' : 'div';
					?>
					<<?php echo $cb_tag; ?> class="cb-feature cb-reveal"<?php echo $cb_t[3] ? ' href="' . esc_url( home_url( $cb_t[3] ) ) . '"' : ''; ?>>
						<div class="cb-feature__ico"><?php cb_icon( $cb_t[0], 20 ); ?></div>
						<h3><?php echo esc_html( $cb_t[1] ); ?></h3>
						<p><?php echo esc_html( $cb_t[2] ); ?></p>
					</<?php echo $cb_tag; ?>>
				<?php endforeach; ?>
			</div>
			<?php if ( get_the_content() ) : ?>
				<div class="cb-prose" style="margin-top:40px;"><?php the_content(); ?></div>
			<?php endif; ?>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'Popular questions', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Quick answers', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-faq" style="text-align:left;">
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
			<div style="margin-top:32px;">
				<?php cb_button( __( 'Still stuck? Get support', 'chatbotistic' ), home_url( '/support/' ), 'ghost', array( 'icon' => 'arrow-r' ) ); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
