<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( 'Event-search-tags', 'fw' );
$manifest['description'] = __( 'Connect extension event with shortcodes map & calendar', 'fw' );
$manifest['version'] = '1.0.0';
$manifest['display'] = 'event';
$manifest['standalone'] = true;
$manifest['requirements'] = array(
	'extensions' => array(
		'shortcodes' => array(),
	)
);
