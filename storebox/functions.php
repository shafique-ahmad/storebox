<?php
/**
 * Storebox functions and definitions.
 *
 * Each concern lives in its own file under inc/. Child themes can override any
 * pluggable function (wrapped in function_exists) or unhook the actions below.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

define( 'STOREBOX_VERSION', '1.0.0' );
define( 'STOREBOX_DIR', get_template_directory() );
define( 'STOREBOX_URI', get_template_directory_uri() );

/*
 * Version of the Storebox Core plugin bundled in /plugins. The setup screen
 * offers an update when the installed copy is older than this.
 */
define( 'STOREBOX_CORE_BUNDLED_VERSION', '1.0.0' );

require STOREBOX_DIR . '/inc/helpers.php';
require STOREBOX_DIR . '/inc/setup.php';
require STOREBOX_DIR . '/inc/customizer.php';
require STOREBOX_DIR . '/inc/assets.php';
require STOREBOX_DIR . '/inc/icons.php';
require STOREBOX_DIR . '/inc/template-functions.php';
require STOREBOX_DIR . '/inc/template-tags.php';
require STOREBOX_DIR . '/inc/navigation.php';
require STOREBOX_DIR . '/inc/page-settings.php';
require STOREBOX_DIR . '/inc/elementor.php';
require STOREBOX_DIR . '/inc/storebox-core.php';

if ( is_admin() ) {
	require STOREBOX_DIR . '/inc/admin/class-storebox-plugins.php';
	require STOREBOX_DIR . '/inc/admin/class-storebox-admin.php';
}
