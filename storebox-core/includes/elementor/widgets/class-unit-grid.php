<?php
/**
 * Widget: Unit Grid — cards in a grid (with optional filters) or a sliding rail.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Unit Grid widget.
 */
class Unit_Grid extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'unit-grid';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-unit-grid';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Unit Grid', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'units', 'storage', 'grid', 'slider', 'carousel', 'rail', 'filter', 'prices' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		/* Units ------------------------------------------------------------ */
		$this->start_controls_section( 'section_query', array( 'label' => esc_html__( 'Units', 'storebox-core' ) ) );

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Show', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'      => esc_html__( 'All units', 'storebox-core' ),
					'featured' => esc_html__( 'Featured units', 'storebox-core' ),
					'manual'   => esc_html__( 'Selected units', 'storebox-core' ),
					'location' => esc_html__( 'Units at a location', 'storebox-core' ),
					'similar'  => esc_html__( 'Other units (excludes the current unit)', 'storebox-core' ),
				),
			)
		);

		$this->add_control(
			'ids',
			array(
				'label'       => esc_html__( 'Units', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => static::post_options( 'sb_unit' ),
				'condition'   => array( 'source' => 'manual' ),
			)
		);

		$this->add_control(
			'location',
			array(
				'label'     => esc_html__( 'Location', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array( '' => esc_html__( 'Current location', 'storebox-core' ) ) + static::post_options( 'sb_location' ),
				'condition' => array( 'source' => 'location' ),
			)
		);

		$this->add_control(
			'type',
			array(
				'label'   => esc_html__( 'Unit type', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => static::term_options( 'sb_unit_type', esc_html__( 'Any type', 'storebox-core' ) ),
			)
		);

		$this->add_control(
			'size',
			array(
				'label'   => esc_html__( 'Unit size', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => static::term_options( 'sb_unit_size', esc_html__( 'Any size', 'storebox-core' ) ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'       => esc_html__( 'Number of units', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 100,
				'default'     => 0,
				'description' => esc_html__( '0 shows all.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'     => esc_html__( 'Order by', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'area',
				'options'   => array(
					'area'       => esc_html__( 'Size', 'storebox-core' ),
					'price'      => esc_html__( 'Price', 'storebox-core' ),
					'menu_order' => esc_html__( 'Manual order', 'storebox-core' ),
					'title'      => esc_html__( 'Title', 'storebox-core' ),
				),
				'condition' => array( 'source!' => 'manual' ),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Order', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ASC',
				'options'   => array(
					'ASC'  => esc_html__( 'Ascending', 'storebox-core' ),
					'DESC' => esc_html__( 'Descending', 'storebox-core' ),
				),
				'condition' => array( 'source!' => 'manual' ),
			)
		);

		$this->add_control(
			'hide_full',
			array(
				'label' => esc_html__( 'Hide fully booked units', 'storebox-core' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'empty_message',
			array(
				'label'       => esc_html__( 'Message when no units match', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
			)
		);

		$this->end_controls_section();

		/* Layout ----------------------------------------------------------- */
		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'Layout', 'storebox-core' ) ) );

		$this->add_design_control();

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid' => esc_html__( 'Grid', 'storebox-core' ),
					'rail' => esc_html__( 'Sliding rail', 'storebox-core' ),
				),
			)
		);

		$this->add_columns_control( 'columns', '.sb-unit-grid__items', 3, 4 );

		$this->add_control(
			'autoplay',
			array(
				'label'       => esc_html__( 'Autoplay delay (ms)', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 20000,
				'step'        => 500,
				'default'     => 4000,
				'description' => esc_html__( '0 turns autoplay off. Autoplay pauses on hover and focus, and never runs for visitors who prefer reduced motion.', 'storebox-core' ),
				'condition'   => array( 'layout' => 'rail' ),
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'label'     => esc_html__( 'Arrows', 'storebox-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'layout' => 'rail' ),
			)
		);

		$this->add_control(
			'show_progress',
			array(
				'label'     => esc_html__( 'Progress bar', 'storebox-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'layout' => 'rail' ),
			)
		);

		$this->add_control(
			'hint',
			array(
				'label'       => esc_html__( 'Hint below the rail', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
				'placeholder' => esc_html__( 'Drag, scroll or use the arrows', 'storebox-core' ),
				'condition'   => array( 'layout' => 'rail' ),
			)
		);

		$this->end_controls_section();

		/* Card ------------------------------------------------------------- */
		$this->start_controls_section( 'section_card', array( 'label' => esc_html__( 'Card', 'storebox-core' ) ) );

		$this->add_control(
			'show_image',
			array(
				'label'   => esc_html__( 'Image', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'     => esc_html__( 'Image size', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'storebox-card',
				'options'   => static::image_size_options(),
				'condition' => array( 'show_image' => 'yes' ),
			)
		);

		$this->add_control(
			'show_status',
			array(
				'label'   => esc_html__( 'Availability tag', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'subline',
			array(
				'label'   => esc_html__( 'Line under the size', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'       => esc_html__( 'Automatic (by design)', 'storebox-core' ),
					'dimensions' => esc_html__( 'Square feet and dimensions', 'storebox-core' ),
					'name'       => esc_html__( 'Square feet and unit name', 'storebox-core' ),
					'none'       => esc_html__( 'Square feet only', 'storebox-core' ),
				),
			)
		);

		$this->add_control(
			'show_name',
			array(
				'label' => esc_html__( 'Unit name', 'storebox-core' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'description',
			array(
				'label'   => esc_html__( 'Description', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'          => esc_html__( 'Automatic (by design)', 'storebox-core' ),
					'bullets'       => esc_html__( 'Bullet list', 'storebox-core' ),
					'fits_location' => esc_html__( 'What fits, type and location', 'storebox-core' ),
					'excerpt'       => esc_html__( 'Excerpt', 'storebox-core' ),
					'none'          => esc_html__( 'None', 'storebox-core' ),
				),
			)
		);

		$this->add_control(
			'bullets',
			array(
				'label'     => esc_html__( 'Bullets from', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'features',
				'options'   => array(
					'features'   => esc_html__( 'Card features', 'storebox-core' ),
					'highlights' => esc_html__( 'Unit highlights', 'storebox-core' ),
				),
				'condition' => array( 'description' => array( 'auto', 'bullets' ) ),
			)
		);

		$this->add_control(
			'bullet_limit',
			array(
				'label'     => esc_html__( 'Number of bullets', 'storebox-core' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 8,
				'default'   => 3,
				'condition' => array( 'description' => array( 'auto', 'bullets' ) ),
			)
		);

		$this->add_control(
			'bullet_location',
			array(
				'label'     => esc_html__( 'Location as last bullet', 'storebox-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'description' => array( 'auto', 'bullets' ) ),
			)
		);

		$this->add_control(
			'show_price',
			array(
				'label'   => esc_html__( 'Price', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'cta_style',
			array(
				'label'   => esc_html__( 'Call to action', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'   => esc_html__( 'Automatic (by design)', 'storebox-core' ),
					'button' => esc_html__( 'Button', 'storebox-core' ),
					'link'   => esc_html__( 'Text link', 'storebox-core' ),
				),
			)
		);

		$this->add_control(
			'cta_text',
			array(
				'label'       => esc_html__( 'Button text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Reserve', 'storebox-core' ),
			)
		);

		$this->add_control(
			'waitlist_text',
			array(
				'label'       => esc_html__( 'Button text when full', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Join waitlist', 'storebox-core' ),
			)
		);

		$this->add_control(
			'full_label',
			array(
				'label'       => esc_html__( 'Tag text when full', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Fully booked', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		/* Header ----------------------------------------------------------- */
		$this->start_controls_section( 'section_header', array( 'label' => esc_html__( 'Header', 'storebox-core' ) ) );

		$this->add_control(
			'header',
			array(
				'label'       => esc_html__( 'Show header', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'A title row above the units, with the rail arrows and an optional button on the right.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'header_eyebrow',
			array(
				'label'     => esc_html__( 'Eyebrow', 'storebox-core' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'header' => 'yes' ),
			)
		);

		$this->add_control(
			'header_title',
			array(
				'label'       => esc_html__( 'Title', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'header' => 'yes' ),
			)
		);

		$this->add_control(
			'header_tag',
			array(
				'label'     => esc_html__( 'Title tag', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h2',
				'options'   => static::tag_options( array( 'h1', 'h2', 'h3', 'h4', 'div', 'p' ) ),
				'condition' => array( 'header' => 'yes' ),
			)
		);

		$this->add_control(
			'header_button_text',
			array(
				'label'     => esc_html__( 'Button text', 'storebox-core' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'header' => 'yes' ),
			)
		);

		$this->add_control(
			'header_button_url',
			array(
				'label'     => esc_html__( 'Button link', 'storebox-core' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'header' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* Filters ---------------------------------------------------------- */
		$this->start_controls_section(
			'section_filters',
			array(
				'label'     => esc_html__( 'Filters', 'storebox-core' ),
				'condition' => array( 'layout' => 'grid' ),
			)
		);

		$this->add_control(
			'filters',
			array(
				'label'       => esc_html__( 'Show filter bar', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Visitors can filter by size, location and type and sort by size or price. Works without JavaScript too.', 'storebox-core' ),
			)
		);

		foreach ( array(
			'filter_size'      => esc_html__( 'Size filter', 'storebox-core' ),
			'filter_location'  => esc_html__( 'Location filter', 'storebox-core' ),
			'filter_type'      => esc_html__( 'Type filter', 'storebox-core' ),
			'filter_sort'      => esc_html__( 'Sorting', 'storebox-core' ),
			'filter_available' => esc_html__( '“Available now” checkbox', 'storebox-core' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'     => $label,
					'type'      => Controls_Manager::SWITCHER,
					'default'   => 'yes',
					'condition' => array( 'filters' => 'yes' ),
				)
			);
		}

		$this->add_control(
			'filters_overlap',
			array(
				'label'       => esc_html__( 'Overlap the section above', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Pulls the filter bar up over the bottom of the previous section, as in the demo.', 'storebox-core' ),
				'condition'   => array( 'filters' => 'yes' ),
			)
		);

		$this->add_control(
			'label_results',
			array(
				'label'       => esc_html__( 'Results text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Showing %s units', 'storebox-core' ),
				'description' => esc_html__( '%s is replaced by the number of units.', 'storebox-core' ),
				'condition'   => array( 'filters' => 'yes' ),
			)
		);

		$this->add_control(
			'label_empty_title',
			array(
				'label'       => esc_html__( 'No results title', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Nothing matches those filters.', 'storebox-core' ),
				'condition'   => array( 'filters' => 'yes' ),
			)
		);

		$this->add_control(
			'label_empty_text',
			array(
				'label'       => esc_html__( 'No results text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'placeholder' => esc_html__( 'Try another location, or call us — we often have units coming free that are not listed yet.', 'storebox-core' ),
				'condition'   => array( 'filters' => 'yes' ),
			)
		);

		$this->end_controls_section();

		/* Style: cards ----------------------------------------------------- */
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Cards', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_gap_control( 'grid_gap', '.sb-unit-grid__items, {{WRAPPER}} .sb-rail' );
		$this->add_box_style( 'card', '.sb-unit-card', array( 'background', 'border', 'radius', 'shadow' ) );
		$this->add_box_style( 'card_body', '.sb-unit-card__body', array( 'padding' ) );

		$this->add_responsive_control(
			'image_ratio',
			array(
				'label'      => esc_html__( 'Image height', 'storebox-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 120,
						'max' => 480,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .sb-unit-card__img' => 'aspect-ratio: auto; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'show_image' => 'yes' ),
			)
		);

		$this->add_text_style( 'size_text', esc_html__( 'Size', 'storebox-core' ), '.sb-unit-card__size b' );
		$this->add_text_style( 'subline_text', esc_html__( 'Line under the size', 'storebox-core' ), '.sb-unit-card__size span' );
		$this->add_text_style( 'desc_text', esc_html__( 'Description', 'storebox-core' ), '.sb-unit-card__list li, {{WRAPPER}} .sb-unit-card__fits, {{WRAPPER}} .sb-unit-card__name' );
		$this->add_text_style( 'price_text', esc_html__( 'Price', 'storebox-core' ), '.sb-price b' );

		$this->end_controls_section();

		/* Style: button ---------------------------------------------------- */
		$this->start_controls_section(
			'section_style_cta',
			array(
				'label' => esc_html__( 'Card button', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_button_style( 'cta', '.sb-unit-card__cta' );

		$this->end_controls_section();

		/* Style: availability tags ----------------------------------------- */
		$this->start_controls_section(
			'section_style_tags',
			array(
				'label'     => esc_html__( 'Availability tags', 'storebox-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_status' => 'yes' ),
			)
		);

		foreach ( array(
			'ok'   => esc_html__( 'Available', 'storebox-core' ),
			'low'  => esc_html__( 'Few left', 'storebox-core' ),
			'full' => esc_html__( 'Fully booked', 'storebox-core' ),
		) as $key => $label ) {
			$this->add_heading( 'tag_' . $key . '_heading', $label );
			$this->add_control(
				'tag_' . $key . '_color',
				array(
					'label'     => esc_html__( 'Text color', 'storebox-core' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} .sb-tag--' . $key => 'color: {{VALUE}};' ),
				)
			);
			$this->add_control(
				'tag_' . $key . '_background',
				array(
					'label'     => esc_html__( 'Background color', 'storebox-core' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} .sb-tag--' . $key => 'background-color: {{VALUE}};' ),
				)
			);
		}

		$this->end_controls_section();

		/* Style: header ---------------------------------------------------- */
		$this->start_controls_section(
			'section_style_header',
			array(
				'label'      => esc_html__( 'Header and arrows', 'storebox-core' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'conditions' => array(
					'relation' => 'or',
					'terms'    => array(
						array(
							'name'  => 'header',
							'value' => 'yes',
						),
						array(
							'name'  => 'layout',
							'value' => 'rail',
						),
					),
				),
			)
		);

		$this->add_text_style( 'header_eyebrow_text', esc_html__( 'Eyebrow', 'storebox-core' ), '.sb-rail-head__text > span' );
		$this->add_text_style( 'header_title_text', esc_html__( 'Title', 'storebox-core' ), '.sb-rail-head__title' );
		$this->add_heading( 'arrows_heading', esc_html__( 'Arrows', 'storebox-core' ) );
		$this->add_button_style( 'arrows', '.sb-rail-btn' );

		$this->end_controls_section();

		/* Style: filters --------------------------------------------------- */
		$this->start_controls_section(
			'section_style_filters',
			array(
				'label'     => esc_html__( 'Filter bar', 'storebox-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'layout'  => 'grid',
					'filters' => 'yes',
				),
			)
		);

		$this->add_box_style( 'filters_box', '.sb-filters' );
		$this->add_text_style( 'filters_label', esc_html__( 'Labels', 'storebox-core' ), '.sb-fgroup label, {{WRAPPER}} .sb-fcheck' );
		$this->add_heading( 'filters_field_heading', esc_html__( 'Fields', 'storebox-core' ) );
		$this->add_box_style( 'filters_field', '.sb-fgroup select', array( 'background', 'border', 'radius' ) );
		$this->add_text_style( 'filters_results', esc_html__( 'Results line', 'storebox-core' ), '.sb-results p' );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$source = isset( $s['source'] ) ? $s['source'] : 'all';

		$labels = array_filter(
			array(
				'results'     => isset( $s['label_results'] ) ? (string) $s['label_results'] : '',
				'empty_title' => isset( $s['label_empty_title'] ) ? (string) $s['label_empty_title'] : '',
				'empty_text'  => isset( $s['label_empty_text'] ) ? (string) $s['label_empty_text'] : '',
			),
			'strlen'
		);
		if ( isset( $labels['results'] ) && false === strpos( $labels['results'], '%s' ) ) {
			$labels['results'] .= ' (%s)';
		}

		$this->render_component(
			array(
				'style'              => isset( $s['design'] ) ? $s['design'] : '',
				'layout'             => isset( $s['layout'] ) ? $s['layout'] : 'grid',
				'columns'            => ! empty( $s['columns'] ) ? absint( $s['columns'] ) : 3,
				'source'             => $source,
				'ids'                => isset( $s['ids'] ) ? (array) $s['ids'] : array(),
				'location'           => 'location' === $source ? $this->resolve_post( isset( $s['location'] ) ? $s['location'] : 0, 'sb_location' ) : 0,
				'unit'               => 'similar' === $source ? $this->resolve_post( 0, 'sb_unit' ) : 0,
				'type'               => isset( $s['type'] ) ? $s['type'] : '',
				'size'               => isset( $s['size'] ) ? $s['size'] : '',
				'count'              => isset( $s['count'] ) ? absint( $s['count'] ) : 0,
				'orderby'            => isset( $s['orderby'] ) ? $s['orderby'] : 'area',
				'order'              => isset( $s['order'] ) ? $s['order'] : 'ASC',
				'hide_full'          => $this->on( $s, 'hide_full' ),
				'empty_message'      => isset( $s['empty_message'] ) ? $s['empty_message'] : '',
				'image_size'         => ! empty( $s['image_size'] ) ? $s['image_size'] : 'storebox-card',
				'show_image'         => $this->on( $s, 'show_image' ),
				'show_status'        => $this->on( $s, 'show_status' ),
				'subline'            => isset( $s['subline'] ) ? $s['subline'] : 'auto',
				'show_name'          => $this->on( $s, 'show_name' ),
				'description'        => isset( $s['description'] ) ? $s['description'] : 'auto',
				'bullets'            => isset( $s['bullets'] ) ? $s['bullets'] : 'features',
				'bullet_limit'       => isset( $s['bullet_limit'] ) ? absint( $s['bullet_limit'] ) : 3,
				'bullet_location'    => $this->on( $s, 'bullet_location' ),
				'show_price'         => $this->on( $s, 'show_price' ),
				'cta_style'          => isset( $s['cta_style'] ) ? $s['cta_style'] : 'auto',
				'cta_text'           => isset( $s['cta_text'] ) ? $s['cta_text'] : '',
				'waitlist_text'      => isset( $s['waitlist_text'] ) ? $s['waitlist_text'] : '',
				'full_label'         => isset( $s['full_label'] ) ? $s['full_label'] : '',
				'filters'            => $this->on( $s, 'filters' ),
				'filter_size'        => $this->on( $s, 'filter_size' ),
				'filter_location'    => $this->on( $s, 'filter_location' ),
				'filter_type'        => $this->on( $s, 'filter_type' ),
				'filter_sort'        => $this->on( $s, 'filter_sort' ),
				'filter_available'   => $this->on( $s, 'filter_available' ),
				'filters_overlap'    => $this->on( $s, 'filters_overlap' ),
				'labels'             => $labels,
				'autoplay'           => isset( $s['autoplay'] ) ? absint( $s['autoplay'] ) : 0,
				'show_arrows'        => $this->on( $s, 'show_arrows' ),
				'show_progress'      => $this->on( $s, 'show_progress' ),
				'hint'               => isset( $s['hint'] ) ? $s['hint'] : '',
				'header'             => $this->on( $s, 'header' ),
				'header_eyebrow'     => isset( $s['header_eyebrow'] ) ? $s['header_eyebrow'] : '',
				'header_title'       => isset( $s['header_title'] ) ? $s['header_title'] : '',
				'header_tag'         => isset( $s['header_tag'] ) ? $s['header_tag'] : 'h2',
				'header_button_text' => isset( $s['header_button_text'] ) ? $s['header_button_text'] : '',
				'header_button_url'  => $this->url( $s, 'header_button_url' ),
			)
		);
	}
}
