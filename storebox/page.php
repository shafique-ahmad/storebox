<?php
/**
 * Pages. Pages built with Elementor render edge to edge; other pages get a
 * page header and a readable content column.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'single' ) ) :
	while ( have_posts() ) :
		the_post();

		if ( storebox_is_built_with_elementor() ) {
			the_content();
			continue;
		}

		if ( ! get_post_meta( get_the_ID(), '_storebox_hide_hero', true ) ) {
			$storebox_image = get_post_thumbnail_id();

			storebox_page_hero(
				array_merge(
					storebox_page_hero_text( get_the_ID() ),
					array(
						'image_id' => $storebox_image ? $storebox_image : absint( storebox_get_mod( 'hero_image' ) ),
						'split'    => (bool) $storebox_image,
					)
				)
			);
		}
		?>
		<section class="sb-sec">
			<div class="sb-wrap">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'sb-page-content' ); ?>>
					<div class="sb-prose entry-content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<nav class="sb-page-links" aria-label="' . esc_attr__( 'Page sections', 'storebox' ) . '"><span>' . esc_html__( 'Pages:', 'storebox' ) . '</span>',
								'after'  => '</nav>',
							)
						);
						?>
					</div>
					<?php
					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</article>
			</div>
		</section>
		<?php
	endwhile;
endif;

get_footer();
