<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( class_exists( 'SitePress' ) ) {
	if ( ! defined( 'WPML_LOAD_API_SUPPORT' ) && defined( 'ICL_PLUGIN_PATH' ) ) {
		define( 'WPML_LOAD_API_SUPPORT', 1 );
		require ICL_PLUGIN_PATH . '/inc/wpml-api.php';
	}
}