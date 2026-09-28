<?php
/**
 * Size guide table.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

defined( 'ABSPATH' ) || exit;

$rows = storebox_core_normalize_sizes( $args['rows'], (bool) $args['sync_prices'] );
if ( ! $rows ) {
	return;
}

$headers = wp_parse_args(
	(array) $args['headers'],
	array(
		'size'     => __( 'Size', 'storebox-core' ),
		'dims'     => __( 'Dimensions', 'storebox-core' ),
		'fits'     => __( 'Typically fits', 'storebox-core' ),
		'practice' => __( 'In practice', 'storebox-core' ),
		'price'    => __( 'From', 'storebox-core' ),
		'caption'  => __( 'Unit size guide', 'storebox-core' ),
	)
);

$note = null === $args['price_note'] ? __( 'per month', 'storebox-core' ) : $args['price_note'];
?>
<div class="sb-c sb-guide sb-s-<?php echo esc_attr( $args['style'] ); ?>">
	<div class="sb-guide__scroll" tabindex="0" role="region" aria-label="<?php echo esc_attr( $headers['caption'] ); ?>">
		<table class="sb-guide__table">
			<caption class="screen-reader-text"><?php echo esc_html( $headers['caption'] ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php echo esc_html( $headers['size'] ); ?></th>
					<th scope="col"><?php echo esc_html( $headers['dims'] ); ?></th>
					<th scope="col"><?php echo esc_html( $headers['fits'] ); ?></th>
					<th scope="col"><?php echo esc_html( $headers['practice'] ); ?></th>
					<th scope="col"><?php echo esc_html( $headers['price'] ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<th scope="row"><b><?php echo esc_html( storebox_core_format_area( $row['m2'] ) ); ?></b><small><?php echo esc_html( storebox_core_format_ft2( $row['m2'] ) ); ?></small></th>
						<td><?php echo esc_html( $row['dims'] ); ?></td>
						<td><?php echo esc_html( $row['fits'] ); ?></td>
						<td><?php echo esc_html( $row['practice'] ); ?></td>
						<td><b><?php echo esc_html( storebox_core_format_price( $row['price'] ) ); ?></b><?php echo $note ? '<small>' . esc_html( $note ) . '</small>' : ''; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
