<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Portfolio Hover Showcase Widget.
 *
 * An ultra-premium interactive list layout featuring floating cursor-following image/video previews.
 */
class Portfolio_Showcase_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'eas-portfolio-showcase';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return esc_html__( 'Portfolio Hover Showcase', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
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
		return [ 'portfolio', 'list', 'hover', 'showcase', 'cursor', 'follow', 'image', 'video', 'agency', 'apex' ];
	}

	/**
	 * Get style dependencies.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-portfolio-showcase-css' ];
	}

	/**
	 * Get script dependencies.
	 */
	public function get_script_depends() {
		return [ 'apexadfo-portfolio-showcase-js' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ---------------------------------------------------------------------
		// Content Tab - Portfolio Items
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_items',
			[
				'label' => esc_html__( 'Portfolio Items', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'item_title',
			[
				'label'       => esc_html__( 'Project Title', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Project Name', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'item_category',
			[
				'label'       => esc_html__( 'Category / Subtitle', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Branding / UI Design', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'item_meta',
			[
				'label'       => esc_html__( 'Right Side Label (Meta)', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'View Case', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'item_link',
			[
				'label'       => esc_html__( 'Link URL', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'default'     => [
					'url' => '#',
				],
			]
		);

		$repeater->add_control(
			'media_type',
			[
				'label'   => esc_html__( 'Preview Media Type', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'image',
				'options' => [
					'image' => esc_html__( 'Image Upload', 'apex-addons-for-elementor' ),
					'video' => esc_html__( 'Self-Hosted Video', 'apex-addons-for-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'item_image',
			[
				'label'     => esc_html__( 'Hover Preview Image', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'condition' => [
					'media_type' => 'image',
				],
			]
		);

		$repeater->add_control(
			'item_video',
			[
				'label'       => esc_html__( 'Hover Preview Video URL (MP4)', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'https://domain.com/video.mp4', 'apex-addons-for-elementor' ),
				'condition'   => [
					'media_type' => 'video',
				],
				'label_block' => true,
			]
		);

		$this->add_control(
			'items_list',
			[
				'label'       => esc_html__( 'Showcase Items', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'item_title'    => esc_html__( 'Locomotive Studio', 'apex-addons-for-elementor' ),
						'item_category' => esc_html__( 'Art Direction & Development', 'apex-addons-for-elementor' ),
						'item_meta'     => esc_html__( '2026', 'apex-addons-for-elementor' ),
					],
					[
						'item_title'    => esc_html__( 'Minimalist Workspace', 'apex-addons-for-elementor' ),
						'item_category' => esc_html__( 'Interior Architecture Design', 'apex-addons-for-elementor' ),
						'item_meta'     => esc_html__( '2026', 'apex-addons-for-elementor' ),
					],
					[
						'item_title'    => esc_html__( 'Quantum Interactive', 'apex-addons-for-elementor' ),
						'item_category' => esc_html__( 'Web3 Fluid Frontend Development', 'apex-addons-for-elementor' ),
						'item_meta'     => esc_html__( '2025', 'apex-addons-for-elementor' ),
					],
				],
				'title_field' => '{{{ item_title }}}',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Interactive Settings
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_interactive',
			[
				'label' => esc_html__( 'Interactive Settings', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'damping',
			[
				'label'      => esc_html__( 'Cursor Follow Speed (Damping)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0.01,
						'max'  => 0.40,
						'step' => 0.01,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 0.1,
				],
				'description'=> esc_html__( 'Lower values create a more organic, delayed trailing lag behind the cursor. Higher values follow faster.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'tilt_sensitivity',
			[
				'label'      => esc_html__( 'Rotation Tilt Sensitivity', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0.01,
						'max'  => 0.20,
						'step' => 0.01,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 0.05,
				],
				'description'=> esc_html__( 'Controls how much the floating card rotates/tilts horizontally when you move your mouse quickly.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'max_tilt',
			[
				'label'      => esc_html__( 'Maximum Rotation Angle', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 45,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 15,
				],
			]
		);

		$this->add_control(
			'hover_style',
			[
				'label'   => esc_html__( 'Title Hover Style', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => [
					'solid'  => esc_html__( 'Solid Color Shift', 'apex-addons-for-elementor' ),
					'stroke' => esc_html__( 'Text Outline Stroke', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'disable_mobile',
			[
				'label'        => esc_html__( 'Disable Preview on Mobile', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'Hides the floating follower on mobile viewports since hover events do not exist on touchscreens.', 'apex-addons-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - List Rows
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_list',
			[
				'label' => esc_html__( 'List Rows', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'row_padding',
			[
				'label'      => esc_html__( 'Row Vertical Padding', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-portfolio-showcase-item' => 'padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label'     => esc_html__( 'Divider Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.1)',
				'selectors' => [
					'{{WRAPPER}} .eas-portfolio-showcase-item' => 'border-bottom-color: {{VALUE}};',
					'{{WRAPPER}} .eas-portfolio-showcase-item:first-child' => 'border-top-color: {{VALUE}};',
				],
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

		// Project Title Controls
		$this->add_control(
			'heading_title',
			[
				'label'     => esc_html__( 'Project Title', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#1e1e1e',
				'selectors' => [
					'{{WRAPPER}} .eas-portfolio-showcase-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_hover_color',
			[
				'label'     => esc_html__( 'Title Hover Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#a855f7',
				'selectors' => [
					'{{WRAPPER}} .eas-portfolio-showcase-item:hover .eas-portfolio-showcase-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .eas-portfolio-showcase-title',
			]
		);

		// Category Controls
		$this->add_control(
			'heading_category',
			[
				'label'     => esc_html__( 'Category / Subtitle', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'category_color',
			[
				'label'     => esc_html__( 'Category Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#666666',
				'selectors' => [
					'{{WRAPPER}} .eas-portfolio-showcase-category' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'category_hover_color',
			[
				'label'     => esc_html__( 'Category Hover Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-portfolio-showcase-item:hover .eas-portfolio-showcase-category' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'category_typography',
				'selector' => '{{WRAPPER}} .eas-portfolio-showcase-category',
			]
		);

		// Meta Labels Controls
		$this->add_control(
			'heading_meta',
			[
				'label'     => esc_html__( 'Right Side Label (Meta)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => esc_html__( 'Meta Label Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#999999',
				'selectors' => [
					'{{WRAPPER}} .eas-portfolio-showcase-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_hover_color',
			[
				'label'     => esc_html__( 'Meta Label Hover Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-portfolio-showcase-item:hover .eas-portfolio-showcase-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .eas-portfolio-showcase-meta',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Floating Hover Preview
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_preview',
			[
				'label' => esc_html__( 'Floating Preview Card', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'preview_w',
			[
				'label'      => esc_html__( 'Preview Width (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 100,
						'max'  => 800,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 320,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-portfolio-showcase-preview-container' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'preview_h',
			[
				'label'      => esc_html__( 'Preview Height (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 80,
						'max'  => 600,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 200,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-portfolio-showcase-preview-container' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'preview_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default'    => [
					'top'    => '12',
					'right'  => '12',
					'bottom' => '12',
					'left'   => '12',
					'unit'   => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-portfolio-showcase-preview-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'preview_border',
				'selector' => '{{WRAPPER}} .eas-portfolio-showcase-preview-container',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'preview_shadow',
				'selector' => '{{WRAPPER}} .eas-portfolio-showcase-preview-container',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$list_items = $settings['items_list'] ?? [];

		if ( empty( $list_items ) ) {
			return;
		}

		$damping = $settings['damping']['size'] ?? 0.1;
		$tilt_sensitivity = $settings['tilt_sensitivity']['size'] ?? 0.05;
		$max_tilt = $settings['max_tilt']['size'] ?? 15;
		$hover_style = $settings['hover_style'] ?? 'solid';
		$disable_mobile = $settings['disable_mobile'] === 'yes';

		$preview_w = $settings['preview_w']['size'] ?? 320;
		$preview_h = $settings['preview_h']['size'] ?? 200;

		// Build classes
		$wrap_classes = [ 'eas-portfolio-showcase-wrap' ];
		if ( $disable_mobile ) {
			$wrap_classes[] = 'eas-disable-mobile-hover';
		}

		// Gather settings variables to pass to data attributes
		$widget_id = $this->get_id();
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>"
			data-id="<?php echo esc_attr( $widget_id ); ?>"
			data-damping="<?php echo esc_attr( $damping ); ?>"
			data-tilt-sensitivity="<?php echo esc_attr( $tilt_sensitivity ); ?>"
			data-max-tilt="<?php echo esc_attr( $max_tilt ); ?>"
			data-preview-width="<?php echo esc_attr( $preview_w ); ?>"
			data-preview-height="<?php echo esc_attr( $preview_h ); ?>"
			data-hover-style="<?php echo esc_attr( $hover_style ); ?>">

			<ul class="eas-portfolio-showcase-list">
				<?php foreach ( $list_items as $index => $item ) : 
					$title = $item['item_title'] ?? '';
					$category = $item['item_category'] ?? '';
					$meta = $item['item_meta'] ?? '';
					
					$link = $item['item_link'] ?? [];
					$link_url = ! empty( $link['url'] ) ? esc_url( $link['url'] ) : '#';
					$link_target = ! empty( $link['is_external'] ) ? ' target="_blank"' : '';
					$link_nofollow = ! empty( $link['nofollow'] ) ? ' rel="nofollow"' : '';
					?>
					<li>
						<a href="<?php echo esc_url( $link_url ); ?>" 
							class="eas-portfolio-showcase-item" 
							data-index="<?php echo esc_attr( $index ); ?>"
							<?php if ( $link_target ) : ?> target="_blank"<?php endif; ?><?php if ( $link_nofollow ) : ?> rel="nofollow"<?php endif; ?>>
							
							<div class="eas-portfolio-showcase-content-left">
								<h2 class="eas-portfolio-showcase-title"><?php echo esc_html( $title ); ?></h2>
								<?php if ( ! empty( $category ) ) : ?>
									<span class="eas-portfolio-showcase-category"><?php echo esc_html( $category ); ?></span>
								<?php endif; ?>
							</div>

							<?php if ( ! empty( $meta ) ) : ?>
								<span class="eas-portfolio-showcase-meta"><?php echo esc_html( $meta ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<!-- Floating Preview Follower Container -->
			<div class="eas-portfolio-showcase-preview-container">
				<div class="eas-portfolio-showcase-media-wrap">
					<?php foreach ( $list_items as $index => $item ) : 
						$media_type = $item['media_type'] ?? 'image';
						?>
						<div class="eas-portfolio-showcase-media-item" data-index="<?php echo esc_attr( $index ); ?>">
							<?php if ( 'image' === $media_type && ! empty( $item['item_image']['url'] ) ) : ?>
								<img src="<?php echo esc_url( $item['item_image']['url'] ); ?>" alt="<?php echo esc_attr( $item['item_title'] ?? '' ); ?>" loading="lazy" />
							<?php elseif ( 'video' === $media_type && ! empty( $item['item_video'] ) ) : ?>
								<video src="<?php echo esc_url( $item['item_video'] ); ?>" muted loop playsinline autoplay></video>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

		</div>
		<?php
	}
}
