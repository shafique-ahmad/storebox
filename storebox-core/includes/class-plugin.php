<?php
/**
 * Plugin orchestrator.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the plugin's modules.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Returns the instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets up the modules.
	 */
	private function __construct() {
		$includes = array(
			'class-post-types.php',
			'class-settings.php',
			'class-components.php',
			'class-enquiries.php',
			'class-integrations.php',
			'class-privacy.php',
		);

		foreach ( $includes as $file ) {
			require_once STOREBOX_CORE_DIR . 'includes/' . $file;
		}

		Post_Types::init();
		Settings::init();
		Components::init();
		Enquiries::init();
		Integrations::init();
		Privacy::init();

		if ( is_admin() ) {
			require_once STOREBOX_CORE_DIR . 'includes/admin/class-meta-boxes.php';
			require_once STOREBOX_CORE_DIR . 'includes/admin/class-admin.php';
			require_once STOREBOX_CORE_DIR . 'includes/import/class-importer.php';
			require_once STOREBOX_CORE_DIR . 'includes/import/class-import-admin.php';

			Admin\Meta_Boxes::init();
			Admin\Admin::init();
			Import\Import_Admin::init();
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once STOREBOX_CORE_DIR . 'includes/import/class-importer.php';
			require_once STOREBOX_CORE_DIR . 'includes/import/class-cli.php';

			\WP_CLI::add_command( 'storebox demo', Import\CLI::class );
		}

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		add_action( 'elementor/init', array( $this, 'load_elementor' ) );
	}

	/**
	 * Loads translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'storebox-core', false, dirname( plugin_basename( STOREBOX_CORE_FILE ) ) . '/languages' );
	}

	/**
	 * Loads the Elementor module once Elementor is ready.
	 */
	public function load_elementor() {
		require_once STOREBOX_CORE_DIR . 'includes/elementor/class-module.php';
		Elementor\Module::init();
	}

	/**
	 * Activation: register content types and refresh rewrite rules.
	 */
	public static function activate() {
		require_once STOREBOX_CORE_DIR . 'includes/class-post-types.php';
		Post_Types::register();
		flush_rewrite_rules();

		if ( false === get_option( 'storebox_core_settings' ) ) {
			add_option( 'storebox_core_settings', array() );
		}
	}

	/**
	 * Deactivation: remove the plugin's rewrite rules.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
