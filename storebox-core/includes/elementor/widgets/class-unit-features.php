<?php
/**
 * Widget: Unit Features — included / not included checklist.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Unit Features widget.
 */
class Unit_Features extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'unit-features';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-unit-features';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Unit Features', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-checkbox';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'unit', 'features', 'checklist', 'amenities' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_features', array( 'label' => esc_html__( 'Features', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_post_select( 'unit', esc_html__( 'Unit', 'storebox-core' ), 'sb_unit', esc_html__( 'Current unit', 'storebox-core' ) );

		$this->add_control(
			'show_missing',
			array(
				'label'       => esc_html__( 'Show features the unit does not have', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Features are managed under Storebox → Features.', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_features',
			array(
				'label' => esc_html__( 'Features', 'storebox-core' ),
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
					''  => esc_html__( 'Default', 'storebox-core' ),
					'1' => '1',
					'2' => '2',
					'3' => '3',
				),
				'selectors' => array(
					'{{WRAPPER}} .sb-checks' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_text_style( 'name_text', esc_html__( 'Feature', 'storebox-core' ), '.sb-checks__name' );

		$this->add_control(
			'yes_color',
			array(
				'label'     => esc_html__( 'Included icon background', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sb-checks li.is-yes::before' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'state_color',
			array(
				'label'     => esc_html__( '“Yes” text color (editorial)', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sb-checks li.is-yes .sb-checks__state' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'no_color',
			array(
				'label'     => esc_html__( 'Not included color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .sb-checks li.is-no, {{WRAPPER}} .sb-checks li.is-no .sb-checks__state' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$this->render_component(
			array(
				'style'        => isset( $s['design'] ) ? $s['design'] : '',
				'unit'         => $this->resolve_post( isset( $s['unit'] ) ? $s['unit'] : 0, 'sb_unit' ),
				'show_missing' => $this->on( $s, 'show_missing' ),
			)
		);
	}
}
