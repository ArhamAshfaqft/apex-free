<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Icons_Manager;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Comparison Table Widget
 */
class Comparison_Table_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-comparison-table';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Comparison Table', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-price-table';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	/**
	 * Get script dependencies.
	 *
	 * @return array Script handles.
	 */
	public function get_script_depends() {
		return [ 'apexadfo-comparison-table-js' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @return array Style handles.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-comparison-table-css' ];
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {

		// ==========================================
		// CONTENT TAB - PLAN COLUMNS
		// ==========================================

		$this->start_controls_section(
			'section_columns',
			[
				'label' => esc_html__( 'Plan Columns', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater_cols = new Repeater();

		$repeater_cols->add_control(
			'item_title',
			[
				'label'       => esc_html__( 'Plan / Product Title', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Standard Plan', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater_cols->add_control(
			'item_subtitle',
			[
				'label'   => esc_html__( 'Subtitle / Tagline', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Perfect for small teams', 'apex-addons-for-elementor' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater_cols->add_control(
			'item_currency',
			[
				'label'   => esc_html__( 'Currency Symbol', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '$',
			]
		);

		$repeater_cols->add_control(
			'item_price',
			[
				'label'   => esc_html__( 'Price Amount', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '29',
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater_cols->add_control(
			'item_period',
			[
				'label'   => esc_html__( 'Period', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '/month', 'apex-addons-for-elementor' ),
			]
		);

		$repeater_cols->add_control(
			'is_featured',
			[
				'label'        => esc_html__( 'Highlight Column', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$repeater_cols->add_control(
			'ribbon_text',
			[
				'label'     => esc_html__( 'Badge / Ribbon Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Most Popular', 'apex-addons-for-elementor' ),
				'condition' => [
					'is_featured' => 'yes',
				],
			]
		);

		$repeater_cols->add_control(
			'btn_text',
			[
				'label'   => esc_html__( 'CTA Button Text', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Choose Plan', 'apex-addons-for-elementor' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater_cols->add_control(
			'btn_link',
			[
				'label'       => esc_html__( 'CTA Button Link', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://your-link.com',
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater_cols->add_control(
			'custom_header_bg',
			[
				'label'     => esc_html__( 'Custom Header Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .apexadfo-comparison-table {{CURRENT_ITEM}}.apexadfo-table-th' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-mobile-plan-card{{CURRENT_ITEM}} .apexadfo-mobile-plan-header' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$repeater_cols->add_control(
			'custom_title_color',
			[
				'label'     => esc_html__( 'Custom Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .apexadfo-plan-title' => 'color: {{VALUE}};',
				],
			]
		);

		$repeater_cols->add_control(
			'custom_btn_bg',
			[
				'label'     => esc_html__( 'Custom Button BG Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .apexadfo-plan-cta-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'columns_list',
			[
				'label'       => esc_html__( 'Comparison Columns', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater_cols->get_controls(),
				'title_field' => '{{{ item_title }}}',
				'default'     => [
					[
						'item_title'    => esc_html__( 'Starter', 'apex-addons-for-elementor' ),
						'item_subtitle' => esc_html__( 'For solo freelancers', 'apex-addons-for-elementor' ),
						'item_price'    => '15',
						'is_featured'   => 'no',
					],
					[
						'item_title'    => esc_html__( 'Professional', 'apex-addons-for-elementor' ),
						'item_subtitle' => esc_html__( 'Best for growing businesses', 'apex-addons-for-elementor' ),
						'item_price'    => '49',
						'is_featured'   => 'yes',
						'ribbon_text'   => esc_html__( 'Most Popular', 'apex-addons-for-elementor' ),
					],
					[
						'item_title'    => esc_html__( 'Enterprise', 'apex-addons-for-elementor' ),
						'item_subtitle' => esc_html__( 'Full suite & priority support', 'apex-addons-for-elementor' ),
						'item_price'    => '99',
						'is_featured'   => 'no',
					],
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - FEATURE ROWS
		// ==========================================

		$this->start_controls_section(
			'section_rows',
			[
				'label' => esc_html__( 'Feature Rows', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater_rows = new Repeater();

		$repeater_rows->add_control(
			'feature_name',
			[
				'label'       => esc_html__( 'Feature Name', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Feature Item', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$repeater_rows->add_control(
			'feature_tooltip',
			[
				'label'   => esc_html__( 'Tooltip Description (Optional)', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => '',
				'dynamic' => [ 'active' => true ],
			]
		);

		for ( $i = 1; $i <= 10; $i++ ) {
			$repeater_rows->add_control(
				'col_' . $i . '_type',
				[
					'label'   => sprintf( esc_html__( 'Col %d Value Type', 'apex-addons-for-elementor' ), $i ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'check',
					'options' => [
						'check'       => esc_html__( 'Checkmark Icon (Included)', 'apex-addons-for-elementor' ),
						'cross'       => esc_html__( 'Cross Icon (Excluded)', 'apex-addons-for-elementor' ),
						'text'        => esc_html__( 'Custom Text Value', 'apex-addons-for-elementor' ),
						'custom_icon' => esc_html__( 'Custom Icon Picker', 'apex-addons-for-elementor' ),
					],
				]
			);

			$repeater_rows->add_control(
				'col_' . $i . '_text',
				[
					'label'     => sprintf( esc_html__( 'Col %d Text Value', 'apex-addons-for-elementor' ), $i ),
					'type'      => Controls_Manager::TEXT,
					'default'   => esc_html__( 'Unlimited', 'apex-addons-for-elementor' ),
					'condition' => [
						'col_' . $i . '_type' => 'text',
					],
				]
			);

			$repeater_rows->add_control(
				'col_' . $i . '_icon',
				[
					'label'     => sprintf( esc_html__( 'Col %d Icon', 'apex-addons-for-elementor' ), $i ),
					'type'      => Controls_Manager::ICONS,
					'default'   => [
						'value'   => 'fas fa-star',
						'library' => 'fa-solid',
					],
					'condition' => [
						'col_' . $i . '_type' => 'custom_icon',
					],
				]
			);
		}

		$this->add_control(
			'rows_list',
			[
				'label'       => esc_html__( 'Feature Comparison Rows', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater_rows->get_controls(),
				'title_field' => '{{{ feature_name }}}',
				'default'     => [
					[
						'feature_name'    => esc_html__( 'Active User Seats', 'apex-addons-for-elementor' ),
						'feature_tooltip' => esc_html__( 'Number of team members permitted on this subscription plan.', 'apex-addons-for-elementor' ),
						'col_1_type'      => 'text',
						'col_1_text'      => '1 Seat',
						'col_2_type'      => 'text',
						'col_2_text'      => '10 Seats',
						'col_3_type'      => 'text',
						'col_3_text'      => 'Unlimited Seats',
					],
					[
						'feature_name'    => esc_html__( 'Cloud Storage Capacity', 'apex-addons-for-elementor' ),
						'feature_tooltip' => esc_html__( 'High-speed NVMe storage provided for project assets.', 'apex-addons-for-elementor' ),
						'col_1_type'      => 'text',
						'col_1_text'      => '10 GB',
						'col_2_type'      => 'text',
						'col_2_text'      => '100 GB',
						'col_3_type'      => 'text',
						'col_3_text'      => '1 TB NVMe',
					],
					[
						'feature_name'    => esc_html__( '24/7 Dedicated Support', 'apex-addons-for-elementor' ),
						'col_1_type'      => 'cross',
						'col_2_type'      => 'check',
						'col_3_type'      => 'check',
					],
					[
						'feature_name'    => esc_html__( 'Custom Domain & SSL', 'apex-addons-for-elementor' ),
						'col_1_type'      => 'check',
						'col_2_type'      => 'check',
						'col_3_type'      => 'check',
					],
					[
						'feature_name'    => esc_html__( 'API & Webhook Access', 'apex-addons-for-elementor' ),
						'col_1_type'      => 'cross',
						'col_2_type'      => 'cross',
						'col_3_type'      => 'check',
					],
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// CONTENT TAB - LAYOUT OPTIONS
		// ==========================================

		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout Options', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'mobile_layout',
			[
				'label'   => esc_html__( 'Mobile Responsive Layout', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'stack',
				'options' => [
					'stack'  => esc_html__( 'Stack as Plan Cards', 'apex-addons-for-elementor' ),
					'scroll' => esc_html__( 'Horizontal Scroll Table', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'show_tooltips',
			[
				'label'        => esc_html__( 'Enable Feature Tooltips', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - HEADER ROW & PLANS
		// ==========================================

		$this->start_controls_section(
			'section_style_header',
			[
				'label' => esc_html__( 'Header & Plan Columns', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'feature_col_width',
			[
				'label'      => esc_html__( 'Feature Column Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [ 'min' => 10, 'max' => 50 ],
					'px' => [ 'min' => 100, 'max' => 500 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-th-feature' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label'      => esc_html__( 'Header Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '28',
					'right'    => '16',
					'bottom'   => '24',
					'left'     => '16',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-table-th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_bg_color',
			[
				'label'     => esc_html__( 'Header Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f9fafb',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-table-th' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_title_typography',
				'label'    => esc_html__( 'Plan Title Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-plan-title',
			]
		);

		$this->add_control(
			'plan_title_color',
			[
				'label'     => esc_html__( 'Plan Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_subtitle_typography',
				'label'    => esc_html__( 'Subtitle Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-plan-subtitle',
			]
		);

		$this->add_control(
			'plan_subtitle_color',
			[
				'label'     => esc_html__( 'Subtitle Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6b7280',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-subtitle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'price_typography',
				'label'    => esc_html__( 'Price Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-plan-amount',
			]
		);

		$this->add_control(
			'price_color',
			[
				'label'     => esc_html__( 'Price Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-amount, {{WRAPPER}} .apexadfo-plan-currency' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'featured_header_bg',
			[
				'label'     => esc_html__( 'Highlighted Header BG Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#eff6ff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-th-plan.apexadfo-is-featured' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'ribbon_typography',
				'label'    => esc_html__( 'Ribbon Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-plan-ribbon',
			]
		);

		$this->add_control(
			'ribbon_bg_color',
			[
				'label'     => esc_html__( 'Badge Ribbon BG Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-ribbon' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'ribbon_text_color',
			[
				'label'     => esc_html__( 'Badge Ribbon Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-ribbon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - FEATURE ROWS & CELLS
		// ==========================================

		$this->start_controls_section(
			'section_style_rows',
			[
				'label' => esc_html__( 'Feature Rows & Cells', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'cell_padding',
			[
				'label'      => esc_html__( 'Cell Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '16',
					'right'    => '16',
					'bottom'   => '16',
					'left'     => '16',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-table-td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'feature_alignment',
			[
				'label'     => esc_html__( 'Feature Name Alignment', 'apex-addons-for-elementor' ),
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
					'{{WRAPPER}} .apexadfo-td-feature' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'feature_typography',
				'label'    => esc_html__( 'Feature Name Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-td-feature',
			]
		);

		$this->add_control(
			'feature_text_color',
			[
				'label'     => esc_html__( 'Feature Name Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-td-feature' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'value_alignment',
			[
				'label'     => esc_html__( 'Cell Values Alignment', 'apex-addons-for-elementor' ),
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
					'{{WRAPPER}} .apexadfo-td-value' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'cell_value_typography',
				'label'    => esc_html__( 'Cell Text Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-cell-text',
			]
		);

		$this->add_control(
			'cell_value_color',
			[
				'label'     => esc_html__( 'Cell Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#374151',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-cell-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'even_row_bg',
			[
				'label'     => esc_html__( 'Even Rows Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f9fafb',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-table-tr:nth-child(even)' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'check_icon_color',
			[
				'label'     => esc_html__( 'Checkmark Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#10b981',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-cell-icon-check' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'check_icon_size',
			[
				'label'      => esc_html__( 'Checkmark Icon Size', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 12, 'max' => 40 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-cell-icon-check svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'cross_icon_color',
			[
				'label'     => esc_html__( 'Cross Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9ca3af',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-cell-icon-cross' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'cross_icon_size',
			[
				'label'      => esc_html__( 'Cross Icon Size', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 12, 'max' => 40 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-cell-icon-cross svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'featured_cell_bg',
			[
				'label'     => esc_html__( 'Highlighted Cells BG Tint', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(239, 246, 255, 0.4)',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-td-value.apexadfo-is-featured' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - TOOLTIPS
		// ==========================================

		$this->start_controls_section(
			'section_style_tooltips',
			[
				'label' => esc_html__( 'Tooltips', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'tooltip_trigger_color',
			[
				'label'     => esc_html__( 'Icon Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6b7280',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-tooltip-trigger' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tooltip_trigger_bg',
			[
				'label'     => esc_html__( 'Icon Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e5e7eb',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-tooltip-trigger' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'tooltip_typography',
				'label'    => esc_html__( 'Bubble Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-tooltip-bubble',
			]
		);

		$this->add_control(
			'tooltip_bg_color',
			[
				'label'     => esc_html__( 'Bubble Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1f2937',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-tooltip-bubble' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .apexadfo-tooltip-bubble::after' => 'border-top-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'tooltip_text_color',
			[
				'label'     => esc_html__( 'Bubble Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-tooltip-bubble' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ==========================================
		// STYLE TAB - CTA BUTTONS
		// ==========================================

		$this->start_controls_section(
			'section_style_buttons',
			[
				'label' => esc_html__( 'CTA Buttons', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'btn_typography',
				'label'    => esc_html__( 'Button Typography', 'apex-addons-for-elementor' ),
				'selector' => '{{WRAPPER}} .apexadfo-plan-cta-btn',
			]
		);

		$this->start_controls_tabs( 'tabs_btn_style' );

		$this->start_controls_tab(
			'tab_btn_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'btn_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3b82f6',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-cta-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-cta-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_btn_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'btn_bg_hover_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-cta-btn:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_text_hover_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .apexadfo-plan-cta-btn:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'btn_padding',
			[
				'label'      => esc_html__( 'Button Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'default'    => [
					'top'      => '10',
					'right'    => '20',
					'bottom'   => '10',
					'left'     => '20',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-plan-cta-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'btn_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '8',
					'right'    => '8',
					'bottom'   => '8',
					'left'     => '8',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .apexadfo-plan-cta-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Helper to render cell value content
	 */
	private function render_cell_value( $type, $text, $icon_data ) {
		if ( 'check' === $type ) {
			?>
			<span class="apexadfo-cell-icon apexadfo-cell-icon-check" aria-label="<?php echo esc_attr__( 'Included', 'apex-addons-for-elementor' ); ?>">
				<svg viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
			</span>
			<?php
		} elseif ( 'cross' === $type ) {
			?>
			<span class="apexadfo-cell-icon apexadfo-cell-icon-cross" aria-label="<?php echo esc_attr__( 'Excluded', 'apex-addons-for-elementor' ); ?>">
				<svg viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
			</span>
			<?php
		} elseif ( 'custom_icon' === $type && ! empty( $icon_data['value'] ) ) {
			?>
			<span class="apexadfo-cell-icon">
				<?php Icons_Manager::render_icon( $icon_data, [ 'aria-hidden' => 'true' ] ); ?>
			</span>
			<?php
		} elseif ( 'text' === $type && ! empty( $text ) ) {
			?>
			<span class="apexadfo-cell-text"><?php echo esc_html( $text ); ?></span>
			<?php
		}
	}

	/**
	 * Render widget output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$columns = ! empty( $settings['columns_list'] ) ? $settings['columns_list'] : [];
		$rows    = ! empty( $settings['rows_list'] ) ? $settings['rows_list'] : [];

		$mobile_layout = ! empty( $settings['mobile_layout'] ) ? $settings['mobile_layout'] : 'stack';
		$show_tooltips = ( isset( $settings['show_tooltips'] ) && 'yes' === $settings['show_tooltips'] );

		if ( empty( $columns ) ) {
			return;
		}

		$container_class = 'apexadfo-comparison-table-container apexadfo-mobile-' . esc_attr( $mobile_layout );
		?>
		<div class="apexadfo-comparison-table-wrapper">
			<div class="<?php echo esc_attr( $container_class ); ?>">

				<!-- Standard Table Layout (Desktop & Table Scroll) -->
				<table class="apexadfo-comparison-table" role="table">
					<thead>
						<tr>
							<!-- Feature Column Header -->
							<th class="apexadfo-table-th apexadfo-th-feature" scope="col">
								<span class="screen-reader-text"><?php echo esc_html__( 'Features', 'apex-addons-for-elementor' ); ?></span>
							</th>

							<!-- Plan Columns Headers -->
							<?php
							foreach ( $columns as $col_index => $col ) :
								$is_featured = ( isset( $col['is_featured'] ) && 'yes' === $col['is_featured'] );
								$th_class    = 'apexadfo-table-th apexadfo-th-plan elementor-repeater-item-' . esc_attr( $col['_id'] ) . ( $is_featured ? ' apexadfo-is-featured' : '' );
								?>
								<th class="<?php echo esc_attr( $th_class ); ?>" scope="col">
									<?php if ( $is_featured && ! empty( $col['ribbon_text'] ) ) : ?>
										<span class="apexadfo-plan-ribbon"><?php echo esc_html( $col['ribbon_text'] ); ?></span>
									<?php endif; ?>

									<?php if ( ! empty( $col['item_title'] ) ) : ?>
										<h3 class="apexadfo-plan-title"><?php echo esc_html( $col['item_title'] ); ?></h3>
									<?php endif; ?>

									<?php if ( ! empty( $col['item_subtitle'] ) ) : ?>
										<p class="apexadfo-plan-subtitle"><?php echo esc_html( $col['item_subtitle'] ); ?></p>
									<?php endif; ?>

									<?php if ( ! empty( $col['item_price'] ) ) : ?>
										<div class="apexadfo-plan-price-wrap">
											<?php if ( ! empty( $col['item_currency'] ) ) : ?>
												<span class="apexadfo-plan-currency"><?php echo esc_html( $col['item_currency'] ); ?></span>
											<?php endif; ?>
											<span class="apexadfo-plan-amount"><?php echo esc_html( $col['item_price'] ); ?></span>
											<?php if ( ! empty( $col['item_period'] ) ) : ?>
												<span class="apexadfo-plan-period"><?php echo esc_html( $col['item_period'] ); ?></span>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php if ( ! empty( $col['btn_link']['url'] ) && ! empty( $col['btn_text'] ) ) : ?>
										<?php
										$btn_key = 'col_btn_' . $col_index;
										$this->add_render_attribute( $btn_key, 'href', esc_url( $col['btn_link']['url'] ) );
										$this->add_render_attribute( $btn_key, 'class', 'apexadfo-plan-cta-btn' );
										$rel_values = [];
										if ( ! empty( $col['btn_link']['is_external'] ) ) {
											$this->add_render_attribute( $btn_key, 'target', '_blank' );
											$rel_values[] = 'noopener';
										}
										if ( ! empty( $col['btn_link']['nofollow'] ) ) {
											$rel_values[] = 'nofollow';
										}
										if ( ! empty( $rel_values ) ) {
											$this->add_render_attribute( $btn_key, 'rel', implode( ' ', $rel_values ) );
										}
										?>
										<a <?php $this->print_render_attribute_string( $btn_key ); ?>><?php echo esc_html( $col['btn_text'] ); ?></a>
									<?php endif; ?>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row_index => $row ) : ?>
							<tr class="apexadfo-table-tr">
								<!-- Feature Name & Tooltip -->
								<td class="apexadfo-table-td apexadfo-td-feature">
									<span class="apexadfo-feature-title"><?php echo esc_html( ! empty( $row['feature_name'] ) ? $row['feature_name'] : '' ); ?></span>
									<?php if ( $show_tooltips && ! empty( $row['feature_tooltip'] ) ) : ?>
										<span class="apexadfo-tooltip-wrap" tabindex="0" aria-label="<?php echo esc_attr__( 'More info', 'apex-addons-for-elementor' ); ?>">
											<span class="apexadfo-tooltip-trigger" aria-hidden="true">?</span>
											<span class="apexadfo-tooltip-bubble" role="tooltip"><?php echo esc_html( $row['feature_tooltip'] ); ?></span>
										</span>
									<?php endif; ?>
								</td>

								<!-- Cell Values per Plan Column -->
								<?php
								foreach ( $columns as $col_index => $col ) :
									$col_num     = $col_index + 1;
									$type_key    = 'col_' . $col_num . '_type';
									$text_key    = 'col_' . $col_num . '_text';
									$icon_key    = 'col_' . $col_num . '_icon';
									$cell_type   = ! empty( $row[ $type_key ] ) ? $row[ $type_key ] : 'check';
									$cell_text   = ! empty( $row[ $text_key ] ) ? $row[ $text_key ] : '';
									$cell_icon   = ! empty( $row[ $icon_key ] ) ? $row[ $icon_key ] : [];
									$is_featured = ( isset( $col['is_featured'] ) && 'yes' === $col['is_featured'] );
									$td_class    = 'apexadfo-table-td apexadfo-td-value elementor-repeater-item-' . esc_attr( $col['_id'] ) . ( $is_featured ? ' apexadfo-is-featured' : '' );
									?>
									<td class="<?php echo esc_attr( $td_class ); ?>">
										<?php $this->render_cell_value( $cell_type, $cell_text, $cell_icon ); ?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<!-- Mobile Stack Cards Mode Output (Rendered when stack mode is selected on mobile) -->
				<?php if ( 'stack' === $mobile_layout ) : ?>
					<div class="apexadfo-mobile-stack-cards-wrapper">
						<?php foreach ( $columns as $col_index => $col ) :
							$is_featured  = ( isset( $col['is_featured'] ) && 'yes' === $col['is_featured'] );
							$has_ribbon   = ( $is_featured && ! empty( $col['ribbon_text'] ) );
							$header_class = 'apexadfo-mobile-plan-header elementor-repeater-item-' . esc_attr( $col['_id'] ) . ( $is_featured ? ' apexadfo-is-featured' : '' );
							$card_class   = 'apexadfo-mobile-plan-card elementor-repeater-item-' . esc_attr( $col['_id'] ) . ( $has_ribbon ? ' apexadfo-has-ribbon' : '' );
							?>
							<div class="<?php echo esc_attr( $card_class ); ?>">
								<div class="<?php echo esc_attr( $header_class ); ?>">
									<?php if ( $is_featured && ! empty( $col['ribbon_text'] ) ) : ?>
										<span class="apexadfo-plan-ribbon"><?php echo esc_html( $col['ribbon_text'] ); ?></span>
									<?php endif; ?>

									<?php if ( ! empty( $col['item_title'] ) ) : ?>
										<h3 class="apexadfo-plan-title"><?php echo esc_html( $col['item_title'] ); ?></h3>
									<?php endif; ?>

									<?php if ( ! empty( $col['item_subtitle'] ) ) : ?>
										<p class="apexadfo-plan-subtitle"><?php echo esc_html( $col['item_subtitle'] ); ?></p>
									<?php endif; ?>

									<?php if ( ! empty( $col['item_price'] ) ) : ?>
										<div class="apexadfo-plan-price-wrap">
											<?php if ( ! empty( $col['item_currency'] ) ) : ?>
												<span class="apexadfo-plan-currency"><?php echo esc_html( $col['item_currency'] ); ?></span>
											<?php endif; ?>
											<span class="apexadfo-plan-amount"><?php echo esc_html( $col['item_price'] ); ?></span>
											<?php if ( ! empty( $col['item_period'] ) ) : ?>
												<span class="apexadfo-plan-period"><?php echo esc_html( $col['item_period'] ); ?></span>
											<?php endif; ?>
										</div>
									<?php endif; ?>

									<?php if ( ! empty( $col['btn_link']['url'] ) && ! empty( $col['btn_text'] ) ) : ?>
										<?php
										$m_btn_key = 'm_col_btn_' . $col_index;
										$this->add_render_attribute( $m_btn_key, 'href', esc_url( $col['btn_link']['url'] ) );
										$this->add_render_attribute( $m_btn_key, 'class', 'apexadfo-plan-cta-btn' );
										$m_rel_values = [];
										if ( ! empty( $col['btn_link']['is_external'] ) ) {
											$this->add_render_attribute( $m_btn_key, 'target', '_blank' );
											$m_rel_values[] = 'noopener';
										}
										if ( ! empty( $col['btn_link']['nofollow'] ) ) {
											$m_rel_values[] = 'nofollow';
										}
										if ( ! empty( $m_rel_values ) ) {
											$this->add_render_attribute( $m_btn_key, 'rel', implode( ' ', $m_rel_values ) );
										}
										?>
										<a <?php $this->print_render_attribute_string( $m_btn_key ); ?>><?php echo esc_html( $col['btn_text'] ); ?></a>
									<?php endif; ?>
								</div>

								<div class="apexadfo-mobile-plan-rows">
									<?php foreach ( $rows as $row_index => $row ) :
										$col_num   = $col_index + 1;
										$type_key  = 'col_' . $col_num . '_type';
										$text_key  = 'col_' . $col_num . '_text';
										$icon_key  = 'col_' . $col_num . '_icon';
										$cell_type = ! empty( $row[ $type_key ] ) ? $row[ $type_key ] : 'check';
										$cell_text = ! empty( $row[ $text_key ] ) ? $row[ $text_key ] : '';
										$cell_icon = ! empty( $row[ $icon_key ] ) ? $row[ $icon_key ] : [];
										?>
										<div class="apexadfo-mobile-row">
											<div class="apexadfo-mobile-feature-name">
												<?php echo esc_html( ! empty( $row['feature_name'] ) ? $row['feature_name'] : '' ); ?>
												<?php if ( $show_tooltips && ! empty( $row['feature_tooltip'] ) ) : ?>
													<span class="apexadfo-tooltip-wrap" tabindex="0">
														<span class="apexadfo-tooltip-trigger" aria-hidden="true">?</span>
														<span class="apexadfo-tooltip-bubble" role="tooltip"><?php echo esc_html( $row['feature_tooltip'] ); ?></span>
													</span>
												<?php endif; ?>
											</div>
											<div class="apexadfo-mobile-feature-value">
												<?php $this->render_cell_value( $cell_type, $cell_text, $cell_icon ); ?>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}
