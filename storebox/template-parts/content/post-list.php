<?php
/**
 * Post listing for the blog, archives and the index fallback.
 *
 * Soft preset: wide feature card, then a card grid.
 * Editorial preset: lead story, then a list of rows.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_soft    = 'soft' === storebox_preset();
$storebox_feature = ! is_paged() && ( is_home() || is_category() || is_tag() );

get_template_part( 'template-parts/content/category-chips' );

if ( have_posts() ) :
	$storebox_index = 0;

	if ( $storebox_soft ) :
		echo '<div class="sb-posts">';
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content/post-card', null, array( 'feature' => $storebox_feature && 0 === $storebox_index ) );
			++$storebox_index;
		endwhile;
		echo '</div>';
	else :
		while ( have_posts() ) :
			the_post();
			if ( $storebox_feature && 0 === $storebox_index ) {
				get_template_part( 'template-parts/content/post-lead' );
			} else {
				if ( ( $storebox_feature && 1 === $storebox_index ) || ( ! $storebox_feature && 0 === $storebox_index ) ) {
					echo '<div class="sb-plist">';
				}
				get_template_part( 'template-parts/content/post-row' );
			}
			++$storebox_index;
		endwhile;
		if ( $storebox_index > ( $storebox_feature ? 1 : 0 ) ) {
			echo '</div>';
		}
	endif;

	storebox_pagination();
else :
	get_template_part( 'template-parts/content/content-none' );
endif;
