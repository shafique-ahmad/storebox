<?php
/**
 * Search form.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_field_id = wp_unique_id( 'sb-search-' );
?>
<form role="search" method="get" class="sb-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $storebox_field_id ); ?>"><?php esc_html_e( 'Search for:', 'storebox' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $storebox_field_id ); ?>" class="sb-searchform__field" placeholder="<?php esc_attr_e( 'Search the site', 'storebox' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button type="submit" class="sb-searchform__button">
		<?php storebox_the_icon( 'search', 18 ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'storebox' ); ?></span>
	</button>
</form>
