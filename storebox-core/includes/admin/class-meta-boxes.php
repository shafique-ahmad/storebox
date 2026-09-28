<?php
/**
 * Unit and location fields, and extra term fields.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Admin;

use Storebox_Core\Post_Types;

defined( 'ABSPATH' ) || exit;

/**
 * Meta boxes.
 */
class Meta_Boxes {

	/**
	 * Nonce action.
	 */
	const NONCE = 'storebox_core_meta';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add' ) );
		add_action( 'save_post_sb_unit', array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'save_post_sb_location', array( __CLASS__, 'save' ), 10, 2 );

		foreach ( array( 'sb_unit_type', 'sb_unit_size', 'sb_unit_feature' ) as $taxonomy ) {
			add_action( $taxonomy . '_add_form_fields', array( __CLASS__, 'term_add_fields' ) );
			add_action( $taxonomy . '_edit_form_fields', array( __CLASS__, 'term_edit_fields' ), 10, 2 );
			add_action( 'created_' . $taxonomy, array( __CLASS__, 'term_save' ), 10, 1 );
			add_action( 'edited_' . $taxonomy, array( __CLASS__, 'term_save' ), 10, 1 );
		}
	}

	/**
	 * Registers the boxes.
	 */
	public static function add() {
		add_meta_box( 'storebox-unit', __( 'Unit details', 'storebox-core' ), array( __CLASS__, 'render_unit' ), 'sb_unit', 'normal', 'high' );
		add_meta_box( 'storebox-location', __( 'Location details', 'storebox-core' ), array( __CLASS__, 'render_location' ), 'sb_location', 'normal', 'high' );
		add_meta_box( 'storebox-gallery', __( 'Photos', 'storebox-core' ), array( __CLASS__, 'render_gallery' ), array( 'sb_unit', 'sb_location' ), 'normal', 'default' );
	}

	/**
	 * Prints a labelled input.
	 *
	 * @param array $field Field definition: key, label, type, value, step, suffix, description, placeholder, required.
	 */
	private static function field( $field ) {
		$field = wp_parse_args(
			$field,
			array(
				'type'        => 'text',
				'step'        => '',
				'suffix'      => '',
				'description' => '',
				'placeholder' => '',
				'required'    => false,
				'class'       => '',
			)
		);
		$id    = 'storebox-' . trim( $field['key'], '_' );
		?>
		<div class="sb-admin-field <?php echo esc_attr( $field['class'] ); ?>">
			<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field['label'] ); ?><?php echo $field['required'] ? ' <span aria-hidden="true">*</span>' : ''; ?></label>
			<?php if ( 'textarea' === $field['type'] ) : ?>
				<textarea id="<?php echo esc_attr( $id ); ?>" name="storebox_meta[<?php echo esc_attr( $field['key'] ); ?>]" rows="3" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_textarea( $field['value'] ); ?></textarea>
			<?php else : ?>
				<span class="sb-admin-field__control">
					<input id="<?php echo esc_attr( $id ); ?>" type="<?php echo esc_attr( $field['type'] ); ?>" name="storebox_meta[<?php echo esc_attr( $field['key'] ); ?>]" value="<?php echo esc_attr( $field['value'] ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"<?php echo $field['step'] ? ' step="' . esc_attr( $field['step'] ) . '" min="0"' : ''; ?><?php echo $field['required'] ? ' required' : ''; ?>>
					<?php if ( $field['suffix'] ) : ?>
						<span class="sb-admin-field__suffix"><?php echo esc_html( $field['suffix'] ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
			<?php if ( $field['description'] ) : ?>
				<p class="description"><?php echo esc_html( $field['description'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Unit box.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function render_unit( $post ) {
		wp_nonce_field( self::NONCE, 'storebox_meta_nonce' );

		$get      = static function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, $key, true );
		};
		$currency = storebox_core_setting( 'currency_symbol' );
		?>
		<div class="sb-admin-box">
			<h3 class="sb-admin-box__title"><?php esc_html_e( 'Size', 'storebox-core' ); ?></h3>
			<div class="sb-admin-grid">
				<?php
				self::field( array( 'key' => '_sb_area', 'label' => __( 'Floor area', 'storebox-core' ), 'type' => 'number', 'step' => '0.1', 'suffix' => 'm²', 'value' => $get( '_sb_area' ), 'required' => true ) );
				self::field( array( 'key' => '_sb_width', 'label' => __( 'Width', 'storebox-core' ), 'type' => 'number', 'step' => '0.1', 'suffix' => 'm', 'value' => $get( '_sb_width' ) ) );
				self::field( array( 'key' => '_sb_depth', 'label' => __( 'Depth', 'storebox-core' ), 'type' => 'number', 'step' => '0.1', 'suffix' => 'm', 'value' => $get( '_sb_depth' ) ) );
				self::field( array( 'key' => '_sb_ceiling', 'label' => __( 'Ceiling height', 'storebox-core' ), 'type' => 'number', 'step' => '0.1', 'suffix' => 'm', 'value' => $get( '_sb_ceiling' ) ) );
				self::field( array( 'key' => '_sb_floor', 'label' => __( 'Floor', 'storebox-core' ), 'value' => $get( '_sb_floor' ), 'placeholder' => __( 'Ground floor', 'storebox-core' ) ) );
				?>
			</div>

			<h3 class="sb-admin-box__title"><?php esc_html_e( 'Price and availability', 'storebox-core' ); ?></h3>
			<div class="sb-admin-grid">
				<?php
				self::field( array( 'key' => '_sb_price', 'label' => __( 'Price per month', 'storebox-core' ), 'type' => 'number', 'step' => '0.01', 'suffix' => $currency, 'value' => $get( '_sb_price' ), 'required' => true ) );
				self::field( array( 'key' => '_sb_available', 'label' => __( 'Units free now', 'storebox-core' ), 'type' => 'number', 'step' => '1', 'value' => $get( '_sb_available' ), 'description' => __( '0 shows "Fully booked" and switches the button to the waitlist.', 'storebox-core' ) ) );
				?>
				<div class="sb-admin-field">
					<label for="storebox-sb_location"><?php esc_html_e( 'Location', 'storebox-core' ); ?></label>
					<select id="storebox-sb_location" name="storebox_meta[_sb_location]">
						<option value="0"><?php esc_html_e( '— None —', 'storebox-core' ); ?></option>
						<?php
						$locations = get_posts(
							array(
								'post_type'      => 'sb_location',
								'post_status'    => array( 'publish', 'draft', 'private' ),
								'posts_per_page' => 100,
								'orderby'        => 'title',
								'order'          => 'ASC',
							)
						);
						foreach ( $locations as $location ) {
							printf( '<option value="%1$d" %2$s>%3$s</option>', (int) $location->ID, selected( absint( $get( '_sb_location' ) ), $location->ID, false ), esc_html( get_the_title( $location ) ) );
						}
						?>
					</select>
				</div>
				<div class="sb-admin-field">
					<span class="sb-admin-field__label"><?php esc_html_e( 'Featured', 'storebox-core' ); ?></span>
					<label class="sb-admin-check"><input type="checkbox" name="storebox_meta[_sb_featured]" value="1" <?php checked( '1', $get( '_sb_featured' ) ); ?>> <?php esc_html_e( 'Show in "Featured units" lists', 'storebox-core' ); ?></label>
				</div>
			</div>

			<h3 class="sb-admin-box__title"><?php esc_html_e( 'Description', 'storebox-core' ); ?></h3>
			<div class="sb-admin-grid sb-admin-grid--2">
				<?php
				self::field( array( 'key' => '_sb_fits', 'label' => __( 'Typically fits', 'storebox-core' ), 'value' => $get( '_sb_fits' ), 'placeholder' => __( 'A one-bedroom flat', 'storebox-core' ) ) );
				self::field( array( 'key' => '_sb_highlights', 'label' => __( 'Card highlights (optional)', 'storebox-core' ), 'type' => 'textarea', 'value' => $get( '_sb_highlights' ), 'description' => __( 'One per line. Unit cards can show these instead of the features.', 'storebox-core' ) ) );
				?>
			</div>
			<p class="description"><?php esc_html_e( 'Unit type, size group and features are set in the boxes on the right. The editor above holds the "What fits" text shown on the unit page; the excerpt is a one-line summary for cards.', 'storebox-core' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Location box.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function render_location( $post ) {
		wp_nonce_field( self::NONCE, 'storebox_meta_nonce' );

		$get   = static function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, $key, true );
		};
		$hours = $get( '_sb_hours' );
		$hours = is_array( $hours ) ? $hours : array();

		$address = trim( implode( ' ', array( $get( '_sb_street' ), $get( '_sb_postcode' ), $get( '_sb_city' ) ) ) );
		?>
		<div class="sb-admin-box">
			<h3 class="sb-admin-box__title"><?php esc_html_e( 'Address', 'storebox-core' ); ?></h3>
			<div class="sb-admin-grid">
				<?php
				self::field( array( 'key' => '_sb_area_name', 'label' => __( 'Short label', 'storebox-core' ), 'value' => $get( '_sb_area_name' ), 'placeholder' => __( 'Noord', 'storebox-core' ), 'description' => __( 'Shown above the name, e.g. the district.', 'storebox-core' ) ) );
				self::field( array( 'key' => '_sb_street', 'label' => __( 'Street and number', 'storebox-core' ), 'value' => $get( '_sb_street' ) ) );
				self::field( array( 'key' => '_sb_postcode', 'label' => __( 'Postcode', 'storebox-core' ), 'value' => $get( '_sb_postcode' ) ) );
				self::field( array( 'key' => '_sb_city', 'label' => __( 'City', 'storebox-core' ), 'value' => $get( '_sb_city' ) ) );
				?>
			</div>

			<h3 class="sb-admin-box__title"><?php esc_html_e( 'Contact, access and capacity', 'storebox-core' ); ?></h3>
			<div class="sb-admin-grid">
				<?php
				self::field( array( 'key' => '_sb_phone', 'label' => __( 'Phone', 'storebox-core' ), 'type' => 'tel', 'value' => $get( '_sb_phone' ) ) );
				self::field( array( 'key' => '_sb_email', 'label' => __( 'Email for enquiries', 'storebox-core' ), 'type' => 'email', 'value' => $get( '_sb_email' ), 'description' => __( 'Requests about this location go here.', 'storebox-core' ) ) );
				self::field( array( 'key' => '_sb_access', 'label' => __( 'Access hours', 'storebox-core' ), 'value' => $get( '_sb_access' ), 'placeholder' => '24/7' ) );
				self::field( array( 'key' => '_sb_units_total', 'label' => __( 'Units in total', 'storebox-core' ), 'type' => 'number', 'step' => '1', 'value' => $get( '_sb_units_total' ) ) );
				self::field( array( 'key' => '_sb_units_free', 'label' => __( 'Units free now', 'storebox-core' ), 'type' => 'number', 'step' => '1', 'value' => $get( '_sb_units_free' ) ) );
				?>
			</div>

			<div class="sb-admin-grid sb-admin-grid--2">
				<?php
				self::field( array( 'key' => '_sb_tags', 'label' => __( 'Facility tags', 'storebox-core' ), 'type' => 'textarea', 'value' => $get( '_sb_tags' ), 'description' => __( 'One per line, e.g. "Climate controlled". Shown as tags on cards and the location page.', 'storebox-core' ) ) );
				?>
				<div class="sb-admin-field">
					<span class="sb-admin-field__label"><?php esc_html_e( 'Office hours', 'storebox-core' ); ?></span>
					<table class="sb-admin-hours">
						<?php foreach ( storebox_core_location_hours( $post->ID ) as $row ) : ?>
							<tr>
								<th scope="row"><label for="storebox-hours-<?php echo esc_attr( $row['day'] ); ?>"><?php echo esc_html( $row['label'] ); ?></label></th>
								<td><input id="storebox-hours-<?php echo esc_attr( $row['day'] ); ?>" type="text" name="storebox_hours[<?php echo esc_attr( $row['day'] ); ?>]" value="<?php echo esc_attr( isset( $hours[ $row['day'] ] ) ? $hours[ $row['day'] ] : '' ); ?>" placeholder="<?php esc_attr_e( '09:00–17:30 or Closed', 'storebox-core' ); ?>"></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>

			<h3 class="sb-admin-box__title"><?php esc_html_e( 'Map position', 'storebox-core' ); ?></h3>
			<div class="sb-admin-grid">
				<?php
				self::field( array( 'key' => '_sb_lat', 'label' => __( 'Latitude', 'storebox-core' ), 'value' => $get( '_sb_lat' ), 'placeholder' => '52.3915' ) );
				self::field( array( 'key' => '_sb_lng', 'label' => __( 'Longitude', 'storebox-core' ), 'value' => $get( '_sb_lng' ), 'placeholder' => '4.8935' ) );
				?>
				<div class="sb-admin-field">
					<span class="sb-admin-field__label">&nbsp;</span>
					<a class="button" target="_blank" rel="noopener noreferrer" href="<?php echo esc_url( 'https://www.openstreetmap.org/search?query=' . rawurlencode( $address ) ); ?>"><?php esc_html_e( 'Find coordinates on OpenStreetMap', 'storebox-core' ); ?></a>
					<p class="description"><?php esc_html_e( 'Right-click the building on the map and choose "Show address" to see its coordinates.', 'storebox-core' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Gallery picker.
	 *
	 * @param \WP_Post $post Post.
	 */
	public static function render_gallery( $post ) {
		$ids = storebox_core_parse_ids( get_post_meta( $post->ID, '_sb_gallery', true ) );
		?>
		<div class="sb-admin-gallery" data-sb-gallery>
			<ul class="sb-admin-gallery__list" data-sb-gallery-list>
				<?php foreach ( $ids as $id ) : ?>
					<li data-id="<?php echo esc_attr( $id ); ?>">
						<?php echo wp_get_attachment_image( $id, 'thumbnail' ); ?>
						<button type="button" class="sb-admin-gallery__remove" aria-label="<?php esc_attr_e( 'Remove photo', 'storebox-core' ); ?>">&times;</button>
					</li>
				<?php endforeach; ?>
			</ul>
			<input type="hidden" name="storebox_meta[_sb_gallery]" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" data-sb-gallery-input>
			<p>
				<button type="button" class="button" data-sb-gallery-add><?php esc_html_e( 'Add photos', 'storebox-core' ); ?></button>
				<span class="description"><?php esc_html_e( 'Drag to reorder. The main photo (featured image) is always shown first.', 'storebox-core' ); ?></span>
			</p>
		</div>
		<?php
	}

	/**
	 * Saves unit and location fields.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post.
	 */
	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['storebox_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['storebox_meta_nonce'] ) ), self::NONCE ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = Post_Types::fields();
		if ( ! isset( $fields[ $post->post_type ] ) ) {
			return;
		}

		$input = isset( $_POST['storebox_meta'] ) && is_array( $_POST['storebox_meta'] ) ? wp_unslash( $_POST['storebox_meta'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each value is sanitized below.

		foreach ( $fields[ $post->post_type ] as $key => $field ) {
			$raw   = isset( $input[ $key ] ) ? $input[ $key ] : '';
			$value = call_user_func( $field[1], $raw );

			if ( '' === $value || null === $value || ( '_sb_location' === $key && 0 === $value ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}

		if ( 'sb_location' === $post->post_type && isset( $_POST['storebox_hours'] ) && is_array( $_POST['storebox_hours'] ) ) {
			$hours = Post_Types::sanitize_hours( wp_unslash( $_POST['storebox_hours'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by sanitize_hours().
			update_post_meta( $post_id, '_sb_hours', $hours );
		}
	}

	/**
	 * Term fields on the "Add" form.
	 *
	 * @param string $taxonomy Taxonomy.
	 */
	public static function term_add_fields( $taxonomy ) {
		wp_nonce_field( 'storebox_term_meta', 'storebox_term_nonce' );
		?>
		<div class="form-field">
			<label for="storebox-term-order"><?php esc_html_e( 'Order', 'storebox-core' ); ?></label>
			<input type="number" id="storebox-term-order" name="storebox_term_order" value="0" step="1">
			<p><?php esc_html_e( 'Lower numbers come first in filters and lists.', 'storebox-core' ); ?></p>
		</div>
		<?php if ( 'sb_unit_feature' === $taxonomy ) : ?>
			<div class="form-field">
				<label><input type="checkbox" name="storebox_term_card" value="1"> <?php esc_html_e( 'Show on unit cards', 'storebox-core' ); ?></label>
			</div>
			<?php
		endif;
	}

	/**
	 * Term fields on the "Edit" form.
	 *
	 * @param \WP_Term $term     Term.
	 * @param string   $taxonomy Taxonomy.
	 */
	public static function term_edit_fields( $term, $taxonomy ) {
		wp_nonce_field( 'storebox_term_meta', 'storebox_term_nonce' );
		?>
		<tr class="form-field">
			<th scope="row"><label for="storebox-term-order"><?php esc_html_e( 'Order', 'storebox-core' ); ?></label></th>
			<td>
				<input type="number" id="storebox-term-order" name="storebox_term_order" value="<?php echo esc_attr( (int) get_term_meta( $term->term_id, 'sb_order', true ) ); ?>" step="1">
				<p class="description"><?php esc_html_e( 'Lower numbers come first in filters and lists.', 'storebox-core' ); ?></p>
			</td>
		</tr>
		<?php if ( 'sb_unit_feature' === $taxonomy ) : ?>
			<tr class="form-field">
				<th scope="row"><?php esc_html_e( 'Unit cards', 'storebox-core' ); ?></th>
				<td><label><input type="checkbox" name="storebox_term_card" value="1" <?php checked( (bool) get_term_meta( $term->term_id, 'sb_card', true ) ); ?>> <?php esc_html_e( 'Show on unit cards', 'storebox-core' ); ?></label></td>
			</tr>
			<?php
		endif;
	}

	/**
	 * Saves term fields.
	 *
	 * @param int $term_id Term ID.
	 */
	public static function term_save( $term_id ) {
		if ( ! isset( $_POST['storebox_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['storebox_term_nonce'] ) ), 'storebox_term_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		if ( isset( $_POST['storebox_term_order'] ) ) {
			update_term_meta( $term_id, 'sb_order', intval( wp_unslash( $_POST['storebox_term_order'] ) ) );
		}

		$term = get_term( $term_id );
		if ( $term && 'sb_unit_feature' === $term->taxonomy ) {
			update_term_meta( $term_id, 'sb_card', ! empty( $_POST['storebox_term_card'] ) );
		}
	}
}
