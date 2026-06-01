<?php
/**
 * Generic page template.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="cb-page-hero">
		<div class="cb-container">
			<nav class="cb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'chatbotistic' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
				<span aria-hidden="true">/</span>
				<span><?php the_title(); ?></span>
			</nav>
			<h1 class="cb-h1"><span class="cb-grad"><?php the_title(); ?></span></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="cb-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<?php if ( cb_is_memberistic_page() ) : ?>
				<?php the_content(); ?>
			<?php else : ?>
				<div class="cb-prose">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
