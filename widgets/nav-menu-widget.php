<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- The custom walker intentionally applies documented WordPress navigation-menu core filters.

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Nav_Menu_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-nav-menu';
	}

	public function get_title() {
		return esc_html__( 'Nav Menu', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-nav-menu-js' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-nav-menu-css', 'elementor-icons-fa-solid' ];
	}

	protected function register_controls() {
		// --- CONTENT: LAYOUT ---
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		// Get all registered menus
		$menus = wp_get_nav_menus();
		$menu_options = [];
		foreach ( $menus as $menu ) {
			$menu_options[ $menu->slug ] = $menu->name;
		}

		$this->add_control(
			'menu',
			[
				'label'       => esc_html__( 'Select Menu', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $menu_options,
				'default'     => ! empty( $menu_options ) ? array_key_first( $menu_options ) : '',
				'description' => empty( $menu_options ) ? esc_html__( 'No menus found. Please create one in WordPress admin (Appearance > Menus).', 'apex-addons-for-elementor' ) : '',
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => esc_html__( 'Horizontal', 'apex-addons-for-elementor' ),
					'vertical'   => esc_html__( 'Vertical', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
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
				'default'   => 'left',
				'prefix_class' => 'eas-nav-menu-align%s-',
			]
		);

		$this->add_control(
			'pointer',
			[
				'label'   => esc_html__( 'Pointer Animation', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'underline',
				'options' => [
					'none'            => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'underline'       => esc_html__( 'Underline (CSS)', 'apex-addons-for-elementor' ),
					'overline'        => esc_html__( 'Overline (CSS)', 'apex-addons-for-elementor' ),
					'double-line'     => esc_html__( 'Double Line (CSS)', 'apex-addons-for-elementor' ),
					'slide-line'      => esc_html__( 'Sliding Underline', 'apex-addons-for-elementor' ),
					'slide-bg'        => esc_html__( 'Sliding Pill Background', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'submenu_indicator',
			[
				'label'   => esc_html__( 'Submenu Indicator', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => [
					'none'    => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'classic' => esc_html__( 'Classic (Angle)', 'apex-addons-for-elementor' ),
					'plus'    => esc_html__( 'Plus (+)', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'submenu_trigger',
			[
				'label'   => esc_html__( 'Submenu Trigger', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'hover',
				'options' => [
					'hover' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
					'click' => esc_html__( 'Click', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'submenu_animation',
			[
				'label'   => esc_html__( 'Submenu Animation', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => [
					'none'     => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'fade'     => esc_html__( 'Fade', 'apex-addons-for-elementor' ),
					'slide-up' => esc_html__( 'Slide Up', 'apex-addons-for-elementor' ),
					'zoom'     => esc_html__( 'Zoom In', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->end_controls_section();

		// --- CONTENT: MOBILE MENU ---
		$this->start_controls_section(
			'section_mobile',
			[
				'label' => esc_html__( 'Mobile Menu', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'mobile_breakpoint',
			[
				'label'   => esc_html__( 'Mobile Breakpoint', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'tablet',
				'options' => [
					'none'   => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'tablet' => esc_html__( 'Tablet (1024px)', 'apex-addons-for-elementor' ),
					'mobile' => esc_html__( 'Mobile (768px)', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'mobile_layout',
			[
				'label'     => esc_html__( 'Mobile Layout', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'dropdown',
				'options'   => [
					'dropdown'    => esc_html__( 'Dropdown (Standard)', 'apex-addons-for-elementor' ),
					'offcanvas-l' => esc_html__( 'Off-Canvas Left', 'apex-addons-for-elementor' ),
					'offcanvas-r' => esc_html__( 'Off-Canvas Right', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'mobile_breakpoint!' => 'none',
				],
			]
		);

		$this->add_control(
			'toggle_icon',
			[
				'label'            => esc_html__( 'Toggle Icon', 'apex-addons-for-elementor' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fas fa-bars',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'close_icon',
			[
				'label'            => esc_html__( 'Close Icon', 'apex-addons-for-elementor' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fas fa-times',
					'library' => 'fa-solid',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE: MAIN MENU ---
		$this->start_controls_section(
			'section_style_main',
			[
				'label' => esc_html__( 'Main Menu Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'main_typography',
				'selector' => '{{WRAPPER}} .eas-nav-menu a.eas-menu-item-link',
			]
		);

		$this->start_controls_tabs( 'tabs_main_menu_color' );

		$this->start_controls_tab(
			'tab_main_menu_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'main_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-menu a.eas-menu-item-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_main_menu_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'main_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-menu a.eas-menu-item-link:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-nav-menu li.current-menu-item > a.eas-menu-item-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pointer_color',
			[
				'label'     => esc_html__( 'Pointer Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-menu-indicator' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-nav-menu.eas-pointer-underline a.eas-menu-item-link::after' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-nav-menu.eas-pointer-overline a.eas-menu-item-link::before' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-nav-menu.eas-pointer-double-line a.eas-menu-item-link::before' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-nav-menu.eas-pointer-double-line a.eas-menu-item-link::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__( 'Item Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-nav-menu a.eas-menu-item-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label'      => esc_html__( 'Space Between', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-nav-menu > li:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eas-nav-menu.eas-menu-layout-vertical > li:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-right: 0;',
				],
			]
		);

		$this->add_control(
			'sliding_indicator_height',
			[
				'label'     => esc_html__( 'Sliding Indicator Height/Padding', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [ 'min' => 1, 'max' => 50 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-menu-indicator' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eas-pointer-slide-bg .eas-menu-indicator' => 'padding: {{SIZE}}px 0;',
				],
				'condition' => [
					'pointer' => [ 'slide-line', 'slide-bg' ],
				],
			]
		);

		$this->add_control(
			'sliding_indicator_radius',
			[
				'label'     => esc_html__( 'Sliding Indicator Border Radius', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-menu-indicator' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'pointer' => [ 'slide-line', 'slide-bg' ],
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE: SUBMENU / DROPDOWNS ---
		$this->start_controls_section(
			'section_style_dropdown',
			[
				'label' => esc_html__( 'Dropdowns (Submenus)', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'dropdown_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-menu ul.sub-menu, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'dropdown_typography',
				'selector' => '{{WRAPPER}} .eas-nav-menu ul.sub-menu a, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu a',
			]
		);

		$this->start_controls_tabs( 'tabs_dropdown_colors' );

		$this->start_controls_tab(
			'tab_dropdown_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'dropdown_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-menu ul.sub-menu a, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dropdown_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'dropdown_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-menu ul.sub-menu a:hover, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-nav-menu ul.sub-menu li.current-menu-item > a, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu li.current-menu-item > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'dropdown_hover_bg',
			[
				'label'     => esc_html__( 'Background Hover Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-menu ul.sub-menu li a:hover, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu li a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'dropdown_border',
				'selector'  => '{{WRAPPER}} .eas-nav-menu ul.sub-menu, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'dropdown_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-nav-menu ul.sub-menu, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'dropdown_box_shadow',
				'selector' => '{{WRAPPER}} .eas-nav-menu ul.sub-menu, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu',
			]
		);

		$this->add_responsive_control(
			'dropdown_item_padding',
			[
				'label'      => esc_html__( 'Item Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-nav-menu ul.sub-menu a, {{WRAPPER}} .eas-mobile-menu-container ul.sub-menu a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
			]
		);

		$this->end_controls_section();

		// --- STYLE: MOBILE TOGGLE ---
		$this->start_controls_section(
			'section_style_toggle',
			[
				'label'     => esc_html__( 'Mobile Toggle Button', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'mobile_breakpoint!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'toggle_size',
			[
				'label'     => esc_html__( 'Icon Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [ 'min' => 10, 'max' => 60 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-menu-toggle i'   => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .eas-menu-toggle svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_toggle_style' );

		$this->start_controls_tab(
			'tab_toggle_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'toggle_color',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-menu-toggle' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle svg path' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toggle_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-menu-toggle' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_toggle_active',
			[
				'label' => esc_html__( 'Active/Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'toggle_color_active',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-menu-toggle.eas-active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:focus' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle.eas-active i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:focus i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:active i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle.eas-active svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:hover svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:focus svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:active svg' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle.eas-active svg path' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:hover svg path' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:focus svg path' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:active svg path' => 'fill: {{VALUE}}; stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toggle_bg_color_active',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-menu-toggle.eas-active' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:hover' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:focus' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-toggle:active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'toggle_border',
				'selector'  => '{{WRAPPER}} .eas-menu-toggle',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'toggle_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-menu-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE: MOBILE CONTAINER ---
		$this->start_controls_section(
			'section_style_mobile_menu',
			[
				'label'     => esc_html__( 'Mobile Menu Container', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'mobile_breakpoint!' => 'none',
				],
			]
		);

		$this->add_control(
			'mobile_menu_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-mobile-menu-container' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'mobile_menu_width',
			[
				'label'      => esc_html__( 'Drawer Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', '%' ],
				'range'      => [
					'px' => [ 'min' => 200, 'max' => 800 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-mobile-menu-container.eas-off-canvas' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'mobile_layout' => [ 'offcanvas-l', 'offcanvas-r' ],
				],
			]
		);

		$this->add_responsive_control(
			'mobile_menu_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-mobile-menu-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'mobile_menu_typography',
				'selector' => '{{WRAPPER}} .eas-mobile-menu-container a',
			]
		);

		$this->start_controls_tabs( 'tabs_mobile_menu_colors' );

		$this->start_controls_tab(
			'tab_mobile_menu_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'mobile_menu_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-mobile-menu-container a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mobile_menu_item_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-mobile-menu-container a' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_mobile_menu_hover',
			[
				'label' => esc_html__( 'Hover/Active', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'mobile_menu_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-mobile-menu-container a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-mobile-menu-container li.current-menu-item > a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mobile_menu_hover_bg',
			[
				'label'     => esc_html__( 'Background Hover', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-mobile-menu-container a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'mobile_overlay_bg',
			[
				'label'     => esc_html__( 'Overlay Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-nav-menu-overlay' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'mobile_layout' => [ 'offcanvas-l', 'offcanvas-r' ],
				],
			]
		);

		// Mobile Close Button Controls (only visible when off-canvas layout is active)
		$this->add_control(
			'heading_mobile_close_btn',
			[
				'label'     => esc_html__( 'Close Button', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'mobile_layout' => [ 'offcanvas-l', 'offcanvas-r' ],
				],
			]
		);

		$this->add_control(
			'mobile_close_btn_align',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'  => [
						'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
				],
				'default'   => 'right',
				'selectors' => [
					'{{WRAPPER}} .eas-menu-close' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'mobile_layout' => [ 'offcanvas-l', 'offcanvas-r' ],
				],
			]
		);

		$this->add_responsive_control(
			'mobile_close_btn_size',
			[
				'label'      => esc_html__( 'Size', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [ 'min' => 10, 'max' => 60 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-menu-close i'   => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .eas-menu-close svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				],
				'condition'  => [
					'mobile_layout' => [ 'offcanvas-l', 'offcanvas-r' ],
				],
			]
		);

		$this->start_controls_tabs(
			'tabs_mobile_close_btn_colors',
			[
				'condition' => [
					'mobile_layout' => [ 'offcanvas-l', 'offcanvas-r' ],
				],
			]
		);

		$this->start_controls_tab(
			'tab_mobile_close_btn_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'mobile_close_btn_color',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-menu-close'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-close svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_mobile_close_btn_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'mobile_close_btn_hover_color',
			[
				'label'     => esc_html__( 'Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-menu-close:hover'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-menu-close:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		// Mobile Menu Items Settings
		$this->add_control(
			'heading_mobile_menu_items',
			[
				'label'     => esc_html__( 'Menu Items', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'mobile_menu_item_padding',
			[
				'label'      => esc_html__( 'Item Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-mobile-menu-container a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'mobile_menu_item_spacing',
			[
				'label'      => esc_html__( 'Item Bottom Spacing', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-mobile-menu-container li' => 'margin-bottom: {{SIZE}}px;',
					'{{WRAPPER}} .eas-mobile-menu-container li li' => 'margin-bottom: 0;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['menu'] ) ) {
			return;
		}

		// Enqueue FontAwesome for the submenu indicator icons on the front-end
		wp_enqueue_style( 'elementor-icons-fa-solid' );

		$args = [
			'menu'            => $settings['menu'],
			'container'       => false,
			'menu_class'      => 'eas-nav-menu-list',
			'echo'            => false,
			'fallback_cb'     => '__return_empty_string',
			'walker'          => new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Eas_Nav_Menu_Walker( $settings['submenu_indicator'] ),
		];

		$menu_html = wp_nav_menu( $args );

		if ( empty( $menu_html ) ) {
			return;
		}

		$pointer_class = 'eas-pointer-' . esc_attr( $settings['pointer'] );
		$layout_class  = 'eas-menu-layout-' . esc_attr( $settings['layout'] );
		$trigger_class = 'eas-trigger-' . esc_attr( $settings['submenu_trigger'] );
		$animate_class = 'eas-submenu-animation-' . esc_attr( $settings['submenu_animation'] );
		$breakpoint_class = 'eas-nav-menu-breakpoint-' . esc_attr( $settings['mobile_breakpoint'] );

		// Desktop wrapper
		echo '<div class="eas-nav-menu-wrapper ' . esc_attr( $breakpoint_class ) . '">';
		
		echo '<nav class="eas-nav-menu ' . esc_attr( $layout_class ) . ' ' . esc_attr( $pointer_class ) . ' ' . esc_attr( $trigger_class ) . ' ' . esc_attr( $animate_class ) . '">';
		echo wp_kses_post( $menu_html );
		
		// Add sliding indicator element if selected
		if ( in_array( $settings['pointer'], [ 'slide-line', 'slide-bg' ], true ) ) {
			echo '<span class="eas-menu-indicator"></span>';
		}
		echo '</nav>';

		// Mobile Toggle and Menu Container
		if ( 'none' !== $settings['mobile_breakpoint'] ) {
			$mobile_layout_class = 'eas-layout-' . esc_attr($settings['mobile_layout']);
			
			echo '<button class="eas-menu-toggle" aria-label="Toggle Navigation">';
			if ( ! empty( $settings['toggle_icon']['value'] ) ) {
				echo '<span class="eas-toggle-icon">';
				\Elementor\Icons_Manager::render_icon( $settings['toggle_icon'], [ 'aria-hidden' => 'true' ] );
				echo '</span>';
			}
			if ( ! empty( $settings['close_icon']['value'] ) ) {
				echo '<span class="eas-close-icon">';
				\Elementor\Icons_Manager::render_icon( $settings['close_icon'], [ 'aria-hidden' => 'true' ] );
				echo '</span>';
			}
			echo '</button>';

			// Drawer Overlay
			if ( strpos( $settings['mobile_layout'], 'offcanvas' ) !== false ) {
				echo '<div class="eas-nav-menu-overlay"></div>';
			}

			// Mobile Menu Container
			echo '<div class="eas-mobile-menu-container ' . esc_attr( $mobile_layout_class ) . '">';
			echo '<div class="eas-mobile-menu-inner">';
			
			// Close button inside offcanvas
			if ( strpos( $settings['mobile_layout'], 'offcanvas' ) !== false ) {
				echo '<button class="eas-menu-close" aria-label="Close Navigation">';
				if ( ! empty( $settings['close_icon']['value'] ) ) {
					\Elementor\Icons_Manager::render_icon( $settings['close_icon'], [ 'aria-hidden' => 'true' ] );
				}
				echo '</button>';
			}

			echo '<nav class="eas-nav-menu-mobile">';
			echo wp_kses_post( $menu_html );
			echo '</nav>';

			echo '</div>'; // .eas-mobile-menu-inner
			echo '</div>'; // .eas-mobile-menu-container
		}

		echo '</div>'; // .eas-nav-menu-wrapper
	}
}

/**
 * Custom Walker to add class tags and classes to make customization and structures highly accessible.
 */
class Eas_Nav_Menu_Walker extends \Walker_Nav_Menu {
	private $indicator;

	public function __construct( $indicator ) {
		$this->indicator = $indicator;
	}

	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		// Compatibility for older WP versions where $data_object might be a different object.
		$item = $data_object;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		if ( in_array( 'current-menu-item', $classes, true ) ) {
			$classes[] = 'active';
		}

		$has_children = ! empty( $this->has_children );
		if ( $has_children && ! in_array( 'menu-item-has-children', $classes, true ) ) {
			$classes[] = 'menu-item-has-children';
		}

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = [];
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$item_output = $args->before;
		$item_output .= '<a class="eas-menu-item-link"'. $attributes .'>';
		$item_output .= $args->link_before . '<span class="eas-menu-title">' . $title . '</span>' . $args->link_after;
		
		// Add indicator icon if item has children
		if ( $has_children && 'none' !== $this->indicator ) {
			$item_output .= '<span class="eas-submenu-indicator-icon">';
			ob_start();
			if ( 'classic' === $this->indicator ) {
				\Elementor\Icons_Manager::render_icon( [
					'value'   => 'fas fa-angle-down',
					'library' => 'fa-solid',
				], [ 'aria-hidden' => 'true' ] );
			} elseif ( 'plus' === $this->indicator ) {
				\Elementor\Icons_Manager::render_icon( [
					'value'   => 'fas fa-plus',
					'library' => 'fa-solid',
				], [ 'aria-hidden' => 'true' ] );
			}
			$item_output .= ob_get_clean();
			$item_output .= '</span>';
		}
		
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
