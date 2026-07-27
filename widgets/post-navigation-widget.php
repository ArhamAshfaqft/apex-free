<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Post_Navigation_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-post-navigation';
	}

	public function get_title() {
		return esc_html__( 'Post Navigation', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-navigation';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_label',
			[
				'label'   => esc_html__( 'Show Label', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'prev_label',
			[
				'label'     => esc_html__( 'Previous Post Label', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Previous', 'apex-addons-for-elementor' ),
				'condition' => [
					'show_label' => 'yes',
				],
			]
		);

		$this->add_control(
			'next_label',
			[
				'label'     => esc_html__( 'Next Post Label', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Next', 'apex-addons-for-elementor' ),
				'condition' => [
					'show_label' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'   => esc_html__( 'Show Post Title', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
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
			'label_color',
			[
				'label'     => esc_html__( 'Label Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-label' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_label' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'label_typography',
				'selector'  => '{{WRAPPER}} .eas-nav-label',
				'condition' => [
					'show_label' => 'yes',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_title' => 'yes',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'title_typography',
				'selector'  => '{{WRAPPER}} .eas-nav-title',
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-icon i' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		// Layout mapping selector definitions (zero inline styles)
		$this->add_control(
			'layout_styles',
			[
				'type'      => Controls_Manager::HIDDEN,
				'selectors' => [
					'{{WRAPPER}} .eas-post-navigation' => 'display: flex; justify-content: space-between; align-items: center; width: 100%; padding: 15px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;',
					'{{WRAPPER}} .eas-post-navigation-prev, {{WRAPPER}} .eas-post-navigation-next' => 'width: 48%;',
					'{{WRAPPER}} .eas-post-navigation-next' => 'text-align: right;',
					'{{WRAPPER}} .eas-post-navigation a' => 'display: inline-flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;',
					'{{WRAPPER}} .eas-post-navigation-next a' => 'flex-direction: row-reverse;',
					'{{WRAPPER}} .eas-nav-label-wrap' => 'display: flex; flex-direction: column;',
					'{{WRAPPER}} .eas-nav-label' => 'font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 2px;',
					'{{WRAPPER}} .eas-nav-title' => 'font-size: 14px; font-weight: 600; color: #0f172a; transition: color 0.2s;',
					'{{WRAPPER}} .eas-post-navigation a:hover .eas-nav-title' => 'color: #475569;',
					'{{WRAPPER}} .eas-nav-icon i' => 'font-size: 24px; color: #64748b;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$prev_post = get_previous_post();
		$next_post = get_next_post();

		$prev_title = '';
		$prev_url   = '';
		$next_title = '';
		$next_url   = '';

		// If no posts are found, show mock navigation links in editor mode
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() && ! $prev_post && ! $next_post ) {
			$prev_title = esc_html__( 'Previous Post Title', 'apex-addons-for-elementor' );
			$prev_url   = '#';
			$next_title = esc_html__( 'Next Post Title', 'apex-addons-for-elementor' );
			$next_url   = '#';
		} else {
			if ( $prev_post ) {
				$prev_title = get_the_title( $prev_post->ID );
				$prev_url   = get_permalink( $prev_post->ID );
			}
			if ( $next_post ) {
				$next_title = get_the_title( $next_post->ID );
				$next_url   = get_permalink( $next_post->ID );
			}
		}

		if ( empty( $prev_url ) && empty( $next_url ) ) {
			return;
		}

		echo '<div class="eas-post-navigation">';

		// Previous Link
		if ( ! empty( $prev_url ) ) {
			echo '<div class="eas-post-navigation-prev">';
			printf( '<a href="%s">', esc_url( $prev_url ) );
			echo '<span class="eas-nav-icon"><i class="fa fa-angle-left" aria-hidden="true"></i></span>';
			echo '<span class="eas-nav-label-wrap">';
			if ( 'yes' === $settings['show_label'] ) {
				printf( '<span class="eas-nav-label">%s</span>', esc_html( $settings['prev_label'] ) );
			}
			if ( 'yes' === $settings['show_title'] ) {
				printf( '<span class="eas-nav-title">%s</span>', esc_html( $prev_title ) );
			}
			echo '</span>';
			echo '</a>';
			echo '</div>';
		} else {
			echo '<div class="eas-post-navigation-prev"></div>'; // Empty spacer
		}

		// Next Link
		if ( ! empty( $next_url ) ) {
			echo '<div class="eas-post-navigation-next">';
			printf( '<a href="%s">', esc_url( $next_url ) );
			echo '<span class="eas-nav-label-wrap">';
			if ( 'yes' === $settings['show_label'] ) {
				printf( '<span class="eas-nav-label">%s</span>', esc_html( $settings['next_label'] ) );
			}
			if ( 'yes' === $settings['show_title'] ) {
				printf( '<span class="eas-nav-title">%s</span>', esc_html( $next_title ) );
			}
			echo '</span>';
			echo '<span class="eas-nav-icon"><i class="fa fa-angle-right" aria-hidden="true"></i></span>';
			echo '</a>';
			echo '</div>';
		} else {
			echo '<div class="eas-post-navigation-next"></div>'; // Empty spacer
		}

		echo '</div>';
	}
}
