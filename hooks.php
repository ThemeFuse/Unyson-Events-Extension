<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Replace the content of the current template with the content of event view
 *
 * @param string $the_content
 *
 * @return string
 */
function _filter_fw_ext_events_the_content( $the_content ) {

	/**
	 * @var FW_Extension_Events $events
	 */
	$events = fw()->extensions->get( 'events' );

	return fw_render_view( $events->locate_view_path( 'content' ), array( 'the_content' => $the_content ) );
}

/**
 * Select custom page template on frontend
 *
 * @internal
 *
 * @param string $template
 *
 * @return string
 */
function _filter_fw_ext_events_template_include( $template ) {

	/**
	 * @var FW_Extension_Events $events
	 */
	$events = fw()->extensions->get( 'events' );

	if ( is_singular( $events->get_post_type_name() ) ) {

		if ( preg_match( '/single-' . '.*\.php/i', basename( $template ) ) === 1 ) {
			return $template;
		}

		if ( $events->locate_view_path( 'single' ) ) {
			return $events->locate_view_path( 'single' );
		} else {
			add_filter( 'the_content', '_filter_fw_ext_portfolio_the_content' );
		}
	} else if ( is_tax( $events->get_taxonomy_name() ) && $events->locate_view_path( 'taxonomy' ) ) {

		if ( preg_match( '/taxonomy-' . '.*\.php/i', basename( $template ) ) === 1 ) {
			return $template;
		}

		return $events->locate_view_path( 'taxonomy' );
	} else if ( is_post_type_archive( $events->get_post_type_name() ) && $events->locate_view_path( 'archive' ) ) {
		if ( preg_match( '/archive-' . '.*\.php/i', basename( $template ) ) === 1 ) {
			return $template;
		}

		return $events->locate_view_path( 'archive' );
	}

	return $template;
}

add_filter( 'template_include', '_filter_fw_ext_events_template_include' );

function _action_fw_ext_events_option_types_init() {
	require_once dirname( __FILE__ ) . '/includes/option-types/event/class-fw-option-type-event.php';
}
add_action( 'fw_option_types_init', '_action_fw_ext_events_option_types_init' );