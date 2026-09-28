<?php
/**
 * Map of one or more locations.
 *
 * Renders the illustrative map (no external requests) with linked pins; in
 * "live" mode the script swaps in a Leaflet map with the same pins. A list of
 * locations is always printed for screen readers.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

defined( 'ABSPATH' ) || exit;

$ids = storebox_core_parse_ids( $args['locations'] );
if ( ! $ids ) {
	$ids = get_posts(
		array(
			'post_type'      => 'sb_location',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
}

$points = array();
foreach ( $ids as $id ) {
	$location = storebox_core_get_location( $id );
	if ( $location && ( $location['lat'] || $location['lng'] ) ) {
		$points[] = array(
			'name'    => $location['name'],
			'url'     => $location['url'],
			'address' => $location['address_inline'],
			'lat'     => $location['lat'],
			'lng'     => $location['lng'],
		);
	}
}

if ( ! $points ) {
	return;
}

// Pin positions for the illustrative map, from the coordinates' bounding box.
if ( 1 === count( $points ) ) {
	$points[0]['x'] = 50;
	$points[0]['y'] = 52;
} else {
	$lats    = wp_list_pluck( $points, 'lat' );
	$lngs    = wp_list_pluck( $points, 'lng' );
	$lat_min = min( $lats );
	$lat_max = max( $lats );
	$lng_min = min( $lngs );
	$lng_max = max( $lngs );
	foreach ( $points as $index => $point ) {
		$x = $lng_max > $lng_min ? ( $point['lng'] - $lng_min ) / ( $lng_max - $lng_min ) : 0.5;
		$y = $lat_max > $lat_min ? ( $lat_max - $point['lat'] ) / ( $lat_max - $lat_min ) : 0.5;

		$points[ $index ]['x'] = round( 14 + $x * 72, 2 );
		$points[ $index ]['y'] = round( 30 + $y * 44, 2 );
	}
}

$mode    = $args['mode'] ? $args['mode'] : storebox_core_setting( 'map_mode' );
$mode    = in_array( $mode, array( 'live', 'illustrative' ), true ) ? $mode : 'live';
$consent = null === $args['consent'] ? (bool) storebox_core_setting( 'map_consent' ) : (bool) $args['consent'];
$label   = $args['label'] ? $args['label'] : ( 1 === count( $points ) ? sprintf( /* translators: %s: location name. */ __( 'Map showing %s', 'storebox-core' ), $points[0]['name'] ) : __( 'Map of our locations', 'storebox-core' ) );

$config = array(
	'mode'        => $mode,
	'consent'     => $consent,
	'tiles'       => esc_url_raw( storebox_core_setting( 'map_tiles' ) ),
	'attribution' => wp_kses(
		storebox_core_setting( 'map_attribution' ),
		array(
			'a' => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
		)
	),
	'zoom'        => max( 3, min( 18, absint( $args['zoom'] ) ) ),
	'points'      => $points,
);

$classes = array( 'sb-c', 'sb-map', 'sb-s-' . $args['style'], 'sb-map--' . $mode );
if ( storebox_core_setting( 'map_muted' ) ) {
	$classes[] = 'sb-map--muted';
}

$ratio        = preg_replace( '#[^0-9/. ]#', '', (string) $args['ratio'] );
$ratio_mobile = preg_replace( '#[^0-9/. ]#', '', (string) $args['ratio_mobile'] );
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-sb-component="map" data-sb-map="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" style="--sb-map-ratio: <?php echo esc_attr( $ratio ? $ratio : '16 / 7' ); ?>; --sb-map-ratio-mobile: <?php echo esc_attr( $ratio_mobile ? $ratio_mobile : '4 / 3' ); ?>;">
	<div class="sb-map__canvas" role="region" aria-label="<?php echo esc_attr( $label ); ?>">
		<div class="sb-map__art" aria-hidden="true"></div>
		<?php foreach ( $points as $point ) : ?>
			<a class="sb-map__pin" href="<?php echo esc_url( $point['url'] ); ?>" style="left: <?php echo esc_attr( $point['x'] ); ?>%; top: <?php echo esc_attr( $point['y'] ); ?>%;"><span><?php echo esc_html( $point['name'] ); ?></span><i aria-hidden="true"></i></a>
		<?php endforeach; ?>
		<?php if ( 'live' === $mode && $consent ) : ?>
			<div class="sb-map__consent">
				<button type="button" class="sb-btn sb-btn--dark" data-sb-map-load><?php esc_html_e( 'Load interactive map', 'storebox-core' ); ?></button>
				<p><?php esc_html_e( 'Loads map tiles from an external server.', 'storebox-core' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
	<ul class="screen-reader-text">
		<?php foreach ( $points as $point ) : ?>
			<li><a href="<?php echo esc_url( $point['url'] ); ?>"><?php echo esc_html( $point['name'] ); ?></a><?php echo $point['address'] ? ', ' . esc_html( $point['address'] ) : ''; ?></li>
		<?php endforeach; ?>
	</ul>
</div>
