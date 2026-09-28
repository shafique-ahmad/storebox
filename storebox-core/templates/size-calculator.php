<?php
/**
 * Size calculator: add items, get a recommended unit size and price.
 *
 * The item list is rendered in HTML; the script does the arithmetic:
 * needed floor area = total volume ÷ (stack height × usable share).
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

$items = is_array( $args['items'] ) && $args['items'] ? $args['items'] : storebox_core_default_calculator_items();
$sizes = storebox_core_normalize_sizes( $args['sizes'], (bool) $args['sync_prices'] );

if ( ! $items || ! $sizes ) {
	return;
}

$uid = Components::uid( 'sb-calc' );

$text = array(
	'title'       => null === $args['title'] ? __( 'What are you storing?', 'storebox-core' ) : $args['title'],
	'subtitle'    => null === $args['subtitle'] ? __( 'Add rough quantities — it does not need to be exact.', 'storebox-core' ) : $args['subtitle'],
	'empty'       => null === $args['empty_text'] ? __( 'Nothing added yet — pick a few items above.', 'storebox-core' ) : $args['empty_text'],
	'recommended' => null === $args['recommended_label'] ? __( 'Recommended', 'storebox-core' ) : $args['recommended_label'],
	'from'        => null === $args['from_label'] ? __( 'From', 'storebox-core' ) : $args['from_label'],
	/* translators: %d: how full the unit would be, in percent. */
	'fill'        => null === $args['fill_text'] ? __( 'About %d%% full, leaving room to walk in and reach the back.', 'storebox-core' ) : $args['fill_text'],
	'button'      => null === $args['button_text'] ? __( 'Check availability', 'storebox-core' ) : $args['button_text'],
);

$button_url = $args['button_url'] ? $args['button_url'] : storebox_core_page_url( 'units' );

$config = array(
	'stack'    => max( 0.5, (float) $args['stack'] ),
	'fill'     => min( 1, max( 0.1, (float) $args['fill'] ) ),
	'fillText' => $text['fill'],
	'sizes'    => array_map(
		static function ( $size ) {
			return array(
				'm'       => $size['m2'],
				'mLabel'  => storebox_core_format_area( $size['m2'] ),
				'ftLabel' => storebox_core_format_ft2( $size['m2'] ),
				'ref'     => $size['ref'],
				'price'   => storebox_core_format_price( $size['price'] ),
			);
		},
		$sizes
	),
	/* translators: %s: comparison, e.g. "a single garage". */
	'about'    => __( 'about %s', 'storebox-core' ),
);
?>
<div class="sb-c sb-calc sb-s-<?php echo esc_attr( $args['style'] ); ?>" data-sb-component="calculator" data-sb-calc="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
	<div class="sb-calc__head">
		<div>
			<?php if ( $text['title'] ) : ?>
				<h3 class="sb-calc__title" id="<?php echo esc_attr( $uid ); ?>-title"><?php echo esc_html( $text['title'] ); ?></h3>
			<?php endif; ?>
			<?php if ( $text['subtitle'] ) : ?>
				<p class="sb-calc__sub"><?php echo esc_html( $text['subtitle'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $args['unit_toggle'] ) : ?>
			<div class="sb-toggle" role="group" aria-label="<?php esc_attr_e( 'Measurement units', 'storebox-core' ); ?>">
				<button type="button" data-unit="m" aria-pressed="true"><?php echo esc_html_x( 'm²', 'square metres, short', 'storebox-core' ); ?></button>
				<button type="button" data-unit="ft" aria-pressed="false"><?php echo esc_html_x( 'ft²', 'square feet, short', 'storebox-core' ); ?></button>
			</div>
		<?php endif; ?>
	</div>

	<ul class="sb-calc__items"<?php echo $text['title'] ? ' aria-labelledby="' . esc_attr( $uid ) . '-title"' : ''; ?>>
		<?php foreach ( $items as $index => $item ) : ?>
			<?php
			$name   = isset( $item['name'] ) ? (string) $item['name'] : '';
			$volume = isset( $item['volume'] ) ? (float) str_replace( ',', '.', (string) $item['volume'] ) : 0;
			if ( '' === $name || $volume <= 0 ) {
				continue;
			}
			?>
			<li class="sb-calc__item" data-v="<?php echo esc_attr( $volume ); ?>">
				<span class="sb-calc__name"><?php echo esc_html( $name ); ?></span>
				<span class="sb-calc__step">
					<?php /* translators: %s: item name. */ ?>
					<button type="button" data-d="-1" aria-label="<?php echo esc_attr( sprintf( __( 'Remove one: %s', 'storebox-core' ), $name ) ); ?>">&minus;</button>
					<output class="sb-calc__qty" aria-live="polite">0</output>
					<?php /* translators: %s: item name. */ ?>
					<button type="button" data-d="1" aria-label="<?php echo esc_attr( sprintf( __( 'Add one: %s', 'storebox-core' ), $name ) ); ?>">+</button>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="sb-calc__result">
		<p class="sb-calc__empty" data-sb-empty><?php echo esc_html( $text['empty'] ); ?></p>
		<div class="sb-calc__res" data-sb-result hidden aria-live="polite">
			<div class="sb-calc__top">
				<div>
					<span class="sb-calc__label"><?php echo esc_html( $text['recommended'] ); ?></span>
					<div class="sb-calc__size" data-sb-size></div>
					<div class="sb-calc__alt" data-sb-alt></div>
				</div>
				<div class="sb-calc__price">
					<span class="sb-calc__label"><?php echo esc_html( $text['from'] ); ?></span>
					<b data-sb-price></b>
				</div>
			</div>
			<div class="sb-calc__bar"><i data-sb-bar></i></div>
			<p class="sb-calc__fill" data-sb-fill></p>
		</div>
		<?php if ( $text['button'] && $button_url ) : ?>
			<a class="sb-btn sb-btn--primary sb-btn--block" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $text['button'] ); ?></a>
		<?php endif; ?>
	</div>
</div>
