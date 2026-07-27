<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Addons Product Meta Widget
 */
class Product_Meta_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-product-meta';
	}

	public function get_title() {
		return esc_html__( 'Product Meta', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-tags';
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single', 'single', 'elementor-addon-suite-category' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'product', 'meta', 'sku', 'category', 'tag', 'apex' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Meta Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_color',
			[
				'label'     => esc_html__( 'Link Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-meta a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .eas-product-meta',
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

		echo '<div class="eas-product-meta woocommerce product_meta">';
		woocommerce_template_single_meta();
		echo '</div>';

		$product = $old_product;
	}
}
