<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$manifest = array();

$manifest['name']        = __( 'Events', 'fw' );
$manifest['description'] = __( 'This extension adds a fully fledged Events module to your theme. It comes with built in pages that contain a calendar where events can be added.', 'fw' );
$manifest['version'] = '1.0.14';
$manifest['display'] = true;
$manifest['standalone'] = true;
$manifest['github_repo'] = 'https://github.com/ThemeFuse/Unyson-Events-Extension';
$manifest['uri'] = 'http://manual.unyson.io/en/latest/extension/events/index.html#content';
$manifest['author'] = 'ThemeFuse';
$manifest['author_uri'] = 'http://themefuse.com/';

$manifest['github_update'] = 'ThemeFuse/Unyson-Events-Extension';
