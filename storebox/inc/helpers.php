<?php
/**
 * Small helpers shared by the rest of the theme.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default value of every theme option (theme_mod) Storebox registers.
 *
 * Text defaults are empty on purpose: visible copy comes from the demo import
 * or from the site owner, never from PHP.
 *
 * @return array<string, mixed>
 */
function storebox_defaults() {
	$defaults = array(
		// Design.
		'preset'               => 'soft',
		'button_lift'          => true,
		'reveal'               => true,
		'local_fonts'          => true,
		// Colours (empty = use the preset colour).
		'color_primary'        => '',
		'color_secondary'      => '',
		'color_accent'         => '',
		'color_text'           => '',
		'color_heading'        => '',
		'color_background'     => '',
		'color_surface'        => '',
		'color_border'         => '',
		'color_dark'           => '',
		'color_muted'          => '',
		// Header.
		'header_layout'        => 'auto',
		'header_transparent'   => 'all',
		'header_phone'         => '',
		'header_cta_text'      => '',
		'header_cta_url'       => '',
		'topbar_enable'        => true,
		'topbar_item_1'        => '',
		'topbar_item_2'        => '',
		'topbar_email'         => '',
		'logo_light'           => 0,
		// Footer.
		'footer_text'          => '',
		'footer_copyright'     => '© {year} {site}',
		'footer_note'          => '',
		'social_facebook'      => '',
		'social_instagram'     => '',
		'social_linkedin'      => '',
		'social_x'             => '',
		'social_youtube'       => '',
		// Page hero.
		'hero_image'           => 0,
		'breadcrumbs'          => true,
		// Call to action band on theme templates.
		'cta_enable'           => false,
		'cta_eyebrow'          => '',
		'cta_title'            => '',
		'cta_text'             => '',
		'cta_btn1_text'        => '',
		'cta_btn1_url'         => '',
		'cta_btn2_text'        => '',
		'cta_btn2_url'         => '',
		'cta_image'            => 0,
		// Blog.
		'blog_chips'           => true,
		'blog_read_time'       => true,
		'blog_author_box'      => true,
		'blog_share'           => true,
		'blog_related'         => true,
		'blog_related_eyebrow' => '',
		'blog_related_title'   => '',
		'blog_excerpt_length'  => 22,
		// 404.
		'notfound_title'       => '',
		'notfound_text'        => '',
	);

	/**
	 * Filters the default theme option values.
	 *
	 * @param array $defaults Default values keyed by option name (without prefix).
	 */
	return apply_filters( 'storebox_defaults', $defaults );
}

/**
 * Returns a Storebox theme option.
 *
 * @param string $key Option name without the "storebox_" prefix.
 * @return mixed
 */
function storebox_get_mod( $key ) {
	$defaults = storebox_defaults();
	$default  = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : '';

	return get_theme_mod( 'storebox_' . $key, $default );
}

/**
 * Active design preset: "soft" (Self Storage demo) or "editorial" (Business Storage demo).
 *
 * @return string
 */
function storebox_preset() {
	$preset = storebox_get_mod( 'preset' );

	return in_array( $preset, array( 'soft', 'editorial' ), true ) ? $preset : 'soft';
}

/**
 * Header layout after resolving "auto" against the preset.
 *
 * @return string "overlay" or "classic".
 */
function storebox_header_layout() {
	$layout = storebox_get_mod( 'header_layout' );

	if ( ! in_array( $layout, array( 'overlay', 'classic' ), true ) ) {
		$layout = 'editorial' === storebox_preset() ? 'classic' : 'overlay';
	}

	return apply_filters( 'storebox_header_layout', $layout );
}

/**
 * Colour palette for a preset. Muted, success and warning are slightly darker
 * than the source design so small text meets WCAG AA contrast.
 *
 * @param string $preset Preset slug.
 * @return array<string, string>
 */
function storebox_preset_colors( $preset = '' ) {
	$preset = $preset ? $preset : storebox_preset();

	$colors = array(
		'primary'    => '#16303F',
		'secondary'  => '#F2B705',
		'accent'     => '#2C5871',
		'text'       => '#1B2429',
		'heading'    => '#16303F',
		'background' => '#FFFFFF',
		'surface'    => '#EEF1F2',
		'border'     => '#DFE4E6',
		'dark'       => '#0D1D26',
		'muted'      => '#5F6E77',
		'success'    => '#1A7F53',
		'warning'    => '#AD601E',
	);

	if ( 'editorial' === $preset ) {
		$colors['background'] = '#FBFBFA';
		$colors['surface']    = '#F1EFEA';
		$colors['border']     = '#E3E0D9';
	}

	return apply_filters( 'storebox_preset_colors', $colors, $preset );
}

/**
 * Whether Elementor is loaded.
 *
 * @return bool
 */
function storebox_elementor_active() {
	return did_action( 'elementor/loaded' ) > 0;
}

/**
 * Whether Elementor Pro is loaded.
 *
 * @return bool
 */
function storebox_elementor_pro_active() {
	return function_exists( 'elementor_pro_load_plugin' ) || defined( 'ELEMENTOR_PRO_VERSION' );
}

/**
 * Whether a post was built with Elementor.
 *
 * @param int $post_id Post ID. Defaults to the current post.
 * @return bool
 */
function storebox_is_built_with_elementor( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( ! $post_id || ! storebox_elementor_active() || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$document = \Elementor\Plugin::instance()->documents->get( $post_id );

	return $document && $document->is_built_with_elementor();
}

/**
 * Whether the Storebox Core plugin is active.
 *
 * @return bool
 */
function storebox_core_active() {
	return defined( 'STOREBOX_CORE_VERSION' );
}

/**
 * Prints an Elementor Theme Builder location when Elementor Pro handles it.
 *
 * @param string $location Location name (header, footer, single, archive).
 * @return bool True when Elementor printed the location.
 */
function storebox_do_elementor_location( $location ) {
	return function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location );
}

/**
 * Replaces {year} and {site} tokens in short footer strings.
 *
 * @param string $text Text with tokens.
 * @return string
 */
function storebox_replace_tokens( $text ) {
	return strtr(
		(string) $text,
		array(
			'{year}' => wp_date( 'Y' ),
			'{site}' => get_bloginfo( 'name' ),
		)
	);
}

/**
 * Normalises a phone number for a tel: link.
 *
 * @param string $phone Human readable number.
 * @return string
 */
function storebox_tel_href( $phone ) {
	$digits = preg_replace( '/[^0-9+]/', '', (string) $phone );

	return $digits ? 'tel:' . $digits : '';
}
