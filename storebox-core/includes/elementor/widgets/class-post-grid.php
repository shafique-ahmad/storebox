<?php
/**
 * Widget: Post Grid — latest, category, related or archive posts as cards or a list.
 *
 * @package Storebox_Core
 */

namespace Storebox_Core\Elementor\Widgets;

use Elementor\Controls_Manager;
use Storebox_Core\Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Post Grid widget.
 */
class Post_Grid extends Widget_Base {

	/**
	 * Component.
	 *
	 * @var string
	 */
	protected $component = 'post-grid';

	/**
	 * Name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'storebox-post-grid';
	}

	/**
	 * Title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return esc_html__( 'Post Grid', 'storebox-core' );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-posts-grid';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array {
		return array( 'storebox', 'posts', 'blog', 'articles', 'news', 'archive', 'related' );
	}

	/**
	 * Category options (ID => name).
	 *
	 * @return array<int, string>
	 */
	private static function category_options() {
		if ( ! is_admin() ) {
			return array();
		}

		$options = array();
		$terms   = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);

		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}

	/**
	 * Controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section( 'section_posts', array( 'label' => esc_html__( 'Posts', 'storebox-core' ) ) );

		$this->add_design_control();

		$this->add_control(
			'source',
			array(
				'label'       => esc_html__( 'Show', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'latest',
				'options'     => array(
					'latest'   => esc_html__( 'Latest posts', 'storebox-core' ),
					'category' => esc_html__( 'Posts from categories', 'storebox-core' ),
					'related'  => esc_html__( 'Related posts (same category)', 'storebox-core' ),
					'current'  => esc_html__( 'Current query (archive templates)', 'storebox-core' ),
				),
				'description' => esc_html__( '“Current query” shows the posts of the archive, category or search page the template is used on.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'category',
			array(
				'label'       => esc_html__( 'Categories', 'storebox-core' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => self::category_options(),
				'condition'   => array( 'source' => 'category' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'     => esc_html__( 'Number of posts', 'storebox-core' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 48,
				'default'   => 6,
				'condition' => array( 'source!' => 'current' ),
			)
		);

		$this->add_control(
			'offset',
			array(
				'label'     => esc_html__( 'Skip the first', 'storebox-core' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 20,
				'default'   => 0,
				'condition' => array( 'source' => array( 'latest', 'category' ) ),
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'     => esc_html__( 'Pagination', 'storebox-core' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array( 'source' => array( 'latest', 'category' ) ),
			)
		);

		$this->add_control(
			'chips',
			array(
				'label'       => esc_html__( 'Category links above the posts', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__( 'Links to each category archive, with the current one highlighted.', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'Layout', 'storebox-core' ) ) );

		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto' => esc_html__( 'Automatic (by design)', 'storebox-core' ),
					'grid' => esc_html__( 'Cards', 'storebox-core' ),
					'list' => esc_html__( 'Lead story and list', 'storebox-core' ),
				),
			)
		);

		$this->add_columns_control( 'columns', '.sb-pgrid__items', 3, 4 );

		$this->add_control(
			'featured',
			array(
				'label'       => esc_html__( 'Feature the first post', 'storebox-core' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'description' => esc_html__( 'Shows the newest post larger, on the first page only.', 'storebox-core' ),
			)
		);

		$this->add_control(
			'heading_tag',
			array(
				'label'   => esc_html__( 'Title tag', 'storebox-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => static::tag_options( array( 'h2', 'h3', 'h4' ) ),
			)
		);

		foreach ( array(
			'show_image'     => esc_html__( 'Image', 'storebox-core' ),
			'show_meta'      => esc_html__( 'Category and date', 'storebox-core' ),
			'show_read_time' => esc_html__( 'Reading time', 'storebox-core' ),
			'show_excerpt'   => esc_html__( 'Excerpt', 'storebox-core' ),
		) as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'   => $label,
					'type'    => Controls_Manager::SWITCHER,
					'default' => 'yes',
				)
			);
		}

		$this->add_control(
			'excerpt_length',
			array(
				'label'     => esc_html__( 'Excerpt length (words)', 'storebox-core' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 5,
				'max'       => 80,
				'default'   => 22,
				'condition' => array( 'show_excerpt' => 'yes' ),
			)
		);

		$this->add_control(
			'read_more',
			array(
				'label'       => esc_html__( 'Lead story link text', 'storebox-core' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Read the article', 'storebox-core' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_posts',
			array(
				'label' => esc_html__( 'Posts', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_gap_control( 'gap', '.sb-pgrid__items' );
		$this->add_box_style( 'image', '.sb-post__img, {{WRAPPER}} .sb-lead__img, {{WRAPPER}} .sb-prow__img', array( 'radius' ) );
		$this->add_text_style( 'meta_text', esc_html__( 'Category and date', 'storebox-core' ), '.sb-meta' );
		$this->add_text_style( 'category_text', esc_html__( 'Category', 'storebox-core' ), '.sb-meta__cat' );
		$this->add_text_style( 'title_text', esc_html__( 'Title', 'storebox-core' ), '.sb-post__title, {{WRAPPER}} .sb-prow__title, {{WRAPPER}} .sb-lead__title' );
		$this->add_text_style( 'excerpt_text', esc_html__( 'Excerpt', 'storebox-core' ), '.sb-post__excerpt, {{WRAPPER}} .sb-prow__excerpt, {{WRAPPER}} .sb-lede' );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_nav',
			array(
				'label' => esc_html__( 'Category links and pagination', 'storebox-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_heading( 'chips_heading', esc_html__( 'Category links', 'storebox-core' ) );
		$this->add_button_style( 'chip', '.sb-chip' );
		$this->add_heading( 'pager_heading', esc_html__( 'Pagination', 'storebox-core' ) );
		$this->add_button_style( 'pager', '.sb-pager .page-numbers' );

		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		$this->render_component(
			array(
				'style'          => isset( $s['design'] ) ? $s['design'] : '',
				'layout'         => isset( $s['layout'] ) ? $s['layout'] : 'auto',
				'source'         => isset( $s['source'] ) ? $s['source'] : 'latest',
				'category'       => isset( $s['category'] ) ? array_map( 'absint', (array) $s['category'] ) : array(),
				'count'          => isset( $s['count'] ) ? absint( $s['count'] ) : 6,
				'offset'         => isset( $s['offset'] ) ? absint( $s['offset'] ) : 0,
				'featured'       => $this->on( $s, 'featured' ),
				'chips'          => $this->on( $s, 'chips' ),
				'pagination'     => $this->on( $s, 'pagination' ),
				'columns'        => ! empty( $s['columns'] ) ? absint( $s['columns'] ) : 3,
				'show_image'     => $this->on( $s, 'show_image' ),
				'show_meta'      => $this->on( $s, 'show_meta' ),
				'show_read_time' => $this->on( $s, 'show_read_time' ),
				'show_excerpt'   => $this->on( $s, 'show_excerpt' ),
				'excerpt_length' => isset( $s['excerpt_length'] ) ? absint( $s['excerpt_length'] ) : 22,
				'read_more'      => isset( $s['read_more'] ) ? $s['read_more'] : '',
				'heading_tag'    => isset( $s['heading_tag'] ) ? $s['heading_tag'] : 'h3',
			)
		);
	}

	/**
	 * Editor hint.
	 *
	 * @return string
	 */
	protected function empty_message() {
		return __( 'No posts match these settings yet.', 'storebox-core' );
	}
}
