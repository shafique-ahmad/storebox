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
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'menu_section_class' ), 10, 2 );
		add_filter( 'nav_menu_link_attributes', array( __CLASS__, 'menu_section_link' ), 20, 2 );
	}

	/**
	 * Page that lists the current single item: the Units page for a unit, the
	 * Locations page for a location and the posts page for a blog post.
	 *
	 * @return int Page ID, or 0.
	 */
	private static function section_page() {
		static $page_id = null;

		if ( null === $page_id ) {
			$page_id = 0;
			if ( is_singular( 'sb_unit' ) ) {
				$page_id = absint( storebox_core_setting( 'units_page' ) );
			} elseif ( is_singular( 'sb_location' ) ) {
				$page_id = absint( storebox_core_setting( 'locations_page' ) );
			} elseif ( is_singular( 'post' ) ) {
				$page_id = absint( get_option( 'page_for_posts' ) );
			}
		}

		return $page_id;
	}

	/**
	 * Whether a menu item links to the page of the current section.
	 *
	 * @param \WP_Post $item Menu item.
	 * @return bool
	 */
	private static function is_section_item( $item ) {
		$page_id = self::section_page();

		return $page_id && isset( $item->object, $item->object_id ) && 'page' === $item->object && (int) $item->object_id === $page_id;
	}

	/**
	 * Marks the menu item of the current section ("Units" on a unit page), so
	 * menus highlight where the visitor is.
	 *
	 * @param string[] $classes Item classes.
	 * @param \WP_Post $item    Menu item.
	 * @return string[]
	 */
	public static function menu_section_class( $classes, $item ) {
		if ( self::is_section_item( $item ) ) {
			$classes[] = 'current-section';
		}

		return $classes;
	}

	/**
	 * Section item link: aria-current="true", and Elementor Pro's active class
	 * when the menu is printed by its Nav Menu widget.
	 *
	 * @param array    $atts Link attributes.
	 * @param \WP_Post $item Menu item.
	 * @return array
	 */
	public static function menu_section_link( $atts, $item ) {
		if ( ! self::is_section_item( $item ) ) {
			return $atts;
		}

		if ( empty( $atts['aria-current'] ) ) {
			$atts['aria-current'] = 'true';
		}

		if ( ! empty( $atts['class'] ) && preg_match( '/(^|\s)elementor-item(\s|$)/', $atts['class'] ) && false === strpos( $atts['class'], 'elementor-item-active' ) ) {
			$atts['class'] .= ' elementor-item-active';
		}

		return $atts;
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
