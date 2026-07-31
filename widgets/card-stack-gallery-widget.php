<?php
/**
 * 3D Stacked Card Image Gallery Widget
 * Apex Addons for Elementor
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Card_Stack_Gallery_Widget extends Widget_Base {

	public function get_name() {
		return 'apexadfo-card-stack-gallery';
	}

	public function get_title() {
		return esc_html__( '3D Card Stack Gallery', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-cards-stack apex-widget-icon';
	}

	public function get_categories() {
		return [ 'apex-addons-category' ];
	}

	public function get_keywords() {
		return [ 'stack', 'card', '3d', 'gallery', 'slider', 'image', 'carousel', 'fan', 'apex' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-card-stack-gallery-css' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-card-stack-gallery-js' ];
	}

	protected function register_controls() {

		// ==================== CONTENT TAB ====================

		$this->start_controls_section( 'section_cards', [
			'label' => esc_html__( 'Gallery Cards', 'apex-addons-for-elementor' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$repeater = new Repeater();

		$repeater->add_control( 'image', [
			'label'   => esc_html__( 'Image', 'apex-addons-for-elementor' ),
			'type'    => Controls_Manager::MEDIA,
			'default' => [
				'url' => Utils::get_placeholder_image_src(),
			],
		] );

		$repeater->add_control( 'eyebrow', [
			'label'       => esc_html__( 'Eyebrow / Category', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Design', 'apex-addons-for-elementor' ),
			'label_block' => true,
		] );

		$repeater->add_control( 'title', [
			'label'       => esc_html__( 'Title', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'Card Title', 'apex-addons-for-elementor' ),
			'label_block' => true,
		] );

		$repeater->add_control( 'description', [
			'label'       => esc_html__( 'Description', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::TEXTAREA,
			'rows'        => 3,
			'default'     => esc_html__( 'High-quality interactive visual design for modern websites.', 'apex-addons-for-elementor' ),
		] );

		$repeater->add_control( 'button_text', [
			'label'       => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => esc_html__( 'View Project', 'apex-addons-for-elementor' ),
			'label_block' => true,
		] );

		$repeater->add_control( 'link', [
			'label'       => esc_html__( 'Link URL', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::URL,
			'placeholder' => 'https://your-link.com',
		] );

		$this->add_control( 'cards', [
			'label'       => esc_html__( 'Stack Items', 'apex-addons-for-elementor' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[
					'eyebrow'     => esc_html__( 'Architecture', 'apex-addons-for-elementor' ),
					'title'       => esc_html__( 'Modern Structure', 'apex-addons-for-elementor' ),
					'description' => esc_html__( 'Clean geometric forms with sustainable materials.', 'apex-addons-for-elementor' ),
					'button_text' => esc_html__( 'Explore Project', 'apex-addons-for-elementor' ),
					'image'       => [ 'url' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=800&q=80' ],
				],
				[
					'eyebrow'     => esc_html__( 'Branding', 'apex-addons-for-elementor' ),
					'title'       => esc_html__( 'Creative Identity', 'apex-addons-for-elementor' ),
					'description' => esc_html__( 'Bold visual systems crafted for modern digital brands.', 'apex-addons-for-elementor' ),
					'button_text' => esc_html__( 'View Case Study', 'apex-addons-for-elementor' ),
					'image'       => [ 'url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80' ],
				],
				[
					'eyebrow'     => esc_html__( 'UI/UX Design', 'apex-addons-for-elementor' ),
					'title'       => esc_html__( 'Fluid Workspace', 'apex-addons-for-elementor' ),
					'description' => esc_html__( 'Intuitive interface experience engineered for speed.', 'apex-addons-for-elementor' ),
					'button_text' => esc_html__( 'Discover More', 'apex-addons-for-elementor' ),
					'image'       => [ 'url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80' ],
				],
				[
					'eyebrow'     => esc_html__( 'Photography', 'apex-addons-for-elementor' ),
					'title'       => esc_html__( 'Urban Geometry', 'apex-addons-for-elementor' ),
					'description' => esc_html__( 'Minimalist shadow and light compositions in urban settings.', 'apex-addons-for-elementor' ),
					'button_text' => esc_html__( 'View Gallery', 'apex-addons-for-elementor' ),
					'image'       => [ 'url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80' ],
				],
			],
			'title_field' => '{{{ title || eyebrow || "Card Item" }}}',
		] );

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'image_size',
				'default'   => 'large',
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// Settings Section
		$this->start_controls_section( 'section_settings', [
			'label' => esc_html__( 'Slider Settings', 'apex-addons-for-elementor' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'show_content', [
			'label'        => esc_html__( 'Show Card Content / Text', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => esc_html__( 'Toggle OFF to completely hide all text overlays across every card.', 'apex-addons-for-elementor' ),
		] );

		$this->add_control( 'autoplay', [
			'label'        => esc_html__( 'Autoplay', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'no',
		] );

		$this->add_control( 'autoplay_speed', [
			'label'     => esc_html__( 'Autoplay Speed (ms)', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 1000,
			'max'       => 10000,
			'step'      => 500,
			'default'   => 3000,
			'condition' => [ 'autoplay' => 'yes' ],
		] );

		$this->add_control( 'visible_cards', [
			'label'   => esc_html__( 'Visible Stack Layers', 'apex-addons-for-elementor' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 6,
			'default' => 4,
		] );

		$this->add_control( 'show_arrows', [
			'label'        => esc_html__( 'Show Navigation Arrows', 'apex-addons-for-elementor' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		] );

		$this->end_controls_section();

		// ==================== STYLE TAB ====================

		// Card Dimensions & Geometry Section
		$this->start_controls_section( 'section_style_geometry', [
			'label' => esc_html__( 'Card Dimensions & 3D Geometry', 'apex-addons-for-elementor' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_responsive_control( 'align', [
			'label'     => esc_html__( 'Stack Alignment', 'apex-addons-for-elementor' ),
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
			'default'   => 'center',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-gallery' => 'justify-content: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'card_width', [
			'label'      => esc_html__( 'Card Width', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'vw', 'em' ],
			'range'      => [
				'px' => [ 'min' => 150, 'max' => 900 ],
				'%'  => [ 'min' => 20, 'max' => 100 ],
				'vw' => [ 'min' => 20, 'max' => 90 ],
			],
			'default'    => [ 'unit' => 'px', 'size' => 340 ],
			'selectors'  => [
				'{{WRAPPER}} .eas-card-stack-viewport' => '--eas-stack-width: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'card_height', [
			'label'      => esc_html__( 'Card Height', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'vh', 'em' ],
			'range'      => [
				'px' => [ 'min' => 200, 'max' => 1000 ],
				'vh' => [ 'min' => 20, 'max' => 90 ],
			],
			'default'    => [ 'unit' => 'px', 'size' => 450 ],
			'selectors'  => [
				'{{WRAPPER}} .eas-card-stack-viewport' => '--eas-stack-height: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->add_responsive_control( 'offset_x', [
			'label'      => esc_html__( 'Offset X (Horizontal Shift)', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [ 'min' => -80, 'max' => 80 ],
			],
			'default'    => [ 'unit' => 'px', 'size' => 20 ],
		] );

		$this->add_responsive_control( 'offset_y', [
			'label'      => esc_html__( 'Offset Y (Vertical Shift)', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [ 'min' => -80, 'max' => 80 ],
			],
			'default'    => [ 'unit' => 'px', 'size' => -20 ],
		] );

		$this->add_responsive_control( 'tilt', [
			'label'      => esc_html__( 'Card Tilt Angle (Degrees)', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'deg' ],
			'range'      => [
				'deg' => [ 'min' => -25, 'max' => 25 ],
			],
			'default'    => [ 'unit' => 'deg', 'size' => 0 ],
		] );

		$this->add_control( 'scale_factor', [
			'label'   => esc_html__( 'Layer Scale Reduction', 'apex-addons-for-elementor' ),
			'type'    => Controls_Manager::NUMBER,
			'min'     => 0.7,
			'max'     => 0.99,
			'step'    => 0.01,
			'default' => 0.94,
		] );

		$this->add_responsive_control( 'perspective', [
			'label'      => esc_html__( '3D Perspective Depth', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px' ],
			'range'      => [
				'px' => [ 'min' => 400, 'max' => 2500 ],
			],
			'default'    => [ 'unit' => 'px', 'size' => 1000 ],
			'selectors'  => [
				'{{WRAPPER}} .eas-card-stack-viewport' => '--eas-stack-perspective: {{SIZE}}{{UNIT}};',
			],
		] );

		$this->end_controls_section();

		// Image Fit & Styling
		$this->start_controls_section( 'section_style_image', [
			'label' => esc_html__( 'Image Fit & Styling', 'apex-addons-for-elementor' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );

		$this->add_control( 'card_bg_color', [
			'label'     => esc_html__( 'Card Background Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => 'transparent',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-item' => 'background-color: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'object_fit', [
			'label'     => esc_html__( 'Image Object Fit', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => [
				'cover'      => esc_html__( 'Cover', 'apex-addons-for-elementor' ),
				'contain'    => esc_html__( 'Contain', 'apex-addons-for-elementor' ),
				'fill'       => esc_html__( 'Fill', 'apex-addons-for-elementor' ),
				'scale-down' => esc_html__( 'Scale Down', 'apex-addons-for-elementor' ),
			],
			'default'   => 'cover',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-img' => '--eas-img-fit: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'object_position', [
			'label'     => esc_html__( 'Image Object Position', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::SELECT,
			'options'   => [
				'center center' => esc_html__( 'Center Center', 'apex-addons-for-elementor' ),
				'top center'    => esc_html__( 'Top Center', 'apex-addons-for-elementor' ),
				'bottom center' => esc_html__( 'Bottom Center', 'apex-addons-for-elementor' ),
				'left center'   => esc_html__( 'Left Center', 'apex-addons-for-elementor' ),
				'right center'  => esc_html__( 'Right Center', 'apex-addons-for-elementor' ),
			],
			'default'   => 'center center',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-img' => '--eas-img-pos: {{VALUE}};',
			],
		] );

		$this->add_responsive_control( 'border_radius', [
			'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%', 'em' ],
			'selectors'  => [
				'{{WRAPPER}} .eas-card-stack-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			],
		] );

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .eas-card-stack-item',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .eas-card-stack-item',
			]
		);

		$this->end_controls_section();

		// Content Overlay & Typography
		$this->start_controls_section( 'section_style_content', [
			'label'     => esc_html__( 'Content & Overlay', 'apex-addons-for-elementor' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_content' => 'yes' ],
		] );

		$this->add_control( 'eyebrow_color', [
			'label'     => esc_html__( 'Eyebrow Text Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-eyebrow' => '--eas-eyebrow-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'eyebrow_bg_color', [
			'label'     => esc_html__( 'Eyebrow Background Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => 'rgba(255, 255, 255, 0.2)',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-eyebrow' => '--eas-eyebrow-bg: {{VALUE}};',
			],
		] );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'eyebrow_typography',
				'selector' => '{{WRAPPER}} .eas-card-stack-eyebrow',
			]
		);

		$this->add_control( 'title_color', [
			'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'separator' => 'before',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-title' => 'color: {{VALUE}};',
			],
		] );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .eas-card-stack-title',
			]
		);

		$this->add_control( 'desc_color', [
			'label'     => esc_html__( 'Description Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => 'rgba(255, 255, 255, 0.85)',
			'separator' => 'before',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-desc' => '--eas-desc-color: {{VALUE}};',
			],
		] );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .eas-card-stack-desc',
			]
		);

		$this->add_control( 'btn_color', [
			'label'     => esc_html__( 'Button Text Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#111111',
			'separator' => 'before',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn-link' => '--eas-btn-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'btn_bg_color', [
			'label'     => esc_html__( 'Button Background Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn-link' => '--eas-btn-bg: {{VALUE}};',
			],
		] );

		$this->add_control( 'btn_hover_color', [
			'label'     => esc_html__( 'Button Hover Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn-link' => '--eas-btn-hover-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'btn_hover_bg_color', [
			'label'     => esc_html__( 'Button Hover Background', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#6d28d9',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn-link' => '--eas-btn-hover-bg: {{VALUE}};',
			],
		] );

		$this->end_controls_section();

		// Navigation Arrows Section
		$this->start_controls_section( 'section_style_nav', [
			'label'     => esc_html__( 'Navigation Arrows', 'apex-addons-for-elementor' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_arrows' => 'yes' ],
		] );

		$this->add_control( 'nav_btn_color', [
			'label'     => esc_html__( 'Button Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#111111',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn' => '--eas-nav-btn-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'nav_btn_bg', [
			'label'     => esc_html__( 'Button Background', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn' => '--eas-nav-btn-bg: {{VALUE}};',
			],
		] );

		$this->add_control( 'nav_btn_hover_color', [
			'label'     => esc_html__( 'Hover Button Color', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn' => '--eas-nav-btn-hover-color: {{VALUE}};',
			],
		] );

		$this->add_control( 'nav_btn_hover_bg', [
			'label'     => esc_html__( 'Hover Background', 'apex-addons-for-elementor' ),
			'type'      => Controls_Manager::COLOR,
			'default'   => '#6d28d9',
			'selectors' => [
				'{{WRAPPER}} .eas-card-stack-btn' => '--eas-nav-btn-hover-bg: {{VALUE}};',
			],
		] );

		$this->end_controls_section();
	}

	protected function render() {
		$settings     = $this->get_settings_for_display();
		$cards        = $settings['cards'] ?? [];
		$show_content = 'yes' === ( $settings['show_content'] ?? 'yes' );

		if ( empty( $cards ) ) {
			return;
		}

		$config = [
			'autoplay'      => 'yes' === ( $settings['autoplay'] ?? 'no' ),
			'autoplaySpeed' => absint( $settings['autoplay_speed'] ?? 3000 ),
			'visibleCards'  => absint( $settings['visible_cards'] ?? 4 ),
			'offsetX'       => (float) ( $settings['offset_x']['size'] ?? 20 ),
			'offsetY'       => (float) ( $settings['offset_y']['size'] ?? -20 ),
			'tilt'          => (float) ( $settings['tilt']['size'] ?? 0 ),
			'scaleFactor'   => (float) ( $settings['scale_factor'] ?? 0.94 ),
		];

		$align_class = ! empty( $settings['align'] ) ? 'eas-align-' . sanitize_html_class( $settings['align'] ) : 'eas-align-center';
		?>
		<div class="eas-card-stack-gallery <?php echo esc_attr( $align_class ); ?>" data-eas-stack-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
			<div class="eas-card-stack-viewport">
				<ul class="eas-card-stack-list">
					<?php foreach ( $cards as $index => $item ) : ?>
						<?php
						$eyebrow     = sanitize_text_field( $item['eyebrow'] ?? '' );
						$title       = sanitize_text_field( $item['title'] ?? '' );
						$description = sanitize_textarea_field( $item['description'] ?? '' );
						$button_text = sanitize_text_field( $item['button_text'] ?? '' );
						
						$img_url = ! empty( $item['image']['url'] ) ? Group_Control_Image_Size::get_attachment_image_src( $item['image']['id'], 'image_size', $settings ) : Utils::get_placeholder_image_src();
						if ( ! $img_url ) {
							$img_url = $item['image']['url'] ?? Utils::get_placeholder_image_src();
						}
						$link_url    = ! empty( $item['link']['url'] ) ? esc_url( $item['link']['url'] ) : '';
						$link_target = ! empty( $item['link']['is_external'] ) ? '_blank' : '_self';

						$has_item_content = $show_content && ( '' !== $eyebrow || '' !== $title || '' !== $description || ( '' !== $button_text && '' !== $link_url ) );
						?>
						<li class="eas-card-stack-item elementor-repeater-item-<?php echo esc_attr( $item['_id'] ); ?>" data-link="<?php echo esc_attr( $link_url ); ?>" data-target="<?php echo esc_attr( $link_target ); ?>">
							<div class="eas-card-stack-media">
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ? $title : $eyebrow ); ?>" class="eas-card-stack-img" loading="lazy" />
								<div class="eas-card-stack-overlay"></div>
							</div>
							<?php if ( $has_item_content ) : ?>
								<div class="eas-card-stack-content">
									<?php if ( '' !== $eyebrow ) : ?>
										<span class="eas-card-stack-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
									<?php endif; ?>
									<?php if ( '' !== $title ) : ?>
										<h4 class="eas-card-stack-title"><?php echo esc_html( $title ); ?></h4>
									<?php endif; ?>
									<?php if ( '' !== $description ) : ?>
										<p class="eas-card-stack-desc"><?php echo esc_html( $description ); ?></p>
									<?php endif; ?>
									<?php if ( '' !== $button_text && '' !== $link_url ) : ?>
										<a href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>" class="eas-card-stack-btn-link">
											<?php echo esc_html( $button_text ); ?>
										</a>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php if ( 'yes' === ( $settings['show_arrows'] ?? 'yes' ) ) : ?>
				<div class="eas-card-stack-nav" aria-label="<?php esc_attr_e( 'Gallery Navigation', 'apex-addons-for-elementor' ); ?>">
					<button type="button" class="eas-card-stack-btn eas-card-stack-prev" aria-label="<?php esc_attr_e( 'Previous Card', 'apex-addons-for-elementor' ); ?>">
						<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
					</button>
					<button type="button" class="eas-card-stack-btn eas-card-stack-next" aria-label="<?php esc_attr_e( 'Next Card', 'apex-addons-for-elementor' ); ?>">
						<svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
					</button>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
