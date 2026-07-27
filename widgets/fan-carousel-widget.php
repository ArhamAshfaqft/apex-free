<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Poker Fan Carousel Widget.
 *
 * Elementor widget rendering interactive, rotated cards in a curved hand-fan layout.
 */
class Fan_Carousel_Widget extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-fan-carousel';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Poker Fan Carousel', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-inner-section';
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
	 * Get widget keywords.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'fan', 'stack', 'carousel', 'poker', 'card', 'deck', 'rotated' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @return array Style dependencies.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-fan-carousel-css' ];
	}

	/**
	 * Get script dependencies.
	 *
	 * @return array Script dependencies.
	 */
	public function get_script_depends() {
		return [ 'apexadfo-fan-carousel-js' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ---------------------------------------------------------------------
		// Content Tab - Cards Repeater
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_content_cards',
			[
				'label' => esc_html__( 'Carousel Cards', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'card_image',
			[
				'label'   => esc_html__( 'Card Image', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'card_tag',
			[
				'label'       => esc_html__( 'Sub-tag / Tagline', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Ace', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'card_title',
			[
				'label'       => esc_html__( 'Title', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Creative Strategy', 'apex-addons-for-elementor' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'card_desc',
			[
				'label'   => esc_html__( 'Description', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Mapping distinct layouts out from raw concepts to high fidelity interactive digital deployment vectors.', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'card_link',
			[
				'label'       => esc_html__( 'Card Link', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
				'default'     => [
					'url'         => '#',
					'is_external' => false,
					'nofollow'    => false,
				],
			]
		);

		$this->add_control(
			'cards_list',
			[
				'label'       => esc_html__( 'Cards', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'card_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'card_tag'   => esc_html__( 'Ace', 'apex-addons-for-elementor' ),
						'card_title' => esc_html__( 'Creative Strategy', 'apex-addons-for-elementor' ),
						'card_desc'  => esc_html__( 'Mapping distinct layouts out from raw concepts to high fidelity interactive digital deployment vectors.', 'apex-addons-for-elementor' ),
					],
					[
						'card_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'card_tag'   => esc_html__( 'King', 'apex-addons-for-elementor' ),
						'card_title' => esc_html__( 'Interface Design', 'apex-addons-for-elementor' ),
						'card_desc'  => esc_html__( 'Crafting pixel-perfect web elements optimized with exceptional speed and strict compliance frameworks.', 'apex-addons-for-elementor' ),
					],
					[
						'card_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'card_tag'   => esc_html__( 'Queen', 'apex-addons-for-elementor' ),
						'card_title' => esc_html__( 'Motion FX', 'apex-addons-for-elementor' ),
						'card_desc'  => esc_html__( 'Injecting modern, hardware-accelerated animations smoothly directly into clean structural interfaces.', 'apex-addons-for-elementor' ),
					],
					[
						'card_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'card_tag'   => esc_html__( 'Jack', 'apex-addons-for-elementor' ),
						'card_title' => esc_html__( 'Custom Widgets', 'apex-addons-for-elementor' ),
						'card_desc'  => esc_html__( 'Extending standard Elementor features using reliable native code blocks written cleanly from scratch.', 'apex-addons-for-elementor' ),
					],
					[
						'card_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'card_tag'   => esc_html__( 'Ten', 'apex-addons-for-elementor' ),
						'card_title' => esc_html__( 'Optimization', 'apex-addons-for-elementor' ),
						'card_desc'  => esc_html__( 'Keeping your site lightning fast by completely removing massive rendering scripts and framework clutter.', 'apex-addons-for-elementor' ),
					],
				],
				'title_field' => '{{{ card_title }}}',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Layout Mechanics
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_content_layout',
			[
				'label' => esc_html__( 'Layout Settings', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'fan_height',
			[
				'label'      => esc_html__( 'Stage Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 300, 'max' => 800, 'step' => 10 ],
				],
				'default'    => [ 'size' => 520, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_width',
			[
				'label'      => esc_html__( 'Card Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 150, 'max' => 500, 'step' => 5 ],
				],
				'default'    => [ 'size' => 260, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-poker-card-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_height',
			[
				'label'      => esc_html__( 'Card Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 200, 'max' => 600, 'step' => 5 ],
				],
				'default'    => [ 'size' => 380, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-poker-card-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'fan_gap_x',
			[
				'label'      => esc_html__( 'Fanning Spread (X)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 40, 'max' => 250, 'step' => 5 ],
				],
				'default'    => [ 'size' => 110, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-gap-x: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'fan_gap_y',
			[
				'label'      => esc_html__( 'Fanning Drop (Y)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => -50, 'max' => 50, 'step' => 1 ],
				],
				'default'    => [ 'size' => -5, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-gap-y: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'fan_angle',
			[
				'label'      => esc_html__( 'Fanning Tilt Angle', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'range'      => [
					'deg' => [ 'min' => 0, 'max' => 45, 'step' => 1 ],
				],
				'default'    => [ 'size' => 12, 'unit' => 'deg' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-angle: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'animation_speed',
			[
				'label'      => esc_html__( 'Animation Duration (s)', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 's' ],
				'range'      => [
					's' => [ 'min' => 0.2, 'max' => 2.0, 'step' => 0.1 ],
				],
				'default'    => [ 'size' => 0.6, 'unit' => 's' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-speed: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Card Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_cards',
			[
				'label' => esc_html__( 'Card styling', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label'       => esc_html__( 'Solid Card Background Color (Fallback)', 'apex-addons-for-elementor' ),
				'description' => esc_html__( 'Applies only as a fallback color or for solid cards with no image.', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => [
					'{{WRAPPER}} .eas-poker-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_overlay_color',
			[
				'label'     => esc_html__( 'Card Overlay Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-poker-card-tint' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'card_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-poker-card' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'size' => 16,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-poker-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .eas-poker-card',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Text Settings
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_text',
			[
				'label' => esc_html__( 'Card Typography & Colors', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Tagline / Tag
		$this->add_control(
			'tag_heading',
			[
				'label'     => esc_html__( 'Tagline / Tag', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tag_color',
			[
				'label'     => esc_html__( 'Tag Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-accent-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'tag_typography',
				'selector' => '{{WRAPPER}} .eas-poker-card-tag',
			]
		);

		// Title
		$this->add_control(
			'title_heading',
			[
				'label'     => esc_html__( 'Title', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => esc_html__( 'Title Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-text-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .eas-poker-card-title',
			]
		);

		// Description
		$this->add_control(
			'desc_heading',
			[
				'label'     => esc_html__( 'Description', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'desc_color',
			[
				'label'     => esc_html__( 'Description Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-fan-carousel-wrapper' => '--eas-fan-text-muted: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'desc_typography',
				'selector' => '{{WRAPPER}} .eas-poker-card-desc',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Controls & Navigation
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_controls',
			[
				'label' => esc_html__( 'Navigation Controls', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'nav_btn_color',
			[
				'label'     => esc_html__( 'Button Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .eas-poker-btn' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_btn_bg',
			[
				'label'     => esc_html__( 'Button Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(0, 0, 0, 0.05)',
				'selectors' => [
					'{{WRAPPER}} .eas-poker-btn' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'nav_btn_hover_bg',
			[
				'label'     => esc_html__( 'Button Background (Hover)', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f59e0b',
				'selectors' => [
					'{{WRAPPER}} .eas-poker-btn:hover' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_offset_y',
			[
				'label'      => esc_html__( 'Distance from Card Stage', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 150, 'step' => 5 ],
				],
				'default'    => [
					'size' => 30,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-poker-control-deck' => '--eas-fan-nav-offset: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'nav_buttons_gap',
			[
				'label'      => esc_html__( 'Distance Between Buttons', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 10, 'max' => 300, 'step' => 5 ],
				],
				'default'    => [
					'size' => 20,
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .eas-poker-control-deck' => '--eas-fan-nav-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget frontend HTML output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$cards    = $settings['cards_list'] ?? [];

		if ( empty( $cards ) ) {
			return;
		}
		?>
		<div class="eas-fan-carousel-wrapper">
			<div class="eas-fan-deck-stage">
				<?php foreach ( $cards as $index => $card ) : 
					// Fallback to Elementor default placeholder image if no image URL is specified
					$img_url = ! empty( $card['card_image']['url'] ) ? esc_url( $card['card_image']['url'] ) : Utils::get_placeholder_image_src();
					$link_url = ! empty( $card['card_link']['url'] ) ? esc_url( $card['card_link']['url'] ) : '#';
					$target   = ! empty( $card['card_link']['is_external'] ) ? ' target="_blank"' : '';
					$nofollow = ! empty( $card['card_link']['nofollow'] ) ? ' rel="nofollow"' : '';
					?>
					<div class="eas-poker-card" data-index="<?php echo esc_attr( $index ); ?>">
						<div class="eas-poker-card-image" style="background-image: url('<?php echo esc_url( $img_url ); ?>');"></div>
						<div class="eas-poker-card-tint"></div>
						<div class="eas-poker-card-details">
							<?php if ( ! empty( $card['card_tag'] ) ) : ?>
								<span class="eas-poker-card-tag"><?php echo esc_html( $card['card_tag'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $card['card_title'] ) ) : ?>
								<h3 class="eas-poker-card-title"><?php echo esc_html( $card['card_title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $card['card_desc'] ) ) : ?>
								<p class="eas-poker-card-desc"><?php echo esc_html( $card['card_desc'] ); ?></p>
							<?php endif; ?>
						</div>
						<?php if ( '#' !== $link_url ) : ?>
							<a href="<?php echo esc_url( $link_url ); ?>"<?php if ( $target ) : ?> target="_blank"<?php endif; ?><?php if ( $nofollow ) : ?> rel="nofollow"<?php endif; ?> style="position: absolute; inset: 0; z-index: 5;"></a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Navigation Buttons -->
			<div class="eas-poker-control-deck">
				<button class="eas-poker-btn deck-left-trigger" aria-label="<?php esc_attr_e( 'Previous card', 'apex-addons-for-elementor' ); ?>">
					<svg viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
				</button>
				<button class="eas-poker-btn deck-right-trigger" aria-label="<?php esc_attr_e( 'Next card', 'apex-addons-for-elementor' ); ?>">
					<svg viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
				</button>
			</div>
		</div>
		<?php
	}
}
