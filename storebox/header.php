<?php
/**
 * The header: document head, skip link and site header.
 *
 * An Elementor Pro header template replaces the theme header when one is
 * assigned to the current page.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'storebox' ); ?></a>

<?php
if ( ! storebox_do_elementor_location( 'header' ) ) {
	get_template_part( 'template-parts/header/site-header' );
}
?>

<main id="content" class="sb-main" tabindex="-1">
