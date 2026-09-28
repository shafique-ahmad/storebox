<?php
/**
 * Widget: Location Facts — address, access hours, units and phone.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Location Facts widget.
 */
class Location_Facts extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'location-facts';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-location-facts';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Location Facts', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-bullet-list';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'location', 'address', 'phone', 'access', 'facts' );
	}

	/**
	 * Fact choices.
	 *
	 * @return array<string, string>
	 */
	private static function items() {
		return array(
			'address' => esc_html__( 'Address', 'storebox-core' ),
			'access'  => esc_html__( 'Access', 'storebox-core' ),
			'units'   => esc_html__( 'Units', 'storebox-core' ),
			'phone'   => esc_html__( 'Phone', 'storebox-core' ),
			'email'   => esc_html__( 'Email', 'storebox-core' ),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_facts', array( 'label' => esc_html__( 'Facts', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_post_select( 'location', esc_html__( 'Location', 'storebox-core' ), 'sb_location', esc_html__( 'Current location', 'storebox-core' ) );

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'Show', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::items(),
				'default'     => array( 'address', 'access', 'units', 'phone' ),
			)
		);

		$this->add_control(
			'overlap',
			array(
				'label'       => esc_html__( 'Overlap the section above', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Pulls the facts bar up over the bottom of the hero, as in the demo.', 'storebox-core' ),
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
			'section_style_facts',
			array(
				'label' => esc_html__( 'Facts', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'box', '.sb-facts' );
		$this->add_text_style( 'label_text', esc_html__( 'Label', 'storebox-core' ), '.sb-facts dt' );
		$this->add_text_style( 'value_text', esc_html__( 'Value', 'storebox-core' ), '.sb-facts dd' );
		$this->add_text_style( 'link_text', esc_html__( 'Links', 'storebox-core' ), '.sb-facts dd a', true );

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
				'style'    => isset( $s['design'] ) ? $s['design'] : '',
				'location' => $this->resolve_post( isset( $s['location'] ) ? $s['location'] : 0, 'sb_location' ),
				'overlap'  => $this->on( $s, 'overlap' ),
				'items'    => ! empty( $s['items'] ) ? (array) $s['items'] : array( 'address', 'access', 'units', 'phone' ),
				'labels'   => $labels,
			)
		);
	}
}
