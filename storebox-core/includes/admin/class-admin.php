<?php
/**
 * Admin list columns, sorting and assets.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin screens.
 */
class Admin {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );

		add_filter( 'manage_sb_unit_posts_columns', array( __CLASS__, 'unit_columns' ) );
		add_action( 'manage_sb_unit_posts_custom_column', array( __CLASS__, 'unit_column' ), 10, 2 );
		add_filter( 'manage_edit-sb_unit_sortable_columns', array( __CLASS__, 'unit_sortable' ) );

		add_filter( 'manage_sb_location_posts_columns', array( __CLASS__, 'location_columns' ) );
		add_action( 'manage_sb_location_posts_custom_column', array( __CLASS__, 'location_column' ), 10, 2 );

		add_action( 'pre_get_posts', array( __CLASS__, 'sort_query' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( STOREBOX_CORE_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
	}

	/**
	 * Admin styles and the gallery picker script on Storebox screens.
	 *
	 * @param string $hook Admin page hook.
	 */
	public static function assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$storebox_screen = in_array( $screen->post_type, array( 'sb_unit', 'sb_location', 'sb_enquiry' ), true ) || false !== strpos( (string) $hook, 'storebox' );
		if ( ! $storebox_screen ) {
			return;
		}

		wp_enqueue_style( 'storebox-core-admin', STOREBOX_CORE_URL . 'assets/css/admin.css', array(), STOREBOX_CORE_VERSION );

		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && in_array( $screen->post_type, array( 'sb_unit', 'sb_location' ), true ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'storebox-core-admin', STOREBOX_CORE_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), STOREBOX_CORE_VERSION, true );
			wp_localize_script(
				'storebox-core-admin',
				'storeboxCoreAdmin',
				array(
					'title'  => __( 'Choose photos', 'storebox-core' ),
					'button' => __( 'Add to gallery', 'storebox-core' ),
					'remove' => __( 'Remove photo', 'storebox-core' ),
				)
			);
		}
	}

	/**
	 * Unit list columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function unit_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['sb_thumb'] = '<span class="screen-reader-text">' . esc_html__( 'Photo', 'storebox-core' ) . '</span>';
				$new[ $key ]     = $label;
				$new['sb_area']  = __( 'Area', 'storebox-core' );
				$new['sb_price'] = __( 'Price', 'storebox-core' );
				$new['sb_avail'] = __( 'Availability', 'storebox-core' );
				$new['sb_loc']   = __( 'Location', 'storebox-core' );
				continue;
			}
			$new[ $key ] = $label;
		}

		return $new;
	}

	/**
	 * Unit column content.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public static function unit_column( $column, $post_id ) {
		$unit = storebox_core_get_unit( $post_id );
		if ( ! $unit ) {
			return;
		}

		switch ( $column ) {
			case 'sb_thumb':
				echo $unit['image_id'] ? wp_get_attachment_image( $unit['image_id'], array( 60, 45 ) ) : '<span class="sb-admin-thumb-empty"></span>';
				break;
			case 'sb_area':
				echo esc_html( $unit['area'] ? $unit['area_label'] : '—' );
				break;
			case 'sb_price':
				echo esc_html( $unit['price'] ? $unit['price_label'] : '—' );
				break;
			case 'sb_avail':
				printf( '<span class="sb-admin-status sb-admin-status--%1$s">%2$s</span>', esc_attr( $unit['status'] ), esc_html( $unit['status_label'] ) );
				break;
			case 'sb_loc':
				echo $unit['location'] ? esc_html( $unit['location']['name'] ) : '—';
				break;
		}
	}

	/**
	 * Sortable unit columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function unit_sortable( $columns ) {
		$columns['sb_area']  = 'sb_area';
		$columns['sb_price'] = 'sb_price';
		$columns['sb_avail'] = 'sb_avail';

		return $columns;
	}

	/**
	 * Applies column sorting.
	 *
	 * @param \WP_Query $query Query.
	 */
	public static function sort_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'sb_unit' !== $query->get( 'post_type' ) ) {
			return;
		}

		$map = array(
			'sb_area'  => '_sb_area',
			'sb_price' => '_sb_price',
			'sb_avail' => '_sb_available',
		);

		$orderby = $query->get( 'orderby' );
		if ( is_string( $orderby ) && isset( $map[ $orderby ] ) ) {
			$query->set( 'meta_key', $map[ $orderby ] );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/**
	 * Location list columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function location_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['sb_thumb']   = '<span class="screen-reader-text">' . esc_html__( 'Photo', 'storebox-core' ) . '</span>';
				$new[ $key ]       = $label;
				$new['sb_address'] = __( 'Address', 'storebox-core' );
				$new['sb_access']  = __( 'Access', 'storebox-core' );
				$new['sb_units']   = __( 'Units', 'storebox-core' );
				$new['sb_phone']   = __( 'Phone', 'storebox-core' );
				continue;
			}
			$new[ $key ] = $label;
		}

		return $new;
	}

	/**
	 * Location column content.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public static function location_column( $column, $post_id ) {
		$location = storebox_core_get_location( $post_id );
		if ( ! $location ) {
			return;
		}

		switch ( $column ) {
			case 'sb_thumb':
				echo $location['image_id'] ? wp_get_attachment_image( $location['image_id'], array( 60, 45 ) ) : '<span class="sb-admin-thumb-empty"></span>';
				break;
			case 'sb_address':
				echo esc_html( $location['address_inline'] ? $location['address_inline'] : '—' );
				break;
			case 'sb_access':
				echo esc_html( $location['access'] ? $location['access'] : '—' );
				break;
			case 'sb_units':
				echo esc_html( $location['units_label'] ? $location['units_label'] : '—' );
				break;
			case 'sb_phone':
				echo esc_html( $location['phone'] ? $location['phone'] : '—' );
				break;
		}
	}

	/**
	 * Links on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift(
			$links,
			sprintf( '<a href="%1$s">%2$s</a>', esc_url( admin_url( 'edit.php?post_type=sb_unit&page=storebox-settings' ) ), esc_html__( 'Settings', 'storebox-core' ) ),
			sprintf( '<a href="%1$s">%2$s</a>', esc_url( admin_url( 'themes.php?page=storebox-demo-import' ) ), esc_html__( 'Demo import', 'storebox-core' ) )
		);

		return $links;
	}

	/**
	 * Title placeholders.
	 *
	 * @param string   $text Placeholder.
	 * @param \WP_Post $post Post.
	 * @return string
	 */
	public static function title_placeholder( $text, $post ) {
		if ( 'sb_unit' === $post->post_type ) {
			return __( 'Unit name, e.g. Small or Locker', 'storebox-core' );
		}
		if ( 'sb_location' === $post->post_type ) {
			return __( 'Location name, e.g. Amsterdam Noord', 'storebox-core' );
		}

		return $text;
	}
}
