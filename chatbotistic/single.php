<?php
/**
 * Single post template.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article>
		<section class="cb-page-hero">
			<div class="cb-container">
				<nav class="cb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'chatbotistic' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'chatbotistic' ); ?></a>
				</nav>
				<h1 class="cb-h1"><span class="cb-grad"><?php the_title(); ?></span></h1>
				<p class="cb-lead"><?php echo esc_html( get_the_date() ); ?> · <?php echo esc_html( get_the_author() ); ?></p>
			</div>
		</section>

		<section class="cb-section">
			<div class="cb-container">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="cb-prose"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>
				<div class="cb-prose">
					<?php
					the_content();
					wp_link_pages( array( 'before' => '<div class="cb-pagination">', 'after' => '</div>' ) );
					?>
				</div>
				<section class="cb-related-links" aria-labelledby="cb-related-title">
					<p class="cb-kicker">Continue exploring</p>
					<h2 id="cb-related-title">Build the rest of your conversation system</h2>
					<div class="cb-grid">
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/features/' ) ); ?>"><strong>All features</strong><span>See the full Chatbotistic platform.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/whatsapp-automation/' ) ); ?>"><strong>WhatsApp Automation</strong><span>Route messages and follow-up.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/ai-chatbot/' ) ); ?>"><strong>AI Chatbot</strong><span>Answer questions with a human handoff.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/booking-forms/' ) ); ?>"><strong>Booking Forms</strong><span>Capture structured appointment requests.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/wordpress-plugin/' ) ); ?>"><strong>WordPress Plugin</strong><span>Connect your site without theme edits.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/agency-white-label/' ) ); ?>"><strong>Agency &amp; White Label</strong><span>Deliver isolated client workspaces.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><strong>Pricing</strong><span>Review current plans and limits.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/docs/' ) ); ?>"><strong>Documentation</strong><span>Follow setup and integration guides.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/support/' ) ); ?>"><strong>Support</strong><span>Get help with the next step.</span></a>
						<a class="cb-related-link" href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><strong>About Chatbotistic</strong><span>Meet the WordPressistic LLC product.</span></a>
						<a class="cb-related-link" href="https://www.wpistic.com/"><strong>WPistic</strong><span>Explore the ecosystem access layer.</span></a>
						<a class="cb-related-link" href="https://www.wordpressistic.com/"><strong>WordPressistic</strong><span>Explore the parent product ecosystem.</span></a>
					</div>
				</section>
				<?php if ( comments_open() || get_comments_number() ) : ?>
					<div class="cb-prose" style="margin-top:40px;">
						<?php comments_template(); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</article>
	<?php
endwhile;

get_footer();
