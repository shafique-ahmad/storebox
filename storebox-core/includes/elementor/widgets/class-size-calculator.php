<?php
/**
 * Widget: Size Calculator — visitors add items and get a recommended size.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Size Calculator widget.
 */
class Size_Calculator extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'size-calculator';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-size-calculator';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Size Calculator', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-number-field';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'size', 'calculator', 'estimate', 'volume', 'how much space' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_calculator', array( 'label' => esc_html__( 'Calculator', 'storebox-core' ) ) );

		$this->add_design_control();

		$this->add_control(
			'title',
			array(
				'label'       => esc_html__( 'Title', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'What are you storing?', 'storebox-core' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'       => esc_html__( 'Subtitle', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Add rough quantities — it does not need to be exact.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'unit_toggle',
			array(
				'label'   => esc_html__( 'm² / ft² switch', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'items_source',
			array(
				'label'   => esc_html__( 'Items', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default' => esc_html__( 'Standard household items', 'storebox-core' ),
					'custom'  => esc_html__( 'Custom items', 'storebox-core' ),
				),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'name',
			array(
				'label'   => esc_html__( 'Item', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Boxes (medium)', 'storebox-core' ),
			)
		);
		$repeater->add_control(
			'volume',
			array(
				'label'       => esc_html__( 'Volume (m³)', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0.01,
				'step'        => 0.05,
				'default'     => 0.1,
				'description' => esc_html__( 'Approximate space one item takes up.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'Items', 'storebox-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => storebox_core_default_calculator_items(),
				'condition'   => array( 'items_source' => 'custom' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_sizes', array( 'label' => esc_html__( 'Sizes and calculation', 'storebox-core' ) ) );

		$this->add_sizes_controls();

		$this->add_control(
			'stack',
			array(
				'label'       => esc_html__( 'Stacking height (m)', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0.5,
				'max'         => 5,
				'step'        => 0.1,
				'default'     => 2.2,
				'description' => esc_html__( 'How high items can be stacked in a unit.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'fill',
			array(
				'label'       => esc_html__( 'Usable share (%)', 'storebox-core' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 10,
				'max'         => 100,
				'step'        => 5,
				'default'     => 65,
				'description' => esc_html__( 'The rest is left free to walk in and reach the back.', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_result', array( 'label' => esc_html__( 'Result', 'storebox-core' ) ) );

		$this->add_control(
			'empty_text',
			array(
				'label'       => esc_html__( 'Before anything is added', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => esc_html__( 'Nothing added yet — pick a few items above.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'recommended_label',
			array(
				'label'   => esc_html__( 'Recommendation label', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Recommended', 'storebox-core' ),
			)
		);

		$this->add_control(
			'from_label',
			array(
				'label'   => esc_html__( 'Price label', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'From', 'storebox-core' ),
			)
		);

		$this->add_control(
			'fill_text',
			array(
				'label'       => esc_html__( 'Fill text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				/* translators: %d: how full the unit would be, in percent. */
				'default'     => esc_html__( 'About %d%% full, leaving room to walk in and reach the back.', 'storebox-core' ),
				/* translators: %d and %%: literal placeholders shown to the user, keep as is. */
				'description' => esc_html__( '%d is the percentage; write %% for a percent sign.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'Button text', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Check availability', 'storebox-core' ),
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
			'section_style_box',
			array(
				'label' => esc_html__( 'Box', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'box', '.sb-calc' );
		$this->add_text_style( 'title_text', esc_html__( 'Title', 'storebox-core' ), '.sb-calc__title' );
		$this->add_text_style( 'subtitle_text', esc_html__( 'Subtitle', 'storebox-core' ), '.sb-calc__sub' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_items',
			array(
				'label' => esc_html__( 'Items', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'item', '.sb-calc__item', array( 'background', 'border', 'radius' ) );

		$this->add_control(
			'item_active_background',
			array(
				'label'     => esc_html__( 'Background when added', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-calc__item.is-on' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'item_text', esc_html__( 'Item name', 'storebox-core' ), '.sb-calc__name' );
		$this->add_heading( 'step_heading', esc_html__( 'Plus / minus buttons', 'storebox-core' ) );
		$this->add_button_style( 'step', '.sb-calc__step button' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_result',
			array(
				'label' => esc_html__( 'Result', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'result', '.sb-calc__result' );
		$this->add_text_style( 'size_text', esc_html__( 'Recommended size', 'storebox-core' ), '.sb-calc__size' );
		$this->add_text_style( 'price_text', esc_html__( 'Price', 'storebox-core' ), '.sb-calc__price b' );

		$this->add_control(
			'bar_color',
			array(
				'label'     => esc_html__( 'Fill bar color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .sb-calc__bar i' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_heading( 'button_heading', esc_html__( 'Button', 'storebox-core' ) );
		$this->add_button_style( 'button', '.sb-calc__result .sb-btn' );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$items = null;

		if ( isset( $s['items_source'] ) && 'custom' === $s['items_source'] && ! empty( $s['items'] ) ) {
			$items = array();
			foreach ( (array) $s['items'] as $row ) {
				$items[] = array(
					'name'   => isset( $row['name'] ) ? $row['name'] : '',
					'volume' => isset( $row['volume'] ) ? $row['volume'] : 0,
				);
			}
		}

		$fill = isset( $s['fill'] ) && is_numeric( $s['fill'] ) ? (float) $s['fill'] / 100 : 0.65;

		$this->render_component(
			array(
				'style'             => isset( $s['design'] ) ? $s['design'] : '',
				'title'             => isset( $s['title'] ) ? (string) $s['title'] : null,
				'subtitle'          => isset( $s['subtitle'] ) ? (string) $s['subtitle'] : null,
				'items'             => $items,
				'sizes'             => $this->sizes_from_settings( $s ),
				'sync_prices'       => $this->on( $s, 'sync_prices' ),
				'stack'             => isset( $s['stack'] ) && is_numeric( $s['stack'] ) ? (float) $s['stack'] : 2.2,
				'fill'              => $fill,
				'unit_toggle'       => $this->on( $s, 'unit_toggle' ),
				'empty_text'        => isset( $s['empty_text'] ) ? (string) $s['empty_text'] : null,
				'recommended_label' => isset( $s['recommended_label'] ) ? (string) $s['recommended_label'] : null,
				'from_label'        => isset( $s['from_label'] ) ? (string) $s['from_label'] : null,
				'fill_text'         => $this->text_or_default( $s, 'fill_text' ),
				'button_text'       => isset( $s['button_text'] ) ? (string) $s['button_text'] : null,
				'button_url'        => $this->url( $s, 'button_url' ),
			)
		);
	}
}
