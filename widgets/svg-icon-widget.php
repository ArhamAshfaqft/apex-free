<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex SVG & Icon Widget.
 *
 * Elementor widget that handles local icon libraries and manual SVG code.
 */
class SVG_Icon_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'eas-svg-icon';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'SVG & Icon', 'apex-addons-for-elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-editor-code';
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
		return [ 'svg', 'icon', 'custom', 'vector', 'code', 'iconify', 'apex' ];
	}

	/**
	 * Get style dependencies.
	 *
	 * @return array Style dependencies.
	 */
	public function get_style_depends() {
		return [ 'apexadfo-svg-icon-css' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		// ---------------------------------------------------------------------
		// Content Tab - SVG / Icon Selection
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_icon_source',
			[
				'label' => esc_html__( 'Icon Source', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'icon_source',
			[
				'label'   => esc_html__( 'Choose Source', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'library' => esc_html__( 'Native Icon Library', 'apex-addons-for-elementor' ),
					'svg'     => esc_html__( 'Custom SVG Code', 'apex-addons-for-elementor' ),
				],
				'default' => 'library',
			]
		);

		// Source 1: Native Icon Library
		$this->add_control(
			'selected_icon',
			[
				'label'     => esc_html__( 'Select Icon', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-star',
					'library' => 'solid',
				],
				'condition' => [
					'icon_source' => 'library',
				],
			]
		);


		// Source 3: Custom SVG Code
		$this->add_control(
			'svg_code',
			[
				'label'       => esc_html__( 'Raw SVG Code', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'rows'        => 10,
				'placeholder' => esc_html__( '<svg ...>...</svg>', 'apex-addons-for-elementor' ),
				'default'     => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.25z"/></svg>',
				'condition'   => [
					'icon_source' => 'svg',
				],
			]
		);

		// Layout view settings
		$this->add_control(
			'view',
			[
				'label'   => esc_html__( 'View', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Default', 'apex-addons-for-elementor' ),
					'stacked' => esc_html__( 'Stacked', 'apex-addons-for-elementor' ),
					'framed'  => esc_html__( 'Framed', 'apex-addons-for-elementor' ),
				],
				'default' => 'default',
			]
		);

		$this->add_control(
			'shape',
			[
				'label'     => esc_html__( 'Shape', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => [
					'circle' => esc_html__( 'Circle', 'apex-addons-for-elementor' ),
					'square' => esc_html__( 'Square', 'apex-addons-for-elementor' ),
				],
				'default'   => 'circle',
				'condition' => [
					'view!' => 'default',
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => esc_html__( 'Link', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
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
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .eas-svg-icon-wrap' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'svg_color_mode',
			[
				'label'       => esc_html__( 'Color Override Mode', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'original' => esc_html__( 'Keep Original Colors', 'apex-addons-for-elementor' ),
					'fill'     => esc_html__( 'Force Fill (For solid icons)', 'apex-addons-for-elementor' ),
					'stroke'   => esc_html__( 'Force Stroke (For outline icons)', 'apex-addons-for-elementor' ),
					'both'     => esc_html__( 'Force Both (Fill & Stroke)', 'apex-addons-for-elementor' ),
				],
				'default'     => 'both',
				'description' => esc_html__( 'Determines how styling controls override the SVG child node colors.', 'apex-addons-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Colors & Layout Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_icon',
			[
				'label' => esc_html__( 'Icon Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_icon_style' );

		// Normal State
		$this->start_controls_tab(
			'tab_icon_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'primary_color',
			[
				'label'     => esc_html__( 'Primary Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => [
					'{{WRAPPER}}' => '--eas-svg-primary-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'secondary_color',
			[
				'label'     => esc_html__( 'Secondary Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'condition' => [
					'view!' => 'default',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--eas-svg-secondary-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_icon_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'hover_primary_color',
			[
				'label'     => esc_html__( 'Primary Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}:hover' => '--eas-svg-primary-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_secondary_color',
			[
				'label'     => esc_html__( 'Secondary Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'condition' => [
					'view!' => 'default',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => '--eas-svg-secondary-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'apex-addons-for-elementor' ),
				'type'  => \Elementor\Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Divider separator
		$this->add_control(
			'hr_style',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);

		// Size & Layout Sizing
		$this->add_responsive_control(
			'size',
			[
				'label'      => esc_html__( 'Size (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min' => 10,
						'max' => 300,
					],
				],
				'default'    => [
					'size' => 50,
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--eas-svg-size: {{SIZE}}px;',
					'{{WRAPPER}} .eas-svg-icon-container' => 'font-size: {{SIZE}}px;',
					'{{WRAPPER}} .eas-svg-icon-container i' => 'font-size: {{SIZE}}px; width: {{SIZE}}px; height: {{SIZE}}px; line-height: 1;',
					'{{WRAPPER}} .eas-svg-icon-container svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
					'{{WRAPPER}} .eas-svg-icon-container img' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label'      => esc_html__( 'Padding (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition'  => [
					'view!' => 'default',
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--eas-svg-padding: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'rotate',
			[
				'label'      => esc_html__( 'Rotate (deg)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => -360,
						'max'  => 360,
						'step' => 1,
					],
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--eas-svg-rotate: {{SIZE}}deg;',
				],
			]
		);

		$this->add_responsive_control(
			'border_width',
			[
				'label'      => esc_html__( 'Border Width (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'condition'  => [
					'view' => 'framed',
				],
				'selectors'  => [
					'{{WRAPPER}}' => '--eas-svg-border-width: {{SIZE}}px;',
				],
			]
		);

		$this->add_responsive_control(
			'border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}}' => '--eas-svg-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'view!' => 'default',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Parse dynamic svg elements (e.g. remove hardcoded sizing so CSS styles work)
	 */
	private function clean_svg( $svg_code ) {
		if ( empty( $svg_code ) ) {
			return '';
		}

		$svg_code = trim( $svg_code );
		
		// Find opening SVG tag
		$svg_start = stripos( $svg_code, '<svg' );
		if ( $svg_start === false ) {
			return $svg_code;
		}
		$svg_code = substr( $svg_code, $svg_start );

		// Strip hardcoded sizing properties
		$svg_code = preg_replace( '/<svg([^>]*?\s)width=(["\'])(.*?)\2/i', '<svg$1', $svg_code );
		$svg_code = preg_replace( '/<svg([^>]*?\s)height=(["\'])(.*?)\2/i', '<svg$1', $svg_code );
		$svg_code = preg_replace( '/<svg([^>]*?\s)style=(["\'])(.*?)\2/i', '<svg$1', $svg_code );

		return $svg_code;
	}

	/**
	 * Sanitization whitelist for rendering clean vector nodes
	 */
	private function sanitize_svg_output( $svg_code ) {
		$allowed_tags = [
			'svg' => [
				'xmlns'               => true,
				'viewbox'             => true,
				'viewBox'             => true,
				'class'               => true,
				'id'                  => true,
				'style'               => true,
				'xml:space'           => true,
				'preserveaspectratio' => true,
				'preserveAspectRatio' => true,
			],
			'path' => [
				'd'               => true,
				'fill'            => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
				'class'           => true,
				'id'              => true,
				'style'           => true,
				'transform'       => true,
				'opacity'         => true,
			],
			'g' => [
				'fill'      => true,
				'stroke'    => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
				'transform' => true,
				'opacity'   => true,
			],
			'circle' => [
				'cx'        => true,
				'cy'        => true,
				'r'         => true,
				'fill'      => true,
				'stroke'    => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
				'transform' => true,
				'opacity'   => true,
			],
			'rect' => [
				'x'         => true,
				'y'         => true,
				'width'     => true,
				'height'    => true,
				'rx'        => true,
				'ry'        => true,
				'fill'      => true,
				'stroke'    => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
				'transform' => true,
				'opacity'   => true,
			],
			'line' => [
				'x1'        => true,
				'y1'        => true,
				'x2'        => true,
				'y2'        => true,
				'fill'      => true,
				'stroke'    => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
				'transform' => true,
				'opacity'   => true,
			],
			'polygon' => [
				'points'    => true,
				'fill'      => true,
				'stroke'    => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
				'transform' => true,
				'opacity'   => true,
			],
			'polyline' => [
				'points'    => true,
				'fill'      => true,
				'stroke'    => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
				'transform' => true,
				'opacity'   => true,
			],
			'ellipse' => [
				'cx'        => true,
				'cy'        => true,
				'rx'        => true,
				'ry'        => true,
				'fill'      => true,
				'stroke'    => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
				'transform' => true,
				'opacity'   => true,
			],
			'defs' => [],
			'linearGradient' => [
				'id'            => true,
				'x1'            => true,
				'y1'            => true,
				'x2'            => true,
				'y2'            => true,
				'gradientUnits' => true,
			],
			'radialGradient' => [
				'id'            => true,
				'cx'            => true,
				'cy'            => true,
				'r'             => true,
				'fx'            => true,
				'fy'            => true,
				'gradientUnits' => true,
			],
			'stop' => [
				'offset'       => true,
				'stop-color'   => true,
				'stop-opacity' => true,
			],
		];

		return wp_kses( $svg_code, $allowed_tags );
	}

	/**
	 * Render output on frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', [
			'eas-svg-icon-wrap',
			'eas-view-' . esc_attr( $settings['view'] ),
			'eas-mode-' . esc_attr( $settings['svg_color_mode'] ),
		] );

		if ( 'default' !== $settings['view'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'eas-shape-' . esc_attr( $settings['shape'] ) );
		}

		$this->add_render_attribute( 'container', 'class', 'eas-svg-icon-container' );

		if ( ! empty( $settings['hover_animation'] ) ) {
			$this->add_render_attribute( 'container', 'class', 'elementor-animation-' . esc_attr( $settings['hover_animation'] ) );
		}

		$has_link = ! empty( $settings['link']['url'] );
		if ( $has_link ) {
			$this->add_link_attributes( 'container', $settings['link'] );
		}

		$tag = $has_link ? 'a' : 'div';
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<<?php echo esc_html( $tag ); ?> <?php $this->print_render_attribute_string( 'container' ); ?>>
				<?php
				if ( 'library' === $settings['icon_source'] ) {
					if ( ! empty( $settings['selected_icon']['value'] ) ) {
						\Elementor\Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
					}
				} elseif ( 'svg' === $settings['icon_source'] ) {
					if ( ! empty( $settings['svg_code'] ) ) {
						$cleaned = $this->clean_svg( $settings['svg_code'] );
						echo $this->sanitize_svg_output( $cleaned ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				} elseif ( 'api' === $settings['icon_source'] ) {
					$svg_code = ! empty( $settings['iconify_svg_code'] ) ? $settings['iconify_svg_code'] : '';
					if ( empty( $svg_code ) && ! empty( $settings['iconify_icon_name'] ) ) {
						$icon_url = 'https://api.iconify.design/' . str_replace( ':', '/', $settings['iconify_icon_name'] ) . '.svg';
						$response = wp_remote_get( $icon_url );
						if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
							$svg_code = wp_remote_retrieve_body( $response );
						}
					}
					if ( ! empty( $svg_code ) ) {
						$cleaned = $this->clean_svg( $svg_code );
						echo $this->sanitize_svg_output( $cleaned ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
				?>
			</<?php echo esc_html( $tag ); ?>>
		</div>
		<?php
	}

	/**
	 * Editor live rendering.
	 */
	protected function content_template() {
		?>
		<#
		var cleanSvg = function( svgCode ) {
			if ( ! svgCode ) return '';
			svgCode = svgCode.trim();
			var svgStart = svgCode.toLowerCase().indexOf('<svg');
			if ( svgStart === -1 ) {
				return svgCode;
			}
			svgCode = svgCode.substring( svgStart );
			try {
				var parser = new DOMParser();
				var doc = parser.parseFromString( svgCode, 'image/svg+xml' );
				var svgEl = doc.querySelector( 'svg' );
				if ( svgEl ) {
					var allowedTags = [ 'svg', 'g', 'path', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'rect', 'defs', 'lineargradient', 'radialgradient', 'stop', 'clippath', 'mask', 'title', 'desc' ];
					Array.prototype.slice.call( svgEl.querySelectorAll( '*' ) ).forEach( function( node ) {
						if ( allowedTags.indexOf( node.tagName.toLowerCase() ) === -1 ) {
							node.remove();
							return;
						}
						Array.prototype.slice.call( node.attributes ).forEach( function( attribute ) {
							var name = attribute.name.toLowerCase();
							if ( name.indexOf( 'on' ) === 0 || name === 'href' || name === 'xlink:href' || name === 'src' || name === 'style' ) {
								node.removeAttribute( attribute.name );
							}
						} );
					} );
					svgEl.removeAttribute( 'width' );
					svgEl.removeAttribute( 'height' );
					svgEl.removeAttribute( 'style' );
					Array.prototype.slice.call( svgEl.attributes ).forEach( function( attribute ) {
						var name = attribute.name.toLowerCase();
						if ( name.indexOf( 'on' ) === 0 || name === 'href' || name === 'xlink:href' || name === 'src' ) {
							svgEl.removeAttribute( attribute.name );
						}
					} );
					return svgEl.outerHTML;
				}
			} catch (e) {}
			return svgCode;
		};

		var iconHtml = '';
		if ( settings.icon_source === 'library' && settings.selected_icon && settings.selected_icon.value ) {
			var isSvg = settings.selected_icon.library === 'svg';
			if ( isSvg ) {
				iconHtml = '<img src="' + settings.selected_icon.value.url + '" style="width: var(--eas-svg-size, 50px); height: var(--eas-svg-size, 50px);" />';
			} else {
				iconHtml = '<i class="' + settings.selected_icon.value + '"></i>';
			}
		} else if ( settings.icon_source === 'svg' && settings.svg_code ) {
			iconHtml = cleanSvg( settings.svg_code );
		} else if ( settings.icon_source === 'api' && ( settings.iconify_svg_code || settings.iconify_icon_name ) ) {
			if ( settings.iconify_svg_code ) {
				iconHtml = cleanSvg( settings.iconify_svg_code );
			} else if ( settings.iconify_icon_name ) {
				var iconUrl = 'https://api.iconify.design/' + settings.iconify_icon_name.replace(':', '/') + '.svg';
				iconHtml = '<img src="' + iconUrl + '" style="width: var(--eas-svg-size, 50px); height: var(--eas-svg-size, 50px);" alt="" />';
			}
		}

		var wrapperClass = 'eas-svg-icon-wrap eas-view-' + settings.view + ' eas-mode-' + settings.svg_color_mode;
		if ( settings.view !== 'default' ) {
			wrapperClass += ' eas-shape-' + settings.shape;
		}

		var hoverAnimationClass = settings.hover_animation ? ' elementor-animation-' + settings.hover_animation : '';
		var hasLink = settings.link && settings.link.url;
		#>
		<div class="{{ wrapperClass }}">
			<# if ( hasLink ) { 
				var linkTarget = settings.link.is_external ? ' target="_blank"' : '';
				var linkNofollow = settings.link.nofollow ? ' rel="nofollow"' : '';
			#>
				<a href="{{ settings.link.url }}"{{{ linkTarget }}}{{{ linkNofollow }}} class="eas-svg-icon-container{{{ hoverAnimationClass }}}">
					{{{ iconHtml }}}
				</a>
			<# } else { #>
				<div class="eas-svg-icon-container{{{ hoverAnimationClass }}}">
					{{{ iconHtml }}}
				</div>
			<# } #>
		</div>
		<?php
	}
}
