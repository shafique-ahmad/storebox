<?php
/**
 * Required and recommended plugins, with one-click install and activation.
 *
 * Uses WordPress core's Plugin_Upgrader. Only the plugins listed in
 * Storebox_Plugins::get_plugins() can be installed or activated from here.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin manager for the Storebox setup screen.
 */
class Storebox_Plugins {

	/**
	 * Nonce action shared by the AJAX handlers.
	 */
	const NONCE = 'storebox_plugins';

	/**
	 * Hooks the AJAX handlers.
	 */
	public static function init() {
		add_action( 'wp_ajax_storebox_install_plugin', array( __CLASS__, 'ajax_install' ) );
		add_action( 'wp_ajax_storebox_activate_plugin', array( __CLASS__, 'ajax_activate' ) );
	}

	/**
	 * Plugins Storebox works with.
	 *
	 * Source: "repo" (WordPress.org), "bundled" (zip inside the theme) or
	 * "external" (premium, link only).
	 *
	 * @return array<string, array>
	 */
	public static function get_plugins() {
		$plugins = array(
			'elementor'     => array(
				'name'        => 'Elementor',
				'file'        => 'elementor/elementor.php',
				'source'      => 'repo',
				'required'    => true,
				'description' => __( 'The page builder every Storebox page is made with.', 'storebox' ),
			),
			'storebox-core' => array(
				'name'        => 'Storebox Core',
				'file'        => 'storebox-core/storebox-core.php',
				'source'      => 'bundled',
				'package'     => STOREBOX_DIR . '/plugins/storebox-core.zip',
				'version'     => STOREBOX_CORE_BUNDLED_VERSION,
				'required'    => true,
				'description' => __( 'Units, locations, reservation requests, the Storebox Elementor widgets and the demo importer.', 'storebox' ),
			),
			'elementor-pro' => array(
				'name'        => 'Elementor Pro',
				'file'        => 'elementor-pro/elementor-pro.php',
				'source'      => 'external',
				'url'         => 'https://elementor.com/pro/',
				'required'    => false,
				'description' => __( 'Optional. Adds the Theme Builder: the demo header, footer, blog, unit and location templates become editable in Elementor.', 'storebox' ),
			),
			'wp-mail-smtp'  => array(
				'name'        => 'WP Mail SMTP',
				'file'        => 'wp-mail-smtp/wp_mail_smtp.php',
				'source'      => 'repo',
				'required'    => false,
				'description' => __( 'Recommended. Sends reservation and contact emails through a real mail service so they do not land in spam.', 'storebox' ),
			),
		);

		/**
		 * Filters the plugins shown on the Storebox setup screen.
		 *
		 * @param array $plugins Plugin definitions keyed by slug.
		 */
		return apply_filters( 'storebox_plugins', $plugins );
	}

	/**
	 * Install/activation status of a plugin.
	 *
	 * @param string $slug Plugin slug.
	 * @return string "active", "update", "inactive" or "missing".
	 */
	public static function get_status( $slug ) {
		$plugins = self::get_plugins();

		if ( ! isset( $plugins[ $slug ] ) ) {
			return 'missing';
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin    = $plugins[ $slug ];
		$installed = get_plugins();

		if ( ! isset( $installed[ $plugin['file'] ] ) ) {
			return 'missing';
		}

		if ( 'bundled' === $plugin['source'] && ! empty( $plugin['version'] ) && version_compare( $installed[ $plugin['file'] ]['Version'], $plugin['version'], '<' ) ) {
			return 'update';
		}

		return is_plugin_active( $plugin['file'] ) ? 'active' : 'inactive';
	}

	/**
	 * Whether every required plugin is active.
	 *
	 * @return bool
	 */
	public static function required_ready() {
		foreach ( self::get_plugins() as $slug => $plugin ) {
			if ( ! empty( $plugin['required'] ) && 'active' !== self::get_status( $slug ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates an AJAX request and returns the requested plugin definition.
	 *
	 * @param string $capability Capability required for the action.
	 * @return array{0: string, 1: array}
	 */
	private static function verify_request( $capability ) {
		check_ajax_referer( self::NONCE, 'nonce' );

		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to manage plugins on this site.', 'storebox' ) ), 403 );
		}

		$slug    = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$plugins = self::get_plugins();

		if ( ! isset( $plugins[ $slug ] ) || 'external' === $plugins[ $slug ]['source'] ) {
			wp_send_json_error( array( 'message' => __( 'Unknown plugin.', 'storebox' ) ), 400 );
		}

		return array( $slug, $plugins[ $slug ] );
	}

	/**
	 * AJAX: installs (or updates, for the bundled plugin) a plugin.
	 */
	public static function ajax_install() {
		list( $slug, $plugin ) = self::verify_request( 'install_plugins' );

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';

		$overwrite = false;

		if ( 'bundled' === $plugin['source'] ) {
			$package = $plugin['package'];
			if ( ! file_exists( $package ) ) {
				wp_send_json_error( array( 'message' => __( 'The bundled plugin file is missing from the theme folder. Upload storebox-core.zip from your download package under Plugins → Add New.', 'storebox' ) ) );
			}
			$overwrite = 'update' === self::get_status( $slug );
		} else {
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);

			if ( is_wp_error( $api ) || empty( $api->download_link ) ) {
				wp_send_json_error( array( 'message' => __( 'WordPress.org could not be reached. Install the plugin from Plugins → Add New instead.', 'storebox' ) ) );
			}

			$package = $api->download_link;
		}

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $package, array( 'overwrite_package' => $overwrite ) );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		if ( is_wp_error( $skin->result ) ) {
			wp_send_json_error( array( 'message' => $skin->result->get_error_message() ) );
		}

		if ( $skin->get_errors()->has_errors() ) {
			wp_send_json_error( array( 'message' => $skin->get_error_messages() ) );
		}

		if ( ! $result ) {
			wp_send_json_error( array( 'message' => __( 'The plugin could not be installed automatically. Your server may need FTP details — please install it from Plugins → Add New.', 'storebox' ) ) );
		}

		wp_send_json_success( array( 'status' => self::get_status( $slug ) ) );
	}

	/**
	 * AJAX: activates an installed plugin.
	 */
	public static function ajax_activate() {
		list( $slug, $plugin ) = self::verify_request( 'activate_plugins' );

		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$result = activate_plugin( $plugin['file'] );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Keep the user on the Storebox setup screen instead of Elementor's onboarding redirect.
		if ( 'elementor' === $slug ) {
			delete_transient( 'elementor_activation_redirect' );
		}

		wp_send_json_success( array( 'status' => self::get_status( $slug ) ) );
	}
}

Storebox_Plugins::init();
