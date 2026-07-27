<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Post_Title_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-post-title';
	}

	public function get_title() {
		return esc_html__( 'Post Title', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-title';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Title', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'header_size',
			[
				'label'   => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default' => 'h2',
			]
		);

		$this->add_control(
			'link_to_post',
			[
				'label'        => esc_html__( 'Link to Post', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
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
					'{{WRAPPER}} .eas-post-title' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Style section
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-title, {{WRAPPER}} .eas-post-title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__( 'Hover Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-title a:hover' => 'color: {{VALUE}};',
				],
				'condition' => [ 'link_to_post' => 'yes' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .eas-post-title',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$current_id = get_the_ID();
		$post_type  = get_post_type( $current_id );

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

		$title = $post_id ? get_the_title( $post_id ) : '';
		if ( empty( $title ) || $post_id === $current_id ) {
			$title = esc_html__( 'Sample Post Title', 'apex-addons-for-elementor' );
		}

		$html_tag = ! empty( $settings['header_size'] ) ? sanitize_key( $settings['header_size'] ) : 'h2';

		if ( isset( $settings['link_to_post'] ) && 'yes' === $settings['link_to_post'] ) {
			$permalink = $post_id ? get_permalink( $post_id ) : '#';
			$title     = sprintf( '<a href="%s">%s</a>', esc_url( $permalink ), esc_html( $title ) );
		} else {
			$title = esc_html( $title );
		}

		printf(
			'<%1$s class="eas-post-title">%2$s</%1$s>',
			esc_attr( $html_tag ),
			$title // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above safely.
		);
	}
}
