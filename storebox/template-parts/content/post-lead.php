<?php
/**
 * Lead story (editorial preset): large image beside the title.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'sb-lead sb-rv' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="sb-lead__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'storebox-wide', array( 'alt' => '', 'sizes' => '(max-width: 1080px) 100vw, 680px' ) ); ?>
		</a>
	<?php endif; ?>
	<div class="sb-lead__body">
		<?php storebox_post_meta(); ?>
		<h2 class="sb-lead__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="sb-lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<p class="sb-lead__more">
			<a class="sb-link" href="<?php the_permalink(); ?>">
				<?php
				/* translators: %s: post title, visually hidden. */
				printf( esc_html__( 'Read the article%s', 'storebox' ), '<span class="screen-reader-text">: ' . esc_html( get_the_title() ) . '</span>' );
				?>
			</a>
		</p>
	</div>
</article>
