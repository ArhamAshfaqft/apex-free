<?php
/**
 * Elementor-first conversational funnel builder.
 *
 * @package ApexAddonsForElementor
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;
use ArhamAshfaq\ApexAddonsForElementor\Free\Funnel_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Conversational_Funnel_Widget extends Widget_Base {

	public function get_name() {
		return 'eas-conversational-funnel';
	}

	public function get_title() {
		return esc_html__( 'Conversational Funnel', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return array( 'elementor-addon-suite-category' );
	}

	public function get_keywords() {
		return array( 'funnel', 'form', 'lead', 'conversation', 'chat', 'quiz', 'multi-step' );
	}

	public function get_script_depends() {
		return array( 'apexadfo-conversational-funnel-js' );
	}

	public function get_style_depends() {
		return array( 'apexadfo-conversational-funnel-css' );
	}

	protected function register_controls() {
		$this->register_step_field_builder_controls();
		$this->register_notification_controls();
		$this->register_interface_controls();
		$this->register_routing_controls();
		do_action( 'apexadfo_funnel_register_controls', $this );
		$this->register_panel_style();
		$this->register_typography_style();
		$this->register_choice_style();
		$this->register_input_style();
		$this->register_button_style();
		$this->register_navigation_style();
		$this->register_progress_style();
		$this->register_launcher_style();
		$this->register_modal_style();
		$this->register_message_style();
	}

	private function register_routing_controls() {
		$routes = new Repeater();
		$routes->add_control( 'route_from', array( 'label' => esc_html__( 'Watch Field ID', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'placeholder' => 'service_type' ) );
		$routes->add_control( 'route_answer', array( 'label' => esc_html__( 'When Answer Equals', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'description' => esc_html__( 'Use the choice value after the | symbol. Leave empty to create the default route.', 'apex-addons-for-elementor' ), 'placeholder' => 'website' ) );
		$routes->add_control( 'route_to', array( 'label' => esc_html__( 'Go To Step ID', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'placeholder' => 'budget' ) );
		$routes->add_control( 'route_score', array( 'label' => esc_html__( 'Add Lead Score', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::NUMBER, 'default' => 0 ) );

		$this->start_controls_section( 'section_conversational_funnel_routing', array( 'label' => esc_html__( 'Conditional Routing & Lead Scoring', 'apex-addons-for-elementor' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'funnel_routing_help', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'Choose a Field ID to watch, the answer value to match, and the destination Step ID. Every route is validated before use.', 'apex-addons-for-elementor' ), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-info' ) );
		$this->add_control( 'funnel_routes', array( 'label' => esc_html__( 'Routing Rules', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::REPEATER, 'fields' => $routes->get_controls(), 'title_field' => '{{{ route_from }}} → {{{ route_to }}}' ) );
		$this->end_controls_section();
	}

	private function register_step_field_builder_controls() {
		$this->start_controls_section( 'section_funnel', array( 'label' => esc_html__( 'Funnel Builder', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'funnel_name',
			array(
				'label'       => esc_html__( 'Funnel Name', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'New Lead Funnel', 'apex-addons-for-elementor' ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'funnel_builder_help',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Step is a blank screen break. Add Heading, Text, fields and a Continue Button beneath it in the exact order they should appear. Add another Step to begin the next screen.', 'apex-addons-for-elementor' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);

		$repeater    = new Repeater();
		$field_types = array(
			'step'       => esc_html__( 'Step Break', 'apex-addons-for-elementor' ),
			'heading'    => esc_html__( 'Heading', 'apex-addons-for-elementor' ),
			'description'=> esc_html__( 'Text / Description', 'apex-addons-for-elementor' ),
			'button'     => esc_html__( 'Continue / Submit Button', 'apex-addons-for-elementor' ),
			'result'     => esc_html__( 'Success Result', 'apex-addons-for-elementor' ),
			'text'       => esc_html__( 'Text', 'apex-addons-for-elementor' ),
			'email'      => esc_html__( 'Email', 'apex-addons-for-elementor' ),
			'tel'        => esc_html__( 'Phone', 'apex-addons-for-elementor' ),
			'textarea'   => esc_html__( 'Textarea', 'apex-addons-for-elementor' ),
			'select'     => esc_html__( 'Select', 'apex-addons-for-elementor' ),
			'radio'      => esc_html__( 'Radio Choices', 'apex-addons-for-elementor' ),
			'checkbox'   => esc_html__( 'Checkbox Choices', 'apex-addons-for-elementor' ),
			'acceptance' => esc_html__( 'Acceptance', 'apex-addons-for-elementor' ),
			'number'     => esc_html__( 'Number', 'apex-addons-for-elementor' ),
			'date'       => esc_html__( 'Date', 'apex-addons-for-elementor' ),
			'time'       => esc_html__( 'Time', 'apex-addons-for-elementor' ),
			'html'       => esc_html__( 'HTML / Message', 'apex-addons-for-elementor' ),
			'hidden'     => esc_html__( 'Hidden Value', 'apex-addons-for-elementor' ),
		);
		$repeater->add_control(
			'type',
			array(
				'label'   => esc_html__( 'Item Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => $field_types,
			)
		);
		$repeater->add_control(
			'step_id',
			array(
				'label'       => esc_html__( 'Step ID', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'step-id',
				'description' => esc_html__( 'Unique ID used as a routing destination.', 'apex-addons-for-elementor' ),
				'condition'   => array( 'type' => 'step' ),
			)
		);
		$repeater->add_control(
			'content_text',
			array(
				'label'       => esc_html__( 'Content', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => esc_html__( 'Add your content', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => array( 'type' => array( 'heading', 'description' ) ),
			)
		);
		$repeater->add_control(
			'heading_tag',
			array(
				'label'     => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'div' => 'DIV' ),
				'condition' => array( 'type' => 'heading' ),
			)
		);
		$repeater->add_control(
			'button_text',
			array(
				'label'       => esc_html__( 'Button Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Continue', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => array( 'type' => 'button' ),
			)
		);
		$repeater->add_control(
			'result_icon',
			array(
				'label'        => esc_html__( 'Show Success Icon', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'type' => 'result' ),
			)
		);
		$repeater->add_control(
			'field_id',
			array(
				'label'       => esc_html__( 'Field ID', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'field-id',
				'description' => esc_html__( 'Unique ID used by routing rules and saved lead data.', 'apex-addons-for-elementor' ),
				'condition'   => array( 'type!' => array( 'step', 'heading', 'description', 'button', 'result', 'html' ) ),
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'     => esc_html__( 'Field Label', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Field label', 'apex-addons-for-elementor' ),
				'condition' => array( 'type!' => array( 'step', 'heading', 'description', 'button', 'result', 'html', 'hidden' ) ),
			)
		);
		$repeater->add_control(
			'placeholder',
			array(
				'label'     => esc_html__( 'Placeholder', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'type' => array( 'text', 'email', 'tel', 'textarea', 'select', 'acceptance', 'number' ) ),
			)
		);
		$repeater->add_control(
			'options',
			array(
				'label'       => esc_html__( 'Options', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => "First option | first\nSecond option | second",
				'description' => esc_html__( 'One option per line. Add a stable routing value after |.', 'apex-addons-for-elementor' ),
				'condition'   => array( 'type' => array( 'select', 'radio', 'checkbox' ) ),
			)
		);
		$repeater->add_control(
			'required',
			array(
				'label'        => esc_html__( 'Required', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array( 'type!' => array( 'step', 'heading', 'description', 'button', 'result', 'html', 'hidden' ) ),
			)
		);
		$repeater->add_control(
			'default_value',
			array(
				'label'     => esc_html__( 'Default Value', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'type!' => array( 'step', 'heading', 'description', 'button', 'result', 'radio', 'checkbox', 'html' ) ),
			)
		);
		$repeater->add_control(
			'html_content',
			array(
				'label'     => esc_html__( 'Content', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::WYSIWYG,
				'condition' => array( 'type' => 'html' ),
			)
		);
		$width_options = array(
			'100' => '100%',
			'80'  => '80%',
			'75'  => '75%',
			'66'  => '66%',
			'60'  => '60%',
			'50'  => '50%',
			'40'  => '40%',
			'33'  => '33%',
			'25'  => '25%',
			'20'  => '20%',
		);
		$repeater->add_control(
			'width',
			array(
				'label'     => esc_html__( 'Desktop Width', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '100',
				'options'   => $width_options,
				'condition' => array( 'type!' => array( 'step', 'hidden' ) ),
			)
		);
		$repeater->add_control(
			'width_tablet',
			array(
				'label'     => esc_html__( 'Tablet Width', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '100',
				'options'   => $width_options,
				'condition' => array( 'type!' => array( 'step', 'hidden' ) ),
			)
		);
		$repeater->add_control(
			'width_mobile',
			array(
				'label'     => esc_html__( 'Mobile Width', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '100',
				'options'   => $width_options,
				'condition' => array( 'type!' => array( 'step', 'hidden' ) ),
			)
		);
		$this->add_control(
			'funnel_fields',
			array(
				'label'       => esc_html__( 'Steps & Fields', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '<# if ( "step" === type ) { #>Step Break: {{{ step_id }}}<# } else if ( "heading" === type || "description" === type ) { #>{{{ content_text || type }}} ({{{ type }}})<# } else if ( "button" === type ) { #>{{{ button_text || "Continue" }}} (button)<# } else if ( "result" === type ) { #>Success Result<# } else { #>{{{ label || type }}} ({{{ type }}})<# } #>',
				'default'     => array(
					array( 'type' => 'step', 'step_id' => 'welcome' ),
					array( 'type' => 'heading', 'content_text' => esc_html__( 'How can we help?', 'apex-addons-for-elementor' ), 'heading_tag' => 'h2' ),
					array( 'type' => 'description', 'content_text' => esc_html__( 'Answer a few quick questions and we will point you in the right direction.', 'apex-addons-for-elementor' ) ),
					array( 'type' => 'button', 'button_text' => esc_html__( 'Get started', 'apex-addons-for-elementor' ) ),
					array( 'type' => 'step', 'step_id' => 'interest' ),
					array( 'type' => 'heading', 'content_text' => esc_html__( 'What are you interested in?', 'apex-addons-for-elementor' ), 'heading_tag' => 'h2' ),
					array(
						'type'         => 'radio',
						'field_id'     => 'interest_type',
						'label'        => esc_html__( 'Choose one', 'apex-addons-for-elementor' ),
						'options'      => "Request a quote | quote\nAsk a question | question",
						'required'     => 'yes',
						'width'        => '100',
						'width_tablet' => '100',
						'width_mobile' => '100',
					),
					array( 'type' => 'button', 'button_text' => esc_html__( 'Continue', 'apex-addons-for-elementor' ) ),
					array( 'type' => 'step', 'step_id' => 'contact' ),
					array( 'type' => 'heading', 'content_text' => esc_html__( 'Where should we send your response?', 'apex-addons-for-elementor' ), 'heading_tag' => 'h2' ),
					array( 'type' => 'description', 'content_text' => esc_html__( 'Your information is only used to respond to this request.', 'apex-addons-for-elementor' ) ),
					array(
						'type'         => 'text',
						'field_id'     => 'name',
						'label'        => esc_html__( 'Name', 'apex-addons-for-elementor' ),
						'required'     => 'yes',
						'width'        => '50',
						'width_tablet' => '50',
						'width_mobile' => '100',
					),
					array(
						'type'         => 'email',
						'field_id'     => 'email',
						'label'        => esc_html__( 'Email', 'apex-addons-for-elementor' ),
						'required'     => 'yes',
						'width'        => '50',
						'width_tablet' => '50',
						'width_mobile' => '100',
					),
					array(
						'type'         => 'tel',
						'field_id'     => 'phone',
						'label'        => esc_html__( 'Phone', 'apex-addons-for-elementor' ),
						'width'        => '100',
						'width_tablet' => '100',
						'width_mobile' => '100',
					),
					array( 'type' => 'button', 'button_text' => esc_html__( 'Send request', 'apex-addons-for-elementor' ) ),
					array( 'type' => 'step', 'step_id' => 'complete' ),
					array( 'type' => 'result', 'result_icon' => 'yes' ),
					array( 'type' => 'heading', 'content_text' => esc_html__( 'You are all set', 'apex-addons-for-elementor' ), 'heading_tag' => 'h2' ),
					array( 'type' => 'description', 'content_text' => esc_html__( 'Thanks—your request has been received.', 'apex-addons-for-elementor' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->register_presentation_controls();
	}

	private function register_presentation_controls() {
		$this->start_controls_section( 'section_presentation', array( 'label' => esc_html__( 'Presentation & Behavior', 'apex-addons-for-elementor' ) ) );
		$modes = array(
			'inline'     => esc_html__( 'Inline', 'apex-addons-for-elementor' ),
			'fullscreen' => esc_html__( 'Full Screen', 'apex-addons-for-elementor' ),
			'floating'   => esc_html__( 'Floating Launcher', 'apex-addons-for-elementor' ),
			'modal'      => esc_html__( 'Button Modal', 'apex-addons-for-elementor' ),
		);
		$this->add_control(
			'display_mode',
			array(
				'label'   => esc_html__( 'Display Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => apply_filters( 'apexadfo_funnel_display_modes', $modes ),
			)
		);
		$this->add_control(
			'launcher_label',
			array(
				'label'     => esc_html__( 'Launcher Label', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Start', 'apex-addons-for-elementor' ),
				'condition' => array( 'display_mode!' => 'inline' ),
			)
		);
		$this->add_control(
			'floating_position',
			array(
				'label'     => esc_html__( 'Screen Position', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'right',
				'options'   => array(
					'right' => esc_html__( 'Bottom Right', 'apex-addons-for-elementor' ),
					'left'  => esc_html__( 'Bottom Left', 'apex-addons-for-elementor' ),
				),
				'condition' => array( 'display_mode' => 'floating' ),
			)
		);
		$this->add_control(
			'show_progress',
			array(
				'label'        => esc_html__( 'Show Progress', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_step_count',
			array(
				'label'        => esc_html__( 'Show Step Count', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'allow_restart',
			array(
				'label'        => esc_html__( 'Allow Restart', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'transition_style',
			array(
				'label'   => esc_html__( 'Step Transition', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => array(
					'slide' => esc_html__( 'Slide Up', 'apex-addons-for-elementor' ),
					'fade'  => esc_html__( 'Fade', 'apex-addons-for-elementor' ),
					'scale' => esc_html__( 'Scale', 'apex-addons-for-elementor' ),
					'none'  => esc_html__( 'None', 'apex-addons-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'transition_duration',
			array(
				'label'   => esc_html__( 'Transition Duration (ms)', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 240,
				'min'     => 0,
				'max'     => 1500,
				'step'    => 10,
			)
		);
		$this->end_controls_section();
	}

	private function register_builder_controls() {
		$this->start_controls_section( 'section_funnel', array( 'label' => esc_html__( 'Funnel Builder', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'funnel_name',
			array(
				'label'       => esc_html__( 'Funnel Name', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'New Lead Funnel', 'apex-addons-for-elementor' ),
				'label_block' => true,
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'step_id',
			array(
				'label'       => esc_html__( 'Step ID', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'step-id',
				'description' => esc_html__( 'Use a unique short ID containing letters, numbers, hyphens or underscores.', 'apex-addons-for-elementor' ),
			)
		);
		$repeater->add_control(
			'step_type',
			array(
				'label'   => esc_html__( 'Step Type', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => array(
					'welcome'  => esc_html__( 'Welcome / Message', 'apex-addons-for-elementor' ),
					'single'   => esc_html__( 'Single Choice', 'apex-addons-for-elementor' ),
					'multiple' => esc_html__( 'Multiple Choice', 'apex-addons-for-elementor' ),
					'text'     => esc_html__( 'Short Text', 'apex-addons-for-elementor' ),
					'email'    => esc_html__( 'Email', 'apex-addons-for-elementor' ),
					'phone'    => esc_html__( 'Phone', 'apex-addons-for-elementor' ),
					'number'   => esc_html__( 'Number', 'apex-addons-for-elementor' ),
					'textarea' => esc_html__( 'Long Text', 'apex-addons-for-elementor' ),
					'date'     => esc_html__( 'Date', 'apex-addons-for-elementor' ),
					'time'     => esc_html__( 'Time', 'apex-addons-for-elementor' ),
					'contact'  => esc_html__( 'Contact Details', 'apex-addons-for-elementor' ),
					'success'  => esc_html__( 'Result / Success', 'apex-addons-for-elementor' ),
				),
			)
		);
		$repeater->add_control(
			'step_title',
			array(
				'label'       => esc_html__( 'Question / Heading', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => esc_html__( 'Your question goes here', 'apex-addons-for-elementor' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'step_description',
			array(
				'label'       => esc_html__( 'Supporting Text', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'step_choices',
			array(
				'label'       => esc_html__( 'Answer Choices', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'default'     => "First option | first\nSecond option | second",
				'description' => esc_html__( 'Enter one choice per line. You may add a stable value after | for routing, for example: Request a quote | quote', 'apex-addons-for-elementor' ),
				'condition'   => array( 'step_type' => array( 'single', 'multiple' ) ),
			)
		);
		$repeater->add_control(
			'step_placeholder',
			array(
				'label'     => esc_html__( 'Placeholder', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => array( 'step_type' => array( 'text', 'email', 'phone', 'number', 'textarea', 'date', 'time' ) ),
			)
		);
		$repeater->add_control(
			'step_required',
			array(
				'label'        => esc_html__( 'Required', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'step_type!' => array( 'welcome', 'success' ) ),
			)
		);
		$repeater->add_control(
			'step_button_label',
			array(
				'label'       => esc_html__( 'Button Label', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Use the global label', 'apex-addons-for-elementor' ),
				'condition'   => array( 'step_type!' => array( 'single', 'success' ) ),
			)
		);

		$this->add_control(
			'funnel_steps',
			array(
				'label'       => esc_html__( 'Funnel Steps', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ step_title }}} ({{{ step_type }}})',
				'default'     => array(
					array(
						'step_id'           => 'welcome',
						'step_type'         => 'welcome',
						'step_title'        => esc_html__( 'How can we help?', 'apex-addons-for-elementor' ),
						'step_description'  => esc_html__( 'Answer a few quick questions and we will point you in the right direction.', 'apex-addons-for-elementor' ),
						'step_button_label' => esc_html__( 'Get started', 'apex-addons-for-elementor' ),
						'step_required'     => '',
					),
					array(
						'step_id'       => 'interest',
						'step_type'     => 'single',
						'step_title'    => esc_html__( 'What are you interested in?', 'apex-addons-for-elementor' ),
						'step_choices'  => "Request a quote | quote\nAsk a question | question",
						'step_required' => 'yes',
					),
					array(
						'step_id'           => 'contact',
						'step_type'         => 'contact',
						'step_title'        => esc_html__( 'Where should we send your response?', 'apex-addons-for-elementor' ),
						'step_description'  => esc_html__( 'Your information is only used to respond to this request.', 'apex-addons-for-elementor' ),
						'step_button_label' => esc_html__( 'Send request', 'apex-addons-for-elementor' ),
						'step_required'     => 'yes',
					),
					array(
						'step_id'          => 'complete',
						'step_type'        => 'success',
						'step_title'       => esc_html__( 'You are all set', 'apex-addons-for-elementor' ),
						'step_description' => esc_html__( 'Thanks—your request has been received.', 'apex-addons-for-elementor' ),
						'step_required'    => '',
					),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_presentation', array( 'label' => esc_html__( 'Presentation & Behavior', 'apex-addons-for-elementor' ) ) );
		$modes = array(
			'inline'     => esc_html__( 'Inline', 'apex-addons-for-elementor' ),
			'fullscreen' => esc_html__( 'Full Screen', 'apex-addons-for-elementor' ),
			'floating'   => esc_html__( 'Floating Launcher', 'apex-addons-for-elementor' ),
			'modal'      => esc_html__( 'Button Modal', 'apex-addons-for-elementor' ),
		);
		$this->add_control(
			'display_mode',
			array(
				'label'   => esc_html__( 'Display Mode', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'inline',
				'options' => apply_filters( 'apexadfo_funnel_display_modes', $modes ),
			)
		);
		$this->add_control(
			'launcher_label',
			array(
				'label'     => esc_html__( 'Launcher Label', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Start', 'apex-addons-for-elementor' ),
				'condition' => array( 'display_mode!' => 'inline' ),
			)
		);
		$this->add_control(
			'floating_position',
			array(
				'label'     => esc_html__( 'Screen Position', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'right',
				'options'   => array(
					'right' => esc_html__( 'Bottom Right', 'apex-addons-for-elementor' ),
					'left'  => esc_html__( 'Bottom Left', 'apex-addons-for-elementor' ),
				),
				'condition' => array( 'display_mode' => 'floating' ),
			)
		);
		$this->add_control(
			'show_progress',
			array(
				'label'        => esc_html__( 'Show Progress', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'show_step_count',
			array(
				'label'        => esc_html__( 'Show Step Count', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'allow_restart',
			array(
				'label'        => esc_html__( 'Allow Restart', 'apex-addons-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'transition_style',
			array(
				'label'   => esc_html__( 'Step Transition', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => array(
					'slide' => esc_html__( 'Slide Up', 'apex-addons-for-elementor' ),
					'fade'  => esc_html__( 'Fade', 'apex-addons-for-elementor' ),
					'scale' => esc_html__( 'Scale', 'apex-addons-for-elementor' ),
					'none'  => esc_html__( 'None', 'apex-addons-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'transition_duration',
			array(
				'label'   => esc_html__( 'Transition Duration (ms)', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 240,
				'min'     => 0,
				'max'     => 1500,
				'step'    => 10,
			)
		);
		$this->end_controls_section();
	}

	private function register_notification_controls() {
		$this->start_controls_section( 'section_notifications', array( 'label' => esc_html__( 'Lead Capture & Email', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'recipient_email',
			array(
				'label'       => esc_html__( 'Send Leads To', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'input_type'  => 'email',
				'default'     => get_option( 'admin_email' ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'email_subject',
			array(
				'label'       => esc_html__( 'Email Subject', 'apex-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'New funnel lead: {funnel}', 'apex-addons-for-elementor' ),
				'description' => esc_html__( 'Use {funnel} to insert the Funnel Name.', 'apex-addons-for-elementor' ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'success_message',
			array(
				'label'   => esc_html__( 'Fallback Success Message', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Thank you. Your details have been received.', 'apex-addons-for-elementor' ),
			)
		);
		$this->add_control(
			'lead_storage_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Every successful submission is stored securely under Apex Addons → Funnel Leads and can be exported as CSV.', 'apex-addons-for-elementor' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			)
		);
		$this->end_controls_section();
	}

	private function register_interface_controls() {
		$this->start_controls_section( 'section_labels', array( 'label' => esc_html__( 'Interface Text', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'continue_label',
			array(
				'label'   => esc_html__( 'Continue', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Continue', 'apex-addons-for-elementor' ),
			)
		);
		$this->add_control(
			'back_label',
			array(
				'label'   => esc_html__( 'Back', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Back', 'apex-addons-for-elementor' ),
			)
		);
		$this->add_control(
			'submit_label',
			array(
				'label'   => esc_html__( 'Submit', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Send request', 'apex-addons-for-elementor' ),
			)
		);
		$this->add_control(
			'restart_label',
			array(
				'label'   => esc_html__( 'Restart', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Start again', 'apex-addons-for-elementor' ),
			)
		);
		$this->add_control(
			'name_placeholder',
			array(
				'label'   => esc_html__( 'Name Placeholder', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Name', 'apex-addons-for-elementor' ),
			)
		);
		$this->add_control(
			'email_placeholder',
			array(
				'label'   => esc_html__( 'Email Placeholder', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Email address', 'apex-addons-for-elementor' ),
			)
		);
		$this->add_control(
			'phone_placeholder',
			array(
				'label'   => esc_html__( 'Phone Placeholder', 'apex-addons-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Phone number', 'apex-addons-for-elementor' ),
			)
		);
		$this->end_controls_section();
	}

	private function register_panel_style() {
		$this->start_controls_section(
			'style_panel',
			array(
				'label' => esc_html__( 'Panel', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'panel_width',
			array(
				'label'      => esc_html__( 'Maximum Width', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 280,
						'max' => 1200,
					),
					'%'  => array(
						'min' => 20,
						'max' => 100,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .eas-funnel-mode-inline .eas-funnel-panel' => 'max-width:{{SIZE}}{{UNIT}};margin-left:auto;margin-right:auto;' ),
			)
		);
		$this->add_responsive_control(
			'panel_min_height',
			array(
				'label'      => esc_html__( 'Minimum Height', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 260,
						'max' => 1000,
					),
					'vh' => array(
						'min' => 30,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 460,
				),
				'selectors'  => array( '{{WRAPPER}} .eas-funnel-panel' => 'min-height:{{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'panel_padding',
			array(
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'default'    => array(
					'top'      => 36,
					'right'    => 36,
					'bottom'   => 30,
					'left'     => 36,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'selectors'  => array( '{{WRAPPER}} .eas-funnel-panel' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'panel_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .eas-funnel-panel',
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'panel_border',
				'selector' => '{{WRAPPER}} .eas-funnel-panel',
			)
		);
		$this->add_responsive_control(
			'panel_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'apex-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => 24,
					'right'    => 24,
					'bottom'   => 24,
					'left'     => 24,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array( '{{WRAPPER}} .eas-funnel-panel' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'panel_shadow',
				'selector' => '{{WRAPPER}} .eas-funnel-panel',
			)
		);
		$this->end_controls_section();
	}

	private function register_typography_style() {
		$this->start_controls_section(
			'style_typography',
			array(
				'label' => esc_html__( 'Question & Description', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'question_color',
			array(
				'label'     => esc_html__( 'Question Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-question' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'question_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-question',
			)
		);
		$this->add_responsive_control(
			'question_spacing',
			array(
				'label'     => esc_html__( 'Question Spacing', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-question' => 'margin-bottom:{{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'description_color',
			array(
				'label'     => esc_html__( 'Description Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-description' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-description',
			)
		);
		$this->end_controls_section();
	}

	private function register_choice_style() {
		$this->start_controls_section(
			'style_choices',
			array(
				'label' => esc_html__( 'Choice Cards', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'choice_columns',
			array(
				'label'          => esc_html__( 'Columns', 'apex-addons-for-elementor' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '2',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'selectors'      => array( '{{WRAPPER}} .eas-funnel-choices-grid' => 'grid-template-columns:repeat({{VALUE}},minmax(0,1fr));' ),
			)
		);
		$this->add_responsive_control(
			'choice_gap',
			array(
				'label'     => esc_html__( 'Gap', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'default'   => array( 'size' => 10 ),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choices-grid' => 'gap:{{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'choice_padding',
			array(
				'label'     => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'choice_indicator_size',
			array(
				'label'     => esc_html__( 'Radio / Checkbox Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 12, 'max' => 40 ) ),
				'default'   => array( 'size' => 20 ),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-option-indicator' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};flex-basis:{{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'choice_indicator_border',
			array(
				'label'     => esc_html__( 'Radio / Checkbox Border', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#94a3b8',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-option-indicator' => 'border-color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'choice_indicator_selected',
			array(
				'label'     => esc_html__( 'Selected Indicator Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array(
					'{{WRAPPER}} .eas-funnel-option-radio .eas-funnel-option-control:checked + .eas-funnel-option-indicator::after' => 'background-color:{{VALUE}};',
					'{{WRAPPER}} .eas-funnel-option-checkbox .eas-funnel-option-control:checked + .eas-funnel-option-indicator' => 'background-color:{{VALUE}};border-color:{{VALUE}};',
				),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'choice_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-choice',
			)
		);
		$this->start_controls_tabs( 'choice_tabs' );
		$this->start_controls_tab( 'choice_normal', array( 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'choice_color',
			array(
				'label'     => esc_html__( 'Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'choice_bg',
			array(
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f8fafc',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'choice_normal_border_color',
			array(
				'label'     => esc_html__( 'Border', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice' => 'border-color:{{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'choice_hover', array( 'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'choice_hover_color',
			array(
				'label'     => esc_html__( 'Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice:hover' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'choice_hover_bg',
			array(
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice:hover' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'choice_selected', array( 'label' => esc_html__( 'Selected', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'choice_selected_color',
			array(
				'label'     => esc_html__( 'Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice.is-selected' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'choice_selected_bg',
			array(
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice.is-selected' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'choice_selected_border',
			array(
				'label'     => esc_html__( 'Border', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice.is-selected' => 'border-color:{{VALUE}};box-shadow:inset 0 0 0 1px {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'choice_border',
				'selector'  => '{{WRAPPER}} .eas-funnel-choice',
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'choice_radius',
			array(
				'label'     => esc_html__( 'Radius', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-choice' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	private function register_input_style() {
		$this->start_controls_section(
			'style_inputs',
			array(
				'label' => esc_html__( 'Inputs', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'field_column_gap',
			array(
				'label'     => esc_html__( 'Column Gap', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'   => array( 'size' => 10 ),
				'selectors' => array(
					'{{WRAPPER}} .eas-funnel-field-grid' => 'margin-left:calc({{SIZE}}{{UNIT}} / -2);margin-right:calc({{SIZE}}{{UNIT}} / -2);',
					'{{WRAPPER}} .eas-funnel-field-wrap' => 'padding-left:calc({{SIZE}}{{UNIT}} / 2);padding-right:calc({{SIZE}}{{UNIT}} / 2);',
				),
			)
		);
		$this->add_responsive_control(
			'field_row_gap',
			array(
				'label'     => esc_html__( 'Row Gap', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'   => array( 'size' => 10 ),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-field-wrap' => 'padding-top:calc({{SIZE}}{{UNIT}} / 2);padding-bottom:calc({{SIZE}}{{UNIT}} / 2);' ),
			)
		);
		$this->add_control(
			'field_label_heading',
			array(
				'label'     => esc_html__( 'Field Labels', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'field_label_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-field-label',
			)
		);
		$this->add_control(
			'field_label_color',
			array(
				'label'     => esc_html__( 'Label Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-field-label' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'required_mark_color',
			array(
				'label'     => esc_html__( 'Required Mark Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#dc2626',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-required-mark' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'input_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-input',
			)
		);
		$this->add_control(
			'input_color',
			array(
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-input' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'input_placeholder_color',
			array(
				'label'     => esc_html__( 'Placeholder Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#94a3b8',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-input::placeholder' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'input_bg',
			array(
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-input' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'input_padding',
			array(
				'label'     => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-input' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'input_border',
				'selector' => '{{WRAPPER}} .eas-funnel-input',
			)
		);
		$this->add_control(
			'input_focus_border',
			array(
				'label'     => esc_html__( 'Focus Border Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array( '{{WRAPPER}} .eas-conversational-funnel' => '--eas-input-focus-color:{{VALUE}};', '{{WRAPPER}} .eas-funnel-input:focus,{{WRAPPER}} .eas-funnel-input:focus-visible' => 'border-color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'input_focus_ring_size',
			array(
				'label'     => esc_html__( 'Focus Ring Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 12 ) ),
				'default'   => array( 'size' => 3 ),
				'selectors' => array( '{{WRAPPER}} .eas-conversational-funnel' => '--eas-input-focus-size:{{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'input_radius',
			array(
				'label'     => esc_html__( 'Radius', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-input' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	private function register_button_style() {
		$this->start_controls_section(
			'style_primary_button',
			array(
				'label' => esc_html__( 'Continue & Submit Button', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'button_alignment',
			array(
				'label'                => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'    => array(
						'title' => esc_html__( 'Left', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center'  => array(
						'title' => esc_html__( 'Center', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'   => array(
						'title' => esc_html__( 'Right', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					),
					'stretch' => array(
						'title' => esc_html__( 'Stretch', 'apex-addons-for-elementor' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'selectors_dictionary' => array(
					'left'    => 'justify-content:flex-start;',
					'center'  => 'justify-content:center;',
					'right'   => 'justify-content:flex-end;',
					'stretch' => 'justify-content:stretch;--eas-button-width:100%;',
				),
				'selectors'            => array( '{{WRAPPER}} .eas-funnel-action-row' => '{{VALUE}}' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-button',
			)
		);
		$this->add_responsive_control(
			'button_padding',
			array(
				'label'     => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-button' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->start_controls_tabs( 'button_tabs' );
		$this->start_controls_tab( 'button_normal', array( 'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-button' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_bg',
			array(
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-button' => 'background-color:{{VALUE}};border-color:{{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'button_hover', array( 'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ) ) );
		$this->add_control(
			'button_hover_color',
			array(
				'label'     => esc_html__( 'Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-button:hover' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_hover_bg',
			array(
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-button:hover' => 'background-color:{{VALUE}};border-color:{{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_responsive_control(
			'button_radius',
			array(
				'label'     => esc_html__( 'Radius', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-button' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_shadow',
				'selector' => '{{WRAPPER}} .eas-funnel-button',
			)
		);
		$this->end_controls_section();
	}

	private function register_navigation_style() {
		$this->start_controls_section(
			'style_navigation',
			array(
				'label' => esc_html__( 'Back, Restart & Close', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'navigation_color',
			array(
				'label'     => esc_html__( 'Back / Restart Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-back,{{WRAPPER}} .eas-funnel-restart' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'navigation_background',
			array(
				'label'     => esc_html__( 'Back / Restart Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-back,{{WRAPPER}} .eas-funnel-restart' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'navigation_border_color',
			array(
				'label'     => esc_html__( 'Back / Restart Border', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#e2e8f0',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-back,{{WRAPPER}} .eas-funnel-restart' => 'border-color:{{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'navigation_padding',
			array(
				'label'     => esc_html__( 'Back / Restart Padding', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-back,{{WRAPPER}} .eas-funnel-restart' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'navigation_radius',
			array(
				'label'     => esc_html__( 'Back / Restart Radius', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-back,{{WRAPPER}} .eas-funnel-restart' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'navigation_hover_color',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-back:hover,{{WRAPPER}} .eas-funnel-restart:hover,{{WRAPPER}} .eas-funnel-back:focus-visible,{{WRAPPER}} .eas-funnel-restart:focus-visible' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'navigation_hover_background',
			array(
				'label'     => esc_html__( 'Hover Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f5f3ff',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-back:hover,{{WRAPPER}} .eas-funnel-restart:hover,{{WRAPPER}} .eas-funnel-back:focus-visible,{{WRAPPER}} .eas-funnel-restart:focus-visible' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'navigation_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-back,{{WRAPPER}} .eas-funnel-restart',
			)
		);
		$this->add_control(
			'close_color',
			array(
				'label'     => esc_html__( 'Close Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-close' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'close_bg',
			array(
				'label'     => esc_html__( 'Close Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f1f5f9',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-close' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'close_size',
			array(
				'label'     => esc_html__( 'Close Size', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 24,
						'max' => 80,
					),
				),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-close' => 'width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};font-size:calc({{SIZE}}{{UNIT}} * .7);' ),
			)
		);
		$this->end_controls_section();
	}

	private function register_progress_style() {
		$this->start_controls_section(
			'style_progress',
			array(
				'label' => esc_html__( 'Progress & Step Counter', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'accent_color',
			array(
				'label'     => esc_html__( 'Accent Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array( '{{WRAPPER}} .eas-conversational-funnel' => '--eas-funnel-accent:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'progress_track',
			array(
				'label'     => esc_html__( 'Track Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ede9fe',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-progress' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'progress_fill',
			array(
				'label'     => esc_html__( 'Fill Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-progress span' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'progress_height',
			array(
				'label'     => esc_html__( 'Bar Height', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 1,
						'max' => 20,
					),
				),
				'default'   => array( 'size' => 4 ),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-progress' => 'height:{{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'counter_color',
			array(
				'label'     => esc_html__( 'Counter Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-step-count' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'counter_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-step-count',
			)
		);
		$this->end_controls_section();
	}

	private function register_launcher_style() {
		$this->start_controls_section(
			'style_launcher',
			array(
				'label'     => esc_html__( 'Launcher Button', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'display_mode!' => 'inline' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'launcher_typography',
				'selector' => '{{WRAPPER}} .eas-funnel-launcher',
			)
		);
		$this->add_control(
			'launcher_color',
			array(
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-launcher' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'launcher_bg',
			array(
				'label'     => esc_html__( 'Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6d28d9',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-launcher' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'launcher_padding',
			array(
				'label'     => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-launcher' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'launcher_radius',
			array(
				'label'     => esc_html__( 'Radius', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-launcher' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'launcher_shadow',
				'selector' => '{{WRAPPER}} .eas-funnel-launcher',
			)
		);
		$this->add_responsive_control(
			'floating_horizontal_offset',
			array(
				'label'     => esc_html__( 'Horizontal Screen Offset', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .eas-funnel-mode-floating.eas-funnel-position-right' => 'right:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .eas-funnel-mode-floating.eas-funnel-position-left' => 'left:{{SIZE}}{{UNIT}};',
				),
				'condition' => array( 'display_mode' => 'floating' ),
			)
		);
		$this->add_responsive_control(
			'floating_vertical_offset',
			array(
				'label'     => esc_html__( 'Vertical Screen Offset', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-mode-floating' => 'bottom:{{SIZE}}{{UNIT}};' ),
				'condition' => array( 'display_mode' => 'floating' ),
			)
		);
		$this->end_controls_section();
	}

	private function register_modal_style() {
		$this->start_controls_section(
			'style_modal',
			array(
				'label'     => esc_html__( 'Modal & Floating Panel', 'apex-addons-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'display_mode!' => 'inline' ),
			)
		);
		$this->add_control(
			'overlay_color',
			array(
				'label'     => esc_html__( 'Modal Overlay', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(15,23,42,.68)',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-mode-modal.eas-funnel-open:before' => 'background-color:{{VALUE}};' ),
				'condition' => array( 'display_mode' => 'modal' ),
			)
		);
		$this->add_responsive_control(
			'popup_width',
			array(
				'label'     => esc_html__( 'Popup Width', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 280,
						'max' => 1000,
					),
				),
				'selectors' => array( '{{WRAPPER}} .eas-funnel-mode-modal>.eas-funnel-panel,{{WRAPPER}} .eas-funnel-mode-floating .eas-funnel-panel' => 'width:min({{SIZE}}{{UNIT}},calc(100vw - 32px));' ),
			)
		);
		$this->end_controls_section();
	}

	private function register_message_style() {
		$this->start_controls_section(
			'style_messages',
			array(
				'label' => esc_html__( 'Success & Error States', 'apex-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'success_icon_color',
			array(
				'label'     => esc_html__( 'Success Icon Color', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#15803d',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-success-message span' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'success_icon_bg',
			array(
				'label'     => esc_html__( 'Success Icon Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#dcfce7',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-success-message span' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'error_color',
			array(
				'label'     => esc_html__( 'Error Text', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#b91c1c',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-error' => 'color:{{VALUE}};' ),
			)
		);
		$this->add_control(
			'error_bg',
			array(
				'label'     => esc_html__( 'Error Background', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#fef2f2',
				'selectors' => array( '{{WRAPPER}} .eas-funnel-error' => 'background-color:{{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'message_radius',
			array(
				'label'     => esc_html__( 'Error Radius', 'apex-addons-for-elementor' ),
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array( '{{WRAPPER}} .eas-funnel-error' => 'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$steps    = Funnel_Manager::normalize_steps( $settings );
		if ( empty( $steps ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<div class="eas-funnel-editor-notice">' . esc_html__( 'Add at least one funnel step.', 'apex-addons-for-elementor' ) . '</div>';
			}
			return;
		}

		$mode          = sanitize_key( $settings['display_mode'] ?? 'inline' );
		$allowed_modes = array_keys(
			apply_filters(
				'apexadfo_funnel_display_modes',
				array(
					'inline'   => 'Inline',
					'floating' => 'Floating',
					'modal'    => 'Modal',
				)
			)
		);
		if ( ! in_array( $mode, $allowed_modes, true ) ) {
			$mode = 'inline';
		}
		$page_id  = get_the_ID();
		$instance = 'eas-funnel-' . $this->get_id();
		$config   = array(
			'widgetId'       => $this->get_id(),
			'pageId'         => $page_id,
			'mode'           => $mode,
			'steps'          => $steps,
			'nonce'          => wp_create_nonce( 'apexadfo_funnel_submit_' . $page_id . '_' . $this->get_id() ),
			'showProgress'   => 'yes' === ( $settings['show_progress'] ?? 'yes' ),
			'showStepCount'  => 'yes' === ( $settings['show_step_count'] ?? 'yes' ),
			'allowRestart'   => 'yes' === ( $settings['allow_restart'] ?? 'yes' ),
			'transition'     => sanitize_key( $settings['transition_style'] ?? 'slide' ),
			'transitionTime' => max( 0, min( 1500, (int) ( $settings['transition_duration'] ?? 240 ) ) ),
			'contactLabels'  => array(
				'name'  => sanitize_text_field( $settings['name_placeholder'] ?? esc_html__( 'Name', 'apex-addons-for-elementor' ) ),
				'email' => sanitize_text_field( $settings['email_placeholder'] ?? esc_html__( 'Email address', 'apex-addons-for-elementor' ) ),
				'phone' => sanitize_text_field( $settings['phone_placeholder'] ?? esc_html__( 'Phone number', 'apex-addons-for-elementor' ) ),
			),
			'labels'         => array(
				'continue' => sanitize_text_field( $settings['continue_label'] ?? esc_html__( 'Continue', 'apex-addons-for-elementor' ) ),
				'back'     => sanitize_text_field( $settings['back_label'] ?? esc_html__( 'Back', 'apex-addons-for-elementor' ) ),
				'submit'   => sanitize_text_field( $settings['submit_label'] ?? esc_html__( 'Send request', 'apex-addons-for-elementor' ) ),
				'restart'  => sanitize_text_field( $settings['restart_label'] ?? esc_html__( 'Start again', 'apex-addons-for-elementor' ) ),
				'select'   => esc_html__( 'Select an option', 'apex-addons-for-elementor' ),
			),
		);
		$classes  = 'eas-conversational-funnel eas-funnel-mode-' . $mode . ' eas-funnel-transition-' . $config['transition'];
		if ( 'floating' === $mode ) {
			$classes .= ' eas-funnel-position-' . sanitize_key( $settings['floating_position'] ?? 'right' );
		}
		?>
		<div id="<?php echo esc_attr( $instance ); ?>" class="<?php echo esc_attr( $classes ); ?>" style="--eas-funnel-transition:<?php echo esc_attr( $config['transitionTime'] ); ?>ms" data-eas-funnel-config="<?php echo esc_attr( wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) ); ?>">
			<?php if ( ! in_array( $mode, array( 'inline', 'fullscreen' ), true ) ) : ?>
				<button type="button" class="eas-funnel-launcher" aria-controls="<?php echo esc_attr( $instance ); ?>-panel" aria-expanded="false"><span class="eas-funnel-launcher-icon" aria-hidden="true">✦</span><span><?php echo esc_html( $settings['launcher_label'] ?? esc_html__( 'Start', 'apex-addons-for-elementor' ) ); ?></span></button>
			<?php endif; ?>
			<div id="<?php echo esc_attr( $instance ); ?>-panel" class="eas-funnel-panel" role="<?php echo in_array( $mode, array( 'inline', 'fullscreen' ), true ) ? 'region' : 'dialog'; ?>" aria-modal="<?php echo 'modal' === $mode ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $settings['funnel_name'] ?? esc_html__( 'Conversational funnel', 'apex-addons-for-elementor' ) ); ?>" <?php echo in_array( $mode, array( 'inline', 'fullscreen' ), true ) ? '' : 'hidden'; ?>>
				<?php
				if ( ! in_array( $mode, array( 'inline', 'fullscreen' ), true ) ) :
					?>
					<button type="button" class="eas-funnel-close" aria-label="<?php esc_attr_e( 'Close funnel', 'apex-addons-for-elementor' ); ?>">×</button><?php endif; ?>
				<div class="eas-funnel-progress" aria-hidden="true"><span></span></div>
				<div class="eas-funnel-step-count" aria-live="polite"></div>
				<div class="eas-funnel-stage" aria-live="polite"></div>
				<div class="eas-funnel-error" role="alert" hidden></div>
				<div class="eas-funnel-footer"><button type="button" class="eas-funnel-back"></button><button type="button" class="eas-funnel-restart"></button></div>
				<input class="eas-funnel-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
			</div>
		</div>
		<?php
	}
}
