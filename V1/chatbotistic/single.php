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
