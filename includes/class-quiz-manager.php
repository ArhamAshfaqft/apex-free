<?php
/**
 * Secure quiz normalization, scoring, response storage, filtering and management.
 *
 * @package ApexAddonsForElementor
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Quiz_Manager {
	const DB_VERSION = '1.1.0';
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
		add_action( 'admin_post_apexadfo_quiz_delete_entry', array( $this, 'delete_entry' ) );
		add_action( 'admin_post_apexadfo_quiz_bulk_delete', array( $this, 'bulk_delete_entries' ) );
		add_action( 'admin_post_apexadfo_quiz_export_responses', array( $this, 'export_responses' ) );

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
		$sql   = "CREATE TABLE {$table} (
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
			if ( '' === $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line ) );
			$label = sanitize_text_field( $parts[0] ?? '' );
			if ( '' === $label ) {
				continue;
			}
			$value     = sanitize_key( $parts[1] ?? '' );
			$options[] = array(
				'label'  => $label,
				'value'  => $value ?: sanitize_title( $label ),
				'points' => max( -10000, min( 10000, (float) ( $parts[2] ?? 0 ) ) ),
			);
		}
		return array_slice( $options, 0, 100 );
	}

	public static function normalize( $settings ) {
		$steps    = array();
		$current  = -1;
		$ids      = array();
		$item_ids = array();
		$widths   = array( '20', '25', '33', '40', '50', '60', '66', '75', '80', '100' );
		foreach ( (array) ( $settings['quiz_items'] ?? array() ) as $index => $item ) {
			$type = sanitize_key( $item['type'] ?? 'heading' );
			if ( 'step' === $type ) {
				$id = sanitize_key( $item['step_id'] ?? '' );
				if ( ! $id || isset( $ids[ $id ] ) ) {
					$id = 'step-' . ( count( $steps ) + 1 );
				}
				$ids[ $id ] = true;
				$steps[]    = array(
					'id'    => $id,
					'items' => array(),
				);
				$current    = count( $steps ) - 1;
				continue;
			}
			if ( $current < 0 ) {
				$steps[] = array(
					'id'    => 'step-1',
					'items' => array(),
				);
				$current = 0;
			}
			if ( ! in_array( $type, array( 'heading', 'description', 'single', 'multiple', 'text', 'email', 'button', 'result' ), true ) ) {
				continue;
			}
			$width   = (string) ( $item['width'] ?? '100' );
			$tablet  = (string) ( $item['width_tablet'] ?? '100' );
			$mobile  = (string) ( $item['width_mobile'] ?? '100' );
			$item_id = sanitize_key( $item['item_id'] ?? $type . '-' . ( $index + 1 ) );
			if ( ! $item_id || isset( $item_ids[ $item_id ] ) ) {
				$item_id = $type . '-' . ( $index + 1 );
			}
			$item_ids[ $item_id ] = true;
			$heading_tag          = (string) ( $item['heading_tag'] ?? 'h3' );
			$normalized           = array(
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
				$found         = $this->search_elements_tree( $elements_data, $widget_id );
			}
		}

		if ( ! is_array( $found ) ) {
			$raw_data      = get_post_meta( $page_id, '_elementor_data', true );
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
				return array(
					'title'       => sanitize_text_field( $rule['result_title'] ?? $default['title'] ),
					'description' => sanitize_textarea_field( $rule['result_description'] ?? $default['description'] ),
				);
			}
		}
		return $default;
	}

	public function submit() {
		$page_id   = isset( $_POST['page_id'] ) ? absint( wp_unslash( $_POST['page_id'] ) ) : 0;
		$widget_id = isset( $_POST['widget_id'] ) ? sanitize_key( wp_unslash( $_POST['widget_id'] ) ) : '';
		$nonce     = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		if ( ! $page_id || ! $widget_id || ! wp_verify_nonce( $nonce, 'apexadfo_quiz_submit_' . $page_id . '_' . $widget_id ) ) {
			wp_send_json_error( array( 'message' => __( 'The security check failed.', 'apex-addons-for-elementor' ) ), 403 );
		}
		$settings = $this->find_widget_settings( $page_id, $widget_id );
		$steps    = is_array( $settings ) ? self::normalize( $settings ) : array();

		if ( empty( $steps ) && ! empty( $_POST['quiz_config'] ) ) {
			$raw_config    = wp_unslash( $_POST['quiz_config'] );
			$parsed_config = json_decode( $raw_config, true );
			if ( is_array( $parsed_config ) && ! empty( $parsed_config['steps'] ) ) {
				$steps    = $parsed_config['steps'];
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
		$attempts       = (int) get_transient( $rate_key );
		if ( $attempts >= 10 ) {
			wp_send_json_error( array( 'message' => __( 'Too many attempts. Please wait and try again.', 'apex-addons-for-elementor' ) ), 429 );
		}
		set_transient( $rate_key, $attempts + 1, MINUTE_IN_SECONDS );
		$answers_json = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : '{}';
		$decoded      = json_decode( $answers_json, true );
		$answers      = is_array( $decoded ) ? $decoded : array();
		$score        = 0.0;
		$max_score    = 0.0;
		$clean        = array();
		foreach ( $steps as $score_step ) {
			foreach ( $score_step['items'] as $score_item ) {
				if ( ! in_array( $score_item['type'], array( 'single', 'multiple' ), true ) ) {
					continue;
				}
				$question_max = 0.0;
				foreach ( $score_item['options'] as $option ) {
					if ( 'multiple' === $score_item['type'] ) {
						$question_max += max( 0, (float) $option['points'] );
					} else {
						$question_max = max( $question_max, (float) $option['points'] );
					}
				}
				$max_score += $question_max;
			}
		}
		foreach ( $steps as $step ) {
			foreach ( $step['items'] as $item ) {
				if ( ! in_array( $item['type'], array( 'single', 'multiple', 'text', 'email' ), true ) ) {
					continue;
				}
				$value = $answers[ $item['id'] ] ?? ( 'multiple' === $item['type'] ? array() : '' );
				$empty = '' === $value || null === $value || array() === $value;
				/* translators: %s: Quiz question or field label. */
				if ( $item['required'] && $empty ) {
					wp_send_json_error( array( 'message' => sprintf( __( '%s is required.', 'apex-addons-for-elementor' ), $item['label'] ?: __( 'This question', 'apex-addons-for-elementor' ) ) ), 422 );
				}
				if ( $empty ) {
					continue;
				}
				if ( in_array( $item['type'], array( 'single', 'multiple' ), true ) ) {
					$selected = 'multiple' === $item['type'] ? (array) $value : array( $value );
					$map      = array();
					foreach ( $item['options'] as $option ) {
						$map[ $option['value'] ] = $option;
					}
					$labels = array();
					foreach ( $selected as $choice ) {
						$choice = sanitize_key( $choice );
						if ( ! isset( $map[ $choice ] ) ) {
							wp_send_json_error( array( 'message' => __( 'An answer was invalid.', 'apex-addons-for-elementor' ) ), 422 );
						}
						$score   += (float) $map[ $choice ]['points'];
						$labels[] = $map[ $choice ]['label'];
					}
					$value = 'multiple' === $item['type'] ? $labels : reset( $labels );
				} elseif ( 'email' === $item['type'] ) {
					$value = sanitize_email( $value );
					if ( ! is_email( $value ) ) {
						wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'apex-addons-for-elementor' ) ), 422 );
					}
				} else {
					$value = sanitize_text_field( $value );
				}
				$clean[] = array(
					'id'    => $item['id'],
					'label' => $item['label'],
					'value' => $value,
				);
			}
		}
		if ( 'yes' === ( $settings['quiz_lead_gate'] ?? '' ) ) {
			$gate_email = sanitize_email( $answers['__gate_email'] ?? '' );
			if ( ! is_email( $gate_email ) ) {
				wp_send_json_error( array( 'message' => __( 'Please enter a valid email address to view the result.', 'apex-addons-for-elementor' ) ), 422 );
			}
			$clean[] = array(
				'id'    => 'lead_name',
				'label' => __( 'Name', 'apex-addons-for-elementor' ),
				'value' => sanitize_text_field( $answers['__gate_name'] ?? '' ),
			);
			$clean[] = array(
				'id'    => 'lead_email',
				'label' => __( 'Email', 'apex-addons-for-elementor' ),
				'value' => $gate_email,
			);
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

	public function delete_entry() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! current_user_can( 'manage_options' ) || ! $id ) {
			wp_die( esc_html__( 'Unauthorized request.', 'apex-addons-for-elementor' ) );
		}
		check_admin_referer( 'apexadfo_quiz_delete_entry_' . $id );
		global $wpdb;
		$wpdb->delete( $this->table_name(), array( 'id' => $id ), array( '%d' ) );
		wp_safe_redirect( admin_url( 'admin.php?page=apexadfo-quiz-responses' ) );
		exit;
	}

	public function bulk_delete_entries() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized request.', 'apex-addons-for-elementor' ) );
		}
		check_admin_referer( 'apexadfo_quiz_bulk_action' );

		$action = isset( $_POST['bulk_action'] ) ? sanitize_key( $_POST['bulk_action'] ) : ( isset( $_POST['bulk_action2'] ) ? sanitize_key( $_POST['bulk_action2'] ) : '' );
		$ids    = isset( $_POST['entry_ids'] ) ? array_map( 'absint', (array) $_POST['entry_ids'] ) : array();

		if ( 'delete' === $action && ! empty( $ids ) ) {
			global $wpdb;
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$table        = esc_sql( $this->table_name() );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		wp_safe_redirect( admin_url( 'admin.php?page=apexadfo-quiz-responses' ) );
		exit;
	}

	public function export_responses() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized request.', 'apex-addons-for-elementor' ) );
		}
		check_admin_referer( 'apexadfo_quiz_export_responses' );
		global $wpdb;
		$table   = esc_sql( $this->table_name() );
		$entries = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=apex-quiz-responses-' . gmdate( 'Y-m-d' ) . '.csv' );
		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'ID', 'Date', 'Quiz Name', 'Page', 'Score', 'Max Score', 'Result Title', 'Answers' ) );

		foreach ( $entries as $entry ) {
			$answers_data = json_decode( $entry->answers, true );
			$parts        = array();
			foreach ( (array) $answers_data as $ans ) {
				$val     = is_array( $ans['value'] ?? '' ) ? implode( ', ', $ans['value'] ) : ( $ans['value'] ?? '' );
				$parts[] = ( $ans['label'] ?? '' ) . ': ' . $val;
			}
			$row = array(
				$entry->id,
				$entry->created_at,
				$entry->quiz_name,
				get_the_title( $entry->page_id ),
				$entry->score,
				$entry->max_score,
				$entry->result_title,
				implode( ' | ', $parts ),
			);
			fputcsv( $output, array_map( array( $this, 'csv_safe' ), $row ) );
		}
		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	public function csv_safe( $value ) {
		$value = (string) $value;
		return preg_match( '/^[=+\-@]/', $value ) ? "'" . $value : $value;
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wpdb;
		$table = esc_sql( $this->table_name() );

		// Filters
		$search      = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$quiz_filter = isset( $_GET['quiz_name'] ) ? sanitize_text_field( wp_unslash( $_GET['quiz_name'] ) ) : '';
		$date_from   = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		$date_to     = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';

		$where = array( '1=1' );
		$args  = array();

		if ( ! empty( $search ) ) {
			$where[] = '(quiz_name LIKE %s OR result_title LIKE %s OR answers LIKE %s)';
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$args[]  = $like;
			$args[]  = $like;
			$args[]  = $like;
		}

		if ( ! empty( $quiz_filter ) ) {
			$where[] = 'quiz_name = %s';
			$args[]  = $quiz_filter;
		}

		if ( ! empty( $date_from ) ) {
			$where[] = 'created_at >= %s';
			$args[]  = $date_from . ' 00:00:00';
		}

		if ( ! empty( $date_to ) ) {
			$where[] = 'created_at <= %s';
			$args[]  = $date_to . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );
		$sql       = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT 500";

		if ( ! empty( $args ) ) {
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$rows = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		}

		$quiz_names = $wpdb->get_col( "SELECT DISTINCT quiz_name FROM {$table} ORDER BY quiz_name ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=apexadfo_quiz_export_responses' ), 'apexadfo_quiz_export_responses' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Quiz Responses', 'apex-addons-for-elementor' ); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export CSV', 'apex-addons-for-elementor' ); ?></a>
			<hr class="wp-header-end">

			<!-- Filter Bar -->
			<form method="get" style="margin: 15px 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; background: #fff; padding: 12px 16px; border: 1px solid #ccd0d4; border-radius: 6px;">
				<input type="hidden" name="page" value="apexadfo-quiz-responses" />
				
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search responses...', 'apex-addons-for-elementor' ); ?>" style="min-width: 200px;" />

				<select name="quiz_name">
					<option value=""><?php esc_html_e( 'All Quizzes', 'apex-addons-for-elementor' ); ?></option>
					<?php foreach ( (array) $quiz_names as $name ) : ?>
						<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $quiz_filter, $name ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>

				<label style="font-size: 13px; color: #50575e;"><?php esc_html_e( 'From:', 'apex-addons-for-elementor' ); ?>
					<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" />
				</label>

				<label style="font-size: 13px; color: #50575e;"><?php esc_html_e( 'To:', 'apex-addons-for-elementor' ); ?>
					<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" />
				</label>

				<button type="submit" class="button button-secondary"><?php esc_html_e( 'Filter', 'apex-addons-for-elementor' ); ?></button>
				<?php if ( ! empty( $search ) || ! empty( $quiz_filter ) || ! empty( $date_from ) || ! empty( $date_to ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=apexadfo-quiz-responses' ) ); ?>" class="button button-link" style="color: #d63638;"><?php esc_html_e( 'Reset Filters', 'apex-addons-for-elementor' ); ?></a>
				<?php endif; ?>
			</form>

			<!-- Bulk Actions Form -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="apexadfo_quiz_bulk_delete" />
				<?php wp_nonce_field( 'apexadfo_quiz_bulk_action' ); ?>

				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<select name="bulk_action">
							<option value=""><?php esc_html_e( 'Bulk Actions', 'apex-addons-for-elementor' ); ?></option>
							<option value="delete"><?php esc_html_e( 'Delete', 'apex-addons-for-elementor' ); ?></option>
						</select>
						<button type="submit" class="button action" onclick="return confirm('<?php echo esc_js( esc_html__( 'Are you sure you want to delete the selected items?', 'apex-addons-for-elementor' ) ); ?>')"><?php esc_html_e( 'Apply', 'apex-addons-for-elementor' ); ?></button>
					</div>
					<div class="tablenav-pages">
						<span class="displaying-num"><?php printf( esc_html__( '%d items', 'apex-addons-for-elementor' ), count( $rows ) ); ?></span>
					</div>
				</div>

				<table class="widefat striped">
					<thead>
						<tr>
							<td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all" onclick="jQuery('.quiz-cb').prop('checked', this.checked);" /></td>
							<th><?php esc_html_e( 'Date', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Quiz Name', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Answers & Details', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Score', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Result', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'apex-addons-for-elementor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $rows ) ) : ?>
							<tr>
								<td colspan="7"><?php esc_html_e( 'No quiz responses found.', 'apex-addons-for-elementor' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $rows as $row ) : 
								$answers_data = json_decode( $row->answers, true );
							?>
								<tr>
									<th scope="row" class="check-column"><input type="checkbox" name="entry_ids[]" class="quiz-cb" value="<?php echo esc_attr( $row->id ); ?>" /></th>
									<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row->created_at ) ); ?></td>
									<td><strong><?php echo esc_html( $row->quiz_name ); ?></strong></td>
									<td>
										<?php if ( is_array( $answers_data ) && ! empty( $answers_data ) ) : ?>
											<details>
												<summary style="cursor: pointer; color: #2271b1; font-weight: 600;"><?php printf( esc_html__( 'View Answers (%d)', 'apex-addons-for-elementor' ), count( $answers_data ) ); ?></summary>
												<div style="margin-top: 8px; font-size: 12px; background: #f6f7f7; padding: 8px 12px; border-radius: 4px; border: 1px solid #dcdcde;">
													<?php foreach ( $answers_data as $ans ) : 
														$val = is_array( $ans['value'] ?? '' ) ? implode( ', ', $ans['value'] ) : ( $ans['value'] ?? '' );
													?>
														<div style="margin-bottom: 4px;"><strong><?php echo esc_html( $ans['label'] ?? '' ); ?>:</strong> <?php echo esc_html( $val ); ?></div>
													<?php endforeach; ?>
												</div>
											</details>
										<?php else : ?>
											<span style="color: #a7aaad;"><?php esc_html_e( 'No answers recorded', 'apex-addons-for-elementor' ); ?></span>
										<?php endif; ?>
									</td>
									<td><span class="badge" style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 12px; font-weight: 700;"><?php echo esc_html( $row->score . ' / ' . $row->max_score ); ?></span></td>
									<td><strong><?php echo esc_html( $row->result_title ); ?></strong></td>
									<td>
										<a class="button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=apexadfo_quiz_delete_entry&id=' . absint( $row->id ) ), 'apexadfo_quiz_delete_entry_' . absint( $row->id ) ) ); ?>" onclick="return confirm('<?php echo esc_js( esc_html__( 'Delete this quiz response permanently?', 'apex-addons-for-elementor' ) ); ?>')">
											<?php esc_html_e( 'Delete', 'apex-addons-for-elementor' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</form>
		</div>
		<?php
	}
}
