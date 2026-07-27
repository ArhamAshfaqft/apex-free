<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Post_Comments_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-post-comments';
	}

	public function get_title() {
		return esc_html__( 'Post Comments', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-comments';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Comments', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'info_msg',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => esc_html__( 'Displays the default WordPress comments section for the current post/page.', 'apex-addons-for-elementor' ),
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

		$this->add_control(
			'box_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-post-comments-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .eas-post-comments-wrap',
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-post-comments-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'placeholder_styles',
			[
				'type'      => Controls_Manager::HIDDEN,
				'selectors' => [
					'{{WRAPPER}} .eas-post-comments-placeholder' => 'padding: 30px; border: 2px dashed #cbd5e1; text-align: center; color: #64748b; border-radius: 8px;',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		echo '<div class="eas-post-comments-wrap">';

		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			echo '<div class="eas-post-comments-placeholder">';
			echo esc_html__( '[Comments Template Placeholder - Renders comments section on frontend]', 'apex-addons-for-elementor' );
			echo '</div>';
		} else {
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		}

		echo '</div>';
	}
}
