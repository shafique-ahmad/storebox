<?php
/**
 * Dynamic tag: Unit Field.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Unit field (text).
 */
class Unit_Field extends Tag {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-unit-field';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Unit field', 'storebox-core' );
	}

	/**
	 * Group.
	 *
	 * @return string
	 */
	public function get_group(): string {
		return 'storebox';
	}

	/**
	 * Categories.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( Tags_Module::TEXT_CATEGORY, Tags_Module::NUMBER_CATEGORY );
	}

	/**
	 * Fields.
	 *
	 * @return array<string, string>
	 */
	public static function fields() {
		return array(
			'display_title'    => esc_html__( 'Name and size (Small — 5 m²)', 'storebox-core' ),
			'short_title'      => esc_html__( 'Short name (Small 5 m²)', 'storebox-core' ),
			'name'             => esc_html__( 'Name', 'storebox-core' ),
			'area_label'       => esc_html__( 'Floor area (m²)', 'storebox-core' ),
			'area_ft_label'    => esc_html__( 'Floor area (ft²)', 'storebox-core' ),
			'dimensions_label' => esc_html__( 'Dimensions', 'storebox-core' ),
			'ceiling_label'    => esc_html__( 'Ceiling height', 'storebox-core' ),
			'floor'            => esc_html__( 'Floor', 'storebox-core' ),
			'fits'             => esc_html__( 'Typically fits', 'storebox-core' ),
			'summary'          => esc_html__( 'Summary (what fits, where, availability)', 'storebox-core' ),
			'price_label'      => esc_html__( 'Monthly price', 'storebox-core' ),
			'weekly_label'     => esc_html__( 'Weekly price', 'storebox-core' ),
			'price'            => esc_html__( 'Monthly price (number)', 'storebox-core' ),
			'status_label'     => esc_html__( 'Availability', 'storebox-core' ),
			'available'        => esc_html__( 'Units free (number)', 'storebox-core' ),
			'type_label'       => esc_html__( 'Type', 'storebox-core' ),
			'location_name'    => esc_html__( 'Location name', 'storebox-core' ),
			'location_address' => esc_html__( 'Location address', 'storebox-core' ),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->add_control(
			'field',
			array(
				'label'   => esc_html__( 'Field', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'display_title',
				'options' => self::fields(),
			)
		);
		add_id_control( $this, esc_html__( 'Unit ID', 'storebox-core' ) );
	}

	/**
	 * Output.
	 */
	public function render(): void {
		$unit = storebox_core_get_unit( resolve_post( $this->get_settings( 'post_id' ), 'sb_unit' ) );
		if ( ! $unit ) {
			return;
		}

		$field = (string) $this->get_settings( 'field' );

		switch ( $field ) {
			case 'location_name':
				$value = $unit['location'] ? $unit['location']['name'] : '';
				break;
			case 'location_address':
				$value = $unit['location'] ? $unit['location']['address_inline'] : '';
				break;
			default:
				$value = isset( $unit[ $field ] ) && is_scalar( $unit[ $field ] ) ? $unit[ $field ] : '';
		}

		echo esc_html( (string) $value );
	}
}
