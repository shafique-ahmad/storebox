<?php
/**
 * Site header dispatcher: loads the overlay or classic layout.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/header/header', storebox_header_layout() );
