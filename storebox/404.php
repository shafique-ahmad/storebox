<?php
/**
 * 404 page. An Elementor Pro 404 template replaces this when assigned.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'single' ) ) :
	$storebox_title = storebox_get_mod( 'notfound_title' );
	$storebox_text  = storebox_get_mod( 'notfound_text' );
	$storebox_soft  = 'soft' === storebox_preset();

	/**
	 * Filters the secondary 404 button. Storebox Core points it at the Units page.
	 *
	 * @param array $link Array with "text" and "url"; an empty URL shows a search form instead.
	 */
	$storebox_second = apply_filters(
		'storebox_404_secondary_link',
		array(
			'text' => '',
			'url'  => '',
		)
	);
	?>
	<section class="sb-nf<?php echo $storebox_soft ? ' sb-nf--soft' : ' sb-nf--editorial'; ?>">
		<?php if ( $storebox_soft ) : ?>
			<div class="sb-nf__doors" aria-hidden="true"><i></i><i></i><i></i></div>
		<?php endif; ?>
		<div class="sb-wrap sb-nf__in">
			<p class="sb-nf__code" aria-hidden="true"><?php echo $storebox_soft ? '404' : '<mark>404</mark>'; ?></p>
			<h1 class="sb-nf__title"><?php echo esc_html( $storebox_title ? $storebox_title : __( 'This unit is empty.', 'storebox' ) ); ?></h1>
			<p class="sb-lede"><?php echo esc_html( $storebox_text ? $storebox_text : __( 'The page you were after has moved, or never existed. Everything else is where you left it.', 'storebox' ) ); ?></p>
			<div class="sb-nf__links">
				<?php
				echo storebox_button( __( 'Back to the homepage', 'storebox' ), home_url( '/' ), $storebox_soft ? 'primary' : 'dark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button().
				if ( ! empty( $storebox_second['url'] ) ) {
					echo storebox_button( $storebox_second['text'], $storebox_second['url'], $storebox_soft ? 'ghost' : 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button().
				}
				?>
			</div>
			<?php if ( empty( $storebox_second['url'] ) ) : ?>
				<div class="sb-nf__search"><?php get_search_form(); ?></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
endif;

get_footer();
