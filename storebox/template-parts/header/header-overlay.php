<?php
/**
 * Overlay header (Self Storage design): transparent over the first section,
 * solid after scrolling.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_phone    = storebox_get_mod( 'header_phone' );
$storebox_cta_text = storebox_get_mod( 'header_cta_text' );
$storebox_cta_url  = storebox_get_mod( 'header_cta_url' );
?>
<header id="sb-header" class="sb-header sb-header--overlay" data-sb-header="overlay">
	<div class="sb-wrap sb-header__row">
		<?php storebox_site_logo( 'light' ); ?>

		<nav id="sb-nav" class="sb-nav" aria-label="<?php esc_attr_e( 'Primary', 'storebox' ); ?>">
			<?php storebox_primary_menu(); ?>
		</nav>

		<button type="button" class="sb-burger" aria-controls="sb-nav" aria-expanded="false">
			<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'storebox' ); ?></span>
			<span class="sb-burger__box" aria-hidden="true"><i></i><i></i><i></i></span>
		</button>

		<?php if ( $storebox_phone || ( $storebox_cta_text && $storebox_cta_url ) ) : ?>
			<div class="sb-header__actions">
				<?php if ( $storebox_phone ) : ?>
					<a class="sb-header__tel" href="<?php echo esc_url( storebox_tel_href( $storebox_phone ), array( 'tel' ) ); ?>"><?php echo esc_html( $storebox_phone ); ?></a>
				<?php endif; ?>
				<?php echo storebox_button( $storebox_cta_text, $storebox_cta_url, 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button(). ?>
			</div>
		<?php endif; ?>
	</div>
</header>
