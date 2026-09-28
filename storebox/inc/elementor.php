<?php
/**
 * Elementor and Elementor Pro integration.
 *
 * Everything here is guarded by Elementor hooks, so the theme runs normally
 * when Elementor is not installed.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lets Elementor Pro Theme Builder replace the header, footer, single and
 * archive locations.
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Locations manager.
 */
function storebox_register_elementor_locations( $manager ) {
	$manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'storebox_register_elementor_locations' );

/**
 * Registers the bundled Archivo as a local font so Elementor does not load it
 * from Google Fonts when a Global Font uses it.
 *
 * @param array $fonts Font name => font type.
 * @return array
 */
function storebox_elementor_local_fonts( $fonts ) {
	if ( storebox_get_mod( 'local_fonts' ) ) {
		$fonts['Archivo'] = 'storebox';
	}

	return $fonts;
}
add_filter( 'elementor/fonts/additional_fonts', 'storebox_elementor_local_fonts' );

/**
 * Label for the local font group in Elementor's font picker.
 *
 * @param array $groups Font groups.
 * @return array
 */
function storebox_elementor_font_groups( $groups ) {
	$groups['storebox'] = esc_html__( 'Storebox (bundled)', 'storebox' );

	return $groups;
}
add_filter( 'elementor/fonts/groups', 'storebox_elementor_font_groups' );

/**
 * Elementor asks the font's owner to load it; the theme stylesheet is enough.
 */
function storebox_elementor_print_local_font() {
	if ( ! wp_style_is( 'storebox-fonts', 'enqueued' ) && ! wp_style_is( 'storebox-fonts', 'done' ) ) {
		wp_enqueue_style( 'storebox-fonts', STOREBOX_URI . '/assets/css/fonts.css', array(), storebox_asset_version( 'assets/css/fonts.css' ) );
	}
}
add_action( 'elementor/fonts/print_font_links/storebox', 'storebox_elementor_print_local_font' );

/**
 * Adds the Storebox entrance animations to Elementor's Motion Effects list.
 * They reproduce the gentle rise-in used throughout the source design.
 *
 * @param array $animations Additional animations grouped by label.
 * @return array
 */
function storebox_elementor_animations( $animations ) {
	$animations[ esc_html__( 'Storebox', 'storebox' ) ] = array(
		'sbRise'  => esc_html__( 'Rise (Storebox)', 'storebox' ),
		'sbFade'  => esc_html__( 'Fade (Storebox)', 'storebox' ),
		'sbScale' => esc_html__( 'Settle (Storebox)', 'storebox' ),
	);

	return $animations;
}
add_filter( 'elementor/controls/animations/additional_animations', 'storebox_elementor_animations' );

/**
 * Adds Storebox options to Elementor's Page Settings panel.
 *
 * @param \Elementor\Core\Base\Document $document Document being edited.
 */
function storebox_elementor_document_controls( $document ) {
	if ( ! class_exists( '\Elementor\Controls_Manager' ) || ! $document instanceof \Elementor\Core\DocumentTypes\PageBase ) {
		return;
	}

	$document->start_controls_section(
		'storebox_page_section',
		array(
			'label' => esc_html__( 'Storebox', 'storebox' ),
			'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
		)
	);

	$document->add_control(
		'storebox_transparent_header',
		array(
			'label'       => esc_html__( 'Overlay header', 'storebox' ),
			'type'        => \Elementor\Controls_Manager::SELECT,
			'default'     => '',
			'options'     => array(
				''    => esc_html__( 'Default', 'storebox' ),
				'on'  => esc_html__( 'Transparent', 'storebox' ),
				'off' => esc_html__( 'Solid', 'storebox' ),
			),
			'description' => esc_html__( 'For the overlay header layout: transparent over the first section, or solid when the page starts with a light section. Reload the preview to see the change.', 'storebox' ),
		)
	);

	$document->end_controls_section();
}
add_action( 'elementor/documents/register_controls', 'storebox_elementor_document_controls' );

/**
 * Whether the active Elementor kit carries the Storebox design tokens (set by
 * the demo importer). Only then do theme colours follow Elementor's Global
 * Colors — Elementor's default kit would otherwise recolour the theme.
 *
 * @return bool
 */
function storebox_is_storebox_kit() {
	static $result = null;

	if ( null !== $result ) {
		return $result;
	}

	$result = false;
	$kit_id = storebox_elementor_active() ? (int) get_option( 'elementor_active_kit' ) : 0;

	if ( $kit_id ) {
		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( is_array( $settings ) && ! empty( $settings['custom_colors'] ) && is_array( $settings['custom_colors'] ) ) {
			foreach ( $settings['custom_colors'] as $color ) {
				if ( isset( $color['_id'] ) && 'sbsurface' === $color['_id'] ) {
					$result = true;
					break;
				}
			}
		}
	}

	/**
	 * Filters whether theme colours follow the Elementor kit.
	 *
	 * @param bool $result True for a Storebox kit.
	 */
	$result = (bool) apply_filters( 'storebox_is_storebox_kit', $result );

	return $result;
}

/**
 * Transparent header preference for a post: Elementor page setting first,
 * then the Storebox meta box.
 *
 * @param int $post_id Post ID.
 * @return string '', 'on' or 'off'.
 */
function storebox_get_transparent_setting( $post_id ) {
	$elementor = get_post_meta( $post_id, '_elementor_page_settings', true );

	if ( is_array( $elementor ) && ! empty( $elementor['storebox_transparent_header'] ) ) {
		return storebox_sanitize_transparent_header( $elementor['storebox_transparent_header'] );
	}

	return storebox_sanitize_transparent_header( get_post_meta( $post_id, '_storebox_transparent_header', true ) );
}
