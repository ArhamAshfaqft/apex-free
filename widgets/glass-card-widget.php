<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Glass_Card_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-glass-card';
	}

	public function get_title() {
		return esc_html__( 'Glass Promo Card', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-image-box';
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
		
		// --- CARD MEDIA ---
		$this->start_controls_section(
			'section_content_media',
			[
				'label' => esc_html__( 'Card Media', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'card_image',
			[
				'label'   => esc_html__( 'Choose Image', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'card_image_size',
				'default' => 'medium_large',
			]
		);

		$this->end_controls_section();

		// --- CARD TEXTS ---
		$this->start_controls_section(
			'section_content_text',
			[
				'label' => esc_html__( 'Card Texts', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'card_badge',
			[
				'label'       => esc_html__( 'Badge Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'FEATURED', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'e.g. NEW, 20% OFF', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'card_title',
			[
				'label'       => esc_html__( 'Card Title', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Creative Design Agency', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'Enter card title', 'apex-addons-for-elementor' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'card_title_tag',
			[
				'label'   => esc_html__( 'Title HTML Tag', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
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
			'card_description',
			[
				'label'       => esc_html__( 'Description', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'Build high-performance websites with stunning frosted glass layouts and responsive styling interfaces.', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'Enter card description', 'apex-addons-for-elementor' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->end_controls_section();

		// --- CARD BUTTON ---
		$this->start_controls_section(
			'section_content_button',
			[
				'label' => esc_html__( 'Action Button', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'button_text',
			[
				'label'       => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Learn More', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'e.g. Read More', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'button_link',
			[
				'label'       => esc_html__( 'Link URL', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'default'     => [
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				],
			]
		);

		$this->add_control(
			'button_icon',
			[
				'label'       => esc_html__( 'Button Icon', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => [
					'value'   => 'fas fa-arrow-right',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'button_icon_position',
			[
				'label'     => esc_html__( 'Icon Position', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => [
					'before' => esc_html__( 'Before Text', 'apex-addons-for-elementor' ),
					'after'  => esc_html__( 'After Text', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'button_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'button_icon_spacing',
			[
				'label'      => esc_html__( 'Icon Spacing (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 30,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 8,
				],
				'condition'  => [
					'button_icon[value]!' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-btn-icon-before' => 'margin-right: {{SIZE}}px;',
					'{{WRAPPER}} .eas-glass-btn-icon-after'  => 'margin-left: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_section();

		// --- TOP-RIGHT FLOATING ICON ---
		$this->start_controls_section(
			'section_tr_icon',
			[
				'label' => esc_html__( 'Top-Right Floating Icon', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_tr_icon',
			[
				'label'        => esc_html__( 'Enable Top-Right Icon', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'tr_icon',
			[
				'label'     => esc_html__( 'Choose Icon', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-atom',
					'library' => 'fa-solid',
				],
				'condition' => [
					'show_tr_icon' => 'yes',
				],
			]
		);

		$this->add_control(
			'tr_icon_link',
			[
				'label'       => esc_html__( 'Link URL', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'condition' => [
					'show_tr_icon' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB
		// ==========================================

		// --- CARD CONTAINER STYLE ---
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Card Container', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_container_styles' );

		// Normal State
		$this->start_controls_tab(
			'tab_container_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		// Glass Background Opacity is managed by classic Color control (RGBA)
		$this->add_control(
			'container_bg_color',
			[
				'label'     => esc_html__( 'Glass Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.1)',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-container' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'container_blur',
			[
				'label'      => esc_html__( 'Backdrop Blur Amount (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 15,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-container' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => '{{WRAPPER}} .eas-glass-card-container',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width' => [
						'default' => [
							'top'    => '1',
							'right'  => '1',
							'bottom' => '1',
							'left'   => '1',
							'isLinked' => true,
						],
					],
					'color' => [
						'default' => 'rgba(255, 255, 255, 0.25)',
					],
				],
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '16',
					'right'  => '16',
					'bottom' => '16',
					'left'   => '16',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_shadow',
				'selector' => '{{WRAPPER}} .eas-glass-card-container',
				'fields_options' => [
					'box_shadow' => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 8,
							'blur'       => 32,
							'spread'     => 0,
							'color'      => 'rgba(0, 0, 0, 0.15)',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '24',
					'right'  => '24',
					'bottom' => '24',
					'left'   => '24',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label'      => esc_html__( 'Card Width (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 200,
						'max'  => 800,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-container' => 'max-width: {{SIZE}}{{UNIT}}; margin: 0 auto;',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_container_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'container_bg_color_hover',
			[
				'label'     => esc_html__( 'Glass Background Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.15)',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-container:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'container_blur_hover',
			[
				'label'      => esc_html__( 'Backdrop Blur Amount (Hover)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 40,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-container:hover' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$this->add_control(
			'container_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.4)',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-container:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_shadow_hover',
				'selector' => '{{WRAPPER}} .eas-glass-card-container:hover',
				'fields_options' => [
					'box_shadow' => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 12,
							'blur'       => 40,
							'spread'     => 0,
							'color'      => 'rgba(0, 0, 0, 0.25)',
						],
					],
				],
			]
		);

		$this->add_control(
			'container_hover_lift',
			[
				'label'     => esc_html__( 'Hover Lift Translation (px)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => -30,
						'max'  => 0,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => -8,
				],
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-container:hover' => 'transform: translateY({{SIZE}}px);',
				],
			]
		);

		$this->add_control(
			'container_transition_duration',
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
					'size' => 0.4,
				],
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-container' => 'transition: all {{SIZE}}s cubic-bezier(0.25, 0.8, 0.25, 1);',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// --- CARD IMAGE STYLE ---
		$this->start_controls_section(
			'section_style_image',
			[
				'label'     => esc_html__( 'Card Image', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'card_image[url]!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Image Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '10',
					'right'  => '10',
					'bottom' => '10',
					'left'   => '10',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_margin_bottom',
			[
				'label'      => esc_html__( 'Spacing Below Image (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-image-wrap' => 'margin-bottom: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'image_height',
			[
				'label'      => esc_html__( 'Image Height (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 100,
						'max'  => 500,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 220,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-image-wrap img' => 'height: {{SIZE}}px; object-fit: cover; width: 100%;',
				],
			]
		);

		// Hover Effects on Image
		$this->add_control(
			'image_hover_scale',
			[
				'label'     => esc_html__( 'Hover Zoom (Scale)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 1,
						'max'  => 1.5,
						'step' => 0.05,
					],
				],
				'default'    => [
					'size' => 1.05,
				],
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-container:hover .eas-glass-card-image-wrap img' => 'transform: scale({{SIZE}});',
					'{{WRAPPER}} .eas-glass-card-image-wrap img'                 => 'transition: transform 0.4s ease; transform-origin: center;',
					'{{WRAPPER}} .eas-glass-card-image-wrap'                     => 'overflow: hidden;',
				],
			]
		);

		$this->end_controls_section();

		// --- CARD BADGE STYLE ---
		$this->start_controls_section(
			'section_style_badge',
			[
				'label'     => esc_html__( 'Card Badge', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'card_badge!' => '',
				],
			]
		);

		$this->add_control(
			'badge_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'badge_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-badge' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'badge_typography',
				'selector' => '{{WRAPPER}} .eas-glass-card-badge',
			]
		);

		$this->add_responsive_control(
			'badge_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '4',
					'right'  => '12',
					'bottom' => '4',
					'left'   => '12',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'badge_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '20',
					'right'  => '20',
					'bottom' => '20',
					'left'   => '20',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// Badge Position (absolute mapping on image wrap)
		$this->add_control(
			'badge_position_heading',
			[
				'label'     => esc_html__( 'Badge Position', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'badge_pos_top',
			[
				'label'      => esc_html__( 'Top Distance (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => -50,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 12,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-badge' => 'top: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'badge_pos_right',
			[
				'label'      => esc_html__( 'Right Distance (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => -50,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 12,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-badge' => 'right: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_section();

		// --- TYPOGRAPHY (TITLE & DESCRIPTION) STYLE ---
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__( 'Card Typography', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Title Styles
		$this->add_control(
			'title_style_heading',
			[
				'label'     => esc_html__( 'Title Styles', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .eas-glass-card-title',
			]
		);

		$this->add_responsive_control(
			'title_margin_bottom',
			[
				'label'      => esc_html__( 'Title Margin Bottom (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 12,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-title' => 'margin-bottom: {{SIZE}}px;',
				],
			]
		);

		// Description Styles
		$this->add_control(
			'description_style_heading',
			[
				'label'     => esc_html__( 'Description Styles', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'description_color',
			[
				'label'     => esc_html__( 'Description Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.7)',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .eas-glass-card-desc',
			]
		);

		$this->add_responsive_control(
			'description_margin_bottom',
			[
				'label'      => esc_html__( 'Description Margin Bottom (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-desc' => 'margin-bottom: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_section();

		// --- BUTTON STYLE ---
		$this->start_controls_section(
			'section_style_button',
			[
				'label'     => esc_html__( 'Action Button', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_button_styles' );

		// Normal State
		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'button_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .eas-glass-card-btn',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .eas-glass-card-btn',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '30',
					'right'  => '30',
					'bottom' => '30',
					'left'   => '30',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '10',
					'right'  => '24',
					'bottom' => '10',
					'left'   => '24',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_shadow',
				'selector' => '{{WRAPPER}} .eas-glass-card-btn',
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'button_color_hover',
			[
				'label'     => esc_html__( 'Text Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-btn:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'button_shadow_hover',
				'selector' => '{{WRAPPER}} .eas-glass-card-btn:hover',
			]
		);

		$this->add_control(
			'button_hover_transition',
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
					'{{WRAPPER}} .eas-glass-card-btn' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// --- TOP-RIGHT FLOATING ICON STYLE ---
		$this->start_controls_section(
			'section_style_tr_icon',
			[
				'label'     => esc_html__( 'Top-Right Floating Icon', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_tr_icon' => 'yes',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_tr_icon_styles' );

		// Normal State
		$this->start_controls_tab(
			'tab_tr_icon_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'tr_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-tr-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-glass-card-tr-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tr_icon_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'transparent',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-tr-icon' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'tr_icon_size',
			[
				'label'      => esc_html__( 'Icon Size (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-tr-icon i' => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .eas-glass-card-tr-icon svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'tr_icon_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-tr-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tr_icon_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-tr-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'tr_icon_pos_top',
			[
				'label'      => esc_html__( 'Top Position Offset (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => -50,
						'max'  => 150,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 15,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-tr-icon' => 'top: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'tr_icon_pos_right',
			[
				'label'      => esc_html__( 'Right Position Offset (px)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => -50,
						'max'  => 150,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 15,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-glass-card-tr-icon' => 'right: {{SIZE}}px;',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_tr_icon_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'tr_icon_color_hover',
			[
				'label'     => esc_html__( 'Icon Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e91e63',
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-tr-icon:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-glass-card-tr-icon:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tr_icon_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-glass-card-tr-icon:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tr_icon_hover_effect',
			[
				'label'   => esc_html__( 'Hover Effect', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'spin',
				'options' => [
					'none'  => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'spin'  => esc_html__( 'Spin', 'apex-addons-for-elementor' ),
					'grow'  => esc_html__( 'Grow (Scale)', 'apex-addons-for-elementor' ),
					'pulse' => esc_html__( 'Pulse', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'tr_icon_transition_duration',
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
					'{{WRAPPER}} .eas-glass-card-tr-icon' => 'transition: all {{SIZE}}s ease;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$title       = $settings['card_title'];
		$desc        = $settings['card_description'];
		$badge       = $settings['card_badge'];
		$btn_text    = $settings['button_text'];
		$btn_link    = $settings['button_link'];
		$btn_icon    = $settings['button_icon'];
		$image       = $settings['card_image'];

		echo '<div class="eas-glass-card-container">';

		// RENDER TOP-RIGHT ICON
		if ( $settings['show_tr_icon'] === 'yes' && ! empty( $settings['tr_icon']['value'] ) ) {
			$tr_icon_link = $settings['tr_icon_link'];
			$hover_effect_class = 'eas-glass-card-tr-icon-' . esc_attr( $settings['tr_icon_hover_effect'] );
			
			echo '<div class="eas-glass-card-tr-icon ' . esc_attr( $hover_effect_class ) . '">';
			if ( ! empty( $tr_icon_link['url'] ) ) {
				$this->add_link_attributes( 'tr_icon_link_attr', $tr_icon_link );
				echo '<a ' . $this->get_render_attribute_string( 'tr_icon_link_attr' ) . ' style="display: inline-flex; text-decoration: none; color: inherit;">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor builds and escapes this attribute string.
				\Elementor\Icons_Manager::render_icon( $settings['tr_icon'], [ 'aria-hidden' => 'true' ] );
				echo '</a>';
			} else {
				\Elementor\Icons_Manager::render_icon( $settings['tr_icon'], [ 'aria-hidden' => 'true' ] );
			}
			echo '</div>';
		}

		// RENDER IMAGE & BADGE
		if ( ! empty( $image['url'] ) ) {
			echo '<div class="eas-glass-card-image-wrap">';
			
			if ( ! empty( $badge ) ) {
				echo '<span class="eas-glass-card-badge">' . esc_html( $badge ) . '</span>';
			}

			echo wp_kses_post( \Elementor\Group_Control_Image_Size::get_attachment_image_html( $settings, 'card_image_size', 'card_image' ) );
			
			echo '</div>';
		} elseif ( ! empty( $badge ) ) {
			// Badge fallback if no image
			echo '<span class="eas-glass-card-badge" style="position: static; display: inline-block; margin-bottom: 12px;">' . esc_html( $badge ) . '</span>';
		}

		// RENDER TITLE
		if ( ! empty( $title ) ) {
			$title_tag = $settings['card_title_tag'];
			printf( '<%1$s class="eas-glass-card-title">%2$s</%1$s>', esc_attr( $title_tag ), esc_html( $title ) );
		}

		// RENDER DESCRIPTION
		if ( ! empty( $desc ) ) {
			echo '<p class="eas-glass-card-desc">' . esc_html( $desc ) . '</p>';
		}

		// RENDER BUTTON
		if ( ! empty( $btn_text ) ) {
			$this->add_link_attributes( 'button_attr', $btn_link );
			$this->add_render_attribute( 'button_attr', 'class', 'eas-glass-card-btn' );

			echo '<a ' . $this->get_render_attribute_string( 'button_attr' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor builds and escapes this attribute string.
			
			if ( ! empty( $btn_icon['value'] ) && $settings['button_icon_position'] === 'before' ) {
				echo '<span class="eas-glass-btn-icon-before">';
				\Elementor\Icons_Manager::render_icon( $btn_icon, [ 'aria-hidden' => 'true' ] );
				echo '</span>';
			}

			echo esc_html( $btn_text );

			if ( ! empty( $btn_icon['value'] ) && $settings['button_icon_position'] === 'after' ) {
				echo '<span class="eas-glass-btn-icon-after">';
				\Elementor\Icons_Manager::render_icon( $btn_icon, [ 'aria-hidden' => 'true' ] );
				echo '</span>';
			}

			echo '</a>';
		}

		echo '</div>';
	}
}
