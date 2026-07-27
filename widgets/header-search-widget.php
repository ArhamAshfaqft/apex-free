<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Header_Search_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-header-search';
	}

	public function get_title() {
		return esc_html__( 'Header Search', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-header-search-js' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-header-search-css', 'elementor-icons-fa-solid' ];
	}

	protected function register_controls() {
		// --- CONTENT SECTION ---
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Search Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout Style', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'expandable',
				'options' => [
					'expandable' => esc_html__( 'Expandable Input', 'apex-addons-for-elementor' ),
					'overlay'    => esc_html__( 'Fullscreen Overlay', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'placeholder',
			[
				'label'   => esc_html__( 'Placeholder Text', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Search...', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'search_icon',
			[
				'label'   => esc_html__( 'Search Icon', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-search',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'close_icon',
			[
				'label'     => esc_html__( 'Close Icon', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-times',
					'library' => 'fa-solid',
				],
				'condition' => [
					'layout' => 'overlay',
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
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
				'default'   => 'right',
				'selectors' => [
					'{{WRAPPER}} .eas-header-search-wrap' => 'text-align: {{VALUE}}; display: flex; justify-content: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE: SEARCH ICON/BUTTON ---
		$this->start_controls_section(
			'section_style_trigger',
			[
				'label' => esc_html__( 'Search Trigger Button', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'trigger_size',
			[
				'label'     => esc_html__( 'Button Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 40,
					'unit' => 'px',
				],
				'range'     => [
					'px' => [ 'min' => 20, 'max' => 100 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-search-trigger' => 'width: {{SIZE}}px; height: {{SIZE}}px; line-height: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'trigger_icon_size',
			[
				'label'     => esc_html__( 'Icon Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 20,
					'unit' => 'px',
				],
				'range'     => [
					'px' => [ 'min' => 10, 'max' => 55 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-search-trigger i' => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .eas-search-trigger svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_trigger_style' );

		$this->start_controls_tab(
			'tab_trigger_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'trigger_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-trigger' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-search-trigger svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'trigger_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-trigger' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_trigger_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'trigger_color_hover',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-trigger:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-search-trigger:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'trigger_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-trigger:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'trigger_border',
				'selector'  => '{{WRAPPER}} .eas-search-trigger',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'trigger_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-search-trigger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE: EXPANDABLE INPUT ---
		$this->start_controls_section(
			'section_style_input',
			[
				'label'     => esc_html__( 'Expandable Input Style', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'expandable',
				],
			]
		);

		$this->add_responsive_control(
			'input_width',
			[
				'label'      => esc_html__( 'Expanded Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'vw' ],
				'range'      => [
					'px' => [ 'min' => 100, 'max' => 500 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-search-expandable.eas-active input.search-field' => 'width: {{SIZE}}{{UNIT}}; padding-left: 15px; padding-right: 45px;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'input_typography',
				'selector' => '{{WRAPPER}} .eas-search-expandable input.search-field',
			]
		);

		$this->add_control(
			'input_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-expandable input.search-field' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-expandable input.search-field' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'input_placeholder_color',
			[
				'label'     => esc_html__( 'Placeholder Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-expandable input.search-field::placeholder' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'input_border',
				'selector' => '{{WRAPPER}} .eas-search-expandable input.search-field',
			]
		);

		$this->add_responsive_control(
			'input_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-search-expandable input.search-field' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// --- STYLE: FULLSCREEN OVERLAY ---
		$this->start_controls_section(
			'section_style_overlay',
			[
				'label'     => esc_html__( 'Fullscreen Overlay Style', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'overlay',
				],
			]
		);

		$this->add_control(
			'overlay_bg',
			[
				'label'     => esc_html__( 'Overlay Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-overlay-container' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'overlay_blur',
			[
				'label'     => esc_html__( 'Backdrop Blur (px)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-search-overlay-container' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);',
				],
			]
		);

		$this->add_control(
			'overlay_close_color',
			[
				'label'     => esc_html__( 'Close Button Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-overlay-close' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-search-overlay-close svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'overlay_close_color_hover',
			[
				'label'     => esc_html__( 'Close Button Hover Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-overlay-close:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-search-overlay-close:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'overlay_input_typography',
				'selector' => '{{WRAPPER}} .eas-search-overlay-container input.search-field',
			]
		);

		$this->add_control(
			'overlay_input_color',
			[
				'label'     => esc_html__( 'Input Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-overlay-container input.search-field' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'overlay_input_line_color',
			[
				'label'     => esc_html__( 'Input Underline Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-search-overlay-container input.search-field' => 'border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$placeholder = ! empty( $settings['placeholder'] ) ? $settings['placeholder'] : esc_html__( 'Search...', 'apex-addons-for-elementor' );
		$unique_id = 'eas-search-' . $this->get_id();

		echo '<div class="eas-header-search-wrap">';

		if ( 'expandable' === $settings['layout'] ) {
			?>
			<div class="eas-search-expandable" id="<?php echo esc_attr( $unique_id ); ?>">
				<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label>
						<span class="screen-reader-text"><?php echo esc_html__( 'Search for:', 'apex-addons-for-elementor' ); ?></span>
						<input type="search" class="search-field" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
					</label>
					<button type="button" class="eas-search-trigger" aria-label="Open Search">
						<?php if ( ! empty( $settings['search_icon']['value'] ) ) : ?>
							<?php \Elementor\Icons_Manager::render_icon( $settings['search_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						<?php else: ?>
							<i class="fas fa-search" aria-hidden="true"></i>
						<?php endif; ?>
					</button>
				</form>
			</div>
			<?php
		} else {
			?>
			<button type="button" class="eas-search-trigger" id="<?php echo esc_attr( $unique_id ); ?>-trigger" aria-label="Open Search Overlay">
				<?php if ( ! empty( $settings['search_icon']['value'] ) ) : ?>
					<?php \Elementor\Icons_Manager::render_icon( $settings['search_icon'], [ 'aria-hidden' => 'true' ] ); ?>
				<?php else: ?>
					<i class="fas fa-search" aria-hidden="true"></i>
				<?php endif; ?>
			</button>

			<div class="eas-search-overlay-container" id="<?php echo esc_attr( $unique_id ); ?>-overlay" role="dialog" aria-modal="true" aria-hidden="true">
				<button class="eas-search-overlay-close" aria-label="Close Search">
					<?php if ( ! empty( $settings['close_icon']['value'] ) ) : ?>
						<?php \Elementor\Icons_Manager::render_icon( $settings['close_icon'], [ 'aria-hidden' => 'true' ] ); ?>
					<?php else: ?>
						<i class="fas fa-times" aria-hidden="true"></i>
					<?php endif; ?>
				</button>
				<div class="eas-search-overlay-content">
					<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="search" class="search-field" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
						<button type="submit" class="search-submit-btn" aria-label="Search">
							<?php if ( ! empty( $settings['search_icon']['value'] ) ) : ?>
								<?php \Elementor\Icons_Manager::render_icon( $settings['search_icon'], [ 'aria-hidden' => 'true' ] ); ?>
							<?php else: ?>
								<i class="fas fa-search" aria-hidden="true"></i>
							<?php endif; ?>
						</button>
					</form>
				</div>
			</div>
			<?php
		}

		echo '</div>';
	}
}
