<?php
/**
 * Unit gallery: large image with thumbnail switcher.
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

$unit = $args['unit'] ? storebox_core_get_unit( $args['unit'] ) : null;
$ids  = storebox_core_parse_ids( $args['ids'] );

if ( ! $ids && $unit ) {
	$ids = $unit['images'];
}

if ( ! $ids ) {
	return;
}

$title  = $unit ? $unit['short_title'] : '';
$thumbs = array_slice( $ids, 0, max( 1, absint( $args['thumbs'] ) ) );
$first  = $ids[0];
$uid    = Components::uid( 'sb-gallery' );
$total  = count( $thumbs );

$main_alt = get_post_meta( $first, '_wp_attachment_image_alt', true );
if ( ! $main_alt && $title ) {
	/* translators: %s: unit name and size. */
	$main_alt = sprintf( __( '%s storage unit', 'storebox-core' ), $title );
}
?>
<div class="sb-c sb-gallery sb-s-<?php echo esc_attr( $args['style'] ); ?>" data-sb-component="gallery">
	<div class="sb-gallery__main">
		<?php
		echo wp_get_attachment_image(
			$first,
			$args['image_size'],
			false,
			array(
				'id'            => $uid . '-main',
				'alt'           => $main_alt,
				'loading'       => false,
				'fetchpriority' => 'high',
				'sizes'         => '(max-width: 1000px) 100vw, 720px',
				'data-sb-main'  => '1',
			)
		);
		?>
	</div>
	<?php if ( $total > 1 ) : ?>
		<div class="sb-gallery__thumbs" role="group" aria-label="<?php esc_attr_e( 'Photos', 'storebox-core' ); ?>">
			<?php foreach ( $thumbs as $index => $id ) : ?>
				<?php
				$full   = wp_get_attachment_image_src( $id, $args['image_size'] );
				$srcset = wp_get_attachment_image_srcset( $id, $args['image_size'] );
				$alt    = get_post_meta( $id, '_wp_attachment_image_alt', true );
				if ( ! $full ) {
					continue;
				}
				?>
				<button type="button"
					data-full="<?php echo esc_url( $full[0] ); ?>"
					data-srcset="<?php echo esc_attr( (string) $srcset ); ?>"
					data-alt="<?php echo esc_attr( $alt ? $alt : $main_alt ); ?>"
					aria-controls="<?php echo esc_attr( $uid . '-main' ); ?>"
					aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>">
					<span class="screen-reader-text">
						<?php
						/* translators: 1: photo number, 2: number of photos. */
						echo esc_html( sprintf( __( 'Show photo %1$d of %2$d', 'storebox-core' ), $index + 1, $total ) );
						?>
					</span>
					<?php echo wp_get_attachment_image( $id, 'storebox-thumb', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '180px' ) ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
