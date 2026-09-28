<?php
/**
 * Category links above post lists ("All", then each category with posts).
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

if ( ! storebox_get_mod( 'blog_chips' ) ) {
	return;
}

$storebox_categories = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'name',
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	)
);

if ( count( $storebox_categories ) < 2 ) {
	return;
}

$storebox_posts_page = (int) get_option( 'page_for_posts' );
$storebox_all_url    = $storebox_posts_page ? get_permalink( $storebox_posts_page ) : home_url( '/' );
?>
<nav class="sb-chips" aria-label="<?php esc_attr_e( 'Blog categories', 'storebox' ); ?>">
	<a class="sb-chip" href="<?php echo esc_url( $storebox_all_url ); ?>"<?php echo is_home() ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All', 'storebox' ); ?></a>
	<?php foreach ( $storebox_categories as $storebox_category ) : ?>
		<a class="sb-chip" href="<?php echo esc_url( get_category_link( $storebox_category ) ); ?>"<?php echo is_category( $storebox_category->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $storebox_category->name ); ?></a>
	<?php endforeach; ?>
</nav>
