<?php
/**
 * Template Name: About Page
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_values = array(
	array( 'spark',  __( 'Accessible by design', 'chatbotistic' ), __( 'Powerful automation should not cost enterprise money. We price so small businesses can actually win.', 'chatbotistic' ) ),
	array( 'shield', __( 'Honest and clear', 'chatbotistic' ),     __( 'Transparent pricing, no lock-in, no dark patterns. You always know what you pay and why.', 'chatbotistic' ) ),
	array( 'bolt',   __( 'Fast and focused', 'chatbotistic' ),     __( 'A lean platform that does a few things brilliantly — not a bloated suite you fight with.', 'chatbotistic' ) ),
);

$cb_provide = array(
	array( 'ai',    __( 'A real product', 'chatbotistic' ),       __( 'AI chat, WhatsApp, booking and a shared inbox — built, maintained and improved continuously.', 'chatbotistic' ) ),
	array( 'users', __( 'Human support', 'chatbotistic' ),        __( 'Real people who answer, documentation that is actually useful, and response times we publish.', 'chatbotistic' ) ),
	array( 'globe', __( 'An ecosystem', 'chatbotistic' ),         __( 'Part of WordPressistic — a family of tools that work together for business automation.', 'chatbotistic' ) ),
);

$cb_stats = array(
	array( '5', __( 'tools in the WordPressistic ecosystem', 'chatbotistic' ) ),
	array( '24/7', __( 'automation working for our customers', 'chatbotistic' ) ),
	array( '1', __( 'mission — make automation affordable', 'chatbotistic' ) ),
);

$cb_faqs = array(
	array( 'q' => __( 'Who is behind Chatbotistic?', 'chatbotistic' ), 'a' => __( 'Chatbotistic is built by WordPressistic, a team focused on making AI business automation affordable and genuinely useful for small and growing businesses.', 'chatbotistic' ) ),
	array( 'q' => __( 'What is the WordPressistic ecosystem?', 'chatbotistic' ), 'a' => __( 'It is a family of focused tools — Chatbotistic for conversation capture, plus analytics, WordPress AI agents and performance tools — that share a design language and work well together.', 'chatbotistic' ) ),
	array( 'q' => __( 'Is Chatbotistic only for WordPress?', 'chatbotistic' ), 'a' => __( 'No. It works on any website via a one-line snippet. WordPress users get an extra-smooth experience through the native plugin.', 'chatbotistic' ) ),
	array( 'q' => __( 'How do I get in touch?', 'chatbotistic' ), 'a' => __( 'Use the contact page for sales questions, or the support page to submit a structured help request. We publish our response times so you know what to expect.', 'chatbotistic' ) ),
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
				<span><?php esc_html_e( 'About', 'chatbotistic' ); ?></span>
			</nav>
			<span class="cb-eyebrow"><?php esc_html_e( 'About', 'chatbotistic' ); ?></span>
			<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'We help small businesses talk to more customers', 'chatbotistic' ); ?></span></h1>
			<p class="cb-lead"><?php esc_html_e( 'Chatbotistic is part of the WordPressistic ecosystem — a family of tools built to make business automation affordable for everyone, not just the enterprise.', 'chatbotistic' ); ?></p>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-split">
				<div class="cb-reveal">
					<span class="cb-eyebrow"><?php esc_html_e( 'Our mission', 'chatbotistic' ); ?></span>
					<h2 class="cb-h2" style="margin-top:14px;"><span class="cb-grad"><?php esc_html_e( 'Enterprise capability, small-business price', 'chatbotistic' ); ?></span></h2>
					<p class="cb-lead" style="margin-top:14px;"><?php esc_html_e( 'The best conversation and automation tools were built for companies with big budgets and big teams. Everyone else was left with missed calls, scattered tools and lost leads.', 'chatbotistic' ); ?></p>
					<p class="cb-soft" style="margin-top:14px;"><?php esc_html_e( 'We built Chatbotistic to close that gap — a platform a solo founder or a growing agency can afford on day one, that still holds up as they scale.', 'chatbotistic' ); ?></p>
				</div>
				<div class="cb-reveal">
					<?php
					if ( get_the_content() ) :
						echo '<div class="cb-card cb-glass cb-prose" style="margin:0;">' . wp_kses_post( apply_filters( 'the_content', get_the_content() ) ) . '</div>';
					else :
						?>
						<div class="cb-quote cb-glass-edge">
							<p>“<?php esc_html_e( 'Automation should not be a luxury. If a tool only pays off for enterprises, it is not really solving the problem.', 'chatbotistic' ); ?>”</p>
							<footer>
								<span class="cb-quote__avatar">W</span>
								<div>
									<b><?php esc_html_e( 'The WordPressistic team', 'chatbotistic' ); ?></b>
									<span><?php esc_html_e( 'Makers of Chatbotistic', 'chatbotistic' ); ?></span>
								</div>
							</footer>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'What we stand for', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Three principles behind every release', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-grid cb-grid--3">
				<?php foreach ( $cb_values as $cb_v ) : ?>
					<div class="cb-feature cb-reveal">
						<div class="cb-feature__ico"><?php cb_icon( $cb_v[0], 20 ); ?></div>
						<h3><?php echo esc_html( $cb_v[1] ); ?></h3>
						<p><?php echo esc_html( $cb_v[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container">
			<div class="cb-shead cb-reveal cb-center">
				<span class="cb-eyebrow"><?php esc_html_e( 'What we provide', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'More than software', 'chatbotistic' ); ?></span></h2>
			</div>
			<div class="cb-grid cb-grid--3">
				<?php foreach ( $cb_provide as $cb_p ) : ?>
					<div class="cb-card cb-glass cb-reveal">
						<div class="cb-feature__ico"><?php cb_icon( $cb_p[0], 20 ); ?></div>
						<h3 class="cb-h3" style="margin-top:6px;"><?php echo esc_html( $cb_p[1] ); ?></h3>
						<p class="cb-soft" style="margin-top:8px;font-size:14px;"><?php echo esc_html( $cb_p[2] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="cb-statband" style="margin-top:36px;">
				<?php foreach ( $cb_stats as $cb_s ) : ?>
					<div class="cb-reveal" style="grid-column:span 1;">
						<div class="cb-statband__num cb-grad cb-grad--brand"><?php echo esc_html( $cb_s[0] ); ?></div>
						<div class="cb-statband__label"><?php echo esc_html( $cb_s[1] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="cb-section cb-section--tight">
		<div class="cb-container cb-center">
			<div class="cb-shead cb-reveal">
				<span class="cb-eyebrow"><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></span>
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'About the company', 'chatbotistic' ); ?></span></h2>
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
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-cta cb-reveal">
				<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Join a growing community of builders', 'chatbotistic' ); ?></span></h2>
				<div class="cb-cta__actions">
					<?php
					cb_button( __( 'Start free', 'chatbotistic' ), cb_plans_url(), 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
					cb_button( __( 'Contact us', 'chatbotistic' ), home_url( '/contact/' ), 'ghost', array( 'size' => 'lg' ) );
					?>
				</div>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
