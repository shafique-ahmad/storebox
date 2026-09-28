<?php
/**
 * Location tags as pills (access hours, facility features, units free).
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

defined( 'ABSPATH' ) || exit;

$location = storebox_core_get_location( $args['location'] ? $args['location'] : get_the_ID() );
if ( ! $location ) {
	return;
}

$tags = array();
if ( $args['show_access'] && $location['access_chip'] ) {
	$tags[] = $location['access_chip'];
}
$limit = absint( $args['limit'] );
$tags  = array_merge( $tags, $limit ? array_slice( $location['tags'], 0, $limit ) : $location['tags'] );
if ( $args['show_free'] && $location['units_total'] ) {
	$tags[] = $location['free_label'];
}

if ( ! $tags ) {
	return;
}
?>
<ul class="sb-c sb-pills sb-s-<?php echo esc_attr( $args['style'] ); ?>">
	<?php foreach ( $tags as $tag ) : ?>
		<li><?php echo esc_html( $tag ); ?></li>
	<?php endforeach; ?>
</ul>
