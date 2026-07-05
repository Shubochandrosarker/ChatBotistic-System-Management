<?php
/**
 * Blog index / fallback template.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_title = is_home() ? __( 'Blog', 'chatbotistic' ) : wp_strip_all_tags( get_the_archive_title() );
?>
<section class="cb-page-hero">
	<div class="cb-container">
		<h1 class="cb-h1"><span class="cb-grad"><?php echo esc_html( $cb_title ); ?></span></h1>
		<p class="cb-lead"><?php esc_html_e( 'Playbooks, product updates, and automation tactics for service businesses.', 'chatbotistic' ); ?></p>
	</div>
</section>

<section class="cb-section">
	<div class="cb-container">
		<?php if ( have_posts() ) : ?>
			<div class="cb-posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<div class="cb-pagination">
				<?php echo paginate_links( array( 'mid_size' => 1, 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) ); ?>
			</div>
		<?php else : ?>
			<p class="cb-lead cb-center"><?php esc_html_e( 'No posts yet — check back soon.', 'chatbotistic' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
