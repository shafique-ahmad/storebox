<?php
/**
 * Post card (soft preset). Pass array( 'feature' => true ) for the wide first card
 * and array( 'heading' => 'h3' ) where the list sits under a section heading.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_feature = ! empty( $args['feature'] );
$storebox_heading = isset( $args['heading'] ) && 'h3' === $args['heading'] ? 'h3' : 'h2';
$storebox_classes = array( 'sb-post', 'sb-rv' );
if ( $storebox_feature ) {
	$storebox_classes[] = 'sb-post--feature';
}
if ( ! has_post_thumbnail() ) {
	$storebox_classes[] = 'sb-post--no-image';
}
?>
<article <?php post_class( $storebox_classes ); ?>>
	<a class="sb-post__link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="sb-post__img">
				<?php
				the_post_thumbnail(
					$storebox_feature ? 'storebox-wide' : 'storebox-card',
					array(
						'alt'   => '',
						'sizes' => $storebox_feature ? '(max-width: 1000px) 100vw, 700px' : '(max-width: 680px) 100vw, (max-width: 1000px) 50vw, 400px',
					)
				);
				?>
			</div>
		<?php endif; ?>
		<div class="sb-post__body">
			<?php storebox_post_meta(); ?>
			<<?php echo esc_attr( $storebox_heading ); ?> class="sb-post__title"><?php the_title(); ?></<?php echo esc_attr( $storebox_heading ); ?>>
			<p class="sb-post__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		</div>
	</a>
</article>
