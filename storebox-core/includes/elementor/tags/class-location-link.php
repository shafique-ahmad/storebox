<?php
/**
 * Dynamic tag: Location Link.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Location link (URL): phone, email, directions or page.
 */
class Location_Link extends Data_Tag {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-location-link';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Location link', 'storebox-core' );
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
		return array( Tags_Module::URL_CATEGORY );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link to', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'phone',
				'options' => array(
					'phone'      => esc_html__( 'Call the location', 'storebox-core' ),
					'email'      => esc_html__( 'Email the location', 'storebox-core' ),
					'directions' => esc_html__( 'Directions (OpenStreetMap)', 'storebox-core' ),
					'page'       => esc_html__( 'Location page', 'storebox-core' ),
					'site_phone' => esc_html__( 'Call the main phone number', 'storebox-core' ),
				),
			)
		);
		add_id_control( $this, esc_html__( 'Location ID', 'storebox-core' ) );
	}

	/**
	 * URL.
	 *
	 * @param array $options Options.
	 * @return string
	 */
	public function get_value( array $options = array() ): string {
		$link = (string) $this->get_settings( 'link' );

		if ( 'site_phone' === $link ) {
			$phone = Phone::number();
			return $phone ? 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) : '';
		}

		$location = storebox_core_get_location( resolve_post( $this->get_settings( 'post_id' ), 'sb_location' ) );
		if ( ! $location ) {
			return '';
		}

		switch ( $link ) {
			case 'email':
				return $location['email'] ? 'mailto:' . antispambot( $location['email'] ) : '';
			case 'directions':
				if ( $location['lat'] || $location['lng'] ) {
					return sprintf( 'https://www.openstreetmap.org/directions?to=%1$s%%2C%2$s', rawurlencode( (string) $location['lat'] ), rawurlencode( (string) $location['lng'] ) );
				}
				return $location['address_inline'] ? 'https://www.openstreetmap.org/search?query=' . rawurlencode( $location['address_inline'] ) : '';
			case 'page':
				return (string) $location['url'];
			default:
				return $location['phone_href'] ? (string) $location['phone_href'] : '';
		}
	}
}
