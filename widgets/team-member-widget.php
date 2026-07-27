<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Team Member Grid & Carousel Widget.
 *
 * An interactive team member showcase widget featuring customizable grids, sliders,
 * and high-end animations/social overlays.
 */
class Team_Member_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'eas-team-member';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return esc_html__( 'Team Member Showcase', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-person';
	}

	/**
	 * Get widget categories.
	 */
	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords() {
		return [ 'team', 'member', 'carousel', 'grid', 'social', 'showcase', 'slider', 'profile', 'apex' ];
	}

	/**
	 * Get style depends.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-team-member-css' ];
	}

	/**
	 * Get script depends.
	 */
	public function get_script_depends() {
		// Enqueues Swiper (built-in Elementor library) and our custom JS file
		return [ 'swiper', 'apexadfo-team-member-js' ];
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ---------------------------------------------------------------------
		// Content Tab - Team Members
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_members',
			[
				'label' => esc_html__( 'Team Members', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'member_name',
			[
				'label'       => esc_html__( 'Name', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Henry Fayowl', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'member_designation',
			[
				'label'       => esc_html__( 'Designation', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'UX Expert', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'member_image',
			[
				'label'   => esc_html__( 'Profile Image', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'facebook_url',
			[
				'label'       => esc_html__( 'Facebook URL', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://facebook.com/username', 'apex-addons-for-elementor' ),
				'default'     => '#',
			]
		);

		$repeater->add_control(
			'twitter_url',
			[
				'label'       => esc_html__( 'Twitter URL', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://twitter.com/username', 'apex-addons-for-elementor' ),
				'default'     => '#',
			]
		);

		$repeater->add_control(
			'linkedin_url',
			[
				'label'       => esc_html__( 'LinkedIn URL', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://linkedin.com/in/username', 'apex-addons-for-elementor' ),
				'default'     => '#',
			]
		);

		$repeater->add_control(
			'instagram_url',
			[
				'label'       => esc_html__( 'Instagram URL', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://instagram.com/username', 'apex-addons-for-elementor' ),
				'default'     => '#',
			]
		);

		$this->add_control(
			'members_list',
			[
				'label'       => esc_html__( 'Team Members', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'member_name'        => esc_html__( 'Henry Fayowl', 'apex-addons-for-elementor' ),
						'member_designation' => esc_html__( 'UX Expert', 'apex-addons-for-elementor' ),
					],
					[
						'member_name'        => esc_html__( 'Sarah Jenkins', 'apex-addons-for-elementor' ),
						'member_designation' => esc_html__( 'Lead Designer', 'apex-addons-for-elementor' ),
					],
					[
						'member_name'        => esc_html__( 'Marcus Aurelius', 'apex-addons-for-elementor' ),
						'member_designation' => esc_html__( 'Tech Director', 'apex-addons-for-elementor' ),
					],
				],
				'title_field' => '{{{ member_name }}}',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Layout & Carousel Config
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout Settings', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout_type',
			[
				'label'   => esc_html__( 'Layout Format', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid'     => esc_html__( 'Static Columns Grid', 'apex-addons-for-elementor' ),
					'carousel' => esc_html__( 'Swiper Slider Carousel', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'hover_preset',
			[
				'label'   => esc_html__( 'Hover Style Preset', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'fade',
				'options' => [
					'fade'   => esc_html__( 'Preset A: Gradient Overlay + Scale Icons', 'apex-addons-for-elementor' ),
					'reveal' => esc_html__( 'Preset B: Grayscale to Color + Slide Up', 'apex-addons-for-elementor' ),
					'zoom'   => esc_html__( 'Preset C: Zoom Photo + Rotate Icons', 'apex-addons-for-elementor' ),
				],
			]
		);

		// Grid Layout Columns Control
		$this->add_responsive_control(
			'grid_columns',
			[
				'label'     => esc_html__( 'Columns', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'selectors' => [
					'{{WRAPPER}} .eas-team-member-grid' => '--eas-tm-cols: {{VALUE}};',
				],
				'condition' => [
					'layout_type' => 'grid',
				],
			]
		);

		// Carousel Layout Columns Control
		$this->add_responsive_control(
			'slides_to_show',
			[
				'label'     => esc_html__( 'Slides to Show', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '3',
				'options'   => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				],
				'condition' => [
					'layout_type' => 'carousel',
				],
			]
		);

		$this->add_control(
			'carousel_autoplay',
			[
				'label'     => esc_html__( 'Autoplay', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => [
					'layout_type' => 'carousel',
				],
			]
		);

		$this->add_control(
			'carousel_autoplay_speed',
			[
				'label'     => esc_html__( 'Autoplay Speed (ms)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 3000,
				'step'      => 100,
				'condition' => [
					'layout_type' => 'carousel',
					'carousel_autoplay' => 'yes',
				],
			]
		);

		$this->add_control(
			'carousel_loop',
			[
				'label'     => esc_html__( 'Infinite Loop', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'layout_type' => 'carousel',
				],
			]
		);

		$this->add_control(
			'carousel_arrows',
			[
				'label'     => esc_html__( 'Show Arrows', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'layout_type' => 'carousel',
				],
			]
		);

		$this->add_control(
			'carousel_dots',
			[
				'label'     => esc_html__( 'Show Pagination Dots', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					'layout_type' => 'carousel',
				],
			]
		);

		$this->add_responsive_control(
			'layout_gap',
			[
				'label'      => esc_html__( 'Item Spacing Gap', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-team-member-grid' => '--eas-tm-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Profile Photo
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_photo',
			[
				'label' => esc_html__( 'Profile Photo', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'photo_width',
			[
				'label'      => esc_html__( 'Photo Width (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 80,
						'max'  => 400,
						'step' => 5,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 240,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-team-member-photo-wrap' => '--eas-tm-photo-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'photo_height',
			[
				'label'      => esc_html__( 'Photo Height (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 80,
						'max'  => 400,
						'step' => 5,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 240,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-team-member-photo-wrap' => '--eas-tm-photo-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'photo_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'    => '50',
					'right'  => '50',
					'bottom' => '50',
					'left'   => '50',
					'unit'   => '%',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-team-member-photo-wrap' => '--eas-tm-photo-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'photo_border',
				'selector' => '{{WRAPPER}} .eas-team-member-photo-wrap',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'photo_shadow',
				'selector' => '{{WRAPPER}} .eas-team-member-photo-wrap',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Social Overlays
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_overlay',
			[
				'label' => esc_html__( 'Hover Overlay & Socials', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name'           => 'overlay_bg',
				'label'          => esc_html__( 'Overlay Background', 'apex-addons-for-elementor' ),
				'types'          => [ 'classic', 'gradient' ],
				'selector'       => '{{WRAPPER}} .eas-team-member-overlay',
				'fields_options' => [
					'background' => [
						'default' => 'gradient',
					],
					'color' => [
						'default' => 'rgba(168, 85, 247, 0.7)',
					],
					'color_b' => [
						'default' => 'rgba(107, 33, 168, 0.95)',
					],
				],
			]
		);

		// Social Icons sub styling
		$this->add_control(
			'heading_socials',
			[
				'label'     => esc_html__( 'Social Icon Styles', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'social_size',
			[
				'label'      => esc_html__( 'Icon Button Size (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 20,
						'max' => 60,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 38,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-team-member-social-link' => '--eas-tm-social-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'social_radius',
			[
				'label'      => esc_html__( 'Border Radius (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 30,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 19,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-team-member-social-link' => '--eas-tm-social-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_social_style' );

		$this->start_controls_tab(
			'tab_social_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'social_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1e1e1e',
				'selectors' => [
					'{{WRAPPER}} .eas-team-member-social-link' => '--eas-tm-social-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'social_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-team-member-social-link' => '--eas-tm-social-bg: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_social_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'social_hover_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-team-member-social-link:hover' => '--eas-tm-social-hover-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'social_hover_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1e1e1e',
				'selectors' => [
					'{{WRAPPER}} .eas-team-member-social-link:hover' => '--eas-tm-social-hover-bg: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Typography & Info
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__( 'Typography & Details', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		// Name Styles
		$this->add_control(
			'heading_name',
			[
				'label'     => esc_html__( 'Name Styles', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'name_color',
			[
				'label'     => esc_html__( 'Name Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1e1e1e',
				'selectors' => [
					'{{WRAPPER}} .eas-team-member-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'name_typography',
				'selector' => '{{WRAPPER}} .eas-team-member-name',
			]
		);

		// Designation Styles
		$this->add_control(
			'heading_designation',
			[
				'label'     => esc_html__( 'Designation Styles', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'designation_color',
			[
				'label'     => esc_html__( 'Designation Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#666666',
				'selectors' => [
					'{{WRAPPER}} .eas-team-member-designation' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'designation_typography',
				'selector' => '{{WRAPPER}} .eas-team-member-designation',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Navigation & Dots
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_navigation',
			[
				'label'     => esc_html__( 'Navigation & Dots', 'apex-addons-for-elementor' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_type' => 'carousel',
				],
			]
		);

		// ── Arrow Controls ───────────────────────────────────────────────────
		$this->add_control(
			'heading_arrows_style',
			[
				'label'     => esc_html__( 'Arrow Buttons', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'condition' => [
					'carousel_arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'arrow_size',
			[
				'label'      => esc_html__( 'Button Size', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 28,
						'max' => 64,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 44,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-tm-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'carousel_arrows' => 'yes',
				],
			]
		);

		$this->start_controls_tabs(
			'tabs_arrow_style',
			[
				'condition' => [
					'carousel_arrows' => 'yes',
				],
			]
		);

		$this->start_controls_tab(
			'tab_arrow_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'arrow_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1e1e1e',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-arrow' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_bg',
			[
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-arrow' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.1)',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-arrow' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrow_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'arrow_hover_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-arrow:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_hover_bg',
			[
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#a855f7',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-arrow:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'arrow_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#a855f7',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-arrow:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		// ── Dots Controls ────────────────────────────────────────────────────
		$this->add_control(
			'heading_dots_style',
			[
				'label'     => esc_html__( 'Pagination Dots', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'carousel_dots' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots_size',
			[
				'label'      => esc_html__( 'Dot Size', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 4,
						'max' => 20,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-tm-dots .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'carousel_dots' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots_active_size_scale',
			[
				'label'      => esc_html__( 'Active Dot Scale', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 2.5,
						'step' => 0.1,
					],
				],
				'default'    => [
					'size' => 1.3,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-tm-dots .swiper-pagination-bullet-active' => 'transform: scale({{SIZE}});',
				],
				'condition' => [
					'carousel_dots' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots_gap',
			[
				'label'      => esc_html__( 'Spacing Between Dots', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 2,
						'max' => 30,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-tm-dots' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'carousel_dots' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots_top_spacing',
			[
				'label'      => esc_html__( 'Top Spacing', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 80,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-tm-dots' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'carousel_dots' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots_border_radius',
			[
				'label'      => esc_html__( 'Dot Border Radius', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-tm-dots .swiper-pagination-bullet' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'carousel_dots' => 'yes',
				],
			]
		);

		$this->start_controls_tabs(
			'tabs_dots_style',
			[
				'condition' => [
					'carousel_dots' => 'yes',
				],
			]
		);

		$this->start_controls_tab(
			'tab_dots_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'dots_color',
			[
				'label'     => esc_html__( 'Dot Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.15)',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-dots .swiper-pagination-bullet' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dots_active',
			[
				'label' => esc_html__( 'Active', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'dots_active_color',
			[
				'label'     => esc_html__( 'Active Dot Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#a855f7',
				'selectors' => [
					'{{WRAPPER}} .eas-tm-dots .swiper-pagination-bullet-active' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render frontend HTML.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$list = $settings['members_list'] ?? [];

		if ( empty( $list ) ) {
			return;
		}

		$layout_type = $settings['layout_type'] ?? 'grid';
		$hover_preset = $settings['hover_preset'] ?? 'fade';

		// Set base wrapper classes
		$wrapper_classes = [ 'eas-team-member-showcase' ];
		if ( $layout_type === 'carousel' ) {
			$wrapper_classes[] = 'eas-team-member-carousel-wrap';
		}

		// Set card list classes
		$list_classes = [];
		if ( $layout_type === 'grid' ) {
			$list_classes[] = 'eas-team-member-grid';
		} else {
			$list_classes[] = 'swiper';
			$list_classes[] = 'swiper-container';
		}

		// Prepare Swiper config values
		$carousel_config = [
			'slidesDesktop' => intval( $settings['slides_to_show'] ?? 3 ),
			'slidesTablet'  => intval( $settings['slides_to_show_tablet'] ?? 2 ),
			'slidesMobile'  => intval( $settings['slides_to_show_mobile'] ?? 1 ),
			'autoplay'      => $settings['carousel_autoplay'] ?? 'no',
			'autoplaySpeed' => intval( $settings['carousel_autoplay_speed'] ?? 3000 ),
			'loop'          => $settings['carousel_loop'] ?? 'yes',
			'arrows'        => $settings['carousel_arrows'] ?? 'yes',
			'dots'          => $settings['carousel_dots'] ?? 'yes',
			'gap'           => isset( $settings['layout_gap']['size'] ) ? intval( $settings['layout_gap']['size'] ) : 30,
		];
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>"
			data-eas-carousel-config="<?php echo esc_attr( wp_json_encode( $carousel_config ) ); ?>">

			<div class="<?php echo esc_attr( implode( ' ', $list_classes ) ); ?>">
				
				<?php if ( $layout_type === 'carousel' ) : ?>
					<div class="swiper-wrapper">
				<?php endif; ?>

				<?php foreach ( $list as $index => $item ) : 
					$name = $item['member_name'] ?? '';
					$designation = $item['member_designation'] ?? '';
					$img_url = ! empty( $item['member_image']['url'] ) ? esc_url( $item['member_image']['url'] ) : '';

					// Gather social URL tags
					$socials = [];
					if ( ! empty( $item['facebook_url'] ) && '#' !== $item['facebook_url'] ) {
						$socials['facebook'] = [ 
							'url' => esc_url( $item['facebook_url'] ), 
							'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="display:block;"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>' 
						];
					}
					if ( ! empty( $item['twitter_url'] ) && '#' !== $item['twitter_url'] ) {
						$socials['twitter'] = [ 
							'url' => esc_url( $item['twitter_url'] ), 
							'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="display:block;"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></svg>' 
						];
					}
					if ( ! empty( $item['linkedin_url'] ) && '#' !== $item['linkedin_url'] ) {
						$socials['linkedin'] = [ 
							'url' => esc_url( $item['linkedin_url'] ), 
							'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="display:block;"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2zM4 2a2 2 0 1 1-2 2 2 2 0 0 1 2-2z"></path></svg>' 
						];
					}
					if ( ! empty( $item['instagram_url'] ) && '#' !== $item['instagram_url'] ) {
						$socials['instagram'] = [ 
							'url' => esc_url( $item['instagram_url'] ), 
							'svg' => '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" style="display:block;"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"></path></svg>' 
						];
					}

					// Card class
					$card_classes = [ 'eas-team-member-card' ];
					if ( $layout_type === 'carousel' ) {
						$card_classes[] = 'swiper-slide';
					}
					?>
					<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
						
						<div class="eas-team-member-photo-wrap eas-tm-preset-<?php echo esc_attr( $hover_preset ); ?>">
							
							<?php if ( ! empty( $img_url ) ) : ?>
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $name ); ?>">
							<?php endif; ?>

							<div class="eas-team-member-overlay"></div>

							<?php if ( ! empty( $socials ) ) : ?>
								<div class="eas-team-member-socials">
									<?php foreach ( $socials as $key => $social ) : ?>
										<a href="<?php echo esc_url( $social['url'] ); ?>" 
											class="eas-team-member-social-link" 
											target="_blank" 
											rel="noopener noreferrer"
											title="<?php echo esc_attr( ucfirst( $key ) ); ?>">
											<?php echo wp_kses( $social['svg'], [ 'svg' => [ 'viewbox' => true, 'width' => true, 'height' => true, 'fill' => true, 'style' => true ], 'path' => [ 'd' => true ] ] ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

						</div>

						<h4 class="eas-team-member-name"><?php echo esc_html( $name ); ?></h4>
						<p class="eas-team-member-designation"><?php echo esc_html( $designation ); ?></p>

					</div>
				<?php endforeach; ?>

				<?php if ( $layout_type === 'carousel' ) : ?>
					</div>
				<?php endif; ?>

			</div>

			<!-- Swiper Carousel Nav elements (only enqueued in carousel mode) -->
			<?php if ( $layout_type === 'carousel' ) : ?>
				<?php if ( 'yes' === $settings['carousel_arrows'] ) : ?>
					<div class="eas-tm-arrow eas-tm-arrow-prev" role="button" aria-label="Previous slide">
						<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
					</div>
					<div class="eas-tm-arrow eas-tm-arrow-next" role="button" aria-label="Next slide">
						<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="display:block;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $settings['carousel_dots'] ) : ?>
					<div class="eas-tm-dots"></div>
				<?php endif; ?>
			<?php endif; ?>

		</div>
		<?php
	}
}
