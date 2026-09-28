<?php
/**
 * The footer: closes the main element and prints the site footer.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;
?>
</main><!-- #content -->

<?php
if ( ! storebox_do_elementor_location( 'footer' ) ) {
	get_template_part( 'template-parts/footer/site-footer' );
}

wp_footer();
?>
</body>
</html>
