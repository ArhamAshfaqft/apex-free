<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Icons_Manager;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Advanced Timeline Widget
 */
class Advanced_Timeline_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-advanced-timeline';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Advanced Timeline', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-time-line';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	/**
	 * Get script dependencies.
	 *
	 * @return array Script handles.
	 */
	public function get_script_depends() {
		return [ 'apexadfo-advanced-timeline-js' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @return array Style handles.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-advanced-timeline-css' ];
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB - TIMELINE ITEMS
		// ==========================================

		$this->start_controls_section(
			'section_items',
			[
				'label' => esc_html__( 'Timeline Items', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_title',
			[
				'label'       => esc_html__( 'Title', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Project Milestone', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_date',
			[
				'label'   => esc_html__( 'Date / Subtitle Badge', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Jan 2026', 'apex-addons-for-elementor' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_desc',
			[
				'label'   => esc_html__( 'Description Content', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Add detailed information about this timeline event or milestone step.', 'apex-addons-for-elementor' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_media',
			[
				'label'   => esc_html__( 'Card Media Image (Optional)', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_icon',
			[
				'label'   => esc_html__( 'Node Pin Icon', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				],
			]
		);

		$repeater->add_control(
			'custom_node_text',
			[
				'label'       => esc_html__( 'Custom Node Text / Number', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'e.g. 1',
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_btn_text',
			[
				'label'   => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Read More', 'apex-addons-for-elementor' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'item_btn_link',
			[
				'label'       => esc_html__( 'Button Link', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://your-link.com',
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'timeline_list',
			[
				'label'       => esc_html__( 'Timeline Milestones', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ item_title }}}',
				'default'     => [
					[
						'item_title' => esc_html__( 'Project Conception & Research', 'apex-addons-for-elementor' ),
						'item_date'  => esc_html__( 'Phase 01', 'apex-addons-for-elementor' ),
						'item_desc'  => esc_html__( 'Comprehensive market research, scope mapping, and initial wireframes.', 'apex-addons-for-elementor' ),
					],
					[
						'item_title' => esc_html__( 'UI/UX Design Systems', 'apex-addons-for-elementor' ),
						'item_date'  => esc_html__( 'Phase 02', 'apex-addons-for-elementor' ),
						'item_desc'  => esc_html__( 'Crafting high-fidelity mockups, design tokens, and fluid component libraries.', 'apex-addons-for-elementor' ),
					],
					[
						'item_title' => esc_html__( 'Development & Release', 'apex-addons-for-elementor' ),
						'item_date'  => esc_html__( 'Phase 03', 'apex-addons-for-elementor' ),
						'item_desc'  => esc_html__( 'Production engineering, strict quality review checks, and official deployment.', 'apex-addons-for-elementor' ),
					],
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - LAYOUT OPTIONS
		// ==========================================

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout Options', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout_mode',
			[
				'label'   => esc_html__( 'Layout Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => [
					'vertical'   => esc_html__( 'Vertical', 'apex-addons-for-elementor' ),
					'horizontal' => esc_html__( 'Horizontal Stream', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_responsive_control(
			'vertical_alignment',
			[
				'label'     => esc_html__( 'Vertical Alignment', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'alternating',
				'options'   => [
					'alternating' => esc_html__( 'Alternating (Staggered)', 'apex-addons-for-elementor' ),
					'left'        => esc_html__( 'All Left', 'apex-addons-for-elementor' ),
					'right'       => esc_html__( 'All Right', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'layout_mode' => 'vertical',
				],
			]
		);

		$this->add_control(
			'node_pin_type',
			[
				'label'   => esc_html__( 'Node Pin Content', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon'   => esc_html__( 'Icon', 'apex-addons-for-elementor' ),
					'number' => esc_html__( 'Sequential Number (1, 2, 3...)', 'apex-addons-for-elementor' ),
					'text'   => esc_html__( 'Custom Text / Repeater Badge', 'apex-addons-for-elementor' ),
					'dot'    => esc_html__( 'Simple Dot Pin', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'image_position',
			[
				'label'   => esc_html__( 'Image Position in Card', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => [
					'top'         => esc_html__( 'Top (Above Title)', 'apex-addons-for-elementor' ),
					'below_title' => esc_html__( 'Below Title', 'apex-addons-for-elementor' ),
					'bottom'      => esc_html__( 'Bottom (Below Content)', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'date_position',
			[
				'label'   => esc_html__( 'Date Badge Position in Card', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'above_title',
				'options' => [
					'above_title' => esc_html__( 'Above Title', 'apex-addons-for-elementor' ),
					'below_title' => esc_html__( 'Below Title', 'apex-addons-for-elementor' ),
					'bottom'      => esc_html__( 'Bottom of Card', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'pulse_animation',
			[
				'label'        => esc_html__( 'Pulse Ring Animation', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - CARD MEDIA IMAGE
		// ==========================================

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Card Media Image', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_alignment',
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
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-media' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'margin-left: {{VALUE}} == "center" ? "auto" : ({{VALUE}} == "right" ? "auto" : "0"); margin-right: {{VALUE}} == "center" ? "auto" : ({{VALUE}} == "right" ? "0" : "auto");',
				],
			]
		);

		$this->add_responsive_control(
			'image_width',
			[
				'label'      => esc_html__( 'Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [ 'min' => 1, 'max' => 100 ],
					'px' => [ 'min' => 50, 'max' => 1200 ],
					'vw' => [ 'min' => 1, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [ 'min' => 1, 'max' => 100 ],
					'px' => [ 'min' => 50, 'max' => 1200 ],
					'vw' => [ 'min' => 1, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label'      => esc_html__( 'Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [ 'min' => 50, 'max' => 800 ],
					'vh' => [ 'min' => 1, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => [
					'fill'    => esc_html__( 'Fill', 'apex-addons-for-elementor' ),
					'cover'   => esc_html__( 'Cover', 'apex-addons-for-elementor' ),
					'contain' => esc_html__( 'Contain', 'apex-addons-for-elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 14,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-media' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_image_style' );

		$this->start_controls_tab(
			'tab_image_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'image_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters',
				'label'    => esc_html__( 'CSS Filters', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-media img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_image_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'image_opacity_hover',
			[
				'label'     => esc_html__( 'Opacity', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-card:hover .apexadfo-timeline-media img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters_hover',
				'label'    => esc_html__( 'CSS Filters', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-card:hover .apexadfo-timeline-media img',
			]
		);

		$this->add_control(
			'image_hover_transition',
			[
				'label'      => esc_html__( 'Transition Duration', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range'      => [
					's' => [
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'default'    => [
					'unit' => 's',
					'size' => 0.3,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'transition: opacity {{SIZE}}{{UNIT}}, filter {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'label'     => esc_html__( 'Border', 'apex-addons-for-elementor' ),
				'separator' => 'before',
				'selector'  => '{{WRAPPER}} .apexadfo-timeline-media img',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-media img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-media img',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - CONNECTOR LINE & NODES
		// ==========================================

		$this->start_controls_section(
			'section_style_line_nodes',
			[
				'label' => esc_html__( 'Line & Node Pins', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'line_color',
			[
				'label'     => esc_html__( 'Connector Line Base Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e5e7eb',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-line' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'line_progress_color',
			[
				'label'     => esc_html__( 'Connector Line Fill Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-line-progress' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'line_thickness',
			[
				'label'      => esc_html__( 'Line Thickness', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 20,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 4,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-layout-vertical .apexadfo-timeline-line'   => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .apexadfo-layout-horizontal .apexadfo-timeline-line' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'node_size',
			[
				'label'      => esc_html__( 'Node Pin Size', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 20,
						'max'  => 80,
						'step' => 2,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 40,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-container' => '--apexadfo-node-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .apexadfo-timeline-node'      => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'node_icon_size',
			[
				'label'      => esc_html__( 'Node Text / Icon Size', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 10,
						'max'  => 36,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 16,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-node-icon'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .apexadfo-timeline-node-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .apexadfo-timeline-node-text'     => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_node_colors' );

		$this->start_controls_tab(
			'tab_node_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'node_bg_color',
			[
				'label'     => esc_html__( 'Node Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-node' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'node_icon_color',
			[
				'label'     => esc_html__( 'Node Icon / Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-node' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pulse_color',
			[
				'label'     => esc_html__( 'Pulse Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-pulse' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'pulse_animation' => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_node_active',
			[
				'label' => esc_html__( 'Active', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'node_active_bg_color',
			[
				'label'     => esc_html__( 'Node Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-item.apexadfo-is-active .apexadfo-timeline-node' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'node_active_icon_color',
			[
				'label'     => esc_html__( 'Node Icon / Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-item.apexadfo-is-active .apexadfo-timeline-node' => 'color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - CARDS
		// ==========================================

		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Timeline Cards', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_text_alignment',
			[
				'label'     => esc_html__( 'Text Alignment', 'apex-addons-for-elementor' ),
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
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-card' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label'     => esc_html__( 'Card Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'card_title_typography',
				'label'    => esc_html__( 'Title Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-title',
			]
		);

		$this->add_control(
			'card_title_color',
			[
				'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'card_desc_typography',
				'label'    => esc_html__( 'Description Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-desc',
			]
		);

		$this->add_control(
			'card_desc_color',
			[
				'label'     => esc_html__( 'Description Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4b5563',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'date_badge_typography',
				'label'    => esc_html__( 'Date Badge Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-date',
			]
		);

		$this->add_control(
			'date_badge_color',
			[
				'label'     => esc_html__( 'Date Badge Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-date' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'date_badge_bg_color',
			[
				'label'     => esc_html__( 'Date Badge Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#eff6ff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-timeline-date' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Card Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '24',
					'right'    => '24',
					'bottom'   => '24',
					'left'     => '24',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-timeline-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'label'    => esc_html__( 'Card Border', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-card',
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Card Border Radius', 'apex-addons-for-elementor' ),
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
					'{{WRAPPER}} .apexadfo-timeline-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'label'    => esc_html__( 'Card Box Shadow', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-timeline-card',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Helper to render card media image safely
	 */
	private function render_card_image( $item ) {
		if ( empty( $item['item_media']['url'] ) ) {
			return;
		}
		$alt = ! empty( $item['item_title'] ) ? $item['item_title'] : '';
		?>
		<div class="apexadfo-timeline-media">
			<img src="<?php echo esc_url( $item['item_media']['url'] ); ?>" alt="<?php echo esc_attr( $alt ); ?>" />
		</div>
		<?php
	}

	/**
	 * Helper to render date badge
	 */
	private function render_date_badge( $item ) {
		if ( empty( $item['item_date'] ) ) {
			return;
		}
		?>
		<span class="apexadfo-timeline-date"><?php echo esc_html( $item['item_date'] ); ?></span>
		<?php
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$layout_mode    = ! empty( $settings['layout_mode'] ) ? $settings['layout_mode'] : 'vertical';
		$vertical_align_desktop = ! empty( $settings['vertical_alignment'] ) ? $settings['vertical_alignment'] : 'alternating';
		$vertical_align_tablet  = ! empty( $settings['vertical_alignment_tablet'] ) ? $settings['vertical_alignment_tablet'] : $vertical_align_desktop;
		$vertical_align_mobile  = ! empty( $settings['vertical_alignment_mobile'] ) ? $settings['vertical_alignment_mobile'] : $vertical_align_tablet;

		$node_type  = ! empty( $settings['node_pin_type'] ) ? $settings['node_pin_type'] : 'icon';
		$image_pos  = ! empty( $settings['image_position'] ) ? $settings['image_position'] : 'top';
		$date_pos   = ! empty( $settings['date_position'] ) ? $settings['date_position'] : 'above_title';
		$show_pulse = ( isset( $settings['pulse_animation'] ) && 'yes' === $settings['pulse_animation'] );

		$items = ! empty( $settings['timeline_list'] ) ? $settings['timeline_list'] : [];

		$this->add_render_attribute(
			'container',
			[
				'class' => [
					'apexadfo-timeline-container',
					'apexadfo-layout-' . esc_attr( $layout_mode ),
					'apexadfo-align-desktop-' . esc_attr( $vertical_align_desktop ),
					'apexadfo-align-tablet-' . esc_attr( $vertical_align_tablet ),
					'apexadfo-align-mobile-' . esc_attr( $vertical_align_mobile ),
				],
				'role'  => 'list',
			]
		);
		?>
		<div class="apexadfo-timeline-wrapper">
			<div <?php $this->print_render_attribute_string( 'container' ); ?>>
				
				<?php if ( 'horizontal' === $layout_mode ) : ?>
					<div class="apexadfo-timeline-stream">
						<!-- Background & Progress Connector Line -->
						<div class="apexadfo-timeline-line">
							<div class="apexadfo-timeline-line-progress"></div>
						</div>
				<?php else : ?>
					<!-- Background & Progress Connector Line -->
					<div class="apexadfo-timeline-line">
						<div class="apexadfo-timeline-line-progress"></div>
					</div>
				<?php endif; ?>

				<?php
				foreach ( $items as $index => $item ) :
					$item_key = 'timeline_item_' . $index;
					$is_even  = ( 0 === $index % 2 );
					$side_class = $is_even ? 'apexadfo-timeline-item-left' : 'apexadfo-timeline-item-right';

					$this->add_render_attribute(
						$item_key,
						[
							'class' => [
								'apexadfo-timeline-item',
								$side_class,
								'elementor-repeater-item-' . esc_attr( $item['_id'] ),
							],
							'role'  => 'listitem',
						]
					);
					?>
					<div <?php $this->print_render_attribute_string( $item_key ); ?>>
						
						<!-- Node Pin -->
						<div class="apexadfo-timeline-node-wrap">
							<div class="apexadfo-timeline-node">
								<?php if ( $show_pulse ) : ?>
									<span class="apexadfo-timeline-pulse"></span>
								<?php endif; ?>

								<?php if ( ! empty( $item['custom_node_text'] ) ) : ?>
									<span class="apexadfo-timeline-node-text"><?php echo esc_html( $item['custom_node_text'] ); ?></span>
								<?php elseif ( 'number' === $node_type ) : ?>
									<span class="apexadfo-timeline-node-text"><?php echo esc_html( $index + 1 ); ?></span>
								<?php elseif ( 'icon' === $node_type && ! empty( $item['item_icon']['value'] ) ) : ?>
									<span class="apexadfo-timeline-node-icon">
										<?php Icons_Manager::render_icon( $item['item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
									</span>
								<?php elseif ( 'text' === $node_type ) : ?>
									<span class="apexadfo-timeline-node-text"><?php echo esc_html( $index + 1 ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<!-- Card Content Wrap -->
						<div class="apexadfo-timeline-content-wrap">
							<div class="apexadfo-timeline-card">
								
								<?php
								// Image Top
								if ( 'top' === $image_pos ) {
									$this->render_card_image( $item );
								}
								// Date Above Title
								if ( 'above_title' === $date_pos ) {
									$this->render_date_badge( $item );
								}
								?>

								<?php if ( ! empty( $item['item_title'] ) ) : ?>
									<h3 class="apexadfo-timeline-title"><?php echo esc_html( $item['item_title'] ); ?></h3>
								<?php endif; ?>

								<?php
								// Image Below Title
								if ( 'below_title' === $image_pos ) {
									$this->render_card_image( $item );
								}
								// Date Below Title
								if ( 'below_title' === $date_pos ) {
									$this->render_date_badge( $item );
								}
								?>

								<?php if ( ! empty( $item['item_desc'] ) ) : ?>
									<div class="apexadfo-timeline-desc"><?php echo wp_kses_post( $item['item_desc'] ); ?></div>
								<?php endif; ?>

								<?php
								// Image Bottom
								if ( 'bottom' === $image_pos ) {
									$this->render_card_image( $item );
								}
								// Date Bottom
								if ( 'bottom' === $date_pos ) {
									$this->render_date_badge( $item );
								}
								?>

								<?php if ( ! empty( $item['item_btn_link']['url'] ) && ! empty( $item['item_btn_text'] ) ) : ?>
									<?php
									$link_key = 'item_link_' . $index;
									$this->add_render_attribute( $link_key, 'href', esc_url( $item['item_btn_link']['url'] ) );
									$this->add_render_attribute( $link_key, 'class', 'apexadfo-timeline-btn' );
									$t_rel = [];
									if ( ! empty( $item['item_btn_link']['is_external'] ) ) {
										$this->add_render_attribute( $link_key, 'target', '_blank' );
										$t_rel[] = 'noopener';
									}
									if ( ! empty( $item['item_btn_link']['nofollow'] ) ) {
										$t_rel[] = 'nofollow';
									}
									if ( ! empty( $t_rel ) ) {
										$this->add_render_attribute( $link_key, 'rel', implode( ' ', $t_rel ) );
									}
									?>
									<a <?php $this->print_render_attribute_string( $link_key ); ?>><?php echo esc_html( $item['item_btn_text'] ); ?></a>
								<?php endif; ?>
							</div>
						</div>

					</div>
				<?php endforeach; ?>

				<?php if ( 'horizontal' === $layout_mode ) : ?>
					</div>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}
