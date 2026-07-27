<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Blob_Background_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-blob-background';
	}

	public function get_title() {
		return esc_html__( 'Floating Blobs', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-background';
	}

	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-widget-structural' ];
	}

	protected function register_controls() {

		// ==========================================
		// CONTENT TAB
		// ==========================================

		$this->start_controls_section(
			'section_blobs',
			[
				'label' => esc_html__( 'Floating Blobs List', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'blob_name',
			[
				'label'   => esc_html__( 'Label Name', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Liquid Blob', 'apex-addons-for-elementor' ),
			]
		);

		// --- SHAPE CONTENT ---
		$repeater->add_control(
			'fill_type',
			[
				'label'   => esc_html__( 'Fill Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'gradient',
				'options' => [
					'solid'    => esc_html__( 'Solid Color', 'apex-addons-for-elementor' ),
					'gradient' => esc_html__( 'Gradient', 'apex-addons-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'solid_color',
			[
				'label'     => esc_html__( 'Solid Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(233, 30, 99, 0.4)',
				'condition' => [
					'fill_type' => 'solid',
				],
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .eas-blob-inner' => 'background-color: {{VALUE}}; background-image: none;',
				],
			]
		);

		$repeater->add_control(
			'gradient_color_a',
			[
				'label'     => esc_html__( 'Gradient Color A', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(103, 58, 183, 0.4)',
				'condition' => [
					'fill_type' => 'gradient',
				],
			]
		);

		$repeater->add_control(
			'gradient_color_b',
			[
				'label'     => esc_html__( 'Gradient Color B', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0, 188, 212, 0.4)',
				'condition' => [
					'fill_type' => 'gradient',
				],
			]
		);

		$repeater->add_control(
			'gradient_angle',
			[
				'label'      => esc_html__( 'Gradient Angle (deg)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 360,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 135,
				],
				'condition'  => [
					'fill_type' => 'gradient',
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .eas-blob-inner' => 'background-image: linear-gradient({{SIZE}}deg, {{gradient_color_a.VALUE}} 0%, {{gradient_color_b.VALUE}} 100%);',
				],
			]
		);

		// --- DIMENSIONS & POSITIONING ---
		$repeater->add_responsive_control(
			'blob_width',
			[
				'label'      => esc_html__( 'Blob Width (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw' ],
				'range'      => [
					'px' => [
						'min'  => 50,
						'max'  => 1000,
						'step' => 10,
					],
					'vw' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 300,
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$repeater->add_responsive_control(
			'blob_height',
			[
				'label'      => esc_html__( 'Blob Height (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [
						'min'  => 50,
						'max'  => 1000,
						'step' => 10,
					],
					'vh' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 300,
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$repeater->add_responsive_control(
			'position_horizontal',
			[
				'label'      => esc_html__( 'Horizontal Position Offset (%)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%' => [
						'min' => -50,
						'max' => 150,
					],
					'px' => [
						'min' => -500,
						'max' => 2000,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 30,
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$repeater->add_responsive_control(
			'position_vertical',
			[
				'label'      => esc_html__( 'Vertical Position Offset (%)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%' => [
						'min' => -50,
						'max' => 150,
					],
					'px' => [
						'min' => -500,
						'max' => 2000,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 30,
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// --- STYLE DETAIL ---
		$repeater->add_control(
			'blob_blur',
			[
				'label'      => esc_html__( 'Blur filter (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 150,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 40,
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'filter: blur({{SIZE}}px); -webkit-filter: blur({{SIZE}}px);',
				],
			]
		);

		$repeater->add_control(
			'blob_opacity',
			[
				'label'      => esc_html__( 'Opacity', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					],
				],
				'default'    => [
					'size' => 0.6,
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'opacity: {{SIZE}};',
				],
			]
		);

		// --- ANIMATION TYPE ---
		$repeater->add_control(
			'morph_type',
			[
				'label'   => esc_html__( 'Morphing Path', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'eas-morph-a',
				'options' => [
					'eas-morph-a' => esc_html__( 'Organic Blob A', 'apex-addons-for-elementor' ),
					'eas-morph-b' => esc_html__( 'Organic Blob B', 'apex-addons-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'morph_speed',
			[
				'label'      => esc_html__( 'Morphing Cycle (s)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 2,
						'max'  => 60,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 15,
				],
			]
		);

		$repeater->add_control(
			'float_type',
			[
				'label'   => esc_html__( 'Floating Animation', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'eas-float-up-down',
				'options' => [
					'none'                 => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'eas-float-up-down'    => esc_html__( 'Float Up & Down', 'apex-addons-for-elementor' ),
					'eas-float-left-right' => esc_html__( 'Float Left & Right', 'apex-addons-for-elementor' ),
					'eas-float-orbit'      => esc_html__( 'Circular Orbit', 'apex-addons-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'float_speed',
			[
				'label'      => esc_html__( 'Floating Speed Cycle (s)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 2,
						'max'  => 60,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 20,
				],
			]
		);

		$this->add_control(
			'blobs_list',
			[
				'label'       => esc_html__( 'Floating Blobs Builder', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'blob_name'           => esc_html__( 'Blob 1 (Purple Glow)', 'apex-addons-for-elementor' ),
						'fill_type'           => 'gradient',
						'gradient_color_a'    => 'rgba(103, 58, 183, 0.45)',
						'gradient_color_b'    => 'rgba(0, 188, 212, 0.45)',
						'blob_width'          => [ 'size' => 350, 'unit' => 'px' ],
						'blob_height'         => [ 'size' => 350, 'unit' => 'px' ],
						'position_horizontal' => [ 'size' => 20, 'unit' => '%' ],
						'position_vertical'   => [ 'size' => 20, 'unit' => '%' ],
						'morph_type'          => 'eas-morph-a',
						'morph_speed'         => [ 'size' => 12 ],
						'float_type'          => 'eas-float-up-down',
						'float_speed'         => [ 'size' => 18 ],
					],
					[
						'blob_name'           => esc_html__( 'Blob 2 (Orange Glow)', 'apex-addons-for-elementor' ),
						'fill_type'           => 'gradient',
						'gradient_color_a'    => 'rgba(233, 30, 99, 0.4)',
						'gradient_color_b'    => 'rgba(255, 193, 7, 0.4)',
						'blob_width'          => [ 'size' => 400, 'unit' => 'px' ],
						'blob_height'         => [ 'size' => 400, 'unit' => 'px' ],
						'position_horizontal' => [ 'size' => 60, 'unit' => '%' ],
						'position_vertical'   => [ 'size' => 40, 'unit' => '%' ],
						'morph_type'          => 'eas-morph-b',
						'morph_speed'         => [ 'size' => 18 ],
						'float_type'          => 'eas-float-left-right',
						'float_speed'         => [ 'size' => 22 ],
					],
				],
				'title_field' => '{{{ blob_name }}}',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - CONTAINER LAYOUT
		// ==========================================
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container Layout', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'display_mode',
			[
				'label'   => esc_html__( 'Display Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'absolute',
				'options' => [
					'absolute' => esc_html__( 'Absolute Background (Fills Parent)', 'apex-addons-for-elementor' ),
					'inline'   => esc_html__( 'Inline Block (Custom Height)', 'apex-addons-for-elementor' ),
				],
				'prefix_class' => 'eas-blobs-mode-',
			]
		);

		$this->add_responsive_control(
			'container_height',
			[
				'label'      => esc_html__( 'Custom Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', 'em' ],
				'range'      => [
					'px' => [
						'min'  => 100,
						'max'  => 1000,
						'step' => 10,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 400,
				],
				'condition'  => [
					'display_mode' => 'inline',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-blobs-container' => 'height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'container_z_index',
			[
				'label'   => esc_html__( 'Z-Index', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'selectors' => [
					'{{WRAPPER}}' => 'z-index: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['blobs_list'] ) ) {
			return;
		}

		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			echo '<div class="eas-blobs-editor-helper">';
			echo '✨ ' . esc_html__( 'Apex Floating Blobs (Background Mode)', 'apex-addons-for-elementor' );
			echo '</div>';
		}

		echo '<div class="eas-blobs-container">';
		
		foreach ( $settings['blobs_list'] as $item ) {
			// Fetch the unique class key generated for this repeater item
			$repeater_class = 'elementor-repeater-item-' . esc_attr( $item['_id'] );
			$morph_speed   = max( 1, min( 60, (float) ( $item['morph_speed']['size'] ?? 15 ) ) );
			$float_speed   = max( 1, min( 60, (float) ( $item['float_speed']['size'] ?? 20 ) ) );
			$morph_anim    = in_array( $item['morph_type'] ?? '', [ 'eas-morph-a', 'eas-morph-b' ], true ) ? $item['morph_type'] : 'eas-morph-a';
			$allowed_float = [ 'none', 'eas-float-up-down', 'eas-float-left-right', 'eas-float-orbit' ];
			$float_anim    = in_array( $item['float_type'] ?? '', $allowed_float, true ) ? $item['float_type'] : 'none';
			$float_style   = 'none' === $float_anim ? '' : 'animation:' . $float_anim . ' ' . $float_speed . 's ease-in-out infinite;';
			
			echo '<div class="eas-blob-item ' . esc_attr( $repeater_class ) . '" style="' . esc_attr( $float_style ) . '">';
			echo '<div class="eas-blob-inner" style="' . esc_attr( 'animation:' . $morph_anim . ' ' . $morph_speed . 's ease-in-out infinite;' ) . '"></div>';
			echo '</div>';
		}
		
		echo '</div>';
	}
}
