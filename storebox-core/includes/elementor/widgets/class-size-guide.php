<?php
/**
 * Widget: Size Guide — table of sizes, dimensions, what fits and prices.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Size Guide widget.
 */
class Size_Guide extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'size-guide';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-size-guide';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Size Guide', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-table';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'size', 'guide', 'table', 'prices', 'dimensions' );
	}

	/**
	 * Column headers.
	 *
	 * @return array<string, string>
	 */
	private static function headers() {
		return array(
			'size'     => esc_html__( 'Size', 'storebox-core' ),
			'dims'     => esc_html__( 'Dimensions', 'storebox-core' ),
			'fits'     => esc_html__( 'Typically fits', 'storebox-core' ),
			'practice' => esc_html__( 'In practice', 'storebox-core' ),
			'price'    => esc_html__( 'From', 'storebox-core' ),
			'caption'  => esc_html__( 'Unit size guide', 'storebox-core' ),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_guide', array( 'label' => esc_html__( 'Size guide', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_sizes_controls( true );

		$this->add_control(
			'price_note',
			array(
				'label'       => esc_html__( 'Text under the price', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'per month', 'storebox-core' ),
			)
		);

		$this->add_heading( 'headers_heading', esc_html__( 'Column headers', 'storebox-core' ) );

		foreach ( self::headers() as $key => $label ) {
			$this->add_control(
				'header_' . $key,
				array(
					'label'       => 'caption' === $key ? esc_html__( 'Table caption (screen readers)', 'storebox-core' ) : $label,
					'type'        => Controls_Manager::TEXT,
					'placeholder' => $label,
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_table',
			array(
				'label' => esc_html__( 'Table', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'table', '.sb-guide__scroll', array( 'background', 'border', 'radius', 'shadow' ) );
		$this->add_text_style( 'head_text', esc_html__( 'Header row', 'storebox-core' ), '.sb-guide__table thead th' );

		$this->add_control(
			'head_background',
			array(
				'label'     => esc_html__( 'Header background', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-guide__table thead th' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_text_style( 'size_text', esc_html__( 'Size column', 'storebox-core' ), '.sb-guide__table tbody th b' );
		$this->add_text_style( 'cell_text', esc_html__( 'Cells', 'storebox-core' ), '.sb-guide__table td' );

		$this->add_control(
			'row_border',
			array(
				'label'     => esc_html__( 'Row divider color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .sb-guide__table th, {{WRAPPER}} .sb-guide__table td' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'cell_padding',
			array(
				'label'      => esc_html__( 'Cell padding', 'storebox-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .sb-guide__table th, {{WRAPPER}} .sb-guide__table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s       = $this->get_settings_for_display();
		$headers = array();

		foreach ( array_keys( self::headers() ) as $key ) {
			if ( ! empty( $s[ 'header_' . $key ] ) ) {
				$headers[ $key ] = $s[ 'header_' . $key ];
			}
		}

		$this->render_component(
			array(
				'style'       => isset( $s['design'] ) ? $s['design'] : '',
				'rows'        => $this->sizes_from_settings( $s ),
				'sync_prices' => $this->on( $s, 'sync_prices' ),
				'headers'     => $headers,
				'price_note'  => $this->text_or_default( $s, 'price_note' ),
			)
		);
	}
}
