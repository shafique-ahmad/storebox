<?php
/**
 * Plugin Name: Storebox Elementor Preview (development)
 * Description: Renders pages that carry Elementor data with Elementor-like markup and CSS, for visual checks where Elementor itself cannot be installed. Add ?sbpreview=1 to a page URL. Development tool; not shipped.
 *
 * Install: require this file from wp-content/mu-plugins.
 *
 * What it covers: the Elementor kit (global colours, fonts, theme style),
 * Flexbox Containers, the core widgets the demos use and the Storebox
 * widgets (rendered by their own classes through tools/elementor-stubs.php).
 * It is an approximation of Elementor's output, not a replacement for
 * checking the demos in Elementor.
 *
 * @package Storebox
 */

namespace Storebox_Preview;

defined( 'ABSPATH' ) || exit;

if ( empty( $_GET['sbpreview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only dev switch.
	return;
}

require_once dirname( __DIR__ ) . '/elementor-stubs.php';
\Elementor\Plugin::instance();

const KIT_ID = 990001;

/** Elementor's default breakpoints. */
const MEDIA = array(
	'tablet' => '(max-width: 1024px)',
	'mobile' => '(max-width: 767px)',
);

add_action(
	'plugins_loaded',
	static function () {
		do_action( 'elementor/loaded' );
	},
	0
);

/**
 * Kit settings of the imported demo (from its content.json).
 *
 * @return array
 */
function kit_settings() {
	static $kit = null;
	if ( null === $kit ) {
		$kit    = array();
		$preset = get_theme_mod( 'storebox_preset', 'soft' );
		$file   = WP_PLUGIN_DIR . '/storebox-core/demo/' . ( 'editorial' === $preset ? 'demo-2' : 'demo-1' ) . '/content.json';
		if ( file_exists( $file ) ) {
			$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$kit  = isset( $data['kit'] ) && is_array( $data['kit'] ) ? $data['kit'] : array();
		}
	}
	return $kit;
}

// The kit, as Elementor would store it.
add_filter(
	'pre_option_elementor_active_kit',
	static function () {
		return KIT_ID;
	}
);
add_filter(
	'get_post_metadata',
	static function ( $value, $object_id, $meta_key ) {
		if ( KIT_ID === (int) $object_id && '_elementor_page_settings' === $meta_key ) {
			return array( kit_settings() );
		}
		return $value;
	},
	10,
	3
);

add_filter(
	'body_class',
	static function ( $classes ) {
		$classes[] = 'elementor-default';
		$classes[] = 'elementor-kit-' . KIT_ID;
		if ( is_singular() && Renderer::document() ) {
			$classes[] = 'elementor-page';
			$classes[] = 'elementor-page-' . get_queried_object_id();
		}
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	static function () {
		$base = content_url( 'mu-plugins/sb-preview' );
		wp_enqueue_style( 'sb-preview-fa', home_url( '/sb-fa/css/all.min.css' ), array(), '5.15.4' );
		wp_enqueue_style( 'sb-preview-base', $base . '/base.css', array(), (string) filemtime( __DIR__ . '/base.css' ) );
		wp_add_inline_style( 'sb-preview-base', Renderer::kit_css( kit_settings() ) );
	},
	5
);

// Render early so widget assets are enqueued in the head, as Elementor does.
add_action(
	'wp_enqueue_scripts',
	static function () {
		$doc = Renderer::document();
		if ( $doc ) {
			wp_add_inline_style( 'sb-preview-base', $doc['css'] );
		}
	},
	99
);

add_filter(
	'the_content',
	static function ( $content ) {
		$doc = Renderer::document();
		if ( $doc && in_the_loop() && is_main_query() && get_the_ID() === get_queried_object_id() ) {
			return '<!--sb-preview-->';
		}
		return $content;
	},
	8
);
add_filter(
	'the_content',
	static function ( $content ) {
		if ( false === strpos( $content, '<!--sb-preview-->' ) ) {
			return $content;
		}
		$doc = Renderer::document();
		return preg_replace( '#(<p>)?<!--sb-preview-->(</p>)?#', $doc['html'], $content );
	},
	999
);

/**
 * Elementor-like renderer.
 */
final class Renderer {

	/** @var int */
	private $post_id;

	/** @var array<string, array<string, array<string, string>>> device => selector => declarations */
	private $rules = array(
		''       => array(),
		'tablet' => array(),
		'mobile' => array(),
	);

	/** @var array */
	private $kit;

	/** @var int */
	private $tab_seed = 1000;

	/**
	 * Rendered document of the current page: html and css.
	 *
	 * @return array|null
	 */
	public static function document() {
		static $doc = false;
		if ( false !== $doc ) {
			return $doc;
		}
		$doc = null;
		if ( ! is_singular() ) {
			return $doc;
		}
		$post_id = get_queried_object_id();
		if ( 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
			return $doc;
		}
		$data = json_decode( (string) get_post_meta( $post_id, '_elementor_data', true ), true );
		if ( ! is_array( $data ) ) {
			return $doc;
		}
		$renderer = new self( $post_id, kit_settings() );
		$html     = $renderer->render( $data );
		$doc      = array(
			'html' => $html,
			'css'  => $renderer->css(),
		);
		return $doc;
	}

	/**
	 * @param int   $post_id Post ID.
	 * @param array $kit     Kit settings.
	 */
	public function __construct( $post_id, $kit ) {
		$this->post_id = (int) $post_id;
		$this->kit     = $kit;
	}

	/**
	 * @param array $elements Elements.
	 * @return string
	 */
	public function render( $elements ) {
		$html = '';
		foreach ( $elements as $element ) {
			$html .= $this->element( $element, true );
		}
		return sprintf( '<div data-elementor-type="wp-page" data-elementor-id="%1$d" class="elementor elementor-%1$d">%2$s</div>', $this->post_id, $html );
	}

	/**
	 * Collected CSS.
	 *
	 * @return string
	 */
	public function css() {
		$out = self::rules_css( $this->rules[''] );
		foreach ( MEDIA as $device => $query ) {
			if ( $this->rules[ $device ] ) {
				$out .= '@media ' . $query . '{' . self::rules_css( $this->rules[ $device ] ) . '}';
			}
		}
		return $out;
	}

	/**
	 * @param array $rules selector => declarations.
	 * @return string
	 */
	private static function rules_css( $rules ) {
		$out = '';
		foreach ( $rules as $selector => $decls ) {
			if ( ! $decls ) {
				continue;
			}
			$body = '';
			foreach ( $decls as $prop => $value ) {
				$body .= $prop . ':' . $value . ';';
			}
			$out .= $selector . '{' . $body . '}';
		}
		return $out;
	}

	/**
	 * Adds declarations.
	 *
	 * @param string $device   '' | tablet | mobile.
	 * @param string $selector Selector.
	 * @param array  $decls    Property => value (null values are skipped).
	 */
	private function add( $device, $selector, $decls ) {
		foreach ( $decls as $prop => $value ) {
			if ( null === $value || '' === $value ) {
				continue;
			}
			$this->rules[ $device ][ $selector ][ $prop ] = $value;
		}
	}

	/* ------------------------------------------------------------ values */

	/**
	 * Responsive values of a setting: '' => value, tablet => value, mobile => value.
	 *
	 * @param array  $s   Settings.
	 * @param string $key Key.
	 * @return array
	 */
	private static function responsive( $s, $key ) {
		$out = array();
		foreach ( array( '' => $key, 'tablet' => $key . '_tablet', 'mobile' => $key . '_mobile' ) as $device => $k ) {
			if ( isset( $s[ $k ] ) && '' !== $s[ $k ] && array() !== $s[ $k ] ) {
				$out[ $device ] = $s[ $k ];
			}
		}
		return $out;
	}

	/**
	 * Slider value as CSS.
	 *
	 * @param mixed $v Value.
	 * @return string|null
	 */
	private static function size( $v ) {
		if ( ! is_array( $v ) ) {
			return ( null === $v || '' === $v ) ? null : (string) $v;
		}
		if ( ! isset( $v['size'] ) || '' === $v['size'] || null === $v['size'] ) {
			return null;
		}
		$unit = isset( $v['unit'] ) ? $v['unit'] : 'px';
		return 'custom' === $unit ? (string) $v['size'] : $v['size'] . $unit;
	}

	/**
	 * Dimensions as four CSS values.
	 *
	 * @param mixed $v Value.
	 * @return array|null [top, right, bottom, left]
	 */
	private static function dims( $v ) {
		if ( ! is_array( $v ) ) {
			return null;
		}
		$unit = isset( $v['unit'] ) ? $v['unit'] : 'px';
		$out  = array();
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			if ( ! isset( $v[ $side ] ) || '' === $v[ $side ] ) {
				return null;
			}
			$out[] = 'custom' === $unit ? $v[ $side ] : $v[ $side ] . $unit;
		}
		return $out;
	}

	/**
	 * Global or local colour.
	 *
	 * @param array  $s   Settings.
	 * @param string $key Key.
	 * @return string|null
	 */
	private static function color( $s, $key ) {
		if ( ! empty( $s['__globals__'][ $key ] ) && preg_match( '/id=([a-z0-9_-]+)/i', $s['__globals__'][ $key ], $m ) ) {
			return 'var( --e-global-color-' . $m[1] . ' )';
		}
		return isset( $s[ $key ] ) && '' !== $s[ $key ] ? $s[ $key ] : null;
	}

	/**
	 * Typography declarations per device.
	 *
	 * @param array  $s      Settings.
	 * @param string $prefix Group prefix, e.g. "typography" or "title_typography".
	 * @return array device => declarations
	 */
	private function typography( $s, $prefix ) {
		$fields = array(
			'font_family'     => 'font-family',
			'font_size'       => 'font-size',
			'font_weight'     => 'font-weight',
			'text_transform'  => 'text-transform',
			'font_style'      => 'font-style',
			'text_decoration' => 'text-decoration',
			'line_height'     => 'line-height',
			'letter_spacing'  => 'letter-spacing',
			'word_spacing'    => 'word-spacing',
		);
		$generic = isset( $this->kit['default_generic_fonts'] ) ? $this->kit['default_generic_fonts'] : 'Sans-serif';
		$out     = array();

		if ( ! empty( $s['__globals__'][ $prefix . '_typography' ] ) && preg_match( '/id=([a-z0-9_-]+)/i', $s['__globals__'][ $prefix . '_typography' ], $m ) ) {
			$global = $this->global_typography( $m[1] );
			if ( ! $global ) {
				return $out;
			}
			foreach ( $fields as $field => $prop ) {
				if ( empty( $global[ 'typography_' . $field ] ) ) {
					continue;
				}
				$value = 'var( --e-global-typography-' . $m[1] . '-' . str_replace( '_', '-', $field ) . ' )';
				if ( 'font_family' === $field ) {
					$value .= ', ' . $generic;
				}
				$out[''][ $prop ] = $value;
			}
			return $out;
		}

		if ( ! isset( $s[ $prefix . '_typography' ] ) || 'custom' !== $s[ $prefix . '_typography' ] ) {
			return $out;
		}
		foreach ( $fields as $field => $prop ) {
			foreach ( self::responsive( $s, $prefix . '_' . $field ) as $device => $value ) {
				if ( 'font_family' === $field ) {
					$value = '"' . $value . '", ' . $generic;
				} elseif ( is_array( $value ) ) {
					$value = self::size( $value );
				}
				if ( null !== $value && '' !== $value ) {
					$out[ $device ][ $prop ] = $value;
				}
			}
		}
		return $out;
	}

	/**
	 * Global typography entry.
	 *
	 * @param string $id Global ID.
	 * @return array|null
	 */
	private function global_typography( $id ) {
		foreach ( array( 'system_typography', 'custom_typography' ) as $group ) {
			foreach ( isset( $this->kit[ $group ] ) ? (array) $this->kit[ $group ] : array() as $entry ) {
				if ( isset( $entry['_id'] ) && $entry['_id'] === $id ) {
					return $entry;
				}
			}
		}
		return null;
	}

	/**
	 * Adds per-device declaration sets.
	 *
	 * @param string $selector Selector.
	 * @param array  $by_device device => declarations.
	 */
	private function add_devices( $selector, $by_device ) {
		foreach ( $by_device as $device => $decls ) {
			$this->add( $device, $selector, $decls );
		}
	}

	/**
	 * Resolves [elementor-tag] strings in __dynamic__.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	private function apply_dynamic( $s ) {
		if ( empty( $s['__dynamic__'] ) || ! is_array( $s['__dynamic__'] ) ) {
			return $s;
		}
		foreach ( $s['__dynamic__'] as $key => $tag ) {
			if ( ! preg_match( '/name="([^"]+)" settings="([^"]*)"/', (string) $tag, $m ) ) {
				continue;
			}
			$settings = json_decode( urldecode( $m[2] ), true );
			$value    = Tags::value( $m[1], is_array( $settings ) ? $settings : array() );
			if ( null !== $value ) {
				$s[ $key ] = $value;
			}
		}
		return $s;
	}

	/* ---------------------------------------------------------- elements */

	/**
	 * @param array $el     Element.
	 * @param bool  $parent Top level.
	 * @return string
	 */
	private function element( $el, $parent = false ) {
		$s = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
		$s = $this->apply_dynamic( $s );

		if ( 'container' === $el['elType'] ) {
			return $this->container( $el, $s, $parent );
		}
		if ( 'widget' === $el['elType'] ) {
			return $this->widget( $el, $s );
		}
		return '';
	}

	/**
	 * Selector of an element.
	 *
	 * @param string $id Element ID.
	 * @return string
	 */
	private function sel( $id ) {
		return '.elementor-' . $this->post_id . ' .elementor-element.elementor-element-' . $id;
	}

	/**
	 * Visibility classes.
	 *
	 * @param array $s Settings.
	 * @return string[]
	 */
	private static function hidden_classes( $s ) {
		$classes = array();
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			if ( ! empty( $s[ 'hide_' . $device ] ) ) {
				$classes[] = 'elementor-hidden-' . $device;
			}
		}
		return $classes;
	}

	/**
	 * Flex item settings shared by containers and widgets.
	 *
	 * @param string $sel Selector.
	 * @param array  $s   Settings.
	 */
	private function flex_item( $sel, $s ) {
		$sizes = array(
			'grow'   => array( 1, 0 ),
			'shrink' => array( 0, 1 ),
			'none'   => array( 0, 0 ),
		);
		foreach ( self::responsive( $s, '_flex_size' ) as $device => $value ) {
			if ( isset( $sizes[ $value ] ) ) {
				$this->add(
					$device,
					$sel,
					array(
						'--flex-grow'   => (string) $sizes[ $value ][0],
						'--flex-shrink' => (string) $sizes[ $value ][1],
						'flex-grow'     => (string) $sizes[ $value ][0],
						'flex-shrink'   => (string) $sizes[ $value ][1],
					)
				);
			}
		}
		foreach ( self::responsive( $s, '_flex_align_self' ) as $device => $value ) {
			$this->add( $device, $sel, array( 'align-self' => $value ) );
		}
		foreach ( self::responsive( $s, '_flex_order' ) as $device => $value ) {
			$order = array(
				'start' => '-99999',
				'end'   => '99999',
			);
			if ( 'custom' === $value ) {
				$custom = self::responsive( $s, '_flex_order_custom' );
				$order  = isset( $custom[ $device ] ) ? $custom[ $device ] : null;
			} else {
				$order = isset( $order[ $value ] ) ? $order[ $value ] : null;
			}
			$this->add( $device, $sel, array( 'order' => $order ) );
		}
	}

	/**
	 * Container.
	 *
	 * @param array $el     Element.
	 * @param array $s      Settings.
	 * @param bool  $parent Top level.
	 * @return string
	 */
	private function container( $el, $s, $parent ) {
		$id    = $el['id'];
		$sel   = $this->sel( $id );
		$boxed = isset( $s['content_width'] ) && 'boxed' === $s['content_width'];

		$this->add( '', $sel, array( '--display' => 'flex' ) );

		$directions = array(
			'row'            => array( 'initial', '100%', '1', 'stretch' ),
			'row-reverse'    => array( 'initial', '100%', '1', 'stretch' ),
			'column'         => array( '100%', 'initial', '0', 'initial' ),
			'column-reverse' => array( '100%', 'initial', '0', 'initial' ),
		);
		foreach ( self::responsive( $s, 'flex_direction' ) as $device => $dir ) {
			if ( isset( $directions[ $dir ] ) ) {
				list( $w, $h, $g, $a ) = $directions[ $dir ];
				$this->add(
					$device,
					$sel,
					array(
						'--flex-direction'               => $dir,
						'--container-widget-width'       => $w,
						'--container-widget-height'      => $h,
						'--container-widget-flex-grow'   => $g,
						'--container-widget-align-self'  => $a,
						'--flex-wrap-mobile'             => 'wrap',
					)
				);
			}
		}
		foreach ( self::responsive( $s, 'flex_justify_content' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--justify-content' => $value ) );
		}
		foreach ( self::responsive( $s, 'flex_align_items' ) as $device => $value ) {
			$this->add(
				$device,
				$sel,
				array(
					'--align-items'            => $value,
					'--container-widget-width' => 'calc( ( 1 - var( --container-widget-flex-grow ) ) * 100% )',
				)
			);
		}
		foreach ( self::responsive( $s, 'flex_wrap' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--flex-wrap' => $value ) );
		}
		foreach ( self::responsive( $s, 'flex_gap' ) as $device => $gap ) {
			if ( is_array( $gap ) ) {
				$unit   = isset( $gap['unit'] ) ? $gap['unit'] : 'px';
				$column = isset( $gap['column'] ) && '' !== $gap['column'] ? $gap['column'] : ( isset( $gap['size'] ) ? $gap['size'] : '' );
				$row    = isset( $gap['row'] ) && '' !== $gap['row'] ? $gap['row'] : $column;
				if ( '' !== $column ) {
					$this->add(
						$device,
						$sel,
						array(
							'--gap'        => $row . $unit . ' ' . $column . $unit,
							'--row-gap'    => $row . $unit,
							'--column-gap' => $column . $unit,
						)
					);
				}
			}
		}
		foreach ( self::responsive( $s, 'boxed_width' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--content-width' => self::size( $value ) ) );
		}
		foreach ( self::responsive( $s, 'width' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--width' => self::size( $value ) ) );
		}
		foreach ( self::responsive( $s, 'min_height' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--min-height' => self::size( $value ) ) );
		}
		foreach ( array( 'padding', 'margin' ) as $box ) {
			foreach ( self::responsive( $s, $box ) as $device => $value ) {
				$d = self::dims( $value );
				if ( $d ) {
					$this->add(
						$device,
						$sel,
						array(
							'--' . $box . '-top'    => $d[0],
							'--' . $box . '-right'  => $d[1],
							'--' . $box . '-bottom' => $d[2],
							'--' . $box . '-left'   => $d[3],
						)
					);
				}
			}
		}
		if ( ! empty( $s['overflow'] ) ) {
			$this->add( '', $sel, array( '--overflow' => $s['overflow'] ) );
		}
		foreach ( self::responsive( $s, 'z_index' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--z-index' => (string) $value ) );
		}
		$this->background( $sel, $s, '' );
		$this->border( $sel, $s, true );
		$this->flex_item( $sel, $s );

		if ( ! empty( $s['sticky'] ) ) {
			$offset = isset( $s['sticky_offset'] ) ? (int) $s['sticky_offset'] : 0;
			$this->add( '', $sel, array( 'position' => 'sticky', 'top' => ( $offset + ( is_admin_bar_showing() ? 32 : 0 ) ) . 'px', 'z-index' => '99', 'align-self' => 'flex-start' ) );
			if ( ! empty( $s['sticky_on'] ) && ! in_array( 'tablet', (array) $s['sticky_on'], true ) ) {
				$this->add( 'tablet', $sel, array( 'position' => 'relative', 'top' => 'auto' ) );
			}
		}

		$classes = array_merge(
			array(
				'elementor-element',
				'elementor-element-' . $id,
				'e-flex',
				$boxed ? 'e-con-boxed' : 'e-con-full',
				'e-con',
				$parent ? 'e-parent' : 'e-child',
			),
			self::hidden_classes( $s )
		);
		if ( ! empty( $s['css_classes'] ) ) {
			$classes[] = $s['css_classes'];
		}

		$children = '';
		foreach ( isset( $el['elements'] ) ? $el['elements'] : array() as $child ) {
			$children .= $this->element( $child );
		}
		if ( $boxed ) {
			$children = '<div class="e-con-inner">' . $children . '</div>';
		}

		$tag   = ! empty( $s['html_tag'] ) ? $s['html_tag'] : 'div';
		$attrs = sprintf( ' class="%s" data-id="%s" data-element_type="container"', esc_attr( implode( ' ', $classes ) ), esc_attr( $id ) );
		if ( ! empty( $s['_element_id'] ) ) {
			$attrs .= ' id="' . esc_attr( $s['_element_id'] ) . '"';
		}
		if ( 'a' === $tag && ! empty( $s['link']['url'] ) ) {
			$attrs .= ' href="' . esc_url( $s['link']['url'] ) . '"';
		}
		return '<' . $tag . $attrs . '>' . $children . '</' . $tag . '>';
	}

	/**
	 * Background of a container.
	 *
	 * @param string $sel    Selector.
	 * @param array  $s      Settings.
	 * @param string $prefix Setting prefix ("" or "background_overlay").
	 */
	private function background( $sel, $s, $prefix ) {
		if ( ! empty( $s['background_background'] ) ) {
			$this->fill( $sel, $s, 'background' );
		}
		if ( ! empty( $s['background_overlay_background'] ) ) {
			$this->add( '', $sel, array( '--background-overlay' => "''" ) );
			$this->fill( $sel . '::before', $s, 'background_overlay' );
			if ( isset( $s['background_overlay_opacity'] ) ) {
				$this->add( '', $sel, array( '--overlay-opacity' => self::size( $s['background_overlay_opacity'] ) ) );
			}
		}
	}

	/**
	 * Classic or gradient background group.
	 *
	 * @param string $sel    Selector.
	 * @param array  $s      Settings.
	 * @param string $prefix Group prefix.
	 */
	private function fill( $sel, $s, $prefix ) {
		$type = $s[ $prefix . '_background' ];
		if ( 'gradient' === $type ) {
			$angle = isset( $s[ $prefix . '_gradient_angle' ] ) ? self::size( $s[ $prefix . '_gradient_angle' ] ) : '180deg';
			$stop  = isset( $s[ $prefix . '_color_stop' ] ) ? self::size( $s[ $prefix . '_color_stop' ] ) : '0%';
			$stopb = isset( $s[ $prefix . '_color_b_stop' ] ) ? self::size( $s[ $prefix . '_color_b_stop' ] ) : '100%';
			$a     = self::color( $s, $prefix . '_color' );
			$b     = self::color( $s, $prefix . '_color_b' );
			$this->add(
				'',
				$sel,
				array(
					'background-color' => 'transparent',
					'background-image' => sprintf( 'linear-gradient(%s, %s %s, %s %s)', $angle, $a ? $a : 'transparent', $stop, $b ? $b : 'transparent', $stopb ),
				)
			);
			return;
		}
		$this->add( '', $sel, array( 'background-color' => self::color( $s, $prefix . '_color' ) ) );
		foreach ( self::responsive( $s, $prefix . '_image' ) as $device => $image ) {
			if ( ! empty( $image['url'] ) ) {
				$this->add( $device, $sel, array( 'background-image' => 'url("' . esc_url_raw( $image['url'] ) . '")' ) );
			}
		}
		foreach ( array( 'position', 'repeat', 'size', 'attachment' ) as $prop ) {
			foreach ( self::responsive( $s, $prefix . '_' . $prop ) as $device => $value ) {
				$this->add( $device, $sel, array( 'background-' . $prop => $value ) );
			}
		}
	}

	/**
	 * Border, radius and shadow.
	 *
	 * @param string $sel       Selector.
	 * @param array  $s         Settings.
	 * @param bool   $container Container variables.
	 * @param string $prefix    Setting prefix.
	 */
	private function border( $sel, $s, $container, $prefix = '' ) {
		if ( ! empty( $s[ $prefix . 'border_border' ] ) ) {
			$this->add( '', $sel, array( 'border-style' => $s[ $prefix . 'border_border' ] ) );
			if ( $container ) {
				$this->add( '', $sel, array( '--border-style' => $s[ $prefix . 'border_border' ], '--border-color' => self::color( $s, $prefix . 'border_color' ) ) );
			}
			foreach ( self::responsive( $s, $prefix . 'border_width' ) as $device => $value ) {
				$d = self::dims( $value );
				if ( $d ) {
					$this->add( $device, $sel, array( 'border-width' => implode( ' ', $d ) ) );
					if ( $container ) {
						$this->add(
							$device,
							$sel,
							array(
								'--border-top-width'    => $d[0],
								'--border-right-width'  => $d[1],
								'--border-bottom-width' => $d[2],
								'--border-left-width'   => $d[3],
							)
						);
					}
				}
			}
			$this->add( '', $sel, array( 'border-color' => self::color( $s, $prefix . 'border_color' ) ) );
		}
		foreach ( self::responsive( $s, $prefix . 'border_radius' ) as $device => $value ) {
			$d = self::dims( $value );
			if ( $d ) {
				$this->add( $device, $sel, $container ? array( '--border-radius' => implode( ' ', $d ) ) : array( 'border-radius' => implode( ' ', $d ) ) );
			}
		}
		if ( ! empty( $s[ $prefix . 'box_shadow_box_shadow_type' ] ) && ! empty( $s[ $prefix . 'box_shadow_box_shadow' ] ) ) {
			$b = $s[ $prefix . 'box_shadow_box_shadow' ];
			$this->add(
				'',
				$sel,
				array(
					'box-shadow' => sprintf(
						'%spx %spx %spx %spx %s',
						isset( $b['horizontal'] ) ? $b['horizontal'] : 0,
						isset( $b['vertical'] ) ? $b['vertical'] : 0,
						isset( $b['blur'] ) ? $b['blur'] : 10,
						isset( $b['spread'] ) ? $b['spread'] : 0,
						isset( $b['color'] ) ? $b['color'] : 'rgba(0,0,0,0.5)'
					),
				)
			);
		}
	}

	/* ------------------------------------------------------------ widgets */

	/**
	 * Widget.
	 *
	 * @param array $el Element.
	 * @param array $s  Settings.
	 * @return string
	 */
	private function widget( $el, $s ) {
		$id      = $el['id'];
		$type    = $el['widgetType'];
		$sel     = $this->sel( $id );
		$classes = array( 'elementor-element', 'elementor-element-' . $id, 'elementor-widget', 'elementor-widget-' . $type );

		// Common (Advanced tab) settings.
		foreach ( array( '_margin' => 'margin', '_padding' => 'padding' ) as $key => $prop ) {
			foreach ( self::responsive( $s, $key ) as $device => $value ) {
				$d = self::dims( $value );
				if ( $d ) {
					$this->add( $device, $sel . ' > .elementor-widget-container', array( $prop => implode( ' ', $d ) ) );
				}
			}
		}
		foreach ( self::responsive( $s, '_element_width' ) as $device => $value ) {
			$infix = '' === $device ? '' : '-' . $device;
			if ( 'initial' === $value ) {
				$classes[] = 'elementor-widget' . $infix . '__width-initial';
				$custom    = self::responsive( $s, '_element_custom_width' );
				if ( isset( $custom[ $device ] ) ) {
					$w = self::size( $custom[ $device ] );
					$this->add( $device, $sel, array( '--container-widget-width' => $w, '--container-widget-flex-grow' => '0', 'width' => 'var( --container-widget-width, ' . $w . ' )', 'max-width' => $w ) );
				}
			} elseif ( 'auto' === $value ) {
				$classes[] = 'elementor-widget' . $infix . '__width-auto';
				$this->add( $device, $sel, array( 'width' => 'auto', 'max-width' => '100%', '--container-widget-flex-grow' => '0' ) );
			} elseif ( 'inherit' === $value ) {
				$classes[] = 'elementor-widget' . $infix . '__width-inherit';
				$this->add( $device, $sel, array( 'width' => '100%', 'max-width' => '100%', '--container-widget-width' => '100%' ) );
			}
		}
		$this->flex_item( $sel, $s );
		if ( isset( $s['_z_index'] ) && '' !== $s['_z_index'] ) {
			$this->add( '', $sel, array( 'z-index' => (string) $s['_z_index'] ) );
		}
		$classes = array_merge( $classes, self::hidden_classes( $s ) );
		if ( ! empty( $s['_css_classes'] ) ) {
			$classes[] = $s['_css_classes'];
		}

		$method = 'w_' . str_replace( '-', '_', $type );
		if ( 0 === strpos( $type, 'storebox-' ) ) {
			$inner = $this->storebox_widget( $type, $s, $sel );
		} elseif ( method_exists( $this, $method ) ) {
			$inner = $this->$method( $s, $sel, $classes );
		} else {
			$inner = '<div style="padding:12px;border:2px dashed #c33;color:#c33;font:600 13px sans-serif">Preview: widget "' . esc_html( $type ) . '" not emulated</div>';
		}

		$attrs = sprintf(
			' class="%s" data-id="%s" data-element_type="widget" data-widget_type="%s.default"',
			esc_attr( implode( ' ', array_unique( $classes ) ) ),
			esc_attr( $id ),
			esc_attr( $type )
		);
		if ( ! empty( $s['_element_id'] ) ) {
			$attrs .= ' id="' . esc_attr( $s['_element_id'] ) . '"';
		}
		return '<div' . $attrs . '><div class="elementor-widget-container">' . $inner . '</div></div>';
	}

	/**
	 * Link attributes.
	 *
	 * @param mixed $link Link setting.
	 * @return string
	 */
	private static function href( $link ) {
		if ( empty( $link['url'] ) ) {
			return '';
		}
		$attrs = ' href="' . esc_url( $link['url'] ) . '"';
		if ( ! empty( $link['is_external'] ) ) {
			$attrs .= ' target="_blank"';
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$attrs .= ' rel="nofollow"';
		}
		return $attrs;
	}

	/**
	 * Icon markup.
	 *
	 * @param mixed $icon Icon setting.
	 * @return string
	 */
	private static function icon( $icon ) {
		if ( empty( $icon['value'] ) ) {
			return '';
		}
		if ( is_array( $icon['value'] ) ) {
			return ! empty( $icon['value']['url'] ) ? '<img src="' . esc_url( $icon['value']['url'] ) . '" alt="">' : '';
		}
		return '<i aria-hidden="true" class="' . esc_attr( $icon['value'] ) . '"></i>';
	}

	/**
	 * Heading.
	 */
	private function w_heading( $s, $sel ) {
		$tag   = ! empty( $s['header_size'] ) ? $s['header_size'] : 'h2';
		$title = isset( $s['title'] ) ? $s['title'] : '';
		if ( ! empty( $s['link']['url'] ) ) {
			$title = '<a' . self::href( $s['link'] ) . '>' . $title . '</a>';
		}
		foreach ( self::responsive( $s, 'align' ) as $device => $value ) {
			$this->add( $device, $sel, array( 'text-align' => $value ) );
		}
		$this->add( '', $sel . ' .elementor-heading-title', array( 'color' => self::color( $s, 'title_color' ) ) );
		$this->add_devices( $sel . ' .elementor-heading-title', $this->typography( $s, 'typography' ) );
		if ( '' === $title ) {
			return '';
		}
		return sprintf( '<%1$s class="elementor-heading-title elementor-size-%3$s">%2$s</%1$s>', tag_escape( $tag ), wp_kses_post( $title ), esc_attr( ! empty( $s['size'] ) ? $s['size'] : 'default' ) );
	}

	/**
	 * Text editor.
	 */
	private function w_text_editor( $s, $sel ) {
		foreach ( self::responsive( $s, 'align' ) as $device => $value ) {
			$this->add( $device, $sel, array( 'text-align' => $value ) );
		}
		$this->add( '', $sel, array( 'color' => self::color( $s, 'text_color' ) ) );
		$this->add_devices( $sel, $this->typography( $s, 'typography' ) );
		$html = isset( $s['editor'] ) ? (string) $s['editor'] : '';
		return wptexturize( do_shortcode( shortcode_unautop( $html ) ) );
	}

	/**
	 * Button.
	 */
	private function w_button( $s, $sel ) {
		$btn = $sel . ' .elementor-button';
		foreach ( self::responsive( $s, 'align' ) as $device => $value ) {
			if ( 'justify' === $value ) {
				$this->add( $device, $btn, array( 'width' => '100%' ) );
			} else {
				$map = array(
					'left'   => 'left',
					'center' => 'center',
					'right'  => 'right',
					'start'  => 'start',
					'end'    => 'end',
				);
				$this->add( $device, $sel . ' .elementor-button-wrapper', array( 'text-align' => isset( $map[ $value ] ) ? $map[ $value ] : $value ) );
			}
		}
		$this->add_devices( $btn, $this->typography( $s, 'typography' ) );
		$text = self::color( $s, 'button_text_color' );
		$this->add( '', $btn, array( 'color' => $text, 'fill' => $text ) );
		if ( ! empty( $s['background_background'] ) ) {
			$this->fill( $btn, $s, 'background' );
		}
		$hover = $btn . ':hover, ' . $btn . ':focus';
		$this->add( '', $hover, array( 'color' => self::color( $s, 'hover_color' ) ) );
		if ( ! empty( $s['button_background_hover_background'] ) ) {
			$this->add( '', $hover, array( 'background-color' => self::color( $s, 'button_background_hover_color' ) ) );
		}
		$this->add( '', $hover, array( 'border-color' => self::color( $s, 'button_hover_border_color' ) ) );
		$this->border( $btn, $s, false );
		foreach ( self::responsive( $s, 'text_padding' ) as $device => $value ) {
			$d = self::dims( $value );
			if ( $d ) {
				$this->add( $device, $btn, array( 'padding' => implode( ' ', $d ) ) );
			}
		}
		if ( isset( $s['icon_indent'] ) ) {
			$this->add( '', $btn . ' .elementor-button-content-wrapper', array( 'gap' => self::size( $s['icon_indent'] ) ) );
		}
		$icon = ! empty( $s['selected_icon'] ) ? self::icon( $s['selected_icon'] ) : '';
		if ( $icon && isset( $s['icon_align'] ) && in_array( $s['icon_align'], array( 'right', 'row-reverse' ), true ) ) {
			$this->add( '', $btn . ' .elementor-button-content-wrapper', array( 'flex-direction' => 'row-reverse' ) );
		}
		return sprintf(
			'<div class="elementor-button-wrapper"><a class="elementor-button elementor-button-link elementor-size-%1$s"%2$s><span class="elementor-button-content-wrapper">%3$s<span class="elementor-button-text">%4$s</span></span></a></div>',
			esc_attr( ! empty( $s['size'] ) ? $s['size'] : 'sm' ),
			self::href( isset( $s['link'] ) ? $s['link'] : array() ),
			$icon ? '<span class="elementor-button-icon">' . $icon . '</span>' : '',
			wp_kses_post( isset( $s['text'] ) ? $s['text'] : '' )
		);
	}

	/**
	 * Image.
	 */
	private function w_image( $s, $sel ) {
		foreach ( self::responsive( $s, 'align' ) as $device => $value ) {
			$this->add( $device, $sel, array( 'text-align' => $value ) );
		}
		foreach ( array( 'width' => 'width', 'space' => 'max-width', 'height' => 'height' ) as $key => $prop ) {
			foreach ( self::responsive( $s, $key ) as $device => $value ) {
				$this->add( $device, $sel . ' img', array( $prop => self::size( $value ) ) );
			}
		}
		foreach ( self::responsive( $s, 'object-fit' ) as $device => $value ) {
			$this->add( $device, $sel . ' img', array( 'object-fit' => $value ) );
		}
		foreach ( self::responsive( $s, 'object-position' ) as $device => $value ) {
			$this->add( $device, $sel . ' img', array( 'object-position' => $value ) );
		}
		foreach ( self::responsive( $s, 'image_border_radius' ) as $device => $value ) {
			$d = self::dims( $value );
			if ( $d ) {
				$this->add( $device, $sel . ' img', array( 'border-radius' => implode( ' ', $d ) ) );
			}
		}
		$image = isset( $s['image'] ) ? $s['image'] : array();
		$size  = ! empty( $s['image_size'] ) ? $s['image_size'] : 'large';
		if ( ! empty( $image['id'] ) ) {
			$html = wp_get_attachment_image( (int) $image['id'], $size, false, array( 'class' => 'attachment-' . $size . ' size-' . $size . ' wp-image-' . (int) $image['id'] ) );
		} elseif ( ! empty( $image['url'] ) ) {
			$html = '<img src="' . esc_url( $image['url'] ) . '" alt="' . esc_attr( isset( $image['alt'] ) ? $image['alt'] : '' ) . '">';
		} else {
			$html = '<img src="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27800%27 height=%27500%27%3E%3Crect width=%27100%25%27 height=%27100%25%27 fill=%27%23ddd%27/%3E%3C/svg%3E" alt="">';
		}
		if ( ! empty( $s['link_to'] ) && 'custom' === $s['link_to'] && ! empty( $s['link']['url'] ) ) {
			$html = '<a' . self::href( $s['link'] ) . '>' . $html . '</a>';
		}
		return $html;
	}

	/**
	 * Icon.
	 */
	private function w_icon( $s, $sel, &$classes ) {
		$view      = ! empty( $s['view'] ) ? $s['view'] : 'default';
		$classes[] = 'elementor-view-' . $view;
		if ( 'default' !== $view && ! empty( $s['shape'] ) ) {
			$classes[] = 'elementor-shape-' . $s['shape'];
		}
		$primary   = self::color( $s, 'primary_color' );
		$secondary = self::color( $s, 'secondary_color' );
		if ( 'stacked' === $view ) {
			$this->add( '', $sel . '.elementor-view-stacked .elementor-icon', array( 'background-color' => $primary, 'color' => $secondary, 'fill' => $secondary ) );
		} else {
			$this->add( '', $sel . '.elementor-view-' . $view . ' .elementor-icon', array( 'color' => $primary, 'border-color' => $primary, 'fill' => $primary ) );
			if ( 'framed' === $view ) {
				$this->add( '', $sel . '.elementor-view-framed .elementor-icon', array( 'background-color' => $secondary ) );
			}
		}
		foreach ( self::responsive( $s, 'size' ) as $device => $value ) {
			$this->add( $device, $sel . ' .elementor-icon', array( 'font-size' => self::size( $value ) ) );
		}
		foreach ( self::responsive( $s, 'icon_padding' ) as $device => $value ) {
			$this->add( $device, $sel . ' .elementor-icon', array( 'padding' => self::size( $value ) ) );
		}
		foreach ( self::responsive( $s, 'border_radius' ) as $device => $value ) {
			$d = self::dims( $value );
			if ( $d ) {
				$this->add( $device, $sel . ' .elementor-icon', array( 'border-radius' => implode( ' ', $d ) ) );
			}
		}
		foreach ( self::responsive( $s, 'align' ) as $device => $value ) {
			$this->add( $device, $sel . ' .elementor-icon-wrapper', array( 'text-align' => $value ) );
		}
		$tag = ! empty( $s['link']['url'] ) ? 'a' : 'div';
		return '<div class="elementor-icon-wrapper"><' . $tag . ' class="elementor-icon"' . ( 'a' === $tag ? self::href( $s['link'] ) : '' ) . '>' . self::icon( isset( $s['selected_icon'] ) ? $s['selected_icon'] : array() ) . '</' . $tag . '></div>';
	}

	/**
	 * Icon list.
	 */
	private function w_icon_list( $s, $sel ) {
		$inline = isset( $s['view'] ) && 'inline' === $s['view'];
		foreach ( self::responsive( $s, 'space_between' ) as $device => $value ) {
			$v = self::size( $value );
			if ( $inline ) {
				$this->add( $device, $sel . ' .elementor-icon-list-items.elementor-inline-items .elementor-icon-list-item', array( 'margin-right' => 'calc(' . $v . '/2)', 'margin-left' => 'calc(' . $v . '/2)' ) );
				$this->add( $device, $sel . ' .elementor-icon-list-items.elementor-inline-items', array( 'margin-right' => 'calc(-' . $v . '/2)', 'margin-left' => 'calc(-' . $v . '/2)' ) );
			} else {
				$this->add( $device, $sel . ' .elementor-icon-list-items:not(.elementor-inline-items) .elementor-icon-list-item:not(:last-child)', array( 'padding-bottom' => 'calc(' . $v . '/2)' ) );
				$this->add( $device, $sel . ' .elementor-icon-list-items:not(.elementor-inline-items) .elementor-icon-list-item:not(:first-child)', array( 'margin-top' => 'calc(' . $v . '/2)' ) );
			}
		}
		$icon_color = self::color( $s, 'icon_color' );
		$this->add( '', $sel . ' .elementor-icon-list-icon i', array( 'color' => $icon_color ) );
		$this->add( '', $sel . ' .elementor-icon-list-icon svg', array( 'fill' => $icon_color ) );
		foreach ( self::responsive( $s, 'icon_size' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--e-icon-list-icon-size' => self::size( $value ) ) );
		}
		$text = $sel . ' .elementor-icon-list-item > .elementor-icon-list-text, ' . $sel . ' .elementor-icon-list-item > a';
		$this->add( '', $sel . ' .elementor-icon-list-text', array( 'color' => self::color( $s, 'text_color' ) ) );
		$this->add( '', $sel . ' .elementor-icon-list-item:hover .elementor-icon-list-text', array( 'color' => self::color( $s, 'text_color_hover' ) ) );
		foreach ( self::responsive( $s, 'text_indent' ) as $device => $value ) {
			$this->add( $device, $sel . ' .elementor-icon-list-text', array( 'padding-inline-start' => self::size( $value ) ) );
		}
		$this->add_devices( $text, $this->typography( $s, 'icon_typography' ) );

		$items = '';
		foreach ( isset( $s['icon_list'] ) ? (array) $s['icon_list'] : array() as $item ) {
			$content = '<span class="elementor-icon-list-icon">' . self::icon( isset( $item['selected_icon'] ) ? $item['selected_icon'] : array() ) . '</span><span class="elementor-icon-list-text">' . wp_kses_post( isset( $item['text'] ) ? $item['text'] : '' ) . '</span>';
			if ( ! empty( $item['link']['url'] ) ) {
				$content = '<a' . self::href( $item['link'] ) . '>' . $content . '</a>';
			}
			$items .= '<li class="elementor-icon-list-item' . ( $inline ? ' elementor-inline-item' : '' ) . '">' . $content . '</li>';
		}
		return '<ul class="elementor-icon-list-items' . ( $inline ? ' elementor-inline-items' : '' ) . '">' . $items . '</ul>';
	}

	/**
	 * Counter (shows the final number).
	 */
	private function w_counter( $s, $sel ) {
		$this->add( '', $sel . ' .elementor-counter-number-wrapper', array( 'color' => self::color( $s, 'number_color' ) ) );
		$this->add_devices( $sel . ' .elementor-counter-number-wrapper', $this->typography( $s, 'typography_number' ) );
		$this->add( '', $sel . ' .elementor-counter-title', array( 'color' => self::color( $s, 'title_color' ) ) );
		$this->add_devices( $sel . ' .elementor-counter-title', $this->typography( $s, 'typography_title' ) );

		$end = isset( $s['ending_number'] ) ? $s['ending_number'] : 100;
		if ( ! empty( $s['thousand_separator'] ) && is_numeric( $end ) ) {
			$sep = isset( $s['thousand_separator_char'] ) ? $s['thousand_separator_char'] : ',';
			$end = number_format( (float) $end, 0, '.', $sep );
		}
		$title = ! empty( $s['title'] ) ? '<div class="elementor-counter-title">' . esc_html( $s['title'] ) . '</div>' : '';
		return sprintf(
			'<div class="elementor-counter"><div class="elementor-counter-number-wrapper"><span class="elementor-counter-number-prefix">%1$s</span><span class="elementor-counter-number">%2$s</span><span class="elementor-counter-number-suffix">%3$s</span></div>%4$s</div>',
			esc_html( isset( $s['prefix'] ) ? $s['prefix'] : '' ),
			esc_html( $end ),
			esc_html( isset( $s['suffix'] ) ? $s['suffix'] : '' ),
			$title
		);
	}

	/**
	 * Accordion (classic).
	 */
	private function w_accordion( $s, $sel ) {
		$item = $sel . ' .elementor-accordion-item';
		foreach ( self::responsive( $s, 'border_width' ) as $device => $value ) {
			$w = self::size( $value );
			$this->add( $device, $item, array( 'border-width' => $w ) );
			$this->add( $device, $item . ' .elementor-tab-content', array( 'border-width' => $w ) );
			$this->add( $device, $item . ' .elementor-tab-title.elementor-active', array( 'border-width' => $w ) );
		}
		$border = self::color( $s, 'border_color' );
		$this->add( '', $item, array( 'border-color' => $border ) );
		$this->add( '', $item . ' .elementor-tab-content', array( 'border-top-color' => $border ) );
		$this->add( '', $item . ' .elementor-tab-title.elementor-active', array( 'border-bottom-color' => $border ) );
		$this->add( '', $sel . ' .elementor-tab-title', array( 'background-color' => self::color( $s, 'title_background' ) ) );
		$title_color = self::color( $s, 'title_color' );
		$this->add( '', $sel . ' .elementor-accordion-icon, ' . $sel . ' .elementor-accordion-title', array( 'color' => $title_color ) );
		$active = self::color( $s, 'tab_active_color' );
		$this->add( '', $sel . ' .elementor-active .elementor-accordion-icon, ' . $sel . ' .elementor-active .elementor-accordion-title', array( 'color' => $active ) );
		$this->add_devices( $sel . ' .elementor-accordion-title', $this->typography( $s, 'title_typography' ) );
		foreach ( self::responsive( $s, 'title_padding' ) as $device => $value ) {
			$d = self::dims( $value );
			if ( $d ) {
				$this->add( $device, $sel . ' .elementor-tab-title', array( 'padding' => implode( ' ', $d ) ) );
			}
		}
		$this->add( '', $sel . ' .elementor-tab-title .elementor-accordion-icon i:before', array( 'color' => self::color( $s, 'icon_color' ) ) );
		$this->add( '', $sel . ' .elementor-tab-title.elementor-active .elementor-accordion-icon i:before', array( 'color' => self::color( $s, 'icon_active_color' ) ) );
		foreach ( self::responsive( $s, 'icon_space' ) as $device => $value ) {
			$this->add( $device, $sel . ' .elementor-accordion-icon.elementor-accordion-icon-left', array( 'margin-right' => self::size( $value ) ) );
			$this->add( $device, $sel . ' .elementor-accordion-icon.elementor-accordion-icon-right', array( 'margin-left' => self::size( $value ) ) );
		}
		$this->add( '', $sel . ' .elementor-tab-content', array( 'background-color' => self::color( $s, 'content_background_color' ), 'color' => self::color( $s, 'content_color' ) ) );
		$this->add_devices( $sel . ' .elementor-tab-content', $this->typography( $s, 'content_typography' ) );
		foreach ( self::responsive( $s, 'content_padding' ) as $device => $value ) {
			$d = self::dims( $value );
			if ( $d ) {
				$this->add( $device, $sel . ' .elementor-tab-content', array( 'padding' => implode( ' ', $d ) ) );
			}
		}

		$tag   = ! empty( $s['title_html_tag'] ) ? $s['title_html_tag'] : 'div';
		$align = ! empty( $s['icon_align'] ) ? $s['icon_align'] : 'right';
		$out   = '';
		foreach ( isset( $s['tabs'] ) ? (array) $s['tabs'] : array() as $i => $tab ) {
			$n    = ++$this->tab_seed;
			$icon = '<span class="elementor-accordion-icon elementor-accordion-icon-' . esc_attr( $align ) . '" aria-hidden="true"><span class="elementor-accordion-icon-closed">' . self::icon( isset( $s['selected_icon'] ) ? $s['selected_icon'] : array() ) . '</span><span class="elementor-accordion-icon-opened">' . self::icon( isset( $s['selected_active_icon'] ) ? $s['selected_active_icon'] : array() ) . '</span></span>';
			$out .= sprintf(
				'<div class="elementor-accordion-item"><%1$s id="elementor-tab-title-%2$d" class="elementor-tab-title" data-tab="%3$d" role="button" aria-controls="elementor-tab-content-%2$d" aria-expanded="false">%4$s<a class="elementor-accordion-title" tabindex="0">%5$s</a></%1$s><div id="elementor-tab-content-%2$d" class="elementor-tab-content elementor-clearfix" data-tab="%3$d" role="region" aria-labelledby="elementor-tab-title-%2$d">%6$s</div></div>',
				tag_escape( $tag ),
				$n,
				$i + 1,
				$icon,
				esc_html( isset( $tab['tab_title'] ) ? $tab['tab_title'] : '' ),
				wp_kses_post( isset( $tab['tab_content'] ) ? $tab['tab_content'] : '' )
			);
		}
		return '<div class="elementor-accordion">' . $out . '</div>';
	}

	/**
	 * Divider.
	 */
	private function w_divider( $s, $sel ) {
		$this->add(
			'',
			$sel,
			array(
				'--divider-border-style' => ! empty( $s['style'] ) ? $s['style'] : 'solid',
				'--divider-border-width' => isset( $s['weight'] ) ? self::size( $s['weight'] ) : null,
				'--divider-color'        => self::color( $s, 'color' ),
			)
		);
		foreach ( self::responsive( $s, 'gap' ) as $device => $value ) {
			$v = self::size( $value );
			$this->add( $device, $sel . ' .elementor-divider', array( 'padding-block-start' => $v, 'padding-block-end' => $v ) );
		}
		foreach ( self::responsive( $s, 'width' ) as $device => $value ) {
			$this->add( $device, $sel . ' .elementor-divider-separator', array( 'width' => self::size( $value ) ) );
		}
		return '<div class="elementor-divider"><span class="elementor-divider-separator"></span></div>';
	}

	/**
	 * Spacer.
	 */
	private function w_spacer( $s, $sel ) {
		foreach ( self::responsive( $s, 'space' ) as $device => $value ) {
			$this->add( $device, $sel, array( '--spacer-size' => self::size( $value ) ) );
		}
		return '<div class="elementor-spacer"><div class="elementor-spacer-inner"></div></div>';
	}

	/**
	 * Storebox widget: rendered by its own class; its control selectors
	 * become CSS as Elementor would generate them.
	 *
	 * @param string $type Widget name.
	 * @param array  $s    Settings.
	 * @param string $sel  Wrapper selector.
	 * @return string
	 */
	private function storebox_widget( $type, $s, $sel ) {
		static $classes = null;
		if ( null === $classes ) {
			require_once STOREBOX_CORE_DIR . 'includes/elementor/class-module.php';
			require_once STOREBOX_CORE_DIR . 'includes/elementor/class-widget-base.php';
			$classes = array();
			foreach ( \Storebox_Core\Elementor\Module::WIDGETS as $file => $class ) {
				require_once STOREBOX_CORE_DIR . 'includes/elementor/widgets/class-' . $file . '.php';
				$classes[ 'storebox-' . $file ] = '\\Storebox_Core\\Elementor\\Widgets\\' . $class;
			}
		}
		if ( ! isset( $classes[ $type ] ) ) {
			return '';
		}
		$class  = $classes[ $type ];
		$widget = new $class( $s );
		$values = $widget->get_settings_for_display();
		foreach ( $widget->controls as $control_id => $control ) {
			if ( empty( $control['selectors'] ) || ! self::condition_met( $control, $values ) ) {
				continue;
			}
			$device = '';
			if ( ! empty( $control['responsive_of'] ) ) {
				$device = substr( $control_id, strrpos( $control_id, '_' ) + 1 );
			}
			$value = isset( $values[ $control_id ] ) ? $values[ $control_id ] : null;
			if ( 'color' === $control['type'] ) {
				$value = self::color( $values, $control_id );
			}
			if ( null === $value || '' === $value || array() === $value ) {
				continue;
			}
			foreach ( $control['selectors'] as $selector => $css ) {
				$css = self::fill_placeholders( $css, $value, $control );
				if ( null === $css ) {
					continue;
				}
				$selector = str_replace( '{{WRAPPER}}', $sel, $selector );
				foreach ( explode( ';', $css ) as $decl ) {
					$parts = explode( ':', $decl, 2 );
					if ( 2 === count( $parts ) && '' !== trim( $parts[1] ) ) {
						$this->add( $device, $selector, array( trim( $parts[0] ) => trim( $parts[1] ) ) );
					}
				}
			}
		}
		return $widget->run_render();
	}

	/**
	 * Whether a control's condition holds.
	 *
	 * @param array $control Control.
	 * @param array $values  Settings.
	 * @return bool
	 */
	private static function condition_met( $control, $values ) {
		if ( empty( $control['condition'] ) ) {
			return true;
		}
		foreach ( $control['condition'] as $key => $expected ) {
			$negate = '!' === substr( $key, -1 );
			$key    = rtrim( $key, '!' );
			$actual = isset( $values[ $key ] ) ? $values[ $key ] : '';
			$match  = is_array( $expected ) ? in_array( $actual, $expected, true ) : (string) $actual === (string) $expected;
			if ( $match === $negate ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Replaces {{VALUE}}, {{SIZE}}{{UNIT}} and dimension placeholders.
	 *
	 * @param string $css     Declaration template.
	 * @param mixed  $value   Value.
	 * @param array  $control Control.
	 * @return string|null
	 */
	private static function fill_placeholders( $css, $value, $control ) {
		if ( ! empty( $control['selectors_dictionary'] ) && is_scalar( $value ) && isset( $control['selectors_dictionary'][ $value ] ) ) {
			$value = $control['selectors_dictionary'][ $value ];
		}
		if ( is_array( $value ) ) {
			if ( isset( $value['top'] ) ) {
				$unit = isset( $value['unit'] ) ? $value['unit'] : 'px';
				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
					if ( ! isset( $value[ $side ] ) || '' === $value[ $side ] ) {
						return null;
					}
				}
				return str_replace(
					array( '{{TOP}}', '{{RIGHT}}', '{{BOTTOM}}', '{{LEFT}}', '{{UNIT}}' ),
					array( $value['top'], $value['right'], $value['bottom'], $value['left'], 'custom' === $unit ? '' : $unit ),
					$css
				);
			}
			if ( ! isset( $value['size'] ) || '' === $value['size'] ) {
				return null;
			}
			$unit = isset( $value['unit'] ) ? $value['unit'] : 'px';
			return str_replace( array( '{{SIZE}}', '{{UNIT}}' ), array( $value['size'], 'custom' === $unit ? '' : $unit ), $css );
		}
		return str_replace( '{{VALUE}}', (string) $value, $css );
	}

	/* --------------------------------------------------------------- kit */

	/**
	 * Kit CSS: global colours and fonts, theme style.
	 *
	 * @param array $kit Kit settings.
	 * @return string
	 */
	public static function kit_css( $kit ) {
		$w       = '.elementor-kit-' . KIT_ID;
		$vars    = array();
		$media   = array(
			'tablet' => array(),
			'mobile' => array(),
		);
		$generic = isset( $kit['default_generic_fonts'] ) ? $kit['default_generic_fonts'] : 'Sans-serif';

		foreach ( array( 'system_colors', 'custom_colors' ) as $group ) {
			foreach ( isset( $kit[ $group ] ) ? (array) $kit[ $group ] : array() as $c ) {
				$vars[ '--e-global-color-' . $c['_id'] ] = $c['color'];
			}
		}
		foreach ( array( 'system_typography', 'custom_typography' ) as $group ) {
			foreach ( isset( $kit[ $group ] ) ? (array) $kit[ $group ] : array() as $t ) {
				foreach ( array( 'font_family', 'font_size', 'font_weight', 'text_transform', 'font_style', 'text_decoration', 'line_height', 'letter_spacing', 'word_spacing' ) as $field ) {
					foreach ( self::responsive( $t, 'typography_' . $field ) as $device => $value ) {
						$css  = 'font_family' === $field ? '"' . $value . '"' : ( is_array( $value ) ? self::size( $value ) : $value );
						$name = '--e-global-typography-' . $t['_id'] . '-' . str_replace( '_', '-', $field );
						if ( '' === $device ) {
							$vars[ $name ] = $css;
						} else {
							$media[ $device ][ $name ] = $css;
						}
					}
				}
			}
		}

		$renderer = new self( 0, $kit );
		$css      = $w . '{' . self::decls( $vars ) . '}';

		$body = array(
			'background-color' => isset( $kit['body_background_color'] ) ? $kit['body_background_color'] : null,
			'color'            => isset( $kit['body_color'] ) ? $kit['body_color'] : null,
		);
		$typo = $renderer->typography( $kit, 'body_typography' );
		$css .= $w . '{' . self::decls( array_merge( $body, isset( $typo[''] ) ? $typo[''] : array() ) ) . '}';

		if ( ! empty( $kit['link_normal_color'] ) ) {
			$css .= $w . ' a{color:' . $kit['link_normal_color'] . '}';
		}
		if ( ! empty( $kit['link_hover_color'] ) ) {
			$css .= $w . ' a:hover{color:' . $kit['link_hover_color'] . '}';
		}
		foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $h ) {
			if ( ! empty( $kit[ $h . '_color' ] ) ) {
				$css .= $w . ' ' . $h . '{color:' . $kit[ $h . '_color' ] . '}';
			}
		}

		// Theme Style buttons and form fields apply to every button and field.
		$buttons = implode( ',', array( $w . ' button', $w . ' input[type="button"]', $w . ' input[type="submit"]', $w . ' .elementor-button' ) );
		$bt      = $renderer->typography( $kit, 'button_typography' );
		$bdecls  = isset( $bt[''] ) ? $bt[''] : array();
		$bdecls['color']            = isset( $kit['button_text_color'] ) ? $kit['button_text_color'] : null;
		$bdecls['background-color'] = isset( $kit['button_background_color'] ) ? $kit['button_background_color'] : null;
		$radius                     = isset( $kit['button_border_radius'] ) ? self::dims( $kit['button_border_radius'] ) : null;
		$padding                    = isset( $kit['button_padding'] ) ? self::dims( $kit['button_padding'] ) : null;
		$bdecls['border-radius']    = $radius ? implode( ' ', $radius ) : null;
		$bdecls['padding']          = $padding ? implode( ' ', $padding ) : null;
		$css                       .= $buttons . '{' . self::decls( $bdecls ) . '}';
		$hover                      = implode( ',', array( $w . ' button:hover', $w . ' button:focus', $w . ' input[type="button"]:hover', $w . ' input[type="submit"]:hover', $w . ' .elementor-button:hover', $w . ' .elementor-button:focus' ) );
		$css                       .= $hover . '{' . self::decls(
			array(
				'color'            => isset( $kit['button_hover_text_color'] ) ? $kit['button_hover_text_color'] : null,
				'background-color' => isset( $kit['button_hover_background_color'] ) ? $kit['button_hover_background_color'] : null,
			)
		) . '}';

		$fields = implode( ',', array( $w . ' input:not([type="button"]):not([type="submit"])', $w . ' textarea', $w . ' .elementor-field-textual' ) );
		$ft     = $renderer->typography( $kit, 'form_field_typography' );
		$fdecls = isset( $ft[''] ) ? $ft[''] : array();
		$fdecls['color']            = isset( $kit['form_field_text_color'] ) ? $kit['form_field_text_color'] : null;
		$fdecls['background-color'] = isset( $kit['form_field_background_color'] ) ? $kit['form_field_background_color'] : null;
		if ( ! empty( $kit['form_field_border_border'] ) ) {
			$fdecls['border-style'] = $kit['form_field_border_border'];
			$bw                     = isset( $kit['form_field_border_width'] ) ? self::dims( $kit['form_field_border_width'] ) : null;
			$fdecls['border-width'] = $bw ? implode( ' ', $bw ) : null;
			$fdecls['border-color'] = isset( $kit['form_field_border_color'] ) ? $kit['form_field_border_color'] : null;
		}
		$fr                      = isset( $kit['form_field_border_radius'] ) ? self::dims( $kit['form_field_border_radius'] ) : null;
		$fp                      = isset( $kit['form_field_padding'] ) ? self::dims( $kit['form_field_padding'] ) : null;
		$fdecls['border-radius'] = $fr ? implode( ' ', $fr ) : null;
		$fdecls['padding']       = $fp ? implode( ' ', $fp ) : null;
		$css                    .= $fields . '{' . self::decls( $fdecls ) . '}';
		if ( ! empty( $kit['form_field_focus_border_color'] ) ) {
			$css .= implode( ',', array( $w . ' input:focus:not([type="button"]):not([type="submit"])', $w . ' textarea:focus', $w . ' .elementor-field-textual:focus' ) ) . '{border-color:' . $kit['form_field_focus_border_color'] . '}';
		}

		// Layout.
		if ( ! empty( $kit['container_width'] ) ) {
			$css .= '.e-con{--container-max-width:' . self::size( $kit['container_width'] ) . '}';
		}
		$cp = isset( $kit['container_padding'] ) ? self::dims( $kit['container_padding'] ) : null;
		if ( $cp ) {
			$css .= '.e-con{--container-default-padding-top:' . $cp[0] . ';--container-default-padding-right:' . $cp[1] . ';--container-default-padding-bottom:' . $cp[2] . ';--container-default-padding-left:' . $cp[3] . '}';
		}
		if ( ! empty( $kit['space_between_widgets'] ) ) {
			$g     = $kit['space_between_widgets'];
			$unit  = isset( $g['unit'] ) ? $g['unit'] : 'px';
			$css  .= '.elementor-element{--widgets-spacing:' . $g['row'] . $unit . ' ' . $g['column'] . $unit . ';--widgets-spacing-row:' . $g['row'] . $unit . ';--widgets-spacing-column:' . $g['column'] . $unit . '}';
		}

		foreach ( MEDIA as $device => $query ) {
			if ( $media[ $device ] ) {
				$css .= '@media ' . $query . '{' . $w . '{' . self::decls( $media[ $device ] ) . '}}';
			}
		}
		return $css;
	}

	/**
	 * @param array $decls Declarations.
	 * @return string
	 */
	private static function decls( $decls ) {
		$out = '';
		foreach ( $decls as $prop => $value ) {
			if ( null !== $value && '' !== $value ) {
				$out .= $prop . ':' . $value . ';';
			}
		}
		return $out;
	}
}

/**
 * Dynamic tags used by the demos.
 */
final class Tags {

	/**
	 * Value of a tag.
	 *
	 * @param string $name     Tag name.
	 * @param array  $settings Tag settings.
	 * @return mixed
	 */
	public static function value( $name, $settings ) {
		$value = null;
		switch ( $name ) {
			case 'site-title':
				$value = get_bloginfo( 'name' );
				break;
			case 'post-title':
				$value = get_the_title( get_queried_object_id() );
				break;
			case 'post-excerpt':
				$value = get_the_excerpt( get_queried_object_id() );
				break;
			case 'current-date-time':
				$value = wp_date( ! empty( $settings['custom_format'] ) ? $settings['custom_format'] : get_option( 'date_format' ) );
				break;
			case 'post-featured-image':
				$id    = get_post_thumbnail_id( get_queried_object_id() );
				$value = $id ? array( 'id' => $id, 'url' => wp_get_attachment_image_url( $id, 'full' ) ) : null;
				break;
			default:
				if ( 0 === strpos( $name, 'storebox-' ) ) {
					$value = self::storebox( $name, $settings );
				}
		}
		if ( is_string( $value ) ) {
			if ( '' === $value && ! empty( $settings['fallback'] ) ) {
				$value = $settings['fallback'];
			} elseif ( '' !== $value ) {
				$value = ( isset( $settings['before'] ) ? $settings['before'] : '' ) . $value . ( isset( $settings['after'] ) ? $settings['after'] : '' );
			}
		}
		return $value;
	}

	/**
	 * Storebox tags, through their own classes.
	 *
	 * @param string $name     Tag name.
	 * @param array  $settings Settings.
	 * @return mixed
	 */
	private static function storebox( $name, $settings ) {
		require_once STOREBOX_CORE_DIR . 'includes/elementor/class-tags.php';
		foreach ( array( 'Unit_Field', 'Location_Field', 'Location_Link', 'Location_Image', 'Phone', 'Read_Time' ) as $class ) {
			$fqcn = '\\Storebox_Core\\Elementor\\Tags\\' . $class;
			$tag  = new $fqcn( $settings );
			if ( $tag->get_name() !== $name ) {
				continue;
			}
			if ( method_exists( $tag, 'get_value' ) ) {
				return $tag->get_value();
			}
			ob_start();
			$tag->render();
			return trim( ob_get_clean() );
		}
		return null;
	}
}
