<?php
/**
 * Template Name: Use Cases Page
 *
 * Hub page — links to every industry use-case landing page.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_cases = array(
	array( 'slug' => 'agencies',        'ico' => 'users',  'title' => __( 'Agencies', 'chatbotistic' ),               'desc' => __( 'Resell AI chat under your own brand and add recurring revenue, managed from one dashboard.', 'chatbotistic' ) ),
	array( 'slug' => 'local-business',  'ico' => 'store',  'title' => __( 'Local businesses', 'chatbotistic' ),        'desc' => __( 'Capture every enquiry and book jobs 24/7 — so a missed call is no longer a lost customer.', 'chatbotistic' ) ),
	array( 'slug' => 'clinics-spas',    'ico' => 'cal',    'title' => __( 'Clinics & spas', 'chatbotistic' ),          'desc' => __( 'Conversational intake and booking with deposits and reminders that cut no-shows.', 'chatbotistic' ) ),
	array( 'slug' => 'real-estate',     'ico' => 'home',   'title' => __( 'Real estate', 'chatbotistic' ),             'desc' => __( 'Qualify property leads in seconds and route hot buyers to the right agent on WhatsApp.', 'chatbotistic' ) ),
	array( 'slug' => 'ecommerce',       'ico' => 'cart',   'title' => __( 'eCommerce', 'chatbotistic' ),               'desc' => __( 'Answer pre-sales questions, look up orders and recover abandoned carts on WhatsApp.', 'chatbotistic' ) ),
	array( 'slug' => 'coaches',         'ico' => 'rocket', 'title' => __( 'Coaches & consultants', 'chatbotistic' ),   'desc' => __( 'Pre-qualify prospects and book paid discovery calls — only the right people get through.', 'chatbotistic' ) ),
	array( 'slug' => 'wordpress-sites', 'ico' => 'wp',     'title' => __( 'WordPress sites', 'chatbotistic' ),         'desc' => __( 'One conversation layer you embed in every client build — and bill for monthly.', 'chatbotistic' ) ),
	array( 'slug' => 'saas-founders',   'ico' => 'spark',  'title' => __( 'SaaS founders', 'chatbotistic' ),           'desc' => __( 'Qualify trials, guide onboarding, book demos and nudge free users toward paid.', 'chatbotistic' ) ),
);

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<nav class="cb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'chatbotistic' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php esc_html_e( 'Use cases', 'chatbotistic' ); ?></span>
			</nav>
			<span class="cb-eyebrow"><?php esc_html_e( 'Use cases', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'One platform, built around how your industry wins customers', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'See exactly how teams like yours use Chatbotistic to capture, qualify and convert — then dive into the playbook for your industry.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-grid cb-grid--2">
				<?php foreach ( $cb_cases as $cb_c ) : ?>
					<a class="cb-feature cb-reveal" href="<?php echo esc_url( home_url( '/' . $cb_c['slug'] . '/' ) ); ?>" style="display:flex;gap:16px;">
						<div class="cb-feature__ico" style="margin-bottom:0;"><?php cb_icon( $cb_c['ico'], 20 ); ?></div>
						<div>
							<h3><?php echo esc_html( $cb_c['title'] ); ?></h3>
							<p style="margin-top:6px;"><?php echo esc_html( $cb_c['desc'] ); ?></p>
							<span class="cb-btn cb-btn--link" style="padding-left:0;margin-top:8px;"><?php esc_html_e( 'See the playbook', 'chatbotistic' ); ?> &rarr;</span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-cta cb-reveal">
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Not sure which fits? Let’s map it together.', 'chatbotistic' ); ?></span></h2>
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
