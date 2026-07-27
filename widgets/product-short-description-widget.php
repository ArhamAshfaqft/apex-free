<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Addons Product Short Description Widget
 */
class Product_Short_Description_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-product-short-description';
	}

	public function get_title() {
		return esc_html__( 'Product Short Description', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-excerpt';
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single', 'single', 'elementor-addon-suite-category' ];
	}

	public function get_keywords() {
		return [ 'woocommerce', 'product', 'description', 'excerpt', 'summary', 'apex' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Style', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-product-short-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .eas-product-short-description',
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => esc_html__( 'Justified', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-product-short-description' => 'text-align: {{VALUE}};',
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
			return;
		}

		$target_product = $this->get_product();
		$excerpt        = $target_product ? $target_product->get_short_description() : '';

		// If short description is empty or in editor/template mode, use sample fallback
		if ( empty( $excerpt ) || Plugin::$instance->editor->is_edit_mode() || 'apexadfo_template' === get_post_type() ) {
			if ( empty( $excerpt ) ) {
				$excerpt = esc_html__( 'This is a sample product short description. It highlights the key features, materials, and benefits of the item.', 'apex-addons-for-elementor' );
			}
		}

		echo '<div class="eas-product-short-description woocommerce-product-details__short-description">';
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Documented core WooCommerce filter.
		echo wp_kses_post( apply_filters( 'woocommerce_short_description', $excerpt ) );
		echo '</div>';
	}
}
