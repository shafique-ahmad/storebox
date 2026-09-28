<?php
/**
 * Post grid / list with optional feature post, category links and pagination.
 *
 * Sources: latest posts, posts of chosen categories, the current archive query
 * (for Theme Builder archive templates) or related posts (single posts).
 *
 * @package Storebox_Core
 *
 * @var array $args Component arguments.
 */

use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

global $wp_query;

$style   = $args['style'];
$layout  = Components::auto( $args['layout'], $style, 'grid', 'list' );
$columns = max( 1, min( 4, absint( $args['columns'] ) ) );
$heading = in_array( $args['heading_tag'], array( 'h2', 'h3', 'h4' ), true ) ? $args['heading_tag'] : 'h3';
$paged   = max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
$count   = max( 1, absint( $args['count'] ) );

if ( 'current' === $args['source'] && $wp_query instanceof WP_Query ) {
	$query = $wp_query;
} else {
	$query_args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
	);

	if ( 'related' === $args['source'] ) {
		$current  = get_the_ID();
		$category = $current ? storebox_core_primary_category( $current ) : null;

		$query_args['post__not_in']  = array( $current );
		$query_args['no_found_rows'] = true;
		if ( $category ) {
			$query_args['cat'] = $category->term_id;
		}
	} else {
		if ( 'category' === $args['source'] && $args['category'] ) {
			$query_args['category__in'] = array_map( 'absint', (array) $args['category'] );
		}
		if ( $args['pagination'] ) {
			$query_args['paged'] = $paged;
			if ( absint( $args['offset'] ) ) {
				$query_args['offset'] = absint( $args['offset'] ) + ( $paged - 1 ) * $count;
			}
		} else {
			$query_args['no_found_rows'] = true;
			$query_args['offset']        = absint( $args['offset'] );
		}
	}

	$query = new WP_Query( apply_filters( 'storebox_core/post_grid_query_args', $query_args, $args ) );

	// Related posts: top up with the latest posts when the category is small.
	if ( 'related' === $args['source'] && $query->post_count < $count ) {
		$exclude = array_merge( array( get_the_ID() ), wp_list_pluck( $query->posts, 'ID' ) );
		$extra   = get_posts(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => $count - $query->post_count,
				'post__not_in'        => $exclude,
				'ignore_sticky_posts' => true,
			)
		);
		$query->posts      = array_merge( $query->posts, $extra );
		$query->post_count = count( $query->posts );
	}
}

if ( ! $query->have_posts() ) {
	return;
}

$feature_first = $args['featured'] && 1 === $paged && 'related' !== $args['source'];

/**
 * Prints the meta line for the current post.
 *
 * @param array $args Component arguments.
 */
$meta_line = static function ( $args ) {
	if ( ! $args['show_meta'] ) {
		return;
	}
	$parts    = array();
	$category = storebox_core_primary_category( get_the_ID() );
	if ( $category ) {
		$parts[] = '<span class="sb-meta__cat">' . esc_html( $category->name ) . '</span>';
	}
	$parts[] = '<time datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>';
	if ( $args['show_read_time'] ) {
		$minutes = storebox_core_read_time( get_the_ID() );
		/* translators: %d: minutes to read. */
		$parts[] = '<span>' . esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'storebox-core' ), $minutes ) ) . '</span>';
	}
	echo '<div class="sb-meta">' . implode( '', $parts ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
};

$excerpt = static function ( $args ) {
	if ( ! $args['show_excerpt'] ) {
		return '';
	}
	$text = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), absint( $args['excerpt_length'] ) );

	return $text;
};
?>
<div class="sb-c sb-pgrid sb-pgrid--<?php echo esc_attr( $layout ); ?> sb-s-<?php echo esc_attr( $style ); ?>" style="--sb-cols: <?php echo esc_attr( $columns ); ?>">
	<?php
	if ( $args['chips'] ) :
		$categories = get_categories(
			array(
				'hide_empty' => true,
				'exclude'    => array( (int) get_option( 'default_category' ) ),
			)
		);
		if ( count( $categories ) > 1 ) :
			$posts_page = (int) get_option( 'page_for_posts' );
			?>
			<nav class="sb-chips" aria-label="<?php esc_attr_e( 'Blog categories', 'storebox-core' ); ?>">
				<a class="sb-chip" href="<?php echo esc_url( $posts_page ? get_permalink( $posts_page ) : home_url( '/' ) ); ?>"<?php echo ( is_home() || ( ! is_category() && ! is_archive() ) ) ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All', 'storebox-core' ); ?></a>
				<?php foreach ( $categories as $category ) : ?>
					<a class="sb-chip" href="<?php echo esc_url( get_category_link( $category ) ); ?>"<?php echo is_category( $category->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $category->name ); ?></a>
				<?php endforeach; ?>
			</nav>
			<?php
		endif;
	endif;
	?>

	<?php
	$index    = 0;
	$list_open = false;

	if ( 'grid' === $layout ) {
		echo '<div class="sb-pgrid__items">';
	}

	while ( $query->have_posts() ) :
		$query->the_post();
		$is_feature = $feature_first && 0 === $index;
		$has_image  = $args['show_image'] && has_post_thumbnail();

		if ( 'grid' === $layout ) :
			?>
			<article <?php post_class( 'sb-post' . ( $is_feature ? ' sb-post--feature' : '' ) . ( $has_image ? '' : ' sb-post--no-image' ) ); ?>>
				<a class="sb-post__link" href="<?php the_permalink(); ?>">
					<?php if ( $has_image ) : ?>
						<div class="sb-post__img"><?php the_post_thumbnail( $is_feature ? 'storebox-wide' : 'storebox-card', array( 'alt' => '', 'sizes' => $is_feature ? '(max-width: 1000px) 100vw, 700px' : '(max-width: 680px) 100vw, (max-width: 1000px) 50vw, 400px' ) ); ?></div>
					<?php endif; ?>
					<div class="sb-post__body">
						<?php $meta_line( $args ); ?>
						<<?php echo esc_html( $heading ); ?> class="sb-post__title"><?php the_title(); ?></<?php echo esc_html( $heading ); ?>>
						<?php $text = $excerpt( $args ); ?>
						<?php if ( $text ) : ?>
							<p class="sb-post__excerpt"><?php echo esc_html( $text ); ?></p>
						<?php endif; ?>
					</div>
				</a>
			</article>
			<?php
		elseif ( $is_feature ) :
			?>
			<article <?php post_class( 'sb-lead' ); ?>>
				<?php if ( $has_image ) : ?>
					<a class="sb-lead__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'storebox-wide', array( 'alt' => '', 'sizes' => '(max-width: 1080px) 100vw, 680px' ) ); ?></a>
				<?php endif; ?>
				<div class="sb-lead__body">
					<?php $meta_line( $args ); ?>
					<h2 class="sb-lead__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php $text = $excerpt( $args ); ?>
					<?php if ( $text ) : ?>
						<p class="sb-lede"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>
					<p class="sb-lead__more"><a class="sb-link" href="<?php the_permalink(); ?>"><?php echo esc_html( $args['read_more'] ? $args['read_more'] : __( 'Read the article', 'storebox-core' ) ); ?><span class="screen-reader-text">: <?php the_title(); ?></span></a></p>
				</div>
			</article>
			<?php
		else :
			if ( ! $list_open ) {
				echo '<div class="sb-pgrid__list">';
				$list_open = true;
			}
			?>
			<article <?php post_class( 'sb-prow' ); ?>>
				<a class="sb-prow__link" href="<?php the_permalink(); ?>">
					<div class="sb-prow__body">
						<?php $meta_line( $args ); ?>
						<<?php echo esc_html( $heading ); ?> class="sb-prow__title"><?php the_title(); ?></<?php echo esc_html( $heading ); ?>>
						<?php $text = $excerpt( $args ); ?>
						<?php if ( $text ) : ?>
							<p class="sb-prow__excerpt"><?php echo esc_html( $text ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $has_image ) : ?>
						<div class="sb-prow__img"><?php the_post_thumbnail( 'storebox-thumb', array( 'alt' => '', 'sizes' => '(max-width: 680px) 100vw, 200px' ) ); ?></div>
					<?php endif; ?>
				</a>
			</article>
			<?php
		endif;

		++$index;
	endwhile;

	if ( 'grid' === $layout || $list_open ) {
		echo '</div>';
	}

	if ( $args['pagination'] || 'current' === $args['source'] ) {
		$links = paginate_links(
			array(
				'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
				'format'    => '',
				'current'   => $paged,
				'total'     => (int) $query->max_num_pages,
				'type'      => 'array',
				'mid_size'  => 1,
				'prev_text' => esc_html__( 'Previous', 'storebox-core' ),
				'next_text' => esc_html__( 'Next', 'storebox-core' ),
			)
		);
		if ( $links ) {
			echo '<nav class="sb-pager" aria-label="' . esc_attr__( 'Posts pages', 'storebox-core' ) . '">';
			foreach ( $links as $link ) {
				echo wp_kses_post( $link );
			}
			echo '</nav>';
		}
	}

	if ( $query !== $wp_query ) {
		wp_reset_postdata();
	} else {
		rewind_posts();
	}
	?>
</div>
