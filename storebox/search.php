<?php
/**
 * Search results.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'archive' ) ) :
	global $wp_query;

	$storebox_count = (int) $wp_query->found_posts;

	storebox_page_hero(
		array(
			'eyebrow'  => esc_html__( 'Search', 'storebox' ),
			/* translators: %s: search terms. */
			'title'    => sprintf( esc_html__( 'Results for “%s”', 'storebox' ), esc_html( get_search_query() ) ),
			/* translators: %d: number of results. */
			'lede'     => esc_html( sprintf( _n( '%d result', '%d results', $storebox_count, 'storebox' ), $storebox_count ) ),
			'image_id' => absint( storebox_get_mod( 'hero_image' ) ),
			'short'    => true,
		)
	);
	?>
	<section class="sb-sec">
		<div class="sb-wrap sb-search">
			<div class="sb-search__form"><?php get_search_form(); ?></div>
			<?php if ( have_posts() ) : ?>
				<div class="sb-plist">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content/post-row' );
					endwhile;
					?>
				</div>
				<?php storebox_pagination(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content/content-none' ); ?>
			<?php endif; ?>
		</div>
	</section>
	<?php
	storebox_cta_band();
endif;

get_footer();
