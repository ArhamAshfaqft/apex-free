<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Addons Product Price Widget
 */
class Product_Price_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-product-price';
	}

	public function get_title() {
		return esc_html__( 'Product Price', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-price-list';
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single', 'single', 'elementor-addon-suite-category' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'product', 'price', 'sale', 'apex' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Price Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
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
				'selectors' => [
					'{{WRAPPER}} .eas-product-price' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Price Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label'     => esc_html__( 'Price Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-price, {{WRAPPER}} .eas-product-price .amount' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'price_typography',
				'selector' => '{{WRAPPER}} .eas-product-price',
			]
		);

		$this->add_control(
			'sale_price_color',
			[
				'label'     => esc_html__( 'Sale / Original Price Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-price del, {{WRAPPER}} .eas-product-price del .amount' => 'color: {{VALUE}}; opacity: 0.7;',
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
		$product    = $this->get_product();
		$price_html = $product ? $product->get_price_html() : '<span class="amount">$29.99</span>';

		echo '<div class="eas-product-price price" style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">';
		echo wp_kses_post( $price_html );
		echo '</div>';
	}
}
