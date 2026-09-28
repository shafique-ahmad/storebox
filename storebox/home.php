<?php
/**
 * Blog posts page.
 *
 * Header content comes from the page assigned as "Posts page": its title, its
 * excerpt (as the intro) and its featured image.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'archive' ) ) :
	$storebox_posts_page = (int) get_option( 'page_for_posts' );

	$storebox_image = $storebox_posts_page ? get_post_thumbnail_id( $storebox_posts_page ) : 0;
	if ( ! $storebox_image ) {
		$storebox_image = absint( storebox_get_mod( 'hero_image' ) );
	}

	$storebox_hero = $storebox_posts_page
		? storebox_page_hero_text( $storebox_posts_page )
		: array(
			'eyebrow' => '',
			'title'   => esc_html__( 'Latest posts', 'storebox' ),
			'lede'    => '',
		);

	storebox_page_hero(
		array_merge(
			$storebox_hero,
			array(
				'image_id' => $storebox_image,
				'split'    => true,
			)
		)
	);
	?>
	<section class="sb-sec">
		<div class="sb-wrap">
			<?php get_template_part( 'template-parts/content/post-list' ); ?>
		</div>
	</section>
	<?php
	storebox_cta_band();
endif;

get_footer();
