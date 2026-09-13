<?php
/**
 * Blog post card.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="cb-post cb-reveal">
	<a class="cb-post__thumb" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Read %s', 'chatbotistic' ), get_the_title() ) ); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => wp_strip_all_tags( get_the_title() ) ) ); ?>
		<?php endif; ?>
	</a>
	<div class="cb-post__body">
		<div class="cb-post__meta"><?php echo esc_html( get_the_date() ); ?></div>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="cb-post__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<a class="cb-btn cb-btn--link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'chatbotistic' ); ?> &rarr;</a>
	</div>
</article>
