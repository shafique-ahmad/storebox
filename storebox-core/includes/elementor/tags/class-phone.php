<?php
/**
 * Dynamic tag: Phone.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Main phone number (text): Storebox → Settings, else the Customizer header phone.
 */
class Phone extends Tag {

	/**
	 * The number.
	 *
	 * @return string
	 */
	public static function number() {
		/** This filter is documented in templates/unit-booking.php */
		return (string) apply_filters( 'storebox_core/default_phone', storebox_core_setting( 'help_phone' ) );
	}

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-phone';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Main phone number', 'storebox-core' );
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
	 * Output.
	 */
	public function render(): void {
		echo esc_html( self::number() );
	}
}
