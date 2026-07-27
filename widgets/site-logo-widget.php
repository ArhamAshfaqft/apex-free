<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Site_Logo_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-site-logo';
	}

	public function get_title() {
		return esc_html__( 'Site Logo', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-site-logo';
	}

	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-site-logo-css' ];
	}

	protected function register_controls() {
		// --- CONTENT SECTION ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Logo Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'logo_source',
			[
				'label'   => esc_html__( 'Source', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => esc_html__( 'Site Customizer Logo', 'apex-addons-for-elementor' ),
					'custom'  => esc_html__( 'Custom Image', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'custom_image',
			[
				'label'     => esc_html__( 'Choose Image', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'logo_source' => 'custom',
				],
			]
		);

		$this->add_control(
			'fallback_type',
			[
				'label'       => esc_html__( 'Fallback when no image', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'title',
				'options'     => [
					'title' => esc_html__( 'Site Title', 'apex-addons-for-elementor' ),
					'custom_text' => esc_html__( 'Custom Text', 'apex-addons-for-elementor' ),
					'none'  => esc_html__( 'None', 'apex-addons-for-elementor' ),
				],
				'description' => esc_html__( 'Displayed if no logo is found or set.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'custom_fallback_text',
			[
				'label'       => esc_html__( 'Custom Fallback Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => esc_html__( 'Enter fallback text', 'apex-addons-for-elementor' ),
				'condition'   => [
					'fallback_type' => 'custom_text',
				],
			]
		);

		$this->add_control(
			'logo_link_type',
			[
				'label'   => esc_html__( 'Link', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'home',
				'options' => [
					'home'   => esc_html__( 'Homepage', 'apex-addons-for-elementor' ),
					'custom' => esc_html__( 'Custom URL', 'apex-addons-for-elementor' ),
					'none'   => esc_html__( 'None', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'custom_link',
			[
				'label'       => esc_html__( 'Custom URL', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'condition'   => [
					'logo_link_type' => 'custom',
				],
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
				],
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .eas-site-logo-container' => 'text-align: {{VALUE}}; justify-content: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION (LOGO / IMAGE) ---
		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Logo Image Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'logo_width',
			[
				'label'      => esc_html__( 'Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 1000 ],
					'%'  => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-site-logo-img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'logo_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 1000 ],
					'%'  => [ 'min' => 0, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-site-logo-img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'logo_height',
			[
				'label'      => esc_html__( 'Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vh' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 500 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-site-logo-img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'contain',
				'options'   => [
					'fill'      => esc_html__( 'Fill', 'apex-addons-for-elementor' ),
					'cover'     => esc_html__( 'Cover', 'apex-addons-for-elementor' ),
					'contain'   => esc_html__( 'Contain', 'apex-addons-for-elementor' ),
					'scale-down'=> esc_html__( 'Scale Down', 'apex-addons-for-elementor' ),
					'none'      => esc_html__( 'None', 'apex-addons-for-elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}} .eas-site-logo-img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		// Hover transitions and effects
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
					'px' => [ 'max' => 1, 'min' => 0.1, 'step' => 0.01 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-site-logo-img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .eas-site-logo-img',
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
					'px' => [ 'max' => 1, 'min' => 0.1, 'step' => 0.01 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-site-logo-container:hover .eas-site-logo-img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .eas-site-logo-container:hover .eas-site-logo-img',
			]
		);

		$this->add_control(
			'hover_transform',
			[
				'label'     => esc_html__( 'Hover Scale', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [ 'min' => 0.5, 'max' => 2, 'step' => 0.05 ],
				],
				'default'   => [ 'size' => 1 ],
				'selectors' => [
					'{{WRAPPER}} .eas-site-logo-container:hover .eas-site-logo-img' => 'transform: scale({{SIZE}});',
				],
			]
		);

		$this->add_control(
			'hover_transition_duration',
			[
				'label'      => esc_html__( 'Transition Duration (s)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 3, 'step' => 0.1 ],
				],
				'default'    => [ 'size' => 0.3 ],
				'selectors'  => [
					'{{WRAPPER}} .eas-site-logo-img' => 'transition: all {{SIZE}}s ease-in-out;',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'logo_border',
				'selector'  => '{{WRAPPER}} .eas-site-logo-img',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'logo_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-site-logo-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'logo_box_shadow',
				'selector' => '{{WRAPPER}} .eas-site-logo-img',
			]
		);

		$this->end_controls_section();

		// --- STYLE SECTION (TEXT FALLBACK) ---
		$this->start_controls_section(
			'section_style_text',
			[
				'label'     => esc_html__( 'Text Style', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'fallback_type' => [ 'title', 'custom_text' ],
				],
			]
		);

		$this->start_controls_tabs( 'tabs_text_style' );

		$this->start_controls_tab(
			'tab_text_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-site-logo-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_text_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'text_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-site-logo-text:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'text_typography',
				'selector'  => '{{WRAPPER}} .eas-site-logo-text',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$logo_url = '';
		if ( 'default' === $settings['logo_source'] ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			if ( $custom_logo_id ) {
				$logo_data = wp_get_attachment_image_src( $custom_logo_id, 'full' );
				if ( $logo_data ) {
					$logo_url = $logo_data[0];
				}
			}
		} else {
			if ( ! empty( $settings['custom_image']['url'] ) ) {
				$logo_url = $settings['custom_image']['url'];
			}
		}

		// Link setup
		$has_link = false;
		$link_url = '';
		if ( 'home' === $settings['logo_link_type'] ) {
			$link_url = home_url( '/' );
			$has_link = true;
		} elseif ( 'custom' === $settings['logo_link_type'] && ! empty( $settings['custom_link']['url'] ) ) {
			$link_url = $settings['custom_link']['url'];
			$has_link = true;
		}

		$wrapper_tag = $has_link ? 'a' : 'div';
		$this->add_render_attribute( 'wrapper', 'class', 'eas-site-logo-container' );

		if ( $has_link ) {
			$this->add_render_attribute( 'wrapper', 'href', esc_url( $link_url ) );
			if ( 'custom' === $settings['logo_link_type'] ) {
				$this->add_link_attributes( 'wrapper', $settings['custom_link'] );
			}
		}

		echo '<div class="eas-site-logo-wrap">';
		echo '<' . esc_html( $wrapper_tag ) . ' ' . $this->get_render_attribute_string( 'wrapper' ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor builds and escapes this attribute string.

		if ( ! empty( $logo_url ) ) {
			echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="eas-site-logo-img" />';
		} elseif ( 'none' !== $settings['fallback_type'] ) {
			$text = 'title' === $settings['fallback_type'] ? get_bloginfo( 'name' ) : $settings['custom_fallback_text'];
			echo '<span class="eas-site-logo-text">' . esc_html( $text ) . '</span>';
		}

		echo '</' . esc_attr( $wrapper_tag ) . '>';
		echo '</div>';
	}
}
