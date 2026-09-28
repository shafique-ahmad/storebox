<?php
/**
 * Minimal stand-ins for the Elementor classes the Storebox widgets use.
 * Shared by the widget harness and the preview renderer (tools only).
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

	class Documents_Stub {
		public function get( $post_id ) {
			return new Document_Stub( $post_id );
		}
	}

	class Document_Stub {
		private $post_id;

		public function __construct( $post_id ) {
			$this->post_id = (int) $post_id;
		}

		public function is_built_with_elementor() {
			return 'builder' === get_post_meta( $this->post_id, '_elementor_edit_mode', true );
		}
	}

	class Plugin {
		public static $instance;
		public $editor;
		public $preview;
		public $documents;

		public function __construct() {
			$this->editor    = new Editor_Stub();
			$this->preview   = new Preview_Stub();
			$this->documents = new Documents_Stub();
		}

		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
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
		const IMAGE_CATEGORY  = 'image';
	}
}
