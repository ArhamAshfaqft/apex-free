<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 100% Customizable Archive Loop Widget
 * Renders flexible post/archive grids, lists, overlays, and Custom Theme Builder Card Templates.
 */
class Archive_Loop_Widget extends Widget_Base {

	/** @var array Track widget instance IDs currently rendering to prevent infinite loops. */
	private static $rendering_instances = [];

	public function get_name() {
		return 'apexadfo-archive-loop';
	}

	public function get_title() {
		return esc_html__( 'Archive Loop', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-archive-loop-css' ];
	}

	/**
	 * Get available Theme Builder & Elementor Saved Templates for card rendering.
	 * Filters out popups, headers, footers, preloaders, 404 pages, and Default Kits.
	 *
	 * @return array
	 */
	/**
	 * Get available Theme Builder & Elementor Saved Templates for card rendering.
	 * Filters out popups, headers, footers, preloaders, 404 pages, and Default Kits.
	 * Contextually filters templates based on post type (product vs post).
	 *
	 * @param string $filter_type Filter type ('product', 'post', or empty).
	 * @return array
	 */
	private function get_theme_builder_templates( $filter_type = '' ) {
		$placeholder = 'product' === $filter_type
			? esc_html__( 'Select a Product Card Template...', 'apex-addons-for-elementor' )
			: ( 'post' === $filter_type
				? esc_html__( 'Select a Post Card Template...', 'apex-addons-for-elementor' )
				: esc_html__( 'Select a Card Template...', 'apex-addons-for-elementor' ) );

		$options   = [ '' => $placeholder ];
		$templates = get_posts( [
			'post_type'      => [ 'apexadfo_template', 'elementor_library' ],
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		if ( ! empty( $templates ) && ! is_wp_error( $templates ) ) {
			// Single page templates, headers, footers, popups etc should not be used in archive card grids
			$excluded_types = [ 'popup', 'preloader', 'header', 'footer', 'not_found_404', 'kit', 'single_post', 'single_page', 'single-post', 'single-page', 'wp-page' ];

			$product_archive_types = [ 'product_archive', 'product-archive', 'product_single', 'single-product', 'product' ];
			$post_archive_types    = [ 'archive', 'post_archive', 'wp-post' ];

			foreach ( $templates as $tmpl ) {
				$apex_type      = get_post_meta( $tmpl->ID, '_apexadfo_template_type', true );
				$elementor_type = get_post_meta( $tmpl->ID, '_elementor_template_type', true );

				// Skip popups, headers, footers, preloaders, 404s, kits, and single post/page templates
				if ( in_array( $apex_type, $excluded_types, true ) || in_array( $elementor_type, $excluded_types, true ) ) {
					continue;
				}

				if ( 'Default Kit' === $tmpl->post_title || 'kit' === $elementor_type ) {
					continue;
				}

				// Contextual filtering based on selected Post Type / Source
				if ( 'product' === $filter_type ) {
					$is_product_archive = ( in_array( $apex_type, $product_archive_types, true ) || in_array( $elementor_type, $product_archive_types, true ) );
					$is_blog_archive    = ( in_array( $apex_type, $post_archive_types, true ) || in_array( $elementor_type, $post_archive_types, true ) );
					if ( $is_blog_archive && ! $is_product_archive ) {
						continue;
					}
				} elseif ( 'post' === $filter_type ) {
					$is_product_archive = ( in_array( $apex_type, $product_archive_types, true ) || in_array( $elementor_type, $product_archive_types, true ) );
					$is_blog_archive    = ( in_array( $apex_type, $post_archive_types, true ) || in_array( $elementor_type, $post_archive_types, true ) );
					if ( $is_product_archive && ! $is_blog_archive ) {
						continue;
					}
				}

				$label = $tmpl->post_title;
				if ( $apex_type ) {
					$label .= ' (' . ucfirst( str_replace( '_', ' ', $apex_type ) ) . ')';
				} elseif ( $elementor_type ) {
					$label .= ' (' . ucfirst( str_replace( '_', ' ', $elementor_type ) ) . ')';
				}

				$options[ $tmpl->ID ] = $label;
			}
		}

		return $options;
	}

	/**
	 * Get public post types for selection in Archive Loop.
	 *
	 * @return array
	 */
	private function get_public_post_types() {
		$options = [
			'post' => esc_html__( 'Posts', 'apex-addons-for-elementor' ),
		];

		if ( class_exists( 'WooCommerce' ) ) {
			$options['product'] = esc_html__( 'Products (WooCommerce)', 'apex-addons-for-elementor' );
		}

		$options['page'] = esc_html__( 'Pages', 'apex-addons-for-elementor' );

		$post_types = get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' );
		if ( ! empty( $post_types ) && ! is_wp_error( $post_types ) ) {
			foreach ( $post_types as $pt ) {
				if ( in_array( $pt->name, [ 'apexadfo_template', 'elementor_library' ], true ) ) {
					continue;
				}
				$options[ $pt->name ] = $pt->label;
			}
		}

		return $options;
	}

	/**
	 * Get WooCommerce product categories for filtering.
	 *
	 * @return array
	 */
	private function get_product_categories() {
		$options = [ '' => esc_html__( 'All Product Categories', 'apex-addons-for-elementor' ) ];
		if ( taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false ] );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$options[ $term->term_id ] = $term->name;
				}
			}
		}

		return $options;
	}

	protected function register_controls() {

		// =========================================================================
		// CONTENT TAB — QUERY & SOURCE
		// =========================================================================
		$this->start_controls_section(
			'section_query',
			[ 'label' => esc_html__( 'Query & Source', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'query_type',
			[
				'label'   => esc_html__( 'Query Source', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'main',
				'options' => [
					'main'   => esc_html__( 'Main Archive Query (Auto)', 'apex-addons-for-elementor' ),
					'custom' => esc_html__( 'Custom Post Query', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'     => esc_html__( 'Post Type / Source', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'post',
				'options'   => $this->get_public_post_types(),
				'condition' => [ 'query_type' => 'custom' ],
			]
		);

		$categories  = get_categories( [ 'hide_empty' => false ] );
		$cat_options = [ '' => esc_html__( 'All Categories', 'apex-addons-for-elementor' ) ];
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			foreach ( $categories as $cat ) {
				$cat_options[ $cat->term_id ] = $cat->name;
			}
		}

		$this->add_control(
			'category_filter',
			[
				'label'     => esc_html__( 'Filter by Category', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $cat_options,
				'condition' => [
					'query_type' => 'custom',
					'post_type'  => 'post',
				],
			]
		);

		$this->add_control(
			'product_cat_filter',
			[
				'label'     => esc_html__( 'Filter by Product Category', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $this->get_product_categories(),
				'condition' => [
					'query_type' => 'custom',
					'post_type'  => 'product',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => esc_html__( 'Posts Per Page', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'default' => 6,
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'     => esc_html__( 'Order By', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => [
					'date'          => esc_html__( 'Publish Date', 'apex-addons-for-elementor' ),
					'title'         => esc_html__( 'Title', 'apex-addons-for-elementor' ),
					'rand'          => esc_html__( 'Random', 'apex-addons-for-elementor' ),
					'comment_count' => esc_html__( 'Comment Count', 'apex-addons-for-elementor' ),
					'modified'      => esc_html__( 'Last Modified', 'apex-addons-for-elementor' ),
					'menu_order'    => esc_html__( 'Menu Order', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'query_type' => 'custom' ],
			]
		);

		$this->add_control(
			'order',
			[
				'label'     => esc_html__( 'Order', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => [
					'DESC' => esc_html__( 'Descending (DESC)', 'apex-addons-for-elementor' ),
					'ASC'  => esc_html__( 'Ascending (ASC)', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'query_type' => 'custom' ],
			]
		);

		$this->add_control(
			'offset',
			[
				'label'     => esc_html__( 'Offset (Skip Posts)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 50,
				'default'   => 0,
				'condition' => [ 'query_type' => 'custom' ],
			]
		);

		$this->add_control(
			'ignore_sticky',
			[
				'label'        => esc_html__( 'Ignore Sticky Posts', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'query_type' => 'custom' ],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// CONTENT TAB — LAYOUT & CARDS
		// =========================================================================
		$this->start_controls_section(
			'section_layout',
			[ 'label' => esc_html__( 'Layout & Cards', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid'            => esc_html__( 'Modern Grid', 'apex-addons-for-elementor' ),
					'list'            => esc_html__( 'List / Row View', 'apex-addons-for-elementor' ),
					'overlay'         => esc_html__( 'Overlay Card', 'apex-addons-for-elementor' ),
					'custom_template' => esc_html__( 'Custom Template (Theme Builder)', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'custom_template_id',
			[
				'label'       => esc_html__( 'Select Card Template', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->get_theme_builder_templates( 'post' ),
				'condition'   => [
					'layout'     => 'custom_template',
					'post_type!' => 'product',
				],
				'description' => esc_html__( 'Design a post card in Theme Builder and select it here.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'custom_template_id_product',
			[
				'label'       => esc_html__( 'Select Product Card Template', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->get_theme_builder_templates( 'product' ),
				'condition'   => [
					'layout'    => 'custom_template',
					'post_type' => 'product',
				],
				'description' => esc_html__( 'Select a product card template designed with WooCommerce widgets in Theme Builder.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'          => esc_html__( 'Columns', 'apex-addons-for-elementor' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 6,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'selectors'      => [
					'{{WRAPPER}} .apexadfo-archive-loop' => '--apexadfo-archive-columns: {{VALUE}};',
				],
				'condition'      => [ 'layout!' => 'list' ],
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Grid / Row Gap', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-loop' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'equal_height',
			[
				'label'        => esc_html__( 'Equal Card Heights', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'card_hover_animation',
			[
				'label'   => esc_html__( 'Card Hover Animation', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'   => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'lift'   => esc_html__( 'Lift Up (-6px)', 'apex-addons-for-elementor' ),
					'scale'  => esc_html__( 'Subtle Scale (1.02)', 'apex-addons-for-elementor' ),
					'shadow' => esc_html__( 'Shadow Float', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// CONTENT TAB — ELEMENT VISIBILITY & CONTENT
		// =========================================================================
		$this->start_controls_section(
			'section_elements',
			[
				'label'     => esc_html__( 'Card Elements Visibility', 'apex-addons-for-elementor' ),
				'condition' => [ 'layout!' => 'custom_template' ],
			]
		);

		$this->add_control(
			'show_image',
			[
				'label'        => esc_html__( 'Featured Image', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'image_size',
			[
				'label'     => esc_html__( 'Image Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'large',
				'options'   => [
					'full'      => esc_html__( 'Full Size', 'apex-addons-for-elementor' ),
					'large'     => esc_html__( 'Large (1024px)', 'apex-addons-for-elementor' ),
					'medium'    => esc_html__( 'Medium (300px)', 'apex-addons-for-elementor' ),
					'thumbnail' => esc_html__( 'Thumbnail (150px)', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'show_image' => 'yes' ],
			]
		);

		$this->add_control(
			'image_hover_effect',
			[
				'label'     => esc_html__( 'Image Hover Animation', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => [
					'none'  => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'zoom'  => esc_html__( 'Smooth Zoom', 'apex-addons-for-elementor' ),
					'slide' => esc_html__( 'Rotate Zoom', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'show_image' => 'yes' ],
			]
		);

		$this->add_control(
			'show_category_badge',
			[
				'label'        => esc_html__( 'Category Badge', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'badge_position',
			[
				'label'     => esc_html__( 'Badge Position', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'on_image',
				'options'   => [
					'on_image'    => esc_html__( 'Floating On Image', 'apex-addons-for-elementor' ),
					'above_title' => esc_html__( 'Above Title', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'show_category_badge' => 'yes' ],
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'   => esc_html__( 'Title HTML Tag', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
				],
			]
		);

		$this->add_control(
			'show_meta',
			[
				'label'        => esc_html__( 'Post Meta Bar', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_meta_date',
			[
				'label'        => esc_html__( 'Meta: Date', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'show_meta' => 'yes' ],
			]
		);

		$this->add_control(
			'show_meta_author',
			[
				'label'        => esc_html__( 'Meta: Author', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [ 'show_meta' => 'yes' ],
			]
		);

		$this->add_control(
			'show_excerpt',
			[
				'label'        => esc_html__( 'Post Excerpt', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'     => esc_html__( 'Excerpt Words', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 100,
				'default'   => 20,
				'condition' => [ 'show_excerpt' => 'yes' ],
			]
		);

		$this->add_control(
			'show_button',
			[
				'label'        => esc_html__( 'Read More Button', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'     => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read more', 'apex-addons-for-elementor' ),
				'condition' => [ 'show_button' => 'yes' ],
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label'     => esc_html__( 'Button Icon', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::ICONS,
				'condition' => [ 'show_button' => 'yes' ],
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label'        => esc_html__( 'Pagination Bar', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'no_results',
			[
				'label'   => esc_html__( 'No Results Message', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'No posts found.', 'apex-addons-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — CARDS & BOX
		// =========================================================================
		$this->start_controls_section(
			'card_style',
			[
				'label'     => esc_html__( 'Card Container', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'layout!' => 'custom_template' ],
			]
		);

		$this->start_controls_tabs( 'tabs_card_style' );

		$this->start_controls_tab(
			'tab_card_normal',
			[ 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'card_bg',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_card_hover',
			[ 'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ) ]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'card_bg_hover',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card:hover',
			]
		);

		$this->add_control(
			'card_border_hover_color',
			[
				'label'     => esc_html__( 'Border Hover Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_shadow_hover',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'card_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '12',
					'right'    => '12',
					'bottom'   => '12',
					'left'     => '12',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Content Box Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '20',
					'right'    => '20',
					'bottom'   => '20',
					'left'     => '20',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — FEATURED IMAGE
		// =========================================================================
		$this->start_controls_section(
			'image_style_section',
			[
				'label'     => esc_html__( 'Featured Image', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_image' => 'yes', 'layout!' => 'custom_template' ],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label'      => esc_html__( 'Custom Image Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [ 'min' => 100, 'max' => 600 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__image-wrap img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Image Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — CATEGORY BADGE
		// =========================================================================
		$this->start_controls_section(
			'badge_style_section',
			[
				'label'     => esc_html__( 'Category Badge', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_category_badge' => 'yes', 'layout!' => 'custom_template' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .apexadfo-archive-badge',
			]
		);

		$this->add_control(
			'badge_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'badge_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-badge' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '4',
					'right'    => '12',
					'bottom'   => '4',
					'left'     => '12',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'badge_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '20',
					'right'    => '20',
					'bottom'   => '20',
					'left'     => '20',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — TITLE
		// =========================================================================
		$this->start_controls_section(
			'title_style_section',
			[
				'label'     => esc_html__( 'Post Title', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'layout!' => 'custom_template' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card__title',
			]
		);

		$this->start_controls_tabs( 'tabs_title_color' );

		$this->start_controls_tab(
			'tab_title_normal',
			[ 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_title_hover',
			[ 'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'title_margin_bottom',
			[
				'label'      => esc_html__( 'Bottom Margin', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — META BAR
		// =========================================================================
		$this->start_controls_section(
			'meta_style_section',
			[
				'label'     => esc_html__( 'Post Meta', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_meta' => 'yes', 'layout!' => 'custom_template' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card__meta',
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => esc_html__( 'Meta Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_margin_bottom',
			[
				'label'      => esc_html__( 'Bottom Margin', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__meta' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — EXCERPT
		// =========================================================================
		$this->start_controls_section(
			'excerpt_style_section',
			[
				'label'     => esc_html__( 'Post Excerpt', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_excerpt' => 'yes', 'layout!' => 'custom_template' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'excerpt_typography',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card__excerpt',
			]
		);

		$this->add_control(
			'excerpt_color',
			[
				'label'     => esc_html__( 'Excerpt Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#475569',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__excerpt' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'excerpt_margin_bottom',
			[
				'label'      => esc_html__( 'Bottom Margin', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — READ MORE BUTTON
		// =========================================================================
		$this->start_controls_section(
			'button_style_section',
			[
				'label'     => esc_html__( 'Read More Button', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_button' => 'yes', 'layout!' => 'custom_template' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card__button',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[ 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'button_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__button' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .apexadfo-archive-card__button',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[ 'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label'     => esc_html__( 'Hover Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label'     => esc_html__( 'Hover Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4338ca',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-card__button:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '6',
					'right'    => '6',
					'bottom'   => '6',
					'left'     => '6',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Button Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '8',
					'right'    => '18',
					'bottom'   => '8',
					'left'     => '18',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-archive-card__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// =========================================================================
		// STYLE TAB — PAGINATION
		// =========================================================================
		$this->start_controls_section(
			'pagination_style_section',
			[
				'label'     => esc_html__( 'Pagination Bar', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'show_pagination' => 'yes' ],
			]
		);

		$this->add_responsive_control(
			'pagination_alignment',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [
						'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'  => [
						'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-pagination ul' => 'justify-content: {{VALUE}} == "left" ? "flex-start" : ({{VALUE}} == "right" ? "flex-end" : "center");',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .apexadfo-archive-pagination a, {{WRAPPER}} .apexadfo-archive-pagination .current',
			]
		);

		$this->start_controls_tabs( 'tabs_pagination_style' );

		$this->start_controls_tab(
			'tab_pag_normal',
			[ 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'pag_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-pagination a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pag_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-pagination a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pag_active',
			[ 'label' => esc_html__( 'Active', 'apex-addons-for-elementor' ) ]
		);

		$this->add_control(
			'pag_active_color',
			[
				'label'     => esc_html__( 'Active Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-pagination .current' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pag_active_bg',
			[
				'label'     => esc_html__( 'Active Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4f46e5',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-archive-pagination .current' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Extract excerpt safely without triggering the_content filter (which causes infinite loops).
	 *
	 * @param \WP_Post $post_obj Post object.
	 * @param int      $length   Word count.
	 * @return string
	 */
	private function get_safe_excerpt( $post_obj, $length = 24 ) {
		if ( ! empty( $post_obj->post_excerpt ) ) {
			return wp_trim_words( $post_obj->post_excerpt, $length );
		}

		$text = (string) $post_obj->post_content;
		$text = strip_shortcodes( $text );
		$text = preg_replace( '/<!--[\s\S]*?-->/', '', $text );
		$text = wp_strip_all_tags( $text );

		return wp_trim_words( $text, $length );
	}

	/**
	 * Render Category Badge markup.
	 *
	 * @param int    $post_id        Post ID.
	 * @param string $position_class Position modifier class.
	 */
	private function render_category_badge( $post_id, $position_class = '' ) {
		$cats = get_the_category( $post_id );
		if ( empty( $cats ) || is_wp_error( $cats ) ) {
			return;
		}

		$cat = reset( $cats );
		?>
		<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="apexadfo-archive-badge <?php echo esc_attr( $position_class ); ?>">
			<?php echo esc_html( $cat->name ); ?>
		</a>
		<?php
	}

	protected function render() {
		$widget_id = $this->get_id();

		// Prevent infinite recursion if the widget renders inside itself or an excerpt call
		if ( isset( self::$rendering_instances[ $widget_id ] ) ) {
			return;
		}

		self::$rendering_instances[ $widget_id ] = true;

		global $wp_query, $post;
		$original_post = $post;
		$current_id    = $original_post ? $original_post->ID : 0;

		try {
			$settings       = $this->get_settings_for_display();
			$preview        = Plugin::$instance->editor->is_edit_mode();
			$posts_per_page = ! empty( $settings['posts_per_page'] ) ? absint( $settings['posts_per_page'] ) : 6;
			$query_type     = ! empty( $settings['query_type'] ) ? $settings['query_type'] : 'main';

			$is_archive_context = ( is_archive() || is_search() || is_home() || is_post_type_archive() || is_category() || is_tag() || is_tax() || is_author() || is_date() );

			$post_type = ! empty( $settings['post_type'] ) ? sanitize_key( $settings['post_type'] ) : 'post';

			if ( $preview ) {
				$args = [
					'post_type'      => $post_type,
					'posts_per_page' => $posts_per_page,
					'post_status'    => 'publish',
					'no_found_rows'  => true,
				];
				if ( 'post' === $post_type && ! empty( $settings['category_filter'] ) ) {
					$args['cat'] = absint( $settings['category_filter'] );
				} elseif ( 'product' === $post_type && ! empty( $settings['product_cat_filter'] ) ) {
					$args['tax_query'] = [
						[
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => absint( $settings['product_cat_filter'] ),
						],
					];
				}
				$query = new \WP_Query( $args );
			} elseif ( 'main' === $query_type && $is_archive_context && $wp_query instanceof \WP_Query && $wp_query->have_posts() ) {
				$query = $wp_query;
			} else {
				// Custom Query or Singular Page context
				$paged = get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : ( get_query_var( 'page' ) ? (int) get_query_var( 'page' ) : 1 );
				$args  = [
					'post_type'      => $post_type,
					'posts_per_page' => $posts_per_page,
					'paged'          => max( 1, $paged ),
					'post_status'    => 'publish',
				];

				if ( ! empty( $settings['orderby'] ) ) {
					$args['orderby'] = sanitize_key( $settings['orderby'] );
				}
				if ( ! empty( $settings['order'] ) ) {
					$args['order'] = 'ASC' === strtoupper( $settings['order'] ) ? 'ASC' : 'DESC';
				}
				if ( ! empty( $settings['offset'] ) && absint( $settings['offset'] ) > 0 ) {
					$args['offset'] = absint( $settings['offset'] );
				}
				if ( 'post' === $post_type && ! empty( $settings['category_filter'] ) ) {
					$args['cat'] = absint( $settings['category_filter'] );
				} elseif ( 'product' === $post_type && ! empty( $settings['product_cat_filter'] ) ) {
					$args['tax_query'] = [
						[
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => absint( $settings['product_cat_filter'] ),
						],
					];
				}
				if ( isset( $settings['ignore_sticky'] ) && 'yes' === $settings['ignore_sticky'] ) {
					$args['ignore_sticky_posts'] = true;
				}
				if ( $current_id > 0 ) {
					$args['post__not_in'] = [ $current_id ];
				}

				$query = new \WP_Query( $args );
			}

			if ( ! $query instanceof \WP_Query || ! $query->have_posts() ) {
				echo '<div class="apexadfo-archive-empty">' . esc_html( $settings['no_results'] ) . '</div>';
				return;
			}

			$layout        = ! empty( $settings['layout'] ) ? $settings['layout'] : 'grid';
			$equal_height  = ( isset( $settings['equal_height'] ) && 'yes' === $settings['equal_height'] ) ? ' apexadfo-equal-heights' : '';
			$wrapper_class = 'apexadfo-archive-loop apexadfo-archive-loop--' . esc_attr( $layout ) . $equal_height;
			$card_hover    = ! empty( $settings['card_hover_animation'] ) && 'none' !== $settings['card_hover_animation'] ? ' apexadfo-card-hover-' . esc_attr( $settings['card_hover_animation'] ) : '';

			// If Custom Template Mode is selected:
			if ( 'custom_template' === $layout ) {
				$template_id = 0;
				if ( 'product' === $post_type && ! empty( $settings['custom_template_id_product'] ) ) {
					$template_id = absint( $settings['custom_template_id_product'] );
				} elseif ( ! empty( $settings['custom_template_id'] ) ) {
					$template_id = absint( $settings['custom_template_id'] );
				}

				if ( $template_id <= 0 ) {
					echo '<div class="apexadfo-archive-empty">' . esc_html__( 'Please select a custom card template in widget settings.', 'apex-addons-for-elementor' ) . '</div>';
					return;
				}

				// Print the card template CSS once before rendering loop items
				if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
					$css_file = new \Elementor\Core\Files\CSS\Post( $template_id );
					if ( $css_file ) {
						$css_file->print_css();
					}
				}

				echo '<div class="' . esc_attr( $wrapper_class ) . '">';
				foreach ( $query->posts as $item ) {
					if ( ! $item instanceof \WP_Post ) {
						continue;
					}

					if ( $current_id > 0 && $item->ID === $current_id ) {
						continue;
					}

					// Set loop post context
					$GLOBALS['post'] = $item;
					setup_postdata( $item );
					if ( isset( $GLOBALS['wp_query'] ) ) {
						$GLOBALS['wp_query']->post = $item;
					}
					if ( function_exists( 'wc_get_product' ) && ( 'product' === $post_type || 'product' === $item->post_type ) ) {
						$GLOBALS['product'] = wc_get_product( $item->ID );
					}

					echo '<div class="apexadfo-archive-custom-card' . esc_attr( $card_hover ) . '">';

					// Disable ElementCache temporarily during display so each post receives dynamic evaluation and styles
					$cache_active = false;
					if ( isset( Plugin::$instance->elementor_cache ) && method_exists( Plugin::$instance->elementor_cache, 'is_active' ) && Plugin::$instance->elementor_cache->is_active() ) {
						$cache_active = true;
						remove_action( 'elementor/element/before_render', [ Plugin::$instance->elementor_cache, 'before_render' ] );
					}

					echo Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor output is safe HTML with style block.

					if ( $cache_active ) {
						add_action( 'elementor/element/before_render', [ Plugin::$instance->elementor_cache, 'before_render' ] );
					}

					echo '</div>';
				}
				echo '</div>';

				wp_reset_postdata();

				if ( ! $preview && 'yes' === $settings['show_pagination'] && $query->max_num_pages > 1 ) {
					$paged_current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
					$links         = paginate_links( [
						'current'   => $paged_current,
						'total'     => (int) $query->max_num_pages,
						'type'      => 'list',
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
					] );
					if ( $links ) {
						echo '<nav class="apexadfo-archive-pagination" aria-label="' . esc_attr__( 'Archive pagination', 'apex-addons-for-elementor' ) . '">' . wp_kses_post( $links ) . '</nav>';
					}
				}

				return;
			}

			// Native Preset Layouts (Grid, List, Overlay)
			$hover_effect = ! empty( $settings['image_hover_effect'] ) && 'none' !== $settings['image_hover_effect'] ? ' apexadfo-image-' . esc_attr( $settings['image_hover_effect'] ) : '';
			$badge_pos    = ! empty( $settings['badge_position'] ) ? $settings['badge_position'] : 'on_image';
			$title_tag    = ! empty( $settings['title_tag'] ) ? sanitize_key( $settings['title_tag'] ) : 'h2';

			echo '<div class="' . esc_attr( $wrapper_class ) . '">';
			foreach ( $query->posts as $item ) {
				if ( ! $item instanceof \WP_Post ) {
					continue;
				}

				// Skip rendering the current host page inside itself
				if ( $current_id > 0 && $item->ID === $current_id ) {
					continue;
				}

				setup_postdata( $item );
				$item_id    = $item->ID;
				$permalink  = get_permalink( $item_id );
				$image_size = ! empty( $settings['image_size'] ) ? $settings['image_size'] : 'large';
				?>
				<article class="apexadfo-archive-card<?php echo esc_attr( $card_hover ); ?>">
					
					<?php if ( 'yes' === $settings['show_image'] && has_post_thumbnail( $item_id ) ) : ?>
						<a class="apexadfo-archive-card__image-wrap<?php echo esc_attr( $hover_effect ); ?>" href="<?php echo esc_url( $permalink ); ?>">
							<?php echo get_the_post_thumbnail( $item_id, $image_size ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core thumbnail output is safe HTML. ?>
							
							<?php if ( 'yes' === $settings['show_category_badge'] && 'on_image' === $badge_pos ) : ?>
								<?php $this->render_category_badge( $item_id, 'apexadfo-badge-on-image' ); ?>
							<?php endif; ?>
						</a>
					<?php endif; ?>

					<div class="apexadfo-archive-card__content">

						<?php if ( 'yes' === $settings['show_category_badge'] && ( 'above_title' === $badge_pos || 'yes' !== $settings['show_image'] || ! has_post_thumbnail( $item_id ) ) ) : ?>
							<div class="apexadfo-archive-badge-wrap">
								<?php $this->render_category_badge( $item_id, 'apexadfo-badge-above-title' ); ?>
							</div>
						<?php endif; ?>

						<<?php echo esc_attr( $title_tag ); ?> class="apexadfo-archive-card__title">
							<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $item_id ) ); ?></a>
						</<?php echo esc_attr( $title_tag ); ?>>

						<?php if ( 'yes' === $settings['show_meta'] ) : ?>
							<div class="apexadfo-archive-card__meta">
								<?php if ( isset( $settings['show_meta_date'] ) && 'yes' === $settings['show_meta_date'] ) : ?>
									<span class="apexadfo-archive-card__meta-item">
										<svg viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
										<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $item_id ) ); ?>"><?php echo esc_html( get_the_date( '', $item_id ) ); ?></time>
									</span>
								<?php endif; ?>

								<?php if ( isset( $settings['show_meta_author'] ) && 'yes' === $settings['show_meta_author'] ) : ?>
									<span class="apexadfo-archive-card__meta-item">
										<svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
										<span><?php echo esc_html( get_the_author_meta( 'display_name', (int) $item->post_author ) ); ?></span>
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( 'yes' === $settings['show_excerpt'] ) : ?>
							<div class="apexadfo-archive-card__excerpt">
								<?php echo esc_html( $this->get_safe_excerpt( $item, absint( $settings['excerpt_length'] ) ) ); ?>
							</div>
						<?php endif; ?>

						<?php if ( 'yes' === $settings['show_button'] ) : ?>
							<a class="apexadfo-archive-card__button" href="<?php echo esc_url( $permalink ); ?>">
								<span><?php echo esc_html( $settings['button_text'] ); ?></span>
								<?php if ( ! empty( $settings['button_icon']['value'] ) ) : ?>
									<span class="apexadfo-archive-card__button-icon">
										<?php Icons_Manager::render_icon( $settings['button_icon'], [ 'aria-hidden' => 'true' ] ); ?>
									</span>
								<?php endif; ?>
							</a>
						<?php endif; ?>

					</div>
				</article>
				<?php
			}
			echo '</div>';

			wp_reset_postdata();

			if ( ! $preview && 'yes' === $settings['show_pagination'] && $query->max_num_pages > 1 ) {
				$paged_current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
				$links         = paginate_links( [
					'current'   => $paged_current,
					'total'     => (int) $query->max_num_pages,
					'type'      => 'list',
					'prev_text' => '&laquo;',
					'next_text' => '&raquo;',
				] );
				if ( $links ) {
					echo '<nav class="apexadfo-archive-pagination" aria-label="' . esc_attr__( 'Archive pagination', 'apex-addons-for-elementor' ) . '">' . wp_kses_post( $links ) . '</nav>';
				}
			}
		} finally {
			wp_reset_postdata();
			$GLOBALS['post'] = $original_post;
			unset( self::$rendering_instances[ $widget_id ] );
		}
	}
}
