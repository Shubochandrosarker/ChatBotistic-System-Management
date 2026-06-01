<?php
/**
 * WordPressistic ecosystem promo.
 *
 * Five-brand strip with an auto-rotating active card synced to a cycling
 * headline word, plus an infinite brand marquee. Animations + rotation are
 * driven by assets/js/theme.js (the [data-eco] hook).
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

$cb_brands = array(
	array( 'name' => 'WordPressistic', 'tag' => 'AI Automation Hub', 'word' => 'AI Business Automation', 'url' => 'https://www.wordpressistic.com', 'icon' => 'wp' ),
	array( 'name' => 'Chatbotistic',   'tag' => 'WhatsApp CRM',      'word' => 'Conversation Capture',   'url' => 'https://www.chatbotistic.com', 'icon' => 'chat', 'active' => true ),
	array( 'name' => 'Insightistic',   'tag' => 'GA4 + AI Insights', 'word' => 'Analytics Intelligence', 'url' => 'https://www.insightistic.com', 'icon' => 'chart' ),
	array( 'name' => 'Wpagentistic',   'tag' => 'WP AI Agents',      'word' => 'Agentic Workflows',      'url' => 'https://www.wpagentistic.com', 'icon' => 'ai' ),
	array( 'name' => 'Wpistic',        'tag' => 'WP Power Tools',    'word' => 'WordPress Performance',  'url' => 'https://www.wpistic.com', 'icon' => 'bolt' ),
);

$cb_marquee = array_merge( $cb_brands, array(
	array( 'name' => 'Laos Visa', 'tag' => 'US → Laos Visa', 'url' => 'https://www.laosvisa.us' ),
) );
?>
<section class="cb-section cb-section--tight">
	<div class="cb-container">
		<div class="cb-eco cb-reveal" data-eco>

			<div class="cb-eco__top">
				<div class="cb-eco__head">
					<div class="cb-eco__mark" aria-hidden="true">W</div>
					<div>
						<span class="cb-eco__eyebrow"><?php esc_html_e( 'Part of the WordPressistic Ecosystem', 'chatbotistic' ); ?></span>
						<div class="cb-eco__title">
							<?php esc_html_e( 'One', 'chatbotistic' ); ?>
							<span class="cb-eco__cycle">
								<?php foreach ( $cb_brands as $cb_i => $cb_b ) : ?>
									<span class="<?php echo ! empty( $cb_b['active'] ) ? 'is-on' : ''; ?>"><?php echo esc_html( $cb_b['word'] ); ?></span>
								<?php endforeach; ?>
							</span>
							<?php esc_html_e( 'stack. Five focused tools.', 'chatbotistic' ); ?>
						</div>
					</div>
				</div>
				<a class="cb-eco__explore" href="https://www.wordpressistic.com" target="_blank" rel="noopener">
					<?php esc_html_e( 'Explore WordPressistic', 'chatbotistic' ); ?>
					<?php cb_icon( 'arrow-tr', 14 ); ?>
				</a>
			</div>

			<div class="cb-eco__cards">
				<?php foreach ( $cb_brands as $cb_b ) : ?>
					<a class="cb-eco__card <?php echo ! empty( $cb_b['active'] ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( $cb_b['url'] ); ?>" target="_blank" rel="noopener">
						<div class="cb-eco__card-name"><?php echo esc_html( $cb_b['name'] ); ?></div>
						<div class="cb-eco__card-tag"><?php echo esc_html( $cb_b['tag'] ); ?></div>
						<span class="cb-eco__card-mark" aria-hidden="true"><?php cb_icon( $cb_b['icon'], 18 ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="cb-marquee" aria-hidden="true">
				<div class="cb-marquee__track">
					<?php for ( $cb_pass = 0; $cb_pass < 2; $cb_pass++ ) : ?>
						<?php foreach ( $cb_marquee as $cb_m ) : ?>
							<a class="cb-marquee__item" href="<?php echo esc_url( $cb_m['url'] ); ?>" target="_blank" rel="noopener" tabindex="-1">
								<span class="cb-marquee__dot"></span>
								<span><?php echo esc_html( $cb_m['name'] ); ?></span>
								<span class="cb-marquee__tag"><?php echo esc_html( $cb_m['tag'] ); ?></span>
							</a>
							<span class="cb-marquee__sep"></span>
						<?php endforeach; ?>
					<?php endfor; ?>
				</div>
			</div>

		</div>
	</div>
</section>
