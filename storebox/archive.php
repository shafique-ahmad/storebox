<?php
/**
 * Archives: categories, tags, authors, dates and post type archives.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'archive' ) ) :
	if ( is_category() ) {
		$storebox_eyebrow = esc_html__( 'Category', 'storebox' );
	} elseif ( is_tag() ) {
		$storebox_eyebrow = esc_html__( 'Tag', 'storebox' );
	} elseif ( is_author() ) {
		$storebox_eyebrow = esc_html__( 'Author', 'storebox' );
	} elseif ( is_date() ) {
		$storebox_eyebrow = esc_html__( 'Archive', 'storebox' );
	} elseif ( is_post_type_archive() ) {
		$storebox_eyebrow = '';
	} elseif ( is_tax() ) {
		$storebox_taxonomy = get_taxonomy( get_queried_object()->taxonomy );
		$storebox_eyebrow  = $storebox_taxonomy ? $storebox_taxonomy->labels->singular_name : '';
	} else {
		$storebox_eyebrow = '';
	}

	storebox_page_hero(
		array(
			'eyebrow'  => $storebox_eyebrow,
			'title'    => wp_strip_all_tags( get_the_archive_title() ),
			'lede'     => wp_strip_all_tags( get_the_archive_description() ),
			'image_id' => absint( storebox_get_mod( 'hero_image' ) ),
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
