<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Flex Accordion Widget.
 *
 * An interactive expandable card grid that expands fluidly on hover or click.
 */
class Flex_Accordion_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'eas-flex-accordion';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return esc_html__( 'Flex Accordion', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-columns';
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
		return [ 'accordion', 'flex', 'expand', 'grid', 'columns', 'services', 'card', 'apex' ];
	}

	/**
	 * Get style depends.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-flex-accordion-css' ];
	}

	/**
	 * Get script depends.
	 */
	public function get_script_depends() {
		return [ 'apexadfo-flex-accordion-js' ];
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ---------------------------------------------------------------------
		// Content Tab - Accordion Items
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_items',
			[
				'label' => esc_html__( 'Accordion Items', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'item_title',
			[
				'label'       => esc_html__( 'Title', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Creative Concept', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'item_description',
			[
				'label'       => esc_html__( 'Description', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => esc_html__( 'We build modern interactive brand identities and layout frameworks.', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'item_meta',
			[
				'label'       => esc_html__( 'Number / Subtitle', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( '01', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'item_image',
			[
				'label'   => esc_html__( 'Background Image', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'item_btn_text',
			[
				'label'   => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Explore More', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'item_btn_link',
			[
				'label'       => esc_html__( 'Button Link', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'default'     => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'items_list',
			[
				'label'       => esc_html__( 'Accordion Cards', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'item_title'    => esc_html__( 'Brand Strategy', 'apex-addons-for-elementor' ),
						'item_description'=> esc_html__( 'Defining unique corporate positions and market-ready directions.', 'apex-addons-for-elementor' ),
						'item_meta'     => esc_html__( '01', 'apex-addons-for-elementor' ),
					],
					[
						'item_title'    => esc_html__( 'UX Research', 'apex-addons-for-elementor' ),
						'item_description'=> esc_html__( 'Analyzing customer behaviors to build solid wireframe maps.', 'apex-addons-for-elementor' ),
						'item_meta'     => esc_html__( '02', 'apex-addons-for-elementor' ),
					],
					[
						'item_title'    => esc_html__( 'Development', 'apex-addons-for-elementor' ),
						'item_description'=> esc_html__( 'Writing highly optimized, zero-reflow layout code.', 'apex-addons-for-elementor' ),
						'item_meta'     => esc_html__( '03', 'apex-addons-for-elementor' ),
					],
				],
				'title_field' => '{{{ item_title }}}',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Layout Config
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout Settings', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'orientation',
			[
				'label'   => esc_html__( 'Orientation', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => esc_html__( 'Horizontal Columns', 'apex-addons-for-elementor' ),
					'vertical'   => esc_html__( 'Vertical Rows', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'trigger_mode',
			[
				'label'   => esc_html__( 'Trigger Action', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'hover',
				'options' => [
					'hover' => esc_html__( 'Hover Expansion', 'apex-addons-for-elementor' ),
					'click' => esc_html__( 'Click Toggle', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'active_index',
			[
				'label'   => esc_html__( 'Default Active Index', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 10,
				'step'    => 1,
				'default' => 1,
			]
		);

		$this->add_responsive_control(
			'accordion_height',
			[
				'label'      => esc_html__( 'Height', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'range'      => [
					'px' => [
						'min'  => 200,
						'max'  => 1000,
						'step' => 10,
					],
					'vh' => [
						'min'  => 20,
						'max'  => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 500,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-flex-accordion' => '--eas-fa-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'accordion_gap',
			[
				'label'      => esc_html__( 'Cards Gap', 'apex-addons-for-elementor' ),
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
					'size' => 16,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-flex-accordion' => '--eas-fa-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Card Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Accordion Cards', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_radius',
			[
				'label'      => esc_html__( 'Border Radius (px)', 'apex-addons-for-elementor' ),
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
					'size' => 16,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-flex-accordion' => '--eas-fa-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => esc_html__( 'Inner Padding', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-flex-accordion-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .eas-flex-accordion-item',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Typography & Colors
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__( 'Typography & Colors', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		// Number / Meta Label
		$this->add_control(
			'heading_meta',
			[
				'label'     => esc_html__( 'Number / Subtitle', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-flex-accordion-number' => 'color: {{VALUE}};',
					'{{WRAPPER}} .eas-flex-accordion-icon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .eas-flex-accordion-number',
			]
		);

		// Card Title
		$this->add_control(
			'heading_title',
			[
				'label'     => esc_html__( 'Card Title', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-flex-accordion-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .eas-flex-accordion-title',
			]
		);

		// Card Description
		$this->add_control(
			'heading_desc',
			[
				'label'     => esc_html__( 'Card Description', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.8)',
				'selectors' => [
					'{{WRAPPER}} .eas-flex-accordion-desc' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .eas-flex-accordion-desc',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Call to Action Button
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Call to Action Button', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'btn_typography',
				'selector' => '{{WRAPPER}} .eas-flex-accordion-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'btn_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1e1e1e',
				'selectors' => [
					'{{WRAPPER}} .eas-flex-accordion-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-flex-accordion-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'btn_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-flex-accordion-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_hover_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#a855f7',
				'selectors' => [
					'{{WRAPPER}} .eas-flex-accordion-btn:hover' => 'background-color: {{VALUE}};',
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
		$list = $settings['items_list'] ?? [];

		if ( empty( $list ) ) {
			return;
		}

		$orientation = $settings['orientation'] ?? 'horizontal';
		$trigger_mode = $settings['trigger_mode'] ?? 'hover';
		$active_index = intval( $settings['active_index'] ?? 1 ) - 1; // Convert 1-indexed to 0-indexed

		$accordion_classes = [
			'eas-flex-accordion',
			'eas-flex-accordion-' . $orientation,
		];
		?>
		<ul class="<?php echo esc_attr( implode( ' ', $accordion_classes ) ); ?>"
			data-trigger-mode="<?php echo esc_attr( $trigger_mode ); ?>">
			
			<?php foreach ( $list as $index => $item ) : 
				$title = $item['item_title'] ?? '';
				$desc = $item['item_description'] ?? '';
				$meta = $item['item_meta'] ?? '';
				$img_url = ! empty( $item['item_image']['url'] ) ? esc_url( $item['item_image']['url'] ) : '';
				
				$btn_text = $item['item_btn_text'] ?? '';
				$btn_link = $item['item_btn_link'] ?? [];
				$btn_url = ! empty( $btn_link['url'] ) ? esc_url( $btn_link['url'] ) : '#';
				$btn_target = ! empty( $btn_link['is_external'] ) ? ' target="_blank"' : '';
				$btn_nofollow = ! empty( $btn_link['nofollow'] ) ? ' rel="nofollow"' : '';

				// Set default active class on chosen load index
				$item_classes = [ 'eas-flex-accordion-item' ];
				if ( $index === $active_index ) {
					$item_classes[] = 'eas-active';
				}

				// Apply inline background images safely
				$bg_style = ! empty( $img_url ) ? 'background-image: url(' . $img_url . ');' : '';
				?>
				<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>" 
					style="<?php echo esc_attr( $bg_style ); ?>">
					
					<div class="eas-flex-accordion-overlay"></div>

					<div class="eas-flex-accordion-content">
						<div class="eas-flex-accordion-meta-wrap">
							<?php if ( ! empty( $meta ) ) : ?>
								<span class="eas-flex-accordion-number"><?php echo esc_html( $meta ); ?></span>
							<?php endif; ?>
						</div>

						<h3 class="eas-flex-accordion-title"><?php echo esc_html( $title ); ?></h3>

						<div class="eas-flex-accordion-details">
							<?php if ( ! empty( $desc ) ) : ?>
								<p class="eas-flex-accordion-desc"><?php echo esc_html( $desc ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $btn_text ) ) : ?>
								<a href="<?php echo esc_url( $btn_url ); ?>" 
									class="eas-flex-accordion-btn"
									<?php if ( $btn_target ) : ?> target="_blank"<?php endif; ?><?php if ( $btn_nofollow ) : ?> rel="nofollow"<?php endif; ?>>
									<?php echo esc_html( $btn_text ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>

				</li>
			<?php endforeach; ?>

		</ul>
		<?php
	}
}
