<?php
/**
 * Plugin Name:       Storebox Core
 * Plugin URI:        https://themeforest.net/
 * Description:       Units, locations, reservation requests, Storebox Elementor widgets and the demo importer for the Storebox theme.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Storebox
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       storebox-core
 * Domain Path:       /languages
 *
 * @package Storebox_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'STOREBOX_CORE_VERSION', '1.0.0' );
define( 'STOREBOX_CORE_FILE', __FILE__ );
define( 'STOREBOX_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'STOREBOX_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once STOREBOX_CORE_DIR . 'includes/functions.php';
require_once STOREBOX_CORE_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( '\Storebox_Core\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\Storebox_Core\Plugin', 'deactivate' ) );

\Storebox_Core\Plugin::instance();
