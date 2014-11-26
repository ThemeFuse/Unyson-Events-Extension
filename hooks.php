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

	return fw_render_view( $events->locate_view_path( 'hook-single' ), array( 'the_content' => $the_content ) );
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
		if ( $events->locate_view_path( 'single' ) ) {
			return $events->locate_view_path( 'single' );
		}

		add_filter( 'the_content', '_filter_fw_ext_events_the_content' );
	} else if ( is_tax( $events->get_taxonomy_name() ) && $events->locate_view_path( 'taxonomy' ) ) {
		return $events->locate_view_path( 'taxonomy' );
	}

	return $template;
}

add_filter( 'template_include', '_filter_fw_ext_events_template_include' );