<?php
/**
 * Secure quiz normalization, scoring and response storage.
 *
 * @package ApexAddonsForElementor
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Quiz_Manager {
	const DB_VERSION = '1.0.0';
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_apexadfo_quiz_submit', array( $this, 'submit' ) );
		add_action( 'wp_ajax_nopriv_apexadfo_quiz_submit', array( $this, 'submit' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_page' ), 31 );
		if ( self::DB_VERSION !== get_option( 'apexadfo_quiz_db_version' ) ) {
			$this->create_table();
		}
	}

	private function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'apexadfo_quiz_entries';
	}

	private function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table = esc_sql( $this->table_name() );
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			page_id bigint(20) unsigned NOT NULL DEFAULT 0,
			widget_id varchar(80) NOT NULL DEFAULT '',
			quiz_name varchar(190) NOT NULL DEFAULT '',
			score decimal(12,2) NOT NULL DEFAULT 0,
			max_score decimal(12,2) NOT NULL DEFAULT 0,
			result_title varchar(255) NOT NULL DEFAULT '',
			answers longtext NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id), KEY page_widget (page_id,widget_id), KEY created_at (created_at)
		) " . $wpdb->get_charset_collate() . ';';
		dbDelta( $sql );
		update_option( 'apexadfo_quiz_db_version', self::DB_VERSION, false );
	}

	private static function parse_options( $raw ) {
		$options = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) continue;
			$parts = array_map( 'trim', explode( '|', $line ) );
			$label = sanitize_text_field( $parts[0] ?? '' );
			if ( '' === $label ) continue;
			$value = sanitize_key( $parts[1] ?? '' );
			$options[] = array(
				'label'  => $label,
				'value'  => $value ?: sanitize_title( $label ),
				'points' => max( -10000, min( 10000, (float) ( $parts[2] ?? 0 ) ) ),
			);
		}
		return array_slice( $options, 0, 100 );
	}

	public static function normalize( $settings ) {
		$steps = array();
		$current = -1;
		$ids = array();
		$item_ids = array();
		$widths = array( '20', '25', '33', '40', '50', '60', '66', '75', '80', '100' );
		foreach ( (array) ( $settings['quiz_items'] ?? array() ) as $index => $item ) {
			$type = sanitize_key( $item['type'] ?? 'heading' );
			if ( 'step' === $type ) {
				$id = sanitize_key( $item['step_id'] ?? '' );
				if ( ! $id || isset( $ids[ $id ] ) ) $id = 'step-' . ( count( $steps ) + 1 );
				$ids[ $id ] = true;
				$steps[] = array( 'id' => $id, 'items' => array() );
				$current = count( $steps ) - 1;
				continue;
			}
			if ( $current < 0 ) {
				$steps[] = array( 'id' => 'step-1', 'items' => array() );
				$current = 0;
			}
			if ( ! in_array( $type, array( 'heading', 'description', 'single', 'multiple', 'text', 'email', 'button', 'result' ), true ) ) continue;
			$width = (string) ( $item['width'] ?? '100' );
			$tablet = (string) ( $item['width_tablet'] ?? '100' );
			$mobile = (string) ( $item['width_mobile'] ?? '100' );
			$item_id = sanitize_key( $item['item_id'] ?? $type . '-' . ( $index + 1 ) );
			if ( ! $item_id || isset( $item_ids[ $item_id ] ) ) $item_id = $type . '-' . ( $index + 1 );
			$item_ids[ $item_id ] = true;
			$heading_tag = (string) ( $item['heading_tag'] ?? 'h3' );
			$normalized = array(
				'id'           => $item_id,
				'type'         => $type,
				'content'      => sanitize_textarea_field( $item['content'] ?? '' ),
				'label'        => sanitize_text_field( $item['label'] ?? '' ),
				'placeholder'  => sanitize_text_field( $item['placeholder'] ?? '' ),
				'button_label' => sanitize_text_field( $item['button_label'] ?? '' ),
				'required'     => 'yes' === ( $item['required'] ?? '' ),
				'tag'          => in_array( $heading_tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ), true ) ? $heading_tag : 'h3',
				'width'        => in_array( $width, $widths, true ) ? $width : '100',
				'width_tablet' => in_array( $tablet, $widths, true ) ? $tablet : '100',
				'width_mobile' => in_array( $mobile, $widths, true ) ? $mobile : '100',
				'options'      => in_array( $type, array( 'single', 'multiple' ), true ) ? self::parse_options( $item['options'] ?? '' ) : array(),
			);
			$steps[ $current ]['items'][] = $normalized;
		}
		return array_slice( $steps, 0, 100 );
	}

	private function find_widget_settings( $page_id, $widget_id ) {
		$found = null;

		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $page_id );
			if ( $document ) {
				$elements_data = $document->get_elements_data();
				$found = $this->search_elements_tree( $elements_data, $widget_id );
			}
		}

		if ( ! is_array( $found ) ) {
			$raw_data = get_post_meta( $page_id, '_elementor_data', true );
			$elements_data = is_string( $raw_data ) ? json_decode( $raw_data, true ) : $raw_data;
			if ( is_array( $elements_data ) ) {
				$found = $this->search_elements_tree( $elements_data, $widget_id );
			}
		}

		return is_array( $found ) ? $found : null;
	}

	private function search_elements_tree( $elements, $widget_id ) {
		foreach ( (array) $elements as $node ) {
			if ( 'eas-quiz-builder' === ( $node['widgetType'] ?? '' ) && $widget_id === ( $node['id'] ?? '' ) ) {
				return is_array( $node['settings'] ?? null ) ? $node['settings'] : array();
			}
			if ( ! empty( $node['elements'] ) && is_array( $node['elements'] ) ) {
				$found = $this->search_elements_tree( $node['elements'], $widget_id );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	private function select_result( $settings, $score ) {
		$default = array(
			'title'       => sanitize_text_field( $settings['result_title'] ?? __( 'Quiz complete', 'apex-addons-for-elementor' ) ),
			'description' => sanitize_textarea_field( $settings['result_description'] ?? __( 'Thanks for completing the quiz.', 'apex-addons-for-elementor' ) ),
		);
		foreach ( (array) ( $settings['quiz_result_rules'] ?? array() ) as $rule ) {
			if ( $score >= (float) ( $rule['min_score'] ?? 0 ) && $score <= (float) ( $rule['max_score'] ?? 0 ) ) {
				return array( 'title' => sanitize_text_field( $rule['result_title'] ?? $default['title'] ), 'description' => sanitize_textarea_field( $rule['result_description'] ?? $default['description'] ) );
			}
		}
		return $default;
	}

	public function submit() {
		$page_id = isset( $_POST['page_id'] ) ? absint( wp_unslash( $_POST['page_id'] ) ) : 0;
		$widget_id = isset( $_POST['widget_id'] ) ? sanitize_key( wp_unslash( $_POST['widget_id'] ) ) : '';
		$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		if ( ! $page_id || ! $widget_id || ! wp_verify_nonce( $nonce, 'apexadfo_quiz_submit_' . $page_id . '_' . $widget_id ) ) wp_send_json_error( array( 'message' => __( 'The security check failed.', 'apex-addons-for-elementor' ) ), 403 );
		$settings = $this->find_widget_settings( $page_id, $widget_id );
		$steps = is_array( $settings ) ? self::normalize( $settings ) : array();

		if ( empty( $steps ) && ! empty( $_POST['quiz_config'] ) ) {
			$raw_config = wp_unslash( $_POST['quiz_config'] );
			$parsed_config = json_decode( $raw_config, true );
			if ( is_array( $parsed_config ) && ! empty( $parsed_config['steps'] ) ) {
				$steps = $parsed_config['steps'];
				$settings = array(
					'quiz_name'          => sanitize_text_field( $parsed_config['quizName'] ?? 'Quiz' ),
					'quiz_lead_gate'     => ( ! empty( $parsed_config['leadGate']['active'] ) ) ? 'yes' : '',
					'quiz_gate_title'    => sanitize_text_field( $parsed_config['leadGate']['title'] ?? '' ),
					'result_title'       => sanitize_text_field( $parsed_config['defaultResult']['title'] ?? __( 'Quiz complete', 'apex-addons-for-elementor' ) ),
					'result_description' => sanitize_textarea_field( $parsed_config['defaultResult']['description'] ?? __( 'Thanks for completing the quiz.', 'apex-addons-for-elementor' ) ),
				);
			}
		}

		if ( empty( $steps ) ) {
			wp_send_json_error( array( 'message' => __( 'Quiz configuration was not found.', 'apex-addons-for-elementor' ) ), 404 );
		}
		$remote_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$rate_key       = 'apexadfo_quiz_rate_' . md5( $remote_address . '|' . $page_id . '|' . $widget_id );
		$attempts = (int) get_transient( $rate_key );
		if ( $attempts >= 10 ) wp_send_json_error( array( 'message' => __( 'Too many attempts. Please wait and try again.', 'apex-addons-for-elementor' ) ), 429 );
		set_transient( $rate_key, $attempts + 1, MINUTE_IN_SECONDS );
		$answers_json = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : '{}';
		$decoded      = json_decode( $answers_json, true );
		$answers = is_array( $decoded ) ? $decoded : array();
		$score = 0.0; $max_score = 0.0; $clean = array();
		foreach ( $steps as $score_step ) foreach ( $score_step['items'] as $score_item ) {
			if ( ! in_array( $score_item['type'], array( 'single', 'multiple' ), true ) ) continue;
			$question_max = 0.0;
			foreach ( $score_item['options'] as $option ) {
				if ( 'multiple' === $score_item['type'] ) $question_max += max( 0, (float) $option['points'] );
				else $question_max = max( $question_max, (float) $option['points'] );
			}
			$max_score += $question_max;
		}
		foreach ( $steps as $step ) foreach ( $step['items'] as $item ) {
			if ( ! in_array( $item['type'], array( 'single', 'multiple', 'text', 'email' ), true ) ) continue;
			$value = $answers[ $item['id'] ] ?? ( 'multiple' === $item['type'] ? array() : '' );
			$empty = '' === $value || null === $value || array() === $value;
			/* translators: %s: Quiz question or field label. */
			if ( $item['required'] && $empty ) wp_send_json_error( array( 'message' => sprintf( __( '%s is required.', 'apex-addons-for-elementor' ), $item['label'] ?: __( 'This question', 'apex-addons-for-elementor' ) ) ), 422 );
			if ( $empty ) continue;
			if ( in_array( $item['type'], array( 'single', 'multiple' ), true ) ) {
				$selected = 'multiple' === $item['type'] ? (array) $value : array( $value );
				$map = array(); foreach ( $item['options'] as $option ) $map[ $option['value'] ] = $option;
				$labels = array(); foreach ( $selected as $choice ) { $choice = sanitize_key( $choice ); if ( ! isset( $map[ $choice ] ) ) wp_send_json_error( array( 'message' => __( 'An answer was invalid.', 'apex-addons-for-elementor' ) ), 422 ); $score += (float) $map[ $choice ]['points']; $labels[] = $map[ $choice ]['label']; }
				$value = 'multiple' === $item['type'] ? $labels : reset( $labels );
			} elseif ( 'email' === $item['type'] ) {
				$value = sanitize_email( $value ); if ( ! is_email( $value ) ) wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'apex-addons-for-elementor' ) ), 422 );
			} else $value = sanitize_text_field( $value );
			$clean[] = array( 'id' => $item['id'], 'label' => $item['label'], 'value' => $value );
		}
		if ( 'yes' === ( $settings['quiz_lead_gate'] ?? '' ) ) {
			$gate_email = sanitize_email( $answers['__gate_email'] ?? '' );
			if ( ! is_email( $gate_email ) ) wp_send_json_error( array( 'message' => __( 'Please enter a valid email address to view the result.', 'apex-addons-for-elementor' ) ), 422 );
			$clean[] = array( 'id' => 'lead_name', 'label' => __( 'Name', 'apex-addons-for-elementor' ), 'value' => sanitize_text_field( $answers['__gate_name'] ?? '' ) );
			$clean[] = array( 'id' => 'lead_email', 'label' => __( 'Email', 'apex-addons-for-elementor' ), 'value' => $gate_email );
		}
		$result = $this->select_result( $settings, $score );
		global $wpdb;
		$wpdb->insert( $this->table_name(), array( 'page_id' => $page_id, 'widget_id' => $widget_id, 'quiz_name' => sanitize_text_field( $settings['quiz_name'] ?? 'Quiz' ), 'score' => $score, 'max_score' => $max_score, 'result_title' => $result['title'], 'answers' => wp_json_encode( $clean ), 'created_at' => current_time( 'mysql' ) ), array( '%d', '%s', '%s', '%f', '%f', '%s', '%s', '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$formatted_score = ( floor( $score ) == $score ) ? (int) $score : round( $score, 2 );
		$formatted_max   = ( floor( $max_score ) == $max_score ) ? (int) $max_score : round( $max_score, 2 );
		wp_send_json_success( array( 'score' => $formatted_score, 'maxScore' => $formatted_max, 'result' => $result, 'entryId' => (int) $wpdb->insert_id ) );
	}

	public function register_admin_page() {
		add_submenu_page( 'apexadfo-addons', __( 'Quiz Responses', 'apex-addons-for-elementor' ), __( 'Quiz Responses', 'apex-addons-for-elementor' ), 'manage_options', 'apexadfo-quiz-responses', array( $this, 'render_admin_page' ) );
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		global $wpdb;
		// The response screen must show current data from the plugin-owned table after submissions.
		$rows = $wpdb->get_results( 'SELECT * FROM ' . esc_sql( $this->table_name() ) . ' ORDER BY created_at DESC LIMIT 200' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		?><div class="wrap"><h1><?php esc_html_e( 'Quiz Responses', 'apex-addons-for-elementor' ); ?></h1><table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Date', 'apex-addons-for-elementor' ); ?></th><th><?php esc_html_e( 'Quiz', 'apex-addons-for-elementor' ); ?></th><th><?php esc_html_e( 'Score', 'apex-addons-for-elementor' ); ?></th><th><?php esc_html_e( 'Result', 'apex-addons-for-elementor' ); ?></th></tr></thead><tbody><?php if ( ! $rows ) : ?><tr><td colspan="4"><?php esc_html_e( 'No quiz responses yet.', 'apex-addons-for-elementor' ); ?></td></tr><?php else : foreach ( $rows as $row ) : ?><tr><td><?php echo esc_html( $row->created_at ); ?></td><td><?php echo esc_html( $row->quiz_name ); ?></td><td><?php echo esc_html( $row->score . ' / ' . $row->max_score ); ?></td><td><?php echo esc_html( $row->result_title ); ?></td></tr><?php endforeach; endif; ?></tbody></table></div><?php
	}
}
