<?php
/**
 * Demo importer screen: Appearance → Demo Import.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Import;

defined( 'ABSPATH' ) || exit;

/**
 * Admin screen and AJAX endpoints of the demo importer.
 */
class Import_Admin {

	const PAGE  = 'storebox-demo-import';
	const NONCE = 'storebox_demo_import';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_ajax_storebox_demo_plan', array( __CLASS__, 'ajax_plan' ) );
		add_action( 'wp_ajax_storebox_demo_step', array( __CLASS__, 'ajax_step' ) );
		add_action( 'wp_ajax_storebox_demo_remove', array( __CLASS__, 'ajax_remove' ) );
	}

	/**
	 * Registers the screen.
	 */
	public static function menu() {
		add_theme_page(
			__( 'Storebox Demo Import', 'storebox-core' ),
			__( 'Demo Import', 'storebox-core' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Screen assets.
	 *
	 * @param string $hook Screen hook.
	 */
	public static function assets( $hook ) {
		if ( 'appearance_page_' . self::PAGE !== $hook ) {
			return;
		}

		wp_enqueue_style( 'storebox-core-import', STOREBOX_CORE_URL . 'assets/css/import.css', array(), STOREBOX_CORE_VERSION );
		wp_enqueue_script( 'storebox-core-import', STOREBOX_CORE_URL . 'assets/js/import.js', array(), STOREBOX_CORE_VERSION, true );
		wp_localize_script(
			'storebox-core-import',
			'storeboxImport',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE ),
				'i18n'    => array(
					'confirmReplace' => __( 'The demo content imported before — including any changes you made to it — will be deleted first. Continue?', 'storebox-core' ),
					'confirmRemove'  => __( 'Delete all imported demo content and restore your previous settings? Changes you made to demo pages will be lost. This cannot be undone.', 'storebox-core' ),
					'starting'       => __( 'Starting…', 'storebox-core' ),
					'done'           => __( 'The demo was imported.', 'storebox-core' ),
					'removing'       => __( 'Removing demo content…', 'storebox-core' ),
					'removed'        => __( 'Demo content removed. Reloading…', 'storebox-core' ),
					'failed'         => __( 'The import stopped:', 'storebox-core' ),
					/* translators: %s: HTTP status code. */
					'serverError'    => __( 'The server did not answer properly (HTTP %s). This is usually a time or memory limit. Click “Retry” to continue where it stopped.', 'storebox-core' ),
					'networkError'   => __( 'The connection was interrupted. Click “Retry” to continue where it stopped.', 'storebox-core' ),
					'chooseDemo'     => __( 'Choose a demo first.', 'storebox-core' ),
				),
			)
		);
	}

	/**
	 * Requirement checks shown on the screen.
	 *
	 * @return array List of { label, status: ok|warn|fail, note }.
	 */
	private static function requirements() {
		$checks = array();
		$theme  = wp_get_theme();

		$checks[] = array(
			'label'  => __( 'Storebox theme', 'storebox-core' ),
			'status' => 'storebox' === get_template() ? 'ok' : 'warn',
			'note'   => 'storebox' === get_template()
				/* translators: %s: theme name. */
				? sprintf( __( 'Active: %s', 'storebox-core' ), $theme->get( 'Name' ) )
				: __( 'Activate the Storebox theme (or its child theme) for the demo to look as intended.', 'storebox-core' ),
		);

		$checks[] = array(
			'label'  => __( 'Elementor', 'storebox-core' ),
			'status' => Importer::elementor_active() ? 'ok' : 'fail',
			'note'   => Importer::elementor_active()
				/* translators: %s: version number. */
				? sprintf( __( 'Version %s', 'storebox-core' ), defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '?' )
				: __( 'Required: the demo pages are built with Elementor. Install it from Appearance → Storebox.', 'storebox-core' ),
		);

		$checks[] = array(
			'label'  => __( 'Elementor Pro', 'storebox-core' ),
			'status' => Importer::pro_active() ? 'ok' : 'warn',
			'note'   => Importer::pro_active()
				? __( 'The Theme Builder templates (header, footer, single, archive, search, 404) can be imported.', 'storebox-core' )
				: __( 'Optional. Without it the theme’s own header, footer and templates are used — they share the same design.', 'storebox-core' ),
		);

		$uploads  = wp_upload_dir( null, false );
		$writable = empty( $uploads['error'] ) && wp_is_writable( $uploads['basedir'] );
		$checks[] = array(
			'label'  => __( 'Uploads folder', 'storebox-core' ),
			'status' => $writable ? 'ok' : 'fail',
			'note'   => $writable ? __( 'Writable', 'storebox-core' ) : __( 'Not writable: images cannot be imported.', 'storebox-core' ),
		);

		$memory   = wp_convert_hr_to_bytes( (string) ini_get( 'memory_limit' ) );
		$checks[] = array(
			'label'  => __( 'PHP memory limit', 'storebox-core' ),
			'status' => ( $memory < 0 || $memory >= 128 * MB_IN_BYTES ) ? 'ok' : 'warn',
			'note'   => $memory < 0 ? __( 'Unlimited', 'storebox-core' ) : size_format( $memory ) . ( $memory < 128 * MB_IN_BYTES ? ' — ' . __( '128 MB or more is recommended.', 'storebox-core' ) : '' ),
		);

		return $checks;
	}

	/**
	 * What a demo contains, for its card.
	 *
	 * @param string $slug Demo slug.
	 * @return string[]
	 */
	private static function summary( $slug ) {
		$data = Importer::data( $slug );
		if ( is_wp_error( $data ) ) {
			return array();
		}

		$counts = array();
		foreach ( $data['posts'] as $item ) {
			$type            = isset( $item['type'] ) ? $item['type'] : '';
			$counts[ $type ] = isset( $counts[ $type ] ) ? $counts[ $type ] + 1 : 1;
		}

		$lines = array();
		$map   = array(
			/* translators: %d: number of pages. */
			'page'        => _n_noop( '%d page', '%d pages', 'storebox-core' ),
			/* translators: %d: number of units. */
			'sb_unit'     => _n_noop( '%d storage unit', '%d storage units', 'storebox-core' ),
			/* translators: %d: number of locations. */
			'sb_location' => _n_noop( '%d location', '%d locations', 'storebox-core' ),
			/* translators: %d: number of posts. */
			'post'        => _n_noop( '%d blog post', '%d blog posts', 'storebox-core' ),
		);

		foreach ( $map as $type => $noop ) {
			if ( ! empty( $counts[ $type ] ) ) {
				$lines[] = sprintf( translate_nooped_plural( $noop, $counts[ $type ], 'storebox-core' ), $counts[ $type ] );
			}
		}

		if ( $data['templates'] ) {
			/* translators: %d: number of templates. */
			$lines[] = sprintf( _n( '%d Theme Builder template', '%d Theme Builder templates', count( $data['templates'] ), 'storebox-core' ), count( $data['templates'] ) );
		}

		return $lines;
	}

	/**
	 * Renders the screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$demos    = Importer::demos();
		$imported = Importer::imported();
		$has      = Importer::has_content();
		$checks   = self::requirements();
		$blocked  = ! Importer::elementor_active();
		$selected = $imported && isset( $demos[ $imported['demo'] ] ) ? $imported['demo'] : key( $demos );
		?>
		<div class="wrap sb-import">
			<h1><?php esc_html_e( 'Storebox Demo Import', 'storebox-core' ); ?></h1>
			<p class="sb-import__lead"><?php esc_html_e( 'Import one of the Storebox demos to get a complete site — pages, units, locations, blog posts, menus, images and design settings — that you can then edit. Your existing content is kept.', 'storebox-core' ); ?></p>

			<noscript><div class="notice notice-error"><p><?php esc_html_e( 'The demo importer needs JavaScript.', 'storebox-core' ); ?></p></div></noscript>

			<?php if ( $imported && isset( $demos[ $imported['demo'] ] ) ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php
						printf(
							/* translators: 1: demo name, 2: date. */
							esc_html__( '“%1$s” was imported on %2$s.', 'storebox-core' ),
							esc_html( $demos[ $imported['demo'] ]['name'] ),
							esc_html( wp_date( get_option( 'date_format' ), (int) $imported['time'] ) )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<div class="sb-import__grid">
				<div class="sb-import__main">
					<?php if ( ! $demos ) : ?>
						<div class="notice notice-error inline"><p><?php esc_html_e( 'No demo packages were found in the plugin folder.', 'storebox-core' ); ?></p></div>
					<?php else : ?>
						<form id="sb-import-form" class="sb-import__form">
							<fieldset class="sb-import__demos">
								<legend class="sb-import__legend"><?php esc_html_e( '1. Choose a demo', 'storebox-core' ); ?></legend>
								<?php foreach ( $demos as $slug => $demo ) : ?>
									<label class="sb-demo">
										<input type="radio" name="demo" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $selected, $slug ); ?>>
										<span class="sb-demo__card">
											<span class="sb-demo__preview">
												<?php if ( $demo['preview'] ) : ?>
													<img src="<?php echo esc_url( Importer::url() . $slug . '/' . ltrim( $demo['preview'], '/' ) ); ?>" alt="" loading="lazy" width="600" height="400">
												<?php endif; ?>
											</span>
											<span class="sb-demo__body">
												<span class="sb-demo__name">
													<?php echo esc_html( $demo['name'] ); ?>
													<?php if ( $imported && $imported['demo'] === $slug ) : ?>
														<span class="sb-demo__badge"><?php esc_html_e( 'Imported', 'storebox-core' ); ?></span>
													<?php endif; ?>
												</span>
												<?php if ( $demo['description'] ) : ?>
													<span class="sb-demo__desc"><?php echo esc_html( $demo['description'] ); ?></span>
												<?php endif; ?>
												<?php $lines = self::summary( $slug ); ?>
												<?php if ( $lines ) : ?>
													<span class="sb-demo__meta"><?php echo esc_html( implode( ' · ', $lines ) ); ?></span>
												<?php endif; ?>
											</span>
										</span>
									</label>
								<?php endforeach; ?>
							</fieldset>

							<fieldset class="sb-import__options">
								<legend class="sb-import__legend"><?php esc_html_e( '2. Choose what to import', 'storebox-core' ); ?></legend>
								<label><input type="checkbox" name="options[content]" value="1" checked> <?php esc_html_e( 'Content: pages, units, locations, blog posts and images', 'storebox-core' ); ?></label>
								<label>
									<input type="checkbox" name="options[templates]" value="1" <?php checked( Importer::pro_active() ); ?> <?php disabled( ! Importer::pro_active() ); ?>>
									<?php esc_html_e( 'Theme Builder templates: header, footer, single post, archive, search, 404, unit and location pages', 'storebox-core' ); ?>
									<?php if ( ! Importer::pro_active() ) : ?>
										<em><?php esc_html_e( '(requires Elementor Pro)', 'storebox-core' ); ?></em>
									<?php endif; ?>
								</label>
								<label><input type="checkbox" name="options[menus]" value="1" checked> <?php esc_html_e( 'Menus (assigned to the header and footer)', 'storebox-core' ); ?></label>
								<label><input type="checkbox" name="options[settings]" value="1" checked> <?php esc_html_e( 'Settings: front page, Customizer options, Elementor global colors and fonts, Storebox settings', 'storebox-core' ); ?></label>
								<?php if ( $has ) : ?>
									<label class="sb-import__replace"><input type="checkbox" name="options[replace]" value="1" checked> <?php esc_html_e( 'Delete the previously imported demo content first (recommended)', 'storebox-core' ); ?></label>
								<?php endif; ?>
							</fieldset>

							<p class="sb-import__actions">
								<button type="submit" class="button button-primary button-hero" <?php disabled( $blocked ); ?>><?php esc_html_e( 'Import demo', 'storebox-core' ); ?></button>
								<?php if ( $blocked ) : ?>
									<span class="sb-import__blocked"><?php esc_html_e( 'Activate Elementor to import a demo.', 'storebox-core' ); ?></span>
								<?php endif; ?>
							</p>
						</form>

						<div id="sb-import-progress" class="sb-import__progress" hidden>
							<div class="sb-import__status" role="status" aria-live="polite"></div>
							<div class="sb-import__bar" aria-hidden="true"><span></span></div>
							<details class="sb-import__log-wrap">
								<summary><?php esc_html_e( 'Details', 'storebox-core' ); ?></summary>
								<ol class="sb-import__log"></ol>
							</details>
							<p class="sb-import__retry" hidden><button type="button" class="button"><?php esc_html_e( 'Retry', 'storebox-core' ); ?></button></p>
							<div class="sb-import__done" hidden>
								<p>
									<a class="button button-primary" data-link="home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View your site', 'storebox-core' ); ?></a>
									<a class="button" data-link="edit" href="#" hidden><?php esc_html_e( 'Edit the home page with Elementor', 'storebox-core' ); ?></a>
									<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'storebox-core' ); ?></a>
								</p>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<aside class="sb-import__side">
					<div class="sb-import__box">
						<h2><?php esc_html_e( 'Requirements', 'storebox-core' ); ?></h2>
						<ul class="sb-import__checks">
							<?php foreach ( $checks as $check ) : ?>
								<li class="is-<?php echo esc_attr( $check['status'] ); ?>">
									<span class="sb-import__check-icon" aria-hidden="true"></span>
									<span>
										<strong><?php echo esc_html( $check['label'] ); ?></strong>
										<span class="screen-reader-text">
											<?php
											$labels = array(
												'ok'   => __( 'OK', 'storebox-core' ),
												'warn' => __( 'Recommended', 'storebox-core' ),
												'fail' => __( 'Required', 'storebox-core' ),
											);
											echo esc_html( $labels[ $check['status'] ] );
											?>
										</span>
										<br><?php echo esc_html( $check['note'] ); ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="sb-import__box">
						<h2><?php esc_html_e( 'Good to know', 'storebox-core' ); ?></h2>
						<ul class="sb-import__notes">
							<li><?php esc_html_e( 'Importing again updates the demo items instead of duplicating them.', 'storebox-core' ); ?></li>
							<li><?php esc_html_e( 'The demo images are original illustrations you may use on your live site.', 'storebox-core' ); ?></li>
							<li><?php esc_html_e( 'Replace the demo addresses, phone numbers and prices with your own before launch.', 'storebox-core' ); ?></li>
						</ul>
					</div>

					<?php if ( $has ) : ?>
						<div class="sb-import__box sb-import__remove">
							<h2><?php esc_html_e( 'Remove demo content', 'storebox-core' ); ?></h2>
							<p><?php esc_html_e( 'Deletes the imported pages, units, locations, posts, menus and templates, and restores the settings you had before the first import.', 'storebox-core' ); ?></p>
							<label><input type="checkbox" id="sb-remove-media" checked> <?php esc_html_e( 'Also delete the imported images', 'storebox-core' ); ?></label>
							<p><button type="button" class="button button-link-delete" id="sb-remove-demo"><?php esc_html_e( 'Remove demo content', 'storebox-core' ); ?></button></p>
							<p class="sb-import__remove-status" role="status" aria-live="polite"></p>
						</div>
					<?php endif; ?>
				</aside>
			</div>
		</div>
		<?php
	}

	/**
	 * Verifies an AJAX request.
	 */
	private static function verify() {
		check_ajax_referer( self::NONCE, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to import demo content.', 'storebox-core' ) ), 403 );
		}
	}

	/**
	 * Request input: demo slug and options.
	 *
	 * @return array{0: string, 1: array}
	 */
	private static function input() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified in verify().
		$demo    = isset( $_POST['demo'] ) ? sanitize_key( wp_unslash( $_POST['demo'] ) ) : '';
		$options = isset( $_POST['options'] ) && is_array( $_POST['options'] ) ? map_deep( wp_unslash( $_POST['options'] ), 'sanitize_text_field' ) : array();
		// phpcs:enable

		$demos = Importer::demos();
		if ( ! isset( $demos[ $demo ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown demo.', 'storebox-core' ) ), 400 );
		}

		return array( $demo, Importer::options( $options ) );
	}

	/**
	 * AJAX: the steps of an import.
	 */
	public static function ajax_plan() {
		self::verify();
		list( $demo, $options ) = self::input();

		$plan = Importer::plan( $demo, $options );
		if ( is_wp_error( $plan ) ) {
			wp_send_json_error( array( 'message' => $plan->get_error_message() ), 400 );
		}

		wp_send_json_success( array( 'steps' => $plan ) );
	}

	/**
	 * AJAX: runs one request of a step.
	 */
	public static function ajax_step() {
		self::verify();
		list( $demo, $options ) = self::input();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified in verify().
		$step  = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
		$chunk = isset( $_POST['chunk'] ) ? absint( wp_unslash( $_POST['chunk'] ) ) : 0;
		// phpcs:enable

		$plan = Importer::plan( $demo, $options );
		if ( is_wp_error( $plan ) || ! in_array( $step, wp_list_pluck( $plan, 'id' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown import step.', 'storebox-core' ) ), 400 );
		}

		$result = Importer::run( $demo, $step, $chunk, $options );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 );
		}

		if ( 'finish' === $step && $result['done'] ) {
			$front = (int) get_option( 'page_on_front' );
			if ( $front && Importer::elementor_active() && get_post_meta( $front, '_elementor_edit_mode', true ) ) {
				$result['links'] = array(
					'edit' => add_query_arg(
						array(
							'post'   => $front,
							'action' => 'elementor',
						),
						admin_url( 'post.php' )
					),
				);
			}
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX: removes demo content, one batch per request.
	 */
	public static function ajax_remove() {
		self::verify();

		$keep_media = empty( $_POST['media'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in verify().

		wp_send_json_success( Importer::remove( $keep_media ) );
	}
}
