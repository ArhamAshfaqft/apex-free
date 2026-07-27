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
 * Interactive Image Hotspots Widget
 */
class Interactive_Image_Hotspots_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-interactive-image-hotspots';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Interactive Image Hotspots', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-hotspot';
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
		return [ 'apexadfo-interactive-image-hotspots-js' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @return array Style handles.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-interactive-image-hotspots-css' ];
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB - BASE IMAGE
		// ==========================================

		$this->start_controls_section(
			'section_image',
			[
				'label' => esc_html__( 'Base Image', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'base_image',
			[
				'label'   => esc_html__( 'Choose Image', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - HOTSPOTS REPEATER
		// ==========================================

		$this->start_controls_section(
			'section_hotspots',
			[
				'label' => esc_html__( 'Hotspots Items', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'hotspot_title',
			[
				'label'       => esc_html__( 'Admin Identifier', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Hotspot #1', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'position_x',
			[
				'label'      => esc_html__( 'Horizontal Position X (%)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range'      => [
					'%' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 30,
				],
			]
		);

		$repeater->add_control(
			'position_y',
			[
				'label'      => esc_html__( 'Vertical Position Y (%)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range'      => [
					'%' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 40,
				],
			]
		);

		$repeater->add_control(
			'hotspot_type',
			[
				'label'   => esc_html__( 'Hotspot Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'icon',
				'options' => [
					'icon' => esc_html__( 'Icon', 'apex-addons-for-elementor' ),
					'text' => esc_html__( 'Text / Number Badge', 'apex-addons-for-elementor' ),
					'dot'  => esc_html__( 'Simple Dot', 'apex-addons-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'hotspot_icon',
			[
				'label'     => esc_html__( 'Pin Icon', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-plus',
					'library' => 'fa-solid',
				],
				'condition' => [
					'hotspot_type' => 'icon',
				],
			]
		);

		$repeater->add_control(
			'hotspot_badge_text',
			[
				'label'       => esc_html__( 'Badge Text / Number', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '1',
				'condition'   => [
					'hotspot_type' => 'text',
				],
				'dynamic'     => [ 'active' => true ],
			]
		);

		// Tooltip Content
		$repeater->add_control(
			'tooltip_heading',
			[
				'label'     => esc_html__( 'Tooltip Card Content', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'tooltip_title',
			[
				'label'       => esc_html__( 'Title', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Feature Highlight', 'apex-addons-for-elementor' ),
				'dynamic'     => [ 'active' => true ],
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'tooltip_desc',
			[
				'label'       => esc_html__( 'Description', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Add detail descriptions or specifications for this hotspot location.', 'apex-addons-for-elementor' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'tooltip_image',
			[
				'label'   => esc_html__( 'Tooltip Image (Optional)', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'tooltip_link_text',
			[
				'label'   => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Learn More', 'apex-addons-for-elementor' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'tooltip_link',
			[
				'label'       => esc_html__( 'Button Link', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://your-link.com',
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'tooltip_position',
			[
				'label'   => esc_html__( 'Tooltip Placement', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => [
					'top'    => esc_html__( 'Top', 'apex-addons-for-elementor' ),
					'bottom' => esc_html__( 'Bottom', 'apex-addons-for-elementor' ),
					'left'   => esc_html__( 'Left', 'apex-addons-for-elementor' ),
					'right'  => esc_html__( 'Right', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'hotspots_list',
			[
				'label'       => esc_html__( 'Hotspots', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ hotspot_title }}}',
				'default'     => [
					[
						'hotspot_title' => esc_html__( 'Hotspot #1', 'apex-addons-for-elementor' ),
						'position_x'    => [ 'unit' => '%', 'size' => 35 ],
						'position_y'    => [ 'unit' => '%', 'size' => 45 ],
						'tooltip_title' => esc_html__( 'Premium Design', 'apex-addons-for-elementor' ),
						'tooltip_desc'  => esc_html__( 'Interactive hotspot cards allow engaging visual product tours.', 'apex-addons-for-elementor' ),
					],
					[
						'hotspot_title' => esc_html__( 'Hotspot #2', 'apex-addons-for-elementor' ),
						'position_x'    => [ 'unit' => '%', 'size' => 65 ],
						'position_y'    => [ 'unit' => '%', 'size' => 55 ],
						'tooltip_title' => esc_html__( 'Smart Tooltips', 'apex-addons-for-elementor' ),
						'tooltip_desc'  => esc_html__( 'Responsive tooltips position smoothly across mobile and desktop devices.', 'apex-addons-for-elementor' ),
					],
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - GLOBAL HOTSPOT SETTINGS
		// ==========================================

		$this->start_controls_section(
			'section_global_settings',
			[
				'label' => esc_html__( 'Global Options', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'trigger_mode',
			[
				'label'   => esc_html__( 'Trigger Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'click',
				'options' => [
					'click' => esc_html__( 'Click / Tap', 'apex-addons-for-elementor' ),
					'hover' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
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

		$this->add_control(
			'auto_close_others',
			[
				'label'        => esc_html__( 'Auto Close Other Tooltips', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - BASE IMAGE
		// ==========================================

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Base Image', 'apex-addons-for-elementor' ),
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
					'{{WRAPPER}} .apexadfo-hotspots-wrapper' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-hotspots-container' => 'margin-left: {{VALUE}} == "center" ? "auto" : ({{VALUE}} == "right" ? "auto" : "0"); margin-right: {{VALUE}} == "center" ? "auto" : ({{VALUE}} == "right" ? "0" : "auto");',
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
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 50,
						'max' => 1200,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspots-container' => 'width: {{SIZE}}{{UNIT}};',
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
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 50,
						'max' => 1200,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspots-container' => 'max-width: {{SIZE}}{{UNIT}};',
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
					'px' => [
						'min' => 50,
						'max' => 1200,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspots-container' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .apexadfo-hotspots-base-img'   => 'height: 100%;',
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
					'{{WRAPPER}} .apexadfo-hotspots-base-img' => 'object-fit: {{VALUE}};',
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
					'{{WRAPPER}} .apexadfo-hotspots-base-img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters',
				'label'    => esc_html__( 'CSS Filters', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-hotspots-base-img',
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
					'{{WRAPPER}} .apexadfo-hotspots-container:hover .apexadfo-hotspots-base-img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters_hover',
				'label'    => esc_html__( 'CSS Filters', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-hotspots-container:hover .apexadfo-hotspots-base-img',
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
					'{{WRAPPER}} .apexadfo-hotspots-base-img' => 'transition: opacity {{SIZE}}{{UNIT}}, filter {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'image_border',
				'label'    => esc_html__( 'Border', 'apex-addons-for-elementor' ),
				'separator' => 'before',
				'selector' => '{{WRAPPER}} .apexadfo-hotspots-container',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspots-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-hotspots-container',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - HOTSPOT PINS
		// ==========================================

		$this->start_controls_section(
			'section_style_pins',
			[
				'label' => esc_html__( 'Hotspot Pins', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'pin_size',
			[
				'label'      => esc_html__( 'Pin Size', 'apex-addons-for-elementor' ),
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
					'size' => 32,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspot-pin' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'pin_icon_size',
			[
				'label'      => esc_html__( 'Icon / Text Size', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 10,
						'max'  => 40,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 14,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspot-icon'       => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .apexadfo-hotspot-icon svg'   => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .apexadfo-hotspot-badge'      => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_pin_colors' );

		$this->start_controls_tab(
			'tab_pin_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'pin_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-hotspot-pin' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pin_color',
			[
				'label'     => esc_html__( 'Icon / Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-hotspot-pin' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pulse_color',
			[
				'label'     => esc_html__( 'Pulse Ring Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-hotspot-pulse' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'pulse_animation' => 'yes',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_pin_active',
			[
				'label' => esc_html__( 'Active / Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'pin_active_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1d4ed8',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-hotspot-pin:hover' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-hotspot-item.apexadfo-is-active .apexadfo-hotspot-pin' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'pin_active_color',
			[
				'label'     => esc_html__( 'Icon / Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-hotspot-pin:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-hotspot-item.apexadfo-is-active .apexadfo-hotspot-pin' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - TOOLTIP CARDS
		// ==========================================

		$this->start_controls_section(
			'section_style_tooltip',
			[
				'label' => esc_html__( 'Tooltip Cards', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'tooltip_max_width',
			[
				'label'      => esc_html__( 'Card Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 160,
						'max'  => 500,
						'step' => 10,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 260,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspot-tooltip' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'tooltip_bg_color',
			[
				'label'     => esc_html__( 'Card Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-hotspot-tooltip' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-tooltip-top .apexadfo-tooltip-arrow' => 'border-color: {{VALUE}} transparent transparent transparent;',
					'{{WRAPPER}} .apexadfo-tooltip-bottom .apexadfo-tooltip-arrow' => 'border-color: transparent transparent {{VALUE}} transparent;',
					'{{WRAPPER}} .apexadfo-tooltip-left .apexadfo-tooltip-arrow' => 'border-color: transparent transparent transparent {{VALUE}};',
					'{{WRAPPER}} .apexadfo-tooltip-right .apexadfo-tooltip-arrow' => 'border-color: transparent {{VALUE}} transparent transparent;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'tooltip_title_typography',
				'label'    => esc_html__( 'Title Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-tooltip-title',
			]
		);

		$this->add_control(
			'tooltip_title_color',
			[
				'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-tooltip-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'tooltip_desc_typography',
				'label'    => esc_html__( 'Description Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-tooltip-desc',
			]
		);

		$this->add_control(
			'tooltip_desc_color',
			[
				'label'     => esc_html__( 'Description Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4b5563',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-tooltip-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'tooltip_padding',
			[
				'label'      => esc_html__( 'Card Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '16',
					'right'    => '16',
					'bottom'   => '16',
					'left'     => '16',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspot-tooltip' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tooltip_border_radius',
			[
				'label'      => esc_html__( 'Card Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '8',
					'right'    => '8',
					'bottom'   => '8',
					'left'     => '8',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-hotspot-tooltip' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'tooltip_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-hotspot-tooltip',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['base_image']['url'] ) ) {
			$base_url = Utils::get_placeholder_image_src();
		} else {
			$base_url = $settings['base_image']['url'];
		}

		$base_alt = ! empty( $settings['base_image']['alt'] ) ? $settings['base_image']['alt'] : esc_html__( 'Interactive Hotspots Image', 'apex-addons-for-elementor' );

		$trigger_mode = ! empty( $settings['trigger_mode'] ) ? $settings['trigger_mode'] : 'click';
		$auto_close   = ( isset( $settings['auto_close_others'] ) && 'yes' === $settings['auto_close_others'] ) ? 'yes' : 'no';
		$show_pulse   = ( isset( $settings['pulse_animation'] ) && 'yes' === $settings['pulse_animation'] );

		$hotspots = ! empty( $settings['hotspots_list'] ) ? $settings['hotspots_list'] : [];

		$this->add_render_attribute(
			'container',
			[
				'class'           => 'apexadfo-hotspots-container',
				'data-trigger'    => esc_attr( $trigger_mode ),
				'data-auto-close' => esc_attr( $auto_close ),
			]
		);
		?>
		<div class="apexadfo-hotspots-wrapper">
			<div <?php $this->print_render_attribute_string( 'container' ); ?>>
				<img src="<?php echo esc_url( $base_url ); ?>" alt="<?php echo esc_attr( $base_alt ); ?>" class="apexadfo-hotspots-base-img" />

				<?php
				foreach ( $hotspots as $index => $item ) :
					$item_key = 'hotspot_item_' . $index;
					$pin_key  = 'hotspot_pin_' . $index;

					$pos_x = isset( $item['position_x']['size'] ) ? floatval( $item['position_x']['size'] ) : 50;
					$pos_y = isset( $item['position_y']['size'] ) ? floatval( $item['position_y']['size'] ) : 50;

					$position_style = sprintf( 'left: %f%%; top: %f%%;', $pos_x, $pos_y );

					$tooltip_pos = ! empty( $item['tooltip_position'] ) ? $item['tooltip_position'] : 'top';
					$type        = ! empty( $item['hotspot_type'] ) ? $item['hotspot_type'] : 'icon';

					$this->add_render_attribute(
						$item_key,
						[
							'class' => [
								'apexadfo-hotspot-item',
								'elementor-repeater-item-' . esc_attr( $item['_id'] ),
							],
							'style' => esc_attr( $position_style ),
						]
					);

					$this->add_render_attribute(
						$pin_key,
						[
							'class'         => 'apexadfo-hotspot-pin',
							'role'          => 'button',
							'tabindex'      => '0',
							'aria-expanded' => 'false',
							'aria-label'    => ! empty( $item['tooltip_title'] ) ? esc_attr( $item['tooltip_title'] ) : esc_attr__( 'Hotspot Pin', 'apex-addons-for-elementor' ),
						]
					);
					?>
					<div <?php $this->print_render_attribute_string( $item_key ); ?>>
						
						<!-- Hotspot Pin Button -->
						<button <?php $this->print_render_attribute_string( $pin_key ); ?>>
							<?php if ( $show_pulse ) : ?>
								<span class="apexadfo-hotspot-pulse"></span>
							<?php endif; ?>

							<?php if ( 'icon' === $type && ! empty( $item['hotspot_icon']['value'] ) ) : ?>
								<span class="apexadfo-hotspot-icon">
									<?php Icons_Manager::render_icon( $item['hotspot_icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</span>
							<?php elseif ( 'text' === $type && isset( $item['hotspot_badge_text'] ) ) : ?>
								<span class="apexadfo-hotspot-badge"><?php echo esc_html( $item['hotspot_badge_text'] ); ?></span>
							<?php endif; ?>
						</button>

						<!-- Tooltip Card -->
						<div class="apexadfo-hotspot-tooltip apexadfo-tooltip-<?php echo esc_attr( $tooltip_pos ); ?>">
							<button type="button" class="apexadfo-tooltip-close" aria-label="<?php esc_attr_e( 'Close tooltip', 'apex-addons-for-elementor' ); ?>">&times;</button>
							<div class="apexadfo-tooltip-arrow"></div>

							<?php if ( ! empty( $item['tooltip_image']['url'] ) ) : ?>
								<div class="apexadfo-tooltip-media">
									<img src="<?php echo esc_url( $item['tooltip_image']['url'] ); ?>" alt="<?php echo esc_attr( ! empty( $item['tooltip_title'] ) ? $item['tooltip_title'] : '' ); ?>" />
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $item['tooltip_title'] ) ) : ?>
								<h4 class="apexadfo-tooltip-title"><?php echo esc_html( $item['tooltip_title'] ); ?></h4>
							<?php endif; ?>

							<?php if ( ! empty( $item['tooltip_desc'] ) ) : ?>
								<p class="apexadfo-tooltip-desc"><?php echo wp_kses_post( $item['tooltip_desc'] ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $item['tooltip_link']['url'] ) && ! empty( $item['tooltip_link_text'] ) ) : ?>
								<?php
								$link_key = 'tooltip_link_' . $index;
								$this->add_render_attribute( $link_key, 'href', esc_url( $item['tooltip_link']['url'] ) );
								$this->add_render_attribute( $link_key, 'class', 'apexadfo-tooltip-btn' );
								$h_rel = [];
								if ( ! empty( $item['tooltip_link']['is_external'] ) ) {
									$this->add_render_attribute( $link_key, 'target', '_blank' );
									$h_rel[] = 'noopener';
								}
								if ( ! empty( $item['tooltip_link']['nofollow'] ) ) {
									$h_rel[] = 'nofollow';
								}
								if ( ! empty( $h_rel ) ) {
									$this->add_render_attribute( $link_key, 'rel', implode( ' ', $h_rel ) );
								}
								?>
								<a <?php $this->print_render_attribute_string( $link_key ); ?>><?php echo esc_html( $item['tooltip_link_text'] ); ?></a>
							<?php endif; ?>
						</div>

					</div>
				<?php endforeach; ?>

			</div>
		</div>
		<?php
	}
}
