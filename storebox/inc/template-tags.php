<?php
/**
 * Template tags used by the theme templates.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'storebox_site_logo' ) ) {
	/**
	 * Prints the site logo.
	 *
	 * @param string $variant "default" or "light" (for dark backgrounds).
	 */
	function storebox_site_logo( $variant = 'default' ) {
		$light_id = absint( storebox_get_mod( 'logo_light' ) );

		if ( 'light' === $variant && $light_id ) {
			printf(
				'<a href="%1$s" class="sb-logo sb-logo--image" rel="home">%2$s</a>',
				esc_url( home_url( '/' ) ),
				wp_get_attachment_image(
					$light_id,
					'full',
					false,
					array(
						'class'   => 'sb-logo__img',
						'alt'     => get_bloginfo( 'name', 'display' ),
						'loading' => false,
					)
				)
			);
			return;
		}

		if ( has_custom_logo() ) {
			echo '<span class="sb-logo sb-logo--image">';
			the_custom_logo();
			echo '</span>';
			return;
		}

		printf(
			'<a href="%1$s" class="sb-logo" rel="home"><span class="sb-logo__mark" aria-hidden="true"><i></i><i></i><i></i></span><span class="sb-logo__text">%2$s</span></a>',
			esc_url( home_url( '/' ) ),
			esc_html( get_bloginfo( 'name', 'display' ) )
		);
	}
}

/**
 * Breadcrumb items for the current request.
 *
 * @return array<int, array{label: string, url: string}>
 */
function storebox_breadcrumb_items() {
	$items = array(
		array(
			'label' => __( 'Home', 'storebox' ),
			'url'   => home_url( '/' ),
		),
	);

	$posts_page = (int) get_option( 'page_for_posts' );
	$blog_item  = $posts_page ? array(
		'label' => get_the_title( $posts_page ),
		'url'   => get_permalink( $posts_page ),
	) : null;

	if ( is_front_page() ) {
		return array();
	}

	if ( is_home() ) {
		$items[] = array(
			'label' => $posts_page ? get_the_title( $posts_page ) : __( 'Blog', 'storebox' ),
			'url'   => '',
		);
	} elseif ( is_singular( 'post' ) ) {
		if ( $blog_item ) {
			$items[] = $blog_item;
		}
		$category = storebox_primary_category();
		if ( $category ) {
			$items[] = array(
				'label' => $category->name,
				'url'   => get_category_link( $category ),
			);
		}
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$items[] = array(
				'label' => get_the_title( $ancestor ),
				'url'   => get_permalink( $ancestor ),
			);
		}
		$items[] = array(
			'label' => single_post_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_singular() ) {
		$items[] = array(
			'label' => single_post_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'label' => __( 'Search', 'storebox' ),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'label' => __( 'Page not found', 'storebox' ),
			'url'   => '',
		);
	} elseif ( is_archive() ) {
		if ( $blog_item && ( is_category() || is_tag() || is_author() || is_date() ) ) {
			$items[] = $blog_item;
		}
		$items[] = array(
			'label' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => '',
		);
	}

	/**
	 * Filters the breadcrumb trail. Storebox Core adds the Units and
	 * Locations pages in front of single units and locations.
	 *
	 * @param array $items Items with "label" and "url" keys; an empty URL marks the current page.
	 */
	return apply_filters( 'storebox_breadcrumb_items', $items );
}

if ( ! function_exists( 'storebox_breadcrumbs' ) ) {
	/**
	 * Prints breadcrumbs. Uses Yoast SEO or Rank Math breadcrumbs when those
	 * are enabled, so their structured data is not duplicated.
	 */
	function storebox_breadcrumbs() {
		if ( ! storebox_get_mod( 'breadcrumbs' ) || is_front_page() ) {
			return;
		}

		if ( function_exists( 'yoast_breadcrumb' ) && class_exists( 'WPSEO_Options' ) && WPSEO_Options::get( 'breadcrumbs-enable' ) ) {
			yoast_breadcrumb( '<nav class="sb-crumbs sb-crumbs--plugin" aria-label="' . esc_attr__( 'Breadcrumb', 'storebox' ) . '">', '</nav>' );
			return;
		}

		if ( function_exists( 'rank_math_the_breadcrumbs' ) && class_exists( '\RankMath\Helper' ) && \RankMath\Helper::is_breadcrumbs_enabled() ) {
			echo '<nav class="sb-crumbs sb-crumbs--plugin" aria-label="' . esc_attr__( 'Breadcrumb', 'storebox' ) . '">';
			rank_math_the_breadcrumbs();
			echo '</nav>';
			return;
		}

		$items = storebox_breadcrumb_items();
		if ( count( $items ) < 2 ) {
			return;
		}

		echo '<nav class="sb-crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'storebox' ) . '"><ol>';
		foreach ( $items as $item ) {
			if ( '' === $item['url'] ) {
				printf( '<li><span aria-current="page">%s</span></li>', esc_html( $item['label'] ) );
			} else {
				printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
			}
		}
		echo '</ol></nav>';
	}
}

/**
 * Prints the page header (hero) used by theme templates.
 *
 * @param array $args {
 *     Page header options.
 *
 *     @type string $eyebrow  Small label above the title.
 *     @type string $title    Title (plain text or limited HTML).
 *     @type string $lede     Intro text.
 *     @type int    $image_id Attachment ID (soft: background; editorial: beside the title).
 *     @type bool   $short    Shorter header without background image (soft).
 *     @type bool   $split    Show the image beside the title (editorial).
 * }
 */
function storebox_page_hero( $args = array() ) {
	get_template_part( 'template-parts/hero/page-hero', null, $args );
}

/**
 * Post meta line: category, date and reading time.
 *
 * @param int   $post_id Post ID.
 * @param array $parts   Any of "category", "date", "read_time".
 * @param bool  $link_category Whether the category links to its archive.
 */
function storebox_post_meta( $post_id = 0, $parts = array( 'category', 'date', 'read_time' ), $link_category = false ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$out     = array();

	if ( in_array( 'category', $parts, true ) ) {
		$category = storebox_primary_category( $post_id );
		if ( $category ) {
			$out[] = $link_category
				? sprintf( '<a class="sb-meta__cat" href="%1$s">%2$s</a>', esc_url( get_category_link( $category ) ), esc_html( $category->name ) )
				: sprintf( '<span class="sb-meta__cat">%s</span>', esc_html( $category->name ) );
		}
	}

	if ( in_array( 'date', $parts, true ) ) {
		$out[] = sprintf(
			'<time datetime="%1$s">%2$s</time>',
			esc_attr( get_the_date( DATE_W3C, $post_id ) ),
			esc_html( get_the_date( '', $post_id ) )
		);
	}

	if ( in_array( 'read_time', $parts, true ) && storebox_get_mod( 'blog_read_time' ) ) {
		$minutes = storebox_read_time( $post_id );
		/* translators: %d: minutes needed to read the post. */
		$out[] = '<span>' . esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'storebox' ), $minutes ) ) . '</span>';
	}

	if ( $out ) {
		echo '<div class="sb-meta">' . implode( '', $out ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Parts escaped above.
	}
}

/**
 * Plain-text version of the date and reading time ("14 Sep 2026 · 6 min read").
 *
 * @param int $post_id Post ID.
 * @return string
 */
function storebox_post_date_read_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$parts   = array( get_the_date( '', $post_id ) );

	if ( storebox_get_mod( 'blog_read_time' ) ) {
		$minutes = storebox_read_time( $post_id );
		/* translators: %d: minutes needed to read the post. */
		$parts[] = sprintf( _n( '%d min read', '%d min read', $minutes, 'storebox' ), $minutes );
	}

	return implode( ' · ', $parts );
}

/**
 * Prints numbered pagination for the main query.
 */
function storebox_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'array',
			'mid_size'  => 1,
			'prev_text' => esc_html__( 'Previous', 'storebox' ),
			'next_text' => esc_html__( 'Next', 'storebox' ),
		)
	);

	if ( ! $links ) {
		return;
	}

	echo '<nav class="sb-pager" aria-label="' . esc_attr__( 'Posts pages', 'storebox' ) . '">';
	foreach ( $links as $link ) {
		echo wp_kses_post( $link );
	}
	echo '</nav>';
}

/**
 * Prints the author box below a post.
 *
 * @param int $post_id Post ID.
 */
function storebox_author_box( $post_id = 0 ) {
	$post_id   = $post_id ? $post_id : get_the_ID();
	$author_id = (int) get_post_field( 'post_author', $post_id );

	if ( ! $author_id || ! storebox_get_mod( 'blog_author_box' ) ) {
		return;
	}

	$name = get_the_author_meta( 'display_name', $author_id );
	$bio  = get_the_author_meta( 'description', $author_id );
	?>
	<div class="sb-author">
		<span class="sb-avatar" aria-hidden="true"><?php echo esc_html( storebox_initials( $name ) ); ?></span>
		<div class="sb-author__body">
			<b class="sb-author__name"><a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $name ); ?></a></b>
			<?php if ( $bio ) : ?>
				<span class="sb-author__bio"><?php echo esc_html( $bio ); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Prints share links for a post.
 *
 * @param int $post_id Post ID.
 */
function storebox_share_links( $post_id = 0 ) {
	if ( ! storebox_get_mod( 'blog_share' ) ) {
		return;
	}

	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = rawurlencode( get_permalink( $post_id ) );
	$title   = rawurlencode( html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' ) );

	$links = array(
		'facebook' => array( 'https://www.facebook.com/sharer/sharer.php?u=' . $url, __( 'Share on Facebook', 'storebox' ) ),
		'x'        => array( 'https://x.com/intent/post?url=' . $url . '&text=' . $title, __( 'Share on X', 'storebox' ) ),
		'linkedin' => array( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url, __( 'Share on LinkedIn', 'storebox' ) ),
		'mail'     => array( 'mailto:?subject=' . $title . '&body=' . $url, __( 'Share by email', 'storebox' ) ),
	);

	echo '<div class="sb-share"><span class="sb-share__label">' . esc_html__( 'Share', 'storebox' ) . '</span>';
	foreach ( $links as $icon => $link ) {
		printf(
			'<a href="%1$s" aria-label="%2$s"%3$s>%4$s</a>',
			esc_url( $link[0] ),
			esc_attr( $link[1] ),
			'mail' === $icon ? '' : ' target="_blank" rel="noopener noreferrer"',
			storebox_icon( $icon, 'mail' === $icon ? 18 : 16 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
		);
	}
	echo '</div>';
}

/**
 * Related posts: same category first, then the latest posts.
 *
 * @param int $post_id Post ID.
 * @param int $count   Number of posts.
 * @return WP_Post[]
 */
function storebox_get_related_posts( $post_id = 0, $count = 3 ) {
	$post_id  = $post_id ? $post_id : get_the_ID();
	$category = storebox_primary_category( $post_id );
	$args     = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	$related = array();
	if ( $category ) {
		$related = get_posts( array_merge( $args, array( 'cat' => $category->term_id ) ) );
	}

	if ( count( $related ) < $count ) {
		$args['post__not_in']   = array_merge( array( $post_id ), wp_list_pluck( $related, 'ID' ) );
		$args['posts_per_page'] = $count - count( $related );
		$related                = array_merge( $related, get_posts( $args ) );
	}

	return apply_filters( 'storebox_related_posts', $related, $post_id );
}

/**
 * Prints social profile links from the Customizer.
 *
 * @param string $extra_class Extra wrapper class.
 */
function storebox_social_links( $extra_class = '' ) {
	$networks = array(
		'facebook'  => __( 'Facebook', 'storebox' ),
		'instagram' => __( 'Instagram', 'storebox' ),
		'linkedin'  => __( 'LinkedIn', 'storebox' ),
		'x'         => __( 'X', 'storebox' ),
		'youtube'   => __( 'YouTube', 'storebox' ),
	);

	$links = '';
	foreach ( $networks as $network => $label ) {
		$url = storebox_get_mod( 'social_' . $network );
		if ( $url ) {
			$links .= sprintf(
				'<a href="%1$s" aria-label="%2$s" target="_blank" rel="noopener noreferrer me">%3$s</a>',
				esc_url( $url ),
				esc_attr( $label ),
				storebox_icon( $network, 16 )
			);
		}
	}

	if ( $links ) {
		echo '<div class="sb-social ' . esc_attr( $extra_class ) . '">' . $links . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from escaped parts.
	}
}

/**
 * Prints the closing call-to-action band on theme templates.
 */
function storebox_cta_band() {
	if ( ! storebox_get_mod( 'cta_enable' ) || '' === trim( (string) storebox_get_mod( 'cta_title' ) ) ) {
		return;
	}

	get_template_part( 'template-parts/cta/cta-band' );
}

/**
 * Button markup used by theme templates.
 *
 * @param string $text    Button text.
 * @param string $url     Link.
 * @param string $variant primary | dark | ghost | outline.
 * @param bool   $arrow   Append an arrow icon.
 * @return string
 */
function storebox_button( $text, $url, $variant = 'primary', $arrow = false ) {
	if ( '' === trim( (string) $text ) || '' === trim( (string) $url ) ) {
		return '';
	}

	return sprintf(
		'<a class="sb-btn sb-btn--%1$s" href="%2$s">%3$s%4$s</a>',
		esc_attr( $variant ),
		esc_url( $url ),
		esc_html( $text ),
		$arrow ? storebox_icon( 'arrow-right', 17, array( 'stroke_width' => 2.5 ) ) : ''
	);
}
