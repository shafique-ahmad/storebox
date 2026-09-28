<?php
/**
 * Customizer: presentation options only. Business data (units, locations,
 * enquiries) lives in the Storebox Core plugin.
 *
 * @package Storebox
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitizes a checkbox value.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function storebox_sanitize_checkbox( $value ) {
	return (bool) $value && 'false' !== $value;
}

/**
 * Sanitizes a select/radio value against the control's choices.
 *
 * @param string               $value   Raw value.
 * @param WP_Customize_Setting $setting Setting instance.
 * @return string
 */
function storebox_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$choices = $control ? $control->choices : array();

	return array_key_exists( $value, $choices ) ? $value : $setting->default;
}

/**
 * Sanitizes a hex colour that may also be empty (empty = preset colour).
 *
 * @param string $value Raw value.
 * @return string
 */
function storebox_sanitize_optional_color( $value ) {
	return '' === $value ? '' : (string) sanitize_hex_color( $value );
}

/**
 * Sanitizes short text that may contain basic inline formatting.
 *
 * @param string $value Raw value.
 * @return string
 */
function storebox_sanitize_inline_html( $value ) {
	return wp_kses(
		$value,
		array(
			'a'      => array( 'href' => true ),
			'strong' => array(),
			'em'     => array(),
			'br'     => array(),
		)
	);
}

/**
 * Customizer structure: sections and their controls.
 *
 * @return array
 */
function storebox_customizer_sections() {
	$transparent_choices = array(
		'all'   => __( 'On every page', 'storebox' ),
		'front' => __( 'Front page only', 'storebox' ),
		'none'  => __( 'Never (always solid)', 'storebox' ),
	);

	return array(
		'storebox_design'  => array(
			'title'       => __( 'Design', 'storebox' ),
			'description' => __( 'The preset switches the look of theme templates, the header and Storebox widgets between the two demo designs.', 'storebox' ),
			'controls'    => array(
				'preset'      => array(
					'label'   => __( 'Design preset', 'storebox' ),
					'type'    => 'radio',
					'choices' => array(
						'soft'      => __( 'Soft — rounded cards, dark page headers (Self Storage)', 'storebox' ),
						'editorial' => __( 'Editorial — square corners, rules and lists (Business Storage)', 'storebox' ),
					),
				),
				'button_lift' => array(
					'label' => __( 'Lift buttons slightly on hover', 'storebox' ),
					'type'  => 'checkbox',
				),
				'reveal'      => array(
					'label'       => __( 'Reveal content on scroll in theme templates', 'storebox' ),
					'description' => __( 'Visitors who prefer reduced motion never see the animation.', 'storebox' ),
					'type'        => 'checkbox',
				),
				'local_fonts' => array(
					'label'       => __( 'Use the bundled Archivo font', 'storebox' ),
					'description' => __( 'Served from your own site — no request to Google. Also used by Elementor when a Global Font is set to Archivo.', 'storebox' ),
					'type'        => 'checkbox',
				),
			),
		),
		'storebox_colors'  => array(
			'title'       => __( 'Colours', 'storebox' ),
			'description' => __( 'Leave a colour empty to use the preset colour. When Elementor is active, its Global Colors (Site Settings) take priority.', 'storebox' ),
			'controls'    => array(
				'color_primary'    => array( 'label' => __( 'Primary', 'storebox' ), 'type' => 'color' ),
				'color_secondary'  => array( 'label' => __( 'Secondary (highlight)', 'storebox' ), 'type' => 'color' ),
				'color_accent'     => array( 'label' => __( 'Accent', 'storebox' ), 'type' => 'color' ),
				'color_text'       => array( 'label' => __( 'Text', 'storebox' ), 'type' => 'color' ),
				'color_heading'    => array( 'label' => __( 'Headings', 'storebox' ), 'type' => 'color' ),
				'color_background' => array( 'label' => __( 'Background', 'storebox' ), 'type' => 'color' ),
				'color_surface'    => array( 'label' => __( 'Surface (tinted sections)', 'storebox' ), 'type' => 'color' ),
				'color_border'     => array( 'label' => __( 'Borders', 'storebox' ), 'type' => 'color' ),
				'color_dark'       => array( 'label' => __( 'Dark (footer)', 'storebox' ), 'type' => 'color' ),
				'color_muted'      => array( 'label' => __( 'Muted text', 'storebox' ), 'type' => 'color' ),
			),
		),
		'storebox_header'  => array(
			'title'       => __( 'Header', 'storebox' ),
			'description' => __( 'Used when no Elementor Pro header template is assigned. Menu items come from Appearance → Menus.', 'storebox' ),
			'controls'    => array(
				'header_layout'      => array(
					'label'   => __( 'Header layout', 'storebox' ),
					'type'    => 'select',
					'choices' => array(
						'auto'    => __( 'Match the design preset', 'storebox' ),
						'overlay' => __( 'Overlay — transparent over the page header, solid on scroll', 'storebox' ),
						'classic' => __( 'Classic — info bar and sticky light header', 'storebox' ),
					),
				),
				'header_transparent' => array(
					'label'       => __( 'Transparent overlay header', 'storebox' ),
					'description' => __( 'Overlay layout only. A page can override this in its Storebox page settings.', 'storebox' ),
					'type'        => 'select',
					'choices'     => $transparent_choices,
				),
				'logo_light'         => array(
					'label'       => __( 'Logo for dark backgrounds', 'storebox' ),
					'description' => __( 'Used in the overlay header and the footer. Falls back to the site logo.', 'storebox' ),
					'type'        => 'media',
				),
				'header_phone'       => array( 'label' => __( 'Phone number', 'storebox' ), 'type' => 'text' ),
				'header_cta_text'    => array( 'label' => __( 'Button text', 'storebox' ), 'type' => 'text' ),
				'header_cta_url'     => array( 'label' => __( 'Button link', 'storebox' ), 'type' => 'url' ),
				'topbar_enable'      => array(
					'label' => __( 'Show the info bar (classic layout)', 'storebox' ),
					'type'  => 'checkbox',
				),
				'topbar_item_1'      => array( 'label' => __( 'Info bar: opening hours text', 'storebox' ), 'type' => 'text' ),
				'topbar_item_2'      => array( 'label' => __( 'Info bar: area text', 'storebox' ), 'type' => 'text' ),
				'topbar_email'       => array( 'label' => __( 'Info bar: email address', 'storebox' ), 'type' => 'email' ),
			),
		),
		'storebox_footer'  => array(
			'title'       => __( 'Footer', 'storebox' ),
			'description' => __( 'Footer columns show the "Footer column" menus, or widgets if a footer widget area is used.', 'storebox' ),
			'controls'    => array(
				'footer_text'      => array( 'label' => __( 'Short description', 'storebox' ), 'type' => 'textarea' ),
				'footer_copyright' => array(
					'label'       => __( 'Copyright line', 'storebox' ),
					'description' => __( '{year} and {site} are replaced automatically.', 'storebox' ),
					'type'        => 'text',
				),
				'footer_note'      => array( 'label' => __( 'Second line (right)', 'storebox' ), 'type' => 'inline_html' ),
				'social_facebook'  => array( 'label' => __( 'Facebook URL', 'storebox' ), 'type' => 'url' ),
				'social_instagram' => array( 'label' => __( 'Instagram URL', 'storebox' ), 'type' => 'url' ),
				'social_linkedin'  => array( 'label' => __( 'LinkedIn URL', 'storebox' ), 'type' => 'url' ),
				'social_x'         => array( 'label' => __( 'X (Twitter) URL', 'storebox' ), 'type' => 'url' ),
				'social_youtube'   => array( 'label' => __( 'YouTube URL', 'storebox' ), 'type' => 'url' ),
			),
		),
		'storebox_cta'     => array(
			'title'       => __( 'Call to action band', 'storebox' ),
			'description' => __( 'A closing band on blog, archive, search and unit/location templates. Elementor pages add their own.', 'storebox' ),
			'controls'    => array(
				'cta_enable'    => array( 'label' => __( 'Show the band', 'storebox' ), 'type' => 'checkbox' ),
				'cta_eyebrow'   => array( 'label' => __( 'Eyebrow', 'storebox' ), 'type' => 'text' ),
				'cta_title'     => array( 'label' => __( 'Heading', 'storebox' ), 'type' => 'text' ),
				'cta_text'      => array( 'label' => __( 'Text', 'storebox' ), 'type' => 'textarea' ),
				'cta_btn1_text' => array( 'label' => __( 'Primary button text', 'storebox' ), 'type' => 'text' ),
				'cta_btn1_url'  => array( 'label' => __( 'Primary button link', 'storebox' ), 'type' => 'url' ),
				'cta_btn2_text' => array( 'label' => __( 'Secondary button text', 'storebox' ), 'type' => 'text' ),
				'cta_btn2_url'  => array(
					'label'       => __( 'Secondary button link', 'storebox' ),
					'description' => __( 'A tel: link works too, e.g. tel:+31200000000.', 'storebox' ),
					'type'        => 'url',
				),
				'cta_image'     => array( 'label' => __( 'Image', 'storebox' ), 'type' => 'media' ),
			),
		),
		'storebox_hero'    => array(
			'title'    => __( 'Page headers', 'storebox' ),
			'controls' => array(
				'hero_image'  => array(
					'label'       => __( 'Default page header image', 'storebox' ),
					'description' => __( 'Soft preset: used behind page titles when a page or post has no featured image.', 'storebox' ),
					'type'        => 'media',
				),
				'breadcrumbs' => array( 'label' => __( 'Show breadcrumbs', 'storebox' ), 'type' => 'checkbox' ),
			),
		),
		'storebox_blog'    => array(
			'title'    => __( 'Blog', 'storebox' ),
			'controls' => array(
				'blog_chips'           => array( 'label' => __( 'Category links above the post list', 'storebox' ), 'type' => 'checkbox' ),
				'blog_read_time'       => array( 'label' => __( 'Show reading time', 'storebox' ), 'type' => 'checkbox' ),
				'blog_author_box'      => array( 'label' => __( 'Show the author box on posts', 'storebox' ), 'type' => 'checkbox' ),
				'blog_share'           => array( 'label' => __( 'Show share links on posts', 'storebox' ), 'type' => 'checkbox' ),
				'blog_related'         => array( 'label' => __( 'Show related posts', 'storebox' ), 'type' => 'checkbox' ),
				'blog_related_eyebrow' => array( 'label' => __( 'Related posts eyebrow', 'storebox' ), 'type' => 'text' ),
				'blog_related_title'   => array( 'label' => __( 'Related posts heading', 'storebox' ), 'type' => 'text' ),
				'blog_excerpt_length'  => array(
					'label'       => __( 'Excerpt length (words)', 'storebox' ),
					'type'        => 'number',
					'input_attrs' => array(
						'min' => 5,
						'max' => 80,
					),
				),
			),
		),
		'storebox_notfound' => array(
			'title'       => __( '404 page', 'storebox' ),
			'description' => __( 'Used when no Elementor Pro 404 template is assigned.', 'storebox' ),
			'controls'    => array(
				'notfound_title' => array( 'label' => __( 'Heading', 'storebox' ), 'type' => 'text' ),
				'notfound_text'  => array( 'label' => __( 'Text', 'storebox' ), 'type' => 'textarea' ),
			),
		),
	);
}

/**
 * Registers the Storebox panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function storebox_customize_register( $wp_customize ) {
	$defaults = storebox_defaults();

	$wp_customize->add_panel(
		'storebox',
		array(
			'title'    => __( 'Storebox', 'storebox' ),
			'priority' => 30,
		)
	);

	$sanitizers = array(
		'checkbox'    => 'storebox_sanitize_checkbox',
		'radio'       => 'storebox_sanitize_choice',
		'select'      => 'storebox_sanitize_choice',
		'text'        => 'sanitize_text_field',
		'textarea'    => 'sanitize_textarea_field',
		'inline_html' => 'storebox_sanitize_inline_html',
		'url'         => 'esc_url_raw',
		'email'       => 'sanitize_email',
		'color'       => 'storebox_sanitize_optional_color',
		'media'       => 'absint',
		'number'      => 'absint',
	);

	$priority = 10;
	foreach ( storebox_customizer_sections() as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title'       => $section['title'],
				'description' => isset( $section['description'] ) ? $section['description'] : '',
				'panel'       => 'storebox',
				'priority'    => $priority,
			)
		);
		$priority += 10;

		foreach ( $section['controls'] as $key => $args ) {
			$setting_id = 'storebox_' . $key;
			$type       = $args['type'];

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
					'sanitize_callback' => $sanitizers[ $type ],
					'transport'         => 'refresh',
				)
			);

			$control_args = array(
				'label'       => $args['label'],
				'description' => isset( $args['description'] ) ? $args['description'] : '',
				'section'     => $section_id,
				'settings'    => $setting_id,
			);

			if ( 'color' === $type ) {
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting_id, $control_args ) );
				continue;
			}

			if ( 'media' === $type ) {
				$control_args['mime_type'] = 'image';
				$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $setting_id, $control_args ) );
				continue;
			}

			$control_args['type'] = 'inline_html' === $type ? 'text' : $type;

			if ( isset( $args['choices'] ) ) {
				$control_args['choices'] = $args['choices'];
			}
			if ( isset( $args['input_attrs'] ) ) {
				$control_args['input_attrs'] = $args['input_attrs'];
			}

			$wp_customize->add_control( $setting_id, $control_args );
		}
	}

	// Live preview for the site title and description.
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.sb-logo__text',
				'render_callback' => static function () {
					return esc_html( get_bloginfo( 'name' ) );
				},
			)
		);
	}
}
add_action( 'customize_register', 'storebox_customize_register' );
