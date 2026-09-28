<?php
/**
 * Dynamic tag: Read Time.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Reading time of the current post (text).
 */
class Read_Time extends Tag {

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-read-time';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Reading time', 'storebox-core' );
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
		return array( Tags_Module::TEXT_CATEGORY );
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->add_control(
			'format',
			array(
				'label'       => esc_html__( 'Format', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				/* translators: %d: minutes. */
				'placeholder' => esc_html__( '%d min read', 'storebox-core' ),
				/* translators: %d: literal placeholder shown to the user, keep as is. */
				'description' => esc_html__( '%d is the number of minutes.', 'storebox-core' ),
			)
		);
	}

	/**
	 * Output.
	 */
	public function render(): void {
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$minutes = storebox_core_read_time( $post_id );
		$format  = (string) $this->get_settings( 'format' );
		if ( '' === $format || false === strpos( $format, '%d' ) ) {
			/* translators: %d: minutes to read. */
			$format = _n( '%d min read', '%d min read', $minutes, 'storebox-core' );
		}

		echo esc_html( str_replace( '%d', number_format_i18n( $minutes ), $format ) );
	}
}
