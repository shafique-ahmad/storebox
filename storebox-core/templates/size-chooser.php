<?php
/**
 * To-scale size chooser: boxes proportional to floor area, with details.
 *
 * Box height = min + (max − min) × √(area ÷ largest area), so boxes compare by
 * area; width = height × ratio.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

$sizes = storebox_core_normalize_sizes( $args['sizes'], (bool) $args['sync_prices'] );
if ( ! $sizes ) {
	return;
}

$style = $args['style'];
$soft  = 'soft' === $style;
$uid   = Components::uid( 'sb-chooser' );

$min_h     = $args['min_height'] ? (float) $args['min_height'] : ( $soft ? 52 : 48 );
$max_h     = $args['max_height'] ? (float) $args['max_height'] : ( $soft ? 200 : 150 );
$ratio     = $args['ratio'] ? (float) $args['ratio'] : ( $soft ? 1.34 : 1.3 );
$show_meta = 'auto' === $args['show_meta'] ? $soft : (bool) $args['show_meta'];
$largest   = max( wp_list_pluck( $sizes, 'm2' ) );
$selected  = max( 0, min( count( $sizes ) - 1, absint( $args['selected'] ) - 1 ) );

$labels = wp_parse_args(
	(array) $args['labels'],
	array(
		'dimensions' => __( 'Dimensions', 'storebox-core' ),
		'fits'       => __( 'Typically fits', 'storebox-core' ),
		'from'       => __( 'From', 'storebox-core' ),
		'group'      => __( 'Unit sizes', 'storebox-core' ),
	)
);

$suffix = null === $args['price_suffix'] ? _x( '/ month', 'price period', 'storebox-core' ) : $args['price_suffix'];
$detail = null === $args['detail_label'] ? __( 'Selected', 'storebox-core' ) : $args['detail_label'];
$button = null === $args['button_text'] ? ( $soft ? __( 'See available units', 'storebox-core' ) : __( 'Reserve this size', 'storebox-core' ) ) : $args['button_text'];
$url    = $args['button_url'] ? $args['button_url'] : storebox_core_page_url( 'units' );

$data = array();
foreach ( $sizes as $size ) {
	$height = round( $min_h + ( $max_h - $min_h ) * sqrt( $size['m2'] / $largest ) );
	$data[] = array(
		'size'   => storebox_core_format_area( $size['m2'] ),
		'alt'    => storebox_core_format_ft2( $size['m2'] ) . ( $size['ref'] ? ' · ' . sprintf( /* translators: %s: comparison. */ __( 'about %s', 'storebox-core' ), $size['ref'] ) : '' ),
		'dim'    => $size['dims'],
		'fits'   => $size['fits'],
		'price'  => trim( storebox_core_format_price( $size['price'] ) . ' ' . $suffix ),
		'height' => $height,
		'width'  => round( $height * $ratio ),
	);
}

$current = $data[ $selected ];
?>
<div class="sb-c sb-chooser sb-s-<?php echo esc_attr( $style ); ?>" data-sb-component="chooser">
	<div class="sb-chooser__row" role="group" aria-label="<?php echo esc_attr( $labels['group'] ); ?>">
		<?php foreach ( $data as $index => $item ) : ?>
			<button type="button"
				class="sb-chooser__item<?php echo $index === $selected ? ' is-active' : ''; ?>"
				aria-pressed="<?php echo $index === $selected ? 'true' : 'false'; ?>"
				aria-controls="<?php echo esc_attr( $uid ); ?>-detail"
				data-size="<?php echo esc_attr( $item['size'] ); ?>"
				data-alt="<?php echo esc_attr( $item['alt'] ); ?>"
				data-dim="<?php echo esc_attr( $item['dim'] ); ?>"
				data-fits="<?php echo esc_attr( $item['fits'] ); ?>"
				data-price="<?php echo esc_attr( $item['price'] ); ?>">
				<span class="sb-chooser__box" style="height: <?php echo esc_attr( $item['height'] ); ?>px; width: <?php echo esc_attr( $item['width'] ); ?>px;"><b><?php echo esc_html( $item['size'] ); ?></b></span>
				<?php if ( $show_meta ) : ?>
					<span class="sb-chooser__meta"><b><?php echo esc_html( $item['price'] ); ?></b><?php echo esc_html( $item['fits'] ); ?></span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="sb-chooser__detail" id="<?php echo esc_attr( $uid ); ?>-detail" aria-live="polite">
		<?php if ( $soft ) : ?>
			<div>
				<div class="sb-chooser__big" data-sb-d="size"><?php echo esc_html( $current['size'] ); ?></div>
				<div class="sb-chooser__alt" data-sb-d="alt"><?php echo esc_html( $current['alt'] ); ?></div>
			</div>
			<dl class="sb-chooser__facts">
				<div><dt><?php echo esc_html( $labels['dimensions'] ); ?></dt><dd data-sb-d="dim"><?php echo esc_html( $current['dim'] ); ?></dd></div>
				<div><dt><?php echo esc_html( $labels['fits'] ); ?></dt><dd data-sb-d="fits"><?php echo esc_html( $current['fits'] ); ?></dd></div>
				<div><dt><?php echo esc_html( $labels['from'] ); ?></dt><dd data-sb-d="price"><?php echo esc_html( $current['price'] ); ?></dd></div>
			</dl>
			<?php if ( $button && $url ) : ?>
				<a class="sb-btn sb-btn--primary" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $button ); ?></a>
			<?php endif; ?>
		<?php else : ?>
			<?php if ( $detail ) : ?>
				<span class="sb-kicker"><?php echo esc_html( $detail ); ?></span>
			<?php endif; ?>
			<div class="sb-chooser__big" data-sb-d="size"><?php echo esc_html( $current['size'] ); ?></div>
			<div class="sb-chooser__alt" data-sb-d="alt"><?php echo esc_html( $current['alt'] ); ?></div>
			<ul class="sb-chooser__list">
				<li><span><?php echo esc_html( $labels['dimensions'] ); ?></span><b data-sb-d="dim"><?php echo esc_html( $current['dim'] ); ?></b></li>
				<li><span><?php echo esc_html( $labels['fits'] ); ?></span><b data-sb-d="fits"><?php echo esc_html( $current['fits'] ); ?></b></li>
				<li><span><?php echo esc_html( $labels['from'] ); ?></span><b data-sb-d="price"><?php echo esc_html( $current['price'] ); ?></b></li>
			</ul>
			<?php if ( $button && $url ) : ?>
				<a class="sb-btn sb-btn--primary sb-btn--block" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $button ); ?></a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
