<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Addons Product Add To Cart Widget
 */
class Product_Add_To_Cart_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-product-add-to-cart';
	}

	public function get_title() {
		return esc_html__( 'Product Add to Cart', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-product-add-to-cart';
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single', 'single', 'elementor-addon-suite-category' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'product', 'cart', 'buy', 'button', 'add to cart', 'apex' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Button Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .eas-product-add-to-cart button.single_add_to_cart_button',
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
			'button_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-add-to-cart button.single_add_to_cart_button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-add-to-cart button.single_add_to_cart_button' => 'background-color: {{VALUE}};',
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
			'button_hover_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-add-to-cart button.single_add_to_cart_button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-add-to-cart button.single_add_to_cart_button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-product-add-to-cart button.single_add_to_cart_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator'  => 'before',
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
			echo '<div class="eas-product-add-to-cart-notice">' . esc_html__( 'WooCommerce is required for Add to Cart widget.', 'apex-addons-for-elementor' ) . '</div>';
			return;
		}

		global $product;
		$target_product = $this->get_product();

		if ( ! $target_product ) {
			echo '<div class="eas-product-add-to-cart-placeholder" style="padding:15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">';
			echo '<button style="background:#0f172a; color:#fff; padding:10px 20px; border:none; border-radius:4px; font-weight:600; cursor:pointer;">' . esc_html__( 'Add to cart', 'apex-addons-for-elementor' ) . '</button>';
			echo '</div>';
			return;
		}

		$old_product = $product;
		$product     = $target_product;

		echo '<div class="eas-product-add-to-cart woocommerce">';
		woocommerce_template_single_add_to_cart();
		echo '</div>';

		$product = $old_product;
	}
}
