<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Progress_Tracker_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-progress-tracker';
	}

	public function get_title() {
		return esc_html__( 'Progress Tracker', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-scroll';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-progress-tracker-css' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-progress-tracker-js' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Tracker Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'position',
			[
				'label'   => esc_html__( 'Position', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'inline' => esc_html__( 'Inline (Within Container)', 'apex-addons-for-elementor' ),
					'top'    => esc_html__( 'Fixed Top of Viewport', 'apex-addons-for-elementor' ),
					'bottom' => esc_html__( 'Fixed Bottom of Viewport', 'apex-addons-for-elementor' ),
				],
				'default' => 'inline',
			]
		);

		$this->add_control(
			'target_selector',
			[
				'label'       => esc_html__( 'Scroll Target Selector', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '.eas-post-content',
				'description' => esc_html__( 'Leave blank to track the scroll progress of the entire page, or specify a container selector like .eas-post-content to track reading progress of that specific post content area.', 'apex-addons-for-elementor' ),
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

		$this->add_responsive_control(
			'tracker_height',
			[
				'label'      => esc_html__( 'Bar Height (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 6,
				],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 30,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-progress-tracker-wrap, body .eas-progress-tracker-fixed-top, body .eas-progress-tracker-fixed-bottom' => 'height: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'bar_color',
			[
				'label'     => esc_html__( 'Progress Indicator Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-progress-tracker-bar, body .eas-progress-tracker-fixed-top .eas-progress-tracker-bar, body .eas-progress-tracker-fixed-bottom .eas-progress-tracker-bar' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label'     => esc_html__( 'Track Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-progress-tracker-wrap, body .eas-progress-tracker-fixed-top, body .eas-progress-tracker-fixed-bottom' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$target   = esc_attr( $settings['target_selector'] );
		$position = esc_attr( $settings['position'] );

		// Render the wrapper structure
		printf(
			'<div class="eas-progress-tracker-wrap" data-position="%1$s" data-target="%2$s">
				<div class="eas-progress-tracker-bar"></div>
			</div>',
			esc_attr( $position ),
			esc_attr( $target )
		);
	}
}
