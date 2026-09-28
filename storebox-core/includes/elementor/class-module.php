<?php
/**
 * Elementor integration: widget category, widgets and dynamic tags.
 *
 * Loaded on "elementor/init", so nothing here runs without Elementor.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor;

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Storebox widgets with Elementor.
 */
class Module {

	/**
	 * Widget classes, keyed by file name.
	 *
	 * @var array<string, string>
	 */
	const WIDGETS = array(
		'unit-grid'       => 'Unit_Grid',
		'unit-gallery'    => 'Unit_Gallery',
		'unit-specs'      => 'Unit_Specs',
		'unit-features'   => 'Unit_Features',
		'unit-booking'    => 'Unit_Booking',
		'location-grid'   => 'Location_Grid',
		'location-facts'  => 'Location_Facts',
		'location-tags'   => 'Location_Tags',
		'opening-hours'   => 'Opening_Hours',
		'location-map'    => 'Location_Map',
		'size-calculator' => 'Size_Calculator',
		'size-chooser'    => 'Size_Chooser',
		'size-guide'      => 'Size_Guide',
		'post-grid'       => 'Post_Grid',
		'enquiry-form'    => 'Enquiry_Form',
	);

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/dynamic_tags/register', array( __CLASS__, 'register_tags' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'preview_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'editor_styles' ) );
	}

	/**
	 * Adds the "Storebox" widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
	 */
	public static function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'storebox',
			array(
				'title' => __( 'Storebox', 'storebox-core' ),
				'icon'  => 'eicon-folder',
			)
		);
	}

	/**
	 * Registers the widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 */
	public static function register_widgets( $widgets_manager ) {
		require_once STOREBOX_CORE_DIR . 'includes/elementor/class-widget-base.php';

		foreach ( self::WIDGETS as $file => $class ) {
			require_once STOREBOX_CORE_DIR . 'includes/elementor/widgets/class-' . $file . '.php';
			$class = __NAMESPACE__ . '\\Widgets\\' . $class;
			$widgets_manager->register( new $class() );
		}
	}

	/**
	 * Registers the dynamic tags.
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $dynamic_tags Dynamic tags manager.
	 */
	public static function register_tags( $dynamic_tags ) {
		require_once STOREBOX_CORE_DIR . 'includes/elementor/class-tags.php';

		$dynamic_tags->register_group(
			'storebox',
			array(
				'title' => __( 'Storebox', 'storebox-core' ),
			)
		);

		foreach ( array( 'Unit_Field', 'Location_Field', 'Location_Link', 'Location_Image', 'Phone', 'Read_Time' ) as $class ) {
			$class = __NAMESPACE__ . '\\Tags\\' . $class;
			$dynamic_tags->register( new $class() );
		}
	}

	/**
	 * Loads every component's assets in the editor preview, so widgets look
	 * right the moment they are dropped in.
	 */
	public static function preview_assets() {
		Components::enqueue( array_keys( Components::registry() ) );
	}

	/**
	 * Editor panel: accent for the Storebox widget icons.
	 */
	public static function editor_styles() {
		wp_add_inline_style(
			'elementor-editor',
			'#elementor-panel-category-storebox .elementor-element .icon{color:#2c5871}#elementor-panel-category-storebox .elementor-element:hover .icon{color:#16303f}'
		);
	}
}
