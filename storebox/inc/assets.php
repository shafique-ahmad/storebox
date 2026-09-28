<?php
/**
 * Styles, scripts, fonts and design tokens.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Asset version: the theme version, or the file time while developing.
 *
 * @param string $relative_path Path relative to the theme directory.
 * @return string
 */
function storebox_asset_version( $relative_path ) {
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$file = STOREBOX_DIR . '/' . $relative_path;
		if ( file_exists( $file ) ) {
			return (string) filemtime( $file );
		}
	}

	return STOREBOX_VERSION;
}

/**
 * Whether the current request renders one of the theme's own content
 * templates (as opposed to a page built entirely with Elementor).
 *
 * @return bool
 */
function storebox_uses_theme_templates() {
	if ( is_singular() && storebox_is_built_with_elementor( get_queried_object_id() ) ) {
		return false;
	}

	return true;
}

/**
 * Enqueues front-end assets.
 */
function storebox_enqueue_assets() {
	if ( storebox_get_mod( 'local_fonts' ) ) {
		wp_enqueue_style( 'storebox-fonts', STOREBOX_URI . '/assets/css/fonts.css', array(), storebox_asset_version( 'assets/css/fonts.css' ) );
	}

	wp_enqueue_style( 'storebox', STOREBOX_URI . '/assets/css/storebox.css', array(), storebox_asset_version( 'assets/css/storebox.css' ) );
	wp_add_inline_style( 'storebox', storebox_tokens_css() );

	if ( storebox_uses_theme_templates() ) {
		wp_enqueue_style( 'storebox-templates', STOREBOX_URI . '/assets/css/templates.css', array( 'storebox' ), storebox_asset_version( 'assets/css/templates.css' ) );
	}

	wp_enqueue_script(
		'storebox',
		STOREBOX_URI . '/assets/js/storebox.js',
		array(),
		storebox_asset_version( 'assets/js/storebox.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'storebox',
		'storeboxL10n',
		array(
			'openMenu'  => esc_html__( 'Open menu', 'storebox' ),
			'closeMenu' => esc_html__( 'Close menu', 'storebox' ),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'storebox_enqueue_assets' );

/**
 * Flags JavaScript support before first paint, so reveal animations only
 * hide content when the script that shows it again can run.
 */
function storebox_js_flag() {
	wp_print_inline_script_tag( "document.documentElement.classList.add('sb-js');" );
}
add_action( 'wp_head', 'storebox_js_flag', 0 );

/**
 * Preloads the Latin subset of the bundled font, which every page needs.
 */
function storebox_preload_font() {
	if ( ! storebox_get_mod( 'local_fonts' ) ) {
		return;
	}

	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( STOREBOX_URI . '/assets/fonts/archivo-latin.woff2' )
	);
}
add_action( 'wp_head', 'storebox_preload_font', 2 );

/**
 * Base design tokens: preset colours merged with Customizer overrides.
 *
 * The main stylesheet maps each base token onto the matching Elementor Global
 * Color when a kit defines it, so Elementor Site Settings stay authoritative.
 *
 * @return string CSS.
 */
function storebox_tokens_css() {
	$colors = storebox_preset_colors();

	foreach ( array_keys( $colors ) as $name ) {
		$override = storebox_get_mod( 'color_' . $name );
		if ( is_string( $override ) && '' !== $override ) {
			$sanitized = sanitize_hex_color( $override );
			if ( $sanitized ) {
				$colors[ $name ] = $sanitized;
			}
		}
	}

	$css = ':root{';
	foreach ( $colors as $name => $value ) {
		$css .= '--sb-base-' . $name . ':' . $value . ';';
	}
	$css .= '}';

	return apply_filters( 'storebox_tokens_css', $css, $colors );
}

/**
 * Makes the block editor use the same tokens and fonts as the front end.
 *
 * @param array $settings Editor settings.
 * @return array
 */
function storebox_block_editor_tokens( $settings ) {
	if ( ! isset( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}

	$css = storebox_tokens_css();

	if ( storebox_get_mod( 'local_fonts' ) ) {
		$fonts_css = file_get_contents( STOREBOX_DIR . '/assets/css/fonts.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
		if ( $fonts_css ) {
			$css .= str_replace( '../fonts/', STOREBOX_URI . '/assets/fonts/', $fonts_css );
		}
	}

	$settings['styles'][] = array( 'css' => $css );

	return $settings;
}
add_filter( 'block_editor_settings_all', 'storebox_block_editor_tokens' );
