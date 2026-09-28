<?php
/**
 * Classic header (Business Storage design): dark info bar and a sticky light
 * header with a bottom rule.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

$storebox_phone    = storebox_get_mod( 'header_phone' );
$storebox_email    = storebox_get_mod( 'topbar_email' );
$storebox_item_1   = storebox_get_mod( 'topbar_item_1' );
$storebox_item_2   = storebox_get_mod( 'topbar_item_2' );
$storebox_cta_text = storebox_get_mod( 'header_cta_text' );
$storebox_cta_url  = storebox_get_mod( 'header_cta_url' );

$storebox_show_topbar = storebox_get_mod( 'topbar_enable' ) && ( $storebox_item_1 || $storebox_item_2 || $storebox_email || $storebox_phone );
?>
<?php if ( $storebox_show_topbar ) : ?>
	<div class="sb-topbar" role="region" aria-label="<?php esc_attr_e( 'Opening hours and contact', 'storebox' ); ?>">
		<div class="sb-wrap sb-topbar__row">
			<div class="sb-topbar__group">
				<?php if ( $storebox_item_1 ) : ?>
					<span><?php storebox_the_icon( 'clock', 14 ); ?><?php echo esc_html( $storebox_item_1 ); ?></span>
				<?php endif; ?>
				<?php if ( $storebox_item_2 ) : ?>
					<span><?php storebox_the_icon( 'pin', 14 ); ?><?php echo esc_html( $storebox_item_2 ); ?></span>
				<?php endif; ?>
			</div>
			<div class="sb-topbar__group">
				<?php if ( $storebox_email ) : ?>
					<a href="<?php echo esc_url( 'mailto:' . antispambot( $storebox_email ) ); ?>"><?php echo esc_html( antispambot( $storebox_email ) ); ?></a>
				<?php endif; ?>
				<?php if ( $storebox_phone ) : ?>
					<a href="<?php echo esc_url( storebox_tel_href( $storebox_phone ), array( 'tel' ) ); ?>"><?php echo esc_html( $storebox_phone ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<header id="sb-header" class="sb-header sb-header--classic" data-sb-header="classic">
	<div class="sb-wrap sb-header__row">
		<?php storebox_site_logo(); ?>

		<nav id="sb-nav" class="sb-nav" aria-label="<?php esc_attr_e( 'Primary', 'storebox' ); ?>">
			<?php storebox_primary_menu(); ?>
		</nav>

		<button type="button" class="sb-burger" aria-controls="sb-nav" aria-expanded="false">
			<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'storebox' ); ?></span>
			<span class="sb-burger__box" aria-hidden="true"><i></i><i></i><i></i></span>
		</button>

		<?php echo storebox_button( $storebox_cta_text, $storebox_cta_url, 'dark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in storebox_button(). ?>
	</div>
</header>
