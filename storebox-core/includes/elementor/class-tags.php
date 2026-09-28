<?php
/**
 * Elementor dynamic tags: unit and location fields, links, phone, reading time.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Finds the post a tag refers to: the ID entered, else the current post.
 *
 * @param mixed  $id        Entered ID.
 * @param string $post_type Post type.
 * @return int
 */
function resolve_post( $id, $post_type ) {
	$id = absint( $id );
	if ( $id && get_post_type( $id ) === $post_type ) {
		return $id;
	}

	$current = get_the_ID();

	return $current && get_post_type( $current ) === $post_type ? (int) $current : 0;
}

/**
 * Adds the optional "ID" control shared by the unit and location tags.
 *
 * @param Tag|Data_Tag $tag   Tag.
 * @param string       $label Label.
 */
function add_id_control( $tag, $label ) {
	$tag->add_control(
		'post_id',
		array(
			'label'       => $label,
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'description' => esc_html__( 'Empty uses the current page.', 'storebox-core' ),
		)
	);
}

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

/**
 * Main phone number (text): Storebox → Settings, else the Customizer header phone.
 */
class Phone extends Tag {

	/**
	 * The number.
	 *
	 * @return string
	 */
	public static function number() {
		/** This filter is documented in templates/unit-booking.php */
		return (string) apply_filters( 'storebox_core/default_phone', storebox_core_setting( 'help_phone' ) );
	}

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-phone';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Main phone number', 'storebox-core' );
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
	 * Output.
	 */
	public function render(): void {
		echo esc_html( self::number() );
	}
}

/**
 * Reading time of the current post (text).
 */
class Read_Time extends Tag {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-read-time';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Reading time', 'storebox-core' );
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
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->add_control(
			'format',
			array(
				'label'       => esc_html__( 'Format', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( '%d min read', 'storebox-core' ),
				'description' => esc_html__( '%d is the number of minutes.', 'storebox-core' ),
			)
		);
	}

	/**
	 * Output.
	 */
	public function render(): void {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$minutes = storebox_core_read_time( $post_id );
		$format  = (string) $this->get_settings( 'format' );
		if ( '' === $format || false === strpos( $format, '%d' ) ) {
			/* translators: %d: minutes to read. */
			$format = _n( '%d min read', '%d min read', $minutes, 'storebox-core' );
		}

		echo esc_html( str_replace( '%d', number_format_i18n( $minutes ), $format ) );
	}
}
