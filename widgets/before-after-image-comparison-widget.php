<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Before/After Image Comparison Widget
 */
class Before_After_Image_Comparison_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-before-after-image-comparison';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Before/After Image Comparison', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-image-before-after';
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
		return [ 'apexadfo-before-after-image-comparison-js' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @return array Style handles.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-before-after-image-comparison-css' ];
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB - IMAGES
		// ==========================================

		$this->start_controls_section(
			'section_images',
			[
				'label' => esc_html__( 'Images', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'before_image',
			[
				'label'   => esc_html__( 'Before Image', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'after_image',
			[
				'label'   => esc_html__( 'After Image', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - LABELS & SETTINGS
		// ==========================================

		$this->start_controls_section(
			'section_settings',
			[
				'label' => esc_html__( 'Labels & Options', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_labels',
			[
				'label'        => esc_html__( 'Show Labels', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'before_label',
			[
				'label'       => esc_html__( 'Before Label Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Before', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'e.g. Before', 'apex-addons-for-elementor' ),
				'condition'   => [
					'show_labels' => 'yes',
				],
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'after_label',
			[
				'label'       => esc_html__( 'After Label Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'After', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'e.g. After', 'apex-addons-for-elementor' ),
				'condition'   => [
					'show_labels' => 'yes',
				],
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'label_position',
			[
				'label'     => esc_html__( 'Labels Position', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'top',
				'options'   => [
					'top'          => esc_html__( 'Top', 'apex-addons-for-elementor' ),
					'bottom-both'  => esc_html__( 'Bottom', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'show_labels' => 'yes',
				],
			]
		);

		$this->add_control(
			'orientation',
			[
				'label'   => esc_html__( 'Comparison Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => esc_html__( 'Horizontal (Left/Right)', 'apex-addons-for-elementor' ),
					'vertical'   => esc_html__( 'Vertical (Top/Bottom)', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'starting_position',
			[
				'label'      => esc_html__( 'Starting Position (%)', 'apex-addons-for-elementor' ),
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
					'size' => 50,
				],
			]
		);

		$this->add_control(
			'move_on_hover',
			[
				'label'        => esc_html__( 'Move Handle on Hover', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - CONTAINER & IMAGES
		// ==========================================

		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container & Images', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_alignment',
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
					'{{WRAPPER}} .apexadfo-before-after-wrapper' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-before-after-container' => 'margin-left: {{VALUE}} == "center" ? "auto" : ({{VALUE}} == "right" ? "auto" : "0"); margin-right: {{VALUE}} == "center" ? "auto" : ({{VALUE}} == "right" ? "0" : "auto");',
				],
			]
		);

		$this->add_responsive_control(
			'container_width',
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
					'{{WRAPPER}} .apexadfo-before-after-container' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_max_width',
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
					'{{WRAPPER}} .apexadfo-before-after-container' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_height',
			[
				'label'      => esc_html__( 'Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [
						'min'  => 150,
						'max'  => 1000,
						'step' => 10,
					],
					'vh' => [
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 450,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-before-after-container' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'image_fit',
			[
				'label'     => esc_html__( 'Image Fit Mode', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => [
					'cover'   => esc_html__( 'Cover', 'apex-addons-for-elementor' ),
					'contain' => esc_html__( 'Contain', 'apex-addons-for-elementor' ),
					'fill'    => esc_html__( 'Fill', 'apex-addons-for-elementor' ),
				],
				'selectors' => [
					'{{WRAPPER}} .apexadfo-before-after-img' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_container_style' );

		$this->start_controls_tab(
			'tab_container_normal',
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
					'{{WRAPPER}} .apexadfo-before-after-img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters',
				'label'    => esc_html__( 'CSS Filters', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-before-after-img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_container_hover',
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
					'{{WRAPPER}} .apexadfo-before-after-container:hover .apexadfo-before-after-img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'image_css_filters_hover',
				'label'    => esc_html__( 'CSS Filters', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-before-after-container:hover .apexadfo-before-after-img',
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
					'{{WRAPPER}} .apexadfo-before-after-img' => 'transition: opacity {{SIZE}}{{UNIT}}, filter {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'label'    => esc_html__( 'Border', 'apex-addons-for-elementor' ),
				'separator' => 'before',
				'selector' => '{{WRAPPER}} .apexadfo-before-after-container',
			]
		);

		$this->add_responsive_control(
			'container_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-before-after-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-before-after-container',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - DIVIDER LINE & HANDLE
		// ==========================================

		$this->start_controls_section(
			'section_style_handle',
			[
				'label' => esc_html__( 'Divider & Handle', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'line_color',
			[
				'label'     => esc_html__( 'Divider Line Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-before-after-line' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'line_thickness',
			[
				'label'      => esc_html__( 'Divider Thickness', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors'  => [
					'{{WRAPPER}}.apexadfo-orientation-horizontal .apexadfo-before-after-line' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.apexadfo-orientation-vertical .apexadfo-before-after-line'   => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'handle_size',
			[
				'label'      => esc_html__( 'Handle Button Size', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 24,
						'max'  => 80,
						'step' => 2,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 44,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-before-after-handle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'handle_bg_color',
			[
				'label'     => esc_html__( 'Handle Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-before-after-handle' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'handle_icon_color',
			[
				'label'     => esc_html__( 'Handle Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-before-after-handle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'handle_border',
				'label'    => esc_html__( 'Handle Border', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-before-after-handle',
			]
		);

		$this->add_responsive_control(
			'handle_border_radius',
			[
				'label'      => esc_html__( 'Handle Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '50',
					'right'    => '50',
					'bottom'   => '50',
					'left'     => '50',
					'unit'     => '%',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-before-after-handle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'handle_box_shadow',
				'label'    => esc_html__( 'Handle Box Shadow', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-before-after-handle',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - LABELS
		// ==========================================

		$this->start_controls_section(
			'section_style_labels',
			[
				'label'     => esc_html__( 'Labels Style', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_labels' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'labels_typography',
				'label'    => esc_html__( 'Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-before-after-label',
			]
		);

		$this->add_control(
			'labels_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-before-after-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'labels_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0, 0, 0, 0.65)',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-before-after-label' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'labels_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '6',
					'right'    => '14',
					'bottom'   => '6',
					'left'     => '14',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-before-after-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'labels_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '4',
					'right'    => '4',
					'bottom'   => '4',
					'left'     => '4',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-before-after-label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$before_url = ! empty( $settings['before_image']['url'] ) ? $settings['before_image']['url'] : Utils::get_placeholder_image_src();
		$after_url  = ! empty( $settings['after_image']['url'] ) ? $settings['after_image']['url'] : Utils::get_placeholder_image_src();

		$before_alt = ! empty( $settings['before_image']['alt'] ) ? $settings['before_image']['alt'] : esc_html__( 'Before Image', 'apex-addons-for-elementor' );
		$after_alt  = ! empty( $settings['after_image']['alt'] ) ? $settings['after_image']['alt'] : esc_html__( 'After Image', 'apex-addons-for-elementor' );

		$orientation  = ! empty( $settings['orientation'] ) ? $settings['orientation'] : 'horizontal';
		$starting_pos = isset( $settings['starting_position']['size'] ) ? floatval( $settings['starting_position']['size'] ) : 50;
		$hover_move   = ( isset( $settings['move_on_hover'] ) && 'yes' === $settings['move_on_hover'] ) ? 'yes' : 'no';

		$label_pos_class = ( ! empty( $settings['label_position'] ) && 'bottom-both' === $settings['label_position'] ) ? 'apexadfo-label-pos-bottom-both' : 'apexadfo-label-pos-top';

		$this->add_render_attribute(
			'container',
			[
				'class'             => [
					'apexadfo-before-after-container',
					'apexadfo-orientation-' . esc_attr( $orientation ),
					$label_pos_class,
				],
				'data-orientation'  => esc_attr( $orientation ),
				'data-starting-pos' => esc_attr( $starting_pos ),
				'data-hover'        => esc_attr( $hover_move ),
			]
		);

		$this->add_render_attribute(
			'handle',
			[
				'class'         => 'apexadfo-before-after-handle',
				'role'          => 'slider',
				'tabindex'      => '0',
				'aria-valuenow' => esc_attr( round( $starting_pos ) ),
				'aria-valuemin' => '0',
				'aria-valuemax' => '100',
				'aria-label'    => esc_attr__( 'Before and after comparison slider', 'apex-addons-for-elementor' ),
			]
		);
		?>
		<div class="apexadfo-before-after-wrapper">
			<div <?php $this->print_render_attribute_string( 'container' ); ?>>
				
				<!-- After Image Layer -->
				<div class="apexadfo-before-after-after-wrap">
					<img src="<?php echo esc_url( $after_url ); ?>" alt="<?php echo esc_attr( $after_alt ); ?>" class="apexadfo-before-after-img" />
					<?php if ( 'yes' === $settings['show_labels'] && ! empty( $settings['after_label'] ) ) : ?>
						<span class="apexadfo-before-after-label apexadfo-before-after-label-after"><?php echo esc_html( $settings['after_label'] ); ?></span>
					<?php endif; ?>
				</div>

				<!-- Before Image Layer (Clipped) -->
				<div class="apexadfo-before-after-before-wrap" style="<?php echo 'horizontal' === $orientation ? 'width: ' . esc_attr( $starting_pos ) . '%;' : 'height: ' . esc_attr( $starting_pos ) . '%;'; ?>">
					<img src="<?php echo esc_url( $before_url ); ?>" alt="<?php echo esc_attr( $before_alt ); ?>" class="apexadfo-before-after-img" />
					<?php if ( 'yes' === $settings['show_labels'] && ! empty( $settings['before_label'] ) ) : ?>
						<span class="apexadfo-before-after-label apexadfo-before-after-label-before"><?php echo esc_html( $settings['before_label'] ); ?></span>
					<?php endif; ?>
				</div>

				<!-- Divider Line -->
				<div class="apexadfo-before-after-line" style="<?php echo 'horizontal' === $orientation ? 'left: ' . esc_attr( $starting_pos ) . '%;' : 'top: ' . esc_attr( $starting_pos ) . '%;'; ?>"></div>

				<!-- Handle Button -->
				<div <?php $this->print_render_attribute_string( 'handle' ); ?> style="<?php echo 'horizontal' === $orientation ? 'left: ' . esc_attr( $starting_pos ) . '%;' : 'top: ' . esc_attr( $starting_pos ) . '%;'; ?>">
					<div class="apexadfo-before-after-handle-arrows">
						<?php if ( 'vertical' === $orientation ) : ?>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4l-6 6h12l-6-6zm0 16l6-6H6l6 6z"/></svg>
						<?php else : ?>
							<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12l6-6v12l-6-6zm16 0l-6 6V6l6 6z"/></svg>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
}
