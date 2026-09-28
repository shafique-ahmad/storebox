<?php
/**
 * Site footer: brand column, three menu/widget columns and a bottom bar.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_footer_text = storebox_get_mod( 'footer_text' );
$storebox_copyright   = storebox_replace_tokens( storebox_get_mod( 'footer_copyright' ) );
$storebox_note        = storebox_get_mod( 'footer_note' );
?>
<footer class="sb-footer">
	<div class="sb-wrap">
		<div class="sb-footer__grid">
			<div class="sb-footer__brand">
				<?php storebox_site_logo( 'light' ); ?>
				<?php if ( $storebox_footer_text ) : ?>
					<p class="sb-footer__text"><?php echo esc_html( $storebox_footer_text ); ?></p>
				<?php endif; ?>
				<?php storebox_social_links( 'sb-footer__social' ); ?>
			</div>

			<?php for ( $storebox_col = 1; $storebox_col <= 3; $storebox_col++ ) : ?>
				<div class="sb-footer__col">
					<?php storebox_footer_column( $storebox_col ); ?>
				</div>
			<?php endfor; ?>
		</div>

		<div class="sb-footer__bottom">
			<?php if ( $storebox_copyright ) : ?>
				<span><?php echo esc_html( $storebox_copyright ); ?></span>
			<?php endif; ?>
			<?php if ( $storebox_note ) : ?>
				<span><?php echo wp_kses( $storebox_note, array( 'a' => array( 'href' => true ), 'strong' => array(), 'em' => array(), 'br' => array() ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</footer>
