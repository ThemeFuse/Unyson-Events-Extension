<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( 'Events', 'fw' );
$manifest['description'] = __( 'This extension adds a fully fledged Events module to your theme. It comes with built in pages that contain a calendar where events can be added.', 'fw' );
$manifest['version'] = '1.0.6';
$manifest['display'] = true;
$manifest['standalone'] = true;

$manifest['github_update'] = 'ThemeFuse/Unyson-Events-Extension';
