<?php
/**
 * Unit specifications row (floor area, dimensions, ceiling, floor level).
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

defined( 'ABSPATH' ) || exit;

$unit = storebox_core_get_unit( $args['unit'] ? $args['unit'] : get_the_ID() );
if ( ! $unit ) {
	return;
}

$labels = wp_parse_args(
	(array) $args['labels'],
	array(
		'area'       => __( 'Floor area', 'storebox-core' ),
		'dimensions' => __( 'Dimensions', 'storebox-core' ),
		'ceiling'    => __( 'Ceiling', 'storebox-core' ),
		'floor'      => __( 'Floor', 'storebox-core' ),
		'price'      => __( 'Price', 'storebox-core' ),
		'area_ft'    => __( 'Square feet', 'storebox-core' ),
	)
);

$values = array(
	'area'       => $unit['area_label'],
	'dimensions' => $unit['dimensions_label'],
	'ceiling'    => $unit['ceiling_label'],
	'floor'      => $unit['floor'],
	'price'      => $unit['price_label'],
	'area_ft'    => $unit['area_ft_label'],
);

$items = array();
foreach ( (array) $args['items'] as $key ) {
	if ( isset( $values[ $key ] ) && '' !== $values[ $key ] ) {
		$items[ $key ] = $values[ $key ];
	}
}

if ( ! $items ) {
	return;
}
?>
<dl class="sb-c sb-specs sb-s-<?php echo esc_attr( $args['style'] ); ?>" style="--sb-spec-cols: <?php echo esc_attr( count( $items ) ); ?>">
	<?php foreach ( $items as $key => $value ) : ?>
		<div class="sb-specs__item">
			<dt><?php echo esc_html( $labels[ $key ] ); ?></dt>
			<dd><?php echo esc_html( $value ); ?></dd>
		</div>
	<?php endforeach; ?>
</dl>
