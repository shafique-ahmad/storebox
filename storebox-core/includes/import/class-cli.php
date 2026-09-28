<?php
/**
 * WP-CLI: wp storebox demo list|import|remove.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Import;

defined( 'ABSPATH' ) || exit;

/**
 * Imports and removes the Storebox demo content.
 */
class CLI {

	/**
	 * Lists the demos that can be imported.
	 *
	 * ## EXAMPLES
	 *
	 *     wp storebox demo list
	 *
	 * @subcommand list
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function list_demos( $args, $assoc_args ) {
		$imported = Importer::imported();
		$rows     = array();

		foreach ( Importer::demos() as $slug => $demo ) {
			$rows[] = array(
				'slug'     => $slug,
				'name'     => $demo['name'],
				'imported' => $imported && $imported['demo'] === $slug ? 'yes' : '',
			);
		}

		\WP_CLI\Utils\format_items( isset( $assoc_args['format'] ) ? $assoc_args['format'] : 'table', $rows, array( 'slug', 'name', 'imported' ) );
	}

	/**
	 * Imports a demo.
	 *
	 * ## OPTIONS
	 *
	 * <demo>
	 * : Demo slug, e.g. demo-1.
	 *
	 * [--skip=<parts>]
	 * : Comma-separated parts to leave out: content, templates, menus, settings.
	 *
	 * [--replace]
	 * : Delete previously imported demo content first.
	 *
	 * ## EXAMPLES
	 *
	 *     wp storebox demo import demo-1
	 *     wp storebox demo import demo-2 --replace --skip=templates
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function import( $args, $assoc_args ) {
		$slug  = sanitize_key( $args[0] );
		$demos = Importer::demos();

		if ( ! isset( $demos[ $slug ] ) ) {
			\WP_CLI::error( sprintf( 'Unknown demo "%s". Available: %s', $slug, implode( ', ', array_keys( $demos ) ) ) );
		}

		self::ensure_user();

		$skip    = isset( $assoc_args['skip'] ) ? array_map( 'trim', explode( ',', $assoc_args['skip'] ) ) : array();
		$options = array(
			'content'   => ! in_array( 'content', $skip, true ),
			'templates' => ! in_array( 'templates', $skip, true ),
			'menus'     => ! in_array( 'menus', $skip, true ),
			'settings'  => ! in_array( 'settings', $skip, true ),
			'replace'   => ! empty( $assoc_args['replace'] ),
		);

		$result = Importer::import_all(
			$slug,
			$options,
			static function ( $line ) {
				\WP_CLI::log( $line );
			}
		);

		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}

		\WP_CLI::success( sprintf( '"%s" imported.', $demos[ $slug ]['name'] ) );
	}

	/**
	 * Removes the imported demo content and restores the previous settings.
	 *
	 * ## OPTIONS
	 *
	 * [--keep-media]
	 * : Keep the imported images.
	 *
	 * [--yes]
	 * : Do not ask for confirmation.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Named arguments.
	 */
	public function remove( $args, $assoc_args ) {
		\WP_CLI::confirm( 'Delete all imported demo content?', $assoc_args );

		self::ensure_user();

		$rounds = 0;
		do {
			$result = Importer::remove( ! empty( $assoc_args['keep-media'] ) );
			foreach ( $result['log'] as $line ) {
				\WP_CLI::log( $line );
			}
			++$rounds;
		} while ( ! $result['done'] && $rounds < 500 );

		\WP_CLI::success( 'Demo content removed.' );
	}

	/**
	 * Runs as an administrator when no --user was given.
	 */
	private static function ensure_user() {
		if ( get_current_user_id() ) {
			return;
		}

		$admins = get_users(
			array(
				'role'   => 'administrator',
				'number' => 1,
				'fields' => 'ID',
			)
		);

		if ( $admins ) {
			wp_set_current_user( (int) $admins[0] );
		}
	}
}
