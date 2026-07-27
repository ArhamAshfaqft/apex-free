<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Post_Excerpt_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-post-excerpt';
	}

	public function get_title() {
		return esc_html__( 'Post Excerpt', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-excerpt';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	protected function register_controls() {
		// Content Section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Excerpt', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'excerpt_length',
			[
				'label'   => esc_html__( 'Excerpt Word Count', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 100,
				'default' => 20,
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
					'{{WRAPPER}} .eas-post-excerpt' => 'text-align: {{VALUE}};',
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
			'excerpt_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-excerpt' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .eas-post-excerpt',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$current_id = get_the_ID();
		$post_type  = get_post_type( $current_id );
		$preview    = Plugin::$instance->editor->is_edit_mode();

		$post_id = $current_id;
		if ( 'apexadfo_template' === $post_type || 'elementor_library' === $post_type ) {
			$sample_posts = get_posts( [
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			] );
			if ( ! empty( $sample_posts ) ) {
				$post_id = $sample_posts[0]->ID;
			}
		}

		$post_obj = $post_id ? get_post( $post_id ) : null;
		$length   = ! empty( $settings['excerpt_length'] ) ? absint( $settings['excerpt_length'] ) : 20;
		$excerpt  = '';

		if ( $post_obj instanceof \WP_Post ) {
			if ( ! empty( $post_obj->post_excerpt ) ) {
				$excerpt = wp_trim_words( $post_obj->post_excerpt, $length );
			} else {
				$text    = (string) $post_obj->post_content;
				$text    = strip_shortcodes( $text );
				$text    = preg_replace( '/<!--[\s\S]*?-->/', '', $text );
				$text    = wp_strip_all_tags( $text );
				$excerpt = wp_trim_words( $text, $length );
			}
		}

		if ( empty( $excerpt ) && ( $preview || 'apexadfo_template' === $post_type || 'elementor_library' === $post_type ) ) {
			$excerpt = esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'apex-addons-for-elementor' );
		}

		if ( empty( $excerpt ) ) {
			return;
		}

		printf(
			'<div class="eas-post-excerpt">%s</div>',
			esc_html( $excerpt )
		);
	}
}
