<?php
/**
 * Dynamic tag: Location Field.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Location field (text).
 */
class Location_Field extends Tag {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-location-field';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Location field', 'storebox-core' );
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
		return array( Tags_Module::TEXT_CATEGORY );
	}

	/**
	 * Fields.
	 *
	 * @return array<string, string>
	 */
	public static function fields() {
		return array(
			'name'           => esc_html__( 'Name', 'storebox-core' ),
			'area_name'      => esc_html__( 'Area', 'storebox-core' ),
			'address_inline' => esc_html__( 'Address (one line)', 'storebox-core' ),
			'address_short'  => esc_html__( 'Street and area', 'storebox-core' ),
			'street'         => esc_html__( 'Street', 'storebox-core' ),
			'postcode'       => esc_html__( 'Postcode', 'storebox-core' ),
			'city'           => esc_html__( 'City', 'storebox-core' ),
			'phone'          => esc_html__( 'Phone', 'storebox-core' ),
			'email'          => esc_html__( 'Email', 'storebox-core' ),
			'access'         => esc_html__( 'Access hours', 'storebox-core' ),
			'units_label'    => esc_html__( 'Units (e.g. 214 units · 12 free)', 'storebox-core' ),
			'free_label'     => esc_html__( 'Units free', 'storebox-core' ),
			'excerpt'        => esc_html__( 'Excerpt', 'storebox-core' ),
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
				'default' => 'address_inline',
				'options' => self::fields(),
			)
		);
		add_id_control( $this, esc_html__( 'Location ID', 'storebox-core' ) );
	}

	/**
	 * Output.
	 */
	public function render(): void {
		$location = storebox_core_get_location( resolve_post( $this->get_settings( 'post_id' ), 'sb_location' ) );
		if ( ! $location ) {
			return;
		}

		$field = (string) $this->get_settings( 'field' );
		$value = isset( $location[ $field ] ) && is_scalar( $location[ $field ] ) ? (string) $location[ $field ] : '';

		if ( 'email' === $field ) {
			$value = antispambot( $value );
		}

		echo esc_html( $value );
	}
}
