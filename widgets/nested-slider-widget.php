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

class Nested_Slider_Widget extends Widget_Nested_Base {

	public function get_name() {
		return 'eas-nested-slider';
	}

	public function get_title() {
		return esc_html__( 'Nested Motion Carousel', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-nested-carousel';
	}

	public function get_keywords() {
		return [ 'nested', 'carousel', 'slider', 'slides', 'motion', 'content' ];
	}

	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	public function get_style_depends() {
		return [ 'e-swiper', 'apexadfo-nested-slider-css' ];
	}

	public function get_script_depends() {
		return [ 'swiper', 'apexadfo-nested-slider-js' ];
	}

	protected function get_default_children_elements() {
		$children = [];
		for ( $index = 1; $index <= 4; $index++ ) {
			$children[] = [
				'elType'  => 'container',
				'settings' => [
					/* translators: %d: Slide number. */
					'_title'       => sprintf( esc_html__( 'Slide #%d', 'apex-addons-for-elementor' ), $index ),
					'content_width' => 'full',
				],
			];
		}
		return $children;
	}

	protected function get_default_repeater_title_setting_key() {
		return 'slide_title';
	}

	protected function get_default_children_title() {
		/* translators: %d: Slide number supplied by Elementor. */
		return esc_html__( 'Slide #%d', 'apex-addons-for-elementor' );
	}

	protected function get_default_children_placeholder_selector() {
		return '.eas-slider-slides';
	}

	protected function get_default_children_container_placeholder_selector() {
		return '.eas-slider-slide-item';
	}

	protected function get_initial_config(): array {
		return array_merge( parent::get_initial_config(), [
			'support_improved_repeaters' => true,
			'target_container'           => [ '.eas-carousel-viewport > .eas-slider-slides' ],
			'node'                       => 'div',
			'is_interlaced'              => true,
		] );
	}

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
		do_action( 'apexadfo_nested_carousel_register_controls', $this );
	}

	private function register_content_controls() {
		$this->start_controls_section( 'section_slides', [
			'label' => esc_html__( 'Slides', 'apex-addons-for-elementor' ),
		] );

		$repeater = new Repeater();
		$repeater->add_control( 'slide_title', [
			'label'       => esc_html__( 'Slide Label', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Slide', 'apex-addons-for-elementor' ),
			'label_block' => true,
		] );

		$this->add_control( 'carousel_name', [
			'label'       => esc_html__( 'Accessible Carousel Name', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Featured content', 'apex-addons-for-elementor' ),
			'label_block' => true,
		] );

		$this->add_control( 'slides', [
			'label'              => esc_html__( 'Carousel Slides', 'apex-addons-for-elementor' ),
			'type'               => Control_Nested_Repeater::CONTROL_TYPE,
			'fields'             => $repeater->get_controls(),
			'frontend_available' => true,
			'default'            => [
				[ 'slide_title' => esc_html__( 'Slide #1', 'apex-addons-for-elementor' ) ],
				[ 'slide_title' => esc_html__( 'Slide #2', 'apex-addons-for-elementor' ) ],
				[ 'slide_title' => esc_html__( 'Slide #3', 'apex-addons-for-elementor' ) ],
				[ 'slide_title' => esc_html__( 'Slide #4', 'apex-addons-for-elementor' ) ],
			],
			'title_field'        => '{{{ slide_title }}}',
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_slider_settings', [
			'label' => esc_html__( 'Layout & Behaviour', 'apex-addons-for-elementor' ),
		] );

		$this->add_responsive_control( 'slides_per_view', [
			'label'              => esc_html__( 'Slides in View', 'apex-addons-for-elementor' ),
			'type'               => Controls_Manager::NUMBER,
			'min'                => 1,
			'max'                => 8,
			'step'               => 0.1,
			'default'            => 3,
			'tablet_default'     => 2,
			'mobile_default'     => 1,
			'frontend_available' => true,
			'render_type'        => 'template',
			'selectors'          => [
				'{{WRAPPER}}' => '--eas-carousel-slides-view: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'slides_to_scroll', [
			'label'              => esc_html__( 'Slides per Movement', 'apex-addons-for-elementor' ),
			'type'               => Controls_Manager::NUMBER,
			'min'                => 1,
			'max'                => 8,
			'step'               => 1,
			'default'            => 1,
			'frontend_available' => true,
		] );

		$this->add_responsive_control( 'space_between', [
			'label'              => esc_html__( 'Gap Between Slides', 'apex-addons-for-elementor' ),
			'type'               => Controls_Manager::SLIDER,
			'size_units'         => [ 'px', 'em', 'rem' ],
			'range'              => [ 'px' => [ 'min' => 0, 'max' => 160 ] ],
			'default'            => [ 'size' => 20, 'unit' => 'px' ],
			'frontend_available' => true,
			'render_type'        => 'template',
			'selectors'          => [
				'{{WRAPPER}}' => '--eas-carousel-gap: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_control( 'speed', [
			'label'      => esc_html__( 'Transition Duration (ms)', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::NUMBER,
			'min'        => 100,
			'max'        => 5000,
			'step'       => 50,
			'default'    => 650,
		] );

		$this->add_control( 'easing', [
			'label'   => esc_html__( 'Movement Easing', 'apex-addons-for-elementor' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'smooth',
			'options' => [
				'smooth' => esc_html__( 'Smooth', 'apex-addons-for-elementor' ),
				'gentle' => esc_html__( 'Gentle', 'apex-addons-for-elementor' ),
				'crisp'  => esc_html__( 'Crisp', 'apex-addons-for-elementor' ),
				'linear' => esc_html__( 'Linear', 'apex-addons-for-elementor' ),
			],
		] );

		$this->add_control( 'centered_slides', [
			'label'        => esc_html__( 'Center Active Slide', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'no',
		] );

		$this->add_control( 'equal_height', [
			'label'        => esc_html__( 'Equal Height Slides', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_control( 'loop', [
			'label'        => esc_html__( 'Infinite Loop', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => esc_html__( 'Looping is disabled inside the editor for stable editing.', 'apex-addons-for-elementor' ),
		] );

		$this->add_control( 'autoplay', [
			'label'        => esc_html__( 'Autoplay', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'no',
			'description'  => esc_html__( 'Autoplay runs in preview and on the frontend, not while editing.', 'apex-addons-for-elementor' ),
		] );

		$this->add_control( 'autoplay_delay', [
			'label'     => esc_html__( 'Autoplay Delay (ms)', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 500,
			'max'       => 20000,
			'step'      => 100,
			'default'   => 3500,
			'condition' => [ 'autoplay' => 'yes' ],
		] );

		$this->add_control( 'pause_on_hover', [
			'label'        => esc_html__( 'Pause on Hover', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => [ 'autoplay' => 'yes' ],
		] );

		$this->add_control( 'pause_on_interaction', [
			'label'        => esc_html__( 'Pause after Interaction', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => [ 'autoplay' => 'yes' ],
		] );

		$this->add_control( 'keyboard', [
			'label'        => esc_html__( 'Keyboard Navigation', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		if ( ! defined( 'APEXADFO_PRO_VERSION' ) ) {
			$this->add_control( 'apexadfo_nested_carousel_companion_notice', [
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<strong>' . esc_html__( 'Available in Apex Pro', 'apex-addons-for-elementor' ) . '</strong><br>' . esc_html__( 'Continuous ticker, vertical carousel, scroll-scrubbed movement, parallax depth, and nested-content entrance choreography.', 'apex-addons-for-elementor' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			] );
		}

		$this->end_controls_section();

		$this->start_controls_section( 'section_navigation', [
			'label' => esc_html__( 'Navigation', 'apex-addons-for-elementor' ),
		] );

		$this->add_control( 'arrows', [
			'label'        => esc_html__( 'Navigation Arrows', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->add_control( 'previous_icon', [
			'label'     => esc_html__( 'Previous Icon', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::ICONS,
			'default'   => [ 'value' => 'eicon-chevron-left', 'library' => 'eicons' ],
			'condition' => [ 'arrows' => 'yes' ],
		] );

		$this->add_control( 'next_icon', [
			'label'     => esc_html__( 'Next Icon', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::ICONS,
			'default'   => [ 'value' => 'eicon-chevron-right', 'library' => 'eicons' ],
			'condition' => [ 'arrows' => 'yes' ],
		] );

		$this->add_control( 'pagination', [
			'label'   => esc_html__( 'Pagination', 'apex-addons-for-elementor' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'bullets',
			'options' => [
				'none'        => esc_html__( 'None', 'apex-addons-for-elementor' ),
				'bullets'     => esc_html__( 'Dots', 'apex-addons-for-elementor' ),
				'fraction'    => esc_html__( 'Fraction', 'apex-addons-for-elementor' ),
				'progressbar' => esc_html__( 'Progress Bar', 'apex-addons-for-elementor' ),
			],
		] );

		$this->end_controls_section();
	}

	private function register_style_controls() {
		$this->start_controls_section( 'section_slides_style', [
			'label' => esc_html__( 'Slides', 'apex-addons-for-elementor' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'slider_padding', [
			'label'      => esc_html__( 'Carousel Padding', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em', 'rem' ],
			'selectors'  => [
				'{{WRAPPER}} .eas-carousel-viewport' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_control( 'gap_background_color', [
			'label'       => esc_html__( 'Gap Background Color', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::COLOR,
			'default'     => '#ffffff',
			'description' => esc_html__( 'Controls the visible spacer between vertical slides. Match it to the carousel section background when needed.', 'apex-addons-for-elementor' ),
			'selectors'   => [
				'{{WRAPPER}} .eas-nested-carousel' => '--eas-carousel-gap-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'slide_height_notice', [
			'type'            => Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Slide height comes from the nested content. Set height or minimum height on the containers placed inside each slide.', 'apex-addons-for-elementor' ),
			'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name'     => 'slide_background',
			'selector' => '{{WRAPPER}} .eas-slider-slide-item > .e-con',
		] );

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'slide_border',
			'selector' => '{{WRAPPER}} .eas-slider-slide-item > .e-con',
		] );

		$this->add_responsive_control( 'slide_radius', [
			'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .eas-slider-slide-item > .e-con' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'slide_shadow',
			'selector' => '{{WRAPPER}} .eas-slider-slide-item > .e-con',
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'section_arrows_style', [
			'label'     => esc_html__( 'Navigation Arrows', 'apex-addons-for-elementor' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'arrows' => 'yes' ],
		] );

		$this->add_responsive_control( 'arrow_box_size', [
			'label'      => esc_html__( 'Button Size', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em', 'rem' ],
			'range'      => [ 'px' => [ 'min' => 20, 'max' => 160 ] ],
			'default'    => [ 'size' => 48, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .eas-carousel-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'arrow_icon_size', [
			'label'      => esc_html__( 'Icon Size', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em', 'rem' ],
			'range'      => [ 'px' => [ 'min' => 8, 'max' => 80 ] ],
			'default'    => [ 'size' => 18, 'unit' => 'px' ],
			'selectors'  => [
				'{{WRAPPER}} .eas-carousel-arrow' => 'font-size: {{SIZE}}{{UNIT}};',
				'{{WRAPPER}} .eas-carousel-arrow svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'arrow_vertical_position', [
			'label'      => esc_html__( 'Y Position', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ '%', 'px' ],
			'range'      => [ '%' => [ 'min' => -50, 'max' => 150 ], 'px' => [ 'min' => -500, 'max' => 1000 ] ],
			'default'    => [ 'size' => 50, 'unit' => '%' ],
			'selectors'  => [
				'{{WRAPPER}} .eas-carousel-arrow' => 'top: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'previous_arrow_x', [
			'label'      => esc_html__( 'Previous Arrow X', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'em', 'rem' ],
			'range'      => [ 'px' => [ 'min' => -300, 'max' => 500 ] ],
			'default'    => [ 'size' => 16, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .eas-carousel-prev' => 'left: {{SIZE}}{{UNIT}};' ],
		] );

		$this->add_responsive_control( 'next_arrow_x', [
			'label'      => esc_html__( 'Next Arrow X', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'em', 'rem' ],
			'range'      => [ 'px' => [ 'min' => -300, 'max' => 500 ] ],
			'default'    => [ 'size' => 16, 'unit' => 'px' ],
			'selectors'  => [ '{{WRAPPER}} .eas-carousel-next' => 'right: {{SIZE}}{{UNIT}};' ],
		] );

		$this->start_controls_tabs( 'arrow_style_tabs' );
		$this->start_controls_tab( 'arrow_normal_tab', [ 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ] );
		$this->add_control( 'arrow_color', [
			'label' => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eas-carousel-arrow' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'arrow_bg_color', [
			'label' => esc_html__( 'Background', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eas-carousel-arrow' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();
		$this->start_controls_tab( 'arrow_hover_tab', [ 'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ) ] );
		$this->add_control( 'arrow_hover_color', [
			'label' => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eas-carousel-arrow:hover' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'arrow_hover_bg', [
			'label' => esc_html__( 'Background', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .eas-carousel-arrow:hover' => 'background-color: {{VALUE}};' ],
		] );
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control( Group_Control_Border::get_type(), [
			'name' => 'arrow_border', 'selector' => '{{WRAPPER}} .eas-carousel-arrow',
		] );
		$this->add_responsive_control( 'arrow_radius', [
			'label' => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name' => 'arrow_shadow', 'selector' => '{{WRAPPER}} .eas-carousel-arrow',
		] );
		$this->end_controls_section();

		$this->start_controls_section( 'section_pagination_style', [
			'label'     => esc_html__( 'Pagination', 'apex-addons-for-elementor' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'pagination!' => 'none' ],
		] );

		$this->add_responsive_control( 'pagination_x', [
			'label' => esc_html__( 'X Position', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ], 'range' => [ 'px' => [ 'min' => -600, 'max' => 600 ], '%' => [ 'min' => -100, 'max' => 100 ] ],
			'default' => [ 'size' => 0, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-pagination' => '--eas-pagination-x: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'pagination_y', [
			'label' => esc_html__( 'Y Position', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ], 'range' => [ 'px' => [ 'min' => -400, 'max' => 400 ], '%' => [ 'min' => -100, 'max' => 100 ] ],
			'default' => [ 'size' => -24, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-pagination' => '--eas-pagination-y: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_control( 'pagination_alignment', [
			'label' => esc_html__( 'Alignment', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::CHOOSE, 'default' => 'center',
			'options' => [
				'left' => [ 'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ), 'icon' => 'eicon-text-align-left' ],
				'center' => [ 'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ), 'icon' => 'eicon-text-align-center' ],
				'right' => [ 'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ), 'icon' => 'eicon-text-align-right' ],
			],
			'selectors_dictionary' => [ 'left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-pagination' => 'justify-content: {{VALUE}};' ],
		] );
		$this->add_responsive_control( 'dot_gap', [
			'label' => esc_html__( 'Dot Gap', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 0, 'max' => 80 ] ], 'default' => [ 'size' => 8, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-pagination' => 'gap: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'dot_size', [
			'label' => esc_html__( 'Dot Size', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 2, 'max' => 50 ] ], 'default' => [ 'size' => 9, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_responsive_control( 'active_dot_width', [
			'label' => esc_html__( 'Active Dot Width', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 2, 'max' => 100 ] ], 'default' => [ 'size' => 24, 'unit' => 'px' ],
			'selectors' => [ '{{WRAPPER}} .swiper-pagination-bullet-active' => 'width: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_control( 'dot_color', [
			'label' => esc_html__( 'Inactive Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .swiper-pagination-bullet' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_control( 'dot_active_color', [
			'label' => esc_html__( 'Active Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .swiper-pagination-bullet-active, {{WRAPPER}} .swiper-pagination-progressbar-fill' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_responsive_control( 'dot_radius', [
			'label' => esc_html__( 'Dot Border Radius', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 50 ], '%' => [ 'min' => 0, 'max' => 50 ] ],
			'default' => [ 'size' => 50, 'unit' => '%' ],
			'selectors' => [ '{{WRAPPER}} .swiper-pagination-bullet' => 'border-radius: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [
			'name' => 'dot_border', 'selector' => '{{WRAPPER}} .swiper-pagination-bullet',
		] );
		$this->add_responsive_control( 'progress_height', [
			'label' => esc_html__( 'Progress Bar Height', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 1, 'max' => 40 ] ], 'default' => [ 'size' => 4, 'unit' => 'px' ],
			'condition' => [ 'pagination' => 'progressbar' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-pagination.swiper-pagination-progressbar' => 'height: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_control( 'progress_track_color', [
			'label' => esc_html__( 'Progress Track Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'condition' => [ 'pagination' => 'progressbar' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-pagination.swiper-pagination-progressbar' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name' => 'fraction_typography', 'selector' => '{{WRAPPER}} .eas-carousel-pagination.swiper-pagination-fraction',
			'condition' => [ 'pagination' => 'fraction' ],
		] );
		$this->add_control( 'fraction_color', [
			'label' => esc_html__( 'Fraction Color', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::COLOR,
			'condition' => [ 'pagination' => 'fraction' ],
			'selectors' => [ '{{WRAPPER}} .eas-carousel-pagination.swiper-pagination-fraction' => 'color: {{VALUE}};' ],
		] );
		$this->end_controls_section();
	}

	private function number_setting( array $settings, $key, $default ) {
		$value = $settings[ $key ] ?? $default;
		if ( is_array( $value ) ) {
			$value = $value['size'] ?? $default;
		}
		return is_numeric( $value ) ? (float) $value : (float) $default;
	}

	private function carousel_config( array $settings ) {
		$config = [
			'version'            => 3,
			'slidesPerView'      => $this->number_setting( $settings, 'slides_per_view', 3 ),
			'slidesPerViewTablet'=> $this->number_setting( $settings, 'slides_per_view_tablet', 2 ),
			'slidesPerViewMobile'=> $this->number_setting( $settings, 'slides_per_view_mobile', 1 ),
			'slidesToScroll'     => $this->number_setting( $settings, 'slides_to_scroll', 1 ),
			'slidesToScrollTablet'=> $this->number_setting( $settings, 'slides_to_scroll_tablet', 1 ),
			'slidesToScrollMobile'=> $this->number_setting( $settings, 'slides_to_scroll_mobile', 1 ),
			'spaceBetween'       => $this->number_setting( $settings, 'space_between', 20 ),
			'spaceBetweenTablet' => $this->number_setting( $settings, 'space_between_tablet', 20 ),
			'spaceBetweenMobile' => $this->number_setting( $settings, 'space_between_mobile', 16 ),
			'speed'              => (int) $this->number_setting( $settings, 'speed', 650 ),
			'easing'             => $settings['easing'] ?? 'smooth',
			'centeredSlides'     => 'yes' === ( $settings['centered_slides'] ?? 'no' ),
			'equalHeight'        => 'yes' === ( $settings['equal_height'] ?? 'yes' ),
			'loop'               => 'yes' === ( $settings['loop'] ?? 'yes' ),
			'autoplay'           => 'yes' === ( $settings['autoplay'] ?? 'no' ),
			'autoplayDelay'      => (int) $this->number_setting( $settings, 'autoplay_delay', 3500 ),
			'pauseOnHover'       => 'yes' === ( $settings['pause_on_hover'] ?? 'yes' ),
			'pauseOnInteraction' => 'yes' === ( $settings['pause_on_interaction'] ?? 'yes' ),
			'keyboard'           => 'yes' === ( $settings['keyboard'] ?? 'yes' ),
			'arrows'             => 'yes' === ( $settings['arrows'] ?? 'yes' ),
			'pagination'         => $settings['pagination'] ?? ( ( $settings['dots'] ?? 'yes' ) === 'yes' ? 'bullets' : 'none' ),
		];

		return apply_filters( 'apexadfo_nested_carousel_config', $config, $settings, $this );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides   = $settings['slides'] ?? [];
		$config   = $this->carousel_config( $settings );
		$count    = count( $slides );

		$this->add_render_attribute( 'root', [
			'class'                => [ 'eas-nested-carousel', 'eas-easing-' . sanitize_html_class( $config['easing'] ) ],
			'data-eas-carousel'    => wp_json_encode( $config ),
			'role'                 => 'region',
			'aria-roledescription' => 'carousel',
			'aria-label'           => $settings['carousel_name'] ?? esc_html__( 'Featured content', 'apex-addons-for-elementor' ),
		] );
		?>
		<div <?php $this->print_render_attribute_string( 'root' ); ?>>
			<div class="eas-carousel-viewport swiper">
				<div class="eas-slider-slides swiper-wrapper" aria-live="<?php echo $config['autoplay'] ? 'off' : 'polite'; ?>">
					<?php foreach ( $slides as $index => $slide ) :
						$key = $this->get_repeater_setting_key( 'slide', 'slides', $index );
						$this->add_render_attribute( $key, [
							'class'                => [ 'eas-slider-slide-item', 'swiper-slide' ],
							'role'                 => 'group',
							'aria-roledescription' => 'slide',
							/* translators: 1: Current slide number, 2: Total number of slides. */
							'aria-label'           => sprintf( esc_attr__( '%1$d of %2$d', 'apex-addons-for-elementor' ), $index + 1, $count ),
						] ); ?>
						<div <?php $this->print_render_attribute_string( $key ); ?>><?php $this->print_child( $index ); ?></div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( $config['arrows'] && $count > 1 ) : ?>
				<button class="eas-carousel-arrow eas-carousel-prev" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'apex-addons-for-elementor' ); ?>"><?php Icons_Manager::render_icon( $settings['previous_icon'] ?? [ 'value' => 'eicon-chevron-left', 'library' => 'eicons' ], [ 'aria-hidden' => 'true' ] ); ?></button>
				<button class="eas-carousel-arrow eas-carousel-next" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'apex-addons-for-elementor' ); ?>"><?php Icons_Manager::render_icon( $settings['next_icon'] ?? [ 'value' => 'eicon-chevron-right', 'library' => 'eicons' ], [ 'aria-hidden' => 'true' ] ); ?></button>
			<?php endif; ?>
			<?php if ( 'none' !== $config['pagination'] && $count > 1 ) : ?><div class="eas-carousel-pagination swiper-pagination" aria-hidden="true"></div><?php endif; ?>
		</div>
		<?php
	}

	protected function content_template_single_repeater_item() {
		?>
		<div class="eas-slider-slide-item swiper-slide" role="group" aria-roledescription="slide"></div>
		<?php
	}

	protected function content_template() {
		?>
		<#
		var slides = settings.slides || [],
			settingNumber = function( value, fallback ) {
				if ( value && typeof value === 'object' ) value = value.size;
				value = parseFloat( value );
				return isFinite( value ) ? value : fallback;
			},
			config = {
				version: 3,
				slidesPerView: parseFloat( settings.slides_per_view || 3 ),
				slidesPerViewTablet: parseFloat( settings.slides_per_view_tablet || 2 ),
				slidesPerViewMobile: parseFloat( settings.slides_per_view_mobile || 1 ),
				slidesToScroll: parseInt( settings.slides_to_scroll || 1 ),
				slidesToScrollTablet: parseInt( settings.slides_to_scroll_tablet || 1 ),
				slidesToScrollMobile: parseInt( settings.slides_to_scroll_mobile || 1 ),
				spaceBetween: settingNumber( settings.space_between, 20 ),
				spaceBetweenTablet: settingNumber( settings.space_between_tablet, 20 ),
				spaceBetweenMobile: settingNumber( settings.space_between_mobile, 16 ),
				speed: settingNumber( settings.speed, 650 ),
				easing: settings.easing || 'smooth',
				centeredSlides: settings.centered_slides === 'yes',
				equalHeight: ( settings.equal_height || 'yes' ) === 'yes',
				loop: settings.loop === 'yes', autoplay: settings.autoplay === 'yes',
				autoplayDelay: parseInt( settings.autoplay_delay || 3500 ),
				pauseOnHover: settings.pause_on_hover === 'yes', pauseOnInteraction: settings.pause_on_interaction === 'yes',
				keyboard: ( settings.keyboard || 'yes' ) === 'yes', arrows: ( settings.arrows || 'yes' ) === 'yes',
				pagination: settings.pagination || ( settings.dots === 'no' ? 'none' : 'bullets' )
			};
		view.addRenderAttribute( 'root', {
			'class': [ 'eas-nested-carousel', 'eas-easing-' + config.easing ],
			'data-eas-carousel': JSON.stringify( config ),
			'role': 'region', 'aria-roledescription': 'carousel', 'aria-label': settings.carousel_name || 'Featured content'
		} );
		#>
		<div {{{ view.getRenderAttributeString( 'root' ) }}}>
			<div class="eas-carousel-viewport swiper"><div class="eas-slider-slides swiper-wrapper">
				<# _.each( slides, function( slide, index ) { #><div class="eas-slider-slide-item swiper-slide" role="group" aria-roledescription="slide" aria-label="{{ index + 1 }} of {{ slides.length }}"></div><# } ); #>
			</div></div>
			<# if ( config.arrows && slides.length > 1 ) { #>
			<button class="eas-carousel-arrow eas-carousel-prev" type="button" aria-label="Previous slide"><i class="eicon-chevron-left" aria-hidden="true"></i></button>
			<button class="eas-carousel-arrow eas-carousel-next" type="button" aria-label="Next slide"><i class="eicon-chevron-right" aria-hidden="true"></i></button>
			<# } if ( config.pagination !== 'none' && slides.length > 1 ) { #><div class="eas-carousel-pagination swiper-pagination" aria-hidden="true"></div><# } #>
		</div>
		<?php
	}
}
