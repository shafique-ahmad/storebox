<?php
/**
 * Storebox → Settings.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin settings screen (WordPress Settings API).
 */
class Settings {

	/**
	 * Option name.
	 */
	const OPTION = 'storebox_core_settings';

	/**
	 * Admin page slug.
	 */
	const PAGE = 'storebox-settings';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Adds the page under the Storebox menu.
	 */
	public static function add_page() {
		add_submenu_page(
			'edit.php?post_type=sb_unit',
			__( 'Storebox settings', 'storebox-core' ),
			__( 'Settings', 'storebox-core' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Field definitions grouped by section.
	 *
	 * @return array
	 */
	public static function sections() {
		$pages = array( 0 => __( '— Select a page —', 'storebox-core' ) );
		foreach ( get_pages( array( 'sort_column' => 'post_title' ) ) as $page ) {
			$pages[ $page->ID ] = $page->post_title;
		}

		return array(
			'general'      => array(
				'title'  => __( 'Prices and availability', 'storebox-core' ),
				'fields' => array(
					'currency_symbol'   => array( 'label' => __( 'Currency symbol', 'storebox-core' ), 'type' => 'text', 'class' => 'small-text' ),
					'currency_position' => array(
						'label'   => __( 'Currency position', 'storebox-core' ),
						'type'    => 'select',
						'choices' => array(
							'before'       => __( 'Before the amount (€59)', 'storebox-core' ),
							'before_space' => __( 'Before, with a space (€ 59)', 'storebox-core' ),
							'after'        => __( 'After the amount (59€)', 'storebox-core' ),
							'after_space'  => __( 'After, with a space (59 €)', 'storebox-core' ),
						),
					),
					'price_decimals'    => array( 'label' => __( 'Price decimals', 'storebox-core' ), 'type' => 'number', 'min' => 0, 'max' => 2 ),
					'low_threshold'     => array(
						'label'       => __( '"Only a few left" from', 'storebox-core' ),
						'type'        => 'number',
						'min'         => 0,
						'max'         => 50,
						'description' => __( 'Units with this many (or fewer) free show the warning status, e.g. "2 left".', 'storebox-core' ),
					),
					'label_full'        => array( 'label' => __( 'Status: fully booked', 'storebox-core' ), 'type' => 'text', 'placeholder' => __( 'Fully booked', 'storebox-core' ) ),
					'label_low'         => array( 'label' => __( 'Status: few left', 'storebox-core' ), 'type' => 'text', 'placeholder' => __( '%d left', 'storebox-core' ), 'description' => __( '%d is replaced by the number free.', 'storebox-core' ) ),
					'label_ok'          => array( 'label' => __( 'Status: available', 'storebox-core' ), 'type' => 'text', 'placeholder' => __( '%d available', 'storebox-core' ) ),
					'weekly_text'       => array( 'label' => __( 'Weekly price line', 'storebox-core' ), 'type' => 'text', 'class' => 'regular-text', 'placeholder' => __( 'About %s a week · first month pro-rata', 'storebox-core' ), 'description' => __( '%s is replaced by the weekly price (monthly × 12 ÷ 52).', 'storebox-core' ) ),
				),
			),
			'pages'        => array(
				'title'  => __( 'Pages', 'storebox-core' ),
				'fields' => array(
					'units_page'     => array( 'label' => __( 'Units page', 'storebox-core' ), 'type' => 'select', 'choices' => $pages, 'description' => __( 'The page with your unit list. Used for breadcrumbs and "All units" links.', 'storebox-core' ) ),
					'locations_page' => array( 'label' => __( 'Locations page', 'storebox-core' ), 'type' => 'select', 'choices' => $pages ),
				),
			),
			'reservations' => array(
				'title'  => __( 'Reservations and enquiries', 'storebox-core' ),
				'fields' => array(
					'notify_email'     => array( 'label' => __( 'Send enquiries to', 'storebox-core' ), 'type' => 'email', 'class' => 'regular-text', 'placeholder' => get_option( 'admin_email' ), 'description' => __( 'A location\'s own email address is used for requests about its units.', 'storebox-core' ) ),
					'store_enquiries'  => array( 'label' => __( 'Keep a copy in Storebox → Enquiries', 'storebox-core' ), 'type' => 'checkbox' ),
					'booking_rows'     => array( 'label' => __( 'Booking panel rows', 'storebox-core' ), 'type' => 'textarea', 'placeholder' => "Deposit | None\nMinimum term | None", 'description' => __( 'Extra rows below location and access, one per line as "Label | Value".', 'storebox-core' ) ),
					'booking_note'     => array( 'label' => __( 'Booking panel note', 'storebox-core' ), 'type' => 'text', 'class' => 'regular-text', 'placeholder' => __( 'Held free for 7 days · cancel any time', 'storebox-core' ) ),
					'help_title'       => array( 'label' => __( 'Help box heading', 'storebox-core' ), 'type' => 'text', 'class' => 'regular-text', 'placeholder' => __( 'Rather talk it through?', 'storebox-core' ) ),
					'help_text'        => array( 'label' => __( 'Help box text', 'storebox-core' ), 'type' => 'text', 'class' => 'regular-text', 'placeholder' => __( 'A person answers, usually within three rings.', 'storebox-core' ) ),
					'help_phone'       => array( 'label' => __( 'Help phone number', 'storebox-core' ), 'type' => 'text', 'description' => __( 'Shown in the booking panel help box. Defaults to the phone number in the theme header settings.', 'storebox-core' ) ),
					'reservation_info' => array( 'label' => __( '"How reserving works" text', 'storebox-core' ), 'type' => 'editor', 'description' => __( 'Shown on single unit pages above the reservation form (theme template).', 'storebox-core' ) ),
					'privacy_note'     => array( 'label' => __( 'Note below forms', 'storebox-core' ), 'type' => 'text', 'class' => 'large-text', 'placeholder' => __( 'We only use your details to answer this enquiry.', 'storebox-core' ) ),
				),
			),
			'map'          => array(
				'title'  => __( 'Map', 'storebox-core' ),
				'fields' => array(
					'map_mode'        => array(
						'label'   => __( 'Map style', 'storebox-core' ),
						'type'    => 'select',
						'choices' => array(
							'live'         => __( 'Live map (map tiles from a tile server)', 'storebox-core' ),
							'illustrative' => __( 'Illustrative map (no external requests)', 'storebox-core' ),
						),
					),
					'map_consent'     => array( 'label' => __( 'Load the live map only after a click', 'storebox-core' ), 'type' => 'checkbox', 'description' => __( 'Shows the illustrative map with a “Load interactive map” button first — useful for privacy regulations.', 'storebox-core' ) ),
					'map_muted'       => array( 'label' => __( 'Muted map colours', 'storebox-core' ), 'type' => 'checkbox' ),
					'map_tiles'       => array( 'label' => __( 'Tile URL', 'storebox-core' ), 'type' => 'url', 'class' => 'large-text code', 'description' => __( 'OpenStreetMap tiles are fine for small sites. For heavy traffic use a tile provider such as MapTiler, Stadia or Mapbox and paste its URL template here.', 'storebox-core' ) ),
					'map_attribution' => array( 'label' => __( 'Tile attribution', 'storebox-core' ), 'type' => 'html', 'class' => 'large-text code', 'description' => __( 'Required by the tile provider.', 'storebox-core' ) ),
				),
			),
		);
	}

	/**
	 * Registers the setting and its fields.
	 */
	public static function register() {
		register_setting(
			'storebox_core',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);

		foreach ( self::sections() as $section_id => $section ) {
			add_settings_section( 'storebox_' . $section_id, $section['title'], '__return_false', self::PAGE );

			foreach ( $section['fields'] as $key => $field ) {
				add_settings_field(
					$key,
					$field['label'],
					array( __CLASS__, 'render_field' ),
					self::PAGE,
					'storebox_' . $section_id,
					array_merge( $field, array( 'key' => $key, 'label_for' => 'storebox-' . $key ) )
				);
			}
		}
	}

	/**
	 * Sanitizes the whole settings array; unknown keys are dropped.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		foreach ( self::sections() as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				$value = isset( $input[ $key ] ) ? $input[ $key ] : '';

				switch ( $field['type'] ) {
					case 'checkbox':
						$clean[ $key ] = empty( $value ) ? 0 : 1;
						break;
					case 'number':
						$number        = absint( $value );
						$clean[ $key ] = min( isset( $field['max'] ) ? $field['max'] : $number, max( isset( $field['min'] ) ? $field['min'] : 0, $number ) );
						break;
					case 'select':
						$clean[ $key ] = array_key_exists( $value, $field['choices'] ) ? $value : '';
						break;
					case 'email':
						$clean[ $key ] = sanitize_email( $value );
						break;
					case 'url':
						$clean[ $key ] = esc_url_raw( $value, array( 'http', 'https' ) );
						break;
					case 'editor':
						$clean[ $key ] = wp_kses_post( $value );
						break;
					case 'textarea':
						$clean[ $key ] = sanitize_textarea_field( $value );
						break;
					case 'html':
						$clean[ $key ] = wp_kses( $value, array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) );
						break;
					default:
						$clean[ $key ] = sanitize_text_field( $value );
				}
			}
		}

		// Page choices store IDs.
		foreach ( array( 'units_page', 'locations_page' ) as $page_key ) {
			$clean[ $page_key ] = absint( $clean[ $page_key ] );
		}

		return $clean;
	}

	/**
	 * Renders one field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function render_field( $args ) {
		$key   = $args['key'];
		$name  = self::OPTION . '[' . $key . ']';
		$id    = 'storebox-' . $key;
		$saved = get_option( self::OPTION, array() );
		$value = is_array( $saved ) && isset( $saved[ $key ] ) ? $saved[ $key ] : '';
		$class = isset( $args['class'] ) ? $args['class'] : 'regular-text';

		if ( '' === $value && ! in_array( $args['type'], array( 'text', 'email', 'editor', 'textarea' ), true ) ) {
			$defaults = storebox_core_settings_defaults();
			$value    = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
		}

		switch ( $args['type'] ) {
			case 'checkbox':
				printf(
					'<input type="hidden" name="%1$s" value="0"><label><input type="checkbox" id="%2$s" name="%1$s" value="1" %3$s> %4$s</label>',
					esc_attr( $name ),
					esc_attr( $id ),
					checked( ! empty( $value ), true, false ),
					esc_html__( 'Enabled', 'storebox-core' )
				);
				break;
			case 'select':
				printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ( $args['choices'] as $choice => $label ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $choice ), selected( (string) $value, (string) $choice, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;
			case 'number':
				printf(
					'<input type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$d" max="%5$d" class="small-text">',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value ),
					isset( $args['min'] ) ? (int) $args['min'] : 0,
					isset( $args['max'] ) ? (int) $args['max'] : 100
				);
				break;
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%2$s" rows="4" class="large-text" placeholder="%3$s">%4$s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ),
					esc_textarea( $value )
				);
				break;
			case 'editor':
				wp_editor(
					$value,
					$id,
					array(
						'textarea_name' => $name,
						'textarea_rows' => 6,
						'media_buttons' => false,
						'teeny'         => true,
					)
				);
				break;
			default:
				printf(
					'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="%5$s" placeholder="%6$s">',
					esc_attr( in_array( $args['type'], array( 'email', 'url' ), true ) ? $args['type'] : 'text' ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $class ),
					esc_attr( isset( $args['placeholder'] ) ? $args['placeholder'] : '' )
				);
		}

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Renders the page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Storebox settings', 'storebox-core' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'storebox_core' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
