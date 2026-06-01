<?php
/**
 * Portal view: Leads — Connector dashboard with the Leads tab pre-selected.
 *
 * @package Chatbotistic
 */

defined( 'ABSPATH' ) || exit;

/** @var bool $cb_active */
?>

<?php if ( ! $cb_active ) :
	get_template_part( 'template-parts/portal-state', null, array(
		'type'  => 'locked',
		'title' => __( 'Lead capture is part of a paid plan', 'chatbotistic' ),
		'lead'  => __( 'Activate a plan to capture and review WhatsApp leads from your widgets.', 'chatbotistic' ),
		'cta'   => array( __( 'See plans', 'chatbotistic' ), function_exists( 'cb_plans_url' ) ? cb_plans_url() : home_url( '/pricing/' ), 'primary' ),
	) );
elseif ( shortcode_exists( 'chatbotistic_dashboard' ) ) : ?>
	<div class="cb-tools-frame cb-glass" data-cbc-default-tab="leads">
		<?php echo do_shortcode( '[chatbotistic_dashboard default_tab="leads"]' ); ?>
	</div>
<?php else :
	get_template_part( 'template-parts/portal-state', null, array(
		'type'  => 'error',
		'title' => __( 'Connector not active', 'chatbotistic' ),
		'lead'  => __( 'The Chatbotistic Connector plugin is required to view your leads.', 'chatbotistic' ),
		'cta'   => array( __( 'Contact support', 'chatbotistic' ), home_url( '/contact/' ), 'ghost' ),
	) );
endif;
