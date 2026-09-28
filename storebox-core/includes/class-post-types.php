<?php
/**
 * Units and Locations post types, their taxonomies and fields.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Registers content types.
 */
class Post_Types {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_meta' ), 6 );
	}

	/**
	 * Menu icon: the Storebox mark (three bars).
	 *
	 * @return string
	 */
	public static function menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="3" y="4" width="11" height="3" rx="1.2" fill="black"/><rect x="3" y="8.5" width="14" height="3" rx="1.2" fill="black"/><rect x="3" y="13" width="12.5" height="3" rx="1.2" fill="black"/></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Admin menu icon data URI.
	}

	/**
	 * Registers post types and taxonomies.
	 */
	public static function register() {
		$unit_slug     = apply_filters( 'storebox_core/unit_slug', 'units' );
		$location_slug = apply_filters( 'storebox_core/location_slug', 'locations' );

		register_post_type(
			'sb_unit',
			array(
				'labels'          => array(
					'name'                  => _x( 'Units', 'post type general name', 'storebox-core' ),
					'singular_name'         => _x( 'Unit', 'post type singular name', 'storebox-core' ),
					'menu_name'             => _x( 'Storebox', 'admin menu', 'storebox-core' ),
					'all_items'             => __( 'Units', 'storebox-core' ),
					'add_new'               => __( 'Add unit', 'storebox-core' ),
					'add_new_item'          => __( 'Add new unit', 'storebox-core' ),
					'edit_item'             => __( 'Edit unit', 'storebox-core' ),
					'new_item'              => __( 'New unit', 'storebox-core' ),
					'view_item'             => __( 'View unit', 'storebox-core' ),
					'view_items'            => __( 'View units', 'storebox-core' ),
					'search_items'          => __( 'Search units', 'storebox-core' ),
					'not_found'             => __( 'No units found.', 'storebox-core' ),
					'not_found_in_trash'    => __( 'No units found in Trash.', 'storebox-core' ),
					'featured_image'        => __( 'Main photo', 'storebox-core' ),
					'set_featured_image'    => __( 'Set main photo', 'storebox-core' ),
					'remove_featured_image' => __( 'Remove main photo', 'storebox-core' ),
					'use_featured_image'    => __( 'Use as main photo', 'storebox-core' ),
					'item_updated'          => __( 'Unit updated.', 'storebox-core' ),
					'item_published'        => __( 'Unit published.', 'storebox-core' ),
				),
				'description'     => __( 'Storage units with size, price and availability.', 'storebox-core' ),
				'public'          => true,
				'show_in_rest'    => true,
				'menu_position'   => 26,
				'menu_icon'       => self::menu_icon(),
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
				'has_archive'     => false,
				'rewrite'         => array(
					'slug'       => $unit_slug,
					'with_front' => false,
				),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);

		register_post_type(
			'sb_location',
			array(
				'labels'          => array(
					'name'                  => _x( 'Locations', 'post type general name', 'storebox-core' ),
					'singular_name'         => _x( 'Location', 'post type singular name', 'storebox-core' ),
					'all_items'             => __( 'Locations', 'storebox-core' ),
					'add_new'               => __( 'Add location', 'storebox-core' ),
					'add_new_item'          => __( 'Add new location', 'storebox-core' ),
					'edit_item'             => __( 'Edit location', 'storebox-core' ),
					'new_item'              => __( 'New location', 'storebox-core' ),
					'view_item'             => __( 'View location', 'storebox-core' ),
					'view_items'            => __( 'View locations', 'storebox-core' ),
					'search_items'          => __( 'Search locations', 'storebox-core' ),
					'not_found'             => __( 'No locations found.', 'storebox-core' ),
					'not_found_in_trash'    => __( 'No locations found in Trash.', 'storebox-core' ),
					'featured_image'        => __( 'Main photo', 'storebox-core' ),
					'set_featured_image'    => __( 'Set main photo', 'storebox-core' ),
					'remove_featured_image' => __( 'Remove main photo', 'storebox-core' ),
					'use_featured_image'    => __( 'Use as main photo', 'storebox-core' ),
				),
				'description'     => __( 'Storage facilities with address, hours and map position.', 'storebox-core' ),
				'public'          => true,
				'show_in_rest'    => true,
				'show_in_menu'    => 'edit.php?post_type=sb_unit',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
				'has_archive'     => false,
				'rewrite'         => array(
					'slug'       => $location_slug,
					'with_front' => false,
				),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
			)
		);

		$taxonomy_defaults = array(
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
			'rewrite'            => false,
		);

		register_taxonomy(
			'sb_unit_type',
			'sb_unit',
			array_merge(
				$taxonomy_defaults,
				array(
					'hierarchical' => true,
					'labels'       => array(
						'name'          => _x( 'Unit types', 'taxonomy general name', 'storebox-core' ),
						'singular_name' => _x( 'Unit type', 'taxonomy singular name', 'storebox-core' ),
						'menu_name'     => __( 'Unit types', 'storebox-core' ),
						'all_items'     => __( 'All unit types', 'storebox-core' ),
						'edit_item'     => __( 'Edit unit type', 'storebox-core' ),
						'add_new_item'  => __( 'Add new unit type', 'storebox-core' ),
						'search_items'  => __( 'Search unit types', 'storebox-core' ),
						'not_found'     => __( 'No unit types found.', 'storebox-core' ),
					),
				)
			)
		);

		register_taxonomy(
			'sb_unit_size',
			'sb_unit',
			array_merge(
				$taxonomy_defaults,
				array(
					'hierarchical' => true,
					'description'  => __( 'Size groups used by the unit filter. The description is shown as the range, e.g. "up to 5 m²".', 'storebox-core' ),
					'labels'       => array(
						'name'          => _x( 'Size groups', 'taxonomy general name', 'storebox-core' ),
						'singular_name' => _x( 'Size group', 'taxonomy singular name', 'storebox-core' ),
						'menu_name'     => __( 'Size groups', 'storebox-core' ),
						'all_items'     => __( 'All size groups', 'storebox-core' ),
						'edit_item'     => __( 'Edit size group', 'storebox-core' ),
						'add_new_item'  => __( 'Add new size group', 'storebox-core' ),
						'search_items'  => __( 'Search size groups', 'storebox-core' ),
						'not_found'     => __( 'No size groups found.', 'storebox-core' ),
					),
				)
			)
		);

		register_taxonomy(
			'sb_unit_feature',
			'sb_unit',
			array_merge(
				$taxonomy_defaults,
				array(
					'hierarchical'      => true,
					'show_admin_column' => false,
					'labels'            => array(
						'name'          => _x( 'Features', 'taxonomy general name', 'storebox-core' ),
						'singular_name' => _x( 'Feature', 'taxonomy singular name', 'storebox-core' ),
						'menu_name'     => __( 'Features', 'storebox-core' ),
						'all_items'     => __( 'All features', 'storebox-core' ),
						'edit_item'     => __( 'Edit feature', 'storebox-core' ),
						'add_new_item'  => __( 'Add new feature', 'storebox-core' ),
						'search_items'  => __( 'Search features', 'storebox-core' ),
						'not_found'     => __( 'No features found.', 'storebox-core' ),
					),
				)
			)
		);
	}

	/**
	 * Field definitions: meta key => array( type, sanitize callback ).
	 *
	 * @return array<string, array<string, array{0: string, 1: callable}>>
	 */
	public static function fields() {
		return array(
			'sb_unit'     => array(
				'_sb_area'       => array( 'number', array( __CLASS__, 'sanitize_float' ) ),
				'_sb_width'      => array( 'number', array( __CLASS__, 'sanitize_float' ) ),
				'_sb_depth'      => array( 'number', array( __CLASS__, 'sanitize_float' ) ),
				'_sb_ceiling'    => array( 'number', array( __CLASS__, 'sanitize_float' ) ),
				'_sb_floor'      => array( 'string', 'sanitize_text_field' ),
				'_sb_price'      => array( 'number', array( __CLASS__, 'sanitize_float' ) ),
				'_sb_available'  => array( 'integer', 'absint' ),
				'_sb_location'   => array( 'integer', 'absint' ),
				'_sb_fits'       => array( 'string', 'sanitize_text_field' ),
				'_sb_highlights' => array( 'string', 'sanitize_textarea_field' ),
				'_sb_gallery'    => array( 'string', array( __CLASS__, 'sanitize_id_list' ) ),
				'_sb_featured'   => array( 'string', array( __CLASS__, 'sanitize_flag' ) ),
			),
			'sb_location' => array(
				'_sb_area_name'   => array( 'string', 'sanitize_text_field' ),
				'_sb_street'      => array( 'string', 'sanitize_text_field' ),
				'_sb_postcode'    => array( 'string', 'sanitize_text_field' ),
				'_sb_city'        => array( 'string', 'sanitize_text_field' ),
				'_sb_phone'       => array( 'string', 'sanitize_text_field' ),
				'_sb_email'       => array( 'string', 'sanitize_email' ),
				'_sb_access'      => array( 'string', 'sanitize_text_field' ),
				'_sb_units_total' => array( 'integer', 'absint' ),
				'_sb_units_free'  => array( 'integer', 'absint' ),
				'_sb_tags'        => array( 'string', 'sanitize_textarea_field' ),
				'_sb_lat'         => array( 'string', array( __CLASS__, 'sanitize_coordinate' ) ),
				'_sb_lng'         => array( 'string', array( __CLASS__, 'sanitize_coordinate' ) ),
				'_sb_gallery'     => array( 'string', array( __CLASS__, 'sanitize_id_list' ) ),
			),
		);
	}

	/**
	 * Registers the fields as post meta (protected by the leading underscore).
	 */
	public static function register_meta() {
		foreach ( self::fields() as $post_type => $fields ) {
			foreach ( $fields as $key => $field ) {
				register_post_meta(
					$post_type,
					$key,
					array(
						'type'              => $field[0],
						'single'            => true,
						'show_in_rest'      => true,
						'sanitize_callback' => $field[1],
						'auth_callback'     => array( __CLASS__, 'can_edit' ),
					)
				);
			}
		}

		register_post_meta(
			'sb_location',
			'_sb_hours',
			array(
				'type'              => 'object',
				'single'            => true,
				'sanitize_callback' => array( __CLASS__, 'sanitize_hours' ),
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'additionalProperties' => array( 'type' => 'string' ),
					),
				),
			)
		);

		register_post_meta(
			'post',
			'_storebox_read_time',
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array( __CLASS__, 'can_edit' ),
			)
		);

		foreach ( array( 'sb_unit_type', 'sb_unit_size', 'sb_unit_feature' ) as $taxonomy ) {
			register_term_meta(
				$taxonomy,
				'sb_order',
				array(
					'type'              => 'integer',
					'single'            => true,
					'sanitize_callback' => 'intval',
					'show_in_rest'      => true,
				)
			);
		}

		register_term_meta(
			'sb_unit_feature',
			'sb_card',
			array(
				'type'              => 'boolean',
				'single'            => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Meta auth callback.
	 *
	 * @param bool   $allowed  Whether allowed.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public static function can_edit( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Sanitizes a decimal number (accepts a comma as decimal separator).
	 *
	 * @param mixed $value Raw value.
	 * @return float|string Empty string for empty input.
	 */
	public static function sanitize_float( $value ) {
		$value = trim( str_replace( ',', '.', (string) $value ) );

		return '' === $value ? '' : round( abs( (float) $value ), 2 );
	}

	/**
	 * Sanitizes a latitude/longitude value.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_coordinate( $value ) {
		$value = trim( str_replace( ',', '.', (string) $value ) );

		if ( '' === $value || ! is_numeric( $value ) ) {
			return '';
		}

		$number = (float) $value;

		return ( $number >= -180 && $number <= 180 ) ? (string) round( $number, 7 ) : '';
	}

	/**
	 * Sanitizes a comma-separated list of IDs.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_id_list( $value ) {
		return implode( ',', storebox_core_parse_ids( $value ) );
	}

	/**
	 * Sanitizes a checkbox flag.
	 *
	 * @param mixed $value Raw value.
	 * @return string '1' or ''.
	 */
	public static function sanitize_flag( $value ) {
		return ! empty( $value ) && 'false' !== $value ? '1' : '';
	}

	/**
	 * Sanitizes the 7-day office hours (keys 0 = Sunday … 6 = Saturday).
	 *
	 * @param mixed $value Raw value.
	 * @return array<int, string>
	 */
	public static function sanitize_hours( $value ) {
		$clean = array();
		$value = is_array( $value ) ? $value : array();

		for ( $day = 0; $day < 7; $day++ ) {
			$clean[ $day ] = isset( $value[ $day ] ) ? sanitize_text_field( (string) $value[ $day ] ) : '';
		}

		return $clean;
	}
}
