<?php
/**
 * Uninstall: removes the plugin's settings.
 *
 * Units, locations and enquiries are content and are kept, so reinstalling
 * the plugin brings them back. To delete them as well, add
 * define( 'STOREBOX_CORE_REMOVE_ALL_DATA', true ); to wp-config.php before
 * deleting the plugin.
 *
 * @package Storebox_Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

foreach ( array( 'storebox_core_settings', 'storebox_core_import_state', 'storebox_core_import_backup', 'storebox_core_imported' ) as $storebox_core_option ) {
	delete_option( $storebox_core_option );
}

if ( defined( 'STOREBOX_CORE_REMOVE_ALL_DATA' ) && STOREBOX_CORE_REMOVE_ALL_DATA ) {
	$storebox_core_posts = get_posts(
		array(
			'post_type'      => array( 'sb_unit', 'sb_location', 'sb_enquiry' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $storebox_core_posts as $storebox_core_post ) {
		wp_delete_post( $storebox_core_post, true );
	}

	foreach ( array( 'sb_unit_type', 'sb_unit_size', 'sb_unit_feature' ) as $storebox_core_taxonomy ) {
		// The plugin is not loaded while uninstalling: register the taxonomy so its terms can be deleted.
		if ( ! taxonomy_exists( $storebox_core_taxonomy ) ) {
			register_taxonomy( $storebox_core_taxonomy, 'sb_unit' );
		}

		$storebox_core_terms = get_terms(
			array(
				'taxonomy'   => $storebox_core_taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);

		foreach ( is_wp_error( $storebox_core_terms ) ? array() : $storebox_core_terms as $storebox_core_term ) {
			wp_delete_term( $storebox_core_term, $storebox_core_taxonomy );
		}
	}
}
