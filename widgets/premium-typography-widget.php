<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Motion Typography widget included in Apex Addons Free.
 *
 * The Free package intentionally contains only the two effects available in
 * this widget: Liquid Morph and Matrix Scramble. Paid effects are implemented
 * and distributed exclusively by the companion plugin.
 */
class Premium_Typography_Widget extends \Elementor\Widget_Base {
	public function get_name() {
		return 'eas-premium-typography';
	}

	public function get_title() {
		return esc_html__( 'Motion Typography', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return [ 'eas-typography-category' ];
	}

	public function get_keywords() {
		return [ 'text', 'typography', 'morph', 'scramble', 'motion', 'apex' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-premium-typography-css' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-premium-typography-js' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_effect_source',
			[
				'label' => esc_html__( 'Text Effect', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'effect_type',
			[
				'label'   => esc_html__( 'Select Effect', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'morph'    => esc_html__( 'Liquid Morph', 'apex-addons-for-elementor' ),
					'scramble' => esc_html__( 'Matrix Scramble', 'apex-addons-for-elementor' ),
				],
				'default' => 'morph',
			]
		);

		$this->add_control(
			'heading_text',
			[
				'label'       => esc_html__( 'Text Content', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter Heading', 'apex-addons-for-elementor' ),
				'default'     => esc_html__( 'Futuristic', 'apex-addons-for-elementor' ),
				'condition'   => [ 'effect_type' => 'scramble' ],
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'morph_texts',
			[
				'label'       => esc_html__( 'Morphing Words (one per line)', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'placeholder' => "Creative\nDynamic\nPremium",
				'default'     => "Creative\nDynamic\nPremium",
				'condition'   => [ 'effect_type' => 'morph' ],
			]
		);

		$this->add_control(
			'html_tag',
			[
				'label'   => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [ 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div', 'span' => 'span' ],
				'default' => 'h2',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [ 'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ), 'icon' => 'eicon-text-align-left' ],
					'center'  => [ 'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ), 'icon' => 'eicon-text-align-center' ],
					'right'   => [ 'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ), 'icon' => 'eicon-text-align-right' ],
					'justify' => [ 'title' => esc_html__( 'Justify', 'apex-addons-for-elementor' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'selectors' => [ '{{WRAPPER}} .eas-premium-typography-wrap' => 'text-align: {{VALUE}};' ],
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_effect_settings',
			[
				'label' => esc_html__( 'Effect Options', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'morph_speed',
			[
				'label'     => esc_html__( 'Rotation Speed (ms)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1000,
				'max'       => 10000,
				'step'      => 500,
				'default'   => 3000,
				'condition' => [ 'effect_type' => 'morph' ],
			]
		);
		$this->add_control(
			'scramble_trigger',
			[
				'label'     => esc_html__( 'Trigger Animation On', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => [
					'load'   => esc_html__( 'On Page Load', 'apex-addons-for-elementor' ),
					'scroll' => esc_html__( 'Scroll Into View', 'apex-addons-for-elementor' ),
					'hover'  => esc_html__( 'On Hover', 'apex-addons-for-elementor' ),
				],
				'default'   => 'scroll',
				'condition' => [ 'effect_type' => 'scramble' ],
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => esc_html__( 'Typography Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .eas-premium-typography-wrap',
			]
		);
		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [ '{{WRAPPER}} .eas-premium-typography-wrap' => 'color: {{VALUE}};' ],
			]
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings    = $this->get_settings_for_display();
		$effect_type = sanitize_key( $settings['effect_type'] ?? 'morph' );
		if ( ! in_array( $effect_type, [ 'morph', 'scramble' ], true ) ) {
			$effect_type = 'morph';
		}
		if ( 'morph' === $effect_type && empty( $settings['morph_texts'] ) ) {
			return;
		}
		if ( 'scramble' === $effect_type && empty( $settings['heading_text'] ) ) {
			return;
		}

		$allowed_tags = [ 'h1', 'h2', 'h3', 'h4', 'div', 'span' ];
		$tag          = in_array( $settings['html_tag'] ?? '', $allowed_tags, true ) ? $settings['html_tag'] : 'h2';
		$this->add_render_attribute( 'wrap', 'class', [ 'eas-premium-typography-wrap', 'eas-effect-' . $effect_type ] );
		?>
		<<?php echo esc_html( $tag ); ?> <?php $this->print_render_attribute_string( 'wrap' ); ?>>
			<?php if ( 'morph' === $effect_type ) : ?>
				<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="eas-morph-filter" aria-hidden="true">
					<defs><filter id="eas-metaball-filter"><feGaussianBlur in="SourceGraphic" stdDeviation="6" result="blur"/><feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -8" result="goo"/><feBlend in="SourceGraphic" in2="goo"/></filter></defs>
				</svg>
				<div class="eas-premium-typography-morph" data-speed="<?php echo esc_attr( absint( $settings['morph_speed'] ?? 3000 ) ); ?>">
					<div class="eas-morph-word-container">
						<?php foreach ( preg_split( '/\r\n|[\r\n]/', (string) $settings['morph_texts'] ) as $index => $word ) : ?>
							<span class="eas-morph-word<?php echo 0 === $index ? ' active' : ''; ?>"><?php echo esc_html( $word ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			<?php else : ?>
				<span class="eas-premium-typography-scramble" data-trigger="<?php echo esc_attr( sanitize_key( $settings['scramble_trigger'] ?? 'scroll' ) ); ?>" data-text="<?php echo esc_attr( $settings['heading_text'] ); ?>"><?php echo esc_html( $settings['heading_text'] ); ?></span>
			<?php endif; ?>
		</<?php echo esc_html( $tag ); ?>>
		<?php
	}

	protected function content_template() {
		?>
		<#
		var effect = [ 'morph', 'scramble' ].indexOf( settings.effect_type ) !== -1 ? settings.effect_type : 'morph';
		var allowedTags = [ 'h1', 'h2', 'h3', 'h4', 'div', 'span' ];
		var tag = allowedTags.indexOf( settings.html_tag ) !== -1 ? settings.html_tag : 'h2';
		if ( effect === 'morph' && ! settings.morph_texts ) return;
		if ( effect === 'scramble' && ! settings.heading_text ) return;
		#>
		<{{{ tag }}} class="eas-premium-typography-wrap eas-effect-{{ effect }}">
			<# if ( effect === 'morph' ) { var words = settings.morph_texts.split('\n'); #>
				<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="eas-morph-filter" aria-hidden="true"><defs><filter id="eas-metaball-filter"><feGaussianBlur in="SourceGraphic" stdDeviation="6" result="blur"/><feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -8" result="goo"/><feBlend in="SourceGraphic" in2="goo"/></filter></defs></svg>
				<div class="eas-premium-typography-morph" data-speed="{{ settings.morph_speed }}"><div class="eas-morph-word-container">
					<# _.each( words, function( word, index ) { #><span class="eas-morph-word{{ index === 0 ? ' active' : '' }}">{{{ _.escape( word ) }}}</span><# } ); #>
				</div></div>
			<# } else { #>
				<span class="eas-premium-typography-scramble" data-trigger="{{ settings.scramble_trigger }}" data-text="{{ settings.heading_text }}">{{{ _.escape( settings.heading_text ) }}}</span>
			<# } #>
		</{{{ tag }}}>
		<?php
	}
}
