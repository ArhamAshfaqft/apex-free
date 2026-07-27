<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free\Widgets;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Optional submission storage writes to the plugin's dedicated custom table.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * APEX ADDONS - Form Widget with Conditional Logic & Signature Pad
 *
 * A highly customizable forms solution with step pagination, signature pad,
 * and country prefix dial selectors.
 */
class Form_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'eas-form';
	}

	public function get_title() {
		return esc_html__( 'Form Builder', 'apex-addons-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return [ 'elementor-addon-suite-category' ];
	}

	public function get_style_depends() {
		return [ 'apexadfo-form-widget-css' ];
	}

	public function get_script_depends() {
		return [ 'apexadfo-form-widget-js' ];
	}

	protected function register_controls() {
		// ---------------------------------------------------------------------
		// Content Tab - Form Fields Section
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_form_fields',
			[
				'label' => esc_html__( 'Form Fields', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'form_name',
			[
				'label'   => esc_html__( 'Form Name', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'New Form', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'show_labels',
			[
				'label'        => esc_html__( 'Label', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'selectors'    => [
					'{{WRAPPER}} .eas-form-label' => 'display: none;',
				],
			]
		);

		$repeater = new \Elementor\Repeater();
		$field_type_options = [
			'text'       => esc_html__( 'Text', 'apex-addons-for-elementor' ),
			'email'      => esc_html__( 'Email', 'apex-addons-for-elementor' ),
			'textarea'   => esc_html__( 'Textarea', 'apex-addons-for-elementor' ),
			'tel'        => esc_html__( 'Tel (with Country Flag)', 'apex-addons-for-elementor' ),
			'select'     => esc_html__( 'Select Dropdown', 'apex-addons-for-elementor' ),
			'radio'      => esc_html__( 'Radio buttons', 'apex-addons-for-elementor' ),
			'checkbox'   => esc_html__( 'Checkboxes', 'apex-addons-for-elementor' ),
			'acceptance' => esc_html__( 'Acceptance (Checkbox)', 'apex-addons-for-elementor' ),
			'number'     => esc_html__( 'Number', 'apex-addons-for-elementor' ),
			'date'       => esc_html__( 'Date', 'apex-addons-for-elementor' ),
			'time'       => esc_html__( 'Time', 'apex-addons-for-elementor' ),
			'password'   => esc_html__( 'Password', 'apex-addons-for-elementor' ),
			'html'       => esc_html__( 'HTML Block', 'apex-addons-for-elementor' ),
			'hidden'     => esc_html__( 'Hidden Value', 'apex-addons-for-elementor' ),
			'file'         => esc_html__( 'File Upload', 'apex-addons-for-elementor' ),
			'signature'    => esc_html__( 'Signature Pad', 'apex-addons-for-elementor' ),
			'range'        => esc_html__( 'Slider Range', 'apex-addons-for-elementor' ),
			'rating'       => esc_html__( 'Interactive Rating', 'apex-addons-for-elementor' ),
			'image_select' => esc_html__( 'Image Choice Cards', 'apex-addons-for-elementor' ),
			'step'         => esc_html__( 'Step Separator', 'apex-addons-for-elementor' ),
		];
		$field_type_options = apply_filters( 'apexadfo_form_field_type_options', $field_type_options );

		// Field Repeater Content Sub-Tab
		$repeater->start_controls_tabs( 'field_repeater_tabs' );
		$repeater->start_controls_tab(
			'field_tab_content',
			[
				'label' => esc_html__( 'Content', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'type',
			[
				'label'   => esc_html__( 'Type', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'text',
				'options' => $field_type_options,
			]
		);

		$repeater->add_control(
			'label',
			[
				'label'   => esc_html__( 'Label', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Field Label', 'apex-addons-for-elementor' ),
				'dynamic' => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'hide_label',
			[
				'label'        => esc_html__( 'Hide Label', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'type!' => [ 'html', 'hidden', 'step' ],
				],
			]
		);

		$repeater->add_control(
			'placeholder',
			[
				'label'     => esc_html__( 'Placeholder', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => esc_html__( 'Placeholder Text', 'apex-addons-for-elementor' ),
				'condition' => [
					'type!' => [ 'checkbox', 'radio', 'acceptance', 'signature', 'range', 'rating', 'image_select', 'html', 'hidden', 'step' ],
				],
				'dynamic'   => [ 'active' => true ],
			]
		);

		$repeater->add_control(
			'required',
			[
				'label'     => esc_html__( 'Required', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off' => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'default'   => '',
				'condition' => [
					'type!' => [ 'html', 'hidden', 'step' ],
				],
			]
		);

		$repeater->add_control(
			'width',
			[
				'label'   => esc_html__( 'Column Width', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '100',
				'options' => [
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
				],
			]
		);

		$repeater->add_control(
			'options',
			[
				'label'       => esc_html__( 'Options', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => "Option 1\nOption 2\nOption 3",
				'description' => esc_html__( 'Enter one option per line. For Image Choice Cards, format as: Label | Image URL (e.g. Graphic Design | https://example.com/icon.png).', 'apex-addons-for-elementor' ),
				'condition'   => [
					'type' => [ 'select', 'radio', 'checkbox', 'image_select' ],
				],
			]
		);

		$repeater->add_control(
			'use_toggle_style',
			[
				'label'        => esc_html__( 'Use iOS Toggle Style', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'type' => [ 'checkbox', 'acceptance' ],
				],
			]
		);

		$repeater->add_control(
			'rating_scale',
			[
				'label'     => esc_html__( 'Rating Scale (Max)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 3,
				'max'       => 10,
				'default'   => 5,
				'condition' => [
					'type' => 'rating',
				],
			]
		);

		$repeater->add_control(
			'rating_icon',
			[
				'label'     => esc_html__( 'Rating Icon', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'star',
				'options'   => [
					'star'  => esc_html__( 'Star', 'apex-addons-for-elementor' ),
					'heart' => esc_html__( 'Heart', 'apex-addons-for-elementor' ),
					'smile' => esc_html__( 'Smiley Face', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'type' => 'rating',
				],
			]
		);

		$repeater->add_control(
			'range_min',
			[
				'label'     => esc_html__( 'Min Value', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 0,
				'condition' => [
					'type' => 'range',
				],
			]
		);

		$repeater->add_control(
			'range_max',
			[
				'label'     => esc_html__( 'Max Value', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 100,
				'condition' => [
					'type' => 'range',
				],
			]
		);

		$repeater->add_control(
			'range_step',
			[
				'label'     => esc_html__( 'Step', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 1,
				'condition' => [
					'type' => 'range',
				],
			]
		);

		$repeater->add_control(
			'range_prefix',
			[
				'label'     => esc_html__( 'Value Prefix', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => [
					'type' => 'range',
				],
			]
		);

		$repeater->add_control(
			'range_suffix',
			[
				'label'     => esc_html__( 'Value Suffix', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => '',
				'condition' => [
					'type' => 'range',
				],
			]
		);

		$repeater->add_control(
			'html_content',
			[
				'label'     => esc_html__( 'HTML Content', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::WYSIWYG,
				'default'   => '<p>' . esc_html__( 'Custom HTML markup goes here.', 'apex-addons-for-elementor' ) . '</p>',
				'condition' => [
					'type' => 'html',
				],
			]
		);

		$repeater->end_controls_tab();

		// Field Repeater Advanced Sub-Tab
		$repeater->start_controls_tab(
			'field_tab_advanced',
			[
				'label' => esc_html__( 'Advanced', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'custom_id',
			[
				'label'       => esc_html__( 'Custom ID', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Unique ID used to reference field in emails/webhooks (e.g. email_address).', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'default_value',
			[
				'label'     => esc_html__( 'Default Value', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'type!' => [ 'signature', 'html', 'step' ],
				],
			]
		);

		$repeater->end_controls_tab();

		// Field Repeater Logic Sub-Tab
		$repeater->start_controls_tab(
			'field_tab_logic',
			[
				'label' => esc_html__( 'Conditional Logic', 'apex-addons-for-elementor' ),
			]
		);

		$repeater->add_control(
			'enable_logic',
			[
				'label'     => esc_html__( 'Enable Logic', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => [
					'type!' => [ 'step' ],
				],
			]
		);

		$repeater->add_control(
			'logic_action',
			[
				'label'     => esc_html__( 'Action', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'show',
				'options'   => [
					'show' => esc_html__( 'Show Field', 'apex-addons-for-elementor' ),
					'hide' => esc_html__( 'Hide Field', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'enable_logic' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'logic_relation',
			[
				'label'     => esc_html__( 'Relationship', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'all',
				'options'   => [
					'all' => esc_html__( 'All Conditions Match (AND)', 'apex-addons-for-elementor' ),
					'any' => esc_html__( 'Any Condition Matches (OR)', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'enable_logic' => 'yes',
				],
			]
		);

		// --- Condition 1 ---
		$repeater->add_control(
			'logic_heading_1',
			[
				'label'     => esc_html__( 'Condition 1', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_field_1',
			[
				'label'       => esc_html__( 'If Field (Custom ID)', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'e.g. name', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_operator_1',
			[
				'label'   => esc_html__( 'Comparison', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'equals',
				'options' => [
					'equals'       => esc_html__( 'Equals', 'apex-addons-for-elementor' ),
					'not_equals'   => esc_html__( 'Not Equals', 'apex-addons-for-elementor' ),
					'contains'     => esc_html__( 'Contains', 'apex-addons-for-elementor' ),
					'greater_than' => esc_html__( 'Greater Than', 'apex-addons-for-elementor' ),
					'less_than'    => esc_html__( 'Less Than', 'apex-addons-for-elementor' ),
					'empty'        => esc_html__( 'Is Empty', 'apex-addons-for-elementor' ),
					'not_empty'    => esc_html__( 'Is Not Empty', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_value_1',
			[
				'label'       => esc_html__( 'Value', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Expected value', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => [
					'enable_logic'     => 'yes',
					'logic_operator_1!' => [ 'empty', 'not_empty' ],
				],
			]
		);

		// --- Condition 2 ---
		$repeater->add_control(
			'logic_heading_2',
			[
				'label'     => esc_html__( 'Condition 2 (Optional)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_field_2',
			[
				'label'       => esc_html__( 'If Field (Custom ID)', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave empty if not needed', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_operator_2',
			[
				'label'   => esc_html__( 'Comparison', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'equals',
				'options' => [
					'equals'       => esc_html__( 'Equals', 'apex-addons-for-elementor' ),
					'not_equals'   => esc_html__( 'Not Equals', 'apex-addons-for-elementor' ),
					'contains'     => esc_html__( 'Contains', 'apex-addons-for-elementor' ),
					'greater_than' => esc_html__( 'Greater Than', 'apex-addons-for-elementor' ),
					'less_than'    => esc_html__( 'Less Than', 'apex-addons-for-elementor' ),
					'empty'        => esc_html__( 'Is Empty', 'apex-addons-for-elementor' ),
					'not_empty'    => esc_html__( 'Is Not Empty', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_value_2',
			[
				'label'       => esc_html__( 'Value', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Expected value', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => [
					'enable_logic'     => 'yes',
					'logic_operator_2!' => [ 'empty', 'not_empty' ],
				],
			]
		);

		// --- Condition 3 ---
		$repeater->add_control(
			'logic_heading_3',
			[
				'label'     => esc_html__( 'Condition 3 (Optional)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_field_3',
			[
				'label'       => esc_html__( 'If Field (Custom ID)', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Leave empty if not needed', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_operator_3',
			[
				'label'   => esc_html__( 'Comparison', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'equals',
				'options' => [
					'equals'       => esc_html__( 'Equals', 'apex-addons-for-elementor' ),
					'not_equals'   => esc_html__( 'Not Equals', 'apex-addons-for-elementor' ),
					'contains'     => esc_html__( 'Contains', 'apex-addons-for-elementor' ),
					'greater_than' => esc_html__( 'Greater Than', 'apex-addons-for-elementor' ),
					'less_than'    => esc_html__( 'Less Than', 'apex-addons-for-elementor' ),
					'empty'        => esc_html__( 'Is Empty', 'apex-addons-for-elementor' ),
					'not_empty'    => esc_html__( 'Is Not Empty', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'enable_logic' => 'yes' ],
			]
		);
		$repeater->add_control(
			'logic_value_3',
			[
				'label'       => esc_html__( 'Value', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Expected value', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => [
					'enable_logic'     => 'yes',
					'logic_operator_3!' => [ 'empty', 'not_empty' ],
				],
			]
		);

		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();

		// Add repeater items
		$this->add_control(
			'form_fields',
			[
				'label'       => esc_html__( 'Fields List', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => [
					[
						'type'        => 'text',
						'label'       => esc_html__( 'Name', 'apex-addons-for-elementor' ),
						'placeholder' => esc_html__( 'Name', 'apex-addons-for-elementor' ),
						'custom_id'   => 'name',
						'width'       => '100',
					],
					[
						'type'        => 'email',
						'label'       => esc_html__( 'Email', 'apex-addons-for-elementor' ),
						'placeholder' => esc_html__( 'Email', 'apex-addons-for-elementor' ),
						'custom_id'   => 'email',
						'width'       => '100',
					],
					[
						'type'        => 'textarea',
						'label'       => esc_html__( 'Message', 'apex-addons-for-elementor' ),
						'placeholder' => esc_html__( 'Message', 'apex-addons-for-elementor' ),
						'custom_id'   => 'message',
						'width'       => '100',
					],
				],
				'title_field' => '{{{ label }}} ({{{ type }}})',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Buttons Settings Section
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_buttons',
			[
				'label' => esc_html__( 'Submit & Steps Buttons', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'submit_text',
			[
				'label'   => esc_html__( 'Submit Button Text', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Send Message', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_responsive_control(
			'button_width',
			[
				'label'   => esc_html__( 'Column Width', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '100',
				'options' => [
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
				],
			]
		);

		$this->add_responsive_control(
			'btn_align',
			[
				'label'        => esc_html__( 'Alignment', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::CHOOSE,
				'options'      => [
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
				'default'      => 'left',
				'prefix_class' => 'eas-form-btn-align%s-',
				'selectors'    => [
					'{{WRAPPER}} .eas-form-field-wrap--submit' => 'align-items: {{VALUE}};',
					'{{WRAPPER}} .eas-form-submit-wrap'        => 'justify-content: {{VALUE}};',
				],
				'selectors_dictionary' => [
					'left'    => 'flex-start',
					'center'  => 'center',
					'right'   => 'flex-end',
					'justify' => 'stretch',
				],
			]
		);

		$this->add_control(
			'next_text',
			[
				'label'   => esc_html__( 'Next Button Text', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Next Step', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label'   => esc_html__( 'Previous Button Text', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Previous Step', 'apex-addons-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Actions After Submit
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_actions',
			[
				'label' => esc_html__( 'Actions After Submit', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$submit_action_options = apply_filters(
			'apexadfo_form_submit_action_options',
			[
				'email'    => esc_html__( 'Email Notification', 'apex-addons-for-elementor' ),
				'database' => esc_html__( 'Collect Submissions', 'apex-addons-for-elementor' ),
				'redirect' => esc_html__( 'Redirect URL', 'apex-addons-for-elementor' ),
				'webhook'  => esc_html__( 'Webhook', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'submit_actions',
			[
				'label'       => esc_html__( 'Add Action', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'default'     => [ 'email' ],
				'options'     => $submit_action_options,
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Actions Settings Sections (Conditional)
		// ---------------------------------------------------------------------
		
		// 1. Email Section
		$this->start_controls_section(
			'section_action_email',
			[
				'label'     => esc_html__( 'Email Notification Settings', 'apex-addons-for-elementor' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'submit_actions' => 'email',
				],
			]
		);

		$this->add_control(
			'email_to',
			[
				'label'       => esc_html__( 'To Email', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => get_option( 'admin_email' ),
				'default'     => get_option( 'admin_email' ),
			]
		);

		$this->add_control(
			'email_subject',
			[
				'label'   => esc_html__( 'Subject', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'New Submission logged from site', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'email_message',
			[
				'label'       => esc_html__( 'Message', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => '[all-fields]',
				'description' => esc_html__( 'Use [all-fields] tag to print all form fields summary.', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'email_from_name',
			[
				'label'   => esc_html__( 'From Name', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => get_bloginfo( 'name' ),
			]
		);

		$this->add_control(
			'email_reply_to',
			[
				'label'       => esc_html__( 'Reply-To Email', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => esc_html__( 'Enter the Custom ID of your email input (e.g. email) to route replies to sender.', 'apex-addons-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// 2. Redirect Section
		$this->start_controls_section(
			'section_action_redirect',
			[
				'label'     => esc_html__( 'Redirect Settings', 'apex-addons-for-elementor' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'submit_actions' => 'redirect',
				],
			]
		);

		$this->add_control(
			'redirect_url',
			[
				'label'       => esc_html__( 'Redirect URL', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'https://yoursite.com/thank-you',
			]
		);

		$this->end_controls_section();

		// 3. Webhook Section
		$this->start_controls_section(
			'section_action_webhook',
			[
				'label'     => esc_html__( 'Webhook Settings', 'apex-addons-for-elementor' ),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'submit_actions' => 'webhook',
				],
			]
		);

		$this->add_control(
			'webhook_url',
			[
				'label'       => esc_html__( 'Webhook Target URL', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'https://hooks.zapier.com/...',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Steps settings (Multi-step)
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_steps_settings',
			[
				'label' => esc_html__( 'Steps Settings', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'step_indicator',
			[
				'label'   => esc_html__( 'Step Indicator', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'bar',
				'options' => [
					'bar'     => esc_html__( 'Progress Bar', 'apex-addons-for-elementor' ),
					'circles' => esc_html__( 'Steps Circles', 'apex-addons-for-elementor' ),
					'none'    => esc_html__( 'None', 'apex-addons-for-elementor' ),
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Content Tab - Custom Form Messages Options
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_custom_messages',
			[
				'label' => esc_html__( 'Custom Messages', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'custom_msg_success',
			[
				'label'   => esc_html__( 'Success Message Text', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Form submitted successfully!', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'custom_msg_error',
			[
				'label'   => esc_html__( 'Error Message Text', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'An error occurred. Submission failed.', 'apex-addons-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Form Fields Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_fields',
			[
				'label' => esc_html__( 'Form Fields Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'fields_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-control, {{WRAPPER}} .eas-form-tel-prefix-selector' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'fields_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-control, {{WRAPPER}} .eas-form-tel-prefix-selector' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'fields_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-control, {{WRAPPER}} .eas-form-tel-prefix-selector' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'fields_focus_border_color',
			[
				'label'     => esc_html__( 'Focus Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => [
					'{{WRAPPER}} .eas-form-control:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'fields_border_radius',
			[
				'label'      => esc_html__( 'Border Radius (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' , 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-form-control' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .eas-form-tel-prefix-selector' => 'border-radius: {{TOP}}{{UNIT}} 0 0 {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .eas-form-tel-input' => 'border-radius: 0 {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 0;',
				],
			]
		);

		$this->add_responsive_control(
			'fields_padding',
			[
				'label'      => esc_html__( 'Input Padding', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-form-control' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'fields_typography',
				'selector' => '{{WRAPPER}} .eas-form-control, {{WRAPPER}} .eas-form-tel-prefix-selector',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'fields_box_shadow',
				'selector' => '{{WRAPPER}} .eas-form-control',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'fields_focus_box_shadow',
				'selector' => '{{WRAPPER}} .eas-form-control:focus',
				'fields_options' => [
					'box_shadow' => [
						'default' => [
							'horizontal' => 0,
							'vertical'   => 0,
							'blur'       => 0,
							'spread'     => 4,
							'color'      => 'rgba(99, 102, 241, 0.1)',
						],
					],
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Labels Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_labels',
			[
				'label' => esc_html__( 'Labels Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'labels_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'labels_spacing',
			[
				'label'     => esc_html__( 'Spacing Bottom (px)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [ 'min' => 0, 'max' => 40 ],
				],
				'selectors' => [
					'{{WRAPPER}} .eas-form-label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'labels_typography',
				'selector' => '{{WRAPPER}} .eas-form-label',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Buttons Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_buttons',
			[
				'label' => esc_html__( 'Buttons Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_buttons_style' );

		// Normal State
		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'next_submit_heading',
			[
				'label'     => esc_html__( 'Next & Submit Button', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'btn_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-submit, {{WRAPPER}} .eas-form-btn-next' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-submit, {{WRAPPER}} .eas-form-btn-next' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'btn_border',
				'selector' => '{{WRAPPER}} .eas-form-btn-submit, {{WRAPPER}} .eas-form-btn-next',
			]
		);

		$this->add_control(
			'prev_heading',
			[
				'label'     => esc_html__( 'Previous Button', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'prev_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-prev' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prev_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-prev' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name'     => 'prev_border',
				'selector' => '{{WRAPPER}} .eas-form-btn-prev',
			]
		);

		$this->end_controls_tab();

		// Hover State
		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'apex-addons-for-elementor' ),
			]
		);

		$this->add_control(
			'next_submit_hover_heading',
			[
				'label'     => esc_html__( 'Next & Submit Button', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'btn_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-submit:hover, {{WRAPPER}} .eas-form-btn-next:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_text_color_hover',
			[
				'label'     => esc_html__( 'Text Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-submit:hover, {{WRAPPER}} .eas-form-btn-next:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'btn_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-submit:hover, {{WRAPPER}} .eas-form-btn-next:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prev_hover_heading',
			[
				'label'     => esc_html__( 'Previous Button', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'prev_bg_color_hover',
			[
				'label'     => esc_html__( 'Background Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-prev:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prev_text_color_hover',
			[
				'label'     => esc_html__( 'Text Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-prev:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prev_border_color_hover',
			[
				'label'     => esc_html__( 'Border Color (Hover)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-btn-prev:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Common button configs
		$this->add_responsive_control(
			'btn_border_radius',
			[
				'label'      => esc_html__( 'Border Radius (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .eas-form-btn-submit, {{WRAPPER}} .eas-form-btn-next, {{WRAPPER}} .eas-form-btn-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'btn_padding',
			[
				'label'      => esc_html__( 'Padding', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .eas-form-btn-submit, {{WRAPPER}} .eas-form-btn-next, {{WRAPPER}} .eas-form-btn-prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'btn_typography',
				'selector' => '{{WRAPPER}} .eas-form-btn-submit, {{WRAPPER}} .eas-form-btn-next, {{WRAPPER}} .eas-form-btn-prev',
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Signature Pad Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_signature',
			[
				'label' => esc_html__( 'Signature Pad Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'sig_bg_color',
			[
				'label'     => esc_html__( 'Canvas Background', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-sig-pad-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'sig_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-sig-pad-wrap' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'sig_canvas_height',
			[
				'label'     => esc_html__( 'Canvas Height (px)', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => [
					'px' => [ 'min' => 100, 'max' => 400 ],
				],
				'default'   => [ 'size' => 180 ],
				'selectors' => [
					'{{WRAPPER}} .eas-form-sig-canvas' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'sig_line_color',
			[
				'label' => esc_html__( 'Drawing Line Color', 'apex-addons-for-elementor' ),
				'type'  => \Elementor\Controls_Manager::COLOR,
			]
		);

		$this->add_control(
			'sig_line_width',
			[
				'label'   => esc_html__( 'Drawing Line Width (px)', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 10,
				'default' => 2,
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Steps Progress Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_steps',
			[
				'label' => esc_html__( 'Steps Indicator Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'steps_active_color',
			[
				'label'     => esc_html__( 'Active Step Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => [
					'{{WRAPPER}} .eas-form-steps-circle-item.active' => 'border-color: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} .eas-form-steps-circle-item.active .eas-form-steps-circle-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'steps_progress_color',
			[
				'label'     => esc_html__( 'Progress Line Fill Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => [
					'{{WRAPPER}} .eas-form-steps-progress-bar-fill' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-form-steps-circles-progress'   => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'steps_completed_color',
			[
				'label'     => esc_html__( 'Completed Step BG/Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#6366f1',
				'selectors' => [
					'{{WRAPPER}} .eas-form-steps-circle-item.completed' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'steps_completed_text_color',
			[
				'label'     => esc_html__( 'Completed Number Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-form-steps-circle-item.completed' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'steps_inactive_color',
			[
				'label'     => esc_html__( 'Inactive Step Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#cbd5e1',
				'selectors' => [
					'{{WRAPPER}} .eas-form-steps-progress-bar-wrap' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-form-steps-circles::before'  => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .eas-form-steps-circle-item'       => 'border-color: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// ---------------------------------------------------------------------
		// Style Tab - Feedback & Success Overlay Styles
		// ---------------------------------------------------------------------
		$this->start_controls_section(
			'section_style_messages',
			[
				'label' => esc_html__( 'Alerts & Success Overlay Style', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'msg_success_heading',
			[
				'label'     => esc_html__( 'Success Overlay', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'success_overlay_bg',
			[
				'label'     => esc_html__( 'Overlay Background', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-success-overlay' => 'background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'success_overlay_check_color',
			[
				'label'     => esc_html__( 'Checkmark Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-success-circle' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .eas-form-success-check'  => 'stroke: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'success_overlay_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-success-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'success_overlay_typography',
				'selector' => '{{WRAPPER}} .eas-form-success-text',
			]
		);

		$this->add_control(
			'msg_alerts_heading',
			[
				'label'     => esc_html__( 'Message Alerts', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'msg_success_bg',
			[
				'label'     => esc_html__( 'Success Banner Background', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-message-success' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'msg_success_text_color',
			[
				'label'     => esc_html__( 'Success Banner Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-message-success' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'msg_success_border_color',
			[
				'label'     => esc_html__( 'Success Banner Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-message-success' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'msg_error_bg',
			[
				'label'     => esc_html__( 'Error Banner Background', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-message-error' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'msg_error_text_color',
			[
				'label'     => esc_html__( 'Error Banner Text Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-message-error' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'msg_error_border_color',
			[
				'label'     => esc_html__( 'Error Banner Border Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .eas-form-message-error' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name'     => 'msg_banners_typography',
				'selector' => '{{WRAPPER}} .eas-form-message',
			]
		);

		$this->end_controls_section();

		// Companion extensions can append additional workflow controls while the
		// complete core form remains usable without another plugin.
		do_action( 'apexadfo_form_register_controls', $this );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$form_fields = is_array( $settings['form_fields'] ?? null ) ? $settings['form_fields'] : [];
		$form_name = ! empty( $settings['form_name'] ) ? $settings['form_name'] : esc_html__( 'New Form', 'apex-addons-for-elementor' );
		$submit_text = ! empty( $settings['submit_text'] ) ? $settings['submit_text'] : esc_html__( 'Send Message', 'apex-addons-for-elementor' );

		// Check if multi-step is active by looking for any 'step' field type
		$has_steps = false;
		$step_count = 0;
		foreach ( $form_fields as $field ) {
			if ( 'step' === $field['type'] ) {
				$has_steps = true;
				$step_count++;
			}
		}

		// Compile country codes list (lightweight high-res emoji list)
		$countries = [
			[ 'name' => 'United States', 'code' => '+1', 'flag' => '🇺🇸' ],
			[ 'name' => 'United Kingdom', 'code' => '+44', 'flag' => '🇬🇧' ],
			[ 'name' => 'Canada', 'code' => '+1', 'flag' => '🇨🇦' ],
			[ 'name' => 'Germany', 'code' => '+49', 'flag' => '🇩🇪' ],
			[ 'name' => 'France', 'code' => '+33', 'flag' => '🇫🇷' ],
			[ 'name' => 'Australia', 'code' => '+61', 'flag' => '🇦🇺' ],
			[ 'name' => 'India', 'code' => '+91', 'flag' => '🇮🇳' ],
			[ 'name' => 'Pakistan', 'code' => '+92', 'flag' => '🇵🇰' ],
			[ 'name' => 'United Arab Emirates', 'code' => '+971', 'flag' => '🇦🇪' ],
			[ 'name' => 'Saudi Arabia', 'code' => '+966', 'flag' => '🇸🇦' ],
			[ 'name' => 'Singapore', 'code' => '+65', 'flag' => '🇸🇬' ],
			[ 'name' => 'Japan', 'code' => '+81', 'flag' => '🇯🇵' ],
			[ 'name' => 'China', 'code' => '+86', 'flag' => '🇨🇳' ],
			[ 'name' => 'Brazil', 'code' => '+55', 'flag' => '🇧🇷' ],
			[ 'name' => 'South Africa', 'code' => '+27', 'flag' => '🇿🇦' ],
		];

		// Enqueue localized AJAX configuration
		wp_localize_script( 'apexadfo-form-widget-js', 'apexadfoFormConfig', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		] );

		?>
		<div class="eas-form-wrap">
			<form method="post" enctype="multipart/form-data" class="eas-form-grid-wrap">
				<!-- Security Nonces & Configs -->
				<?php wp_nonce_field( 'apexadfo_form_submission_action', 'apexadfo_form_nonce' ); ?>
				<input type="hidden" name="apexadfo_form_id" value="<?php echo esc_attr( $this->get_id() ); ?>" />
				<input type="hidden" name="apexadfo_post_id" value="<?php echo esc_attr( get_the_ID() ); ?>" />
				<input type="hidden" name="apexadfo_form_name" value="<?php echo esc_attr( $form_name ); ?>" />
				
				<!-- Honeypot anti-spam trap input (hidden from real users, autofilled by spam bots) -->
				<div style="display: none !important;">
					<input type="text" name="apexadfo_hp_field" class="eas-form-hidden-hp" autocomplete="off" tabindex="-1" />
				</div>

				<?php if ( $has_steps ) : ?>
					<!-- Steps progress indicator tracker bar -->
					<div class="eas-form-steps-indicator">
						<?php if ( 'bar' === $settings['step_indicator'] ) : ?>
							<div class="eas-form-steps-progress-bar-wrap">
								<div class="eas-form-steps-progress-bar-fill"></div>
							</div>
						<?php elseif ( 'circles' === $settings['step_indicator'] ) : ?>
							<div class="eas-form-steps-circles">
								<div class="eas-form-steps-circles-progress"></div>
								<div class="eas-form-steps-circle-item active" data-step="0">
									1
									<span class="eas-form-steps-circle-label"><?php esc_html_e( 'Start', 'apex-addons-for-elementor' ); ?></span>
								</div>
								<?php for ( $i = 1; $i <= $step_count; $i++ ) : ?>
									<div class="eas-form-steps-circle-item" data-step="<?php echo intval( $i ); ?>">
										<?php echo intval( $i + 1 ); ?>
										<span class="eas-form-steps-circle-label"><?php echo esc_html__( 'Step', 'apex-addons-for-elementor' ) . ' ' . intval( $i + 1 ); ?></span>
									</div>
								<?php endfor; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="eas-form-grid">
					<?php 
					$step_open = false;
					
					// If multi-step is active, wrap first fields in step 1 wrapper
					if ( $has_steps ) {
						echo '<div class="eas-form-step active">';
						echo '<div class="eas-form-grid">';
						$step_open = true;
					}

					foreach ( $form_fields as $index => $field ) :
						$type = $field['type'];
						$label = ! empty( $field['label'] ) ? $field['label'] : '';
						$placeholder = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
						$required = ( 'yes' === $field['required'] ) ? 'required' : '';
						$field_id = ! empty( $field['custom_id'] ) ? esc_attr( $field['custom_id'] ) : 'field_' . $index;
						$width = $field['width'];

						// If step separator type, close previous step wrapper and open new step wrapper
						if ( 'step' === $type ) {
							if ( $step_open ) {
								echo '</div>'; // Close .eas-form-grid
								// Add Step navigation actions
								echo '<div class="eas-form-step-actions">';
								echo '<button class="eas-form-btn-prev">' . esc_html( $settings['prev_text'] ) . '</button>';
								echo '<button class="eas-form-btn-next">' . esc_html( $settings['next_text'] ) . '</button>';
								echo '</div>';
								echo '</div>'; // Close .eas-form-step
							}
							echo '<div class="eas-form-step">';
							echo '<div class="eas-form-grid">';
							$step_open = true;
							continue;
						}

						// Prepare Conditional Logic data attributes
						$logic_json = '';
						if ( 'yes' === ( $field['enable_logic'] ?? '' ) ) {
							$rules_arr = [];
							for ( $r = 1; $r <= 3; $r++ ) {
								$fid = $field[ 'logic_field_' . $r ] ?? '';
								$op  = $field[ 'logic_operator_' . $r ] ?? '';
								if ( ! empty( $fid ) ) {
									$rules_arr[] = [
										'field_id' => $fid,
										'operator' => $op,
										'value'    => $field[ 'logic_value_' . $r ] ?? '',
									];
								}
							}
							if ( ! empty( $rules_arr ) ) {
								$logic_config = [
									'action'   => $field['logic_action'] ?? 'show',
									'relation' => $field['logic_relation'] ?? 'all',
									'rules'    => $rules_arr,
								];
								$logic_json = wp_json_encode( $logic_config );
							}
						}

						// Field wrapper class
						$wrap_classes = 'eas-form-field-wrap eas-form-width-' . $width;
						?>
						<div class="<?php echo esc_attr( $wrap_classes ); ?>"<?php if ( $logic_json ) : ?> data-eas-logic="<?php echo esc_attr( $logic_json ); ?>"<?php endif; ?>>
							<?php if ( ! empty( $label ) && 'hidden' !== $type && 'html' !== $type && 'yes' !== ( $field['hide_label'] ?? '' ) ) : ?>
								<label class="eas-form-label" for="<?php echo esc_attr( $field_id ); ?>">
									<?php echo esc_html( $label ); ?>
									<?php if ( $required ) : ?>
										<span class="eas-form-required-mark">*</span>
									<?php endif; ?>
								</label>
							<?php endif; ?>

							<?php 
							switch ( $type ) {
								case 'text':
								case 'email':
								case 'number':
								case 'date':
								case 'time':
								case 'password':
									?>
									<input type="<?php echo esc_attr( $type ); ?>" 
										id="<?php echo esc_attr( $field_id ); ?>" 
										name="<?php echo esc_attr( $field_id ); ?>" 
										class="eas-form-control" 
										placeholder="<?php echo esc_attr( $placeholder ); ?>" 
										value="<?php echo esc_attr( $field['default_value'] ); ?>"
										data-eas-field-id="<?php echo esc_attr( $field_id ); ?>"
										<?php if ( $required ) : ?> required<?php endif; ?> />
									<?php
									break;

								case 'range':
									$min = isset( $field['range_min'] ) ? intval( $field['range_min'] ) : 0;
									$max = isset( $field['range_max'] ) ? intval( $field['range_max'] ) : 100;
									$step = isset( $field['range_step'] ) ? intval( $field['range_step'] ) : 1;
									$default = isset( $field['default_value'] ) && '' !== $field['default_value'] ? intval( $field['default_value'] ) : $min;
									$prefix = isset( $field['range_prefix'] ) ? esc_html( $field['range_prefix'] ) : '';
									$suffix = isset( $field['range_suffix'] ) ? esc_html( $field['range_suffix'] ) : '';
									?>
									<div class="eas-form-range-wrap" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-suffix="<?php echo esc_attr( $suffix ); ?>">
										<input type="range" 
											id="<?php echo esc_attr( $field_id ); ?>" 
											name="<?php echo esc_attr( $field_id ); ?>" 
											class="eas-form-range-slider" 
											min="<?php echo esc_attr( $min ); ?>" 
											max="<?php echo esc_attr( $max ); ?>" 
											step="<?php echo esc_attr( $step ); ?>" 
											value="<?php echo esc_attr( $default ); ?>"
											data-eas-field-id="<?php echo esc_attr( $field_id ); ?>"
											<?php if ( $required ) : ?> required<?php endif; ?> />
										<span class="eas-form-range-val"><?php echo esc_html( $prefix . $default . $suffix ); ?></span>
									</div>
									<?php
									break;

								case 'textarea':
									?>
									<textarea id="<?php echo esc_attr( $field_id ); ?>" 
										name="<?php echo esc_attr( $field_id ); ?>" 
										class="eas-form-control" 
										placeholder="<?php echo esc_attr( $placeholder ); ?>"
										data-eas-field-id="<?php echo esc_attr( $field_id ); ?>"
										<?php if ( $required ) : ?> required<?php endif; ?>><?php echo esc_textarea( $field['default_value'] ); ?></textarea>
									<?php
									break;

								case 'select':
									$options_arr = explode( "\n", $field['options'] );
									?>
									<select id="<?php echo esc_attr( $field_id ); ?>" 
										name="<?php echo esc_attr( $field_id ); ?>" 
										class="eas-form-control"
										data-eas-field-id="<?php echo esc_attr( $field_id ); ?>"
										<?php if ( $required ) : ?> required<?php endif; ?>>
										<?php if ( ! empty( $placeholder ) ) : ?>
											<option value=""><?php echo esc_html( $placeholder ); ?></option>
										<?php endif; ?>
										<?php foreach ( $options_arr as $opt ) : 
											$opt = trim( $opt );
											if ( empty( $opt ) ) continue;
										?>
											<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $field['default_value'], $opt ); ?>><?php echo esc_html( $opt ); ?></option>
										<?php endforeach; ?>
									</select>
									<?php
									break;

								case 'radio':
									$options_arr = explode( "\n", $field['options'] );
									?>
									<div class="eas-form-option-list" data-eas-field-id="<?php echo esc_attr( $field_id ); ?>">
										<?php foreach ( $options_arr as $idx => $opt ) : 
											$opt = trim( $opt );
											if ( empty( $opt ) ) continue;
											$is_checked = ( $field['default_value'] === $opt );
										?>
											<label class="eas-form-option-item">
												<input type="radio" 
													name="<?php echo esc_attr( $field_id ); ?>" 
													value="<?php echo esc_attr( $opt ); ?>" 
													<?php checked( $is_checked ); ?>
													<?php if ( $required ) : ?> required<?php endif; ?> />
												<?php echo esc_html( $opt ); ?>
											</label>
										<?php endforeach; ?>
									</div>
									<?php
									break;

								case 'checkbox':
									$options_arr = explode( "\n", $field['options'] );
									$use_toggle = ( 'yes' === ( $field['use_toggle_style'] ?? '' ) );
									$item_class = 'eas-form-option-item' . ( $use_toggle ? ' eas-form-toggle-switch' : '' );
									?>
									<div class="eas-form-option-list" data-eas-field-id="<?php echo esc_attr( $field_id ); ?>">
										<?php foreach ( $options_arr as $idx => $opt ) : 
											$opt = trim( $opt );
											if ( empty( $opt ) ) continue;
											$is_checked = ( strpos( $field['default_value'], $opt ) !== false );
										?>
											<label class="<?php echo esc_attr( $item_class ); ?>">
												<input type="checkbox" 
													name="<?php echo esc_attr( $field_id ); ?>[]" 
													value="<?php echo esc_attr( $opt ); ?>" 
													<?php checked( $is_checked ); ?> />
												<?php if ( $use_toggle ) : ?>
													<span class="eas-form-toggle-slider"></span>
												<?php endif; ?>
												<span class="eas-form-option-label-text"><?php echo esc_html( $opt ); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
									<?php
									break;

								case 'acceptance':
									$use_toggle = ( 'yes' === ( $field['use_toggle_style'] ?? '' ) );
									$item_class = 'eas-form-option-item' . ( $use_toggle ? ' eas-form-toggle-switch' : '' );
									?>
									<div class="eas-form-option-list" data-eas-field-id="<?php echo esc_attr( $field_id ); ?>">
										<label class="<?php echo esc_attr( $item_class ); ?>">
											<input type="checkbox" 
												name="<?php echo esc_attr( $field_id ); ?>" 
												value="yes" 
												<?php if ( $required ) : ?> required<?php endif; ?> />
											<?php if ( $use_toggle ) : ?>
												<span class="eas-form-toggle-slider"></span>
											<?php endif; ?>
											<span class="eas-form-option-label-text"><?php echo esc_html( $placeholder ); ?></span>
										</label>
									</div>
									<?php
									break;

								case 'rating':
									$scale = isset( $field['rating_scale'] ) ? intval( $field['rating_scale'] ) : 5;
									$icon_type = isset( $field['rating_icon'] ) ? $field['rating_icon'] : 'star';
									$default_val = isset( $field['default_value'] ) ? intval( $field['default_value'] ) : 0;
									?>
									<div class="eas-form-rating-wrap" 
										data-scale="<?php echo esc_attr( $scale ); ?>" 
										data-icon="<?php echo esc_attr( $icon_type ); ?>"
										data-eas-field-id="<?php echo esc_attr( $field_id ); ?>">
										<div class="eas-form-rating-stars">
											<?php for ( $i = 1; $i <= $scale; $i++ ) : 
												$active_class = ( $i <= $default_val ) ? 'active' : '';
											?>
												<span class="eas-form-rating-item <?php echo esc_attr( $active_class ); ?>" data-value="<?php echo esc_attr( intval( $i ) ); ?>">
													<?php if ( 'heart' === $icon_type ) : ?>
														&#9829;
													<?php elseif ( 'smile' === $icon_type ) : ?>
														&#9786;
													<?php else : ?>
														&#9733;
													<?php endif; ?>
												</span>
											<?php endfor; ?>
										</div>
										<input type="hidden" 
											id="<?php echo esc_attr( $field_id ); ?>" 
											name="<?php echo esc_attr( $field_id ); ?>" 
											class="eas-form-rating-value-input" 
											value="<?php echo esc_attr( $default_val ); ?>" 
											<?php if ( $required ) : ?> required<?php endif; ?> />
									</div>
									<?php
									break;

								case 'image_select':
									$options_arr = explode( "\n", $field['options'] );
									$default_val = isset( $field['default_value'] ) ? $field['default_value'] : '';
									?>
									<div class="eas-form-image-select-grid" data-eas-field-id="<?php echo esc_attr( $field_id ); ?>">
										<?php foreach ( $options_arr as $idx => $opt ) : 
											$opt = trim( $opt );
											if ( empty( $opt ) ) continue;

											$parts = explode( '|', $opt );
											$opt_label = trim( $parts[0] );
											$opt_img = isset( $parts[1] ) ? trim( $parts[1] ) : '';
											
											$is_checked = ( $default_val === $opt_label );
										?>
											<div class="eas-form-image-select-card <?php echo $is_checked ? 'active' : ''; ?>" data-value="<?php echo esc_attr( $opt_label ); ?>">
												<?php if ( ! empty( $opt_img ) ) : ?>
													<div class="eas-form-image-select-img-wrap">
														<img src="<?php echo esc_url( $opt_img ); ?>" alt="<?php echo esc_attr( $opt_label ); ?>" loading="lazy" />
													</div>
												<?php endif; ?>
												<div class="eas-form-image-select-card-title"><?php echo esc_html( $opt_label ); ?></div>
											</div>
										<?php endforeach; ?>
										
										<!-- Hidden actual select input for submission data -->
										<input type="hidden" 
											id="<?php echo esc_attr( $field_id ); ?>" 
											name="<?php echo esc_attr( $field_id ); ?>" 
											class="eas-form-image-select-hidden-input" 
											value="<?php echo esc_attr( $default_val ); ?>" 
											<?php if ( $required ) : ?> required<?php endif; ?> />
									</div>
									<?php
									break;

								case 'file':
									?>
									<input type="file" 
										id="<?php echo esc_attr( $field_id ); ?>" 
										name="<?php echo esc_attr( $field_id ); ?>" 
										class="eas-form-control"
										data-eas-field-id="<?php echo esc_attr( $field_id ); ?>"
										<?php if ( $required ) : ?> required<?php endif; ?> />
									<?php
									break;

								case 'signature':
									$line_col = ! empty( $settings['sig_line_color'] ) ? esc_attr( $settings['sig_line_color'] ) : '#0f172a';
									$line_w = ! empty( $settings['sig_line_width'] ) ? intval( $settings['sig_line_width'] ) : 2;
									?>
									<div class="eas-form-sig-pad-wrap" data-line-color="<?php echo esc_attr( $line_col ); ?>" data-line-width="<?php echo esc_attr( $line_w ); ?>">
										<canvas class="eas-form-sig-canvas"></canvas>
										<div class="eas-form-sig-actions">
											<button class="eas-form-sig-btn eas-form-sig-clear"><?php esc_html_e( 'Clear', 'apex-addons-for-elementor' ); ?></button>
										</div>
										<input type="hidden" 
											name="<?php echo esc_attr( $field_id ); ?>" 
											class="eas-form-sig-value" 
											data-eas-field-id="<?php echo esc_attr( $field_id ); ?>"
											<?php if ( $required ) : ?> required<?php endif; ?> />
									</div>
									<?php
									break;

								case 'tel':
									?>
									<div class="eas-form-tel-wrap" data-eas-field-id="<?php echo esc_attr( $field_id ); ?>">
										<div class="eas-form-tel-prefix-selector">
											<span class="eas-form-tel-badge-flag">🇺🇸</span>
											<span class="eas-form-tel-badge-code">+1</span>
											<span class="eas-form-tel-arrow">&#9662;</span>
										</div>
										<input type="tel" 
											class="eas-form-control eas-form-tel-input" 
											placeholder="<?php echo esc_attr( $placeholder ); ?>" />
										
										<!-- Hidden actual value for submissions code + number -->
										<input type="hidden" 
											name="<?php echo esc_attr( $field_id ); ?>" 
											class="eas-form-tel-hidden-val" 
											data-eas-field-id="<?php echo esc_attr( $field_id ); ?>"
											<?php if ( $required ) : ?> required<?php endif; ?> />

										<!-- Country List dropdown search -->
										<div class="eas-form-tel-dropdown">
											<div class="eas-form-tel-dropdown-search-wrap">
												<input type="text" class="eas-form-tel-search" placeholder="<?php esc_attr_e( 'Search country...', 'apex-addons-for-elementor' ); ?>" />
											</div>
											<?php foreach ( $countries as $c ) : ?>
												<div class="eas-form-tel-option" data-code="<?php echo esc_attr( $c['code'] ); ?>" data-flag="<?php echo esc_attr( $c['flag'] ); ?>">
													<span class="eas-form-tel-flag"><?php echo esc_html( $c['flag'] ); ?></span>
													<span class="eas-form-tel-name"><?php echo esc_html( $c['name'] ); ?></span>
													<span class="eas-form-tel-code"><?php echo esc_html( $c['code'] ); ?></span>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
									<?php
									break;

								case 'html':
									echo do_shortcode( $field['html_content'] );
									break;

								case 'hidden':
									?>
									<input type="hidden" 
										name="<?php echo esc_attr( $field_id ); ?>" 
										value="<?php echo esc_attr( $field['default_value'] ); ?>"
										data-eas-field-id="<?php echo esc_attr( $field_id ); ?>" />
									<?php
									break;
							}
							?>
						</div>
					<?php endforeach; ?>

					<?php if ( $has_steps ) : ?>
						</div> <!-- Close last step's .eas-form-grid -->
						<div class="eas-form-step-actions">
							<button class="eas-form-btn-prev"><?php echo esc_html( $settings['prev_text'] ); ?></button>
							<button class="eas-form-btn-submit"><?php echo esc_html( $submit_text ); ?></button>
						</div>
						</div> <!-- Close .eas-form-step -->
					<?php else : ?>
						<?php
						$button_width = ! empty( $settings['button_width'] ) ? $settings['button_width'] : '100';
						?>
						<!-- Single step standard submit button -->
						<div class="eas-form-field-wrap eas-form-field-wrap--submit eas-form-width-<?php echo esc_attr( $button_width ); ?>">
							<div class="eas-form-submit-wrap">
								<button type="submit" class="eas-form-btn-submit"><?php echo esc_html( $submit_text ); ?></button>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<!-- Alerts alerts box success/error messages -->
				<div class="eas-form-message eas-form-message-success"></div>
				<div class="eas-form-message eas-form-message-error"></div>
			</form>
		</div>
		<?php
	}

	/**
	 * Unified Form Submission AJAX Handler
	 */
	private static function submitted_logic_value( $values, $field_id ) {
		$value = $values[ $field_id ] ?? '';
		if ( is_array( $value ) ) return array_map( 'sanitize_text_field', wp_unslash( $value ) );
		return sanitize_text_field( wp_unslash( $value ) );
	}

	private static function logic_rule_matches( $actual, $operator, $expected ) {
		$actual_values = is_array( $actual ) ? $actual : [ $actual ];
		$actual_text = implode( ', ', $actual_values );
		switch ( $operator ) {
			case 'not_equals': return ! in_array( $expected, $actual_values, true );
			case 'contains': return '' !== $expected && false !== stripos( $actual_text, $expected );
			case 'greater_than': return is_numeric( $actual_text ) && (float) $actual_text > (float) $expected;
			case 'less_than': return is_numeric( $actual_text ) && (float) $actual_text < (float) $expected;
			case 'empty': return '' === trim( $actual_text );
			case 'not_empty': return '' !== trim( $actual_text );
			case 'equals':
			default: return in_array( $expected, $actual_values, true );
		}
	}

	private static function is_conditionally_visible( $field, $values ) {
		if ( 'yes' !== ( $field['enable_logic'] ?? '' ) ) return true;
		$matches = [];
		for ( $index = 1; $index <= 3; $index++ ) {
			$source_id = sanitize_text_field( $field[ 'logic_field_' . $index ] ?? '' );
			if ( '' === $source_id ) continue;
			$actual = self::submitted_logic_value( $values, $source_id );
			$operator = sanitize_key( $field[ 'logic_operator_' . $index ] ?? 'equals' );
			$expected = sanitize_text_field( $field[ 'logic_value_' . $index ] ?? '' );
			$matches[] = self::logic_rule_matches( $actual, $operator, $expected );
		}
		if ( empty( $matches ) ) return true;
		$rules_match = 'any' === ( $field['logic_relation'] ?? 'all' ) ? in_array( true, $matches, true ) : ! in_array( false, $matches, true );
		return 'hide' === ( $field['logic_action'] ?? 'show' ) ? ! $rules_match : $rules_match;
	}

	public static function handle_form_submit() {
		// Verify nonce security
		if ( ! check_ajax_referer( 'apexadfo_form_submission_action', 'nonce', false ) ) {
			wp_send_json_error( esc_html__( 'Security check failed. Please refresh and try again.', 'apex-addons-for-elementor' ) );
		}

		$max_request_size = (int) apply_filters( 'apexadfo_form_max_request_size', 12 * MB_IN_BYTES );
		$request_size     = isset( $_SERVER['CONTENT_LENGTH'] ) ? absint( $_SERVER['CONTENT_LENGTH'] ) : 0;
		if ( $request_size > $max_request_size ) {
			wp_send_json_error( esc_html__( 'The submitted form is too large.', 'apex-addons-for-elementor' ), 413 );
		}

		// Bots commonly fill this visually hidden field. Return a generic success
		// so the endpoint does not reveal the anti-spam rule.
		$honeypot = isset( $_POST['apexadfo_hp_field'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['apexadfo_hp_field'] ) ) ) : '';
		if ( '' !== $honeypot ) {
			wp_send_json_success( [ 'message' => esc_html__( 'Form submitted successfully!', 'apex-addons-for-elementor' ) ] );
		}

		$form_id = isset( $_POST['apexadfo_form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['apexadfo_form_id'] ) ) : '';

		if ( empty( $form_id ) ) {
			wp_send_json_error( esc_html__( 'Form ID is missing.', 'apex-addons-for-elementor' ) );
		}

		// Retrieve the real widget configuration. Never trust submitted action,
		// recipient, webhook, or field information from the browser.
		$referrer = wp_get_referer();
		$post_id  = isset( $_POST['apexadfo_post_id'] ) ? absint( $_POST['apexadfo_post_id'] ) : 0;
		if ( ! $post_id && $referrer ) {
			$post_id = url_to_postid( $referrer );
		}
		$settings = null;

		if ( $post_id ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$elements_data = $document->get_elements_data();
				$settings = self::find_widget_settings( $elements_data, $form_id );
			}
		}

		if ( ! is_array( $settings ) || empty( $settings['form_fields'] ) || ! is_array( $settings['form_fields'] ) ) {
			wp_send_json_error( esc_html__( 'This form could not be verified. Please refresh and try again.', 'apex-addons-for-elementor' ), 400 );
		}

		$form_name      = ! empty( $settings['form_name'] ) ? sanitize_text_field( $settings['form_name'] ) : esc_html__( 'Form Submission', 'apex-addons-for-elementor' );
		$allowed_action_options = apply_filters(
			'apexadfo_form_submit_action_options',
			[
				'email'    => 'Email',
				'database' => 'Database',
				'redirect' => 'Redirect',
				'webhook'  => 'Webhook',
			]
		);
		$requested_actions = ! empty( $settings['submit_actions'] ) && is_array( $settings['submit_actions'] ) ? array_map( 'sanitize_key', $settings['submit_actions'] ) : [ 'email' ];
		$submit_actions = array_values( array_intersect( $requested_actions, array_keys( $allowed_action_options ) ) );
		$custom_msg_success = $settings['custom_msg_success'] ?? esc_html__( 'Form submitted successfully!', 'apex-addons-for-elementor' );
		$custom_msg_error = $settings['custom_msg_error'] ?? esc_html__( 'Submission failed. Please check fields.', 'apex-addons-for-elementor' );

		// Apply a small per-IP/per-form sliding-window limit. Site owners may
		// change the limit with apexadfo_form_rate_limit.
		$client_ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$rate_limit = max( 1, (int) apply_filters( 'apexadfo_form_rate_limit', 5, $form_id ) );
		$rate_key   = 'apexadfo_form_rate_' . md5( $client_ip . '|' . $post_id . '|' . $form_id );
		$rate_count = (int) get_transient( $rate_key );
		if ( $rate_count >= $rate_limit ) {
			wp_send_json_error( esc_html__( 'Too many submissions. Please wait a minute and try again.', 'apex-addons-for-elementor' ), 429 );
		}
		set_transient( $rate_key, $rate_count + 1, MINUTE_IN_SECONDS );

		$allowed_fields = [];
		foreach ( $settings['form_fields'] as $index => $field ) {
			$type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : 'text';
			if ( in_array( $type, [ 'html', 'step' ], true ) ) {
				continue;
			}
			$field_id = ! empty( $field['custom_id'] ) ? sanitize_text_field( $field['custom_id'] ) : 'field_' . $index;
			if ( '' !== $field_id ) {
				if ( isset( $allowed_fields[ $field_id ] ) ) {
					wp_send_json_error( esc_html__( 'Every form field must have a unique Custom ID.', 'apex-addons-for-elementor' ), 400 );
				}
				$allowed_fields[ $field_id ] = $field;
			}
		}
		$clean_post = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
		$conditionally_hidden_fields = [];
		foreach ( $allowed_fields as $field_id => $field ) {
			if ( ! self::is_conditionally_visible( $field, $clean_post ) ) {
				$conditionally_hidden_fields[ $field_id ] = true;
				unset( $allowed_fields[ $field_id ] );
			}
		}

		// 1. Process Form Data & Files
		$submission_data = [];
		$email_attachments = [];

		// Handle text/phone/signature fields
		foreach ( $clean_post as $key => $val ) {
			if ( in_array( $key, [ 'action', 'nonce', 'apexadfo_form_nonce', 'apexadfo_form_id', 'apexadfo_post_id', 'apexadfo_form_name', 'apexadfo_hp_field' ], true ) ) {
				continue;
			}

			$key = sanitize_key( $key );
			if ( isset( $conditionally_hidden_fields[ $key ] ) ) {
				continue;
			}
			if ( ! isset( $allowed_fields[ $key ] ) ) {
				wp_send_json_error( esc_html__( 'The form contains an unexpected field.', 'apex-addons-for-elementor' ), 400 );
			}

			// Clean values
			$label = sanitize_text_field( self::get_field_label_by_id( $settings, $key ) );
			$field_type = sanitize_key( $allowed_fields[ $key ]['type'] ?? 'text' );
			
			if ( in_array( $field_type, [ 'select', 'radio', 'checkbox', 'image_select' ], true ) ) {
				// An empty value is valid for optional choice fields. Required fields
				// are rejected by the authoritative validation pass below.
				$allowed_values = [ '' ];
				foreach ( preg_split( '/\r\n|\r|\n/', (string) ( $allowed_fields[ $key ]['options'] ?? '' ) ) as $option ) {
					$option = trim( $option );
					if ( '' === $option ) continue;
					$allowed_values[] = trim( explode( '|', $option, 2 )[0] );
				}
				$posted_values = is_array( $val ) ? array_map( 'sanitize_text_field', $val ) : [ sanitize_text_field( $val ) ];
				if ( array_diff( $posted_values, $allowed_values ) ) {
					wp_send_json_error( esc_html__( 'A submitted option is not valid.', 'apex-addons-for-elementor' ), 400 );
				}
			}

			if ( is_array( $val ) ) {
				// Checkbox arrays
				$clean_val = implode( ', ', array_map( 'sanitize_text_field', $val ) );
			} elseif ( 'textarea' === $field_type ) {
				$clean_val = sanitize_textarea_field( $val );
			} elseif ( 'email' === $field_type ) {
				$clean_val = sanitize_email( $val );
				if ( '' !== trim( (string) $val ) && ! is_email( $clean_val ) ) {
					wp_send_json_error( esc_html__( 'Please enter a valid email address.', 'apex-addons-for-elementor' ), 400 );
				}
			} elseif ( 'number' === $field_type || 'range' === $field_type || 'rating' === $field_type ) {
				if ( '' !== trim( (string) $val ) && ! is_numeric( $val ) ) {
					wp_send_json_error( esc_html__( 'A numeric field contains an invalid value.', 'apex-addons-for-elementor' ), 400 );
				}
				if ( '' !== trim( (string) $val ) && 'range' === $field_type ) {
					$minimum = (float) ( $allowed_fields[ $key ]['range_min'] ?? 0 );
					$maximum = (float) ( $allowed_fields[ $key ]['range_max'] ?? 100 );
					if ( (float) $val < $minimum || (float) $val > $maximum ) {
						wp_send_json_error( esc_html__( 'A range value is outside its allowed limits.', 'apex-addons-for-elementor' ), 400 );
					}
				}
				if ( '' !== trim( (string) $val ) && 'rating' === $field_type ) {
					$rating_scale = max( 1, (int) ( $allowed_fields[ $key ]['rating_scale'] ?? 5 ) );
					if ( (float) $val < 1 || (float) $val > $rating_scale ) {
						wp_send_json_error( esc_html__( 'A rating value is outside its allowed scale.', 'apex-addons-for-elementor' ), 400 );
					}
				}
				$clean_val = sanitize_text_field( $val );
			} else {
				$clean_val = sanitize_text_field( $val );
			}

			// Handle Base64 Signature Image data URL
			if ( 'signature' === $field_type && strpos( $clean_val, 'data:image/png;base64,' ) === 0 ) {
				if ( strlen( $clean_val ) > ( 2 * MB_IN_BYTES * 4 / 3 ) + 100 ) {
					wp_send_json_error( esc_html__( 'The signature image is too large.', 'apex-addons-for-elementor' ), 413 );
				}
				// Decode signature and save as file
				$file_url = self::save_base64_signature( $clean_val, $key );
				if ( $file_url ) {
					$clean_val = $file_url;
					// Add file path as attachment
					$upload_dir = wp_upload_dir();
					$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url );
					if ( file_exists( $file_path ) ) {
						$email_attachments[] = $file_path;
					}
				}
			}

			$submission_data[] = [
				'id'    => $key,
				'label' => $label,
				'value' => $clean_val,
			];
		}

		// Handle standard File Upload fields
		if ( ! empty( $_FILES ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			foreach ( $_FILES as $key => $file_info ) {
				if ( empty( $file_info['name'] ) ) continue;
				$key = sanitize_key( $key );
				$file_info = [
					'name'     => sanitize_file_name( wp_unslash( $file_info['name'] ?? '' ) ),
					'type'     => sanitize_mime_type( wp_unslash( $file_info['type'] ?? '' ) ),
					'tmp_name' => sanitize_text_field( wp_unslash( $file_info['tmp_name'] ?? '' ) ),
					'error'    => absint( $file_info['error'] ?? UPLOAD_ERR_NO_FILE ),
					'size'     => absint( $file_info['size'] ?? 0 ),
				];
				if ( isset( $conditionally_hidden_fields[ $key ] ) ) continue;
				if ( ! isset( $allowed_fields[ $key ] ) || 'file' !== ( $allowed_fields[ $key ]['type'] ?? '' ) ) {
					wp_send_json_error( esc_html__( 'An unexpected file was submitted.', 'apex-addons-for-elementor' ), 400 );
				}
				if ( ! empty( $file_info['error'] ) || empty( $file_info['tmp_name'] ) ) {
					wp_send_json_error( esc_html__( 'The uploaded file is invalid.', 'apex-addons-for-elementor' ), 400 );
				}
				$max_file_size = (int) apply_filters( 'apexadfo_form_max_file_size', 5 * MB_IN_BYTES, $form_id, $key );
				if ( empty( $file_info['size'] ) || (int) $file_info['size'] > $max_file_size ) {
					wp_send_json_error( esc_html__( 'The uploaded file is too large.', 'apex-addons-for-elementor' ), 413 );
				}
				$file_check = wp_check_filetype_and_ext( $file_info['tmp_name'], $file_info['name'] );
				if ( empty( $file_check['ext'] ) || empty( $file_check['type'] ) ) {
					wp_send_json_error( esc_html__( 'This file type is not allowed.', 'apex-addons-for-elementor' ), 400 );
				}

				$label = sanitize_text_field( self::get_field_label_by_id( $settings, $key ) );
				
				// Upload file safely
				$upload = wp_handle_upload( $file_info, [ 'test_form' => false ] );
				if ( $upload && ! isset( $upload['error'] ) ) {
					$submission_data[] = [
						'id'    => $key,
						'label' => $label,
						'value' => $upload['url'],
					];
					if ( file_exists( $upload['file'] ) ) {
						$email_attachments[] = $upload['file'];
					}
				} else {
					$error_message = is_array( $upload ) && ! empty( $upload['error'] ) ? $upload['error'] : esc_html__( 'Unknown upload error.', 'apex-addons-for-elementor' );
					/* translators: %s: file upload error message. */
					wp_send_json_error( sprintf( esc_html__( 'File upload error: %s', 'apex-addons-for-elementor' ), esc_html( $error_message ) ) );
				}
			}
		}

		// Browser validation is convenient, but server-side required validation is
		// authoritative and cannot be bypassed by a crafted AJAX request.
		$submitted_by_id = [];
		foreach ( $submission_data as $submitted_field ) {
			$submitted_by_id[ $submitted_field['id'] ] = $submitted_field['value'];
		}
		foreach ( $allowed_fields as $field_id => $field ) {
			if ( 'yes' !== ( $field['required'] ?? '' ) ) continue;
			$value = $submitted_by_id[ $field_id ] ?? '';
			if ( is_array( $value ) ? empty( $value ) : '' === trim( (string) $value ) ) {
				/* translators: %s: form field label. */
				wp_send_json_error( sprintf( esc_html__( '%s is required.', 'apex-addons-for-elementor' ), sanitize_text_field( $field['label'] ?? $field_id ) ), 400 );
			}
		}

		// 2. Perform DB log submission
		if ( in_array( 'database', $submit_actions ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'apexadfo_submissions';

			$meta_data = apply_filters( 'apexadfo_form_submission_metadata', [], $settings, $client_ip, $referrer );
			$meta_data = is_array( $meta_data ) ? map_deep( $meta_data, 'sanitize_text_field' ) : [];

			$inserted = $wpdb->insert(
				$table_name,
				[
					'form_id'         => $form_id,
					'form_name'       => $form_name,
					'submission_data' => wp_json_encode( $submission_data ),
					'meta_data'       => wp_json_encode( $meta_data ),
					'created_at'      => current_time( 'mysql' ),
				],
				[ '%s', '%s', '%s', '%s', '%s' ]
			);

			if ( ! $inserted ) {
				wp_send_json_error( $custom_msg_error );
			}
		}

		// 3. Perform Email action
		if ( in_array( 'email', $submit_actions ) ) {
			$email_to = ! empty( $settings['email_to'] ) ? sanitize_email( $settings['email_to'] ) : get_option( 'admin_email' );
			$email_to = apply_filters( 'apexadfo_form_email_to', $email_to, $settings, $submission_data, $form_id );
			$email_subject = ! empty( $settings['email_subject'] ) ? sanitize_text_field( $settings['email_subject'] ) : esc_html__( 'New Submission logged from site', 'apex-addons-for-elementor' );
			$email_msg_tmpl = ! empty( $settings['email_message'] ) ? $settings['email_message'] : '[all-fields]';
			$from_name = ! empty( $settings['email_from_name'] ) ? sanitize_text_field( $settings['email_from_name'] ) : get_bloginfo( 'name' );
			
			// Build fields summary
			$fields_summary = "";
			foreach ( $submission_data as $field ) {
				$fields_summary .= $field['label'] . ": " . $field['value'] . "\r\n";
			}

			$email_body = str_replace( '[all-fields]', $fields_summary, $email_msg_tmpl );

			// Headers
			$headers = [];
			$headers[] = 'From: ' . $from_name . ' <' . get_option( 'admin_email' ) . '>';
			
			// Reply-To mapping
			$reply_to_key = $settings['email_reply_to'] ?? '';
			if ( ! empty( $reply_to_key ) ) {
				foreach ( $submission_data as $field ) {
					if ( $field['id'] === $reply_to_key && is_email( $field['value'] ) ) {
						$headers[] = 'Reply-To: ' . $field['value'];
						break;
					}
				}
			}

			wp_mail( $email_to, $email_subject, $email_body, $headers, $email_attachments );
		}

		// 4. Perform Webhook payload
		if ( in_array( 'webhook', $submit_actions ) && ! empty( $settings['webhook_url'] ) ) {
			$webhook_url = esc_url_raw( $settings['webhook_url'] );
			
			$payload = [
				'form_id'    => $form_id,
				'form_name'  => $form_name,
				'fields'     => $submission_data,
				'created_at' => current_time( 'mysql' ),
			];

			$webhook_headers = apply_filters( 'apexadfo_form_webhook_headers', [ 'Content-Type' => 'application/json' ], $settings, $submission_data );
			wp_safe_remote_post( $webhook_url, [
				'method'    => 'POST',
				'headers'   => $webhook_headers,
				'body'      => wp_json_encode( $payload ),
				'timeout'   => 15,
				'blocking'  => false, // Don't delay frontend submission loading
			] );
		}

		do_action( 'apexadfo_form_after_actions', $settings, $submission_data, $form_id, $form_name, $email_attachments );

		// Compile response data
		$res_data = [
			'message' => $custom_msg_success,
		];

		if ( in_array( 'redirect', $submit_actions ) && ! empty( $settings['redirect_url'] ) ) {
			$res_data['redirect_url'] = esc_url_raw( $settings['redirect_url'] );
		}

		wp_send_json_success( $res_data );
	}

	/**
	 * Decode Base64 PNG signature string and save into uploads folder
	 */
	private static function save_base64_signature( $base64_string, $field_id ) {
		$data_parts = explode( ',', $base64_string );
		if ( count( $data_parts ) < 2 ) return false;

		$decoded_img = base64_decode( $data_parts[1], true );
		if ( ! $decoded_img || strlen( $decoded_img ) > 2 * MB_IN_BYTES ) return false;

		$image_info = @getimagesizefromstring( $decoded_img );
		if ( ! is_array( $image_info ) || IMAGETYPE_PNG !== $image_info[2] ) return false;

		// Generate dynamic file name
		$filename = 'signature-' . $field_id . '-' . time() . '.png';
		
		// Save file using native WP Upload Bits API
		$upload = wp_upload_bits( $filename, null, $decoded_img );
		if ( $upload && ! isset( $upload['error'] ) && ! $upload['error'] ) {
			return $upload['url'];
		}

		return false;
	}

	/**
	 * Fetch field label matching ID from config
	 */
	private static function get_field_label_by_id( $settings, $key ) {
		if ( empty( $settings ) || empty( $settings['form_fields'] ) ) {
			return ucfirst( str_replace( '_', ' ', $key ) );
		}
		foreach ( $settings['form_fields'] as $field ) {
			$field_id = ! empty( $field['custom_id'] ) ? $field['custom_id'] : '';
			if ( $field_id === $key ) {
				return $field['label'];
			}
		}
		return ucfirst( str_replace( '_', ' ', $key ) );
	}

	/**
	 * Recursively search for specific form widget inside Elementor page config layout array
	 */
	private static function find_widget_settings( $elements, $form_id ) {
		foreach ( $elements as $element ) {
			if ( isset( $element['elType'] ) && 'widget' === $element['elType'] && 'eas-form' === $element['widgetType'] && $form_id === $element['id'] ) {
				return $element['settings'] ?? [];
			}
			if ( ! empty( $element['elements'] ) ) {
				$found = self::find_widget_settings( $element['elements'], $form_id );
				if ( ! empty( $found ) ) {
					return $found;
				}
			}
		}
		return null;
	}
}
