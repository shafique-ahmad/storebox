<?php
/**
 * Widget: Opening Hours — office hours table with today highlighted.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Opening Hours widget.
 */
class Opening_Hours extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'opening-hours';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-opening-hours';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Opening Hours', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-clock-o';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'hours', 'opening', 'office', 'schedule', 'location' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_hours', array( 'label' => esc_html__( 'Opening hours', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_post_select( 'location', esc_html__( 'Location', 'storebox-core' ), 'sb_location', esc_html__( 'Current location', 'storebox-core' ) );

		$this->add_control(
			'show_title',
			array(
				'label'   => esc_html__( 'Title', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Office hours', 'storebox-core' ),
				'condition'   => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'show_note',
			array(
				'label'   => esc_html__( 'Note', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => esc_html__( 'Note text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'placeholder' => esc_html__( 'Access to your unit is 24/7 regardless.', 'storebox-core' ),
				'description' => esc_html__( 'Empty mentions the location’s access hours.', 'storebox-core' ),
				'condition'   => array( 'show_note' => 'yes' ),
			)
		);

		$this->add_control(
			'highlight_today',
			array(
				'label'   => esc_html__( 'Highlight today', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_hours',
			array(
				'label' => esc_html__( 'Table', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'box', '.sb-hours' );
		$this->add_text_style( 'title_text', esc_html__( 'Title', 'storebox-core' ), '.sb-hours__title' );
		$this->add_text_style( 'note_text', esc_html__( 'Note', 'storebox-core' ), '.sb-hours__note' );
		$this->add_text_style( 'day_text', esc_html__( 'Days', 'storebox-core' ), '.sb-hours__table th' );
		$this->add_text_style( 'time_text', esc_html__( 'Hours', 'storebox-core' ), '.sb-hours__table td' );

		$this->add_heading( 'today_heading', esc_html__( 'Today', 'storebox-core' ) );
		$this->add_control(
			'today_color',
			array(
				'label'     => esc_html__( 'Text color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-hours__table tr.is-today th, {{WRAPPER}} .sb-hours__table tr.is-today td' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'today_background',
			array(
				'label'     => esc_html__( 'Background color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-hours__table tr.is-today th, {{WRAPPER}} .sb-hours__table tr.is-today td' => 'background-color: {{VALUE}};' ),
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
				'style'           => isset( $s['design'] ) ? $s['design'] : '',
				'location'        => $this->resolve_post( isset( $s['location'] ) ? $s['location'] : 0, 'sb_location' ),
				'title'           => $this->on( $s, 'show_title' ) ? $this->text_or_default( $s, 'title' ) : '',
				'note'            => $this->on( $s, 'show_note' ) ? $this->text_or_default( $s, 'note' ) : '',
				'highlight_today' => $this->on( $s, 'highlight_today' ),
			)
		);
	}

	/**
	 * Editor hint.
	 *
	 * @return string
	 */
	protected function empty_message() {
		return __( 'No office hours yet. Add them to the location under Storebox → Locations.', 'storebox-core' );
	}
}
