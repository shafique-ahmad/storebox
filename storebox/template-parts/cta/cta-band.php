<?php
/**
 * Closing call-to-action band (Customizer → Storebox → Call to action band).
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_image = absint( storebox_get_mod( 'cta_image' ) );
$storebox_soft  = 'soft' === storebox_preset();

$storebox_btn1 = storebox_button( storebox_get_mod( 'cta_btn1_text' ), storebox_get_mod( 'cta_btn1_url' ), $storebox_soft ? 'primary' : 'dark' );
$storebox_btn2 = storebox_button( storebox_get_mod( 'cta_btn2_text' ), storebox_get_mod( 'cta_btn2_url' ), $storebox_soft ? 'ghost' : 'outline' );
?>
<section class="sb-cta<?php echo $storebox_soft ? ' sb-cta--soft' : ' sb-cta--editorial'; ?>" aria-labelledby="sb-cta-title">
	<?php if ( $storebox_soft && $storebox_image ) : ?>
		<?php
		echo wp_get_attachment_image(
			$storebox_image,
			'storebox-hero',
			false,
			array(
				'class' => 'sb-cta__bg',
				'alt'   => '',
				'sizes' => '100vw',
			)
		);
		?>
	<?php endif; ?>
	<div class="sb-wrap sb-cta__in">
		<div class="sb-cta__text sb-rv">
			<?php if ( storebox_get_mod( 'cta_eyebrow' ) ) : ?>
				<span class="<?php echo $storebox_soft ? 'sb-eyebrow' : 'sb-kicker'; ?>"><?php echo esc_html( storebox_get_mod( 'cta_eyebrow' ) ); ?></span>
			<?php endif; ?>
			<h2 id="sb-cta-title" class="sb-cta__title"><?php echo esc_html( storebox_get_mod( 'cta_title' ) ); ?></h2>
			<?php if ( storebox_get_mod( 'cta_text' ) ) : ?>
				<p class="sb-cta__lede"><?php echo esc_html( storebox_get_mod( 'cta_text' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $storebox_btn1 || $storebox_btn2 ) : ?>
				<div class="sb-cta__buttons">
					<?php echo $storebox_btn1 . $storebox_btn2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button(). ?>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( ! $storebox_soft && $storebox_image ) : ?>
			<div class="sb-cta__img sb-rv">
				<?php
				echo wp_get_attachment_image(
					$storebox_image,
					'storebox-card',
					false,
					array(
						'alt'   => '',
						'sizes' => '(max-width: 1080px) 100vw, 560px',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
