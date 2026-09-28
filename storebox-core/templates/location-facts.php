<?php
/**
 * Location facts: address, access, units and phone.
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

$labels = wp_parse_args(
	(array) $args['labels'],
	array(
		'address' => __( 'Address', 'storebox-core' ),
		'access'  => __( 'Access', 'storebox-core' ),
		'units'   => __( 'Units', 'storebox-core' ),
		'phone'   => __( 'Phone', 'storebox-core' ),
		'email'   => __( 'Email', 'storebox-core' ),
	)
);

$items = array();
foreach ( (array) $args['items'] as $key ) {
	switch ( $key ) {
		case 'address':
			if ( $location['address_lines'] ) {
				$items[ $key ] = implode( '<br>', array_map( 'esc_html', $location['address_lines'] ) );
			}
			break;
		case 'access':
			if ( $location['access'] ) {
				$items[ $key ] = esc_html( $location['access'] );
			}
			break;
		case 'units':
			if ( $location['units_label'] ) {
				$items[ $key ] = esc_html( $location['units_label'] );
			}
			break;
		case 'phone':
			if ( $location['phone'] ) {
				$items[ $key ] = '<a href="' . esc_url( $location['phone_href'], array( 'tel' ) ) . '">' . esc_html( $location['phone'] ) . '</a>';
			}
			break;
		case 'email':
			if ( $location['email'] ) {
				$items[ $key ] = '<a href="' . esc_url( 'mailto:' . antispambot( $location['email'] ) ) . '">' . esc_html( antispambot( $location['email'] ) ) . '</a>';
			}
			break;
	}
}

if ( ! $items ) {
	return;
}
?>
<dl class="sb-c sb-facts sb-s-<?php echo esc_attr( $args['style'] ); ?><?php echo $args['overlap'] ? ' sb-facts--overlap' : ''; ?>" style="--sb-fact-cols: <?php echo esc_attr( count( $items ) ); ?>">
	<?php foreach ( $items as $key => $html ) : ?>
		<div class="sb-facts__item">
			<dt><?php echo esc_html( $labels[ $key ] ); ?></dt>
			<dd><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built above. ?></dd>
		</div>
	<?php endforeach; ?>
</dl>
