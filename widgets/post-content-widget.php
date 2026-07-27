<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Post_Content_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-post-content';
	}

	public function get_title() {
		return esc_html__( 'Post Content / Product Content', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-content';
	}

	public function get_categories() {
		return [ 'single', 'woocommerce-elements-single', 'elementor-addon-suite-category' ];
	}

	public function get_keywords() {
		return [ 'post', 'product', 'content', 'body', 'text', 'description', 'apex' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-post-content' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-content' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .eas-post-content, {{WRAPPER}} .eas-post-content p',
			]
		);

		$this->add_control(
			'link_color',
			[
				'label'     => esc_html__( 'Link Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-content a' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'link_hover_color',
			[
				'label'     => esc_html__( 'Link Hover Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-content a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$current_id = get_the_ID();
		$post_type  = get_post_type( $current_id );
		$preview    = Plugin::$instance->editor->is_edit_mode();

		// In Elementor Editor, render rich sample post/product content for immediate visual feedback
		if ( $preview ) {
			$sample_content = '';
			if ( class_exists( 'WooCommerce' ) ) {
				$sample_products = get_posts( [
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				] );
				if ( ! empty( $sample_products ) && ! empty( $sample_products[0]->post_content ) ) {
					$sample_content = wpautop( wp_strip_all_tags( $sample_posts[0]->post_content ) );
				}
			}

			if ( empty( $sample_content ) ) {
				$sample_posts = get_posts( [
					'post_type'      => 'post',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				] );
				if ( ! empty( $sample_posts ) && ! empty( $sample_posts[0]->post_content ) ) {
					$sample_content = wpautop( wp_strip_all_tags( $sample_posts[0]->post_content ) );
				}
			}

			if ( empty( $sample_content ) ) {
				$sample_content = '<p>' . esc_html__( 'This is the main content / product description area. Here you can write detailed specifications, features, and information for your page or product.', 'apex-addons-for-elementor' ) . '</p>' .
					'<p>' . esc_html__( 'Crafted with premium materials, designed for maximum durability and effortless everyday performance.', 'apex-addons-for-elementor' ) . '</p>';
			}

			echo '<div class="eas-post-content">' . wp_kses_post( $sample_content ) . '</div>';
			return;
		}

		// On Template Direct Preview (e.g. viewing template ID directly in browser), prevent recursive loop
		if ( 'apexadfo_template' === $post_type || 'elementor_library' === $post_type ) {
			$sample_content = '<p>' . esc_html__( 'This is a live preview of the template content layout area.', 'apex-addons-for-elementor' ) . '</p>';
			echo '<div class="eas-post-content">' . wp_kses_post( $sample_content ) . '</div>';
			return;
		}

		// Standard Frontend rendering for single post/page/product
		$content = get_the_content( null, false, $current_id );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Documented core WordPress the_content filter.
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		echo '<div class="eas-post-content">' . wp_kses_post( $content ) . '</div>';
	}
}
