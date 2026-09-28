<?php
/**
 * Appearance → Storebox: setup steps, plugins and useful links.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme admin screen and setup notice.
 */
class Storebox_Admin {

	/**
	 * Admin page slug.
	 */
	const PAGE = 'storebox';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'admin_notices', array( __CLASS__, 'setup_notice' ) );
		add_action( 'wp_ajax_storebox_dismiss_notice', array( __CLASS__, 'dismiss_notice' ) );
	}

	/**
	 * Registers the page under Appearance.
	 */
	public static function add_page() {
		add_theme_page(
			esc_html__( 'Storebox', 'storebox' ),
			esc_html__( 'Storebox', 'storebox' ),
			'edit_theme_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Enqueues admin assets on the Storebox screen and where the notice shows.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue( $hook ) {
		wp_register_style( 'storebox-admin', STOREBOX_URI . '/assets/css/admin.css', array(), storebox_asset_version( 'assets/css/admin.css' ) );
		wp_register_script( 'storebox-admin', STOREBOX_URI . '/assets/js/admin.js', array(), storebox_asset_version( 'assets/js/admin.js' ), true );

		wp_localize_script(
			'storebox-admin',
			'storeboxAdmin',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( Storebox_Plugins::NONCE ),
				'noticeNonce' => wp_create_nonce( 'storebox_dismiss_notice' ),
				'i18n'        => array(
					'activating' => esc_html__( 'Activating…', 'storebox' ),
					'done'       => esc_html__( 'Done', 'storebox' ),
					'failed'     => esc_html__( 'Something went wrong. Please try again, or use Plugins → Add New.', 'storebox' ),
				),
			)
		);

		if ( 'appearance_page_' . self::PAGE === $hook ) {
			wp_enqueue_style( 'storebox-admin' );
			wp_enqueue_script( 'storebox-admin' );
		}
	}

	/**
	 * Link to the Storebox Core demo importer, if available.
	 *
	 * @return string
	 */
	private static function demo_import_url() {
		return storebox_core_active() ? admin_url( 'themes.php?page=storebox-demo-import' ) : '';
	}

	/**
	 * Renders the page.
	 */
	public static function render_page() {
		$theme      = wp_get_theme( get_template() );
		$plugins    = Storebox_Plugins::get_plugins();
		$ready      = Storebox_Plugins::required_ready();
		$import_url = self::demo_import_url();
		?>
		<div class="wrap sb-admin">
			<div class="sb-admin__hero">
				<div>
					<h1><?php esc_html_e( 'Welcome to Storebox', 'storebox' ); ?> <span class="sb-admin__version"><?php echo esc_html( $theme->get( 'Version' ) ); ?></span></h1>
					<p><?php esc_html_e( 'Three steps and your storage website looks like the demo — then every page is yours to edit in Elementor.', 'storebox' ); ?></p>
				</div>
			</div>

			<ol class="sb-admin__steps">
				<li class="<?php echo $ready ? 'is-done' : 'is-current'; ?>">
					<h2><?php esc_html_e( 'Install the required plugins', 'storebox' ); ?></h2>
					<p><?php esc_html_e( 'Elementor builds the pages; Storebox Core adds units, locations, the Storebox widgets and the demo importer.', 'storebox' ); ?></p>
				</li>
				<li class="<?php echo $ready ? 'is-current' : ''; ?>">
					<h2><?php esc_html_e( 'Import a demo', 'storebox' ); ?></h2>
					<p><?php esc_html_e( 'Self Storage or Business Storage: pages, units, locations, posts, menus, images and design settings in one go.', 'storebox' ); ?></p>
					<?php if ( $import_url ) : ?>
						<p><a class="button button-primary" href="<?php echo esc_url( $import_url ); ?>"><?php esc_html_e( 'Open the demo importer', 'storebox' ); ?></a></p>
					<?php endif; ?>
				</li>
				<li>
					<h2><?php esc_html_e( 'Make it yours', 'storebox' ); ?></h2>
					<p><?php esc_html_e( 'Replace the logo, colours, texts and images, add your units and locations.', 'storebox' ); ?></p>
					<p class="sb-admin__links">
						<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=storebox' ) ); ?>"><?php esc_html_e( 'Customizer', 'storebox' ); ?></a>
						<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Menus', 'storebox' ); ?></a>
						<?php if ( storebox_core_active() ) : ?>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sb_unit' ) ); ?>"><?php esc_html_e( 'Units', 'storebox' ); ?></a>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sb_location' ) ); ?>"><?php esc_html_e( 'Locations', 'storebox' ); ?></a>
						<?php endif; ?>
					</p>
				</li>
			</ol>

			<h2 class="sb-admin__section-title"><?php esc_html_e( 'Plugins', 'storebox' ); ?></h2>
			<table class="widefat striped sb-admin__plugins">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Plugin', 'storebox' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Type', 'storebox' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Status', 'storebox' ); ?></th>
						<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Action', 'storebox' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $plugins as $slug => $plugin ) : ?>
						<?php $status = Storebox_Plugins::get_status( $slug ); ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
								<p class="description"><?php echo esc_html( $plugin['description'] ); ?></p>
							</td>
							<td><?php echo $plugin['required'] ? esc_html__( 'Required', 'storebox' ) : esc_html__( 'Recommended', 'storebox' ); ?></td>
							<td class="sb-admin__status sb-admin__status--<?php echo esc_attr( $status ); ?>">
								<?php
								$labels = array(
									'active'   => __( 'Active', 'storebox' ),
									'inactive' => __( 'Installed, not active', 'storebox' ),
									'update'   => __( 'Update available', 'storebox' ),
									'missing'  => __( 'Not installed', 'storebox' ),
								);
								echo esc_html( $labels[ $status ] );
								?>
							</td>
							<td class="sb-admin__action">
								<?php self::render_action( $slug, $plugin, $status ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="sb-admin__cards">
				<div class="sb-admin__card">
					<h2><?php esc_html_e( 'Documentation', 'storebox' ); ?></h2>
					<p><?php esc_html_e( 'The full guide — installation, demo import, Elementor templates, colours, typography, child themes and troubleshooting — is in the "documentation" folder of your download package. Open documentation/index.html in a browser.', 'storebox' ); ?></p>
				</div>
				<div class="sb-admin__card">
					<h2><?php esc_html_e( 'Child theme', 'storebox' ); ?></h2>
					<p><?php esc_html_e( 'Planning code changes? Install storebox-child.zip from the package first, so theme updates never overwrite your work.', 'storebox' ); ?></p>
				</div>
				<div class="sb-admin__card">
					<h2><?php esc_html_e( 'Support', 'storebox' ); ?></h2>
					<p><?php esc_html_e( 'Questions or a bug? Use the Support tab on the item page where you bought Storebox. Include your WordPress, Elementor and PHP versions.', 'storebox' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Prints the action button for a plugin row.
	 *
	 * @param string $slug        Plugin slug.
	 * @param array  $plugin      Plugin definition.
	 * @param string $status      Status.
	 */
	private static function render_action( $slug, $plugin, $status ) {
		if ( 'active' === $status ) {
			echo '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>';
			return;
		}

		if ( 'external' === $plugin['source'] ) {
			if ( 'missing' === $status ) {
				printf(
					'<a class="button" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s<span class="screen-reader-text"> %3$s</span></a>',
					esc_url( $plugin['url'] ),
					esc_html__( 'Learn more', 'storebox' ),
					esc_html__( '(opens in a new tab)', 'storebox' )
				);
				return;
			}
			if ( 'inactive' === $status && current_user_can( 'activate_plugins' ) ) {
				printf(
					'<a class="button" href="%1$s">%2$s</a>',
					esc_url( wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin['file'] ) ), 'activate-plugin_' . $plugin['file'] ) ),
					esc_html__( 'Activate', 'storebox' )
				);
			}
			return;
		}

		$actions = array(
			'missing'  => array( 'install', __( 'Install', 'storebox' ), 'install_plugins', __( 'Installing…', 'storebox' ) ),
			'update'   => array( 'install', __( 'Update', 'storebox' ), 'install_plugins', __( 'Updating…', 'storebox' ) ),
			'inactive' => array( 'activate', __( 'Activate', 'storebox' ), 'activate_plugins', __( 'Activating…', 'storebox' ) ),
		);

		if ( ! isset( $actions[ $status ] ) || ! current_user_can( $actions[ $status ][2] ) ) {
			return;
		}

		printf(
			'<button type="button" class="button %1$s" data-sb-plugin="%2$s" data-sb-action="%3$s" data-sb-activate-after="%4$s" data-sb-busy="%5$s">%6$s</button>',
			esc_attr( $plugin['required'] ? 'button-primary' : '' ),
			esc_attr( $slug ),
			esc_attr( $actions[ $status ][0] ),
			'missing' === $status ? '1' : '0',
			esc_attr( $actions[ $status ][3] ),
			esc_html( $actions[ $status ][1] )
		);
	}

	/**
	 * Notice shown until the required plugins are active.
	 */
	public static function setup_notice() {
		if ( ! current_user_can( 'install_plugins' ) || Storebox_Plugins::required_ready() ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen && 'appearance_page_' . self::PAGE === $screen->id ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), 'storebox_dismissed_setup', true ) ) {
			return;
		}

		wp_enqueue_script( 'storebox-admin' );
		?>
		<div class="notice notice-info is-dismissible storebox-setup-notice" data-sb-notice>
			<p>
				<strong><?php esc_html_e( 'Storebox is almost ready.', 'storebox' ); ?></strong>
				<?php esc_html_e( 'Install Elementor and Storebox Core, then import a demo.', 'storebox' ); ?>
				<a href="<?php echo esc_url( admin_url( 'themes.php?page=' . self::PAGE ) ); ?>"><?php esc_html_e( 'Open Storebox setup', 'storebox' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX: remembers that the user dismissed the setup notice.
	 */
	public static function dismiss_notice() {
		check_ajax_referer( 'storebox_dismiss_notice', 'nonce' );

		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( null, 403 );
		}

		update_user_meta( get_current_user_id(), 'storebox_dismissed_setup', 1 );
		wp_send_json_success();
	}
}

Storebox_Admin::init();
