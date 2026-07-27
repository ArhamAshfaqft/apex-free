<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Table_Of_Contents_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-table-of-contents';
	}

	public function get_title() {
		return esc_html__( 'Table of Contents', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-table-of-contents';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-table-of-contents-css' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-table-of-contents-js' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Table of Contents', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'   => esc_html__( 'Title', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Table of Contents', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'headings',
			[
				'label'       => esc_html__( 'Include Headings', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				],
				'default'     => [ 'h2', 'h3', 'h4' ],
				'label_block' => true,
			]
		);

		$this->add_control(
			'target_selector',
			[
				'label'       => esc_html__( 'Container Selector', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '.eas-post-content, .entry-content, main',
				'placeholder' => '.eas-post-content',
				'description' => esc_html__( 'The CSS class or ID of the content container to scan for headings.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'text_minimize',
			[
				'label'   => esc_html__( 'Minimize Text', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Minimize', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'text_maximize',
			[
				'label'   => esc_html__( 'Maximize Text', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Maximize', 'apex-addons-for-elementor' ),
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
			'box_background',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-toc-box' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .eas-toc-box',
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-toc-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Header styles
		$this->add_control(
			'header_title_style',
			[
				'label'     => esc_html__( 'Header Title', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-toc-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .eas-toc-title',
			]
		);

		// List styles
		$this->add_control(
			'list_items_style',
			[
				'label'     => esc_html__( 'List Items', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'item_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-toc-list li a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'item_active_color',
			[
				'label'     => esc_html__( 'Active/Hover Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-toc-list li a:hover, {{WRAPPER}} .eas-toc-list li a.active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'item_typography',
				'selector' => '{{WRAPPER}} .eas-toc-list li a',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$headings  = implode( ',', $settings['headings'] );
		$target    = esc_attr( $settings['target_selector'] );

		printf(
			'<div class="eas-toc-box" data-target="%1$s" data-headings="%2$s">
				<div class="eas-toc-header">
					<h4 class="eas-toc-title">%3$s</h4>
					<button class="eas-toc-toggle" data-text-minimize="%4$s" data-text-maximize="%5$s">%4$s</button>
				</div>
				<ul class="eas-toc-list">
					<li><span class="eas-toc-empty">%6$s</span></li>
				</ul>
			</div>',
			esc_attr( $target ),
			esc_attr( $headings ),
			esc_html( $settings['title'] ),
			esc_attr( $settings['text_minimize'] ),
			esc_attr( $settings['text_maximize'] ),
			esc_html__( 'Scanning content headings...', 'apex-addons-for-elementor' )
		);
	}
}
