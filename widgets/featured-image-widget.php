<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Featured_Image_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-featured-image';
	}

	public function get_title() {
		return esc_html__( 'Featured Image', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-featured-image';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Featured Image', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'image',
				'default' => 'large',
			]
		);

		$this->add_responsive_control(
			'align',
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
					'{{WRAPPER}} .eas-featured-image-container' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_to',
			[
				'label'   => esc_html__( 'Link', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'none' => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'file' => esc_html__( 'Media File', 'apex-addons-for-elementor' ),
					'post' => esc_html__( 'Post URL', 'apex-addons-for-elementor' ),
				],
				'default' => 'none',
			]
		);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label'      => esc_html__( 'Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-featured-image-container img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label'      => esc_html__( 'Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-featured-image-container img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .eas-featured-image-container img, {{WRAPPER}} .eas-featured-image-placeholder',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-featured-image-container img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .eas-featured-image-placeholder' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .eas-featured-image-container img, {{WRAPPER}} .eas-featured-image-placeholder',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .eas-featured-image-container img',
			]
		);

		// Dynamic styles for the placeholder
		$this->add_control(
			'placeholder_styling',
			[
				'type'      => Controls_Manager::HIDDEN,
				'selectors' => [
					'{{WRAPPER}} .eas-featured-image-placeholder' => 'display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; max-width: 600px; height: 250px; background: #f8fafc; border: 2px dashed #cbd5e1; color: #64748b; padding: 20px;',
					'{{WRAPPER}} .eas-featured-image-placeholder i' => 'font-size: 48px; margin-bottom: 10px;',
					'{{WRAPPER}} .eas-featured-image-placeholder p' => 'margin: 0; font-size: 14px; font-weight: 500;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings   = $this->get_settings_for_display();
		$current_id = get_the_ID();
		$post_type  = get_post_type( $current_id );
		$preview    = Plugin::$instance->editor->is_edit_mode();

		$post_id = $current_id;
		if ( 'apexadfo_template' === $post_type || 'elementor_library' === $post_type ) {
			$sample_posts = get_posts( [
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			] );
			if ( ! empty( $sample_posts ) ) {
				$post_id = $sample_posts[0]->ID;
			}
		}

		$size       = ! empty( $settings['image_size'] ) ? $settings['image_size'] : 'large';
		$image_html = get_the_post_thumbnail( $post_id, $size );

		// Fallback sample image for editor/preview if target post has no featured image
		if ( empty( $image_html ) && ( $preview || 'apexadfo_template' === $post_type || 'elementor_library' === $post_type ) ) {
			$sample_attachment = get_posts( [
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
				'post_status'    => 'inherit',
			] );
			if ( ! empty( $sample_attachment ) ) {
				$image_html = wp_get_attachment_image( $sample_attachment[0]->ID, $size );
			}
		}

		if ( empty( $image_html ) ) {
			if ( $preview ) {
				echo '<div class="eas-featured-image-container">';
				echo '<div class="eas-featured-image-placeholder">';
				echo '<i class="eicon-image" aria-hidden="true"></i>';
				echo '<p>' . esc_html__( 'Featured Image Placeholder', 'apex-addons-for-elementor' ) . '</p>';
				echo '</div>';
				echo '</div>';
			}
			return;
		}

		// Handle Link
		if ( 'file' === $settings['link_to'] ) {
			$image_url  = get_the_post_thumbnail_url( $post_id, 'full' );
			$image_html = sprintf( '<a href="%s">%s</a>', esc_url( $image_url ), $image_html );
		} elseif ( 'post' === $settings['link_to'] ) {
			$image_html = sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post_id ) ), $image_html );
		}

		echo '<div class="eas-featured-image-container">' . wp_kses_post( $image_html ) . '</div>';
	}
}
