<?php
/**
 * Dynamic tag: Location Image.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Location photo (image): the first gallery photo or the featured image.
 */
class Location_Image extends Data_Tag {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-location-image';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Location photo', 'storebox-core' );
	}

	/**
	 * Group.
	 *
	 * @return string
	 */
	public function get_group(): string {
		return 'storebox';
	}

	/**
	 * Categories.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( Tags_Module::IMAGE_CATEGORY );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Photo', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'gallery',
				'options' => array(
					'gallery'  => esc_html__( 'First gallery photo', 'storebox-core' ),
					'featured' => esc_html__( 'Featured image', 'storebox-core' ),
				),
			)
		);
		add_id_control( $this, esc_html__( 'Location ID', 'storebox-core' ) );
	}

	/**
	 * Image data.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_value( array $options = array() ): array {
		$location = storebox_core_get_location( resolve_post( $this->get_settings( 'post_id' ), 'sb_location' ) );
		if ( ! $location ) {
			return array();
		}

		$id  = 'featured' === $this->get_settings( 'source' ) ? $location['image_id'] : ( $location['gallery'] ? $location['gallery'][0] : $location['image_id'] );
		$src = $id ? wp_get_attachment_image_src( $id, 'full' ) : false;

		return $src ? array(
			'id'  => (int) $id,
			'url' => $src[0],
		) : array();
	}
}
