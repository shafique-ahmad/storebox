<?php
/**
 * Reservation, waitlist and contact requests.
 *
 * Requests are validated (nonce, honeypot, time token, rate limit), stored as
 * private "sb_enquiry" posts and emailed to the location or the site owner.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Enquiry handling.
 */
class Enquiries {

	/**
	 * Nonce action.
	 */
	const NONCE = 'storebox_enquiry';

	/**
	 * Post type.
	 */
	const POST_TYPE = 'sb_enquiry';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 5 );

		add_action( 'admin_post_nopriv_storebox_enquiry', array( __CLASS__, 'handle_post' ) );
		add_action( 'admin_post_storebox_enquiry', array( __CLASS__, 'handle_post' ) );
		add_action( 'wp_ajax_nopriv_storebox_enquiry', array( __CLASS__, 'handle_ajax' ) );
		add_action( 'wp_ajax_storebox_enquiry', array( __CLASS__, 'handle_ajax' ) );
		add_action( 'wp_ajax_nopriv_storebox_enquiry_nonce', array( __CLASS__, 'fresh_nonce' ) );
		add_action( 'wp_ajax_storebox_enquiry_nonce', array( __CLASS__, 'fresh_nonce' ) );

		if ( is_admin() ) {
			add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
			add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
			add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
			add_action( 'admin_post_storebox_enquiry_status', array( __CLASS__, 'toggle_status' ) );
			add_action( 'add_meta_boxes_' . self::POST_TYPE, array( __CLASS__, 'meta_box' ) );
			add_action( 'admin_menu', array( __CLASS__, 'menu_count' ), 99 );
		}
	}

	/**
	 * Capability required to read enquiries (they contain personal data).
	 *
	 * @return string
	 */
	public static function capability() {
		return apply_filters( 'storebox_core/enquiry_capability', 'manage_options' );
	}

	/**
	 * Registers the private post type.
	 */
	public static function register() {
		$cap = self::capability();

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'               => _x( 'Enquiries', 'post type general name', 'storebox-core' ),
					'singular_name'      => _x( 'Enquiry', 'post type singular name', 'storebox-core' ),
					'all_items'          => __( 'Enquiries', 'storebox-core' ),
					'edit_item'          => __( 'Enquiry', 'storebox-core' ),
					'view_item'          => __( 'View enquiry', 'storebox-core' ),
					'search_items'       => __( 'Search enquiries', 'storebox-core' ),
					'not_found'          => __( 'No enquiries yet. Reservation and contact requests from your forms appear here.', 'storebox-core' ),
					'not_found_in_trash' => __( 'No enquiries in Trash.', 'storebox-core' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=sb_unit',
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => false,
				'capabilities'        => array(
					'edit_post'              => $cap,
					'read_post'              => $cap,
					'delete_post'            => $cap,
					'edit_posts'             => $cap,
					'edit_others_posts'      => $cap,
					'delete_posts'           => $cap,
					'delete_others_posts'    => $cap,
					'delete_published_posts' => $cap,
					'edit_published_posts'   => $cap,
					'publish_posts'          => $cap,
					'read_private_posts'     => $cap,
					'create_posts'           => 'do_not_allow',
				),
			)
		);
	}

	/**
	 * Signed timestamp used to reject instant (bot) submissions.
	 *
	 * @return string
	 */
	public static function time_token() {
		$time = time();

		return $time . '.' . substr( wp_hash( 'storebox_enquiry|' . $time ), 0, 20 );
	}

	/**
	 * Checks a time token: valid signature, at least 3 seconds and at most two
	 * days old.
	 *
	 * @param string $token Token.
	 * @return bool
	 */
	private static function valid_token( $token ) {
		$parts = explode( '.', (string) $token );

		if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
			return false;
		}

		$expected = substr( wp_hash( 'storebox_enquiry|' . $parts[0] ), 0, 20 );
		$age      = time() - (int) $parts[0];

		return hash_equals( $expected, $parts[1] ) && $age >= 3 && $age <= 2 * DAY_IN_SECONDS;
	}

	/**
	 * Rate limit per visitor: 5 requests per 10 minutes.
	 *
	 * @return bool True when the visitor may submit.
	 */
	private static function rate_ok() {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'sb_enq_' . substr( wp_hash( $ip ), 0, 16 );
		$hit = (int) get_transient( $key );

		if ( $hit >= (int) apply_filters( 'storebox_core/enquiry_rate_limit', 5 ) ) {
			return false;
		}

		set_transient( $key, $hit + 1, 10 * MINUTE_IN_SECONDS );

		return true;
	}

	/**
	 * Validates and processes a submission.
	 *
	 * @param array $data Raw request data (unslashed).
	 * @return true|\WP_Error
	 */
	public static function process( $data ) {
		if ( ! empty( $data['sb_website'] ) ) {
			return new \WP_Error( 'spam', __( 'Your message could not be sent.', 'storebox-core' ) );
		}

		if ( ! self::valid_token( isset( $data['sb_token'] ) ? $data['sb_token'] : '' ) ) {
			return new \WP_Error( 'token', __( 'Please take a moment to fill in the form, then send it again.', 'storebox-core' ) );
		}

		if ( ! wp_verify_nonce( isset( $data['sb_nonce'] ) ? sanitize_text_field( $data['sb_nonce'] ) : '', self::NONCE ) ) {
			return new \WP_Error( 'nonce', __( 'Your session expired. Please reload the page and try again.', 'storebox-core' ) );
		}

		$type = isset( $data['sb_type'] ) ? sanitize_key( $data['sb_type'] ) : 'contact';
		if ( ! in_array( $type, array( 'reservation', 'waitlist', 'contact' ), true ) ) {
			$type = 'contact';
		}

		$name    = isset( $data['sb_name'] ) ? sanitize_text_field( $data['sb_name'] ) : '';
		$email   = isset( $data['sb_email'] ) ? sanitize_email( $data['sb_email'] ) : '';
		$phone   = isset( $data['sb_phone'] ) ? sanitize_text_field( $data['sb_phone'] ) : '';
		$message = isset( $data['sb_message'] ) ? sanitize_textarea_field( $data['sb_message'] ) : '';
		$date    = isset( $data['sb_date'] ) ? sanitize_text_field( $data['sb_date'] ) : '';
		$unit    = isset( $data['sb_unit'] ) ? absint( $data['sb_unit'] ) : 0;
		$loc     = isset( $data['sb_location'] ) ? absint( $data['sb_location'] ) : 0;

		if ( '' === $name || mb_strlen( $name ) > 120 ) {
			return new \WP_Error( 'name', __( 'Please enter your name.', 'storebox-core' ) );
		}
		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'email', __( 'Please enter a valid email address.', 'storebox-core' ) );
		}
		if ( 'contact' === $type && '' === trim( $message ) ) {
			return new \WP_Error( 'message', __( 'Please write a short message.', 'storebox-core' ) );
		}
		if ( $date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			$date = '';
		}
		if ( $unit && 'sb_unit' !== get_post_type( $unit ) ) {
			$unit = 0;
		}
		if ( $loc && 'sb_location' !== get_post_type( $loc ) ) {
			$loc = 0;
		}
		if ( ! $loc && $unit ) {
			$loc = absint( get_post_meta( $unit, '_sb_location', true ) );
		}

		if ( ! self::rate_ok() ) {
			return new \WP_Error( 'rate', __( 'You have sent several requests in a short time. Please wait a few minutes or call us.', 'storebox-core' ) );
		}

		$enquiry = array(
			'type'     => $type,
			'name'     => $name,
			'email'    => $email,
			'phone'    => mb_substr( $phone, 0, 40 ),
			'date'     => $date,
			'unit'     => $unit,
			'location' => $loc,
			'message'  => mb_substr( $message, 0, 4000 ),
			'page'     => isset( $data['sb_page'] ) ? esc_url_raw( $data['sb_page'] ) : '',
		);

		/**
		 * Filters an enquiry before it is stored and emailed. Return a WP_Error to reject it.
		 *
		 * @param array $enquiry Sanitized enquiry.
		 */
		$enquiry = apply_filters( 'storebox_core/enquiry', $enquiry );
		if ( is_wp_error( $enquiry ) ) {
			return $enquiry;
		}

		$stored = false;
		if ( storebox_core_setting( 'store_enquiries' ) ) {
			$stored = self::store( $enquiry );
		}

		$mailed = self::notify( $enquiry );

		if ( ! $stored && ! $mailed ) {
			return new \WP_Error( 'mail', __( 'Sorry, your message could not be sent. Please call us instead.', 'storebox-core' ) );
		}

		do_action( 'storebox_core/enquiry_received', $enquiry, $stored );

		return true;
	}

	/**
	 * Human label of an enquiry type.
	 *
	 * @param string $type Type.
	 * @return string
	 */
	public static function type_label( $type ) {
		$labels = array(
			'reservation' => __( 'Reservation', 'storebox-core' ),
			'waitlist'    => __( 'Waitlist', 'storebox-core' ),
			'contact'     => __( 'Contact', 'storebox-core' ),
		);

		return isset( $labels[ $type ] ) ? $labels[ $type ] : $labels['contact'];
	}

	/**
	 * Stores an enquiry.
	 *
	 * @param array $enquiry Enquiry.
	 * @return int|false Post ID.
	 */
	private static function store( $enquiry ) {
		$about = '';
		if ( $enquiry['unit'] ) {
			$unit  = storebox_core_get_unit( $enquiry['unit'] );
			$about = $unit ? $unit['short_title'] : '';
		} elseif ( $enquiry['location'] ) {
			$about = get_the_title( $enquiry['location'] );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'publish',
				'post_title'   => implode( ' — ', array_filter( array( self::type_label( $enquiry['type'] ), $enquiry['name'], $about ) ) ),
				'post_content' => $enquiry['message'],
				'post_author'  => 0,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		foreach ( array( 'type', 'name', 'email', 'phone', 'date', 'unit', 'location', 'page' ) as $key ) {
			update_post_meta( $post_id, '_sb_e_' . $key, $enquiry[ $key ] );
		}
		update_post_meta( $post_id, '_sb_e_status', 'new' );

		return $post_id;
	}

	/**
	 * Emails an enquiry to the location or the site owner.
	 *
	 * @param array $enquiry Enquiry.
	 * @return bool
	 */
	private static function notify( $enquiry ) {
		$to = '';
		if ( $enquiry['location'] ) {
			$to = sanitize_email( (string) get_post_meta( $enquiry['location'], '_sb_email', true ) );
		}
		if ( ! $to ) {
			$to = storebox_core_setting( 'notify_email' );
		}
		if ( ! $to ) {
			$to = get_option( 'admin_email' );
		}

		$lines = array(
			__( 'Type', 'storebox-core' ) . ': ' . self::type_label( $enquiry['type'] ),
			__( 'Name', 'storebox-core' ) . ': ' . $enquiry['name'],
			__( 'Email', 'storebox-core' ) . ': ' . $enquiry['email'],
		);
		if ( $enquiry['phone'] ) {
			$lines[] = __( 'Phone', 'storebox-core' ) . ': ' . $enquiry['phone'];
		}
		if ( $enquiry['unit'] ) {
			$unit    = storebox_core_get_unit( $enquiry['unit'] );
			$lines[] = __( 'Unit', 'storebox-core' ) . ': ' . ( $unit ? $unit['short_title'] . ' (' . $unit['price_label'] . ')' : '#' . $enquiry['unit'] );
		}
		if ( $enquiry['location'] ) {
			$lines[] = __( 'Location', 'storebox-core' ) . ': ' . get_the_title( $enquiry['location'] );
		}
		if ( $enquiry['date'] ) {
			$lines[] = __( 'Move-in date', 'storebox-core' ) . ': ' . $enquiry['date'];
		}
		if ( $enquiry['message'] ) {
			$lines[] = '';
			$lines[] = $enquiry['message'];
		}

		$subject = sprintf(
			/* translators: 1: site name, 2: enquiry type, 3: visitor name. */
			__( '[%1$s] New %2$s request from %3$s', 'storebox-core' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			mb_strtolower( self::type_label( $enquiry['type'] ) ),
			$enquiry['name']
		);

		$headers = array( 'Reply-To: ' . str_replace( array( "\r", "\n", '<', '>' ), '', $enquiry['name'] ) . ' <' . $enquiry['email'] . '>' );

		return (bool) wp_mail(
			apply_filters( 'storebox_core/enquiry_recipient', $to, $enquiry ),
			$subject,
			implode( "\n", $lines ),
			$headers
		);
	}

	/**
	 * AJAX submission.
	 */
	public static function handle_ajax() {
		$result = self::process( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in process().

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success();
	}

	/**
	 * Form submission without JavaScript: process, then redirect back.
	 */
	public static function handle_post() {
		$data   = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in process().
		$result = self::process( $data );
		$form   = isset( $data['sb_form'] ) ? sanitize_html_class( $data['sb_form'] ) : '';

		$back = wp_get_referer();
		$back = $back ? $back : home_url( '/' );
		$back = remove_query_arg( array( 'sb_enquiry', 'sb_form' ), $back );
		$back = add_query_arg(
			array(
				'sb_enquiry' => is_wp_error( $result ) ? 'error' : 'sent',
				'sb_form'    => $form,
			),
			$back
		);

		wp_safe_redirect( $back . ( $form ? '#' . $form : '' ) );
		exit;
	}

	/**
	 * Returns a fresh nonce (pages may be served from a cache).
	 */
	public static function fresh_nonce() {
		nocache_headers();
		wp_send_json_success( array( 'nonce' => wp_create_nonce( self::NONCE ) ) );
	}

	/*
	 * ---------------------------------------------------------------------
	 * Admin
	 * ---------------------------------------------------------------------
	 */

	/**
	 * List table columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array(
			'cb'        => $columns['cb'],
			'title'     => __( 'Enquiry', 'storebox-core' ),
			'sb_type'   => __( 'Type', 'storebox-core' ),
			'sb_email'  => __( 'Email', 'storebox-core' ),
			'sb_phone'  => __( 'Phone', 'storebox-core' ),
			'sb_about'  => __( 'Unit / location', 'storebox-core' ),
			'sb_status' => __( 'Status', 'storebox-core' ),
			'date'      => __( 'Received', 'storebox-core' ),
		);
	}

	/**
	 * Column content.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post ID.
	 */
	public static function column( $column, $post_id ) {
		switch ( $column ) {
			case 'sb_type':
				echo esc_html( self::type_label( get_post_meta( $post_id, '_sb_e_type', true ) ) );
				break;
			case 'sb_email':
				$email = get_post_meta( $post_id, '_sb_e_email', true );
				printf( '<a href="%1$s">%2$s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
				break;
			case 'sb_phone':
				echo esc_html( get_post_meta( $post_id, '_sb_e_phone', true ) );
				break;
			case 'sb_about':
				$unit = absint( get_post_meta( $post_id, '_sb_e_unit', true ) );
				$loc  = absint( get_post_meta( $post_id, '_sb_e_location', true ) );
				$out  = array();
				if ( $unit && get_post( $unit ) ) {
					$data  = storebox_core_get_unit( $unit );
					$out[] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $unit ) ), esc_html( $data ? $data['short_title'] : get_the_title( $unit ) ) );
				}
				if ( $loc && get_post( $loc ) ) {
					$out[] = esc_html( get_the_title( $loc ) );
				}
				echo $out ? implode( '<br>', $out ) : '—'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
				break;
			case 'sb_status':
				$status = get_post_meta( $post_id, '_sb_e_status', true );
				printf(
					'<span class="sb-enquiry-status sb-enquiry-status--%1$s">%2$s</span>',
					esc_attr( 'handled' === $status ? 'handled' : 'new' ),
					'handled' === $status ? esc_html__( 'Handled', 'storebox-core' ) : esc_html__( 'New', 'storebox-core' )
				);
				break;
		}
	}

	/**
	 * "Mark handled / new" row action.
	 *
	 * @param array    $actions Actions.
	 * @param \WP_Post $post    Post.
	 * @return array
	 */
	public static function row_actions( $actions, $post ) {
		if ( self::POST_TYPE !== $post->post_type || ! current_user_can( self::capability() ) ) {
			return $actions;
		}

		unset( $actions['inline hide-if-no-js'] );

		$handled = 'handled' === get_post_meta( $post->ID, '_sb_e_status', true );
		$url     = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'storebox_enquiry_status',
					'post'   => $post->ID,
				),
				admin_url( 'admin-post.php' )
			),
			'storebox_enquiry_status_' . $post->ID
		);

		$actions['sb_status'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $url ),
			$handled ? esc_html__( 'Mark as new', 'storebox-core' ) : esc_html__( 'Mark as handled', 'storebox-core' )
		);

		return $actions;
	}

	/**
	 * Toggles the handled status.
	 */
	public static function toggle_status() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

		check_admin_referer( 'storebox_enquiry_status_' . $post_id );

		if ( ! $post_id || self::POST_TYPE !== get_post_type( $post_id ) || ! current_user_can( self::capability() ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'storebox-core' ), 403 );
		}

		$handled = 'handled' === get_post_meta( $post_id, '_sb_e_status', true );
		update_post_meta( $post_id, '_sb_e_status', $handled ? 'new' : 'handled' );

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=' . self::POST_TYPE ) );
		exit;
	}

	/**
	 * Read-only details box on the edit screen.
	 */
	public static function meta_box() {
		remove_meta_box( 'submitdiv', self::POST_TYPE, 'side' );

		add_meta_box(
			'storebox-enquiry',
			__( 'Request details', 'storebox-core' ),
			array( __CLASS__, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Details box content.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function render_meta_box( $post ) {
		$get  = static function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, '_sb_e_' . $key, true );
		};
		$unit = absint( $get( 'unit' ) );
		$loc  = absint( $get( 'location' ) );
		$rows = array(
			__( 'Type', 'storebox-core' )         => esc_html( self::type_label( $get( 'type' ) ) ),
			__( 'Name', 'storebox-core' )         => esc_html( $get( 'name' ) ),
			__( 'Email', 'storebox-core' )        => sprintf( '<a href="%1$s">%2$s</a>', esc_url( 'mailto:' . $get( 'email' ) ), esc_html( $get( 'email' ) ) ),
			__( 'Phone', 'storebox-core' )        => esc_html( $get( 'phone' ) ),
			__( 'Move-in date', 'storebox-core' ) => esc_html( $get( 'date' ) ),
			__( 'Unit', 'storebox-core' )         => $unit && get_post( $unit ) ? sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $unit ) ), esc_html( get_the_title( $unit ) ) ) : '—',
			__( 'Location', 'storebox-core' )     => $loc && get_post( $loc ) ? esc_html( get_the_title( $loc ) ) : '—',
			__( 'Received', 'storebox-core' )     => esc_html( get_the_date( '', $post ) . ' ' . get_the_time( '', $post ) ),
		);
		?>
		<table class="widefat striped">
			<tbody>
				<?php foreach ( $rows as $label => $value ) : ?>
					<tr><th scope="row" style="width:160px"><?php echo esc_html( $label ); ?></th><td><?php echo $value ? $value : '—'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php if ( $post->post_content ) : ?>
			<h3><?php esc_html_e( 'Message', 'storebox-core' ); ?></h3>
			<div style="white-space:pre-wrap;background:#f6f7f7;padding:14px 16px;border-radius:6px"><?php echo esc_html( $post->post_content ); ?></div>
		<?php endif; ?>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( 'mailto:' . $get( 'email' ) . '?subject=' . rawurlencode( get_bloginfo( 'name' ) ) ); ?>"><?php esc_html_e( 'Reply by email', 'storebox-core' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Adds a count of new enquiries to the menu item.
	 */
	public static function menu_count() {
		global $submenu;

		$parent = 'edit.php?post_type=sb_unit';
		if ( empty( $submenu[ $parent ] ) || ! current_user_can( self::capability() ) ) {
			return;
		}

		$new = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 99,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_sb_e_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'new', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);

		if ( ! $new ) {
			return;
		}

		foreach ( $submenu[ $parent ] as $index => $item ) {
			if ( 'edit.php?post_type=' . self::POST_TYPE === $item[2] ) {
				$submenu[ $parent ][ $index ][0] .= sprintf( ' <span class="awaiting-mod"><span class="pending-count">%d</span></span>', count( $new ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Standard way to add a menu count.
			}
		}
	}
}
