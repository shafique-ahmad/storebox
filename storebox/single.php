<?php
/**
 * Single post.
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

		$storebox_category = storebox_primary_category();
		$storebox_soft     = 'soft' === storebox_preset();

		storebox_page_hero(
			array(
				'eyebrow' => $storebox_category ? $storebox_category->name : '',
				'title'   => esc_html( get_the_title() ),
				'lede'    => esc_html( storebox_post_date_read_time() ),
				'short'   => true,
			)
		);
		?>
		<section class="sb-sec sb-sec--article">
			<div class="sb-wrap">
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'sb-article' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="sb-article__img">
							<?php
							the_post_thumbnail(
								'storebox-wide',
								array(
									'loading'       => false,
									'fetchpriority' => 'high',
									'sizes'         => '(max-width: 800px) 100vw, 740px',
								)
							);
							?>
						</figure>
					<?php endif; ?>

					<div class="sb-prose entry-content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<nav class="sb-page-links" aria-label="' . esc_attr__( 'Post pages', 'storebox' ) . '"><span>' . esc_html__( 'Pages:', 'storebox' ) . '</span>',
								'after'  => '</nav>',
							)
						);
						?>
					</div>

					<?php
					$storebox_tags = get_the_tags();
					if ( $storebox_tags ) :
						?>
						<ul class="sb-tags" aria-label="<?php esc_attr_e( 'Tags', 'storebox' ); ?>">
							<?php foreach ( $storebox_tags as $storebox_tag ) : ?>
								<li><a href="<?php echo esc_url( get_tag_link( $storebox_tag ) ); ?>"><?php echo esc_html( $storebox_tag->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php
					storebox_author_box();
					storebox_share_links();

					if ( is_active_sidebar( 'sidebar-blog' ) ) {
						echo '<aside class="sb-post-widgets" aria-label="' . esc_attr__( 'Blog sidebar', 'storebox' ) . '">';
						dynamic_sidebar( 'sidebar-blog' );
						echo '</aside>';
					}

					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</article>
			</div>
		</section>

		<?php
		if ( storebox_get_mod( 'blog_related' ) ) :
			$storebox_related = storebox_get_related_posts( get_the_ID(), 3 );

			if ( $storebox_related ) :
				$storebox_posts_page  = (int) get_option( 'page_for_posts' );
				$storebox_rel_eyebrow = storebox_get_mod( 'blog_related_eyebrow' );
				$storebox_rel_title   = storebox_get_mod( 'blog_related_title' );
				?>
				<section class="sb-sec sb-sec--surface sb-related" aria-labelledby="sb-related-title">
					<div class="sb-wrap">
						<div class="sb-head sb-head--split sb-rv">
							<div>
								<span class="<?php echo $storebox_soft ? 'sb-eyebrow sb-eyebrow--dark' : 'sb-kicker'; ?>"><?php echo esc_html( $storebox_rel_eyebrow ? $storebox_rel_eyebrow : __( 'Keep reading', 'storebox' ) ); ?></span>
								<h2 id="sb-related-title" class="sb-head__title"><?php echo esc_html( $storebox_rel_title ? $storebox_rel_title : __( 'More from the blog.', 'storebox' ) ); ?></h2>
							</div>
							<?php if ( $storebox_posts_page ) : ?>
								<?php echo storebox_button( __( 'All posts', 'storebox' ), get_permalink( $storebox_posts_page ), $storebox_soft ? 'dark' : 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button(). ?>
							<?php endif; ?>
						</div>
						<div class="<?php echo $storebox_soft ? 'sb-posts' : 'sb-plist'; ?>">
							<?php
							foreach ( $storebox_related as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restored by wp_reset_postdata().
								setup_postdata( $post );
								get_template_part( 'template-parts/content/' . ( $storebox_soft ? 'post-card' : 'post-row' ) );
							endforeach;
							wp_reset_postdata();
							?>
						</div>
					</div>
				</section>
				<?php
			endif;
		endif;
	endwhile;

	storebox_cta_band();
endif;

get_footer();
