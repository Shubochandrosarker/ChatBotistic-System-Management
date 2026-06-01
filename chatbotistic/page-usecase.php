<?php
/**
 * Template Name: Use Case Landing
 *
 * One template for every industry use-case page. Content is resolved from
 * inc/landing-data.php by the page slug, then rendered by template-parts/landing.php.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$cb_data = cb_landing_get( get_post()->post_name );

	if ( $cb_data ) {
		get_template_part( 'template-parts/landing', null, array( 'data' => $cb_data ) );
	} else {
		// Page assigned this template but has no data entry — show its editor content.
		?>
		<section class="cb-page-hero">
			<div class="cb-container">
				<h1 class="cb-h1"><span class="cb-grad"><?php the_title(); ?></span></h1>
			</div>
		</section>
		<section class="cb-section">
			<div class="cb-container"><div class="cb-prose"><?php the_content(); ?></div></div>
		</section>
		<?php
	}
endwhile;

get_footer();
