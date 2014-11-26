<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if (is_admin()) {
	$ext_instance = fw_ext('event-tickets');
	$current_screen = array(
		'only'  => array(
			array( 'post_type'   => $ext_instance->get_parent()->get_post_type_name() )
		)
	);

	if (fw_current_screen_match($current_screen)) {
		wp_enqueue_script( 'fw-ext-' . $ext_instance->get_name() . '-js',
			$ext_instance->locate_URI('/static/js/calculates.js'),
			array('jquery', 'fw-events', 'underscore'),
			fw()->manifest->get_version()
		);

		wp_enqueue_style( 'fw-ext-' . $ext_instance->get_name() . '-css',
			$ext_instance->locate_URI('/static/css/styles.css'),
			array(),
			fw()->manifest->get_version()
		);
	}

}