<?php
/**
 * Unit card (used by the unit grid).
 *
 * Override: copy to {theme}/storebox-core/unit-card.php.
 *
 * @package Storebox_Core
 *
 * @var array $args Card arguments; $args['unit'] holds the unit data.
 */

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

$unit  = $args['unit'];
$style = $args['style'];

$subline_mode = Components::auto( $args['subline'], $style, 'dimensions', 'name' );
$desc_mode    = Components::auto( $args['description'], $style, 'bullets', 'fits_location' );
$cta_style    = Components::auto( $args['cta_style'], $style, 'button', 'link' );

$is_full      = 'full' === $unit['status'];
$status_label = ( $is_full && ! empty( $args['full_label'] ) ) ? $args['full_label'] : $unit['status_label'];

if ( $is_full ) {
	$cta_text = $args['waitlist_text'] ? $args['waitlist_text'] : __( 'Join waitlist', 'storebox-core' );
} else {
	$cta_text = $args['cta_text'] ? $args['cta_text'] : __( 'Reserve', 'storebox-core' );
}

$subline = array( $unit['area_ft_label'] );
if ( 'dimensions' === $subline_mode && $unit['dimensions_label'] ) {
	$subline[] = $unit['dimensions_label'];
} elseif ( 'name' === $subline_mode ) {
	$subline[] = $unit['name'];
}

$location_slug = $unit['location'] ? get_post_field( 'post_name', $unit['location']['id'] ) : '';

$attributes = array(
	'class'      => 'sb-unit-card sb-unit-card--' . $unit['status'],
	'data-size'  => $unit['size'] ? $unit['size']['slug'] : '',
	'data-loc'   => $location_slug,
	'data-type'  => $unit['type'] ? $unit['type']['slug'] : '',
	'data-area'  => $unit['area'],
	'data-price' => $unit['price'],
	'data-avail' => $unit['available'],
);
?>
<article
<?php
foreach ( $attributes as $name => $value ) {
	printf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) ); }
?>
<?php echo ! empty( $args['hidden'] ) ? ' hidden' : ''; ?>>
	<?php if ( $args['show_image'] ) : ?>
		<a class="sb-unit-card__img" href="<?php echo esc_url( $unit['url'] ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( $unit['image_id'] ) {
				echo wp_get_attachment_image(
					$unit['image_id'],
					$args['image_size'],
					false,
					array(
						'alt'     => '',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 680px) 100vw, (max-width: 1000px) 50vw, 420px',
					)
				);
			}
			?>
			<?php if ( $args['show_status'] ) : ?>
				<span class="sb-tag sb-tag--<?php echo esc_attr( $unit['status'] ); ?>"><i></i><?php echo esc_html( $status_label ); ?></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>

	<div class="sb-unit-card__body">
		<div class="sb-unit-card__size">
			<b><?php echo esc_html( $unit['area_label'] ); ?></b>
			<span><?php echo esc_html( implode( ' · ', $subline ) ); ?></span>
		</div>

		<?php if ( ! $args['show_image'] && $args['show_status'] ) : ?>
			<span class="sb-tag sb-tag--inline sb-tag--<?php echo esc_attr( $unit['status'] ); ?>"><i></i><?php echo esc_html( $status_label ); ?></span>
		<?php endif; ?>

		<?php if ( $args['show_name'] ) : ?>
			<p class="sb-unit-card__name">
				<a href="<?php echo esc_url( $unit['url'] ); ?>"><?php echo esc_html( $unit['name'] ); ?></a><?php echo $unit['fits'] ? ' · ' . esc_html( $unit['fits'] ) : ''; ?>
			</p>
		<?php endif; ?>

		<?php
		if ( 'bullets' === $desc_mode ) {
			$bullets = storebox_core_unit_bullets( $unit, $args['bullets'], $args['bullet_limit'], $args['bullet_location'] );
			if ( $bullets ) {
				echo '<ul class="sb-unit-card__list">';
				foreach ( $bullets as $bullet ) {
					echo '<li>' . esc_html( $bullet ) . '</li>';
				}
				echo '</ul>';
			}
		} elseif ( 'fits_location' === $desc_mode ) {
			$sentence = '';
			if ( $unit['fits'] ) {
				$sentence .= rtrim( $unit['fits'], '.' ) . '. ';
			}
			if ( $unit['type_label'] && $unit['location'] ) {
				/* translators: 1: unit type, 2: location name. */
				$sentence .= sprintf( __( '%1$s at %2$s.', 'storebox-core' ), $unit['type_label'], $unit['location']['name'] );
			} elseif ( $unit['location'] ) {
				$sentence .= $unit['location']['name'] . '.';
			}
			if ( trim( $sentence ) ) {
				echo '<p class="sb-unit-card__fits">' . esc_html( trim( $sentence ) ) . '</p>';
			}
		} elseif ( 'excerpt' === $desc_mode && $unit['excerpt'] ) {
			echo '<p class="sb-unit-card__fits">' . esc_html( $unit['excerpt'] ) . '</p>';
		}
		?>

		<div class="sb-unit-card__foot">
			<?php if ( $args['show_price'] ) : ?>
				<div class="sb-price">
					<b><?php echo esc_html( $unit['price_label'] ); ?></b>
					<span><?php echo esc_html_x( '/ month', 'price period', 'storebox-core' ); ?></span>
				</div>
			<?php endif; ?>
			<a class="sb-unit-card__cta sb-unit-card__cta--<?php echo esc_attr( $cta_style ); ?><?php echo $is_full ? ' is-waitlist' : ''; ?>" href="<?php echo esc_url( $unit['url'] ); ?>">
				<?php echo esc_html( $cta_text ); ?><span class="screen-reader-text"><?php echo esc_html( ': ' . $unit['short_title'] ); ?></span>
			</a>
		</div>
	</div>
</article>
