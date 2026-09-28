<?php
/**
 * Comments list and form.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="sb-comments" aria-labelledby="sb-comments-title">
	<?php if ( have_comments() ) : ?>
		<h2 id="sb-comments-title" class="sb-comments__title">
			<?php
			$storebox_comment_count = get_comments_number();
			printf(
				/* translators: 1: number of comments, 2: post title. */
				esc_html( _nx( '%1$s comment on “%2$s”', '%1$s comments on “%2$s”', $storebox_comment_count, 'comments title', 'storebox' ) ),
				esc_html( number_format_i18n( $storebox_comment_count ) ),
				'<span>' . esc_html( get_the_title() ) . '</span>'
			);
			?>
		</h2>

		<ol class="sb-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older comments', 'storebox' ),
				'next_text' => esc_html__( 'Newer comments', 'storebox' ),
			)
		);

		if ( ! comments_open() ) :
			?>
			<p class="sb-comments__closed"><?php esc_html_e( 'Comments are closed.', 'storebox' ); ?></p>
			<?php
		endif;
	else :
		?>
		<h2 id="sb-comments-title" class="screen-reader-text"><?php esc_html_e( 'Comments', 'storebox' ); ?></h2>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'         => 'sb-comment-form',
			'title_reply_before' => '<h2 id="reply-title" class="sb-comments__title">',
			'title_reply_after'  => '</h2>',
			'class_submit'       => 'sb-btn sb-btn--primary',
		)
	);
	?>
</section>
