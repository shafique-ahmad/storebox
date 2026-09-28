<?php
/**
 * Functions that hook into WordPress to adjust output.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Elementor Pro has a Theme Builder template for a location on the
 * current request.
 *
 * @param string $location Location name.
 * @return bool
 */
function storebox_has_elementor_location( $location ) {
	static $cache = array();

	if ( isset( $cache[ $location ] ) ) {
		return $cache[ $location ];
	}

	$cache[ $location ] = false;

	if ( ! storebox_elementor_pro_active() || ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
		return false;
	}

	$module = \ElementorPro\Modules\ThemeBuilder\Module::instance();
	if ( method_exists( $module, 'get_conditions_manager' ) ) {
		$manager = $module->get_conditions_manager();
		if ( method_exists( $manager, 'get_documents_for_location' ) ) {
			$cache[ $location ] = ! empty( $manager->get_documents_for_location( $location ) );
		}
	}

	return $cache[ $location ];
}

/**
 * Whether the overlay header starts transparent on this request.
 *
 * @return bool
 */
function storebox_header_is_transparent() {
	if ( 'overlay' !== storebox_header_layout() ) {
		return false;
	}

	$page_setting = is_singular() ? storebox_get_transparent_setting( get_queried_object_id() ) : '';

	if ( 'on' === $page_setting ) {
		return true;
	}
	if ( 'off' === $page_setting ) {
		return false;
	}

	// Theme templates only have a dark page header in the soft preset.
	if ( storebox_uses_theme_templates() && 'soft' !== storebox_preset() ) {
		return false;
	}

	$mode = storebox_get_mod( 'header_transparent' );

	if ( 'none' === $mode ) {
		return false;
	}
	if ( 'front' === $mode ) {
		return is_front_page();
	}

	return true;
}

/**
 * Adds design and layout classes to <body>.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function storebox_body_classes( $classes ) {
	$classes[] = 'sb-preset-' . storebox_preset();

	if ( storebox_is_storebox_kit() ) {
		$classes[] = 'sb-kit';
	}

	if ( storebox_has_elementor_location( 'header' ) ) {
		$classes[] = 'sb-header-elementor';
	} else {
		$classes[] = 'sb-header-' . storebox_header_layout();
		$classes[] = storebox_header_is_transparent() ? 'sb-header-transparent' : 'sb-header-solid';
	}

	if ( storebox_get_mod( 'button_lift' ) ) {
		$classes[] = 'sb-btn-lift';
	}

	if ( storebox_get_mod( 'reveal' ) ) {
		$classes[] = 'sb-reveal';
	}

	if ( storebox_uses_theme_templates() ) {
		$classes[] = 'sb-theme-template';
	}

	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', 'storebox_body_classes' );

/**
 * Adds a pingback link on single posts that accept pings.
 */
function storebox_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'storebox_pingback_header' );

/**
 * Excerpt length from the Customizer.
 *
 * @param int $length Default length.
 * @return int
 */
function storebox_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}

	$custom = absint( storebox_get_mod( 'blog_excerpt_length' ) );

	return $custom ? $custom : $length;
}
add_filter( 'excerpt_length', 'storebox_excerpt_length' );

/**
 * Plain ellipsis instead of "[…]".
 *
 * @param string $more Default more string.
 * @return string
 */
function storebox_excerpt_more( $more ) {
	return is_admin() ? $more : '&hellip;';
}
add_filter( 'excerpt_more', 'storebox_excerpt_more' );

// The archive type is shown as an eyebrow in the page header instead of a "Category:" prefix.
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * Estimated reading time in minutes. A number stored in the
 * "_storebox_read_time" post meta overrides the estimate.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function storebox_read_time( $post_id = 0 ) {
	$post_id  = $post_id ? $post_id : get_the_ID();
	$override = absint( get_post_meta( $post_id, '_storebox_read_time', true ) );

	if ( $override ) {
		return $override;
	}

	$content = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post_id ) ) );
	$words   = count( preg_split( '/\s+/u', trim( $content ), -1, PREG_SPLIT_NO_EMPTY ) );

	/**
	 * Filters the reading speed used for the estimate.
	 *
	 * @param int $wpm Words per minute.
	 */
	$wpm = max( 1, absint( apply_filters( 'storebox_words_per_minute', 220 ) ) );

	return max( 1, (int) ceil( $words / $wpm ) );
}

/**
 * Initials for avatar badges ("Samira Mansour" → "SM").
 *
 * @param string $name Full name.
 * @return string
 */
function storebox_initials( $name ) {
	$parts    = preg_split( '/\s+/u', trim( wp_strip_all_tags( (string) $name ) ), -1, PREG_SPLIT_NO_EMPTY );
	$initials = '';

	if ( ! $parts ) {
		return '';
	}

	$initials .= mb_substr( $parts[0], 0, 1 );
	if ( count( $parts ) > 1 ) {
		$initials .= mb_substr( end( $parts ), 0, 1 );
	}

	return mb_strtoupper( $initials );
}

/**
 * Primary category of a post (respects Yoast SEO and Rank Math primary terms).
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function storebox_primary_category( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	foreach ( array( '_yoast_wpseo_primary_category', 'rank_math_primary_category' ) as $meta_key ) {
		$term_id = absint( get_post_meta( $post_id, $meta_key, true ) );
		if ( $term_id ) {
			$term = get_term( $term_id, 'category' );
			if ( $term instanceof WP_Term ) {
				return $term;
			}
		}
	}

	$categories = get_the_category( $post_id );

	return $categories ? $categories[0] : null;
}

/**
 * Wraps oEmbed output so embeds keep their aspect ratio in the prose column.
 *
 * @param string $html Embed HTML.
 * @return string
 */
function storebox_embed_wrap( $html ) {
	if ( false !== strpos( $html, '<iframe' ) && false === strpos( $html, 'sb-embed' ) ) {
		return '<div class="sb-embed">' . $html . '</div>';
	}

	return $html;
}
add_filter( 'embed_oembed_html', 'storebox_embed_wrap' );
