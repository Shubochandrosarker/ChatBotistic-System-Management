<?php
/**
 * Template Name: HTML Sitemap
 *
 * A human-readable, automatically generated index of the public site.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cb_skip      = function_exists( 'cb_noindex_slugs' ) ? cb_noindex_slugs() : array();
$cb_special   = function_exists( 'cb_special_landing_slugs' ) ? cb_special_landing_slugs() : array( 'usecases' => array(), 'products' => array() );
$cb_reserved  = array_merge( $cb_special['usecases'], $cb_special['products'] );
$cb_link_list = static function ( $items ) {
	if ( ! $items ) {
		return;
	}
	foreach ( $items as $cb_item ) :
		?>
		<li><a href="<?php echo esc_url( $cb_item['url'] ); ?>"><?php echo esc_html( $cb_item['label'] ); ?></a></li>
		<?php
	endforeach;
};

$cb_products = array();
foreach ( $cb_special['products'] as $cb_slug ) {
	$cb_page = get_page_by_path( $cb_slug );
	if ( $cb_page && 'publish' === $cb_page->post_status ) {
		$cb_products[] = array( 'label' => get_the_title( $cb_page ), 'url' => get_permalink( $cb_page ) );
	}
}

$cb_usecases = array();
foreach ( $cb_special['usecases'] as $cb_slug ) {
	$cb_page = get_page_by_path( $cb_slug );
	if ( $cb_page && 'publish' === $cb_page->post_status ) {
		$cb_usecases[] = array( 'label' => get_the_title( $cb_page ), 'url' => get_permalink( $cb_page ) );
	}
}

$cb_pages = array();
foreach ( get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $cb_page ) {
	if ( in_array( $cb_page->post_name, $cb_skip, true ) || in_array( $cb_page->post_name, $cb_reserved, true ) || 'blog' === $cb_page->post_name ) {
		continue;
	}
	$cb_pages[] = array( 'label' => get_the_title( $cb_page ), 'url' => get_permalink( $cb_page ) );
}

$cb_posts = array();
foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1000, 'orderby' => 'date', 'order' => 'DESC' ) ) as $cb_post ) {
	$cb_posts[] = array( 'label' => get_the_title( $cb_post ), 'url' => get_permalink( $cb_post ) );
}
?>

<section class="cb-page-hero">
	<div class="cb-container">
		<span class="cb-kicker">Chatbotistic</span>
		<h1 class="cb-h1"><span class="cb-grad"><?php esc_html_e( 'Explore Chatbotistic', 'chatbotistic' ); ?></span></h1>
		<p class="cb-lead"><?php esc_html_e( 'Browse every public product, use case, guide, and article. This index updates automatically as the site grows.', 'chatbotistic' ); ?></p>
	</div>
</section>

<section class="cb-section cb-sitemap-page">
	<div class="cb-container">
		<div class="cb-grid cb-grid--2">
			<div class="glass cb-sitemap-group">
				<h2><?php esc_html_e( 'Products', 'chatbotistic' ); ?></h2>
				<ul><?php $cb_link_list( $cb_products ); ?></ul>
			</div>
			<div class="glass cb-sitemap-group">
				<h2><?php esc_html_e( 'Use cases', 'chatbotistic' ); ?></h2>
				<ul><?php $cb_link_list( $cb_usecases ); ?></ul>
			</div>
			<div class="glass cb-sitemap-group">
				<h2><?php esc_html_e( 'Resources & pages', 'chatbotistic' ); ?></h2>
				<ul><?php $cb_link_list( $cb_pages ); ?></ul>
			</div>
			<div class="glass cb-sitemap-group">
				<h2><?php esc_html_e( 'Blog articles', 'chatbotistic' ); ?></h2>
				<ul><?php $cb_link_list( $cb_posts ); ?></ul>
			</div>
		</div>
		<p class="cb-center" style="margin-top:32px;color:var(--cb-dim);font-size:13px;"><?php esc_html_e( 'Search engines and AI systems can use the XML sitemap and llms.txt files for machine-readable discovery.', 'chatbotistic' ); ?> <a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>"><?php esc_html_e( 'View XML sitemap', 'chatbotistic' ); ?></a></p>
	</div>
</section>

<?php get_footer();
