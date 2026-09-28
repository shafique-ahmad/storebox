<?php
/**
 * Menus: primary navigation, submenu toggles and footer columns.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints the primary menu.
 */
function storebox_primary_menu() {
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'sb-menu',
			'menu_id'        => 'sb-menu',
			'depth'          => 3,
			'fallback_cb'    => 'storebox_primary_menu_fallback',
		)
	);
}

/**
 * Fallback when no primary menu is assigned: top-level pages.
 */
function storebox_primary_menu_fallback() {
	$pages = wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
			'number'   => 6,
			'echo'     => false,
		)
	);

	if ( $pages ) {
		echo '<ul id="sb-menu" class="sb-menu">' . $pages . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core output.
	}
}

/**
 * Adds a disclosure button after primary menu items that have children, so
 * submenus work with keyboards and touch screens.
 *
 * @param string   $item_output Item HTML.
 * @param WP_Post  $item        Menu item.
 * @param int      $depth       Depth.
 * @param stdClass $args        wp_nav_menu() arguments.
 * @return string
 */
function storebox_submenu_toggle( $item_output, $item, $depth, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $item_output;
	}

	if ( ! in_array( 'menu-item-has-children', (array) $item->classes, true ) ) {
		return $item_output;
	}

	return $item_output . sprintf(
		'<button type="button" class="sb-submenu-toggle" aria-expanded="false"><span class="screen-reader-text">%1$s</span>%2$s</button>',
		/* translators: %s: menu item label. */
		esc_html( sprintf( __( 'Show submenu for %s', 'storebox' ), wp_strip_all_tags( $item->title ) ) ),
		storebox_icon( 'chevron', 14, array( 'stroke_width' => 2.6 ) )
	);
}
add_filter( 'walker_nav_menu_start_el', 'storebox_submenu_toggle', 10, 4 );

/**
 * Prints a footer column: widgets when the widget area is used, otherwise the
 * menu assigned to the matching location (its name becomes the heading).
 *
 * @param int $column Column number 1–3.
 */
function storebox_footer_column( $column ) {
	$sidebar  = 'footer-' . $column;
	$location = 'footer-' . $column;

	if ( is_active_sidebar( $sidebar ) ) {
		dynamic_sidebar( $sidebar );
		return;
	}

	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return;
	}

	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	if ( ! $menu ) {
		return;
	}

	printf( '<h2 class="sb-footer__title">%s</h2>', esc_html( $menu->name ) );

	wp_nav_menu(
		array(
			'theme_location'       => $location,
			'container'            => 'nav',
			'container_aria_label' => $menu->name,
			'menu_class'           => 'sb-footer__menu',
			'depth'                => 1,
			'fallback_cb'          => false,
		)
	);
}
