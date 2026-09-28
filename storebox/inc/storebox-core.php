<?php
/**
 * Integration with the Storebox Core plugin (units, locations, widgets).
 *
 * Every call is guarded, so the theme works on its own.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Storebox widgets follow the theme's design preset unless a widget overrides it.
 *
 * @return string
 */
function storebox_core_default_style() {
	return storebox_preset();
}
add_filter( 'storebox_core/default_style', 'storebox_core_default_style' );

/**
 * Supplies the header phone number as the default "help" number for
 * Storebox widgets.
 *
 * @param string $phone Phone from the plugin settings.
 * @return string
 */
function storebox_core_default_phone( $phone ) {
	$theme_phone = storebox_get_mod( 'header_phone' );

	return $phone ? $phone : $theme_phone;
}
add_filter( 'storebox_core/default_phone', 'storebox_core_default_phone' );

/**
 * Loads component styles and scripts in the <head> for the theme's unit and
 * location templates (the plugin would otherwise print them late).
 */
function storebox_core_template_assets() {
	if ( ! storebox_core_active() || ! function_exists( 'storebox_core_enqueue_components' ) ) {
		return;
	}

	if ( ! is_singular( array( 'sb_unit', 'sb_location' ) ) || storebox_is_built_with_elementor( get_queried_object_id() ) || storebox_has_elementor_location( 'single' ) ) {
		return;
	}

	if ( is_singular( 'sb_unit' ) ) {
		storebox_core_enqueue_components( array( 'unit-gallery', 'unit-specs', 'unit-features', 'unit-booking', 'unit-grid', 'enquiry-form' ) );
	} else {
		storebox_core_enqueue_components( array( 'location-facts', 'opening-hours', 'unit-grid', 'location-map' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'storebox_core_template_assets', 20 );
