<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Modules\NestedElements\Base\Widget_Nested_Base;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible nested content switcher.
 */
class Nested_Content_Switcher_Widget extends Widget_Nested_Base {

	public function get_name() {
		return 'eas-nested-content-switcher';
	}

	public function get_title() {
		return esc_html__( 'Nested Content Switcher', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-tabs';
	}

	public function get_keywords() {
		return [ 'nested', 'content', 'switcher', 'toggle', 'tabs', 'pricing' ];
	}

	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-nested-content-switcher-css' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-nested-content-switcher-js' ];
	}

	protected function get_default_children_elements() {
		$children = [];
		for ( $index = 1; $index <= 2; $index++ ) {
			$children[] = [
				'elType'   => 'container',
				'settings' => [
					/* translators: %d: Panel number. */
					'_title'        => sprintf( esc_html__( 'Panel #%d', 'apex-addons-for-elementor' ), $index ),
					'content_width' => 'full',
				],
			];
		}
		return $children;
	}

	protected function get_default_repeater_title_setting_key() {
		return 'tab_label';
	}

	protected function get_default_children_title() {
		/* translators: %d: Panel number supplied by Elementor. */
		return esc_html__( 'Panel #%d', 'apex-addons-for-elementor' );
	}

	protected function get_default_children_placeholder_selector() {
		return '.apexadfo-switcher-panels';
	}

	protected function get_initial_config(): array {
		return array_merge(
			parent::get_initial_config(),
			[
				'support_improved_repeaters' => true,
				'target_container'           => [ '.apexadfo-switcher-nav' ],
				'node'                       => 'button',
			]
		);
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	private function register_content_controls() {
		$this->start_controls_section(
			'apexadfo_section_panels',
			[ 'label' => esc_html__( 'Switcher Panels', 'apex-addons-for-elementor' ) ]
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'tab_label',
			[
				'label'       => esc_html__( 'Navigation Label', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Panel', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);
		$repeater->add_control(
			'tab_icon',
			[
				'label' => esc_html__( 'Icon', 'apex-addons-for-elementor' ),
				'type'  => Controls_Manager::ICONS,
			]
		);
		$repeater->add_control(
			'tab_slug',
			[
				'label'       => esc_html__( 'Deep-Link Slug', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'Optional. Use lowercase letters, numbers and hyphens.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'panels',
			[
				'label'              => esc_html__( 'Panels', 'apex-addons-for-elementor' ),
				'type'               => Control_Nested_Repeater::CONTROL_TYPE,
				'fields'             => $repeater->get_controls(),
				'frontend_available' => true,
				'default'            => [
					[ 'tab_label' => esc_html__( 'First', 'apex-addons-for-elementor' ), 'tab_slug' => 'first' ],
					[ 'tab_label' => esc_html__( 'Second', 'apex-addons-for-elementor' ), 'tab_slug' => 'second' ],
				],
				'title_field'        => '{{{ tab_label }}}',
			]
		);
		$this->add_control(
			'accessible_name',
			[
				'label'       => esc_html__( 'Accessible Name', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Content options', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'apexadfo_section_behavior',
			[ 'label' => esc_html__( 'Layout & Behaviour', 'apex-addons-for-elementor' ) ]
		);
		$this->add_control(
			'navigation_style',
			[
				'label'              => esc_html__( 'Starting Preset', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'custom',
				'options'            => [
					'custom'    => esc_html__( 'Custom / Neutral', 'apex-addons-for-elementor' ),
					'segmented' => esc_html__( 'Segmented', 'apex-addons-for-elementor' ),
					'pills'     => esc_html__( 'Pills', 'apex-addons-for-elementor' ),
					'underline' => esc_html__( 'Underline', 'apex-addons-for-elementor' ),
					'boxed'     => esc_html__( 'Boxed Tabs', 'apex-addons-for-elementor' ),
					'minimal'   => esc_html__( 'Minimal', 'apex-addons-for-elementor' ),
					'switch'    => esc_html__( 'Toggle Switch', 'apex-addons-for-elementor' ),
				],
				'description'        => esc_html__( 'Presets are optional starting points. Every visual property remains adjustable in the Style tab.', 'apex-addons-for-elementor' ),
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);
		$this->add_control(
			'navigation_position',
			[
				'label'              => esc_html__( 'Navigation Position', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'top',
				'options'            => [
					'top'    => esc_html__( 'Top', 'apex-addons-for-elementor' ),
					'bottom' => esc_html__( 'Bottom', 'apex-addons-for-elementor' ),
					'left'   => esc_html__( 'Left', 'apex-addons-for-elementor' ),
					'right'  => esc_html__( 'Right', 'apex-addons-for-elementor' ),
				],
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);
		$this->add_responsive_control(
			'navigation_alignment',
			[
				'label'     => esc_html__( 'Items Alignment', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [ 'title' => esc_html__( 'Start', 'apex-addons-for-elementor' ), 'icon' => 'eicon-align-start-h' ],
					'center'     => [ 'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ), 'icon' => 'eicon-align-center-h' ],
					'flex-end'   => [ 'title' => esc_html__( 'End', 'apex-addons-for-elementor' ), 'icon' => 'eicon-align-end-h' ],
					'space-between' => [ 'title' => esc_html__( 'Justified', 'apex-addons-for-elementor' ), 'icon' => 'eicon-justify-space-between-h' ],
				],
				'default'   => 'center',
				'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-nav' => 'justify-content: {{VALUE}};' ],
			]
		);
		$this->add_control(
			'initial_panel',
			[
				'label'              => esc_html__( 'Initial Panel', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 1,
				'default'            => 1,
				'frontend_available' => true,
			]
		);
		$this->add_control(
			'transition',
			[
				'label'              => esc_html__( 'Panel Transition', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'fade',
				'options'            => [
					'none'       => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'fade'       => esc_html__( 'Fade', 'apex-addons-for-elementor' ),
					'slide-left' => esc_html__( 'Slide Left', 'apex-addons-for-elementor' ),
					'slide-up'   => esc_html__( 'Slide Up', 'apex-addons-for-elementor' ),
					'scale'      => esc_html__( 'Scale', 'apex-addons-for-elementor' ),
					'blur'       => esc_html__( 'Blur', 'apex-addons-for-elementor' ),
					'flip'       => esc_html__( 'Soft Flip', 'apex-addons-for-elementor' ),
				],
				'frontend_available' => true,
			]
		);
		$this->add_control(
			'transition_duration',
			[
				'label'              => esc_html__( 'Transition Duration (ms)', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::NUMBER,
				'min'                => 0,
				'max'                => 3000,
				'step'               => 50,
				'default'            => 450,
				'frontend_available' => true,
			]
		);
		$this->add_control(
			'animate_height',
			[
				'label'              => esc_html__( 'Animate Panel Height', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::SWITCHER,
				'return_value'       => 'yes',
				'default'            => 'yes',
				'frontend_available' => true,
			]
		);
		$this->add_control(
			'deep_linking',
			[
				'label'              => esc_html__( 'URL Hash Deep Linking', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::SWITCHER,
				'return_value'       => 'yes',
				'default'            => 'yes',
				'frontend_available' => true,
			]
		);
		$this->add_control(
			'mobile_navigation',
			[
				'label'              => esc_html__( 'Mobile Navigation Mode', 'apex-addons-for-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'scroll',
				'options'            => [
					'scroll' => esc_html__( 'Side Scrolling (Horizontal Row)', 'apex-addons-for-elementor' ),
					'wrap'   => esc_html__( 'Multi-Line Stack (Wrap to Next Line)', 'apex-addons-for-elementor' ),
					'stack'  => esc_html__( 'Full-Width Stack (1 Tab per Line)', 'apex-addons-for-elementor' ),
				],
				'description'        => esc_html__( 'Choose how tabs adapt on mobile screens (below 768px).', 'apex-addons-for-elementor' ),
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);
		$this->end_controls_section();
	}

	private function register_style_controls() {
		$this->start_controls_section(
			'apexadfo_style_navigation',
			[
				'label' => esc_html__( 'Navigation', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_responsive_control( 'nav_gap', [
			'label' => esc_html__( 'Item Gap', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 0, 'max' => 80 ] ], 'default' => [ 'size' => 8, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-nav' => 'gap: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'nav_wrapper_alignment', [
			'label' => esc_html__( 'Wrapper Alignment', 'apex-addons-for-elementor' ),
			'type' => Controls_Manager::CHOOSE,
			'options' => [
				'flex-start' => [ 'title' => esc_html__( 'Start', 'apex-addons-for-elementor' ), 'icon' => 'eicon-align-start-h' ],
				'center' => [ 'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ), 'icon' => 'eicon-align-center-h' ],
				'flex-end' => [ 'title' => esc_html__( 'End', 'apex-addons-for-elementor' ), 'icon' => 'eicon-align-end-h' ],
				'stretch' => [ 'title' => esc_html__( 'Stretch', 'apex-addons-for-elementor' ), 'icon' => 'eicon-align-stretch-h' ],
			],
			'default' => 'center',
			'toggle' => false,
			'selectors_dictionary' => [
				'flex-start' => 'align-self: flex-start;',
				'center' => 'align-self: center;',
				'flex-end' => 'align-self: flex-end;',
				'stretch' => 'align-self: stretch; width: 100%;',
			],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-nav' => '{{VALUE}}' ],
		] );
		$this->add_responsive_control( 'nav_width', [
			'label' => esc_html__( 'Navigation Width', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'vw' ],
			'range' => [
				'px' => [ 'min' => 120, 'max' => 2000 ],
				'%'  => [ 'min' => 10, 'max' => 100 ],
				'vw' => [ 'min' => 10, 'max' => 100 ],
			],
			'description' => esc_html__( 'Leave empty for content width. The navigation never exceeds its available container.', 'apex-addons-for-elementor' ),
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-nav' => 'width: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'nav_min_height', [
			'label' => esc_html__( 'Minimum Height', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em', 'rem' ],
			'range' => [
				'px'  => [ 'min' => 0, 'max' => 300 ],
				'em'  => [ 'min' => 0, 'max' => 20, 'step' => 0.1 ],
				'rem' => [ 'min' => 0, 'max' => 20, 'step' => 0.1 ],
			],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-nav' => 'min-height: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'nav_panel_gap', [
			'label' => esc_html__( 'Distance from Content', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 0, 'max' => 160 ] ], 'default' => [ 'size' => 24, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-nested-switcher' => '--apexadfo-switcher-panel-gap: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'nav_padding', [
			'label' => esc_html__( 'Navigation Padding', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', 'rem' ],
			'default' => [ 'top' => 6, 'right' => 6, 'bottom' => 6, 'left' => 6, 'unit' => 'px', 'isLinked' => true ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-nav' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Background::get_type(), [
			'name' => 'nav_background',
			'selector' => '{{WRAPPER}} .apexadfo-switcher-nav',
			'fields_options' => [
				'background' => [ 'default' => 'classic' ],
				'color' => [ 'default' => '#F1F5F9' ],
			],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [ 'name' => 'nav_border', 'selector' => '{{WRAPPER}} .apexadfo-switcher-nav' ] );
		$this->add_responsive_control( 'nav_radius', [
			'label' => esc_html__( 'Navigation Radius', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'default' => [ 'top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10, 'unit' => 'px', 'isLinked' => true ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'nav_shadow', 'selector' => '{{WRAPPER}} .apexadfo-switcher-nav' ] );
		$this->end_controls_section();

		$this->start_controls_section(
			'apexadfo_style_tabs',
			[ 'label' => esc_html__( 'Navigation Items', 'apex-addons-for-elementor' ), 'tab' => Controls_Manager::TAB_STYLE ]
		);
		$this->add_group_control( Group_Control_Typography::get_type(), [ 'name' => 'tab_typography', 'selector' => '{{WRAPPER}} .apexadfo-switcher-tab' ] );
		$this->add_responsive_control( 'tab_min_width', [
			'label' => esc_html__( 'Minimum Item Width', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em', 'rem' ],
			'range' => [
				'px'  => [ 'min' => 0, 'max' => 600 ],
				'em'  => [ 'min' => 0, 'max' => 40, 'step' => 0.1 ],
				'rem' => [ 'min' => 0, 'max' => 40, 'step' => 0.1 ],
			],
			'description' => esc_html__( 'Tabs may grow beyond this value so their icon, label and padding are never clipped.', 'apex-addons-for-elementor' ),
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab' => 'width: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'tab_padding', [
			'label' => esc_html__( 'Item Padding', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', 'rem' ], 'default' => [ 'top' => 12, 'right' => 20, 'bottom' => 12, 'left' => 20, 'unit' => 'px', 'isLinked' => false ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'tab_radius', [
			'label' => esc_html__( 'Item Radius', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'default' => [ 'top' => 7, 'right' => 7, 'bottom' => 7, 'left' => 7, 'unit' => 'px', 'isLinked' => true ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [ 'name' => 'tab_border', 'selector' => '{{WRAPPER}} .apexadfo-switcher-tab' ] );
		$this->add_responsive_control( 'icon_size', [
			'label' => esc_html__( 'Icon Size', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 8, 'max' => 80 ] ], 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab-icon' => 'font-size: {{SIZE}}{{UNIT}};', '{{WRAPPER}} .apexadfo-switcher-tab-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'icon_gap', [
			'label' => esc_html__( 'Icon Gap', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 0, 'max' => 60 ] ], 'default' => [ 'size' => 8, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab' => 'gap: {{SIZE}}{{UNIT}};' ],
		] );

		$this->start_controls_tabs( 'apexadfo_tab_states' );
		$this->start_controls_tab( 'apexadfo_tab_normal', [ 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ] );
		$this->add_control( 'tab_color', [ 'label' => esc_html__( 'Text & Icon Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#334155', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab' => 'color: {{VALUE}};' ] ] );
		$this->add_control( 'tab_background', [ 'label' => esc_html__( 'Background', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab' => 'background-color: {{VALUE}};' ] ] );
		$this->add_control( 'tab_normal_border_color', [ 'label' => esc_html__( 'Border Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#E2E8F0', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab' => 'border-color: {{VALUE}};' ] ] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'tab_shadow', 'selector' => '{{WRAPPER}} .apexadfo-switcher-tab' ] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'apexadfo_tab_hover', [ 'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ) ] );
		$this->add_control( 'tab_hover_color', [ 'label' => esc_html__( 'Text & Icon Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab:hover' => 'color: {{VALUE}};' ] ] );
		$this->add_control( 'tab_hover_background', [ 'label' => esc_html__( 'Background', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#7C3AED', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab:hover' => 'background-color: {{VALUE}};' ] ] );
		$this->add_control( 'tab_hover_border_color', [ 'label' => esc_html__( 'Border Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#7C3AED', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab:hover' => 'border-color: {{VALUE}};' ] ] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'tab_hover_shadow', 'selector' => '{{WRAPPER}} .apexadfo-switcher-tab:hover' ] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'apexadfo_tab_active', [ 'label' => esc_html__( 'Active', 'apex-addons-for-elementor' ) ] );
		$this->add_control( 'tab_active_color', [ 'label' => esc_html__( 'Text & Icon Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab[aria-selected="true"]' => 'color: {{VALUE}};' ] ] );
		$this->add_control( 'tab_active_background', [ 'label' => esc_html__( 'Background', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#6D28D9', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab[aria-selected="true"]' => 'background-color: {{VALUE}};' ] ] );
		$this->add_control( 'tab_active_border_color', [ 'label' => esc_html__( 'Border & Indicator Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR, 'default' => '#6D28D9', 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab[aria-selected="true"]' => 'border-color: {{VALUE}};', '{{WRAPPER}} .apexadfo-switcher-tab[aria-selected="true"]::after' => 'background-color: {{VALUE}};' ] ] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'tab_active_shadow', 'selector' => '{{WRAPPER}} .apexadfo-switcher-tab[aria-selected="true"]' ] );
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_responsive_control( 'indicator_size', [
			'label' => esc_html__( 'Active Indicator Size', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 1, 'max' => 16 ] ], 'default' => [ 'size' => 3, 'unit' => 'px' ], 'selectors' => [ '{{WRAPPER}} .apexadfo-nested-switcher' => '--apexadfo-switcher-indicator: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_control( 'apexadfo_focus_heading', [
			'label' => esc_html__( 'Keyboard Focus', 'apex-addons-for-elementor' ),
			'type' => Controls_Manager::HEADING,
			'separator' => 'before',
		] );
		$this->add_control( 'tab_focus_color', [
			'label' => esc_html__( 'Outline Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab:focus-visible' => 'outline-color: {{VALUE}};' ],
		] );
		$this->add_responsive_control( 'tab_focus_width', [
			'label' => esc_html__( 'Outline Width', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 1, 'max' => 10 ] ], 'default' => [ 'size' => 2, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab:focus-visible' => 'outline-width: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'tab_focus_offset', [
			'label' => esc_html__( 'Outline Offset', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => -5, 'max' => 20 ] ], 'default' => [ 'size' => 3, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-tab:focus-visible' => 'outline-offset: {{SIZE}}{{UNIT}};' ],
		] );
		$this->end_controls_section();

		$this->start_controls_section(
			'apexadfo_style_content',
			[ 'label' => esc_html__( 'Content Panel', 'apex-addons-for-elementor' ), 'tab' => Controls_Manager::TAB_STYLE ]
		);
		$this->add_responsive_control( 'panel_padding', [
			'label' => esc_html__( 'Padding', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', 'rem', '%' ], 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-panels > .e-con' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Background::get_type(), [ 'name' => 'panel_background', 'selector' => '{{WRAPPER}} .apexadfo-switcher-panels > .e-con' ] );
		$this->add_group_control( Group_Control_Border::get_type(), [ 'name' => 'panel_border', 'selector' => '{{WRAPPER}} .apexadfo-switcher-panels > .e-con' ] );
		$this->add_responsive_control( 'panel_radius', [
			'label' => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ], 'selectors' => [ '{{WRAPPER}} .apexadfo-switcher-panels > .e-con' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [ 'name' => 'panel_shadow', 'selector' => '{{WRAPPER}} .apexadfo-switcher-panels > .e-con' ] );
		$this->end_controls_section();
	}

	private function switcher_config( array $settings ) {
		return [
			'initialPanel'  => max( 0, (int) ( $settings['initial_panel'] ?? 1 ) - 1 ),
			'transition'    => sanitize_key( $settings['transition'] ?? 'fade' ),
			'duration'      => max( 0, min( 3000, (int) ( $settings['transition_duration'] ?? 450 ) ) ),
			'animateHeight' => 'yes' === ( $settings['animate_height'] ?? 'yes' ),
			'deepLinking'   => 'yes' === ( $settings['deep_linking'] ?? 'yes' ),
		];
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$panels   = $settings['panels'] ?? [];
		if ( empty( $panels ) ) {
			return;
		}

		$config   = $this->switcher_config( $settings );
		$initial  = min( count( $panels ) - 1, $config['initialPanel'] );
		$position = sanitize_key( $settings['navigation_position'] ?? 'top' );
		$style    = sanitize_key( $settings['navigation_style'] ?? 'custom' );
		$mobile   = sanitize_key( $settings['mobile_navigation'] ?? 'scroll' );
		$uid      = 'apexadfo-switcher-' . $this->get_id();

		$this->add_render_attribute( 'root', [
			'class'                  => [ 'apexadfo-nested-switcher', 'apexadfo-nav-' . $position, 'apexadfo-style-' . $style, 'apexadfo-mobile-' . $mobile ],
			'data-apexadfo-switcher' => wp_json_encode( $config ),
			'id'                     => $uid,
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'root' ); ?>>
			<div class="apexadfo-switcher-nav" role="tablist" aria-orientation="<?php echo in_array( $position, [ 'left', 'right' ], true ) ? 'vertical' : 'horizontal'; ?>" aria-label="<?php echo esc_attr( $settings['accessible_name'] ?? esc_html__( 'Content options', 'apex-addons-for-elementor' ) ); ?>">
				<?php foreach ( $panels as $index => $panel ) :
					$slug     = sanitize_title( $panel['tab_slug'] ?? '' );
					$slug     = $slug ?: 'panel-' . ( $index + 1 );
					$tab_id   = $uid . '-tab-' . $index;
					$panel_id = $uid . '-panel-' . $index;
					?>
					<button class="apexadfo-switcher-tab" type="button" role="tab" id="<?php echo esc_attr( $tab_id ); ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" aria-selected="<?php echo $index === $initial ? 'true' : 'false'; ?>" tabindex="<?php echo $index === $initial ? '0' : '-1'; ?>" data-index="<?php echo esc_attr( $index ); ?>" data-slug="<?php echo esc_attr( $slug ); ?>">
						<?php if ( ! empty( $panel['tab_icon']['value'] ) ) : ?><span class="apexadfo-switcher-tab-icon"><?php Icons_Manager::render_icon( $panel['tab_icon'], [ 'aria-hidden' => 'true' ] ); ?></span><?php endif; ?>
						<span class="apexadfo-switcher-tab-label"><?php echo esc_html( $panel['tab_label'] ?? '' ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="apexadfo-switcher-viewport">
				<div class="apexadfo-switcher-panels">
					<?php foreach ( $panels as $index => $panel ) : ?>
						<?php $this->print_child( $index ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	protected function content_template_single_repeater_item() {
		?>
		<button class="apexadfo-switcher-tab" type="button" role="tab"></button>
		<?php
	}

	protected function content_template() {
		?>
		<#
		var panels = settings.panels || [],
			uid = 'apexadfo-switcher-' + ( view.getID ? view.getID() : view.model.get( 'id' ) ),
			config = {
				initialPanel: Math.max( 0, Math.min( panels.length - 1, parseInt( settings.initial_panel || 1, 10 ) - 1 ) ),
				transition: settings.transition || 'fade',
				duration: Math.max( 0, parseInt( settings.transition_duration || 450, 10 ) ),
				animateHeight: ( settings.animate_height || 'yes' ) === 'yes',
				deepLinking: ( settings.deep_linking || 'yes' ) === 'yes'
			};
		view.addRenderAttribute( 'root', {
			'class': [ 'apexadfo-nested-switcher', 'apexadfo-nav-' + ( settings.navigation_position || 'top' ), 'apexadfo-style-' + ( settings.navigation_style || 'custom' ), 'apexadfo-mobile-' + ( settings.mobile_navigation || 'scroll' ) ],
			'data-apexadfo-switcher': JSON.stringify( config ),
			'id': uid
		} );
		#>
		<div {{{ view.getRenderAttributeString( 'root' ) }}}>
			<div class="apexadfo-switcher-nav" role="tablist" aria-orientation="{{ settings.navigation_position === 'left' || settings.navigation_position === 'right' ? 'vertical' : 'horizontal' }}" aria-label="{{ settings.accessible_name || 'Content options' }}">
			<# _.each( panels, function( panel, index ) { var slug = panel.tab_slug || ( 'panel-' + ( index + 1 ) ); #>
				<button class="apexadfo-switcher-tab" type="button" role="tab" id="{{ uid }}-tab-{{ index }}" aria-controls="{{ uid }}-panel-{{ index }}" aria-selected="{{ index === config.initialPanel ? 'true' : 'false' }}" tabindex="{{ index === config.initialPanel ? '0' : '-1' }}" data-index="{{ index }}" data-slug="{{ slug }}">
					<# if ( panel.tab_icon && panel.tab_icon.value ) { #><span class="apexadfo-switcher-tab-icon"><i class="{{ panel.tab_icon.value }}" aria-hidden="true"></i></span><# } #>
					<span class="apexadfo-switcher-tab-label">{{ panel.tab_label || '' }}</span>
				</button>
			<# } ); #>
			</div>
			<div class="apexadfo-switcher-viewport"><div class="apexadfo-switcher-panels"></div></div>
		</div>
		<?php
	}
}
