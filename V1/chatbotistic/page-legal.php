<?php
/**
 * Template Name: Legal (V4)
 *
 * Shared template for Privacy, Terms, Refunds, Security. Title comes
 * from the page itself; body renders the editor content in V4 .cb-prose.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main class="page-fade">
	<section class="page-hero">
		<div class="container" style="text-align:center;">
			<span class="section-eyebrow"><span class="dot"></span><?php esc_html_e( 'Legal', 'chatbotistic' ); ?></span>
			<h1 class="text-grad" style="max-width:760px;margin:18px auto 0;"><?php echo esc_html( get_the_title() ); ?></h1>
			<p style="max-width:640px;margin:18px auto 0;color:var(--text-dim);font-family:var(--font-mono);font-size:12.5px;letter-spacing:0.06em;">
				<?php
				/* translators: %s: last updated date */
				printf( esc_html__( 'Last updated: %s', 'chatbotistic' ), esc_html( get_the_modified_date() ) );
				?>
			</p>
		</div>
	</section>

	<section class="section section-tight">
		<div class="container">
			<div class="cb-prose" style="max-width:760px;margin:0 auto;">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="big-cta">
				<h2 class="text-grad"><?php esc_html_e( 'Questions about this page?', 'chatbotistic' ); ?></h2>
				<p><?php esc_html_e( 'Email legal@chatbotistic.com or open a support ticket.', 'chatbotistic' ); ?></p>
				<div class="cta-actions">
					<a class="btn btn-primary btn-lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact us', 'chatbotistic' ); ?></a>
					<a class="btn btn-ghost btn-lg" href="mailto:legal@chatbotistic.com">legal@chatbotistic.com</a>
				</div>
			</div>
		</div>
	</section>
</main>

<?php get_footer();
