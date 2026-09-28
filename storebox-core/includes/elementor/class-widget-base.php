<?php
/**
 * Base class of the Storebox widgets.
 *
 * Each widget renders one component (templates/{component}.php), so the
 * widgets, the theme templates and child-theme overrides share one markup.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Storebox_Core\Components;

defined( 'ABSPATH' ) || exit;

/**
 * Shared controls and rendering.
 */
abstract class Widget_Base extends \Elementor\Widget_Base {

	/**
	 * Component rendered by the widget.
	 *
	 * @var string
	 */
	protected $component = '';

	/**
	 * Widget category.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( 'storebox' );
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'storage' );
	}

	/**
	 * Styles the widget needs (registered by the plugin, loaded only when used).
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return Components::style_handles( array( $this->component ) );
	}

	/**
	 * Scripts the widget needs.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return Components::script_handles( array( $this->component ) );
	}

	/**
	 * Output depends on live data (availability, prices), so it is never cached.
	 *
	 * @return bool
	 */
	public function is_dynamic_content(): bool {
		return true;
	}

	/**
	 * Whether the widget renders inside the editor.
	 *
	 * @return bool
	 */
	protected function is_editor() {
		$elementor = \Elementor\Plugin::$instance;

		return ( isset( $elementor->editor ) && $elementor->editor->is_edit_mode() )
			|| ( isset( $elementor->preview ) && $elementor->preview->is_preview_mode() );
	}

	/**
	 * Whether a switcher is on.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Control ID.
	 * @return bool
	 */
	protected function on( $settings, $key ) {
		return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
	}

	/**
	 * A text setting, or null (component default) when empty.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Control ID.
	 * @return string|null
	 */
	protected function text_or_default( $settings, $key ) {
		return isset( $settings[ $key ] ) && '' !== trim( (string) $settings[ $key ] ) ? (string) $settings[ $key ] : null;
	}

	/**
	 * URL from a URL control.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Control ID.
	 * @return string
	 */
	protected function url( $settings, $key ) {
		return isset( $settings[ $key ]['url'] ) ? (string) $settings[ $key ]['url'] : '';
	}

	/**
	 * Resolves a post: the one picked, else the current post, else (in the
	 * editor only) the first one, so templates can be designed with real data.
	 *
	 * @param mixed  $selected  Selected ID.
	 * @param string $post_type Post type.
	 * @return int
	 */
	protected function resolve_post( $selected, $post_type ) {
		$id = absint( $selected );
		if ( $id && get_post_type( $id ) === $post_type ) {
			return $id;
		}

		$current = get_the_ID();
		if ( $current && get_post_type( $current ) === $post_type ) {
			return (int) $current;
		}

		if ( $this->is_editor() ) {
			$first = get_posts(
				array(
					'post_type'      => $post_type,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'orderby'        => array(
						'menu_order' => 'ASC',
						'date'       => 'DESC',
					),
					'no_found_rows'  => true,
				)
			);
			return $first ? (int) $first[0] : 0;
		}

		return 0;
	}

	/**
	 * Renders the component, or a hint in the editor when it has nothing to show.
	 *
	 * @param array $args Component arguments.
	 */
	protected function render_component( $args ) {
		$html = Components::render( $this->component, $args );

		if ( '' === trim( $html ) ) {
			if ( $this->is_editor() ) {
				echo '<div class="sb-e-empty">' . esc_html( $this->empty_message() ) . '</div>';
			}
			return;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Component templates escape their output.
	}

	/**
	 * Editor hint shown when the widget renders nothing.
	 *
	 * @return string
	 */
	protected function empty_message() {
		return __( 'Nothing to show yet — add content under Storebox in the dashboard, or check this widget’s settings.', 'storebox-core' );
	}

	/* ---------------------------------------------------------------------
	 * Option lists
	 * ------------------------------------------------------------------ */

	/**
	 * Published posts of a type, for select controls (built in the admin only).
	 *
	 * @param string $post_type Post type.
	 * @return array<int, string>
	 */
	protected static function post_options( $post_type ) {
		if ( ! is_admin() ) {
			return array();
		}

		$options = array();
		$posts   = get_posts(
			array(
				'post_type'              => $post_type,
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'orderby'                => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $posts as $post ) {
			$options[ $post->ID ] = '' !== $post->post_title ? $post->post_title : '#' . $post->ID;
		}

		return $options;
	}

	/**
	 * Terms of a taxonomy (slug => name), for select controls.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $any      Label of the empty option, or '' for none.
	 * @return array<string, string>
	 */
	protected static function term_options( $taxonomy, $any = '' ) {
		$options = '' !== $any ? array( '' => $any ) : array();

		if ( ! is_admin() || ! taxonomy_exists( $taxonomy ) ) {
			return $options;
		}

		$terms = 'category' === $taxonomy ? get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		) : storebox_core_get_terms_ordered( $taxonomy, false );

		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}

		return $options;
	}

	/**
	 * Registered image sizes.
	 *
	 * @return array<string, string>
	 */
	protected static function image_size_options() {
		$options = array();
		foreach ( get_intermediate_image_sizes() as $size ) {
			$options[ $size ] = ucwords( str_replace( array( '-', '_' ), ' ', $size ) );
		}
		$options['full'] = __( 'Full', 'storebox-core' );

		return $options;
	}

	/**
	 * Heading tags.
	 *
	 * @param string[] $tags Allowed tags.
	 * @return array<string, string>
	 */
	protected static function tag_options( $tags = array( 'h2', 'h3', 'h4', 'div' ) ) {
		return array_combine( $tags, array_map( 'strtoupper', $tags ) );
	}

	/* ---------------------------------------------------------------------
	 * Shared controls
	 * ------------------------------------------------------------------ */

	/**
	 * "Design" select: follow the theme, or force the soft or editorial look.
	 */
	protected function add_design_control() {
		$this->add_control(
			'design',
			array(
				'label'       => esc_html__( 'Design', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => array(
					''          => esc_html__( 'Theme default', 'storebox-core' ),
					'soft'      => esc_html__( 'Soft', 'storebox-core' ),
					'editorial' => esc_html__( 'Editorial', 'storebox-core' ),
				),
				'description' => esc_html__( 'Theme default follows Appearance → Customize → Storebox → Design.', 'storebox-core' ),
			)
		);
	}

	/**
	 * Select of one post of a type, with "current" as the default.
	 *
	 * @param string $id        Control ID.
	 * @param string $label     Label.
	 * @param string $post_type Post type.
	 * @param string $current   Label of the "current post" option.
	 */
	protected function add_post_select( $id, $label, $post_type, $current ) {
		$this->add_control(
			$id,
			array(
				'label'   => $label,
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array( '' => $current ) + static::post_options( $post_type ),
			)
		);
	}

	/**
	 * Heading control inside a section.
	 *
	 * @param string $id    Control ID.
	 * @param string $label Label.
	 */
	protected function add_heading( $id, $label ) {
		$this->add_control(
			$id,
			array(
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
	}

	/**
	 * Color and typography of a text element.
	 *
	 * @param string $id       Control ID prefix.
	 * @param string $label    Heading label ('' for none).
	 * @param string $selector CSS selector (relative to {{WRAPPER}}).
	 * @param bool   $hover    Add a hover color.
	 */
	protected function add_text_style( $id, $label, $selector, $hover = false ) {
		if ( '' !== $label ) {
			$this->add_heading( $id . '_heading', $label );
		}

		$this->add_control(
			$id . '_color',
			array(
				'label'     => esc_html__( 'Color', 'storebox-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $selector => 'color: {{VALUE}};',
				),
			)
		);

		if ( $hover ) {
			$this->add_control(
				$id . '_hover_color',
				array(
					'label'     => esc_html__( 'Hover color', 'storebox-core' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . $selector . ':hover' => 'color: {{VALUE}};',
					),
				)
			);
		}

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $id . '_typography',
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
	}

	/**
	 * Background, border, radius, shadow and padding of a box.
	 *
	 * @param string $id       Control ID prefix.
	 * @param string $selector CSS selector (relative to {{WRAPPER}}).
	 * @param array  $parts    Which controls to add.
	 */
	protected function add_box_style( $id, $selector, $parts = array( 'background', 'border', 'radius', 'shadow', 'padding' ) ) {
		$target = '{{WRAPPER}} ' . $selector;

		if ( in_array( 'background', $parts, true ) ) {
			$this->add_control(
				$id . '_background',
				array(
					'label'     => esc_html__( 'Background color', 'storebox-core' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$target => 'background-color: {{VALUE}};',
					),
				)
			);
		}

		if ( in_array( 'border', $parts, true ) ) {
			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => $id . '_border',
					'selector' => $target,
				)
			);
		}

		if ( in_array( 'radius', $parts, true ) ) {
			$this->add_responsive_control(
				$id . '_radius',
				array(
					'label'      => esc_html__( 'Border radius', 'storebox-core' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array(
						$target => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);
		}

		if ( in_array( 'shadow', $parts, true ) ) {
			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => $id . '_shadow',
					'selector' => $target,
				)
			);
		}

		if ( in_array( 'padding', $parts, true ) ) {
			$this->add_responsive_control(
				$id . '_padding',
				array(
					'label'      => esc_html__( 'Padding', 'storebox-core' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', 'rem' ),
					'selectors'  => array(
						$target => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);
		}
	}

	/**
	 * Typography, colors (normal / hover), radius and padding of a button.
	 *
	 * @param string $id       Control ID prefix.
	 * @param string $selector CSS selector (relative to {{WRAPPER}}).
	 */
	protected function add_button_style( $id, $selector ) {
		$target = '{{WRAPPER}} ' . $selector;

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $id . '_typography',
				'selector' => $target,
			)
		);

		$this->start_controls_tabs( $id . '_tabs' );

		foreach ( array(
			'normal' => esc_html__( 'Normal', 'storebox-core' ),
			'hover'  => esc_html__( 'Hover', 'storebox-core' ),
		) as $state => $state_label ) {
			$state_target = 'hover' === $state ? $target . ':hover, ' . $target . ':focus-visible' : $target;

			$this->start_controls_tab( $id . '_tab_' . $state, array( 'label' => $state_label ) );

			$this->add_control(
				$id . '_' . $state . '_color',
				array(
					'label'     => esc_html__( 'Text color', 'storebox-core' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$state_target => 'color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				$id . '_' . $state . '_background',
				array(
					'label'     => esc_html__( 'Background color', 'storebox-core' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$state_target => 'background-color: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				$id . '_' . $state . '_border',
				array(
					'label'     => esc_html__( 'Border color', 'storebox-core' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$state_target => 'border-color: {{VALUE}};',
					),
				)
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();

		$this->add_responsive_control(
			$id . '_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'storebox-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'separator'  => 'before',
				'selectors'  => array(
					$target => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			$id . '_padding',
			array(
				'label'      => esc_html__( 'Padding', 'storebox-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					$target => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);
	}

	/**
	 * Responsive column count for a CSS grid.
	 *
	 * @param string $id       Control ID.
	 * @param string $selector Grid selector (relative to {{WRAPPER}}).
	 * @param int    $default  Desktop default.
	 * @param int    $max      Largest column count.
	 */
	protected function add_columns_control( $id, $selector, $default = 3, $max = 4 ) {
		$options = array();
		for ( $i = 1; $i <= $max; $i++ ) {
			$options[ (string) $i ] = (string) $i;
		}

		$this->add_responsive_control(
			$id,
			array(
				'label'          => esc_html__( 'Columns', 'storebox-core' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => (string) $default,
				'tablet_default' => (string) min( 2, $default ),
				'mobile_default' => '1',
				'options'        => $options,
				'selectors'      => array(
					'{{WRAPPER}} ' . $selector => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);
	}

	/**
	 * Size list controls shared by the size calculator, chooser and guide.
	 *
	 * @param bool $with_practice Include the "In practice" column.
	 */
	protected function add_sizes_controls( $with_practice = false ) {
		$this->add_control(
			'sizes_source',
			array(
				'label'       => esc_html__( 'Sizes', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'default',
				'options'     => array(
					'default' => esc_html__( 'Standard sizes', 'storebox-core' ),
					'custom'  => esc_html__( 'Custom sizes', 'storebox-core' ),
				),
				'description' => esc_html__( 'Standard sizes: 2, 5, 10, 15, 20 and 30 m². Developers can change them with the storebox_core/default_sizes filter.', 'storebox-core' ),
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'm2',
			array(
				'label'   => esc_html__( 'Floor area (m²)', 'storebox-core' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0.5,
				'step'    => 0.5,
				'default' => 5,
			)
		);
		$repeater->add_control(
			'dims',
			array(
				'label'   => esc_html__( 'Dimensions', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '2.0 × 2.5 m',
			)
		);
		$repeater->add_control(
			'fits',
			array(
				'label'   => esc_html__( 'Typically fits', 'storebox-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'A one-bedroom flat', 'storebox-core' ),
			)
		);
		$repeater->add_control(
			'ref',
			array(
				'label'       => esc_html__( 'Comparison', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'a one-bedroom flat', 'storebox-core' ),
				'description' => esc_html__( 'Shown as “about …”.', 'storebox-core' ),
			)
		);
		if ( $with_practice ) {
			$repeater->add_control(
				'practice',
				array(
					'label'   => esc_html__( 'In practice', 'storebox-core' ),
					'type'    => Controls_Manager::TEXT,
					'default' => esc_html__( 'Double bed, sofa, table and chairs, ~20 boxes', 'storebox-core' ),
				)
			);
		}
		$repeater->add_control(
			'price',
			array(
				'label'   => esc_html__( 'Price from (per month)', 'storebox-core' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'step'    => 1,
				'default' => 59,
			)
		);

		$this->add_control(
			'sizes',
			array(
				'label'       => esc_html__( 'Sizes', 'storebox-core' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ m2 }}} m²',
				'default'     => array_map(
					static function ( $size ) {
						return array(
							'm2'       => $size['m2'],
							'dims'     => $size['dims'],
							'fits'     => $size['fits'],
							'ref'      => $size['ref'],
							'practice' => $size['practice'],
							'price'    => $size['price'],
						);
					},
					storebox_core_default_sizes()
				),
				'condition'   => array( 'sizes_source' => 'custom' ),
			)
		);

		$this->add_control(
			'sync_prices',
			array(
				'label'       => esc_html__( 'Take prices from units', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Uses the lowest monthly price of the units with that floor area, when there are any.', 'storebox-core' ),
			)
		);
	}

	/**
	 * Size list from the size controls (null = standard sizes).
	 *
	 * @param array $settings Settings.
	 * @return array|null
	 */
	protected function sizes_from_settings( $settings ) {
		if ( ! isset( $settings['sizes_source'] ) || 'custom' !== $settings['sizes_source'] || empty( $settings['sizes'] ) ) {
			return null;
		}

		$sizes = array();
		foreach ( (array) $settings['sizes'] as $row ) {
			$sizes[] = array(
				'm2'       => isset( $row['m2'] ) ? $row['m2'] : 0,
				'dims'     => isset( $row['dims'] ) ? $row['dims'] : '',
				'fits'     => isset( $row['fits'] ) ? $row['fits'] : '',
				'ref'      => isset( $row['ref'] ) ? $row['ref'] : '',
				'practice' => isset( $row['practice'] ) ? $row['practice'] : '',
				'price'    => isset( $row['price'] ) ? $row['price'] : 0,
			);
		}

		return $sizes;
	}

	/**
	 * Responsive gap of a grid or flex container.
	 *
	 * @param string $id       Control ID.
	 * @param string $selector Container selector (relative to {{WRAPPER}}).
	 * @param string $label    Label.
	 */
	protected function add_gap_control( $id, $selector, $label = '' ) {
		$this->add_responsive_control(
			$id,
			array(
				'label'      => '' !== $label ? $label : esc_html__( 'Gap', 'storebox-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $selector => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);
	}
}
