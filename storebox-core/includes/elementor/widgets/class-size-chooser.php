<?php
/**
 * Widget: Size Chooser — boxes drawn to scale; selecting one shows its details.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Size Chooser widget.
 */
class Size_Chooser extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'size-chooser';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-size-chooser';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Size Chooser', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-column';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'size', 'chooser', 'scale', 'compare', 'units' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_chooser', array( 'label' => esc_html__( 'Sizes', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_sizes_controls();

		$this->add_control(
			'selected',
			array(
				'label'       => esc_html__( 'Selected size', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 20,
				'default'     => 3,
				'description' => esc_html__( 'Position in the list, starting at 1.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'show_meta',
			array(
				'label'   => esc_html__( 'Price and “fits” under each box', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto' => esc_html__( 'Automatic (by design)', 'storebox-core' ),
					'yes'  => esc_html__( 'Show', 'storebox-core' ),
					'no'   => esc_html__( 'Hide', 'storebox-core' ),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_details', array( 'label' => esc_html__( 'Details panel', 'storebox-core' ) ) );

		$this->add_control(
			'detail_label',
			array(
				'label'       => esc_html__( 'Kicker (editorial)', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Selected', 'storebox-core' ),
			)
		);

		foreach ( array(
			'label_dimensions' => array( esc_html__( 'Dimensions label', 'storebox-core' ), esc_html__( 'Dimensions', 'storebox-core' ) ),
			'label_fits'       => array( esc_html__( '“Fits” label', 'storebox-core' ), esc_html__( 'Typically fits', 'storebox-core' ) ),
			'label_from'       => array( esc_html__( 'Price label', 'storebox-core' ), esc_html__( 'From', 'storebox-core' ) ),
		) as $key => $texts ) {
			$this->add_control(
				$key,
				array(
					'label'       => $texts[0],
					'type'        => Controls_Manager::TEXT,
					'placeholder' => $texts[1],
				)
			);
		}

		$this->add_control(
			'price_suffix',
			array(
				'label'       => esc_html__( 'Price suffix', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( '/ month', 'storebox-core' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Button text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'See available units', 'storebox-core' ),
			)
		);

		$this->add_control(
			'button_url',
			array(
				'label'       => esc_html__( 'Button link', 'storebox-core' ),
				'type'        => Controls_Manager::URL,
				'description' => esc_html__( 'Empty links to the units page set in Storebox → Settings.', 'storebox-core' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_scale',
			array(
				'label' => esc_html__( 'Scale', 'storebox-core' ),
			)
		);

		$this->add_control(
			'min_height',
			array(
				'label'       => esc_html__( 'Smallest box height (px)', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 20,
				'max'         => 200,
				'placeholder' => '52',
			)
		);

		$this->add_control(
			'max_height',
			array(
				'label'       => esc_html__( 'Largest box height (px)', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 60,
				'max'         => 400,
				'placeholder' => '200',
			)
		);

		$this->add_control(
			'ratio',
			array(
				'label'       => esc_html__( 'Width ÷ height', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0.5,
				'max'         => 3,
				'step'        => 0.05,
				'placeholder' => '1.34',
				'description' => esc_html__( 'Box heights grow with the square root of the floor area, so boxes compare by area.', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_boxes',
			array(
				'label' => esc_html__( 'Boxes', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'box_background',
			array(
				'label'     => esc_html__( 'Box color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-chooser__box' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'box_active_background',
			array(
				'label'     => esc_html__( 'Selected box color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-chooser__item.is-active .sb-chooser__box' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'box_active_text',
			array(
				'label'     => esc_html__( 'Selected box text', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-chooser__item.is-active .sb-chooser__box b' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'box_label', esc_html__( 'Box label', 'storebox-core' ), '.sb-chooser__box b' );
		$this->add_text_style( 'meta_text', esc_html__( 'Text under the boxes', 'storebox-core' ), '.sb-chooser__meta' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_detail',
			array(
				'label' => esc_html__( 'Details panel', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'detail', '.sb-chooser__detail' );
		$this->add_text_style( 'big_text', esc_html__( 'Size', 'storebox-core' ), '.sb-chooser__big' );
		$this->add_text_style( 'alt_text', esc_html__( 'Square feet and comparison', 'storebox-core' ), '.sb-chooser__alt' );
		$this->add_text_style( 'facts_text', esc_html__( 'Facts', 'storebox-core' ), '.sb-chooser__facts dd, {{WRAPPER}} .sb-chooser__list b' );
		$this->add_heading( 'button_heading', esc_html__( 'Button', 'storebox-core' ) );
		$this->add_button_style( 'button', '.sb-chooser__detail .sb-btn' );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$labels = array();

		foreach ( array( 'dimensions', 'fits', 'from' ) as $key ) {
			if ( ! empty( $s[ 'label_' . $key ] ) ) {
				$labels[ $key ] = $s[ 'label_' . $key ];
			}
		}

		$show_meta = isset( $s['show_meta'] ) ? $s['show_meta'] : 'auto';

		$this->render_component(
			array(
				'style'        => isset( $s['design'] ) ? $s['design'] : '',
				'sizes'        => $this->sizes_from_settings( $s ),
				'sync_prices'  => $this->on( $s, 'sync_prices' ),
				'selected'     => isset( $s['selected'] ) ? absint( $s['selected'] ) : 3,
				'min_height'   => isset( $s['min_height'] ) && is_numeric( $s['min_height'] ) ? (float) $s['min_height'] : 0,
				'max_height'   => isset( $s['max_height'] ) && is_numeric( $s['max_height'] ) ? (float) $s['max_height'] : 0,
				'ratio'        => isset( $s['ratio'] ) && is_numeric( $s['ratio'] ) ? (float) $s['ratio'] : 0,
				'show_meta'    => 'auto' === $show_meta ? 'auto' : 'yes' === $show_meta,
				'detail_label' => $this->text_or_default( $s, 'detail_label' ),
				'labels'       => $labels,
				'price_suffix' => $this->text_or_default( $s, 'price_suffix' ),
				'button_text'  => $this->text_or_default( $s, 'button_text' ),
				'button_url'   => $this->url( $s, 'button_url' ),
			)
		);
	}
}
