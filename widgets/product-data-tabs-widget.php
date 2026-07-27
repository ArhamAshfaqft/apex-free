<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Addons Product Data Tabs Widget
 */
class Product_Data_Tabs_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-product-data-tabs';
	}

	public function get_title() {
		return esc_html__( 'Product Data Tabs', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-tabs';
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single', 'single', 'elementor-addon-suite-category' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'product', 'tabs', 'data', 'reviews', 'description', 'apex' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_style_tabs',
			[
				'label' => esc_html__( 'Tabs Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'tab_color',
			[
				'label'     => esc_html__( 'Tab Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-data-tabs .wc-tabs li a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tab_active_color',
			[
				'label'     => esc_html__( 'Active Tab Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-data-tabs .wc-tabs li.active a' => 'color: {{VALUE}}; border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'tab_typography',
				'selector' => '{{WRAPPER}} .eas-product-data-tabs .wc-tabs li a',
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
			return;
		}

		global $product;
		$target_product = $this->get_product();

		if ( ! $target_product ) {
			return;
		}

		$old_product = $product;
		$product     = $target_product;

		echo '<div class="eas-product-data-tabs woocommerce">';
		woocommerce_output_product_data_tabs();
		echo '</div>';

		$product = $old_product;
	}
}
