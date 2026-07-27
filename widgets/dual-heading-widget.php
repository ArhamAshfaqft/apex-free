<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Dual_Heading_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-dual-heading';
	}

	public function get_title() {
		return esc_html__( 'Dual Heading', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-heading';
	}

	public function get_categories() {
		return [ 'eas-typography-category' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-widget-structural' ];
	}

	protected function register_controls() {
		
		// ==========================================
		// CONTENT TAB
		// ==========================================
		
		$this->start_controls_section(
			'section_content_headings',
			[
				'label' => esc_html__( 'Headings Text', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'first_text',
			[
				'label'       => esc_html__( 'First Part Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Create Modern', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'Enter first part of heading', 'apex-addons-for-elementor' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'second_text',
			[
				'label'       => esc_html__( 'Second Part Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Websites Fast', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'Enter second part of heading', 'apex-addons-for-elementor' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'heading_tag',
			[
				'label'   => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ),
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
					'p'    => 'p',
				],
			]
		);

		$this->add_control(
			'layout_mode',
			[
				'label'   => esc_html__( 'Layout Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => [
					'inline' => esc_html__( 'Inline (Side-by-Side)', 'apex-addons-for-elementor' ),
					'block'  => esc_html__( 'Stacked (Block)', 'apex-addons-for-elementor' ),
				],
				'prefix_class' => 'eas-dual-layout-',
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left' => [
						'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-container' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB
		// ==========================================

		// --- FIRST TEXT STYLE ---
		$this->start_controls_section(
			'section_style_first',
			[
				'label' => esc_html__( 'First Text Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_first_text' );

		// Normal State
		$this->start_controls_tab(
			'tab_first_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'first_color_type',
			[
				'label'   => esc_html__( 'Color Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'solid'    => esc_html__( 'Solid Color', 'apex-addons-for-elementor' ),
					'gradient' => esc_html__( 'Gradient', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'first_color',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111111',
				'condition' => [
					'first_color_type' => 'solid',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-first' => 'color: {{VALUE}}; background-image: none; -webkit-background-clip: unset; -webkit-text-fill-color: initial;',
				],
			]
		);

		// Gradient Color Controls
		$this->add_control(
			'first_grad_color_a',
			[
				'label'     => esc_html__( 'Gradient Color A', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#673ab7',
				'condition' => [
					'first_color_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'first_grad_color_b',
			[
				'label'     => esc_html__( 'Gradient Color B', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#00bcd4',
				'condition' => [
					'first_color_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'first_grad_angle',
			[
				'label'      => esc_html__( 'Gradient Angle (deg)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [
					'deg' => [
						'min'  => 0,
						'max'  => 360,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'deg',
					'size' => 135,
				],
				'condition'  => [
					'first_color_type' => 'gradient',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-first' => 'background-image: linear-gradient({{SIZE}}deg, {{first_grad_color_a.VALUE}} 0%, {{first_grad_color_b.VALUE}} 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'first_typography',
				'selector' => '{{WRAPPER}} .eas-dual-heading-first',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'first_shadow',
				'selector' => '{{WRAPPER}} .eas-dual-heading-first',
			]
		);

		$this->add_responsive_control(
			'first_spacing',
			[
				'label'      => esc_html__( 'Spacing (Gap)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => [
					'{{WRAPPER}}.eas-dual-layout-inline .eas-dual-heading-first' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.eas-dual-layout-block .eas-dual-heading-first'  => 'margin-bottom: {{SIZE}}{{UNIT}}; display: block;',
				],
			]
		);

		$this->add_responsive_control(
			'first_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-first' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_first_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'first_color_hover_type',
			[
				'label'   => esc_html__( 'Color Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'solid'    => esc_html__( 'Solid Color', 'apex-addons-for-elementor' ),
					'gradient' => esc_html__( 'Gradient', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'first_color_hover',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#00bcd4',
				'condition' => [
					'first_color_hover_type' => 'solid',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-first:hover' => 'color: {{VALUE}}; background-image: none; -webkit-background-clip: unset; -webkit-text-fill-color: initial;',
				],
			]
		);

		// Gradient Hover Color Controls
		$this->add_control(
			'first_grad_color_a_hover',
			[
				'label'     => esc_html__( 'Gradient Color A', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#00bcd4',
				'condition' => [
					'first_color_hover_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'first_grad_color_b_hover',
			[
				'label'     => esc_html__( 'Gradient Color B', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#673ab7',
				'condition' => [
					'first_color_hover_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'first_grad_angle_hover',
			[
				'label'      => esc_html__( 'Gradient Angle (deg)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [
					'deg' => [
						'min'  => 0,
						'max'  => 360,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'deg',
					'size' => 135,
				],
				'condition'  => [
					'first_color_hover_type' => 'gradient',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-first:hover' => 'background-image: linear-gradient({{SIZE}}deg, {{first_grad_color_a_hover.VALUE}} 0%, {{first_grad_color_b_hover.VALUE}} 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;',
				],
			]
		);

		$this->add_control(
			'first_hover_transition',
			[
				'label'     => esc_html__( 'Transition Duration (s)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'default'   => [
					'size' => 0.3,
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-first' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		$this->add_control(
			'first_hover_effect',
			[
				'label'     => esc_html__( 'Hover Lift Effect', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => [
					'none'  => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'lift'  => esc_html__( 'Translate Up', 'apex-addons-for-elementor' ),
					'scale' => esc_html__( 'Scale Up', 'apex-addons-for-elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-first:hover' => 'display: inline-block; transform: {{VALUE === "lift" ? "translateY(-5px)" : (VALUE === "scale" ? "scale(1.05)" : "none")}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// --- SECOND TEXT STYLE ---
		$this->start_controls_section(
			'section_style_second',
			[
				'label' => esc_html__( 'Second Text Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_second_text' );

		// Normal State
		$this->start_controls_tab(
			'tab_second_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'second_color_type',
			[
				'label'   => esc_html__( 'Color Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'gradient',
				'options' => [
					'solid'    => esc_html__( 'Solid Color', 'apex-addons-for-elementor' ),
					'gradient' => esc_html__( 'Gradient', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'second_color',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'condition' => [
					'second_color_type' => 'solid',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-second' => 'color: {{VALUE}}; background-image: none; -webkit-background-clip: unset; -webkit-text-fill-color: initial;',
				],
			]
		);

		// Gradient Color Controls
		$this->add_control(
			'second_grad_color_a',
			[
				'label'     => esc_html__( 'Gradient Color A', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'condition' => [
					'second_color_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'second_grad_color_b',
			[
				'label'     => esc_html__( 'Gradient Color B', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f44336',
				'condition' => [
					'second_color_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'second_grad_angle',
			[
				'label'      => esc_html__( 'Gradient Angle (deg)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [
					'deg' => [
						'min'  => 0,
						'max'  => 360,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'deg',
					'size' => 135,
				],
				'condition'  => [
					'second_color_type' => 'gradient',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second' => 'background-image: linear-gradient({{SIZE}}deg, {{second_grad_color_a.VALUE}} 0%, {{second_grad_color_b.VALUE}} 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'second_typography',
				'selector' => '{{WRAPPER}} .eas-dual-heading-second',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'second_shadow',
				'selector' => '{{WRAPPER}} .eas-dual-heading-second',
			]
		);

		$this->add_responsive_control(
			'second_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Special Outline Option
		$this->add_control(
			'second_outline_heading',
			[
				'label'     => esc_html__( 'Outline Text Effect', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'second_outline_enable',
			[
				'label'        => esc_html__( 'Enable Outline', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'second_outline_width',
			[
				'label'      => esc_html__( 'Stroke Width (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 1,
				],
				'condition'  => [
					'second_outline_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second' => '-webkit-text-stroke-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'second_outline_color',
			[
				'label'     => esc_html__( 'Stroke Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'condition' => [
					'second_outline_enable' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-second' => '-webkit-text-stroke-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'second_outline_fill_color',
			[
				'label'     => esc_html__( 'Fill Color (Hover/Inside)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'transparent',
				'condition' => [
					'second_outline_enable' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-second' => 'color: {{VALUE}}; -webkit-text-fill-color: {{VALUE}}; background-image: none;',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_second_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'second_color_hover_type',
			[
				'label'   => esc_html__( 'Color Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'solid'    => esc_html__( 'Solid Color', 'apex-addons-for-elementor' ),
					'gradient' => esc_html__( 'Gradient', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'second_color_hover',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9c27b0',
				'condition' => [
					'second_color_hover_type' => 'solid',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-second:hover' => 'color: {{VALUE}}; -webkit-text-fill-color: {{VALUE}}; background-image: none; -webkit-background-clip: unset;',
				],
			]
		);

		// Gradient Hover Color Controls
		$this->add_control(
			'second_grad_color_a_hover',
			[
				'label'     => esc_html__( 'Gradient Color A', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9c27b0',
				'condition' => [
					'second_color_hover_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'second_grad_color_b_hover',
			[
				'label'     => esc_html__( 'Gradient Color B', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'condition' => [
					'second_color_hover_type' => 'gradient',
				],
			]
		);

		$this->add_control(
			'second_grad_angle_hover',
			[
				'label'      => esc_html__( 'Gradient Angle (deg)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [
					'deg' => [
						'min'  => 0,
						'max'  => 360,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'deg',
					'size' => 135,
				],
				'condition'  => [
					'second_color_hover_type' => 'gradient',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second:hover' => 'background-image: linear-gradient({{SIZE}}deg, {{second_grad_color_a_hover.VALUE}} 0%, {{second_grad_color_b_hover.VALUE}} 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;',
				],
			]
		);

		$this->add_control(
			'second_hover_transition',
			[
				'label'     => esc_html__( 'Transition Duration (s)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'default'   => [
					'size' => 0.3,
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-second' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		$this->add_control(
			'second_hover_effect',
			[
				'label'     => esc_html__( 'Hover Lift Effect', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => [
					'none'  => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'lift'  => esc_html__( 'Translate Up', 'apex-addons-for-elementor' ),
					'scale' => esc_html__( 'Scale Up', 'apex-addons-for-elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-second:hover' => 'display: inline-block; transform: {{VALUE === "lift" ? "translateY(-5px)" : (VALUE === "scale" ? "scale(1.05)" : "none")}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Underline Decoration options (Sub-section)
		$this->add_control(
			'second_underline_heading',
			[
				'label'     => esc_html__( 'Underline Decoration', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'second_underline_enable',
			[
				'label'        => esc_html__( 'Enable Underline', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_responsive_control(
			'second_underline_width',
			[
				'label'      => esc_html__( 'Underline Width (%)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 10,
						'max' => 500,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'condition'  => [
					'second_underline_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second-wrap::after' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'second_underline_height',
			[
				'label'      => esc_html__( 'Underline Height (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 20,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 3,
				],
				'condition'  => [
					'second_underline_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second-wrap::after' => 'height: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'second_underline_offset',
			[
				'label'      => esc_html__( 'Underline Gap Offset (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 5,
				],
				'condition'  => [
					'second_underline_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second-wrap::after' => 'bottom: -{{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'second_underline_color',
			[
				'label'     => esc_html__( 'Underline Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'condition' => [
					'second_underline_enable' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-dual-heading-second-wrap::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'second_underline_radius',
			[
				'label'      => esc_html__( 'Underline Border Radius (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 10,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 2,
				],
				'condition'  => [
					'second_underline_enable' => 'yes',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-second-wrap::after' => 'border-radius: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_section();

		// --- GENERAL CONTAINER STYLE ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Heading Container', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'container_bg',
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .eas-dual-heading-container',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .eas-dual-heading-container',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_box_shadow',
				'selector' => '{{WRAPPER}} .eas-dual-heading-container',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Container Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_margin',
			[
				'label'      => esc_html__( 'Container Margin', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-dual-heading-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$first_text  = $settings['first_text'];
		$second_text = $settings['second_text'];
		$heading_tag = $settings['heading_tag'];

		if ( empty( $first_text ) && empty( $second_text ) ) {
			return;
		}

		echo '<div class="eas-dual-heading-container">';
		
		printf( '<%1$s class="eas-dual-heading-wrapper">', esc_attr( $heading_tag ) );
		
		if ( ! empty( $first_text ) ) {
			echo '<span class="eas-dual-heading-first">' . esc_html( $first_text ) . '</span>';
		}
		
		if ( ! empty( $second_text ) ) {
			echo '<span class="eas-dual-heading-second-wrap">';
			echo '<span class="eas-dual-heading-second">' . esc_html( $second_text ) . '</span>';
			echo '</span>';
		}
		
		printf( '</%1$s>', esc_attr( $heading_tag ) );
		
		echo '</div>';
	}
}
