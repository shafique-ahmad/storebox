<?php
/**
 * Per-page presentation settings: header transparency and page header.
 *
 * The same options appear in Elementor's Page Settings panel (see
 * inc/elementor.php); the Elementor value wins when both are set.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post types that get the Storebox page settings box.
 *
 * @return string[]
 */
function storebox_page_settings_post_types() {
	return apply_filters( 'storebox_page_settings_post_types', array( 'page', 'post', 'sb_unit', 'sb_location' ) );
}

/**
 * Registers the meta keys so they are protected and typed.
 */
function storebox_register_page_meta() {
	foreach ( storebox_page_settings_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			'_storebox_transparent_header',
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'storebox_sanitize_transparent_header',
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
		foreach ( array( '_storebox_hero_title', '_storebox_hero_intro' ) as $storebox_text_key ) {
			register_post_meta(
				$post_type,
				$storebox_text_key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_textarea_field',
					'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
		register_post_meta(
			$post_type,
			'_storebox_hide_hero',
			array(
				'type'              => 'boolean',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}
}
add_action( 'init', 'storebox_register_page_meta' );

/**
 * Sanitizes the transparent header value.
 *
 * @param string $value Raw value.
 * @return string '', 'on' or 'off'.
 */
function storebox_sanitize_transparent_header( $value ) {
	return in_array( $value, array( 'on', 'off' ), true ) ? $value : '';
}

/**
 * Adds the meta box.
 */
function storebox_add_page_settings_box() {
	foreach ( storebox_page_settings_post_types() as $post_type ) {
		add_meta_box(
			'storebox-page-settings',
			esc_html__( 'Storebox page settings', 'storebox' ),
			'storebox_render_page_settings_box',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'storebox_add_page_settings_box' );

/**
 * Meta box markup.
 *
 * @param WP_Post $post Current post.
 */
function storebox_render_page_settings_box( $post ) {
	wp_nonce_field( 'storebox_page_settings', 'storebox_page_settings_nonce' );

	$transparent = get_post_meta( $post->ID, '_storebox_transparent_header', true );
	$hide_hero   = (bool) get_post_meta( $post->ID, '_storebox_hide_hero', true );
	$hero_title  = get_post_meta( $post->ID, '_storebox_hero_title', true );
	$hero_intro  = get_post_meta( $post->ID, '_storebox_hero_intro', true );
	?>
	<p>
		<label for="storebox-hero-title"><strong><?php esc_html_e( 'Page header title', 'storebox' ); ?></strong></label>
		<input type="text" class="widefat" name="storebox_hero_title" id="storebox-hero-title" value="<?php echo esc_attr( $hero_title ); ?>">
		<span class="description"><?php esc_html_e( 'Optional headline for theme templates (not Elementor pages). The page title then appears above it as a small label.', 'storebox' ); ?></span>
	</p>
	<p>
		<label for="storebox-hero-intro"><strong><?php esc_html_e( 'Page header intro', 'storebox' ); ?></strong></label>
		<textarea class="widefat" rows="3" name="storebox_hero_intro" id="storebox-hero-intro"><?php echo esc_textarea( $hero_intro ); ?></textarea>
	</p>
	<p>
		<label for="storebox-transparent-header"><strong><?php esc_html_e( 'Overlay header', 'storebox' ); ?></strong></label><br>
		<select name="storebox_transparent_header" id="storebox-transparent-header" class="widefat">
			<option value="" <?php selected( $transparent, '' ); ?>><?php esc_html_e( 'Default (Customizer setting)', 'storebox' ); ?></option>
			<option value="on" <?php selected( $transparent, 'on' ); ?>><?php esc_html_e( 'Transparent over the first section', 'storebox' ); ?></option>
			<option value="off" <?php selected( $transparent, 'off' ); ?>><?php esc_html_e( 'Solid', 'storebox' ); ?></option>
		</select>
		<span class="description"><?php esc_html_e( 'Only used by the overlay header layout. Choose "Solid" if the page starts with a light section.', 'storebox' ); ?></span>
	</p>
	<p>
		<label>
			<input type="checkbox" name="storebox_hide_hero" value="1" <?php checked( $hide_hero ); ?>>
			<?php esc_html_e( 'Hide the page header (title) on theme templates', 'storebox' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Saves the meta box.
 *
 * @param int $post_id Post ID.
 */
function storebox_save_page_settings( $post_id ) {
	if ( ! isset( $_POST['storebox_page_settings_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['storebox_page_settings_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'storebox_page_settings' ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$transparent = isset( $_POST['storebox_transparent_header'] )
		? storebox_sanitize_transparent_header( sanitize_key( wp_unslash( $_POST['storebox_transparent_header'] ) ) )
		: '';

	if ( '' === $transparent ) {
		delete_post_meta( $post_id, '_storebox_transparent_header' );
	} else {
		update_post_meta( $post_id, '_storebox_transparent_header', $transparent );
	}

	if ( ! empty( $_POST['storebox_hide_hero'] ) ) {
		update_post_meta( $post_id, '_storebox_hide_hero', true );
	} else {
		delete_post_meta( $post_id, '_storebox_hide_hero' );
	}

	foreach ( array(
		'storebox_hero_title' => '_storebox_hero_title',
		'storebox_hero_intro' => '_storebox_hero_intro',
	) as $field => $meta_key ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $value );
		}
	}
}
add_action( 'save_post', 'storebox_save_page_settings' );

/**
 * Eyebrow, title and intro for a page header on theme templates.
 *
 * With a "Page header title" set, the page title becomes the eyebrow and the
 * custom headline the H1 (as on the demo's Blog page); otherwise the page title
 * is the H1. The intro falls back to the excerpt.
 *
 * @param int $post_id Post ID.
 * @return array{eyebrow: string, title: string, lede: string}
 */
function storebox_page_hero_text( $post_id ) {
	$custom_title = trim( (string) get_post_meta( $post_id, '_storebox_hero_title', true ) );
	$intro        = trim( (string) get_post_meta( $post_id, '_storebox_hero_intro', true ) );

	if ( '' === $intro && has_excerpt( $post_id ) ) {
		$intro = get_the_excerpt( $post_id );
	}

	return array(
		'eyebrow' => '' !== $custom_title ? get_the_title( $post_id ) : '',
		'title'   => '' !== $custom_title ? esc_html( $custom_title ) : esc_html( get_the_title( $post_id ) ),
		'lede'    => esc_html( $intro ),
	);
}
