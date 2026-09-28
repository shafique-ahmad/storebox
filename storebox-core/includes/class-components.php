<?php
/**
 * Component registry: assets, defaults and template rendering.
 *
 * Components are shared by the Elementor widgets and the theme templates.
 * A theme can override any template by copying it to
 * {theme}/storebox-core/{component}.php.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Renders components and manages their assets.
 */
class Components {

	/**
	 * Counter for unique element IDs.
	 *
	 * @var int
	 */
	private static $counter = 0;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_assets' ), 20 );
	}

	/**
	 * Components and the style/script groups they need.
	 *
	 * @return array<string, array{css: string[], js: string[]}>
	 */
	public static function registry() {
		return apply_filters(
			'storebox_core/components',
			array(
				'unit-grid'       => array( 'css' => array( 'units' ), 'js' => array( 'unit-grid' ) ),
				'unit-gallery'    => array( 'css' => array( 'units' ), 'js' => array( 'gallery' ) ),
				'unit-specs'      => array( 'css' => array( 'units' ), 'js' => array() ),
				'unit-features'   => array( 'css' => array( 'units' ), 'js' => array() ),
				'unit-booking'    => array( 'css' => array( 'units' ), 'js' => array() ),
				'location-grid'   => array( 'css' => array( 'locations' ), 'js' => array() ),
				'location-facts'  => array( 'css' => array( 'locations' ), 'js' => array() ),
				'location-tags'   => array( 'css' => array( 'locations' ), 'js' => array() ),
				'opening-hours'   => array( 'css' => array( 'locations' ), 'js' => array( 'hours' ) ),
				'location-map'    => array( 'css' => array( 'map' ), 'js' => array( 'map' ) ),
				'size-calculator' => array( 'css' => array( 'sizes' ), 'js' => array( 'calculator' ) ),
				'size-chooser'    => array( 'css' => array( 'sizes' ), 'js' => array( 'chooser' ) ),
				'size-guide'      => array( 'css' => array( 'sizes' ), 'js' => array() ),
				'post-grid'       => array( 'css' => array( 'posts' ), 'js' => array() ),
				'enquiry-form'    => array( 'css' => array( 'forms' ), 'js' => array( 'enquiry' ) ),
			)
		);
	}

	/**
	 * Asset version (file time when SCRIPT_DEBUG is on).
	 *
	 * @param string $relative Path relative to the plugin directory.
	 * @return string
	 */
	private static function version( $relative ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( STOREBOX_CORE_DIR . $relative ) ) {
			return (string) filemtime( STOREBOX_CORE_DIR . $relative );
		}

		return STOREBOX_CORE_VERSION;
	}

	/**
	 * Style handle of a CSS group.
	 *
	 * @param string $group Group name.
	 * @return string
	 */
	public static function style_handle( $group ) {
		return 'storebox-core-' . $group;
	}

	/**
	 * Script handle of a JS module.
	 *
	 * @param string $module Module name.
	 * @return string
	 */
	public static function script_handle( $module ) {
		return 'storebox-core-' . $module;
	}

	/**
	 * Registers every style and script (enqueued only where used).
	 */
	public static function register_assets() {
		wp_register_style( 'storebox-core', STOREBOX_CORE_URL . 'assets/css/core.css', array(), self::version( 'assets/css/core.css' ) );

		foreach ( array( 'units', 'locations', 'map', 'sizes', 'posts', 'forms' ) as $group ) {
			wp_register_style( self::style_handle( $group ), STOREBOX_CORE_URL . 'assets/css/' . $group . '.css', array( 'storebox-core' ), self::version( 'assets/css/' . $group . '.css' ) );
		}

		// Leaflet is loaded on demand by map.js; the handles are registered for child themes and add-ons.
		wp_register_style( 'storebox-leaflet', STOREBOX_CORE_URL . 'assets/vendor/leaflet/leaflet.css', array(), '1.9.4' );

		$script_args = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		wp_register_script( 'storebox-core', STOREBOX_CORE_URL . 'assets/js/core.js', array(), self::version( 'assets/js/core.js' ), $script_args );
		wp_register_script( 'storebox-leaflet', STOREBOX_CORE_URL . 'assets/vendor/leaflet/leaflet.js', array(), '1.9.4', $script_args );

		foreach ( array( 'unit-grid', 'gallery', 'hours', 'map', 'calculator', 'chooser', 'enquiry' ) as $module ) {
			wp_register_script( self::script_handle( $module ), STOREBOX_CORE_URL . 'assets/js/' . $module . '.js', array( 'storebox-core' ), self::version( 'assets/js/' . $module . '.js' ), $script_args );
		}

		wp_localize_script(
			'storebox-core',
			'storeboxCore',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'timezone' => wp_timezone_string(),
				'assets'   => array(
					'leafletJs'  => STOREBOX_CORE_URL . 'assets/vendor/leaflet/' . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'leaflet-src.js' : 'leaflet.js' ) . '?ver=1.9.4',
					'leafletCss' => STOREBOX_CORE_URL . 'assets/vendor/leaflet/leaflet.css?ver=1.9.4',
				),
				'i18n'     => array(
					'sending'  => __( 'Sending…', 'storebox-core' ),
					'error'    => __( 'Sorry, something went wrong. Please try again or call us.', 'storebox-core' ),
					/* translators: %d: percentage of the unit that would be filled. */
					'fillText' => __( 'About %d%% full, leaving room to walk in and reach the back.', 'storebox-core' ),
				),
			)
		);
	}

	/**
	 * Style handles needed by components.
	 *
	 * @param string[] $names Component names.
	 * @return string[]
	 */
	public static function style_handles( $names ) {
		$registry = self::registry();
		$handles  = array( 'storebox-core' );

		foreach ( $names as $name ) {
			if ( isset( $registry[ $name ] ) ) {
				foreach ( $registry[ $name ]['css'] as $group ) {
					$handles[] = self::style_handle( $group );
				}
			}
		}

		return array_values( array_unique( $handles ) );
	}

	/**
	 * Script handles needed by components.
	 *
	 * @param string[] $names Component names.
	 * @return string[]
	 */
	public static function script_handles( $names ) {
		$registry = self::registry();
		$handles  = array();

		foreach ( $names as $name ) {
			if ( isset( $registry[ $name ] ) ) {
				foreach ( $registry[ $name ]['js'] as $module ) {
					$handles[] = self::script_handle( $module );
				}
			}
		}

		return array_values( array_unique( $handles ) );
	}

	/**
	 * Enqueues assets for components.
	 *
	 * @param string[] $names Component names.
	 */
	public static function enqueue( $names ) {
		if ( ! did_action( 'init' ) ) {
			return;
		}

		foreach ( self::style_handles( $names ) as $handle ) {
			wp_enqueue_style( $handle );
		}
		foreach ( self::script_handles( $names ) as $handle ) {
			wp_enqueue_script( $handle );
		}
	}

	/**
	 * A unique element ID for accessible labels.
	 *
	 * @param string $prefix Prefix.
	 * @return string
	 */
	public static function uid( $prefix = 'sb' ) {
		++self::$counter;

		return $prefix . '-' . self::$counter . '-' . wp_rand( 100, 999 );
	}

	/**
	 * Default arguments of a component.
	 *
	 * @param string $name Component name.
	 * @return array
	 */
	public static function defaults( $name ) {
		$defaults = array(
			'unit-grid'       => array(
				'style'           => '',
				'layout'          => 'grid',
				'columns'         => 3,
				'source'          => 'all',
				'ids'             => array(),
				'location'        => 0,
				'unit'            => 0,
				'type'            => '',
				'size'            => '',
				'count'           => 0,
				'orderby'         => 'area',
				'order'           => 'ASC',
				'hide_full'       => false,
				'image_size'      => 'storebox-card',
				'show_image'      => true,
				'show_status'     => true,
				'subline'         => 'auto',
				'show_name'       => false,
				'description'     => 'auto',
				'bullets'         => 'features',
				'bullet_limit'    => 3,
				'bullet_location' => true,
				'show_price'      => true,
				'cta_style'       => 'auto',
				'cta_text'        => '',
				'waitlist_text'   => '',
				'full_label'      => '',
				'filters'         => false,
				'filter_size'     => true,
				'filter_location' => true,
				'filter_type'     => true,
				'filter_sort'     => true,
				'filter_available' => true,
				'filters_overlap' => false,
				'labels'          => array(),
				'autoplay'        => 4000,
				'show_arrows'     => true,
				'show_progress'   => true,
				'hint'            => '',
				'header'          => false,
				'header_eyebrow'  => '',
				'header_title'    => '',
				'header_tag'      => 'h2',
				'header_button_text' => '',
				'header_button_url'  => '',
				'empty_message'   => '',
			),
			'unit-gallery'    => array(
				'style'      => '',
				'unit'       => 0,
				'ids'        => array(),
				'thumbs'     => 4,
				'image_size' => 'storebox-wide',
			),
			'unit-specs'      => array(
				'style'  => '',
				'unit'   => 0,
				'items'  => array( 'area', 'dimensions', 'ceiling', 'floor' ),
				'labels' => array(),
			),
			'unit-features'   => array(
				'style'        => '',
				'unit'         => 0,
				'show_missing' => true,
			),
			'unit-booking'    => array(
				'style'         => '',
				'unit'          => 0,
				'show_weekly'   => true,
				'show_status'   => true,
				'show_location' => true,
				'show_access'   => true,
				'rows'          => null,
				'button_text'   => '',
				'waitlist_text' => '',
				'button_url'    => '#reserve',
				'waitlist_url'  => '#reserve',
				'note'          => null,
				'help'          => false,
				'help_title'    => null,
				'help_text'     => null,
				'help_phone'    => '',
				'labels'        => array(),
			),
			'location-grid'   => array(
				'style'       => '',
				'skin'        => 'auto',
				'ids'         => array(),
				'count'       => 0,
				'columns'     => 3,
				'address'     => 'short',
				'show_access' => true,
				'tag_limit'   => 1,
				'show_free'   => true,
				'show_excerpt' => true,
				'show_meta'   => true,
				'link_text'   => '',
				'heading_tag' => 'h3',
			),
			'location-facts'  => array(
				'style'    => '',
				'location' => 0,
				'overlap'  => false,
				'items'    => array( 'address', 'access', 'units', 'phone' ),
				'labels'   => array(),
			),
			'location-tags'   => array(
				'style'       => '',
				'location'    => 0,
				'show_access' => true,
				'limit'       => 0,
				'show_free'   => false,
			),
			'opening-hours'   => array(
				'style'           => '',
				'location'        => 0,
				'title'           => null,
				'note'            => null,
				'highlight_today' => true,
			),
			'location-map'    => array(
				'style'        => '',
				'locations'    => array(),
				'mode'         => '',
				'zoom'         => 12,
				'ratio'        => '16 / 7',
				'ratio_mobile' => '4 / 3',
				'label'        => '',
				'consent'      => null,
			),
			'size-calculator' => array(
				'style'             => '',
				'title'             => null,
				'subtitle'          => null,
				'items'             => null,
				'sizes'             => null,
				'stack'             => 2.2,
				'fill'              => 0.65,
				'unit_toggle'       => true,
				'empty_text'        => null,
				'recommended_label' => null,
				'from_label'        => null,
				'fill_text'         => null,
				'button_text'       => null,
				'button_url'        => '',
				'sync_prices'       => true,
			),
			'size-chooser'    => array(
				'style'        => '',
				'sizes'        => null,
				'selected'     => 3,
				'min_height'   => 0,
				'max_height'   => 0,
				'ratio'        => 0,
				'show_meta'    => 'auto',
				'detail_label' => null,
				'labels'       => array(),
				'price_suffix' => null,
				'button_text'  => null,
				'button_url'   => '',
				'sync_prices'  => true,
			),
			'size-guide'      => array(
				'style'       => '',
				'rows'        => null,
				'headers'     => array(),
				'price_note'  => null,
				'sync_prices' => true,
			),
			'post-grid'       => array(
				'style'          => '',
				'layout'         => 'auto',
				'source'         => 'latest',
				'category'       => array(),
				'count'          => 6,
				'offset'         => 0,
				'featured'       => true,
				'chips'          => false,
				'pagination'     => false,
				'columns'        => 3,
				'show_image'     => true,
				'show_meta'      => true,
				'show_read_time' => true,
				'show_excerpt'   => true,
				'excerpt_length' => 22,
				'read_more'      => '',
				'heading_tag'    => 'h3',
			),
			'enquiry-form'    => array(
				'style'         => '',
				'type'          => 'contact',
				'unit'          => 0,
				'location'      => 0,
				'form_id'       => '',
				'title'         => null,
				'intro'         => null,
				'button_text'   => null,
				'success_text'  => null,
				'note'          => null,
				'show_phone'    => true,
				'show_date'     => 'auto',
				'show_location' => 'auto',
				'labels'        => array(),
				'card'          => true,
			),
		);

		return isset( $defaults[ $name ] ) ? $defaults[ $name ] : array();
	}

	/**
	 * Finds a component template (theme override first).
	 *
	 * @param string $name Component name.
	 * @return string Path or empty string.
	 */
	public static function locate( $name ) {
		$name = sanitize_file_name( $name );

		$theme_file = locate_template( 'storebox-core/' . $name . '.php' );
		if ( $theme_file ) {
			return $theme_file;
		}

		$file = STOREBOX_CORE_DIR . 'templates/' . $name . '.php';

		return file_exists( $file ) ? $file : '';
	}

	/**
	 * Renders a component.
	 *
	 * @param string $name Component name.
	 * @param array  $args Arguments (merged with the component defaults).
	 * @return string HTML.
	 */
	public static function render( $name, $args = array() ) {
		$template = self::locate( $name );

		if ( ! $template ) {
			return '';
		}

		$args          = wp_parse_args( $args, self::defaults( $name ) );
		$args['style'] = storebox_core_style( isset( $args['style'] ) ? $args['style'] : '' );

		/**
		 * Filters a component's arguments before rendering.
		 *
		 * @param array  $args Arguments.
		 * @param string $name Component name.
		 */
		$args = apply_filters( 'storebox_core/component_args', $args, $name );
		$args = apply_filters( "storebox_core/component_args/{$name}", $args );

		self::enqueue( array( $name ) );

		ob_start();
		self::include_template( $template, $args );

		return (string) ob_get_clean();
	}

	/**
	 * Includes a template with $args in scope.
	 *
	 * @param string $template Template path.
	 * @param array  $args     Arguments.
	 */
	private static function include_template( $template, $args ) {
		include $template;
	}

	/**
	 * Resolves a style-dependent "auto" value.
	 *
	 * @param string $value     Value that may be "auto".
	 * @param string $style     Resolved style.
	 * @param string $soft      Value for the soft style.
	 * @param string $editorial Value for the editorial style.
	 * @return string
	 */
	public static function auto( $value, $style, $soft, $editorial ) {
		if ( 'auto' !== $value && '' !== $value && null !== $value ) {
			return $value;
		}

		return 'editorial' === $style ? $editorial : $soft;
	}

	/**
	 * Inline SVG icon (line style) for component templates.
	 *
	 * @param string $name Icon name.
	 * @param int    $size Size in pixels.
	 * @return string
	 */
	public static function icon( $name, $size = 16 ) {
		$paths = array(
			'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
			'arrow-left'  => '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',
			'pin'         => '<path d="M20 10.5c0 5.6-8 12-8 12s-8-6.4-8-12a8 8 0 1 1 16 0z"/><circle cx="12" cy="10.3" r="2.6"/>',
			'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
			'box'         => '<path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
			'phone'       => '<path d="M5 4h3.2l1.6 4.2-2.2 1.4a11 11 0 0 0 6.8 6.8l1.4-2.2L20 15.8V19a1 1 0 0 1-1 1A16 16 0 0 1 4 5a1 1 0 0 1 1-1z"/>',
			'check'       => '<path d="M20 6 9 17l-5-5"/>',
			'close'       => '<path d="M18 6 6 18M6 6l12 12"/>',
		);

		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}

		return sprintf(
			'<svg class="sb-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
			absint( $size ),
			$paths[ $name ]
		);
	}
}
