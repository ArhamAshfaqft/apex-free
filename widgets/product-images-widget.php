<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Addons Product Images Widget
 */
class Product_Images_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-product-images';
	}

	public function get_title() {
		return esc_html__( 'Product Images', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-single-product';
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single', 'single', 'elementor-addon-suite-category' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'product', 'images', 'gallery', 'lightbox', 'apex' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-product-images-css' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-product-images-js' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Gallery Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_sale_flash',
			[
				'label'        => esc_html__( 'Show Sale Flash', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'sale_flash_text',
			[
				'label'     => esc_html__( 'Sale Flash Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Sale!', 'apex-addons-for-elementor' ),
				'condition' => [
					'show_sale_flash' => 'yes',
				],
			]
		);

		$this->add_control(
			'show_zoom_trigger',
			[
				'label'        => esc_html__( 'Show Lightbox Zoom Button', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => esc_html__( 'Image Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .eas-product-gallery img, {{WRAPPER}} .woocommerce-product-gallery__image img',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-product-gallery img, {{WRAPPER}} .woocommerce-product-gallery__image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'gallery_spacing',
			[
				'label'      => esc_html__( 'Spacing', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .woocommerce-product-gallery .flex-viewport' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_product() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		global $product;
		if ( $product instanceof \WC_Product ) {
			return $product;
		}

		$post_id = get_the_ID();
		if ( $post_id && 'product' === get_post_type( $post_id ) ) {
			return wc_get_product( $post_id );
		}

		$sample_posts = get_posts( [
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
		] );

		if ( ! empty( $sample_posts ) ) {
			return wc_get_product( $sample_posts[0]->ID );
		}

		return false;
	}

	protected function render() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<div class="eas-product-images-notice">' . esc_html__( 'WooCommerce is required for Product Images widget.', 'apex-addons-for-elementor' ) . '</div>';
			return;
		}

		global $product;
		$target_product = $this->get_product();

		if ( ! $target_product ) {
			echo '<div class="eas-product-images-placeholder" style="background:#f1f5f9; padding:40px; text-align:center; border:2px dashed #cbd5e1; border-radius:8px; color:#64748b;">';
			echo '<span class="dashicons dashicons-format-image" style="font-size:36px; width:36px; height:36px;"></span>';
			echo '<p style="margin:8px 0 0 0; font-weight:600;">' . esc_html__( 'No product found to preview image gallery.', 'apex-addons-for-elementor' ) . '</p>';
			echo '</div>';
			return;
		}

		// Enqueue standard WooCommerce single product gallery scripts if available
		if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
			wp_enqueue_script( 'zoom' );
		}
		if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
			wp_enqueue_script( 'flexslider' );
		}
		if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
			wp_enqueue_script( 'photoswipe-ui-default' );
			wp_enqueue_style( 'photoswipe-default-skin' );
		}
		wp_enqueue_script( 'wc-single-product' );

		// Store old product reference to restore afterwards
		$old_product = $product;
		$product     = $target_product;

		$settings          = $this->get_settings_for_display();
		$show_sale_flash   = 'yes' === $settings['show_sale_flash'];
		$sale_flash_text   = ! empty( $settings['sale_flash_text'] ) ? $settings['sale_flash_text'] : esc_html__( 'Sale!', 'apex-addons-for-elementor' );
		$show_zoom_trigger = 'yes' === $settings['show_zoom_trigger'];

		$gallery_classes = 'eas-product-gallery woocommerce';
		if ( ! $show_zoom_trigger ) {
			$gallery_classes .= ' eas-hide-zoom-trigger';
		}

		echo '<div class="' . esc_attr( $gallery_classes ) . '">';
		if ( $show_sale_flash && $product->is_on_sale() ) {
			echo '<span class="onsale" style="position:absolute; top:10px; left:10px; z-index:10; background:#ef4444; color:#fff; padding:4px 12px; border-radius:4px; font-weight:700; font-size:12px; text-transform:uppercase;">' . esc_html( $sale_flash_text ) . '</span>';
		}

		// Render native WooCommerce single product gallery
		wc_get_template( 'single-product/product-image.php' );

		echo '</div>';

		// Restore original product
		$product = $old_product;
	}
}
