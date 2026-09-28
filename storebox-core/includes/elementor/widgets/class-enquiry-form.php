<?php
/**
 * Widget: Enquiry Form — reservation, waitlist or contact request.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Enquiry Form widget.
 */
class Enquiry_Form extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'enquiry-form';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-enquiry-form';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Enquiry Form', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-form-horizontal';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'form', 'contact', 'reservation', 'reserve', 'waitlist', 'enquiry', 'quote' );
	}

	/**
	 * Field labels that can be changed.
	 *
	 * @return array<string, string>
	 */
	private static function labels() {
		return array(
			'name'     => esc_html__( 'Name', 'storebox-core' ),
			'email'    => esc_html__( 'Email', 'storebox-core' ),
			'phone'    => esc_html__( 'Phone', 'storebox-core' ),
			'date'     => esc_html__( 'Move-in date', 'storebox-core' ),
			'location' => esc_html__( 'Preferred location', 'storebox-core' ),
			'message'  => esc_html__( 'Message', 'storebox-core' ),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_form', array( 'label' => esc_html__( 'Form', 'storebox-core' ) ) );

		$this->add_design_control();

		$this->add_control(
			'type',
			array(
				'label'       => esc_html__( 'Request type', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'contact',
				'options'     => array(
					'contact'     => esc_html__( 'Contact', 'storebox-core' ),
					'reservation' => esc_html__( 'Reservation', 'storebox-core' ),
					'waitlist'    => esc_html__( 'Waitlist', 'storebox-core' ),
					'auto'        => esc_html__( 'Automatic: reservation, or waitlist when the unit is full', 'storebox-core' ),
				),
				'description' => esc_html__( 'Requests are emailed to the location (or to the address in Storebox → Settings) and kept under Storebox → Enquiries.', 'storebox-core' ),
			)
		);

		$this->add_post_select( 'unit', esc_html__( 'Unit', 'storebox-core' ), 'sb_unit', esc_html__( 'Current unit (on unit pages)', 'storebox-core' ) );
		$this->update_control( 'unit', array( 'condition' => array( 'type!' => 'contact' ) ) );

		$this->add_post_select( 'location', esc_html__( 'Location', 'storebox-core' ), 'sb_location', esc_html__( 'Automatic', 'storebox-core' ) );

		$this->add_control(
			'form_id',
			array(
				'label'       => esc_html__( 'Form ID', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'reserve',
				'description' => esc_html__( 'Letters, numbers and dashes. Buttons can link to it with #form-id (the unit booking button links to #reserve).', 'storebox-core' ),
			)
		);

		$this->add_control(
			'card',
			array(
				'label'   => esc_html__( 'Card style', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_texts', array( 'label' => esc_html__( 'Texts', 'storebox-core' ) ) );

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
				'label_block' => true,
				'placeholder' => esc_html__( 'Depends on the request type', 'storebox-core' ),
				'condition'   => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'     => esc_html__( 'Title HTML tag', 'storebox-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => static::tag_options( array( 'h2', 'h3', 'h4', 'p' ) ),
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'show_intro',
			array(
				'label'   => esc_html__( 'Intro', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'intro',
			array(
				'label'       => esc_html__( 'Intro text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'placeholder' => esc_html__( 'Depends on the request type', 'storebox-core' ),
				'condition'   => array( 'show_intro' => 'yes' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Button text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Depends on the request type', 'storebox-core' ),
			)
		);

		$this->add_control(
			'success_text',
			array(
				'label'       => esc_html__( 'Thank-you message', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'Thanks — we will be in touch shortly.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'show_note',
			array(
				'label'   => esc_html__( 'Privacy note', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'note',
			array(
				'label'       => esc_html__( 'Privacy note text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__( 'We only use your details to answer this enquiry.', 'storebox-core' ),
				'description' => esc_html__( 'Empty uses the note from Storebox → Settings.', 'storebox-core' ),
				'condition'   => array( 'show_note' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_fields', array( 'label' => esc_html__( 'Fields', 'storebox-core' ) ) );

		$this->add_control(
			'show_phone',
			array(
				'label'   => esc_html__( 'Phone field', 'storebox-core' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		foreach ( array(
			'show_date'     => esc_html__( 'Move-in date field', 'storebox-core' ),
			'show_location' => esc_html__( 'Location field', 'storebox-core' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'   => $label,
					'type'    => Controls_Manager::SELECT,
					'default' => 'auto',
					'options' => array(
						'auto' => esc_html__( 'Automatic', 'storebox-core' ),
						'yes'  => esc_html__( 'Show', 'storebox-core' ),
						'no'   => esc_html__( 'Hide', 'storebox-core' ),
					),
				)
			);
		}

		$this->add_heading( 'labels_heading', esc_html__( 'Labels', 'storebox-core' ) );

		foreach ( self::labels() as $key => $label ) {
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
			'section_style_form',
			array(
				'label' => esc_html__( 'Form', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_box_style( 'form', '.sb-enquiry' );
		$this->add_text_style( 'title_text', esc_html__( 'Title', 'storebox-core' ), '.sb-form__title' );
		$this->add_text_style( 'intro_text', esc_html__( 'Intro', 'storebox-core' ), '.sb-form__intro' );
		$this->add_gap_control( 'fields_gap', '.sb-form__grid', esc_html__( 'Space between fields', 'storebox-core' ) );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_fields',
			array(
				'label' => esc_html__( 'Fields', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_text_style( 'label_text', esc_html__( 'Labels', 'storebox-core' ), '.sb-field label' );
		$this->add_heading( 'input_heading', esc_html__( 'Inputs', 'storebox-core' ) );
		$this->add_text_style( 'input_text', '', '.sb-field input, {{WRAPPER}} .sb-field select, {{WRAPPER}} .sb-field textarea' );
		$this->add_box_style( 'input', '.sb-field input, {{WRAPPER}} .sb-field select, {{WRAPPER}} .sb-field textarea', array( 'background', 'border', 'radius', 'padding' ) );

		$this->add_control(
			'input_focus',
			array(
				'label'     => esc_html__( 'Focus border color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .sb-field input:focus, {{WRAPPER}} .sb-field select:focus, {{WRAPPER}} .sb-field textarea:focus' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_button',
			array(
				'label' => esc_html__( 'Button and messages', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_button_style( 'button', '.sb-form__submit' );
		$this->add_text_style( 'note_text', esc_html__( 'Privacy note', 'storebox-core' ), '.sb-form__note' );
		$this->add_text_style( 'ok_text', esc_html__( 'Thank-you message', 'storebox-core' ), '.sb-form__ok' );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$labels = array();

		foreach ( array_keys( self::labels() ) as $key ) {
			if ( ! empty( $s[ 'label_' . $key ] ) ) {
				$labels[ $key ] = $s[ 'label_' . $key ];
			}
		}

		$tri = static function ( $value ) {
			return 'auto' === $value || '' === $value || null === $value ? 'auto' : 'yes' === $value;
		};

		$type = isset( $s['type'] ) ? $s['type'] : 'contact';
		$unit = 0;
		if ( 'contact' !== $type && ! empty( $s['unit'] ) ) {
			$unit = absint( $s['unit'] );
		}

		$this->render_component(
			array(
				'style'         => isset( $s['design'] ) ? $s['design'] : '',
				'type'          => $type,
				'unit'          => $unit,
				'location'      => ! empty( $s['location'] ) ? absint( $s['location'] ) : 0,
				'form_id'       => isset( $s['form_id'] ) ? $s['form_id'] : '',
				'title'         => $this->on( $s, 'show_title' ) ? $this->text_or_default( $s, 'title' ) : '',
				'title_tag'     => isset( $s['title_tag'] ) ? $s['title_tag'] : 'h3',
				'intro'         => $this->on( $s, 'show_intro' ) ? $this->text_or_default( $s, 'intro' ) : '',
				'button_text'   => $this->text_or_default( $s, 'button_text' ),
				'success_text'  => $this->text_or_default( $s, 'success_text' ),
				'note'          => $this->on( $s, 'show_note' ) ? $this->text_or_default( $s, 'note' ) : '',
				'show_phone'    => $this->on( $s, 'show_phone' ),
				'show_date'     => $tri( isset( $s['show_date'] ) ? $s['show_date'] : 'auto' ),
				'show_location' => $tri( isset( $s['show_location'] ) ? $s['show_location'] : 'auto' ),
				'labels'        => $labels,
				'card'          => $this->on( $s, 'card' ),
			)
		);
	}
}
