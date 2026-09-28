<?php
/**
 * Demo importer.
 *
 * Reads a demo package bundled with the plugin (demo/{slug}/) and imports it
 * in small steps, so that each request stays well inside PHP time limits:
 *
 *   prepare → cleanup → media → terms → posts → relations → menus →
 *   elementor → settings → finish
 *
 * Every imported post, term, menu and image is tagged, so importing again
 * updates the same items instead of duplicating them, and "Remove demo
 * content" deletes exactly what was imported and restores the settings the
 * site had before the first import.
 *
 * Demo files may contain placeholders that are resolved on import:
 * {{post:key}} and {{image:key}} (IDs), {{sid:key}} (ID as a string),
 * {{url:key}} and {{image_url:key}} (URLs), {{images:a,b}} (ID list), {{term:taxonomy/key}}, {{menu:key}}
 * (menu slug), {{menu_id:key}} and {{home}}.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Import;

defined( 'ABSPATH' ) || exit;

/**
 * Imports and removes demo content.
 */
class Importer {

	const STATE_OPTION  = 'storebox_core_import_state';
	const BACKUP_OPTION = 'storebox_core_import_backup';
	const DONE_OPTION   = 'storebox_core_imported';

	const DEMO_META  = '_storebox_demo';
	const KEY_META   = '_storebox_demo_key';
	const IMAGE_META = '_storebox_demo_image';

	const MEDIA_BATCH     = 3;
	const POSTS_BATCH     = 12;
	const ELEMENTOR_BATCH = 4;
	const REMOVE_BATCH    = 25;

	/**
	 * Options a demo may set (anything else in a demo file is ignored).
	 *
	 * @var string[]
	 */
	const ALLOWED_OPTIONS = array(
		'show_on_front',
		'page_on_front',
		'page_for_posts',
		'posts_per_page',
		'elementor_disable_color_schemes',
		'elementor_disable_typography_schemes',
		'elementor_experiment-container',
		'elementor_load_fa4_shim',
		'elementor_font_display',
	);

	/**
	 * Decoded demo data, per demo.
	 *
	 * @var array
	 */
	private static $data = array();

	/**
	 * Key → ID maps used to resolve placeholders.
	 *
	 * @var array
	 */
	private static $map = array(
		'posts'  => array(),
		'terms'  => array(),
		'images' => array(),
		'menus'  => array(),
	);

	/* ---------------------------------------------------------------------
	 * Demo packages
	 * ------------------------------------------------------------------ */

	/**
	 * Directory holding the demo packages.
	 *
	 * @return string
	 */
	public static function dir() {
		/**
		 * Filters the directory the demo packages are read from.
		 *
		 * @param string $dir Absolute path.
		 */
		return trailingslashit( apply_filters( 'storebox_core/demo_dir', STOREBOX_CORE_DIR . 'demo' ) );
	}

	/**
	 * URL of the demo directory (for preview images).
	 *
	 * @return string
	 */
	public static function url() {
		/**
		 * Filters the URL of the demo directory.
		 *
		 * @param string $url URL.
		 */
		return trailingslashit( apply_filters( 'storebox_core/demo_url', STOREBOX_CORE_URL . 'demo' ) );
	}

	/**
	 * Reads a JSON file inside the demo directory.
	 *
	 * @param string $relative Path relative to the demo directory.
	 * @return array|\WP_Error
	 */
	private static function read_json( $relative ) {
		$base = realpath( self::dir() );
		$file = realpath( self::dir() . ltrim( $relative, '/' ) );

		if ( ! $base || ! $file || 0 !== strpos( $file, $base . DIRECTORY_SEPARATOR ) || ! is_readable( $file ) ) {
			/* translators: %s: file name. */
			return new \WP_Error( 'storebox_demo_file', sprintf( __( 'Demo file not found: %s', 'storebox-core' ), $relative ) );
		}

		$json = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file inside the plugin.

		if ( ! is_array( $json ) ) {
			/* translators: %s: file name. */
			return new \WP_Error( 'storebox_demo_json', sprintf( __( 'Demo file is not valid JSON: %s', 'storebox-core' ), $relative ) );
		}

		return $json;
	}

	/**
	 * Available demos, keyed by slug.
	 *
	 * @return array
	 */
	public static function demos() {
		$demos = array();
		$files = glob( self::dir() . '*/demo.json' );

		foreach ( $files ? $files : array() as $file ) {
			$slug     = basename( dirname( $file ) );
			$manifest = self::read_json( $slug . '/demo.json' );

			if ( is_wp_error( $manifest ) || sanitize_key( $slug ) !== $slug ) {
				continue;
			}

			$demos[ $slug ] = wp_parse_args(
				$manifest,
				array(
					'name'        => $slug,
					'description' => '',
					'preview'     => '',
					'preset'      => '',
					'order'       => 10,
				)
			);
			$demos[ $slug ]['slug'] = $slug;
		}

		uasort(
			$demos,
			static function ( $a, $b ) {
				return (int) $a['order'] - (int) $b['order'];
			}
		);

		/**
		 * Filters the available demos.
		 *
		 * @param array $demos Demos keyed by slug.
		 */
		return apply_filters( 'storebox_core/demos', $demos );
	}

	/**
	 * Content of a demo.
	 *
	 * @param string $slug Demo slug.
	 * @return array|\WP_Error
	 */
	public static function data( $slug ) {
		if ( isset( self::$data[ $slug ] ) ) {
			return self::$data[ $slug ];
		}

		$demos = self::demos();
		if ( ! isset( $demos[ $slug ] ) ) {
			return new \WP_Error( 'storebox_demo_unknown', __( 'Unknown demo.', 'storebox-core' ) );
		}

		$data = self::read_json( $slug . '/content.json' );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		self::$data[ $slug ] = wp_parse_args(
			$data,
			array(
				'images'     => array(),
				'terms'      => array(),
				'posts'      => array(),
				'templates'  => array(),
				'menus'      => array(),
				'theme_mods' => array(),
				'options'    => array(),
				'settings'   => array(),
				'kit'        => array(),
				'sidebars'   => array(),
			)
		);

		return self::$data[ $slug ];
	}

	/* ---------------------------------------------------------------------
	 * Environment
	 * ------------------------------------------------------------------ */

	/**
	 * Whether Elementor is loaded.
	 *
	 * @return bool
	 */
	public static function elementor_active() {
		return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Whether Elementor Pro (Theme Builder) is loaded.
	 *
	 * @return bool
	 */
	public static function pro_active() {
		return self::elementor_active() && defined( 'ELEMENTOR_PRO_VERSION' );
	}

	/**
	 * The demo imported last, if any.
	 *
	 * @return array|null { demo, time, version }
	 */
	public static function imported() {
		$done = get_option( self::DONE_OPTION );

		return is_array( $done ) && ! empty( $done['demo'] ) ? $done : null;
	}

	/**
	 * Whether any tagged demo content exists.
	 *
	 * @return bool
	 */
	public static function has_content() {
		$found = get_posts(
			array(
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => self::DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin-only lookup.
				'no_found_rows'  => true,
			)
		);

		return ! empty( $found ) || null !== self::imported();
	}

	/* ---------------------------------------------------------------------
	 * Plan and state
	 * ------------------------------------------------------------------ */

	/**
	 * Normalizes import options.
	 *
	 * @param array $options Raw options.
	 * @return array
	 */
	public static function options( $options ) {
		$options = is_array( $options ) ? $options : array();
		$clean   = array();

		foreach ( array( 'content', 'templates', 'menus', 'settings', 'replace' ) as $key ) {
			$clean[ $key ] = ! empty( $options[ $key ] ) && 'false' !== $options[ $key ];
		}

		return $clean;
	}

	/**
	 * Steps of an import, with an estimate of the requests each needs.
	 *
	 * @param string $slug    Demo slug.
	 * @param array  $options Options.
	 * @return array|\WP_Error List of { id, label, chunks }.
	 */
	public static function plan( $slug, $options ) {
		$data = self::data( $slug );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$options = self::options( $options );
		$steps   = array(
			array(
				'id'     => 'prepare',
				'label'  => __( 'Preparing', 'storebox-core' ),
				'chunks' => 1,
			),
		);

		if ( $options['replace'] ) {
			$steps[] = array(
				'id'     => 'cleanup',
				'label'  => __( 'Removing the previous demo', 'storebox-core' ),
				'chunks' => 2,
			);
		}

		if ( $options['content'] ) {
			$steps[] = array(
				'id'     => 'media',
				'label'  => __( 'Importing images', 'storebox-core' ),
				'chunks' => max( 1, (int) ceil( count( $data['images'] ) / self::MEDIA_BATCH ) ),
			);
			$steps[] = array(
				'id'     => 'terms',
				'label'  => __( 'Importing categories and unit types', 'storebox-core' ),
				'chunks' => 1,
			);
			$steps[] = array(
				'id'     => 'posts',
				'label'  => __( 'Importing units, locations, posts and pages', 'storebox-core' ),
				'chunks' => max( 1, (int) ceil( count( $data['posts'] ) / self::POSTS_BATCH ) ),
			);
			$steps[] = array(
				'id'     => 'relations',
				'label'  => __( 'Linking content', 'storebox-core' ),
				'chunks' => 1,
			);
		}

		if ( $options['menus'] ) {
			$steps[] = array(
				'id'     => 'menus',
				'label'  => __( 'Creating menus', 'storebox-core' ),
				'chunks' => 1,
			);
		}

		if ( $options['content'] || $options['templates'] ) {
			$steps[] = array(
				'id'     => 'elementor',
				'label'  => __( 'Building pages and templates', 'storebox-core' ),
				'chunks' => max( 1, (int) ceil( count( self::elementor_queue( $data, $options ) ) / self::ELEMENTOR_BATCH ) ),
			);
		}

		if ( $options['settings'] ) {
			$steps[] = array(
				'id'     => 'settings',
				'label'  => __( 'Applying settings, colors and fonts', 'storebox-core' ),
				'chunks' => 1,
			);
		}

		$steps[] = array(
			'id'     => 'finish',
			'label'  => __( 'Finishing', 'storebox-core' ),
			'chunks' => 1,
		);

		return $steps;
	}

	/**
	 * Current import state.
	 *
	 * @return array
	 */
	private static function state() {
		$state = get_option( self::STATE_OPTION );

		return is_array( $state ) ? $state : array();
	}

	/**
	 * Saves the import state.
	 *
	 * @param array $state State.
	 */
	private static function save_state( $state ) {
		update_option( self::STATE_OPTION, $state, false );
	}

	/**
	 * Rebuilds the key → ID maps from the tags on imported items.
	 *
	 * @param string $slug Demo slug.
	 */
	private static function load_map( $slug ) {
		global $wpdb;

		self::$map = array(
			'posts'  => array(),
			'terms'  => array(),
			'images' => array(),
			'menus'  => array(),
		);

		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- One-off admin lookups of the importer's own tags.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT k.post_id, k.meta_value AS item_key FROM {$wpdb->postmeta} k
				INNER JOIN {$wpdb->postmeta} d ON d.post_id = k.post_id AND d.meta_key = %s AND d.meta_value = %s
				INNER JOIN {$wpdb->posts} p ON p.ID = k.post_id
				WHERE k.meta_key = %s",
				self::DEMO_META,
				$slug,
				self::KEY_META
			)
		);
		foreach ( $rows as $row ) {
			self::$map['posts'][ $row->item_key ] = (int) $row->post_id;
		}

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT m.post_id, m.meta_value AS item_key FROM {$wpdb->postmeta} m
				INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id AND p.post_type = 'attachment'
				WHERE m.meta_key = %s",
				self::IMAGE_META
			)
		);
		foreach ( $rows as $row ) {
			self::$map['images'][ $row->item_key ] = (int) $row->post_id;
		}

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT k.term_id, k.meta_value AS item_key, t.taxonomy FROM {$wpdb->termmeta} k
				INNER JOIN {$wpdb->termmeta} d ON d.term_id = k.term_id AND d.meta_key = %s AND d.meta_value = %s
				INNER JOIN {$wpdb->term_taxonomy} t ON t.term_id = k.term_id
				WHERE k.meta_key = %s",
				self::DEMO_META,
				$slug,
				self::KEY_META
			)
		);
		// phpcs:enable
		foreach ( $rows as $row ) {
			if ( 'nav_menu' === $row->taxonomy ) {
				self::$map['menus'][ $row->item_key ] = (int) $row->term_id;
			} else {
				self::$map['terms'][ $row->taxonomy ][ $row->item_key ] = (int) $row->term_id;
			}
		}

		// Terms that already existed are reused, not tagged: they are kept in the state.
		$state = self::state();
		if ( ! empty( $state['reused_terms'] ) && is_array( $state['reused_terms'] ) ) {
			foreach ( $state['reused_terms'] as $taxonomy => $terms ) {
				foreach ( (array) $terms as $key => $term_id ) {
					if ( ! isset( self::$map['terms'][ $taxonomy ][ $key ] ) && term_exists( (int) $term_id, $taxonomy ) ) {
						self::$map['terms'][ $taxonomy ][ $key ] = (int) $term_id;
					}
				}
			}
		}
	}

	/* ---------------------------------------------------------------------
	 * Running an import
	 * ------------------------------------------------------------------ */

	/**
	 * Runs one request's worth of a step.
	 *
	 * @param string $slug    Demo slug.
	 * @param string $step    Step ID.
	 * @param int    $chunk   Zero-based request number within the step.
	 * @param array  $options Options.
	 * @return array|\WP_Error { done: bool, log: string[] }
	 */
	public static function run( $slug, $step, $chunk, $options ) {
		$data = self::data( $slug );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$options = self::options( $options );
		$chunk   = max( 0, (int) $chunk );
		$log     = array();

		self::raise_limits();

		if ( 'prepare' !== $step ) {
			$state = self::state();
			if ( empty( $state['demo'] ) || $state['demo'] !== $slug ) {
				return new \WP_Error( 'storebox_demo_state', __( 'The import was interrupted. Please start it again.', 'storebox-core' ) );
			}
			self::load_map( $slug );
		}

		switch ( $step ) {
			case 'prepare':
				$done = self::step_prepare( $slug, $data, $options, $log );
				break;
			case 'cleanup':
				$done = self::remove_batch( true, $log );
				break;
			case 'media':
				$done = self::step_media( $data, $chunk, $log );
				break;
			case 'terms':
				$done = self::step_terms( $slug, $data, $log );
				break;
			case 'posts':
				$done = self::step_posts( $slug, $data, $chunk, $log );
				break;
			case 'relations':
				$done = self::step_relations( $data, $log );
				break;
			case 'menus':
				$done = self::step_menus( $slug, $data, $log );
				break;
			case 'elementor':
				$done = self::step_elementor( $slug, $data, $options, $chunk, $log );
				break;
			case 'settings':
				$done = self::step_settings( $data, $log );
				break;
			case 'finish':
				$done = self::step_finish( $slug, $log );
				break;
			default:
				return new \WP_Error( 'storebox_demo_step', __( 'Unknown import step.', 'storebox-core' ) );
		}

		if ( is_wp_error( $done ) ) {
			return $done;
		}

		return array(
			'done' => (bool) $done,
			'log'  => $log,
		);
	}

	/**
	 * Runs a whole import in one go (WP-CLI).
	 *
	 * @param string        $slug    Demo slug.
	 * @param array         $options Options.
	 * @param callable|null $logger  Receives each log line.
	 * @return true|\WP_Error
	 */
	public static function import_all( $slug, $options, $logger = null ) {
		$plan = self::plan( $slug, $options );
		if ( is_wp_error( $plan ) ) {
			return $plan;
		}

		foreach ( $plan as $step ) {
			$chunk = 0;
			do {
				$result = self::run( $slug, $step['id'], $chunk, $options );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
				if ( $logger ) {
					foreach ( $result['log'] as $line ) {
						call_user_func( $logger, $line );
					}
				}
				++$chunk;
			} while ( ! $result['done'] && $chunk < 500 );
		}

		return true;
	}

	/**
	 * Gives long steps more time and memory where the host allows it.
	 */
	private static function raise_limits() {
		wp_raise_memory_limit( 'admin' );

		if ( function_exists( 'set_time_limit' ) && false === strpos( (string) ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
			set_time_limit( 300 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Importing images can take a while.
		}
	}

	/**
	 * Step: check requirements, back up settings, start a fresh state.
	 *
	 * @param string $slug    Demo slug.
	 * @param array  $data    Demo data.
	 * @param array  $options Options.
	 * @param array  $log     Log lines.
	 * @return bool
	 */
	private static function step_prepare( $slug, $data, $options, &$log ) {
		$demos = self::demos();

		self::save_state(
			array(
				'demo'         => $slug,
				'options'      => $options,
				'started'      => time(),
				'reused_terms' => array(),
			)
		);

		// Back up what the import changes, once: removal restores the pre-demo site.
		if ( ! is_array( get_option( self::BACKUP_OPTION ) ) ) {
			$mods = get_theme_mods();

			$options_backup = array();
			foreach ( array_merge( self::ALLOWED_OPTIONS, array( 'storebox_core_settings' ) ) as $name ) {
				$options_backup[ $name ] = get_option( $name, null );
			}

			$kit_id = self::kit_id( false );

			update_option(
				self::BACKUP_OPTION,
				array(
					'theme'      => get_stylesheet(),
					'theme_mods' => is_array( $mods ) ? $mods : array(),
					'options'    => $options_backup,
					'sidebars'   => (array) get_option( 'sidebars_widgets', array() ),
					'kit_id'     => $kit_id,
					'kit'        => $kit_id ? get_post_meta( $kit_id, '_elementor_page_settings', true ) : null,
				),
				false
			);
		}

		/* translators: %s: demo name. */
		$log[] = sprintf( __( 'Importing “%s”.', 'storebox-core' ), $demos[ $slug ]['name'] );

		if ( ! self::elementor_active() ) {
			$log[] = __( 'Elementor is not active: pages are imported with their Elementor layouts, which appear once Elementor is activated. Global colors and fonts are skipped.', 'storebox-core' );
		}
		if ( $options['templates'] && ! self::pro_active() ) {
			$log[] = __( 'Elementor Pro is not active: the Theme Builder templates are skipped. The theme’s own header, footer and templates are used.', 'storebox-core' );
		}

		return true;
	}

	/**
	 * Step: import a batch of images.
	 *
	 * @param array $data  Demo data.
	 * @param int   $chunk Batch number.
	 * @param array $log   Log lines.
	 * @return bool Whether all images are done.
	 */
	private static function step_media( $data, $chunk, &$log ) {
		$images = array_slice( $data['images'], $chunk * self::MEDIA_BATCH, self::MEDIA_BATCH, true );

		if ( $images ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		foreach ( $images as $key => $image ) {
			$result = self::import_image( (string) $key, (array) $image );
			if ( is_wp_error( $result ) ) {
				/* translators: 1: image file, 2: error message. */
				$log[] = sprintf( __( 'Image %1$s skipped: %2$s', 'storebox-core' ), isset( $image['file'] ) ? $image['file'] : $key, $result->get_error_message() );
				continue;
			}
			self::$map['images'][ $key ] = $result;
		}

		$total = count( $data['images'] );
		$done  = min( $total, ( $chunk + 1 ) * self::MEDIA_BATCH );
		/* translators: 1: images done, 2: total images. */
		$log[] = sprintf( __( 'Images: %1$d of %2$d.', 'storebox-core' ), $done, $total );

		return $done >= $total;
	}

	/**
	 * Imports (or reuses) one image.
	 *
	 * @param string $key   Image key.
	 * @param array  $image { file, alt, title, caption }.
	 * @return int|\WP_Error Attachment ID.
	 */
	private static function import_image( $key, $image ) {
		if ( ! empty( self::$map['images'][ $key ] ) && get_post( self::$map['images'][ $key ] ) ) {
			return self::$map['images'][ $key ];
		}

		$name = isset( $image['file'] ) ? sanitize_file_name( basename( $image['file'] ) ) : '';
		$file = self::dir() . 'images/' . $name;

		if ( '' === $name || ! is_readable( $file ) ) {
			return new \WP_Error( 'storebox_demo_image', __( 'file missing', 'storebox-core' ) );
		}

		$tmp = wp_tempnam( $name );
		if ( ! $tmp || ! copy( $file, $tmp ) ) {
			return new \WP_Error( 'storebox_demo_image', __( 'could not copy the file to the temporary folder', 'storebox-core' ) );
		}

		$attachment_id = media_handle_sideload(
			array(
				'name'     => $name,
				'tmp_name' => $tmp,
			),
			0,
			isset( $image['title'] ) ? sanitize_text_field( $image['title'] ) : null,
			array(
				'post_excerpt' => isset( $image['caption'] ) ? sanitize_text_field( $image['caption'] ) : '',
			)
		);

		if ( is_wp_error( $attachment_id ) ) {
			wp_delete_file( $tmp );
			return $attachment_id;
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', isset( $image['alt'] ) ? sanitize_text_field( $image['alt'] ) : '' );
		update_post_meta( $attachment_id, self::IMAGE_META, $key );

		return (int) $attachment_id;
	}

	/**
	 * Step: create taxonomy terms.
	 *
	 * @param string $slug Demo slug.
	 * @param array  $data Demo data.
	 * @param array  $log  Log lines.
	 * @return bool
	 */
	private static function step_terms( $slug, $data, &$log ) {
		$state   = self::state();
		$reused  = isset( $state['reused_terms'] ) && is_array( $state['reused_terms'] ) ? $state['reused_terms'] : array();
		$created = 0;

		foreach ( $data['terms'] as $taxonomy => $terms ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			foreach ( (array) $terms as $term ) {
				if ( empty( $term['key'] ) || empty( $term['name'] ) ) {
					continue;
				}

				$key     = (string) $term['key'];
				$term_id = isset( self::$map['terms'][ $taxonomy ][ $key ] ) ? self::$map['terms'][ $taxonomy ][ $key ] : 0;
				$args    = array(
					'slug'        => isset( $term['slug'] ) ? sanitize_title( $term['slug'] ) : sanitize_title( $term['name'] ),
					'description' => isset( $term['description'] ) ? wp_kses_post( $term['description'] ) : '',
					'parent'      => ! empty( $term['parent'] ) && isset( self::$map['terms'][ $taxonomy ][ $term['parent'] ] ) ? self::$map['terms'][ $taxonomy ][ $term['parent'] ] : 0,
				);

				if ( $term_id && term_exists( $term_id, $taxonomy ) ) {
					wp_update_term( $term_id, $taxonomy, array_merge( $args, array( 'name' => $term['name'] ) ) );
				} else {
					$existing = term_exists( $args['slug'], $taxonomy );
					if ( $existing ) {
						// A term the site already had: use it, but never delete it later.
						$term_id                        = (int) $existing['term_id'];
						$reused[ $taxonomy ][ $key ]    = $term_id;
					} else {
						$inserted = wp_insert_term( $term['name'], $taxonomy, $args );
						if ( is_wp_error( $inserted ) ) {
							/* translators: 1: term name, 2: error message. */
							$log[] = sprintf( __( 'Term “%1$s” skipped: %2$s', 'storebox-core' ), $term['name'], $inserted->get_error_message() );
							continue;
						}
						$term_id = (int) $inserted['term_id'];
						update_term_meta( $term_id, self::DEMO_META, $slug );
						update_term_meta( $term_id, self::KEY_META, $key );
						++$created;
					}
				}

				if ( ! empty( $term['meta'] ) && is_array( $term['meta'] ) && empty( $reused[ $taxonomy ][ $key ] ) ) {
					foreach ( $term['meta'] as $meta_key => $meta_value ) {
						update_term_meta( $term_id, sanitize_key( $meta_key ), wp_slash( self::resolve( $meta_value ) ) );
					}
				}

				self::$map['terms'][ $taxonomy ][ $key ] = $term_id;
			}
		}

		$state['reused_terms'] = $reused;
		self::save_state( $state );

		/* translators: %d: number of terms. */
		$log[] = sprintf( _n( '%d term created.', '%d terms created.', $created, 'storebox-core' ), $created );

		return true;
	}

	/**
	 * Step: create or update a batch of posts, pages, units and locations.
	 *
	 * @param string $slug  Demo slug.
	 * @param array  $data  Demo data.
	 * @param int    $chunk Batch number.
	 * @param array  $log   Log lines.
	 * @return bool
	 */
	private static function step_posts( $slug, $data, $chunk, &$log ) {
		$items = array_slice( $data['posts'], $chunk * self::POSTS_BATCH, self::POSTS_BATCH );
		$user  = get_current_user_id();

		if ( ! $user ) {
			$admins = get_users(
				array(
					'role'   => 'administrator',
					'number' => 1,
					'fields' => 'ID',
				)
			);
			$user   = $admins ? (int) $admins[0] : 0;
		}

		foreach ( $items as $item ) {
			$result = self::import_post( $slug, (array) $item, $user );
			if ( is_wp_error( $result ) ) {
				/* translators: 1: item title, 2: error message. */
				$log[] = sprintf( __( '“%1$s” skipped: %2$s', 'storebox-core' ), isset( $item['title'] ) ? $item['title'] : '?', $result->get_error_message() );
			}
		}

		$total = count( $data['posts'] );
		$done  = min( $total, ( $chunk + 1 ) * self::POSTS_BATCH );
		/* translators: 1: items done, 2: total items. */
		$log[] = sprintf( __( 'Content: %1$d of %2$d items.', 'storebox-core' ), $done, $total );

		return $done >= $total;
	}

	/**
	 * Creates or updates one post of any type.
	 *
	 * @param string $slug Demo slug.
	 * @param array  $item Item data.
	 * @param int    $user Author ID.
	 * @return int|\WP_Error Post ID.
	 */
	private static function import_post( $slug, $item, $user ) {
		if ( empty( $item['key'] ) || empty( $item['type'] ) || ! post_type_exists( $item['type'] ) ) {
			return new \WP_Error( 'storebox_demo_post', __( 'invalid item', 'storebox-core' ) );
		}

		$key     = (string) $item['key'];
		$post_id = isset( self::$map['posts'][ $key ] ) && get_post( self::$map['posts'][ $key ] ) ? self::$map['posts'][ $key ] : 0;

		$postarr = array(
			'post_type'      => $item['type'],
			'post_status'    => 'publish',
			'post_title'     => isset( $item['title'] ) ? (string) $item['title'] : '',
			'post_name'      => isset( $item['slug'] ) ? sanitize_title( $item['slug'] ) : '',
			'post_content'   => isset( $item['content'] ) ? (string) self::resolve( $item['content'] ) : '',
			'post_excerpt'   => isset( $item['excerpt'] ) ? (string) $item['excerpt'] : '',
			'menu_order'     => isset( $item['menu_order'] ) ? (int) $item['menu_order'] : 0,
			'comment_status' => isset( $item['comments'] ) ? ( $item['comments'] ? 'open' : 'closed' ) : get_default_comment_status( $item['type'] ),
			'post_author'    => $user,
		);

		if ( ! empty( $item['date'] ) ) {
			$time = strtotime( (string) $item['date'] );
			if ( $time ) {
				$postarr['post_date']     = wp_date( 'Y-m-d H:i:s', $time );
				$postarr['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $time );
			}
		}

		if ( ! empty( $item['parent'] ) && isset( self::$map['posts'][ $item['parent'] ] ) ) {
			$postarr['post_parent'] = self::$map['posts'][ $item['parent'] ];
		}

		if ( $post_id ) {
			$postarr['ID'] = $post_id;
			$post_id       = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$post_id = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, self::DEMO_META, $slug );
		update_post_meta( $post_id, self::KEY_META, $key );
		self::$map['posts'][ $key ] = (int) $post_id;

		if ( ! empty( $item['template'] ) ) {
			update_post_meta( $post_id, '_wp_page_template', sanitize_text_field( $item['template'] ) );
		}

		if ( ! empty( $item['meta'] ) && is_array( $item['meta'] ) ) {
			foreach ( $item['meta'] as $meta_key => $meta_value ) {
				if ( 0 === strpos( $meta_key, '_elementor' ) ) {
					continue; // Written by the Elementor step.
				}
				update_post_meta( $post_id, $meta_key, wp_slash( self::resolve( $meta_value ) ) );
			}
		}

		if ( ! empty( $item['terms'] ) && is_array( $item['terms'] ) ) {
			foreach ( $item['terms'] as $taxonomy => $keys ) {
				if ( ! taxonomy_exists( $taxonomy ) ) {
					continue;
				}
				$ids = array();
				foreach ( (array) $keys as $term_key ) {
					if ( isset( self::$map['terms'][ $taxonomy ][ $term_key ] ) ) {
						$ids[] = self::$map['terms'][ $taxonomy ][ $term_key ];
					}
				}
				wp_set_object_terms( $post_id, $ids, $taxonomy );
			}
		}

		if ( ! empty( $item['image'] ) && isset( self::$map['images'][ $item['image'] ] ) ) {
			set_post_thumbnail( $post_id, self::$map['images'][ $item['image'] ] );
		}

		return (int) $post_id;
	}

	/**
	 * Step: resolve references between items (e.g. a unit's location).
	 *
	 * @param array $data Demo data.
	 * @param array $log  Log lines.
	 * @return bool
	 */
	private static function step_relations( $data, &$log ) {
		foreach ( $data['posts'] as $item ) {
			if ( empty( $item['key'] ) || empty( self::$map['posts'][ $item['key'] ] ) ) {
				continue;
			}

			$post_id = self::$map['posts'][ $item['key'] ];

			if ( ! empty( $item['meta'] ) && is_array( $item['meta'] ) ) {
				foreach ( $item['meta'] as $meta_key => $meta_value ) {
					if ( 0 !== strpos( $meta_key, '_elementor' ) && false !== strpos( wp_json_encode( $meta_value ), '{{post:' ) ) {
						update_post_meta( $post_id, $meta_key, wp_slash( self::resolve( $meta_value ) ) );
					}
				}
			}

			if ( ! empty( $item['parent'] ) && isset( self::$map['posts'][ $item['parent'] ] ) && (int) wp_get_post_parent_id( $post_id ) !== self::$map['posts'][ $item['parent'] ] ) {
				wp_update_post(
					array(
						'ID'          => $post_id,
						'post_parent' => self::$map['posts'][ $item['parent'] ],
					)
				);
			}

			// Content can link to other items ({{url:key}}), which may not have existed yet.
			if ( ! empty( $item['content'] ) && false !== strpos( (string) $item['content'], '{{' ) ) {
				wp_update_post(
					wp_slash(
						array(
							'ID'           => $post_id,
							'post_content' => (string) self::resolve( $item['content'] ),
						)
					)
				);
			}
		}

		$log[] = __( 'Content linked.', 'storebox-core' );

		return true;
	}

	/**
	 * Step: create menus and assign them to theme locations.
	 *
	 * @param string $slug Demo slug.
	 * @param array  $data Demo data.
	 * @param array  $log  Log lines.
	 * @return bool
	 */
	private static function step_menus( $slug, $data, &$log ) {
		$locations  = get_theme_mod( 'nav_menu_locations', array() );
		$locations  = is_array( $locations ) ? $locations : array();
		$registered = get_registered_nav_menus();

		foreach ( $data['menus'] as $menu ) {
			if ( empty( $menu['key'] ) || empty( $menu['name'] ) ) {
				continue;
			}

			$key     = (string) $menu['key'];
			$menu_id = isset( self::$map['menus'][ $key ] ) && wp_get_nav_menu_object( self::$map['menus'][ $key ] ) ? self::$map['menus'][ $key ] : 0;

			if ( $menu_id ) {
				// Rebuild the items of a menu imported before.
				foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $old ) {
					wp_delete_post( $old->ID, true );
				}
			} else {
				$name = $menu['name'];
				if ( wp_get_nav_menu_object( $name ) ) {
					/* translators: %s: menu name. */
					$name = sprintf( __( '%s (demo)', 'storebox-core' ), $name );
				}
				$menu_id = wp_create_nav_menu( $name );
				if ( is_wp_error( $menu_id ) ) {
					/* translators: 1: menu name, 2: error message. */
					$log[] = sprintf( __( 'Menu “%1$s” skipped: %2$s', 'storebox-core' ), $menu['name'], $menu_id->get_error_message() );
					continue;
				}
				update_term_meta( $menu_id, self::DEMO_META, $slug );
				update_term_meta( $menu_id, self::KEY_META, $key );
			}

			self::$map['menus'][ $key ] = (int) $menu_id;
			self::add_menu_items( $menu_id, isset( $menu['items'] ) ? (array) $menu['items'] : array(), 0 );

			if ( ! empty( $menu['location'] ) && isset( $registered[ $menu['location'] ] ) ) {
				$locations[ $menu['location'] ] = (int) $menu_id;
			}
		}

		set_theme_mod( 'nav_menu_locations', $locations );

		/* translators: %d: number of menus. */
		$log[] = sprintf( _n( '%d menu created.', '%d menus created.', count( $data['menus'] ), 'storebox-core' ), count( $data['menus'] ) );

		return true;
	}

	/**
	 * Adds menu items, recursively.
	 *
	 * @param int   $menu_id Menu ID.
	 * @param array $items   Items: { title, post | url, children }.
	 * @param int   $parent  Parent menu item ID.
	 */
	private static function add_menu_items( $menu_id, $items, $parent ) {
		$position = 0;

		foreach ( $items as $item ) {
			$args = array(
				'menu-item-title'     => isset( $item['title'] ) ? (string) $item['title'] : '',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
				'menu-item-position'  => ++$position,
				'menu-item-classes'   => isset( $item['classes'] ) ? sanitize_text_field( $item['classes'] ) : '',
			);

			if ( ! empty( $item['post'] ) && ! empty( self::$map['posts'][ $item['post'] ] ) ) {
				$object_id                    = self::$map['posts'][ $item['post'] ];
				$args['menu-item-type']       = 'post_type';
				$args['menu-item-object']     = get_post_type( $object_id );
				$args['menu-item-object-id']  = $object_id;
			} else {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = isset( $item['url'] ) ? esc_url_raw( self::resolve( $item['url'] ) ) : '#';
			}

			$item_id = wp_update_nav_menu_item( $menu_id, 0, wp_slash( $args ) );

			if ( ! is_wp_error( $item_id ) && ! empty( $item['children'] ) ) {
				self::add_menu_items( $menu_id, (array) $item['children'], $item_id );
			}
		}
	}

	/**
	 * Elementor documents to write: pages built with Elementor, then templates.
	 *
	 * @param array $data    Demo data.
	 * @param array $options Options.
	 * @return array List of { kind: page|template, item }.
	 */
	private static function elementor_queue( $data, $options ) {
		$queue = array();

		if ( $options['content'] ) {
			foreach ( $data['posts'] as $item ) {
				if ( ! empty( $item['elementor'] ) ) {
					$queue[] = array(
						'kind' => 'page',
						'item' => $item,
					);
				}
			}
		}

		if ( $options['templates'] && self::pro_active() ) {
			foreach ( $data['templates'] as $item ) {
				$queue[] = array(
					'kind' => 'template',
					'item' => $item,
				);
			}
		}

		return $queue;
	}

	/**
	 * Step: write Elementor layouts and create Theme Builder templates.
	 *
	 * @param string $slug    Demo slug.
	 * @param array  $data    Demo data.
	 * @param array  $options Options.
	 * @param int    $chunk   Batch number.
	 * @param array  $log     Log lines.
	 * @return bool|\WP_Error
	 */
	private static function step_elementor( $slug, $data, $options, $chunk, &$log ) {
		$queue = self::elementor_queue( $data, $options );
		$batch = array_slice( $queue, $chunk * self::ELEMENTOR_BATCH, self::ELEMENTOR_BATCH );

		foreach ( $batch as $entry ) {
			$item     = $entry['item'];
			$document = self::read_json( $slug . '/' . ltrim( (string) $item['elementor'], '/' ) );

			if ( is_wp_error( $document ) ) {
				$log[] = $document->get_error_message();
				continue;
			}

			if ( 'template' === $entry['kind'] ) {
				$post_id = self::create_template( $slug, $item );
				if ( is_wp_error( $post_id ) ) {
					/* translators: 1: template title, 2: error message. */
					$log[] = sprintf( __( 'Template “%1$s” skipped: %2$s', 'storebox-core' ), $item['title'], $post_id->get_error_message() );
					continue;
				}
				$type = sanitize_key( $item['type'] );
			} else {
				$post_id = isset( self::$map['posts'][ $item['key'] ] ) ? self::$map['posts'][ $item['key'] ] : 0;
				$type    = 'page' === $item['type'] ? 'wp-page' : 'wp-post';
			}

			if ( ! $post_id ) {
				continue;
			}

			$elements = isset( $document['content'] ) && is_array( $document['content'] ) ? $document['content'] : array();
			$settings = isset( $document['page_settings'] ) && is_array( $document['page_settings'] ) ? $document['page_settings'] : array();

			self::save_elementor( $post_id, $type, self::resolve( $elements ), self::resolve( $settings ) );

			if ( 'template' === $entry['kind'] && ! empty( $item['conditions'] ) ) {
				update_post_meta( $post_id, '_elementor_conditions', array_map( 'sanitize_text_field', (array) $item['conditions'] ) );
			}

			/* translators: %s: page or template title. */
			$log[] = sprintf( __( 'Built “%s”.', 'storebox-core' ), get_the_title( $post_id ) );
		}

		if ( ! $queue ) {
			$log[] = __( 'No Elementor layouts to build.', 'storebox-core' );
		}

		return ( $chunk + 1 ) * self::ELEMENTOR_BATCH >= count( $queue );
	}

	/**
	 * Creates or updates an Elementor library template.
	 *
	 * @param string $slug Demo slug.
	 * @param array  $item { key, title, type, conditions, elementor }.
	 * @return int|\WP_Error
	 */
	private static function create_template( $slug, $item ) {
		if ( empty( $item['key'] ) || empty( $item['type'] ) || ! post_type_exists( 'elementor_library' ) ) {
			return new \WP_Error( 'storebox_demo_template', __( 'Elementor library unavailable', 'storebox-core' ) );
		}

		$key     = 'template:' . $item['key'];
		$post_id = isset( self::$map['posts'][ $key ] ) && get_post( self::$map['posts'][ $key ] ) ? self::$map['posts'][ $key ] : 0;

		$postarr = array(
			'post_type'   => 'elementor_library',
			'post_status' => 'publish',
			'post_title'  => isset( $item['title'] ) ? (string) $item['title'] : $item['key'],
		);

		if ( $post_id ) {
			$postarr['ID'] = $post_id;
			$post_id       = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$post_id = wp_insert_post( wp_slash( $postarr ), true );
		}

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, self::DEMO_META, $slug );
		update_post_meta( $post_id, self::KEY_META, $key );
		self::$map['posts'][ $key ] = (int) $post_id;

		if ( taxonomy_exists( 'elementor_library_type' ) ) {
			wp_set_object_terms( $post_id, sanitize_key( $item['type'] ), 'elementor_library_type' );
		}

		return (int) $post_id;
	}

	/**
	 * Writes an Elementor document's data.
	 *
	 * Meta is written directly (as Elementor's own importer does) so that
	 * layouts survive even when a widget's plugin is not active yet; the CSS
	 * is regenerated on the first visit.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $type     Elementor document type.
	 * @param array  $elements Elements.
	 * @param array  $settings Page settings.
	 */
	private static function save_elementor( $post_id, $type, $elements, $settings ) {
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', $type );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.20.0' );
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			update_post_meta( $post_id, '_elementor_pro_version', ELEMENTOR_PRO_VERSION );
		}
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );

		if ( $settings ) {
			update_post_meta( $post_id, '_elementor_page_settings', wp_slash( $settings ) );
		} else {
			delete_post_meta( $post_id, '_elementor_page_settings' );
		}

		foreach ( array( '_elementor_css', '_elementor_element_cache', '_elementor_page_assets' ) as $cache ) {
			delete_post_meta( $post_id, $cache );
		}

		if ( 'wp-page' === $type && ! get_post_meta( $post_id, '_wp_page_template', true ) ) {
			update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );
		}
	}

	/**
	 * Step: theme mods, options, Storebox settings, widgets and Elementor Site Settings.
	 *
	 * @param array $data Demo data.
	 * @param array $log  Log lines.
	 * @return bool
	 */
	private static function step_settings( $data, &$log ) {
		foreach ( (array) $data['theme_mods'] as $key => $value ) {
			if ( 'nav_menu_locations' !== $key && sanitize_key( $key ) === $key && ( is_scalar( $value ) || is_array( $value ) ) ) {
				set_theme_mod( $key, self::resolve( $value ) );
			}
		}

		foreach ( (array) $data['options'] as $name => $value ) {
			if ( in_array( $name, self::ALLOWED_OPTIONS, true ) ) {
				update_option( $name, self::resolve( $value ) );
			}
		}

		if ( $data['settings'] && class_exists( '\Storebox_Core\Settings' ) ) {
			$current = get_option( \Storebox_Core\Settings::OPTION, array() );
			$merged  = array_merge( is_array( $current ) ? $current : array(), (array) self::resolve( $data['settings'] ) );
			update_option( \Storebox_Core\Settings::OPTION, \Storebox_Core\Settings::sanitize( $merged ) );
		}

		// Demo sidebars: move widgets out of the areas the demo defines.
		if ( $data['sidebars'] ) {
			$sidebars = get_option( 'sidebars_widgets', array() );
			$sidebars = is_array( $sidebars ) ? $sidebars : array();
			$inactive = isset( $sidebars['wp_inactive_widgets'] ) ? (array) $sidebars['wp_inactive_widgets'] : array();

			foreach ( (array) $data['sidebars'] as $sidebar => $widgets ) {
				if ( ! empty( $sidebars[ $sidebar ] ) ) {
					$inactive = array_merge( $inactive, (array) $sidebars[ $sidebar ] );
				}
				$sidebars[ $sidebar ] = array();
			}

			$sidebars['wp_inactive_widgets'] = array_values( array_unique( $inactive ) );
			update_option( 'sidebars_widgets', $sidebars );
		}

		if ( $data['kit'] ) {
			$kit_id = self::kit_id( true );
			if ( $kit_id ) {
				$current = get_post_meta( $kit_id, '_elementor_page_settings', true );
				$merged  = array_merge( is_array( $current ) ? $current : array(), (array) self::resolve( $data['kit'] ) );
				update_post_meta( $kit_id, '_elementor_page_settings', wp_slash( $merged ) );
				delete_post_meta( $kit_id, '_elementor_css' );
				$log[] = __( 'Elementor Site Settings (colors, fonts, layout) applied.', 'storebox-core' );
			} else {
				$log[] = __( 'Elementor Site Settings skipped: activate Elementor and import again to apply the global colors and fonts.', 'storebox-core' );
			}
		}

		$log[] = __( 'Settings applied.', 'storebox-core' );

		return true;
	}

	/**
	 * The active Elementor kit (Site Settings) ID.
	 *
	 * @param bool $create Create the default kit if it is missing.
	 * @return int
	 */
	private static function kit_id( $create ) {
		if ( ! self::elementor_active() ) {
			return 0;
		}

		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( $kit_id && 'elementor_library' === get_post_type( $kit_id ) ) {
			return $kit_id;
		}

		if ( $create && isset( \Elementor\Plugin::$instance->kits_manager ) && is_callable( array( \Elementor\Plugin::$instance->kits_manager, 'create_default' ) ) ) {
			$kit_id = (int) \Elementor\Plugin::$instance->kits_manager->create_default();
			if ( $kit_id ) {
				update_option( 'elementor_active_kit', $kit_id );
				return $kit_id;
			}
		}

		return 0;
	}

	/**
	 * Step: apply Theme Builder conditions, clear caches, record the import.
	 *
	 * @param string $slug Demo slug.
	 * @param array  $log  Log lines.
	 * @return bool
	 */
	private static function step_finish( $slug, &$log ) {
		if ( self::pro_active() ) {
			if ( self::regenerate_conditions() ) {
				$log[] = __( 'Theme Builder display conditions applied.', 'storebox-core' );
			} else {
				$log[] = __( 'Could not refresh the Theme Builder conditions automatically. Open Templates → Theme Builder and re-save each imported template.', 'storebox-core' );
			}
		}

		self::clear_elementor_cache();
		flush_rewrite_rules( false );

		update_option(
			self::DONE_OPTION,
			array(
				'demo'    => $slug,
				'time'    => time(),
				'version' => STOREBOX_CORE_VERSION,
			),
			false
		);
		delete_option( self::STATE_OPTION );

		/**
		 * Fires after a demo was imported.
		 *
		 * @param string $slug Demo slug.
		 * @param array  $map  Imported items: posts, terms, images, menus.
		 */
		do_action( 'storebox_core/demo_imported', $slug, self::$map );

		$log[] = __( 'All done!', 'storebox-core' );

		return true;
	}

	/**
	 * Rebuilds Elementor Pro's Theme Builder conditions cache.
	 *
	 * @return bool
	 */
	public static function regenerate_conditions() {
		if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			return false;
		}

		$module = \ElementorPro\Modules\ThemeBuilder\Module::instance();
		if ( ! is_callable( array( $module, 'get_conditions_manager' ) ) ) {
			return false;
		}

		$manager = $module->get_conditions_manager();
		if ( ! is_object( $manager ) || ! is_callable( array( $manager, 'get_cache' ) ) ) {
			return false;
		}

		$cache = $manager->get_cache();
		if ( ! is_object( $cache ) || ! is_callable( array( $cache, 'regenerate' ) ) ) {
			return false;
		}

		$cache->regenerate();

		return true;
	}

	/**
	 * Clears Elementor's generated CSS.
	 */
	private static function clear_elementor_cache() {
		if ( self::elementor_active() && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/* ---------------------------------------------------------------------
	 * Placeholders
	 * ------------------------------------------------------------------ */

	/**
	 * Resolves placeholders in a value, recursively.
	 *
	 * A string that is exactly one ID placeholder becomes an integer.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	public static function resolve( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = self::resolve( $item );
			}
			return $value;
		}

		if ( ! is_string( $value ) || false === strpos( $value, '{{' ) ) {
			return $value;
		}

		if ( preg_match( '/^\{\{([a-z_]+)(?::([^{}]*))?\}\}$/', $value, $match ) ) {
			$resolved = self::token( $match[1], isset( $match[2] ) ? $match[2] : '' );
			if ( null !== $resolved ) {
				return $resolved;
			}
		}

		return preg_replace_callback(
			'/\{\{([a-z_]+)(?::([^{}]*))?\}\}/',
			static function ( $match ) {
				$resolved = self::token( $match[1], isset( $match[2] ) ? $match[2] : '' );
				return null === $resolved ? $match[0] : (string) $resolved;
			},
			$value
		);
	}

	/**
	 * Value of one placeholder.
	 *
	 * @param string $type Placeholder type.
	 * @param string $arg  Argument.
	 * @return mixed|null Null when the type is unknown.
	 */
	private static function token( $type, $arg ) {
		switch ( $type ) {
			case 'home':
				return home_url( '/' );

			case 'post':
				return isset( self::$map['posts'][ $arg ] ) ? (int) self::$map['posts'][ $arg ] : 0;

			case 'sid':
				// Post ID as a string, as Elementor select controls store it.
				return isset( self::$map['posts'][ $arg ] ) ? (string) self::$map['posts'][ $arg ] : '';

			case 'url':
				$parts = explode( '#', $arg, 2 );
				$id    = isset( self::$map['posts'][ $parts[0] ] ) ? self::$map['posts'][ $parts[0] ] : 0;
				$url   = $id ? get_permalink( $id ) : home_url( '/' );
				return $url . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );

			case 'image':
				return isset( self::$map['images'][ $arg ] ) ? (int) self::$map['images'][ $arg ] : 0;

			case 'image_url':
				$id = isset( self::$map['images'][ $arg ] ) ? self::$map['images'][ $arg ] : 0;
				return $id ? (string) wp_get_attachment_url( $id ) : '';

			case 'images':
				$ids = array();
				foreach ( array_filter( array_map( 'trim', explode( ',', $arg ) ) ) as $key ) {
					if ( ! empty( self::$map['images'][ $key ] ) ) {
						$ids[] = self::$map['images'][ $key ];
					}
				}
				return implode( ',', $ids );

			case 'term':
				$parts = explode( '/', $arg, 2 );
				return 2 === count( $parts ) && isset( self::$map['terms'][ $parts[0] ][ $parts[1] ] ) ? (int) self::$map['terms'][ $parts[0] ][ $parts[1] ] : 0;

			case 'menu':
				$menu = isset( self::$map['menus'][ $arg ] ) ? wp_get_nav_menu_object( self::$map['menus'][ $arg ] ) : false;
				return $menu ? $menu->slug : '';

			case 'menu_id':
				return isset( self::$map['menus'][ $arg ] ) ? (int) self::$map['menus'][ $arg ] : 0;
		}

		return null;
	}

	/* ---------------------------------------------------------------------
	 * Removal
	 * ------------------------------------------------------------------ */

	/**
	 * Deletes one batch of imported content.
	 *
	 * @param bool  $keep_media Keep imported images (they are reused by the next import).
	 * @param array $log        Log lines.
	 * @return bool Whether everything is removed.
	 */
	private static function remove_batch( $keep_media, &$log ) {
		$meta_query = array(
			'relation' => 'OR',
			array(
				'key'     => self::DEMO_META,
				'compare' => 'EXISTS',
			),
		);
		if ( ! $keep_media ) {
			$meta_query[] = array(
				'key'     => self::IMAGE_META,
				'compare' => 'EXISTS',
			);
		}

		$ids = get_posts(
			array(
				'post_type'        => 'any',
				'post_status'      => 'any',
				'posts_per_page'   => self::REMOVE_BATCH,
				'fields'           => 'ids',
				'meta_query'       => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin-only cleanup.
				'no_found_rows'    => true,
				'suppress_filters' => true,
			)
		);

		// "any" skips types excluded from search (e.g. the Elementor library).
		if ( count( $ids ) < self::REMOVE_BATCH && post_type_exists( 'elementor_library' ) ) {
			$ids = array_merge(
				$ids,
				get_posts(
					array(
						'post_type'        => 'elementor_library',
						'post_status'      => 'any',
						'posts_per_page'   => self::REMOVE_BATCH - count( $ids ),
						'fields'           => 'ids',
						'meta_key'         => self::DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin-only cleanup.
						'no_found_rows'    => true,
						'suppress_filters' => true,
					)
				)
			);
		}

		foreach ( array_unique( $ids ) as $post_id ) {
			if ( 'attachment' === get_post_type( $post_id ) ) {
				wp_delete_attachment( $post_id, true );
			} else {
				wp_delete_post( $post_id, true );
			}
		}

		if ( $ids ) {
			/* translators: %d: number of items. */
			$log[] = sprintf( _n( '%d item removed.', '%d items removed.', count( $ids ), 'storebox-core' ), count( $ids ) );
			return false;
		}

		// Terms and menus.
		$terms = get_terms(
			array(
				'taxonomy'   => array_values( get_taxonomies() ),
				'hide_empty' => false,
				'meta_key'   => self::DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin-only cleanup.
				'fields'     => 'all',
			)
		);
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 'nav_menu' === $term->taxonomy ) {
					wp_delete_nav_menu( $term->term_id );
				} else {
					wp_delete_term( $term->term_id, $term->taxonomy );
				}
			}
		}

		delete_option( self::DONE_OPTION );

		return true;
	}

	/**
	 * Removes imported content, one batch per call, then restores settings.
	 *
	 * @param bool $keep_media Keep imported images.
	 * @return array { done: bool, log: string[] }
	 */
	public static function remove( $keep_media ) {
		self::raise_limits();

		$log  = array();
		$done = self::remove_batch( $keep_media, $log );

		if ( $done ) {
			self::restore_backup( $log );
			delete_option( self::STATE_OPTION );
			self::clear_elementor_cache();
			flush_rewrite_rules( false );
			$log[] = __( 'Demo content removed.', 'storebox-core' );
		}

		return array(
			'done' => $done,
			'log'  => $log,
		);
	}

	/**
	 * Restores the settings saved before the first import.
	 *
	 * @param array $log Log lines.
	 */
	private static function restore_backup( &$log ) {
		$backup = get_option( self::BACKUP_OPTION );
		if ( ! is_array( $backup ) ) {
			return;
		}

		// Only what a demo sets is restored; everything else stays as it is now.
		$mod_keys = array( 'nav_menu_locations' );
		$sidebars = array();
		foreach ( array_keys( self::demos() ) as $slug ) {
			$data = self::data( $slug );
			if ( ! is_wp_error( $data ) ) {
				$mod_keys = array_merge( $mod_keys, array_keys( (array) $data['theme_mods'] ) );
				$sidebars = array_merge( $sidebars, array_keys( (array) $data['sidebars'] ) );
			}
		}

		if ( isset( $backup['theme'], $backup['theme_mods'] ) && get_stylesheet() === $backup['theme'] ) {
			foreach ( array_unique( $mod_keys ) as $key ) {
				if ( array_key_exists( $key, $backup['theme_mods'] ) ) {
					set_theme_mod( $key, $backup['theme_mods'][ $key ] );
				} else {
					remove_theme_mod( $key );
				}
			}
		}

		if ( ! empty( $backup['options'] ) ) {
			foreach ( $backup['options'] as $name => $value ) {
				if ( null === $value ) {
					delete_option( $name );
				} else {
					update_option( $name, $value );
				}
			}
		}

		// Widgets moved aside by the import go back to widget areas that are still empty.
		if ( isset( $backup['sidebars'] ) && is_array( $backup['sidebars'] ) && $sidebars ) {
			$current  = get_option( 'sidebars_widgets', array() );
			$current  = is_array( $current ) ? $current : array();
			$inactive = isset( $current['wp_inactive_widgets'] ) ? (array) $current['wp_inactive_widgets'] : array();

			foreach ( array_unique( $sidebars ) as $sidebar ) {
				if ( empty( $current[ $sidebar ] ) && ! empty( $backup['sidebars'][ $sidebar ] ) ) {
					$current[ $sidebar ] = (array) $backup['sidebars'][ $sidebar ];
					$inactive            = array_diff( $inactive, $current[ $sidebar ] );
				}
			}

			$current['wp_inactive_widgets'] = array_values( $inactive );
			update_option( 'sidebars_widgets', $current );
		}

		if ( ! empty( $backup['kit_id'] ) && 'elementor_library' === get_post_type( $backup['kit_id'] ) ) {
			if ( is_array( $backup['kit'] ) ) {
				update_post_meta( $backup['kit_id'], '_elementor_page_settings', wp_slash( $backup['kit'] ) );
			} else {
				delete_post_meta( $backup['kit_id'], '_elementor_page_settings' );
			}
			delete_post_meta( $backup['kit_id'], '_elementor_css' );
		}

		delete_option( self::BACKUP_OPTION );
		$log[] = __( 'Previous settings restored.', 'storebox-core' );
	}
}
