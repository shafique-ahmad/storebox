<?php
/**
 * Fallback template.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'archive' ) ) :
	storebox_page_hero(
		array(
			'eyebrow'  => is_home() ? '' : wp_strip_all_tags( get_the_archive_title() ),
			'title'    => is_home() ? esc_html__( 'Latest posts', 'storebox' ) : wp_strip_all_tags( get_the_archive_title() ),
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
