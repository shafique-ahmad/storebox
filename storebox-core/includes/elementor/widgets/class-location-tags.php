<?php
/**
 * Widget: Location Tags — access hours and the location's tags as pills.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Location Tags widget.
 */
class Location_Tags extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'location-tags';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-location-tags';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Location Tags', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-tags';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'location', 'tags', 'pills', 'features' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_tags', array( 'label' => esc_html__( 'Tags', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_post_select( 'location', esc_html__( 'Location', 'storebox-core' ), 'sb_location', esc_html__( 'Current location', 'storebox-core' ) );

		$this->add_control(
			'show_access',
			array(
				'label'   => esc_html__( 'Access hours', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'       => esc_html__( 'Number of tags', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 20,
				'default'     => 0,
				'description' => esc_html__( '0 shows all tags.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'show_free',
			array(
				'label' => esc_html__( '“Units free” tag', 'storebox-core' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_tags',
			array(
				'label' => esc_html__( 'Tags', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_gap_control( 'gap', '.sb-pills' );
		$this->add_text_style( 'pill_text', '', '.sb-pills li' );
		$this->add_box_style( 'pill', '.sb-pills li', array( 'background', 'border', 'radius', 'padding' ) );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$this->render_component(
			array(
				'style'       => isset( $s['design'] ) ? $s['design'] : '',
				'location'    => $this->resolve_post( isset( $s['location'] ) ? $s['location'] : 0, 'sb_location' ),
				'show_access' => $this->on( $s, 'show_access' ),
				'limit'       => isset( $s['limit'] ) ? absint( $s['limit'] ) : 0,
				'show_free'   => $this->on( $s, 'show_free' ),
			)
		);
	}
}
