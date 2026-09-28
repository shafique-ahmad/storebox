<?php
/**
 * Single storage unit (Storebox Core). Used when no Elementor Pro single
 * template is assigned; every block is a Storebox Core component, so the page
 * updates from the unit's fields.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'single' ) ) :
	while ( have_posts() ) :
		the_post();

		if ( storebox_is_built_with_elementor() || ! function_exists( 'storebox_core_get_unit' ) ) {
			the_content();
			continue;
		}

		$storebox_unit = storebox_core_get_unit( get_the_ID() );
		$storebox_soft = 'soft' === storebox_preset();

		storebox_page_hero(
			array(
				'eyebrow' => $storebox_unit['type_label'],
				'title'   => esc_html( $storebox_unit['display_title'] ),
				'lede'    => esc_html( $storebox_unit['summary'] ),
				'short'   => true,
			)
		);
		?>
		<section class="sb-sec sb-sec--unit">
			<div class="sb-wrap sb-unit-layout">
				<div class="sb-unit-layout__main">
					<?php
					storebox_core_component( 'unit-gallery', array( 'unit' => $storebox_unit['id'] ) );
					storebox_core_component( 'unit-specs', array( 'unit' => $storebox_unit['id'] ) );
					?>

					<div class="sb-prose entry-content"><?php the_content(); ?></div>

					<h2 class="sb-unit-layout__heading"><?php esc_html_e( 'This unit has', 'storebox' ); ?></h2>
					<?php storebox_core_component( 'unit-features', array( 'unit' => $storebox_unit['id'] ) ); ?>

					<?php
					$storebox_info = storebox_core_setting( 'reservation_info' );
					if ( $storebox_info ) {
						echo '<div class="sb-prose sb-unit-layout__info">' . wp_kses_post( wpautop( $storebox_info ) ) . '</div>';
					}

					storebox_core_component(
						'enquiry-form',
						array(
							'type'    => 'auto',
							'unit'    => $storebox_unit['id'],
							'form_id' => 'reserve',
						)
					);
					?>
				</div>

				<aside class="sb-unit-layout__aside" aria-label="<?php esc_attr_e( 'Price and booking', 'storebox' ); ?>">
					<?php
					storebox_core_component(
						'unit-booking',
						array(
							'unit' => $storebox_unit['id'],
							'help' => true,
						)
					);
					?>
				</aside>
			</div>
		</section>

		<section class="sb-sec sb-sec--surface" aria-labelledby="sb-similar-title">
			<div class="sb-wrap">
				<div class="sb-head sb-head--split sb-rv">
					<div>
						<span class="<?php echo $storebox_soft ? 'sb-eyebrow sb-eyebrow--dark' : 'sb-kicker'; ?>"><?php esc_html_e( 'Similar sizes', 'storebox' ); ?></span>
						<h2 id="sb-similar-title" class="sb-head__title"><?php esc_html_e( 'Or a little bigger, or smaller.', 'storebox' ); ?></h2>
					</div>
					<?php
					$storebox_units_url = storebox_core_page_url( 'units' );
					if ( $storebox_units_url ) {
						echo storebox_button( __( 'All units', 'storebox' ), $storebox_units_url, $storebox_soft ? 'dark' : 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button().
					}
					?>
				</div>
				<?php
				storebox_core_component(
					'unit-grid',
					array(
						'source'  => 'similar',
						'unit'    => $storebox_unit['id'],
						'count'   => 3,
						'columns' => 3,
					)
				);
				?>
			</div>
		</section>
		<?php
	endwhile;

	storebox_cta_band();
endif;

get_footer();
