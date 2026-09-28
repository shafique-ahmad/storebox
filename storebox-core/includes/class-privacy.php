<?php
/**
 * Personal data export and erasure for enquiries (Tools → Export/Erase
 * Personal Data), plus a suggested privacy policy paragraph.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Privacy tools integration.
 */
class Privacy {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
		add_action( 'admin_init', array( __CLASS__, 'policy_content' ) );
	}

	/**
	 * Registers the exporter.
	 *
	 * @param array $exporters Exporters.
	 * @return array
	 */
	public static function register_exporter( $exporters ) {
		$exporters['storebox-core'] = array(
			'exporter_friendly_name' => __( 'Storebox enquiries', 'storebox-core' ),
			'callback'               => array( __CLASS__, 'export' ),
		);

		return $exporters;
	}

	/**
	 * Registers the eraser.
	 *
	 * @param array $erasers Erasers.
	 * @return array
	 */
	public static function register_eraser( $erasers ) {
		$erasers['storebox-core'] = array(
			'eraser_friendly_name' => __( 'Storebox enquiries', 'storebox-core' ),
			'callback'             => array( __CLASS__, 'erase' ),
		);

		return $erasers;
	}

	/**
	 * Enquiries sent from an email address.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page number.
	 * @return int[]
	 */
	private static function find( $email, $page ) {
		return get_posts(
			array(
				'post_type'      => Enquiries::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'paged'          => max( 1, (int) $page ),
				'fields'         => 'ids',
				'meta_key'       => '_sb_e_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => sanitize_email( $email ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
	}

	/**
	 * Exports enquiries.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page.
	 * @return array
	 */
	public static function export( $email, $page = 1 ) {
		$items = array();

		foreach ( self::find( $email, $page ) as $post_id ) {
			$data = array();
			foreach ( array( 'type', 'name', 'email', 'phone', 'date' ) as $key ) {
				$value = get_post_meta( $post_id, '_sb_e_' . $key, true );
				if ( '' !== $value ) {
					$data[] = array(
						'name'  => ucfirst( $key ),
						'value' => $value,
					);
				}
			}
			$data[] = array(
				'name'  => __( 'Message', 'storebox-core' ),
				'value' => get_post_field( 'post_content', $post_id ),
			);
			$data[] = array(
				'name'  => __( 'Received', 'storebox-core' ),
				'value' => get_the_date( 'c', $post_id ),
			);

			$items[] = array(
				'group_id'    => 'storebox-enquiries',
				'group_label' => __( 'Storebox enquiries', 'storebox-core' ),
				'item_id'     => 'storebox-enquiry-' . $post_id,
				'data'        => $data,
			);
		}

		return array(
			'data' => $items,
			'done' => count( $items ) < 50,
		);
	}

	/**
	 * Erases enquiries.
	 *
	 * @param string $email Email address.
	 * @param int    $page  Page (erasure always restarts at 1 as posts are deleted).
	 * @return array
	 */
	public static function erase( $email, $page = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Signature of the personal data eraser API.
		$ids     = self::find( $email, 1 );
		$removed = 0;

		foreach ( $ids as $post_id ) {
			if ( wp_delete_post( $post_id, true ) ) {
				++$removed;
			}
		}

		return array(
			'items_removed'  => $removed,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => count( $ids ) < 50,
		);
	}

	/**
	 * Suggested privacy policy text.
	 */
	public static function policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		wp_add_privacy_policy_content(
			'Storebox Core',
			wp_kses_post(
				'<p>' . __( 'When you send a reservation, waitlist or contact request, we store your name, email address, phone number, preferred move-in date, the unit or location you asked about and your message, and email them to our team so we can answer you. Requests are kept until they are no longer needed to handle your enquiry or contract.', 'storebox-core' ) . '</p>'
			)
		);
	}
}
