<?php
namespace Chatbotistic_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * Injects the Chatbotistic widget loader into the front-end footer.
 *
 * The widget key is resolved via Targeting. If no key resolves (none
 * configured or none match), nothing is printed — there's no point in
 * loading a stray script tag.
 */
final class Widget_Renderer {

	public function __construct() {
		add_action( 'wp_footer', [ $this, 'print_widget' ], 99 );
	}

	public function print_widget(): void {
		if ( is_admin() ) return;

		$key = Targeting::resolve_for_current_request();
		if ( ! $key ) return;
		// A configured key is not an authorization grant. Rendering is allowed
		// only for a widget returned by the active license's own catalog.
		if ( ! License::is_widget_allowed( $key ) ) return;

		// Free tier hard cap: only one widget can ever render.
		$caps = License::get_caps();
		if ( -1 !== (int) $caps['max_widgets'] ) {
			$configured = Targeting::configured_keys();
			$allowed    = array_slice( $configured, 0, (int) $caps['max_widgets'] );
			if ( ! in_array( $key, $allowed, true ) ) {
				return;
			}
		}

		printf(
			'<!-- Chatbotistic Widget v%1$s -->%2$s<script defer src="https://app.chatbotistic.com/install-widget/bundle.js?key=%3$s"></script>%2$s',
			esc_attr( CBW_VERSION ),
			"\n",
			rawurlencode( $key )
		);
	}
}
