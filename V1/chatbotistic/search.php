<?php
/**
 * Search results.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="cb-page-hero">
	<div class="cb-container">
		<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Search results', 'chatbotistic' ); ?></span></h1>
		<p class="cb-lead">
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Results for “%s”', 'chatbotistic' ), esc_html( get_search_query() ) );
			?>
		</p>
		<form role="search" method="get" class="cb-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="max-width:440px;margin:20px auto 0;">
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'chatbotistic' ); ?>">
		</form>
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
			<p class="cb-lead cb-center"><?php esc_html_e( 'Nothing matched. Try a different search term.', 'chatbotistic' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
