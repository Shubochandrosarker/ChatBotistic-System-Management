<?php
/**
 * Rich landing-page renderer — shared by product and use-case pages.
 *
 * Expects $args['data'] shaped by inc/landing-data.php:
 *   eyebrow, title, lead, intro_cta, stats[], problems[], solution{},
 *   features{}, why[], related[], faqs[], cta{}.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$d = isset( $args['data'] ) && is_array( $args['data'] ) ? $args['data'] : array();
if ( ! $d ) {
	return;
}

$signup  = cb_plans_url();
$kind    = $d['kind'] ?? 'page';
$crumb   = 'usecase' === $kind ? __( 'Use cases', 'chatbotistic' ) : __( 'Products', 'chatbotistic' );
$crumb_u = 'usecase' === $kind ? home_url( '/use-cases/' ) : home_url( '/features/' );

if ( ! empty( $d['faqs'] ) ) {
	cb_add_faq_schema( $d['faqs'] );
}
?>

<section class="cb-page-hero">
	<div class="cb-container">
		<nav class="cb-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'chatbotistic' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'chatbotistic' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( $crumb_u ); ?>"><?php echo esc_html( $crumb ); ?></a>
			<span aria-hidden="true">/</span>
			<span><?php echo esc_html( get_the_title() ); ?></span>
		</nav>
		<span class="cb-eyebrow"><?php echo esc_html( $d['eyebrow'] ); ?></span>
		<h1 class="cb-h1"><span class="cb-grad"><?php echo esc_html( $d['title'] ); ?></span></h1>
		<p class="cb-lead"><?php echo esc_html( $d['lead'] ); ?></p>
		<div class="cb-cta__actions" style="justify-content:center;margin-top:26px;">
			<?php
			cb_button( __( 'Start free', 'chatbotistic' ), $signup, 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
			cb_button( __( 'Book a demo', 'chatbotistic' ), home_url( '/demo/' ), 'ghost', array( 'size' => 'lg' ) );
			?>
		</div>
	</div>
</section>

<?php if ( ! empty( $d['stats'] ) ) : ?>
<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-grid cb-grid--3">
			<?php foreach ( $d['stats'] as $stat ) : ?>
				<div class="cb-card cb-glass-edge cb-reveal" style="text-align:center;">
					<div class="cb-grad cb-grad--brand" style="font-family:var(--cb-font-display);font-size:42px;font-weight:600;letter-spacing:-0.03em;"><?php echo esc_html( $stat[0] ); ?></div>
					<div class="cb-soft" style="margin-top:8px;font-size:14px;"><?php echo esc_html( $stat[1] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $d['problems'] ) ) : ?>
<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'The problem', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php echo esc_html( $d['problems_title'] ?? __( 'Where leads quietly slip away', 'chatbotistic' ) ); ?></span></h2>
		</div>
		<div class="cb-grid cb-grid--3">
			<?php foreach ( $d['problems'] as $i => $p ) : ?>
				<div class="cb-feature cb-reveal">
					<div class="cb-mono cb-dim" style="font-size:12px;letter-spacing:0.12em;"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></div>
					<h3 style="margin-top:8px;"><?php echo esc_html( $p[0] ); ?></h3>
					<p><?php echo esc_html( $p[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $d['solution']['items'] ) ) : ?>
<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'How Chatbotistic helps', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php echo esc_html( $d['solution']['heading'] ); ?></span></h2>
			<?php if ( ! empty( $d['solution']['lead'] ) ) : ?>
				<p class="cb-lead"><?php echo esc_html( $d['solution']['lead'] ); ?></p>
			<?php endif; ?>
		</div>
		<div class="cb-grid cb-grid--2">
			<?php foreach ( $d['solution']['items'] as $s ) : ?>
				<div class="cb-feature cb-reveal" style="display:flex;gap:16px;">
					<div class="cb-feature__ico" style="margin-bottom:0;"><?php cb_icon( $s[0], 20 ); ?></div>
					<div>
						<h3><?php echo esc_html( $s[1] ); ?></h3>
						<p style="margin-top:6px;"><?php echo esc_html( $s[2] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $d['features']['items'] ) ) : ?>
<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-shead cb-reveal cb-center">
			<span class="cb-eyebrow"><?php esc_html_e( 'What you get', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php echo esc_html( $d['features']['heading'] ); ?></span></h2>
		</div>
		<div class="cb-grid cb-grid--3">
			<?php foreach ( $d['features']['items'] as $f ) : ?>
				<div class="cb-feature cb-reveal">
					<div class="cb-feature__ico"><?php cb_icon( $f[0], 20 ); ?></div>
					<h3><?php echo esc_html( $f[1] ); ?></h3>
					<p><?php echo esc_html( $f[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $d['why'] ) ) : ?>
<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'Why choose us', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php echo esc_html( $d['why_title'] ?? __( 'Why teams pick Chatbotistic', 'chatbotistic' ) ); ?></span></h2>
		</div>
		<div class="cb-grid cb-grid--3">
			<?php foreach ( $d['why'] as $w ) : ?>
				<div class="cb-card cb-glass cb-reveal">
					<div class="cb-feature__ico"><?php cb_icon( $w[0], 20 ); ?></div>
					<h3 class="cb-h3" style="margin-top:6px;"><?php echo esc_html( $w[1] ); ?></h3>
					<p class="cb-soft" style="margin-top:8px;font-size:14px;"><?php echo esc_html( $w[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $d['faqs'] ) ) : ?>
<section class="cb-section cb-section--tight">
	<div class="cb-container cb-center">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'FAQ', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Questions, answered', 'chatbotistic' ); ?></span></h2>
		</div>
		<div class="cb-faq" style="text-align:left;">
			<?php foreach ( $d['faqs'] as $faq ) : ?>
				<div class="cb-faq__item">
					<button type="button" class="cb-faq__q">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<?php cb_icon( 'plus', 16 ); ?>
					</button>
					<div class="cb-faq__a"><p><?php echo esc_html( $faq['a'] ); ?></p></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $d['related'] ) ) : ?>
<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-shead cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'Keep exploring', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php esc_html_e( 'Related pages', 'chatbotistic' ); ?></span></h2>
		</div>
		<div class="cb-grid cb-grid--4">
			<?php foreach ( $d['related'] as $rel ) : ?>
				<a class="cb-use cb-reveal" href="<?php echo esc_url( $rel[1] ); ?>">
					<span class="cb-use__ico"><?php cb_icon( $rel[2] ?? 'arrow-tr', 17 ); ?></span>
					<span><b><?php echo esc_html( $rel[0] ); ?></b><span><?php echo esc_html( $rel[3] ?? __( 'Learn more', 'chatbotistic' ) ); ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="cb-section">
	<div class="cb-container">
		<div class="cb-cta cb-reveal">
			<span class="cb-eyebrow"><?php esc_html_e( 'Get started', 'chatbotistic' ); ?></span>
			<h2 class="cb-h2"><span class="cb-grad"><?php echo esc_html( $d['cta']['title'] ); ?></span></h2>
			<p class="cb-lead" style="max-width:540px;margin:0 auto;"><?php echo esc_html( $d['cta']['sub'] ); ?></p>
			<div class="cb-cta__actions">
				<?php
				cb_button( __( 'Start free', 'chatbotistic' ), $signup, 'primary', array( 'size' => 'lg', 'icon' => 'arrow-r' ) );
				cb_button( __( 'Talk to sales', 'chatbotistic' ), home_url( '/contact/' ), 'ghost', array( 'size' => 'lg' ) );
				?>
			</div>
		</div>
	</div>
</section>
