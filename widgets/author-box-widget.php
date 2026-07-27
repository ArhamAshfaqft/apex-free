<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Author_Box_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-author-box';
	}

	public function get_title() {
		return esc_html__( 'Author Box', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_categories() {
		return [ 'single' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-author-box-css' ];
	}

	protected function register_controls() {
		// Content section
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Author Info', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source',
			[
				'label'   => esc_html__( 'Source', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'current' => esc_html__( 'Current Author', 'apex-addons-for-elementor' ),
					'custom'  => esc_html__( 'Custom', 'apex-addons-for-elementor' ),
				],
				'default' => 'current',
			]
		);

		// Profile Picture Controls
		$this->add_control(
			'show_avatar',
			[
				'label'        => esc_html__( 'Profile Picture', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'avatar_size',
			[
				'label'     => esc_html__( 'Picture Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 80,
				],
				'range'     => [
					'px' => [
						'min' => 40,
						'max' => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-author-avatar img, {{WRAPPER}} .eas-author-avatar-placeholder' => 'width: {{SIZE}}px; height: {{SIZE}}px; font-size: calc({{SIZE}}px / 2);',
				],
				'condition' => [
					'show_avatar' => 'yes',
				],
			]
		);

		$this->add_control(
			'author_avatar',
			[
				'label'     => esc_html__( 'Choose Picture', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [
					'source'      => 'custom',
					'show_avatar' => 'yes',
				],
			]
		);

		// Name Controls
		$this->add_control(
			'show_name',
			[
				'label'        => esc_html__( 'Display Name', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'author_name',
			[
				'label'     => esc_html__( 'Name', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'John Doe', 'apex-addons-for-elementor' ),
				'condition' => [
					'source'    => 'custom',
					'show_name' => 'yes',
				],
			]
		);

		$this->add_control(
			'author_name_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h3',
				'condition' => [
					'show_name' => 'yes',
				],
			]
		);

		$this->add_control(
			'link_to',
			[
				'label'     => esc_html__( 'Link', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'none'          => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'website'       => esc_html__( 'Author Website', 'apex-addons-for-elementor' ),
					'posts_archive' => esc_html__( 'Author Posts Archive', 'apex-addons-for-elementor' ),
					'custom'        => esc_html__( 'Custom URL', 'apex-addons-for-elementor' ),
				],
				'default'   => 'posts_archive',
			]
		);

		$this->add_control(
			'author_website',
			[
				'label'       => esc_html__( 'Custom URL', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'condition'   => [
					'link_to' => 'custom',
				],
			]
		);

		// Biography Controls
		$this->add_control(
			'show_biography',
			[
				'label'        => esc_html__( 'Biography', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'author_bio',
			[
				'label'     => esc_html__( 'Biography Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXTAREA,
				'default'   => esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'apex-addons-for-elementor' ),
				'condition' => [
					'source'         => 'custom',
					'show_biography' => 'yes',
				],
			]
		);

		// Archive Button Link Controls
		$this->add_control(
			'show_link',
			[
				'label'        => esc_html__( 'Archive Button', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'link_text',
			[
				'label'     => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'View All Posts', 'apex-addons-for-elementor' ),
				'condition' => [
					'show_link' => 'yes',
				],
			]
		);

		$this->add_control(
			'posts_url',
			[
				'label'       => esc_html__( 'Custom Archive URL', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'condition'   => [
					'source'    => 'custom',
					'show_link' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section(
			'section_style_box',
			[
				'label' => esc_html__( 'Box Container', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'      => '24',
					'right'    => '24',
					'bottom'   => '24',
					'left'     => '24',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-author-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'box_bg',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-author-box' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .eas-author-box',
			]
		);

		$this->add_responsive_control(
			'box_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-author-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .eas-author-box',
			]
		);

		$this->end_controls_section();

		// Style Section: Name & Bio
		$this->start_controls_section(
			'section_style_content',
			[
				'label' => esc_html__( 'Text & Bio', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'name_color',
			[
				'label'     => esc_html__( 'Name Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-author-name, {{WRAPPER}} .eas-author-name a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_name' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'name_typography',
				'selector'  => '{{WRAPPER}} .eas-author-name',
				'condition' => [
					'show_name' => 'yes',
				],
			]
		);

		$this->add_control(
			'bio_color',
			[
				'label'     => esc_html__( 'Bio Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-author-bio' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
				'condition' => [
					'show_biography' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'bio_typography',
				'selector'  => '{{WRAPPER}} .eas-author-bio',
				'condition' => [
					'show_biography' => 'yes',
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

		$author_id = get_post_field( 'post_author', $target_post_id );
		if ( ! $author_id ) {
			$author_id = get_current_user_id();
		}

		$email    = '';
		$name     = '';
		$bio      = '';
		$link_url = '';

		// Resolve values based on source
		if ( 'current' === $settings['source'] ) {
			$email = get_the_author_meta( 'user_email', $author_id );
			$name  = get_the_author_meta( 'display_name', $author_id );
			$bio   = get_the_author_meta( 'description', $author_id );

			if ( empty( $name ) && $preview ) {
				$name = esc_html__( 'Admin Author', 'apex-addons-for-elementor' );
			}
			if ( empty( $bio ) && $preview ) {
				$bio = esc_html__( 'Author biography content goes here. Pass internal details or custom metadata to customize your author profile.', 'apex-addons-for-elementor' );
			}

			if ( 'website' === $settings['link_to'] ) {
				$link_url = get_the_author_meta( 'user_url', $author_id );
			} elseif ( 'posts_archive' === $settings['link_to'] ) {
				$link_url = get_author_posts_url( $author_id );
			}
		} else {
			$name = ! empty( $settings['author_name'] ) ? $settings['author_name'] : '';
			$bio  = ! empty( $settings['author_bio'] ) ? $settings['author_bio'] : '';

			if ( 'website' === $settings['link_to'] || 'custom' === $settings['link_to'] ) {
				$link_url = ! empty( $settings['author_website']['url'] ) ? $settings['author_website']['url'] : '';
			} elseif ( 'posts_archive' === $settings['link_to'] ) {
				$link_url = get_author_posts_url( $author_id );
			}
		}

		// Resolve Avatar HTML
		$avatar_html = '';
		if ( 'yes' === $settings['show_avatar'] ) {
			$avatar_size = isset( $settings['avatar_size']['size'] ) ? intval( $settings['avatar_size']['size'] ) : 80;

			if ( 'current' === $settings['source'] ) {
				$avatar_html = get_avatar( $email, $avatar_size );
			} else {
				if ( ! empty( $settings['author_avatar']['url'] ) ) {
					$avatar_html = sprintf(
						'<img src="%s" alt="%s" />',
						esc_url( $settings['author_avatar']['url'] ),
						esc_attr( $name )
					);
				} else {
					$avatar_html = '<div class="eas-author-avatar-placeholder"><i class="eicon-user" aria-hidden="true"></i></div>';
				}
			}
		}

		// Resolve Archive Link
		$archive_url = '';
		if ( 'yes' === $settings['show_link'] ) {
			if ( ! empty( $settings['posts_url']['url'] ) ) {
				$archive_url = $settings['posts_url']['url'];
			} else {
				$archive_url = get_author_posts_url( $author_id );
			}
		}

		$name_tag = \Elementor\Utils::validate_html_tag( $settings['author_name_tag'] );

		echo '<div class="eas-author-box">';

		// Profile Picture (Avatar)
		if ( 'yes' === $settings['show_avatar'] && ! empty( $avatar_html ) ) {
			echo '<div class="eas-author-avatar">';
			if ( ! empty( $link_url ) ) {
				printf( '<a href="%s">%s</a>', esc_url( $link_url ), wp_kses_post( $avatar_html ) );
			} else {
				echo wp_kses_post( $avatar_html );
			}
			echo '</div>';
		}

		// Content area (Name, Bio, Button)
		echo '<div class="eas-author-content">';

		// Author Name
		if ( 'yes' === $settings['show_name'] && ! empty( $name ) ) {
			printf( '<%1$s class="eas-author-name">', esc_html( $name_tag ) );
			if ( ! empty( $link_url ) ) {
				printf( '<a href="%s">%s</a>', esc_url( $link_url ), esc_html( $name ) );
			} else {
				echo esc_html( $name );
			}
			printf( '</%1$s>', esc_html( $name_tag ) );
		}

		// Biography
		if ( 'yes' === $settings['show_biography'] && ! empty( $bio ) ) {
			echo '<div class="eas-author-bio">' . wp_kses_post( $bio ) . '</div>';
		}

		// Archive Button
		if ( 'yes' === $settings['show_link'] && ! empty( $archive_url ) && ! empty( $settings['link_text'] ) ) {
			echo '<div class="eas-author-archive-wrap">';
			printf(
				'<a href="%s" class="eas-author-archive-button">%s</a>',
				esc_url( $archive_url ),
				esc_html( $settings['link_text'] )
			);
			echo '</div>';
		}

		echo '</div>'; // .eas-author-content
		echo '</div>'; // .eas-author-box
	}
}
