<?php
/**
 * Template Name: Legal Page
 *
 * Privacy, Terms, Refund Policy, Security — long-form legal content.
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
			<p class="cb-lead">
				<?php
				/* translators: %s: last updated date. */
				printf( esc_html__( 'Last updated %s', 'chatbotistic' ), esc_html( get_the_modified_date() ) );
				?>
			</p>
		</div>
	</section>

	<section class="cb-section">
		<div class="cb-container">
			<div class="cb-prose">
				<?php
				the_content();
				if ( ! get_the_content() ) :
					?>
					<p><?php esc_html_e( 'This page is ready for your content. Edit it in the WordPress editor — the theme styles every heading, list, and link automatically.', 'chatbotistic' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
