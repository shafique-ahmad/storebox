<?php
/**
 * Helpers shared by the Storebox dynamic tags.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Core\DynamicTags\Tag;

defined( 'ABSPATH' ) || exit;

/**
 * Finds the post a tag refers to: the ID entered, else the current post.
 *
 * @param mixed  $id        Entered ID.
 * @param string $post_type Post type.
 * @return int
 */
function resolve_post( $id, $post_type ) {
	$id = absint( $id );
	if ( $id && get_post_type( $id ) === $post_type ) {
		return $id;
	}

	$current = get_the_ID();

	return $current && get_post_type( $current ) === $post_type ? (int) $current : 0;
}

/**
 * Adds the optional "ID" control shared by the unit and location tags.
 *
 * @param Tag|Data_Tag $tag   Tag.
 * @param string       $label Label.
 */
function add_id_control( $tag, $label ) {
	$tag->add_control(
		'post_id',
		array(
			'label'       => $label,
			'type'        => Controls_Manager::NUMBER,
			'min'         => 0,
			'description' => esc_html__( 'Empty uses the current page.', 'storebox-core' ),
		)
	);
}
