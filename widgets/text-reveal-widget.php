<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Text Scroll Highlight Reveal Widget.
 *
 * Elementor widget offering Apple-style scroll-triggered typography highlights.
 */
class Text_Reveal_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-text-reveal';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Text Highlight Reveal', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-text-align-left';
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
	 * Register widget controls.
	 */
	protected function register_controls() {

		// --------------------------------------------------------------------------
		// CONTENT TAB
		// --------------------------------------------------------------------------
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Text Content', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'eas_tr_content',
			[
				'label'       => esc_html__( 'Text', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::WYSIWYG,
				'default'     => esc_html__( 'Scroll down to reveal this stunning Apple-style highlighted text anim. Design high-performance websites with Apex Addons.', 'apex-addons-for-elementor' ),
				'placeholder' => esc_html__( 'Enter your text here', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'eas_tr_html_tag',
			[
				'label'   => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'div',
				'options' => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'p'    => 'p',
					'div'  => 'div',
					'span' => 'span',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings',
			[
				'label' => esc_html__( 'Reveal Settings', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'eas_text_reveal',
			[
				'label'        => esc_html__( 'Scroll Highlight Reveal', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		// Complete reveal granularity options.
		$this->add_control(
			'eas_tr_granularity',
			[
				'label'   => esc_html__( 'Reveal Granularity', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'line',
				'options' => [
					'line' => esc_html__( 'Line by Line', 'apex-addons-for-elementor' ),
					'word' => esc_html__( 'Word by Word', 'apex-addons-for-elementor' ),
					'char' => esc_html__( 'Character by Character', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'eas_text_reveal' => 'yes',
				],
			]
		);

		$this->add_control(
			'eas_tr_disable_tablet',
			[
				'label'        => esc_html__( 'Disable on Tablet', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'eas_text_reveal' => 'yes',
				],
			]
		);

		$this->add_control(
			'eas_tr_disable_mobile',
			[
				'label'        => esc_html__( 'Disable on Mobile', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'eas_text_reveal' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// --------------------------------------------------------------------------
		// STYLE TAB
		// --------------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_text',
			[
				'label' => esc_html__( 'Text Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'eas_tr_align',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
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
					'{{WRAPPER}} .eas-tr-container' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'eas_tr_typography',
				'selector' => '{{WRAPPER}} .eas-tr-container, {{WRAPPER}} .eas-tr-container *',
			]
		);

		$this->add_control(
			'eas_tr_inactive_color',
			[
				'label'     => esc_html__( 'Dimmed Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.25)',
				'selectors' => [
					'{{WRAPPER}} .eas-tr-container' => '--eas-tr-inactive-color: {{VALUE}};',
				],
				'condition' => [
					'eas_text_reveal' => 'yes',
				],
			]
		);

		$this->add_control(
			'eas_tr_active_color',
			[
				'label'     => esc_html__( 'Highlighted Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-tr-container' => '--eas-tr-active-color: {{VALUE}};',
				],
				'condition' => [
					'eas_text_reveal' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['eas_tr_content'] ) ) {
			return;
		}

		// Enqueue scripts and styles
		wp_enqueue_script( 'apexadfo-text-reveal-js' );
		wp_enqueue_style( 'apexadfo-text-reveal-css' );

		// Setup HTML tag
		$html_tag = \Elementor\Utils::validate_html_tag( $settings['eas_tr_html_tag'] );

		// Setup configuration array
		$granularity = sanitize_key( $settings['eas_tr_granularity'] ?? 'line' );
		if ( ! in_array( $granularity, [ 'line', 'word', 'char' ], true ) ) {
			$granularity = 'line';
		}
		$tr_config = [
			'granularity'   => $granularity,
			'inactiveColor' => esc_attr( $settings['eas_tr_inactive_color'] ?? 'rgba(255,255,255,0.25)' ),
			'activeColor'   => esc_attr( $settings['eas_tr_active_color'] ?? '#ffffff' ),
			'disableTablet' => esc_attr( $settings['eas_tr_disable_tablet'] ?? 'no' ),
			'disableMobile' => esc_attr( $settings['eas_tr_disable_mobile'] ?? 'no' ),
		];

		// Allow companion extensions to merge additional settings.
		$tr_config = apply_filters( 'apexadfo_text_reveal_config', $tr_config, $settings );

		// Render output wrapper classes
		$wrapper_classes = [ 'eas-tr-container' ];
		if ( 'yes' === $settings['eas_text_reveal'] ) {
			$wrapper_classes[] = 'eas-text-reveal-active';
			$this->add_render_attribute( 'wrapper', 'data-eas-tr-config', wp_json_encode( $tr_config ) );
		}

		$this->add_render_attribute( 'wrapper', 'class', $wrapper_classes );
		?>
		<<?php echo esc_html( $html_tag ); ?> <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php echo wp_kses_post( $settings['eas_tr_content'] ); ?>
		</<?php echo esc_html( $html_tag ); ?>>
		<?php
	}
}
