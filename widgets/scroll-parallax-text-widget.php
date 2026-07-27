<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Scroll Parallax Text Widget.
 *
 * Elementor widget offering hardware-accelerated horizontal text marquee scrolling on scroll.
 */
class Scroll_Parallax_Text_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-scroll-parallax-text';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Scroll Marquee', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-animated-headline';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'eas-typography-category' ];
	}

	/**
	 * Get widget keywords.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'scroll', 'parallax', 'marquee', 'text', 'ticker', 'horizontal' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @return array Style dependencies.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-scroll-parallax-text-css' ];
	}

	/**
	 * Get script dependencies.
	 *
	 * @return array Script dependencies.
	 */
	public function get_script_depends() {
		return [ 'apexadfo-scroll-parallax-text-js' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ---------------------------------------------------------------------
		// Content Tab - Text Options
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_content_parallax',
			[
				'label' => esc_html__( 'Text Options', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'marquee_text',
			[
				'label'       => esc_html__( 'Text Content', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter scrolling text...', 'apex-addons-for-elementor' ),
				'default'     => esc_html__( 'Apex Addons for Elementor - Build high performance websites with stunning custom layouts.', 'apex-addons-for-elementor' ),
				'dynamic'     => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'repeat_count',
			[
				'label'   => esc_html__( 'Repeat Count', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				],
				'default' => [
					'size' => 3,
				],
			]
		);

		$this->add_control(
			'scroll_direction',
			[
				'label'   => esc_html__( 'Direction', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'rtl' => esc_html__( 'Right to Left', 'apex-addons-for-elementor' ),
					'ltr' => esc_html__( 'Left to Right', 'apex-addons-for-elementor' ),
				],
				'default' => 'rtl',
			]
		);

		$this->add_control(
			'scroll_speed',
			[
				'label'   => esc_html__( 'Speed Multiplier', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0.1,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 1,
				],
			]
		);

		$this->add_control(
			'html_tag',
			[
				'label'   => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'div'  => 'div',
					'span' => 'span',
				],
				'default' => 'h2',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Styling Options
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_parallax',
			[
				'label' => esc_html__( 'Text Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .eas-scroll-parallax-text-wrap',
			]
		);

		$this->start_controls_tabs( 'tabs_text_style' );

		$this->start_controls_tab(
			'tab_text_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .eas-scroll-parallax-text-wrap' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_text_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'text_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-scroll-parallax-text-wrap:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'repeater_gap',
			[
				'label'     => esc_html__( 'Spacing Between Repeats (px)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 10,
						'max'  => 200,
						'step' => 5,
					],
				],
				'default'   => [
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}}' => '--eas-spt-gap: {{SIZE}}px;',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		do_action( 'apexadfo_scroll_marquee/register_controls', $this );
	}

	/**
	 * Render output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['marquee_text'] ) ) {
			return;
		}

		$repeats = ! empty( $settings['repeat_count']['size'] ) ? intval( $settings['repeat_count']['size'] ) : 3;
		$direction = esc_attr( $settings['scroll_direction'] );
		$speed = ! empty( $settings['scroll_speed']['size'] ) ? floatval( $settings['scroll_speed']['size'] ) : 1;
		$allowed_tags = [ 'div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];
		$tag = in_array( $settings['html_tag'] ?? '', $allowed_tags, true ) ? $settings['html_tag'] : 'div';
		$marquee_config = apply_filters(
			'apexadfo_scroll_marquee_config',
			[ 'mode' => 'scroll' ],
			$settings
		);

		// Set render attributes
		$this->add_render_attribute( 'wrap', [
			'class'          => 'eas-scroll-parallax-text-wrap',
			'data-direction' => $direction,
			'data-speed'     => $speed,
			'data-eas-marquee-config' => wp_json_encode( $marquee_config ),
		] );

		?>
		<<?php echo esc_html( $tag ); ?> <?php $this->print_render_attribute_string( 'wrap' ); ?>>
			<div class="eas-scroll-parallax-text-inner eas-marquee-row" data-row="0" data-direction="<?php echo esc_attr( $direction ); ?>" data-speed="<?php echo esc_attr( $speed ); ?>">
				<?php for ( $i = 0; $i < $repeats; $i++ ) : ?>
					<span class="eas-parallax-text-item"><?php echo esc_html( $settings['marquee_text'] ); ?></span>
				<?php endfor; ?>
			</div>
		</<?php echo esc_html( $tag ); ?>>
		<?php
	}

	/**
	 * Render live templates in the editor (Backbone JS).
	 */
	protected function content_template() {
		?>
		<#
		if ( ! settings.marquee_text ) return;

		var text = settings.marquee_text;
		var repeats = ( settings.repeat_count && settings.repeat_count.size ) || 3;
		var direction = settings.scroll_direction;
		var speed = settings.scroll_speed.size || 1;
		var tag = settings.html_tag;
		var marqueeConfig = { mode: 'scroll' };
		#>
		<{{{ tag }}} class="eas-scroll-parallax-text-wrap" data-direction="{{ direction }}" data-speed="{{ speed }}" data-eas-marquee-config="{{ _.escape( JSON.stringify( marqueeConfig ) ) }}">
			<div class="eas-scroll-parallax-text-inner eas-marquee-row" data-row="0" data-direction="{{ direction }}" data-speed="{{ speed }}">
				<# for ( var i = 0; i < repeats; i++ ) { #>
					<span class="eas-parallax-text-item">{{{ _.escape( text ) }}}</span>
				<# } #>
			</div>
		</{{{ tag }}}>
		<?php
	}
}
