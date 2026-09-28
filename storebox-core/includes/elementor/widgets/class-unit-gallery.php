<?php
/**
 * Widget: Unit Gallery — large photo with a thumbnail switcher.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Unit Gallery widget.
 */
class Unit_Gallery extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'unit-gallery';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-unit-gallery';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Unit Gallery', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-gallery-group';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'unit', 'gallery', 'photos', 'images' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_gallery', array( 'label' => esc_html__( 'Gallery', 'storebox-core' ) ) );

		$this->add_design_control();

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Photos', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'unit',
				'options' => array(
					'unit'   => esc_html__( 'From a unit', 'storebox-core' ),
					'custom' => esc_html__( 'Choose images', 'storebox-core' ),
				),
			)
		);

		$this->add_post_select( 'unit', esc_html__( 'Unit', 'storebox-core' ), 'sb_unit', esc_html__( 'Current unit', 'storebox-core' ) );
		$this->update_control( 'unit', array( 'condition' => array( 'source' => 'unit' ) ) );

		$this->add_control(
			'gallery',
			array(
				'label'     => esc_html__( 'Images', 'storebox-core' ),
				'type'      => Controls_Manager::GALLERY,
				'condition' => array( 'source' => 'custom' ),
			)
		);

		$this->add_control(
			'thumbs',
			array(
				'label'   => esc_html__( 'Thumbnails', 'storebox-core' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 2,
				'max'     => 8,
				'default' => 4,
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => esc_html__( 'Image size', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'storebox-wide',
				'options' => static::image_size_options(),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_gallery',
			array(
				'label' => esc_html__( 'Gallery', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'main_height',
			array(
				'label'      => esc_html__( 'Photo height', 'storebox-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 900,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .sb-gallery__main' => 'aspect-ratio: auto; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_box_style( 'main', '.sb-gallery__main', array( 'radius', 'shadow' ) );

		$this->add_heading( 'thumbs_heading', esc_html__( 'Thumbnails', 'storebox-core' ) );
		$this->add_gap_control( 'thumbs_gap', '.sb-gallery__thumbs' );
		$this->add_box_style( 'thumb', '.sb-gallery__thumbs button', array( 'border', 'radius' ) );

		$this->add_control(
			'thumb_active_color',
			array(
				'label'     => esc_html__( 'Selected outline', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sb-gallery__thumbs button[aria-current="true"]' => 'border-color: {{VALUE}}; box-shadow: 0 0 0 1px {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s   = $this->get_settings_for_display();
		$ids = array();

		if ( isset( $s['source'] ) && 'custom' === $s['source'] && ! empty( $s['gallery'] ) ) {
			$ids = array_filter( array_map( 'absint', wp_list_pluck( (array) $s['gallery'], 'id' ) ) );
		}

		$this->render_component(
			array(
				'style'      => isset( $s['design'] ) ? $s['design'] : '',
				'unit'       => $ids ? 0 : $this->resolve_post( isset( $s['unit'] ) ? $s['unit'] : 0, 'sb_unit' ),
				'ids'        => $ids,
				'thumbs'     => isset( $s['thumbs'] ) ? absint( $s['thumbs'] ) : 4,
				'image_size' => ! empty( $s['image_size'] ) ? $s['image_size'] : 'storebox-wide',
			)
		);
	}

	/**
	 * Editor hint.
	 *
	 * @return string
	 */
	protected function empty_message() {
		return __( 'This unit has no photos yet. Add a featured image or gallery photos to the unit.', 'storebox-core' );
	}
}
