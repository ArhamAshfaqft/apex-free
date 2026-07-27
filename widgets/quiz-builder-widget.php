<?php
/**
 * Apex Quiz Builder widget.
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
use ArhamAshfaq\ApexAddonsForElementor\Free\Quiz_Manager;

if ( ! defined( 'ABSPATH' ) ) exit;

class Quiz_Builder_Widget extends Widget_Base {
	public function get_name() { return 'eas-quiz-builder'; }
	public function get_title() { return esc_html__( 'Quiz Builder', 'apex-addons-for-elementor' ); }
	public function get_icon() { return 'eicon-form-horizontal'; }
	public function get_categories() { return array( 'elementor-addon-suite-category' ); }
	public function get_script_depends() { return array( 'apexadfo-quiz-builder-js' ); }
	public function get_style_depends() { return array( 'apexadfo-quiz-builder-css' ); }

	protected function register_controls() {
		$this->register_builder_controls();
		$this->register_behavior_controls();
		$this->register_result_controls();
		$this->register_advanced_result_controls();
		do_action( 'apexadfo_quiz_register_controls', $this );
		$this->register_panel_style();
		$this->register_content_style();
		$this->register_option_style();
		$this->register_input_style();
		$this->register_button_style();
		$this->register_navigation_style();
	}

	private function register_advanced_result_controls() {
		$rules = new Repeater();
		$rules->add_control( 'min_score', array( 'label' => esc_html__( 'Minimum Score', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::NUMBER, 'default' => 0 ) );
		$rules->add_control( 'max_score', array( 'label' => esc_html__( 'Maximum Score', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::NUMBER, 'default' => 1 ) );
		$rules->add_control( 'result_title', array( 'label' => esc_html__( 'Result Heading', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'Your result', 'apex-addons-for-elementor' ), 'label_block' => true ) );
		$rules->add_control( 'result_description', array( 'label' => esc_html__( 'Result Description', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->start_controls_section( 'section_quiz_builder_outcomes', array( 'label' => esc_html__( 'Advanced Quiz Outcomes', 'apex-addons-for-elementor' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'quiz_outcomes_notice', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'Create different result screens for score ranges and optionally collect a lead before revealing the result.', 'apex-addons-for-elementor' ), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-info' ) );
		$this->add_control( 'quiz_result_rules', array( 'label' => esc_html__( 'Score Result Rules', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::REPEATER, 'fields' => $rules->get_controls(), 'title_field' => '{{{ min_score }}}–{{{ max_score }}}: {{{ result_title }}}' ) );
		$this->add_control( 'quiz_lead_gate', array( 'label' => esc_html__( 'Lead Gate Before Result', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'quiz_gate_title', array( 'label' => esc_html__( 'Lead Gate Heading', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'Where should we send your result?', 'apex-addons-for-elementor' ), 'label_block' => true, 'condition' => array( 'quiz_lead_gate' => 'yes' ) ) );
		$this->end_controls_section();
	}

	private function register_builder_controls() {
		$this->start_controls_section( 'quiz_builder', array( 'label' => esc_html__( 'Quiz Builder', 'apex-addons-for-elementor' ) ) );
		$this->add_control( 'quiz_name', array( 'label' => esc_html__( 'Quiz Name', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'Knowledge Quiz', 'apex-addons-for-elementor' ), 'label_block' => true ) );
		$this->add_control( 'quiz_help', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'Step is a blank screen break. Add content, questions and a Button beneath it in the exact order they should appear.', 'apex-addons-for-elementor' ), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-info' ) );

		$repeater = new Repeater();
		$repeater->add_control( 'type', array(
			'label' => esc_html__( 'Item Type', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SELECT, 'default' => 'heading',
			'options' => array(
				'step' => esc_html__( 'Step Break', 'apex-addons-for-elementor' ), 'heading' => esc_html__( 'Heading', 'apex-addons-for-elementor' ), 'description' => esc_html__( 'Text / Description', 'apex-addons-for-elementor' ),
				'single' => esc_html__( 'Single Choice Question', 'apex-addons-for-elementor' ), 'multiple' => esc_html__( 'Multiple Choice Question', 'apex-addons-for-elementor' ),
				'text' => esc_html__( 'Text / Name Field', 'apex-addons-for-elementor' ), 'email' => esc_html__( 'Email Field', 'apex-addons-for-elementor' ),
				'button' => esc_html__( 'Continue / Submit Button', 'apex-addons-for-elementor' ), 'result' => esc_html__( 'Result Screen', 'apex-addons-for-elementor' ),
			),
		) );
		$repeater->add_control( 'step_id', array( 'label' => esc_html__( 'Step ID', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'default' => 'step-id', 'condition' => array( 'type' => 'step' ) ) );
		$repeater->add_control( 'content', array( 'label' => esc_html__( 'Content', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'label_block' => true, 'condition' => array( 'type' => array( 'heading', 'description' ) ) ) );
		$repeater->add_control( 'heading_tag', array( 'label' => esc_html__( 'HTML Tag', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SELECT, 'default' => 'h3', 'options' => array( 'h1'=>'H1','h2'=>'H2','h3'=>'H3','h4'=>'H4','h5'=>'H5','h6'=>'H6','div'=>'DIV' ), 'condition' => array( 'type' => 'heading' ) ) );
		$repeater->add_control( 'item_id', array( 'label' => esc_html__( 'Question / Field ID', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'default' => 'question-id', 'condition' => array( 'type' => array( 'single', 'multiple', 'text', 'email' ) ) ) );
		$repeater->add_control( 'label', array( 'label' => esc_html__( 'Question / Field Label', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'label_block' => true, 'condition' => array( 'type' => array( 'single', 'multiple', 'text', 'email' ) ) ) );
		$repeater->add_control( 'placeholder', array( 'label' => esc_html__( 'Placeholder', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'type' => array( 'text', 'email' ) ) ) );
		$repeater->add_control( 'options', array( 'label' => esc_html__( 'Answers & Points', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 6, 'default' => "First answer | first | 1\nSecond answer | second | 0", 'description' => esc_html__( 'One answer per line: Label | stable-value | points', 'apex-addons-for-elementor' ), 'condition' => array( 'type' => array( 'single', 'multiple' ) ) ) );
		$repeater->add_control( 'required', array( 'label' => esc_html__( 'Required', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'type' => array( 'single', 'multiple', 'text', 'email' ) ) ) );
		$repeater->add_control( 'button_label', array( 'label' => esc_html__( 'Button Text', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'Continue', 'apex-addons-for-elementor' ), 'condition' => array( 'type' => 'button' ) ) );
		$widths = array( '100'=>'100%','80'=>'80%','75'=>'75%','66'=>'66%','60'=>'60%','50'=>'50%','40'=>'40%','33'=>'33%','25'=>'25%','20'=>'20%' );
		$responsive_width_labels = array(
			'width'        => esc_html__( 'Desktop Width', 'apex-addons-for-elementor' ),
			'width_tablet' => esc_html__( 'Tablet Width', 'apex-addons-for-elementor' ),
			'width_mobile' => esc_html__( 'Mobile Width', 'apex-addons-for-elementor' ),
		);
		foreach ( $responsive_width_labels as $id => $label ) {
			$repeater->add_control( $id, array( 'label' => $label, 'type' => Controls_Manager::SELECT, 'default' => '100', 'options' => $widths, 'condition' => array( 'type!' => 'step' ) ) );
		}
		$this->add_control( 'quiz_items', array(
			'label' => esc_html__( 'Steps & Quiz Items', 'apex-addons-for-elementor' ), 'type' => Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(),
			'title_field' => '<# if ( "step" === type ) { #>Step Break: {{{ step_id }}}<# } else if ( "heading" === type || "description" === type ) { #>{{{ content || type }}} ({{{ type }}})<# } else if ( "button" === type ) { #>{{{ button_label }}} (button)<# } else { #>{{{ label || type }}} ({{{ type }}})<# } #>',
			'default' => array(
				array( 'type'=>'step','step_id'=>'welcome' ), array( 'type'=>'heading','content'=>esc_html__( 'Ready to test your knowledge?', 'apex-addons-for-elementor' ),'heading_tag'=>'h2' ), array( 'type'=>'description','content'=>esc_html__( 'Answer two quick questions and see your score.', 'apex-addons-for-elementor' ) ), array( 'type'=>'button','button_label'=>esc_html__( 'Start quiz', 'apex-addons-for-elementor' ) ),
				array( 'type'=>'step','step_id'=>'question-one' ), array( 'type'=>'single','item_id'=>'q1','label'=>esc_html__( 'Which language runs natively in a web browser?', 'apex-addons-for-elementor' ),'options'=>"JavaScript | javascript | 1\nPHP | php | 0\nPython | python | 0",'required'=>'yes' ), array( 'type'=>'button','button_label'=>esc_html__( 'Next question', 'apex-addons-for-elementor' ) ),
				array( 'type'=>'step','step_id'=>'question-two' ), array( 'type'=>'multiple','item_id'=>'q2','label'=>esc_html__( 'Which are valid CSS layout systems?', 'apex-addons-for-elementor' ),'options'=>"Grid | grid | 0.5\nFlexbox | flexbox | 0.5\nMySQL | mysql | 0",'required'=>'yes' ), array( 'type'=>'button','button_label'=>esc_html__( 'See my result', 'apex-addons-for-elementor' ) ),
				array( 'type'=>'step','step_id'=>'result' ), array( 'type'=>'result' ),
			),
		) );
		$this->end_controls_section();
	}

	private function register_behavior_controls() {
		$this->start_controls_section( 'quiz_behavior', array( 'label' => esc_html__( 'Behavior', 'apex-addons-for-elementor' ) ) );
		$this->add_control( 'show_progress', array( 'label'=>esc_html__( 'Progress Bar', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::SWITCHER,'return_value'=>'yes','default'=>'yes' ) );
		$this->add_control( 'show_counter', array( 'label'=>esc_html__( 'Step Counter', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::SWITCHER,'return_value'=>'yes','default'=>'yes' ) );
		$this->add_control( 'allow_back', array( 'label'=>esc_html__( 'Allow Back', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::SWITCHER,'return_value'=>'yes','default'=>'yes' ) );
		$this->add_control( 'allow_restart', array( 'label'=>esc_html__( 'Allow Restart', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::SWITCHER,'return_value'=>'yes','default'=>'yes' ) );
		$this->add_control( 'transition_duration', array( 'label'=>esc_html__( 'Transition Duration (ms)', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::NUMBER,'default'=>240,'min'=>0,'max'=>1500 ) );
		$this->end_controls_section();
	}

	private function register_result_controls() {
		$this->start_controls_section( 'quiz_result', array( 'label' => esc_html__( 'Default Result', 'apex-addons-for-elementor' ) ) );
		$this->add_control( 'result_title', array( 'label'=>esc_html__( 'Result Heading', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::TEXT,'default'=>esc_html__( 'Quiz complete', 'apex-addons-for-elementor' ),'label_block'=>true ) );
		$this->add_control( 'result_description', array( 'label'=>esc_html__( 'Result Description', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::TEXTAREA,'default'=>esc_html__( 'Thanks for completing the quiz.', 'apex-addons-for-elementor' ) ) );
		$this->add_control( 'show_score', array( 'label'=>esc_html__( 'Show Score', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::SWITCHER,'return_value'=>'yes','default'=>'yes' ) );
		$this->end_controls_section();
	}

	private function register_panel_style() {
		$this->start_controls_section( 'style_quiz_panel', array( 'label'=>esc_html__( 'Panel', 'apex-addons-for-elementor' ),'tab'=>Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Background::get_type(), array( 'name'=>'panel_background','selector'=>'{{WRAPPER}} .eas-quiz-panel' ) );
		$this->add_responsive_control( 'panel_padding', array( 'label'=>esc_html__( 'Padding', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::DIMENSIONS,'selectors'=>array( '{{WRAPPER}} .eas-quiz-panel'=>'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name'=>'panel_border','selector'=>'{{WRAPPER}} .eas-quiz-panel' ) );
		$this->add_responsive_control( 'panel_radius', array( 'label'=>esc_html__( 'Radius', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::DIMENSIONS,'selectors'=>array( '{{WRAPPER}} .eas-quiz-panel'=>'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name'=>'panel_shadow','selector'=>'{{WRAPPER}} .eas-quiz-panel' ) );
		$this->end_controls_section();
	}

	private function register_content_style() {
		$this->start_controls_section( 'style_quiz_content', array( 'label'=>esc_html__( 'Headings & Text', 'apex-addons-for-elementor' ),'tab'=>Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'heading_color', array( 'label'=>esc_html__( 'Heading Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-heading'=>'color:{{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name'=>'heading_typography','selector'=>'{{WRAPPER}} .eas-quiz-heading' ) );
		$this->add_control( 'description_color', array( 'label'=>esc_html__( 'Text Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-description'=>'color:{{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name'=>'description_typography','selector'=>'{{WRAPPER}} .eas-quiz-description' ) );
		$this->add_control( 'question_color', array( 'label'=>esc_html__( 'Question Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-question-label'=>'color:{{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name'=>'question_typography','selector'=>'{{WRAPPER}} .eas-quiz-question-label' ) );
		$this->end_controls_section();
	}

	private function register_option_style() {
		$this->start_controls_section( 'style_quiz_options', array( 'label'=>esc_html__( 'Answer Choices', 'apex-addons-for-elementor' ),'tab'=>Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'option_columns', array( 'label'=>esc_html__( 'Columns', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::SELECT,'default'=>'2','tablet_default'=>'2','mobile_default'=>'1','options'=>array('1'=>'1','2'=>'2','3'=>'3','4'=>'4'),'selectors'=>array( '{{WRAPPER}} .eas-quiz-options'=>'grid-template-columns:repeat({{VALUE}},minmax(0,1fr));' ) ) );
		$this->add_responsive_control( 'option_gap', array( 'label'=>esc_html__( 'Gap', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::SLIDER,'range'=>array('px'=>array('min'=>0,'max'=>60)),'selectors'=>array( '{{WRAPPER}} .eas-quiz-options'=>'gap:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'option_bg', array( 'label'=>esc_html__( 'Background', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-option'=>'background:{{VALUE}};' ) ) );
		$this->add_control( 'option_selected', array( 'label'=>esc_html__( 'Selected Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'default'=>'#6d28d9','selectors'=>array( '{{WRAPPER}} .eas-quiz-option.is-selected'=>'border-color:{{VALUE}};box-shadow:inset 0 0 0 1px {{VALUE}};', '{{WRAPPER}} .eas-quiz-option input:checked + .eas-quiz-indicator'=>'border-color:{{VALUE}};background:{{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name'=>'option_border','selector'=>'{{WRAPPER}} .eas-quiz-option' ) );
		$this->add_responsive_control( 'option_radius', array( 'label'=>esc_html__( 'Radius', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::DIMENSIONS,'selectors'=>array( '{{WRAPPER}} .eas-quiz-option'=>'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function register_input_style() {
		$this->start_controls_section( 'style_quiz_inputs', array( 'label'=>esc_html__( 'Lead Fields', 'apex-addons-for-elementor' ),'tab'=>Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'input_color', array( 'label'=>esc_html__( 'Text Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-input'=>'color:{{VALUE}};' ) ) );
		$this->add_control( 'input_bg', array( 'label'=>esc_html__( 'Background', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-input'=>'background:{{VALUE}};' ) ) );
		$this->add_control( 'input_focus', array( 'label'=>esc_html__( 'Focus Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'default'=>'#6d28d9','selectors'=>array( '{{WRAPPER}} .eas-quiz-input:focus'=>'border-color:{{VALUE}};box-shadow:0 0 0 3px color-mix(in srgb,{{VALUE}} 18%,transparent);' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name'=>'input_border','selector'=>'{{WRAPPER}} .eas-quiz-input' ) );
		$this->add_responsive_control( 'input_radius', array( 'label'=>esc_html__( 'Radius', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::DIMENSIONS,'selectors'=>array( '{{WRAPPER}} .eas-quiz-input'=>'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function register_button_style() {
		$this->start_controls_section( 'style_quiz_button', array( 'label'=>esc_html__( 'Continue & Submit Button', 'apex-addons-for-elementor' ),'tab'=>Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name'=>'button_typography','selector'=>'{{WRAPPER}} .eas-quiz-button' ) );
		$this->add_control( 'button_color', array( 'label'=>esc_html__( 'Text', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-button'=>'color:{{VALUE}};' ) ) );
		$this->add_control( 'button_bg', array( 'label'=>esc_html__( 'Background', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'default'=>'#6d28d9','selectors'=>array( '{{WRAPPER}} .eas-quiz-button'=>'background:{{VALUE}};border-color:{{VALUE}};' ) ) );
		$this->add_responsive_control( 'button_padding', array( 'label'=>esc_html__( 'Padding', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::DIMENSIONS,'selectors'=>array( '{{WRAPPER}} .eas-quiz-button'=>'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'button_radius', array( 'label'=>esc_html__( 'Radius', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::DIMENSIONS,'selectors'=>array( '{{WRAPPER}} .eas-quiz-button'=>'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function register_navigation_style() {
		$this->start_controls_section( 'style_quiz_navigation', array( 'label'=>esc_html__( 'Back, Restart & Progress', 'apex-addons-for-elementor' ),'tab'=>Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'nav_color', array( 'label'=>esc_html__( 'Navigation Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'selectors'=>array( '{{WRAPPER}} .eas-quiz-nav-button'=>'color:{{VALUE}};' ) ) );
		$this->add_control( 'progress_color', array( 'label'=>esc_html__( 'Progress Color', 'apex-addons-for-elementor' ),'type'=>Controls_Manager::COLOR,'default'=>'#6d28d9','selectors'=>array( '{{WRAPPER}} .eas-quiz-progress span'=>'background:{{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$steps = Quiz_Manager::normalize( $settings );
		if ( ! $steps ) { if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) echo '<div class="eas-quiz-editor-notice">' . esc_html__( 'Add at least one quiz Step.', 'apex-addons-for-elementor' ) . '</div>'; return; }
		$page_id = get_the_ID(); $widget_id = $this->get_id();
		$config = array(
			'pageId'=>$page_id,'widgetId'=>$widget_id,'steps'=>$steps,'nonce'=>wp_create_nonce( 'apexadfo_quiz_submit_' . $page_id . '_' . $widget_id ),
			'showProgress'=>'yes'===( $settings['show_progress']??'yes' ),'showCounter'=>'yes'===( $settings['show_counter']??'yes' ),'allowBack'=>'yes'===( $settings['allow_back']??'yes' ),'allowRestart'=>'yes'===( $settings['allow_restart']??'yes' ),'showScore'=>'yes'===( $settings['show_score']??'yes' ),'transitionTime'=>max(0,min(1500,(int)( $settings['transition_duration']??240 ))),
			'leadGate'=>array( 'active'=>'yes'===( $settings['quiz_lead_gate']??'' ), 'title'=>sanitize_text_field( $settings['quiz_gate_title']??__( 'Where should we send your result?', 'apex-addons-for-elementor' ) ) ),
			'defaultResult'=>array( 'title'=>sanitize_text_field( $settings['result_title']??__( 'Quiz complete', 'apex-addons-for-elementor' ) ), 'description'=>sanitize_textarea_field( $settings['result_description']??__( 'Thanks for completing the quiz.', 'apex-addons-for-elementor' ) ) ),
			'labels'=>array( 'back'=>__( 'Back', 'apex-addons-for-elementor' ),'restart'=>__( 'Restart quiz', 'apex-addons-for-elementor' ),'submit'=>__( 'See result', 'apex-addons-for-elementor' ) ),
		);
		?><div id="eas-quiz-<?php echo esc_attr( $widget_id ); ?>" class="eas-quiz-builder" style="--eas-quiz-transition:<?php echo esc_attr( $config['transitionTime'] ); ?>ms" data-eas-quiz-config="<?php echo esc_attr( wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) ); ?>"><div class="eas-quiz-panel"><div class="eas-quiz-progress"><span></span></div><div class="eas-quiz-counter" aria-live="polite"></div><div class="eas-quiz-stage" aria-live="polite"></div><div class="eas-quiz-error" role="alert" hidden></div><div class="eas-quiz-footer"><button type="button" class="eas-quiz-nav-button eas-quiz-back"></button><button type="button" class="eas-quiz-nav-button eas-quiz-restart"></button></div></div></div><?php
	}
}
