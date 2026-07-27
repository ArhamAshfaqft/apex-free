<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Plugin;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Post_Info_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-post-info';
	}

	public function get_title() {
		return esc_html__( 'Post Info', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-info';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Meta Info', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'layout',
			[
				'label'        => esc_html__( 'Layout', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'inline'  => esc_html__( 'Inline', 'apex-addons-for-elementor' ),
					'stacked' => esc_html__( 'Stacked', 'apex-addons-for-elementor' ),
				],
				'default'      => 'inline',
				'prefix_class' => 'eas-post-info-layout-',
			]
		);

		// Repeater for meta items
		$repeater = new Repeater();

		$repeater->add_control(
			'type',
			[
				'label'   => esc_html__( 'Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'author'   => esc_html__( 'Author', 'apex-addons-for-elementor' ),
					'date'     => esc_html__( 'Date', 'apex-addons-for-elementor' ),
					'time'     => esc_html__( 'Time', 'apex-addons-for-elementor' ),
					'comments' => esc_html__( 'Comments', 'apex-addons-for-elementor' ),
					'custom'   => esc_html__( 'Custom Field', 'apex-addons-for-elementor' ),
				],
				'default' => 'author',
			]
		);

		$repeater->add_control(
			'custom_field_key',
			[
				'label'     => esc_html__( 'Custom Field Key', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					'type' => 'custom',
				],
			]
		);

		$repeater->add_control(
			'label',
			[
				'label'       => esc_html__( 'Custom Label', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave blank for dynamic value', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'icon',
			[
				'label'   => esc_html__( 'Icon', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [
					'value'   => 'fas fa-user',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'meta_items',
			[
				'label'       => esc_html__( 'Meta Items', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'type' => 'author',
						'icon' => [ 'value' => 'fas fa-user', 'library' => 'fa-solid' ],
					],
					[
						'type' => 'date',
						'icon' => [ 'value' => 'fas fa-calendar-alt', 'library' => 'fa-solid' ],
					],
					[
						'type' => 'comments',
						'icon' => [ 'value' => 'fas fa-comments', 'library' => 'fa-solid' ],
					],
				],
				'title_field' => '{{{ type.charAt(0).toUpperCase() + type.slice(1) }}}',
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
			'item_gap',
			[
				'label'      => esc_html__( 'Space Between Items', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'default'    => [
					'size' => 15,
				],
				'selectors'  => [
					'{{WRAPPER}}.eas-post-info-layout-inline .eas-post-info-list' => 'gap: {{SIZE}}px;',
					'{{WRAPPER}}.eas-post-info-layout-stacked .eas-post-info-list' => 'gap: {{SIZE}}px;',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-info-text' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .eas-post-info-text',
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-info-icon i, {{WRAPPER}} .eas-post-info-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		// Core structure layout styles (zero inline styles)
		$this->add_control(
			'layout_styles',
			[
				'type'      => Controls_Manager::HIDDEN,
				'selectors' => [
					'{{WRAPPER}} .eas-post-info-list' => 'display: flex; flex-wrap: wrap; list-style: none; padding: 0; margin: 0; gap: 15px;',
					'{{WRAPPER}}.eas-post-info-layout-stacked .eas-post-info-list' => 'flex-direction: column; gap: 8px;',
					'{{WRAPPER}} .eas-post-info-item' => 'display: inline-flex; align-items: center; gap: 8px;',
					'{{WRAPPER}} .eas-post-info-icon' => 'display: inline-flex; align-items: center;',
					'{{WRAPPER}} .eas-post-info-icon i' => 'font-size: 14px; color: #64748b;',
					'{{WRAPPER}} .eas-post-info-text' => 'font-size: 13px; color: #475569;',
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

		$target_post_id = $current_id;
		if ( $preview && ( 'apexadfo_template' === $post_type || 'elementor_library' === $post_type ) ) {
			$sample_posts = get_posts( [
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			] );
			if ( ! empty( $sample_posts ) ) {
				$target_post_id = $sample_posts[0]->ID;
			}
		}

		if ( empty( $settings['meta_items'] ) ) {
			return;
		}

		echo '<ul class="eas-post-info-list">';

		foreach ( $settings['meta_items'] as $item ) {
			$value = '';

			switch ( $item['type'] ) {
				case 'author':
					$author_id = get_post_field( 'post_author', $target_post_id );
					$value     = get_the_author_meta( 'display_name', $author_id );
					if ( empty( $value ) && $preview ) {
						$value = esc_html__( 'Admin', 'apex-addons-for-elementor' );
					}
					break;
				case 'date':
					$value = get_the_date( '', $target_post_id );
					if ( empty( $value ) && $preview ) {
						$value = date_i18n( get_option( 'date_format' ) );
					}
					break;
				case 'time':
					$value = get_the_time( '', $target_post_id );
					if ( empty( $value ) && $preview ) {
						$value = date_i18n( get_option( 'time_format' ) );
					}
					break;
				case 'comments':
					$num   = get_comments_number( $target_post_id );
					$value = sprintf( _n( '%s Comment', '%s Comments', $num, 'apex-addons-for-elementor' ), number_format_i18n( $num ) );
					break;
				case 'custom':
					if ( ! empty( $item['custom_field_key'] ) ) {
						$value = get_post_meta( $target_post_id, $item['custom_field_key'], true );
					}
					if ( empty( $value ) && $preview ) {
						$value = esc_html__( 'Sample Meta', 'apex-addons-for-elementor' );
					}
					break;
			}

			// Exclude empty fields
			if ( empty( $value ) && empty( $item['label'] ) ) {
				continue;
			}

			$display_text = ! empty( $item['label'] ) ? $item['label'] : $value;

			echo '<li class="eas-post-info-item">';

			if ( ! empty( $item['icon']['value'] ) ) {
				echo '<span class="eas-post-info-icon">';
				\Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] );
				echo '</span>';
			}

			printf( '<span class="eas-post-info-text">%s</span>', esc_html( $display_text ) );
			echo '</li>';
		}

		echo '</ul>';
	}
}
