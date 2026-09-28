<?php
/**
 * Shown when a query has no posts.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="sb-empty">
	<?php if ( is_search() ) : ?>
		<h2 class="sb-empty__title"><?php esc_html_e( 'Nothing matched your search.', 'storebox' ); ?></h2>
		<p><?php esc_html_e( 'Try different words, or browse the latest posts instead.', 'storebox' ); ?></p>
		<?php get_search_form(); ?>
	<?php elseif ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
		<h2 class="sb-empty__title"><?php esc_html_e( 'No posts yet.', 'storebox' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: %s: link to the new post screen. */
				esc_html__( 'Ready to publish your first post? %s', 'storebox' ),
				'<a href="' . esc_url( admin_url( 'post-new.php' ) ) . '">' . esc_html__( 'Get started here', 'storebox' ) . '</a>'
			);
			?>
		</p>
	<?php else : ?>
		<h2 class="sb-empty__title"><?php esc_html_e( 'Nothing here yet.', 'storebox' ); ?></h2>
		<p><?php esc_html_e( 'It seems we can’t find what you’re looking for. Perhaps searching can help.', 'storebox' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
