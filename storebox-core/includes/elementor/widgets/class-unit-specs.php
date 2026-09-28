<?php
/**
 * Widget: Unit Specs — floor area, dimensions, ceiling height, floor.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Unit Specs widget.
 */
class Unit_Specs extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'unit-specs';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-unit-specs';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Unit Specs', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-info-box';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'unit', 'specs', 'dimensions', 'size', 'details' );
	}

	/**
	 * Specification choices.
	 *
	 * @return array<string, string>
	 */
	private static function items() {
		return array(
			'area'       => esc_html__( 'Floor area', 'storebox-core' ),
			'area_ft'    => esc_html__( 'Square feet', 'storebox-core' ),
			'dimensions' => esc_html__( 'Dimensions', 'storebox-core' ),
			'ceiling'    => esc_html__( 'Ceiling', 'storebox-core' ),
			'floor'      => esc_html__( 'Floor', 'storebox-core' ),
			'price'      => esc_html__( 'Price', 'storebox-core' ),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_specs', array( 'label' => esc_html__( 'Specs', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_post_select( 'unit', esc_html__( 'Unit', 'storebox-core' ), 'sb_unit', esc_html__( 'Current unit', 'storebox-core' ) );

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'Show', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::items(),
				'default'     => array( 'area', 'dimensions', 'ceiling', 'floor' ),
				'description' => esc_html__( 'Shown in the order selected. Empty values are skipped.', 'storebox-core' ),
			)
		);

		$this->add_heading( 'labels_heading', esc_html__( 'Labels', 'storebox-core' ) );

		foreach ( self::items() as $key => $label ) {
			$this->add_control(
				'label_' . $key,
				array(
					'label'       => $label,
					'type'        => Controls_Manager::TEXT,
					'placeholder' => $label,
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_specs',
			array(
				'label' => esc_html__( 'Specs', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'     => esc_html__( 'Columns', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''  => esc_html__( 'One per item', 'storebox-core' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors' => array(
					'{{WRAPPER}} .sb-specs' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_gap_control( 'gap', '.sb-specs' );
		$this->add_box_style( 'item', '.sb-specs__item' );
		$this->add_text_style( 'label_text', esc_html__( 'Label', 'storebox-core' ), '.sb-specs dt' );
		$this->add_text_style( 'value_text', esc_html__( 'Value', 'storebox-core' ), '.sb-specs dd' );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$labels = array();

		foreach ( array_keys( self::items() ) as $key ) {
			if ( ! empty( $s[ 'label_' . $key ] ) ) {
				$labels[ $key ] = $s[ 'label_' . $key ];
			}
		}

		$this->render_component(
			array(
				'style'  => isset( $s['design'] ) ? $s['design'] : '',
				'unit'   => $this->resolve_post( isset( $s['unit'] ) ? $s['unit'] : 0, 'sb_unit' ),
				'items'  => ! empty( $s['items'] ) ? (array) $s['items'] : array( 'area', 'dimensions', 'ceiling', 'floor' ),
				'labels' => $labels,
			)
		);
	}
}
