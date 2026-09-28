<?php
/**
 * Post row for list layouts (editorial preset and search results). Pass
 * array( 'heading' => 'h3' ) where the list sits under a section heading.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_heading = isset( $args['heading'] ) && 'h3' === $args['heading'] ? 'h3' : 'h2';
?>
<article <?php post_class( 'sb-prow' ); ?>>
	<a class="sb-prow__link" href="<?php the_permalink(); ?>">
		<div class="sb-prow__body">
			<?php
			if ( 'post' === get_post_type() ) {
				storebox_post_meta();
			} else {
				printf( '<div class="sb-meta"><span class="sb-meta__cat">%s</span></div>', esc_html( get_post_type_object( get_post_type() )->labels->singular_name ) );
			}
			?>
			<<?php echo esc_attr( $storebox_heading ); ?> class="sb-prow__title"><?php the_title(); ?></<?php echo esc_attr( $storebox_heading ); ?>>
			<p class="sb-prow__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="sb-prow__img">
				<?php
				the_post_thumbnail(
					'storebox-thumb',
					array(
						'alt'   => '',
						'sizes' => '(max-width: 680px) 100vw, 200px',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</a>
</article>
