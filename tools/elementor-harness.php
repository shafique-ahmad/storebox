<?php
/**
 * Development harness: validates and renders the Storebox Elementor widgets
 * without Elementor, using minimal stand-ins for the Elementor classes.
 *
 * Run inside WordPress:  wp eval-file tools/elementor-harness.php [render]
 *
 * Checks, per widget: sections are opened and closed, controls live inside a
 * section, control IDs are unique, SELECT defaults exist in their options,
 * conditions point at existing controls, selectors use {{WRAPPER}} and known
 * placeholders, repeater title fields exist. With "render" it also renders
 * every widget with its default settings and reports PHP notices.
 *
 * Not shipped with the theme or plugin.
 *
 * @package Storebox
 */

namespace Elementor {

	class Controls_Manager {
		const TAB_CONTENT = 'content';
		const TAB_STYLE   = 'style';
		const TEXT        = 'text';
		const TEXTAREA    = 'textarea';
		const NUMBER      = 'number';
		const SELECT      = 'select';
		const SELECT2     = 'select2';
		const SWITCHER    = 'switcher';
		const SLIDER      = 'slider';
		const DIMENSIONS  = 'dimensions';
		const COLOR       = 'color';
		const URL         = 'url';
		const GALLERY     = 'gallery';
		const REPEATER    = 'repeater';
		const HEADING     = 'heading';
		const CHOOSE      = 'choose';
		const HIDDEN      = 'hidden';
		const MEDIA       = 'media';
	}

	class Group_Control_Typography {
		public static function get_type() {
			return 'typography';
		}
	}

	class Group_Control_Border {
		public static function get_type() {
			return 'border';
		}
	}

	class Group_Control_Box_Shadow {
		public static function get_type() {
			return 'box-shadow';
		}
	}

	/**
	 * Records controls like Elementor's controls stack.
	 */
	class Stack {
		public $controls = array();
		public $errors   = array();
		public $section  = null;
		public $tabs     = 0;
		public $tab      = null;

		public function add_control( $id, array $args, $options = array() ) {
			if ( null === $this->section && ! ( $this instanceof Repeater ) ) {
				$this->errors[] = "control '$id' outside a section";
			}
			if ( isset( $this->controls[ $id ] ) ) {
				$this->errors[] = "duplicate control '$id'";
			}
			if ( empty( $args['type'] ) ) {
				$this->errors[] = "control '$id' has no type";
			}
			$args['section']       = $this->section;
			$this->controls[ $id ] = $args;
			return true;
		}

		public function add_responsive_control( $id, array $args, $options = array() ) {
			$this->add_control( $id, $args );
			foreach ( array( 'tablet', 'mobile' ) as $device ) {
				$device_args = $args;
				if ( isset( $args[ $device . '_default' ] ) ) {
					$device_args['default'] = $args[ $device . '_default' ];
				} else {
					unset( $device_args['default'] );
				}
				$device_args['responsive_of'] = $id;
				$this->controls[ $id . '_' . $device ] = $device_args;
			}
		}

		public function add_group_control( $type, array $args = array(), array $options = array() ) {
			if ( empty( $args['name'] ) ) {
				$this->errors[] = "group control '$type' without a name";
			}
			if ( empty( $args['selector'] ) || false === strpos( $args['selector'], '{{WRAPPER}}' ) ) {
				$this->errors[] = "group control '{$args['name']}' selector must use {{WRAPPER}}";
			}
			if ( null === $this->section ) {
				$this->errors[] = "group control '{$args['name']}' outside a section";
			}
			$this->controls[ $args['name'] . '__group' ] = array(
				'type'    => 'group:' . $type,
				'section' => $this->section,
			);
		}

		public function update_control( $id, array $args, array $options = array() ) {
			if ( ! isset( $this->controls[ $id ] ) ) {
				$this->errors[] = "update_control: unknown control '$id'";
				return false;
			}
			$this->controls[ $id ] = array_merge( $this->controls[ $id ], $args );
			return true;
		}

		public function start_controls_section( $id, array $args = array() ) {
			if ( null !== $this->section ) {
				$this->errors[] = "section '$id' opened inside '{$this->section}'";
			}
			if ( empty( $args['label'] ) ) {
				$this->errors[] = "section '$id' has no label";
			}
			$this->section = $id;
		}

		public function end_controls_section() {
			if ( null === $this->section ) {
				$this->errors[] = 'end_controls_section without a section';
			}
			if ( $this->tabs ) {
				$this->errors[] = "section '{$this->section}' closed with open tabs";
			}
			$this->section = null;
		}

		public function start_controls_tabs( $id, array $args = array() ) {
			++$this->tabs;
		}

		public function end_controls_tabs() {
			--$this->tabs;
			if ( null !== $this->tab ) {
				$this->errors[] = 'tabs closed with an open tab';
			}
		}

		public function start_controls_tab( $id, array $args ) {
			if ( ! $this->tabs ) {
				$this->errors[] = "tab '$id' outside tabs";
			}
			$this->tab = $id;
		}

		public function end_controls_tab() {
			$this->tab = null;
		}

		public function get_controls() {
			return $this->controls;
		}
	}

	class Repeater extends Stack {
	}

	abstract class Widget_Base extends Stack {
		private $settings = array();

		public function __construct( $settings = array() ) {
			$this->register_controls();
			if ( null !== $this->section ) {
				$this->errors[] = "section '{$this->section}' never closed";
			}
			$this->settings = $settings;
		}

		public function get_settings_for_display( $key = null ) {
			$settings = array();
			foreach ( $this->controls as $id => $control ) {
				if ( isset( $control['default'] ) ) {
					$settings[ $id ] = $control['default'];
				}
			}
			$settings = array_merge( $settings, $this->settings );
			return null === $key ? $settings : ( isset( $settings[ $key ] ) ? $settings[ $key ] : null );
		}

		public function get_settings( $key = null ) {
			return $this->get_settings_for_display( $key );
		}

		public function run_render() {
			ob_start();
			$this->render();
			return ob_get_clean();
		}

		protected function register_controls() {}

		protected function render() {}
	}

	class Editor_Stub {
		public $edit = false;

		public function is_edit_mode() {
			return $this->edit;
		}
	}

	class Preview_Stub {
		public function is_preview_mode() {
			return false;
		}
	}

	class Plugin {
		public static $instance;
		public $editor;
		public $preview;

		public function __construct() {
			$this->editor  = new Editor_Stub();
			$this->preview = new Preview_Stub();
		}
	}
}

namespace Elementor\Core\DynamicTags {

	abstract class Tag extends \Elementor\Stack {
		private $settings = array();

		public function __construct( $settings = array() ) {
			$this->section = 'tag';
			$this->register_controls();
			$this->settings = $settings;
		}

		public function get_settings( $key = null ) {
			$settings = array();
			foreach ( $this->controls as $id => $control ) {
				if ( isset( $control['default'] ) ) {
					$settings[ $id ] = $control['default'];
				}
			}
			$settings = array_merge( $settings, $this->settings );
			return null === $key ? $settings : ( isset( $settings[ $key ] ) ? $settings[ $key ] : null );
		}

		protected function register_controls() {}
	}

	abstract class Data_Tag extends Tag {
	}
}

namespace Elementor\Modules\DynamicTags {

	class Module {
		const TEXT_CATEGORY   = 'text';
		const NUMBER_CATEGORY = 'number';
		const URL_CATEGORY    = 'url';
	}
}

namespace {

	\Elementor\Plugin::$instance = new \Elementor\Plugin();

	$storebox_render = in_array( 'render', isset( $args ) ? (array) $args : array(), true );

	// Admin context so option lists are built.
	if ( ! defined( 'WP_ADMIN' ) ) {
		set_current_screen( 'dashboard' );
	}

	require_once STOREBOX_CORE_DIR . 'includes/elementor/class-module.php';
	require_once STOREBOX_CORE_DIR . 'includes/elementor/class-widget-base.php';

	$storebox_placeholders = array( '{{WRAPPER}}', '{{VALUE}}', '{{SIZE}}', '{{UNIT}}', '{{TOP}}', '{{RIGHT}}', '{{BOTTOM}}', '{{LEFT}}' );
	$storebox_failed       = 0;
	$storebox_names        = array();

	set_error_handler(
		static function ( $errno, $errstr, $errfile, $errline ) {
			if ( false !== strpos( $errfile, 'storebox' ) ) {
				echo "  PHP notice: $errstr in " . basename( $errfile ) . ":$errline\n";
				$GLOBALS['storebox_failed']++;
			}
			return false !== strpos( $errfile, 'wp-includes' );
		}
	);

	foreach ( \Storebox_Core\Elementor\Module::WIDGETS as $file => $class ) {
		require_once STOREBOX_CORE_DIR . 'includes/elementor/widgets/class-' . $file . '.php';
		$fqcn   = '\\Storebox_Core\\Elementor\\Widgets\\' . $class;
		$widget = new $fqcn();
		$errors = $widget->errors;
		$ids    = array_keys( $widget->controls );

		$name = $widget->get_name();
		if ( isset( $storebox_names[ $name ] ) ) {
			$errors[] = "duplicate widget name $name";
		}
		$storebox_names[ $name ] = true;

		foreach ( array( 'get_title', 'get_icon' ) as $method ) {
			if ( '' === (string) $widget->$method() ) {
				$errors[] = "$method is empty";
			}
		}

		foreach ( $widget->controls as $id => $control ) {
			if ( in_array( $control['type'], array( 'select' ), true ) && isset( $control['default'] ) && empty( $control['responsive_of'] ) ) {
				$keys = array_map( 'strval', array_keys( (array) $control['options'] ) );
				if ( $keys && ! in_array( (string) $control['default'], $keys, true ) ) {
					$errors[] = "select '$id' default '{$control['default']}' not in options";
				}
			}
			foreach ( array( 'condition', 'conditions' ) as $key ) {
				if ( empty( $control[ $key ] ) ) {
					continue;
				}
				$terms = 'condition' === $key ? array_keys( $control[ $key ] ) : wp_list_pluck( $control[ $key ]['terms'], 'name' );
				foreach ( $terms as $term ) {
					$term = rtrim( $term, '!' );
					if ( ! in_array( $term, $ids, true ) ) {
						$errors[] = "control '$id' $key refers to unknown '$term'";
					}
				}
			}
			if ( ! empty( $control['selectors'] ) ) {
				foreach ( $control['selectors'] as $selector => $css ) {
					if ( false === strpos( $selector, '{{WRAPPER}}' ) ) {
						$errors[] = "control '$id' selector without {{WRAPPER}}: $selector";
					}
					foreach ( explode( ',', $selector ) as $part ) {
						if ( false === strpos( $part, '{{WRAPPER}}' ) ) {
							$errors[] = "control '$id' selector part without {{WRAPPER}}: $part";
						}
					}
					preg_match_all( '/\{\{[A-Z_]+\}\}/', $css . $selector, $found );
					foreach ( $found[0] as $placeholder ) {
						if ( ! in_array( $placeholder, $storebox_placeholders, true ) ) {
							$errors[] = "control '$id' unknown placeholder $placeholder";
						}
					}
				}
			}
			if ( 'repeater' === $control['type'] ) {
				if ( ! empty( $control['title_field'] ) && preg_match( '/\{\{\{\s*(\w+)\s*\}\}\}/', $control['title_field'], $match ) && ! isset( $control['fields'][ $match[1] ] ) ) {
					$errors[] = "repeater '$id' title field '{$match[1]}' is not a field";
				}
			}
		}

		$status = $errors ? 'FAIL' : 'ok';
		printf( "%-26s %-4s %3d controls\n", $name, $status, count( $widget->controls ) );
		foreach ( $errors as $error ) {
			echo "  - $error\n";
			++$storebox_failed;
		}

		if ( $storebox_render ) {
			$html = $widget->run_render();
			printf( "  render: %d bytes%s\n", strlen( $html ), '' === trim( $html ) ? ' (empty)' : '' );
		}
	}

	require_once STOREBOX_CORE_DIR . 'includes/elementor/class-tags.php';
	foreach ( array( 'Unit_Field', 'Location_Field', 'Location_Link', 'Phone', 'Read_Time' ) as $class ) {
		$fqcn = '\\Storebox_Core\\Elementor\\Tags\\' . $class;
		$tag  = new $fqcn();
		printf( "%-26s %-4s\n", $tag->get_name(), $tag->errors ? 'FAIL' : 'ok' );
		if ( $storebox_render ) {
			if ( method_exists( $tag, 'get_value' ) ) {
				echo '  value: ' . $tag->get_value() . "\n";
			} else {
				ob_start();
				$tag->render();
				echo '  render: ' . ob_get_clean() . "\n";
			}
		}
	}

	restore_error_handler();
	echo $storebox_failed ? "\n$storebox_failed problem(s)\n" : "\nAll widgets valid.\n";
}
