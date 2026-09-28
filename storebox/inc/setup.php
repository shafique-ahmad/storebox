<?php
/**
 * Theme setup: supports, menus, image sizes and widget areas.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'storebox_setup' ) ) {
	/**
	 * Registers theme features.
	 */
	function storebox_setup() {
		load_theme_textdomain( 'storebox', STOREBOX_DIR . '/languages' );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 240,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/editor-style.css' );

		register_nav_menus(
			array(
				'primary'  => esc_html__( 'Primary menu', 'storebox' ),
				'footer-1' => esc_html__( 'Footer column 1', 'storebox' ),
				'footer-2' => esc_html__( 'Footer column 2', 'storebox' ),
				'footer-3' => esc_html__( 'Footer column 3', 'storebox' ),
			)
		);

		/*
		 * Image sizes. Layouts crop with CSS aspect-ratio and object-fit, so
		 * these are soft limits that keep downloads small, not hard crops.
		 */
		add_image_size( 'storebox-card', 900, 675, true );
		add_image_size( 'storebox-wide', 1600, 1000, true );
		add_image_size( 'storebox-hero', 1920, 1280, false );
		add_image_size( 'storebox-thumb', 420, 315, true );
	}
}
add_action( 'after_setup_theme', 'storebox_setup' );

/**
 * Block styles that match the Storebox article design.
 */
function storebox_register_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	register_block_style(
		'core/group',
		array(
			'name'  => 'sb-callout',
			'label' => esc_html__( 'Callout', 'storebox' ),
		)
	);
}
add_action( 'init', 'storebox_register_block_styles' );

/**
 * Content width for embeds, matching the article column.
 */
function storebox_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'storebox_content_width', 740 );
}
add_action( 'after_setup_theme', 'storebox_content_width', 0 );

/**
 * Human-friendly names for the custom image sizes in the media modal.
 *
 * @param array $sizes Size names.
 * @return array
 */
function storebox_image_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'storebox-card' => esc_html__( 'Storebox card (4:3)', 'storebox' ),
			'storebox-wide' => esc_html__( 'Storebox wide (16:10)', 'storebox' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'storebox_image_size_names' );

/**
 * Widget areas. Footer columns fall back to the matching footer menu when a
 * column has no widgets, so the footer works out of the box.
 */
function storebox_widgets_init() {
	$shared = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$shared,
			array(
				'name'        => esc_html__( 'Blog sidebar', 'storebox' ),
				'id'          => 'sidebar-blog',
				'description' => esc_html__( 'Shown below single posts when it contains widgets.', 'storebox' ),
			)
		)
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar(
			array_merge(
				$shared,
				array(
					/* translators: %d: footer column number. */
					'name'         => sprintf( esc_html__( 'Footer column %d', 'storebox' ), $i ),
					'id'           => 'footer-' . $i,
					'description'  => esc_html__( 'Replaces the footer menu of the same column.', 'storebox' ),
					'before_title' => '<h2 class="widget-title sb-footer__title">',
				)
			)
		);
	}
}
add_action( 'widgets_init', 'storebox_widgets_init' );
