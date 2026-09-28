<?php
/**
 * Widget: Location Grid — facility cards, photo tiles or a list.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Location Grid widget.
 */
class Location_Grid extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'location-grid';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-location-grid';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Location Grid', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-map-pin';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'locations', 'facilities', 'branches', 'grid', 'list' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_locations', array( 'label' => esc_html__( 'Locations', 'storebox-core' ) ) );

		$this->add_design_control();

		$this->add_control(
			'skin',
			array(
				'label'   => esc_html__( 'Layout', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'    => esc_html__( 'Automatic (by design)', 'storebox-core' ),
					'card'    => esc_html__( 'Cards', 'storebox-core' ),
					'overlay' => esc_html__( 'Photo tiles', 'storebox-core' ),
					'row'     => esc_html__( 'List', 'storebox-core' ),
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
				'description' => esc_html__( 'Leave empty to show all, in their menu order.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'       => esc_html__( 'Number of locations', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 50,
				'default'     => 0,
				'description' => esc_html__( '0 shows all.', 'storebox-core' ),
			)
		);

		$this->add_columns_control( 'columns', '.sb-locs:not(.sb-locs--row)', 3, 4 );

		$this->add_control(
			'heading_tag',
			array(
				'label'   => esc_html__( 'Name tag', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => static::tag_options(),
			)
		);

		$this->add_control(
			'address',
			array(
				'label'   => esc_html__( 'Address', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'short',
				'options' => array(
					'short' => esc_html__( 'Street and area', 'storebox-core' ),
					'full'  => esc_html__( 'Full address', 'storebox-core' ),
				),
			)
		);

		$this->add_control(
			'show_access',
			array(
				'label'   => esc_html__( 'Access hours chip', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'tag_limit',
			array(
				'label'       => esc_html__( 'Number of tags', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 10,
				'default'     => 1,
				'description' => esc_html__( '0 shows all tags.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'show_free',
			array(
				'label'   => esc_html__( '“Units free” chip', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_excerpt',
			array(
				'label'     => esc_html__( 'Excerpt', 'storebox-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'skin' => array( 'auto', 'card' ) ),
			)
		);

		$this->add_control(
			'show_meta',
			array(
				'label'     => esc_html__( 'Address, access and units lines', 'storebox-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'skin' => array( 'auto', 'card' ) ),
			)
		);

		$this->add_control(
			'link_text',
			array(
				'label'       => esc_html__( 'Link text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'View facility', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_items',
			array(
				'label' => esc_html__( 'Items', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_gap_control( 'gap', '.sb-locs' );
		$this->add_box_style( 'item', '.sb-loc', array( 'background', 'border', 'radius', 'shadow' ) );
		$this->add_box_style( 'item_body', '.sb-loc__body, {{WRAPPER}} .sb-loc__in', array( 'padding' ) );
		$this->add_text_style( 'city_text', esc_html__( 'Area', 'storebox-core' ), '.sb-loc__city' );
		$this->add_text_style( 'name_text', esc_html__( 'Name', 'storebox-core' ), '.sb-loc__name' );
		$this->add_text_style( 'address_text', esc_html__( 'Address and details', 'storebox-core' ), '.sb-loc__address, {{WRAPPER}} .sb-loc__meta, {{WRAPPER}} .sb-loc__excerpt' );
		$this->add_text_style( 'link_style', esc_html__( 'Link', 'storebox-core' ), '.sb-loc__more, {{WRAPPER}} .sb-loc__go' );

		$this->add_heading( 'chips_heading', esc_html__( 'Chips', 'storebox-core' ) );
		$this->add_control(
			'chip_color',
			array(
				'label'     => esc_html__( 'Text color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-loc__chips span, {{WRAPPER}} .sb-loc__tags span' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'chip_background',
			array(
				'label'     => esc_html__( 'Background color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-loc__chips span, {{WRAPPER}} .sb-loc__tags span' => 'background-color: {{VALUE}};' ),
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
				'skin'         => isset( $s['skin'] ) ? $s['skin'] : 'auto',
				'ids'          => isset( $s['ids'] ) ? (array) $s['ids'] : array(),
				'count'        => isset( $s['count'] ) ? absint( $s['count'] ) : 0,
				'columns'      => ! empty( $s['columns'] ) ? absint( $s['columns'] ) : 3,
				'address'      => isset( $s['address'] ) ? $s['address'] : 'short',
				'show_access'  => $this->on( $s, 'show_access' ),
				'tag_limit'    => isset( $s['tag_limit'] ) ? absint( $s['tag_limit'] ) : 1,
				'show_free'    => $this->on( $s, 'show_free' ),
				'show_excerpt' => $this->on( $s, 'show_excerpt' ),
				'show_meta'    => $this->on( $s, 'show_meta' ),
				'link_text'    => isset( $s['link_text'] ) ? $s['link_text'] : '',
				'heading_tag'  => isset( $s['heading_tag'] ) ? $s['heading_tag'] : 'h3',
			)
		);
	}
}
