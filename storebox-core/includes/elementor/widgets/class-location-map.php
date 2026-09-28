<?php
/**
 * Widget: Location Map — illustrative map, or a live map with the same pins.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Location Map widget.
 */
class Location_Map extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'location-map';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-location-map';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Location Map', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-google-maps';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'map', 'locations', 'pins', 'openstreetmap', 'leaflet' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_map', array( 'label' => esc_html__( 'Map', 'storebox-core' ) ) );

		$this->add_design_control();

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Locations', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'     => esc_html__( 'All locations', 'storebox-core' ),
					'current' => esc_html__( 'Current location', 'storebox-core' ),
					'manual'  => esc_html__( 'Selected locations', 'storebox-core' ),
				),
			)
		);

		$this->add_control(
			'ids',
			array(
				'label'       => esc_html__( 'Locations', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => static::post_options( 'sb_location' ),
				'condition'   => array( 'source' => 'manual' ),
			)
		);

		$this->add_control(
			'mode',
			array(
				'label'       => esc_html__( 'Map type', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''             => esc_html__( 'Default (Storebox → Settings)', 'storebox-core' ),
					'live'         => esc_html__( 'Live map', 'storebox-core' ),
					'illustrative' => esc_html__( 'Illustrative map (no external requests)', 'storebox-core' ),
				),
				'description' => esc_html__( 'Pins come from each location’s latitude and longitude.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'consent',
			array(
				'label'     => esc_html__( 'Ask before loading the live map', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array(
					''    => esc_html__( 'Default (Storebox → Settings)', 'storebox-core' ),
					'yes' => esc_html__( 'Yes', 'storebox-core' ),
					'no'  => esc_html__( 'No', 'storebox-core' ),
				),
				'condition' => array( 'mode!' => 'illustrative' ),
			)
		);

		$this->add_control(
			'zoom',
			array(
				'label'       => esc_html__( 'Zoom', 'storebox-core' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 3,
						'max' => 18,
					),
				),
				'default'     => array( 'size' => 12 ),
				'description' => esc_html__( 'With several locations the map fits them all, zooming in no further than this.', 'storebox-core' ),
				'condition'   => array( 'mode!' => 'illustrative' ),
			)
		);

		$this->add_control(
			'ratio',
			array(
				'label'   => esc_html__( 'Shape', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '16 / 7',
				'options' => array(
					'21 / 9' => '21:9',
					'16 / 7' => '16:7',
					'16 / 9' => '16:9',
					'4 / 3'  => '4:3',
					'1 / 1'  => '1:1',
				),
			)
		);

		$this->add_control(
			'ratio_mobile',
			array(
				'label'   => esc_html__( 'Shape on phones and tablets', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '4 / 3',
				'options' => array(
					'16 / 9' => '16:9',
					'4 / 3'  => '4:3',
					'1 / 1'  => '1:1',
					'3 / 4'  => '3:4',
				),
			)
		);

		$this->add_control(
			'label',
			array(
				'label'       => esc_html__( 'Accessible name', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Map of our locations', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_map',
			array(
				'label' => esc_html__( 'Map', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'canvas', '.sb-map__canvas', array( 'border', 'radius', 'shadow' ) );

		$this->add_control(
			'canvas_background',
			array(
				'label'     => esc_html__( 'Illustration background', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-map__canvas' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_heading( 'pin_heading', esc_html__( 'Pins', 'storebox-core' ) );
		$this->add_control(
			'pin_background',
			array(
				'label'     => esc_html__( 'Background color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-map__pin span, {{WRAPPER}} .sb-map__pin i' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pin_color',
			array(
				'label'     => esc_html__( 'Text color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-map__pin span' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pin_hover_background',
			array(
				'label'     => esc_html__( 'Hover background', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-map__pin:hover span, {{WRAPPER}} .sb-map__pin:hover i, {{WRAPPER}} .sb-map__pin:focus-visible span, {{WRAPPER}} .sb-map__pin:focus-visible i' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$source = isset( $s['source'] ) ? $s['source'] : 'all';
		$ids    = array();

		if ( 'manual' === $source ) {
			$ids = isset( $s['ids'] ) ? array_map( 'absint', (array) $s['ids'] ) : array();
		} elseif ( 'current' === $source ) {
			$current = $this->resolve_post( 0, 'sb_location' );
			$ids     = $current ? array( $current ) : array();
		}

		if ( 'all' !== $source && ! $ids ) {
			if ( $this->is_editor() ) {
				echo '<div class="sb-e-empty">' . esc_html( $this->empty_message() ) . '</div>';
			}
			return;
		}

		$consent = null;
		if ( isset( $s['consent'] ) && '' !== $s['consent'] ) {
			$consent = 'yes' === $s['consent'];
		}

		$this->render_component(
			array(
				'style'        => isset( $s['design'] ) ? $s['design'] : '',
				'locations'    => $ids,
				'mode'         => isset( $s['mode'] ) ? $s['mode'] : '',
				'zoom'         => isset( $s['zoom']['size'] ) ? absint( $s['zoom']['size'] ) : 12,
				'ratio'        => isset( $s['ratio'] ) ? $s['ratio'] : '16 / 7',
				'ratio_mobile' => isset( $s['ratio_mobile'] ) ? $s['ratio_mobile'] : '4 / 3',
				'label'        => isset( $s['label'] ) ? $s['label'] : '',
				'consent'      => $consent,
			)
		);
	}

	/**
	 * Editor hint.
	 *
	 * @return string
	 */
	protected function empty_message() {
		return __( 'No map yet: add a latitude and longitude to your locations under Storebox → Locations.', 'storebox-core' );
	}
}
