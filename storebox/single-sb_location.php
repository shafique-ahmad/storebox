<?php
/**
 * Single location (Storebox Core). Used when no Elementor Pro single template
 * is assigned.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! storebox_do_elementor_location( 'single' ) ) :
	while ( have_posts() ) :
		the_post();

		if ( storebox_is_built_with_elementor() || ! function_exists( 'storebox_core_get_location' ) ) {
			the_content();
			continue;
		}

		$storebox_location = storebox_core_get_location( get_the_ID() );
		$storebox_soft     = 'soft' === storebox_preset();
		$storebox_eyebrow  = $storebox_soft ? 'sb-eyebrow sb-eyebrow--dark' : 'sb-kicker';

		storebox_page_hero(
			array(
				'eyebrow'  => $storebox_location['area_name'],
				'title'    => esc_html( $storebox_location['name'] ),
				'lede'     => esc_html( $storebox_location['excerpt'] ),
				'image_id' => $storebox_location['image_id'],
				'split'    => true,
			)
		);
		?>
		<section class="sb-sec sb-sec--facts">
			<div class="sb-wrap">
				<?php
				storebox_core_component(
					'location-facts',
					array(
						'location' => $storebox_location['id'],
						'overlap'  => $storebox_soft,
					)
				);
				?>
			</div>
		</section>

		<section class="sb-sec sb-sec--location">
			<div class="sb-wrap sb-split sb-split--top">
				<div class="sb-rv">
					<span class="<?php echo esc_attr( $storebox_eyebrow ); ?>"><?php esc_html_e( 'About this facility', 'storebox' ); ?></span>
					<div class="sb-prose entry-content sb-location-content"><?php the_content(); ?></div>
					<?php storebox_core_component( 'location-tags', array( 'location' => $storebox_location['id'] ) ); ?>
				</div>
				<div class="sb-rv">
					<?php
					if ( $storebox_soft && ! empty( $storebox_location['gallery'][0] ) ) {
						echo '<div class="sb-media sb-media--4x3">' . wp_get_attachment_image( $storebox_location['gallery'][0], 'storebox-card', false, array( 'sizes' => '(max-width: 1000px) 100vw, 580px' ) ) . '</div>';
					}

					storebox_core_component( 'opening-hours', array( 'location' => $storebox_location['id'] ) );
					?>
				</div>
			</div>
		</section>

		<section class="sb-sec sb-sec--surface" aria-labelledby="sb-units-here">
			<div class="sb-wrap">
				<div class="sb-head sb-head--split sb-rv">
					<div>
						<span class="<?php echo esc_attr( $storebox_eyebrow ); ?>"><?php esc_html_e( 'Units here', 'storebox' ); ?></span>
						<h2 id="sb-units-here" class="sb-head__title">
							<?php
							/* translators: %s: location name. */
							echo esc_html( sprintf( __( 'Available at %s.', 'storebox' ), $storebox_location['name'] ) );
							?>
						</h2>
					</div>
					<?php
					$storebox_units_url = storebox_core_page_url( 'units' );
					if ( $storebox_units_url ) {
						echo storebox_button( __( 'All locations', 'storebox' ), $storebox_units_url, $storebox_soft ? 'dark' : 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button().
					}
					?>
				</div>
				<?php
				storebox_core_component(
					'unit-grid',
					array(
						'source'   => 'location',
						'location' => $storebox_location['id'],
						'count'    => 8,
						'columns'  => 3,
					)
				);
				?>
			</div>
		</section>

		<?php if ( $storebox_location['lat'] && $storebox_location['lng'] ) : ?>
			<section class="sb-sec" aria-labelledby="sb-getting-here">
				<div class="sb-wrap">
					<div class="sb-head sb-rv">
						<span class="<?php echo esc_attr( $storebox_eyebrow ); ?>"><?php esc_html_e( 'Getting here', 'storebox' ); ?></span>
						<h2 id="sb-getting-here" class="sb-head__title"><?php echo esc_html( $storebox_location['address_inline'] ); ?></h2>
					</div>
					<?php
					storebox_core_component(
						'location-map',
						array(
							'locations' => array( $storebox_location['id'] ),
							'zoom'      => 14,
						)
					);
					?>
				</div>
			</section>
		<?php endif; ?>
		<?php
	endwhile;

	storebox_cta_band();
endif;

get_footer();
