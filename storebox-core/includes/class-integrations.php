<?php
/**
 * Small integrations with the Storebox theme and WordPress output.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Theme and front-end integrations.
 */
class Integrations {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'storebox_breadcrumb_items', array( __CLASS__, 'breadcrumbs' ) );
		add_filter( 'storebox_404_secondary_link', array( __CLASS__, 'not_found_link' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'document_title_parts', array( __CLASS__, 'document_title' ) );
		add_filter( 'display_post_states', array( __CLASS__, 'post_states' ), 10, 2 );
	}

	/**
	 * Puts the Units / Locations page in front of single units and locations.
	 *
	 * @param array $items Breadcrumb items.
	 * @return array
	 */
	public static function breadcrumbs( $items ) {
		if ( ! is_singular( array( 'sb_unit', 'sb_location' ) ) || count( $items ) < 2 ) {
			return $items;
		}

		$is_unit = is_singular( 'sb_unit' );
		$page_id = absint( storebox_core_setting( $is_unit ? 'units_page' : 'locations_page' ) );
		$last    = array_pop( $items );

		if ( $is_unit ) {
			$unit = storebox_core_get_unit( get_queried_object_id() );
			if ( $unit ) {
				$last['label'] = $unit['short_title'];
			}
		}

		if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
			$items[] = array(
				'label' => get_the_title( $page_id ),
				'url'   => get_permalink( $page_id ),
			);
		}

		$items[] = $last;

		return $items;
	}

	/**
	 * Second 404 button: the Units page.
	 *
	 * @param array $link Link data.
	 * @return array
	 */
	public static function not_found_link( $link ) {
		$url = storebox_core_page_url( 'units' );

		if ( $url && empty( $link['url'] ) ) {
			$link = array(
				'text' => __( 'Browse units', 'storebox-core' ),
				'url'  => $url,
			);
		}

		return $link;
	}

	/**
	 * Marks pages using a Storebox Elementor kit so widget colours follow it.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( in_array( 'sb-kit', $classes, true ) || ! did_action( 'elementor/loaded' ) ) {
			return $classes;
		}

		$kit_id   = (int) get_option( 'elementor_active_kit' );
		$settings = $kit_id ? get_post_meta( $kit_id, '_elementor_page_settings', true ) : array();

		if ( is_array( $settings ) && ! empty( $settings['custom_colors'] ) && is_array( $settings['custom_colors'] ) ) {
			foreach ( $settings['custom_colors'] as $color ) {
				if ( isset( $color['_id'] ) && 'sbsurface' === $color['_id'] ) {
					$classes[] = 'sb-kit';
					break;
				}
			}
		}

		return $classes;
	}

	/**
	 * Document title of a unit includes its size ("Small 5 m²").
	 *
	 * @param array $parts Title parts.
	 * @return array
	 */
	public static function document_title( $parts ) {
		if ( is_singular( 'sb_unit' ) ) {
			$unit = storebox_core_get_unit( get_queried_object_id() );
			if ( $unit ) {
				$parts['title'] = $unit['short_title'];
			}
		}

		return $parts;
	}

	/**
	 * Labels the Units and Locations pages in the Pages list.
	 *
	 * @param string[] $states Post states.
	 * @param \WP_Post $post   Post.
	 * @return string[]
	 */
	public static function post_states( $states, $post ) {
		if ( 'page' !== $post->post_type ) {
			return $states;
		}

		if ( absint( storebox_core_setting( 'units_page' ) ) === $post->ID ) {
			$states['storebox_units'] = __( 'Units page', 'storebox-core' );
		}
		if ( absint( storebox_core_setting( 'locations_page' ) ) === $post->ID ) {
			$states['storebox_locations'] = __( 'Locations page', 'storebox-core' );
		}

		return $states;
	}
}
