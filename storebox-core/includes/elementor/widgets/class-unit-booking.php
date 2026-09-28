<?php
/**
 * Widget: Unit Booking — price, availability, key facts and the reserve button.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Unit Booking widget.
 */
class Unit_Booking extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'unit-booking';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-unit-booking';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Unit Booking', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-price-table';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'unit', 'booking', 'reserve', 'price', 'sidebar' );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_booking', array( 'label' => esc_html__( 'Booking panel', 'storebox-core' ) ) );

		$this->add_design_control();
		$this->add_post_select( 'unit', esc_html__( 'Unit', 'storebox-core' ), 'sb_unit', esc_html__( 'Current unit', 'storebox-core' ) );

		foreach ( array(
			'show_weekly'   => esc_html__( 'Weekly price', 'storebox-core' ),
			'show_status'   => esc_html__( 'Availability tag', 'storebox-core' ),
			'show_location' => esc_html__( 'Location row', 'storebox-core' ),
			'show_access'   => esc_html__( 'Access hours row', 'storebox-core' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'   => $label,
					'type'    => Controls_Manager::SWITCHER,
					'default' => 'yes',
				)
			);
		}

		$this->add_control(
			'rows_source',
			array(
				'label'       => esc_html__( 'Extra rows', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'settings',
				'options'     => array(
					'settings' => esc_html__( 'From Storebox → Settings', 'storebox-core' ),
					'custom'   => esc_html__( 'Custom', 'storebox-core' ),
					'none'     => esc_html__( 'None', 'storebox-core' ),
				),
				'description' => esc_html__( 'Rows such as deposit or minimum term.', 'storebox-core' ),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'label',
			array(
				'label'   => esc_html__( 'Label', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Deposit', 'storebox-core' ),
			)
		);
		$repeater->add_control(
			'value',
			array(
				'label'   => esc_html__( 'Value', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'None', 'storebox-core' ),
			)
		);

		$this->add_control(
			'rows',
			array(
				'label'       => esc_html__( 'Rows', 'storebox-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array(
						'label' => esc_html__( 'Deposit', 'storebox-core' ),
						'value' => esc_html__( 'None', 'storebox-core' ),
					),
					array(
						'label' => esc_html__( 'Minimum term', 'storebox-core' ),
						'value' => esc_html__( 'None', 'storebox-core' ),
					),
				),
				'condition'   => array( 'rows_source' => 'custom' ),
			)
		);

		$this->add_heading( 'button_heading', esc_html__( 'Button', 'storebox-core' ) );

		$this->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Button text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Reserve this unit', 'storebox-core' ),
			)
		);

		$this->add_control(
			'button_url',
			array(
				'label'       => esc_html__( 'Button link', 'storebox-core' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => '#reserve',
				'description' => esc_html__( 'Defaults to #reserve — the ID of the Enquiry Form widget on the unit page.', 'storebox-core' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'waitlist_text',
			array(
				'label'       => esc_html__( 'Button text when full', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Join the waitlist', 'storebox-core' ),
			)
		);

		$this->add_control(
			'waitlist_url',
			array(
				'label'       => esc_html__( 'Button link when full', 'storebox-core' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => '#reserve',
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => esc_html__( 'Note below the button', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Held free for 7 days · cancel any time', 'storebox-core' ),
				'description' => esc_html__( 'Empty uses the note from Storebox → Settings.', 'storebox-core' ),
			)
		);

		$this->add_heading( 'help_heading', esc_html__( 'Help box', 'storebox-core' ) );

		$this->add_control(
			'help',
			array(
				'label'   => esc_html__( 'Show “talk to us” box', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'help_title',
			array(
				'label'       => esc_html__( 'Title', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Rather talk it through?', 'storebox-core' ),
				'condition'   => array( 'help' => 'yes' ),
			)
		);

		$this->add_control(
			'help_text',
			array(
				'label'       => esc_html__( 'Text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'A person answers, usually within three rings.', 'storebox-core' ),
				'condition'   => array( 'help' => 'yes' ),
			)
		);

		$this->add_control(
			'help_phone',
			array(
				'label'       => esc_html__( 'Phone', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'description' => esc_html__( 'Empty uses the phone number from Storebox → Settings or the Customizer.', 'storebox-core' ),
				'condition'   => array( 'help' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_panel',
			array(
				'label' => esc_html__( 'Panel', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'panel', '.sb-book' );
		$this->add_text_style( 'price_text', esc_html__( 'Price', 'storebox-core' ), '.sb-book__price b' );
		$this->add_text_style( 'weekly_text', esc_html__( 'Weekly price', 'storebox-core' ), '.sb-book__alt' );
		$this->add_text_style( 'rows_text', esc_html__( 'Rows', 'storebox-core' ), '.sb-book__rows li' );
		$this->add_text_style( 'note_text', esc_html__( 'Note', 'storebox-core' ), '.sb-book__note' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => esc_html__( 'Button', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_button_style( 'button', '.sb-book .sb-btn' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_help',
			array(
				'label'     => esc_html__( 'Help box', 'storebox-core' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'help' => 'yes' ),
			)
		);

		$this->add_box_style( 'help_box', '.sb-help' );
		$this->add_text_style( 'help_title_text', esc_html__( 'Title', 'storebox-core' ), '.sb-help__title' );
		$this->add_text_style( 'help_body_text', esc_html__( 'Text', 'storebox-core' ), '.sb-help p' );
		$this->add_text_style( 'help_phone_text', esc_html__( 'Phone', 'storebox-core' ), '.sb-help a', true );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$rows = null;

		if ( isset( $s['rows_source'] ) && 'custom' === $s['rows_source'] ) {
			$rows = array();
			foreach ( (array) $s['rows'] as $row ) {
				$rows[] = array(
					'label' => isset( $row['label'] ) ? $row['label'] : '',
					'value' => isset( $row['value'] ) ? $row['value'] : '',
				);
			}
		} elseif ( isset( $s['rows_source'] ) && 'none' === $s['rows_source'] ) {
			$rows = array();
		}

		$this->render_component(
			array(
				'style'         => isset( $s['design'] ) ? $s['design'] : '',
				'unit'          => $this->resolve_post( isset( $s['unit'] ) ? $s['unit'] : 0, 'sb_unit' ),
				'show_weekly'   => $this->on( $s, 'show_weekly' ),
				'show_status'   => $this->on( $s, 'show_status' ),
				'show_location' => $this->on( $s, 'show_location' ),
				'show_access'   => $this->on( $s, 'show_access' ),
				'rows'          => $rows,
				'button_text'   => isset( $s['button_text'] ) ? $s['button_text'] : '',
				'button_url'    => $this->url( $s, 'button_url' ),
				'waitlist_text' => isset( $s['waitlist_text'] ) ? $s['waitlist_text'] : '',
				'waitlist_url'  => $this->url( $s, 'waitlist_url' ),
				'note'          => $this->text_or_default( $s, 'note' ),
				'help'          => $this->on( $s, 'help' ),
				'help_title'    => $this->text_or_default( $s, 'help_title' ),
				'help_text'     => $this->text_or_default( $s, 'help_text' ),
				'help_phone'    => isset( $s['help_phone'] ) ? $s['help_phone'] : '',
			)
		);
	}
}
