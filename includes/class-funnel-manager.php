<?php
/**
 * Conversational Funnel lead storage and secure submission service.
 *
 * Funnel construction lives entirely in the Elementor widget. This service
 * deliberately owns only persistence, validation, notifications and the
 * central lead inbox.
 *
 * @package ApexAddonsForElementor
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Funnel_Manager {

	const DB_VERSION = '1.1.0';

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_leads_page' ), 30 );
		add_action( 'wp_ajax_apexadfo_funnel_submit', array( $this, 'submit' ) );
		add_action( 'wp_ajax_nopriv_apexadfo_funnel_submit', array( $this, 'submit' ) );
		add_action( 'admin_post_apexadfo_funnel_delete_lead', array( $this, 'delete_lead' ) );
		add_action( 'admin_post_apexadfo_funnel_bulk_delete', array( $this, 'bulk_delete_leads' ) );
		add_action( 'admin_post_apexadfo_funnel_export_leads', array( $this, 'export_leads' ) );

		if ( self::DB_VERSION !== get_option( 'apexadfo_funnel_db_version' ) ) {
			$this->create_table();
		}
	}

	public function register_leads_page() {
		add_submenu_page(
			'apexadfo-addons',
			esc_html__( 'Funnel Leads', 'apex-addons-for-elementor' ),
			esc_html__( 'Funnel Leads', 'apex-addons-for-elementor' ),
			'manage_options',
			'apexadfo-funnel-leads',
			array( $this, 'render_leads_page' )
		);
	}

	private function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'apexadfo_funnel_entries';
	}

	private function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table   = esc_sql( $this->table_name() );
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			funnel_id bigint(20) unsigned NOT NULL DEFAULT 0,
			widget_id varchar(80) NOT NULL DEFAULT '',
			page_id bigint(20) unsigned NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'new',
			submission_data longtext NOT NULL,
			meta_data longtext NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY funnel_id (funnel_id),
			KEY widget_id (widget_id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset};";
		dbDelta( $sql );
		update_option( 'apexadfo_funnel_db_version', self::DB_VERSION, false );
	}

	private static function parse_field_options( $raw_options ) {
		$options = array();
		$lines   = preg_split( '/\r\n|\r|\n/', (string) $raw_options );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts     = array_map( 'trim', explode( '|', $line, 2 ) );
			$label     = sanitize_text_field( $parts[0] );
			$value     = sanitize_text_field( $parts[1] ?? '' );
			$options[] = array(
				'label' => $label,
				'value' => '' !== $value ? $value : sanitize_title( $label ),
			);
		}
		return $options;
	}

	private static function normalize_field_builder( $settings ) {
		$items       = (array) ( $settings['funnel_fields'] ?? array() );
		$steps       = array();
		$step_ids    = array();
		$field_ids   = array();
		$current     = -1;
		$field_types = array( 'heading', 'description', 'button', 'result', 'text', 'email', 'tel', 'textarea', 'select', 'radio', 'checkbox', 'acceptance', 'number', 'date', 'time', 'html', 'hidden' );
		$widths      = array( '20', '25', '33', '40', '50', '60', '66', '75', '80', '100' );

		foreach ( $items as $index => $item ) {
			$type = sanitize_key( $item['type'] ?? 'text' );
			if ( 'step' === $type ) {
				$id = sanitize_key( $item['step_id'] ?? 'step-' . ( count( $steps ) + 1 ) );
				if ( ! $id || isset( $step_ids[ $id ] ) ) {
					$id = 'step-' . ( count( $steps ) + 1 );
				}
				$kind = sanitize_key( $item['step_kind'] ?? 'form' );
				if ( ! in_array( $kind, array( 'intro', 'form', 'success' ), true ) ) {
					$kind = 'form';
				}
				$step_ids[ $id ] = true;
				$steps[]         = array(
					'id'           => $id,
					'type'         => $kind,
					'title'        => '',
					'description'  => '',
					'button_label' => '',
					'next'         => '',
					'routes'       => array(),
					'fields'       => array(),
				);
				$current         = count( $steps ) - 1;
				// Preserve funnels saved by the earlier combined Step control.
				if ( 'success' === $kind ) {
					$steps[ $current ]['fields'][] = array( 'id' => 'legacy-result-' . $index, 'type' => 'result', 'show_icon' => true, 'width' => '100', 'width_tablet' => '100', 'width_mobile' => '100' );
				}
				if ( ! empty( $item['step_title'] ) ) {
					$steps[ $current ]['fields'][] = array( 'id' => 'legacy-heading-' . $index, 'type' => 'heading', 'content' => sanitize_text_field( $item['step_title'] ), 'tag' => 'h3', 'width' => '100', 'width_tablet' => '100', 'width_mobile' => '100' );
				}
				if ( ! empty( $item['step_description'] ) ) {
					$steps[ $current ]['fields'][] = array( 'id' => 'legacy-description-' . $index, 'type' => 'description', 'content' => sanitize_textarea_field( $item['step_description'] ), 'width' => '100', 'width_tablet' => '100', 'width_mobile' => '100' );
				}
				if ( array_key_exists( 'step_kind', $item ) && 'success' !== $kind ) {
					$steps[ $current ]['fields'][] = array( 'id' => 'legacy-button-' . $index, 'type' => 'button', 'button_label' => sanitize_text_field( $item['step_button_label'] ?? '' ), 'width' => '100', 'width_tablet' => '100', 'width_mobile' => '100' );
				}
				continue;
			}
			if ( ! in_array( $type, $field_types, true ) ) {
				$type = 'text';
			}
			if ( $current < 0 ) {
				$steps[]            = array(
					'id'           => 'step-1',
					'type'         => 'form',
					'title'        => '',
					'description'  => '',
					'button_label' => '',
					'next'         => '',
					'routes'       => array(),
					'fields'       => array(),
				);
				$step_ids['step-1'] = true;
				$current            = 0;
			}
			$id = sanitize_key( $item['field_id'] ?? 'field-' . ( $index + 1 ) );
			if ( in_array( $type, array( 'heading', 'description', 'button', 'result' ), true ) ) {
				$id = $type . '-' . ( $index + 1 );
			} elseif ( 'html' === $type ) {
				$id = 'content-' . ( $index + 1 );
			} elseif ( ! $id || isset( $field_ids[ $id ] ) ) {
				$id = 'field-' . ( $index + 1 );
			}
			$field_ids[ $id ]              = true;
			$width_value                   = (string) ( $item['width'] ?? '100' );
			$tablet_value                  = (string) ( $item['width_tablet'] ?? '100' );
			$mobile_value                  = (string) ( $item['width_mobile'] ?? '100' );
			$width                         = in_array( $width_value, $widths, true ) ? $width_value : '100';
			$tablet                        = in_array( $tablet_value, $widths, true ) ? $tablet_value : '100';
			$mobile                        = in_array( $mobile_value, $widths, true ) ? $mobile_value : '100';
			$heading_tag                   = (string) ( $item['heading_tag'] ?? 'h3' );
			$field = array(
				'id'           => $id,
				'type'         => $type,
				'label'        => sanitize_text_field( $item['label'] ?? '' ),
				'placeholder'  => sanitize_text_field( $item['placeholder'] ?? '' ),
				'required'     => 'yes' === ( $item['required'] ?? '' ),
				'default'      => sanitize_text_field( $item['default_value'] ?? '' ),
				'content'      => 'html' === $type ? wp_kses_post( $item['html_content'] ?? '' ) : ( in_array( $type, array( 'heading', 'description' ), true ) ? sanitize_textarea_field( $item['content_text'] ?? '' ) : '' ),
				'tag'          => 'heading' === $type && in_array( $heading_tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ), true ) ? $heading_tag : 'h3',
				'button_label' => 'button' === $type ? sanitize_text_field( $item['button_text'] ?? '' ) : '',
				'show_icon'    => 'result' === $type && 'yes' === ( $item['result_icon'] ?? 'yes' ),
				'width'        => $width,
				'width_tablet' => $tablet,
				'width_mobile' => $mobile,
				'options'      => in_array( $type, array( 'select', 'radio', 'checkbox' ), true ) ? self::parse_field_options( $item['options'] ?? '' ) : array(),
			);
			$steps[ $current ]['fields'][] = $field;
			if ( 'result' === $type ) {
				$steps[ $current ]['type'] = 'success';
			} elseif ( ! in_array( $type, array( 'heading', 'description', 'button', 'html', 'hidden' ), true ) && 'success' !== $steps[ $current ]['type'] ) {
				$steps[ $current ]['type'] = 'form';
			}
		}

		foreach ( (array) ( $settings['funnel_routes'] ?? array() ) as $route ) {
				$from   = sanitize_key( $route['route_from'] ?? '' );
				$answer = sanitize_text_field( $route['route_answer'] ?? '' );
				$to     = sanitize_key( $route['route_to'] ?? '' );
				$score  = (int) ( $route['route_score'] ?? 0 );
				foreach ( $steps as &$step ) {
					$field_match = in_array( $from, wp_list_pluck( $step['fields'], 'id' ), true );
					if ( ! $field_match && $step['id'] !== $from ) {
						continue;
					}
					$step['routes'][] = array(
						'field'  => $field_match ? $from : '',
						'answer' => $answer,
						'next'   => $to,
						'score'  => $score,
					);
					if ( '' === $answer ) {
						$step['next'] = $to;
					}
				}
				unset( $step );
		}

		foreach ( $steps as &$step ) {
			if ( 'success' === $step['type'] ) {
				continue;
			}
			$input_types = array( 'text', 'email', 'tel', 'textarea', 'select', 'radio', 'checkbox', 'acceptance', 'number', 'date', 'time' );
			$step['type'] = array_intersect( $input_types, wp_list_pluck( $step['fields'], 'type' ) ) ? 'form' : 'intro';
		}
		unset( $step );

		return apply_filters( 'apexadfo_funnel_normalized_steps', array_slice( $steps, 0, 100 ), $settings );
	}

	public static function normalize_steps( $settings ) {
		if ( ! empty( $settings['funnel_fields'] ) && is_array( $settings['funnel_fields'] ) ) {
			return self::normalize_field_builder( $settings );
		}
		$raw_steps = isset( $settings['funnel_steps'] ) && is_array( $settings['funnel_steps'] ) ? $settings['funnel_steps'] : array();
		$steps     = array();
		$used      = array();
		$types     = array( 'welcome', 'single', 'multiple', 'text', 'email', 'phone', 'number', 'textarea', 'date', 'time', 'contact', 'success' );
		foreach ( $raw_steps as $index => $raw ) {
			$id = sanitize_key( $raw['step_id'] ?? 'step-' . ( $index + 1 ) );
			if ( ! $id || isset( $used[ $id ] ) ) {
				$id = 'step-' . ( $index + 1 );
			}
			$used[ $id ] = true;
			$type        = sanitize_key( $raw['step_type'] ?? 'text' );
			if ( ! in_array( $type, $types, true ) ) {
				$type = 'text';
			}
			$choices = array();
			if ( in_array( $type, array( 'single', 'multiple' ), true ) ) {
				$lines = preg_split( '/\r\n|\r|\n/', (string) ( $raw['step_choices'] ?? '' ) );
				foreach ( $lines as $line ) {
					$line = trim( $line );
					if ( '' === $line ) {
						continue;
					}
					$parts     = array_map( 'trim', explode( '|', $line, 2 ) );
					$label     = sanitize_text_field( $parts[0] );
					$value     = sanitize_text_field( $parts[1] ?? '' );
					$choices[] = array(
						'label' => $label,
						'value' => '' !== $value ? $value : sanitize_title( $label ),
						'next'  => '',
						'score' => 0,
					);
				}
			}
			$steps[] = array(
				'id'           => $id,
				'type'         => $type,
				'title'        => sanitize_text_field( $raw['step_title'] ?? '' ),
				'description'  => sanitize_textarea_field( $raw['step_description'] ?? '' ),
				'placeholder'  => sanitize_text_field( $raw['step_placeholder'] ?? '' ),
				'button_label' => sanitize_text_field( $raw['step_button_label'] ?? '' ),
				'required'     => 'yes' === ( $raw['step_required'] ?? '' ),
				'next'         => '',
				'routes'       => array(),
				'choices'      => $choices,
			);
		}

		$routes = isset( $settings['funnel_routes'] ) && is_array( $settings['funnel_routes'] ) ? $settings['funnel_routes'] : array();
		foreach ( $routes as $route ) {
				$from   = sanitize_key( $route['route_from'] ?? '' );
				$answer = sanitize_text_field( $route['route_answer'] ?? '' );
				$to     = sanitize_key( $route['route_to'] ?? '' );
				$score  = (int) ( $route['route_score'] ?? 0 );
				foreach ( $steps as &$step ) {
					if ( $step['id'] !== $from ) {
						continue;
					}
					if ( '' === $answer ) {
						$step['next'] = $to;
					}
					$step['routes'][] = array(
						'answer' => $answer,
						'next'   => $to,
						'score'  => $score,
					);
					foreach ( $step['choices'] as &$choice ) {
						if ( $choice['value'] === $answer ) {
							$choice['next']  = $to;
							$choice['score'] = 0;
						}
					}
					unset( $choice );
				}
				unset( $step );
		}

		return apply_filters( 'apexadfo_funnel_normalized_steps', array_slice( $steps, 0, 100 ), $settings );
	}

	public function submit() {
		$page_id   = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
		$widget_id = isset( $_POST['widget_id'] ) ? sanitize_key( wp_unslash( $_POST['widget_id'] ) ) : '';
		$nonce     = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! $page_id || ! $widget_id || ! wp_verify_nonce( $nonce, 'apexadfo_funnel_submit_' . $page_id . '_' . $widget_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'The security check failed. Refresh the page and try again.', 'apex-addons-for-elementor' ) ), 403 );
		}
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_success( array( 'message' => esc_html__( 'Thank you.', 'apex-addons-for-elementor' ) ) );
		}

		$post = get_post( $page_id );
		if ( ! $post ) {
			wp_send_json_error( array( 'message' => esc_html__( 'This funnel is not available.', 'apex-addons-for-elementor' ) ), 404 );
		}
		$settings = null;
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $page_id );
			if ( $document ) {
				$elements_data = $document->get_elements_data();
				$settings = self::find_widget_settings( is_array( $elements_data ) ? $elements_data : array(), $widget_id );
			}
		}
		if ( ! is_array( $settings ) ) {
			$elements = json_decode( get_post_meta( $page_id, '_elementor_data', true ), true );
			$settings = self::find_widget_settings( is_array( $elements ) ? $elements : array(), $widget_id );
		}
		if ( ! is_array( $settings ) || ( empty( $settings['funnel_fields'] ) && empty( $settings['funnel_steps'] ) ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'The funnel configuration could not be verified.', 'apex-addons-for-elementor' ) ), 400 );
		}

		$ip       = $this->client_ip();
		$rate_key = 'apexadfo_funnel_rate_' . md5( $ip . '|' . $page_id . '|' . $widget_id );
		$attempts = (int) get_transient( $rate_key );
		if ( $attempts >= (int) apply_filters( 'apexadfo_funnel_rate_limit', 5, $page_id, $widget_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Too many requests. Please wait before trying again.', 'apex-addons-for-elementor' ) ), 429 );
		}
		set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

		// Sanitize the serialized request before decoding; individual values are
		// subsequently validated against the authoritative server-side schema.
		$answers_json = isset( $_POST['answers'] ) ? sanitize_textarea_field( wp_unslash( $_POST['answers'] ) ) : '{}';
		$answers_raw  = json_decode( $answers_json, true );
		if ( ! is_array( $answers_raw ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid submission data.', 'apex-addons-for-elementor' ) ), 400 );
		}
		$steps     = self::normalize_steps( $settings );
		$path      = $this->resolve_submission_path( $steps, $answers_raw );
		$validated = $this->validate_answers( $path, $answers_raw );
		if ( is_wp_error( $validated ) ) {
			wp_send_json_error( array( 'message' => $validated->get_error_message() ), 422 );
		}

		$route_score = array_sum( wp_list_pluck( $path, '_route_score' ) );
		$score       = array_sum( wp_list_pluck( $validated, 'score' ) ) + $route_score;
		$funnel_name = sanitize_text_field( $settings['funnel_name'] ?? get_the_title( $page_id ) );
		$meta        = array(
			'funnel_name' => $funnel_name,
			'ip_hash'     => wp_hash( $ip ),
			'user_agent'  => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
			'source_url'  => esc_url_raw( wp_unslash( $_POST['source_url'] ?? '' ) ),
			'referrer'    => esc_url_raw( wp_unslash( $_POST['referrer'] ?? '' ) ),
			'score'       => $score,
		);

		global $wpdb;
		// A direct insert is appropriate for the plugin-owned lead table; no read cache is involved.
		$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$this->table_name(),
			array(
				'funnel_id'       => 0,
				'widget_id'       => $widget_id,
				'page_id'         => $page_id,
				'status'          => 'new',
				'submission_data' => wp_json_encode( $validated ),
				'meta_data'       => wp_json_encode( $meta ),
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%d', '%s', '%s', '%s', '%s' )
		);
		if ( false === $inserted ) {
			wp_send_json_error( array( 'message' => esc_html__( 'The lead could not be saved. Please try again.', 'apex-addons-for-elementor' ) ), 500 );
		}

		$this->send_notification( $settings, $funnel_name, $validated, $score );
		do_action( 'apexadfo_funnel_after_submission', (int) $wpdb->insert_id, $page_id, $widget_id, $validated, $meta, $settings );
		wp_send_json_success(
			array(
				'message' => sanitize_text_field( $settings['success_message'] ?? esc_html__( 'Thank you. Your details have been received.', 'apex-addons-for-elementor' ) ),
				'lead_id' => (int) $wpdb->insert_id,
			)
		);
	}

	private static function find_widget_settings( $elements, $widget_id ) {
		foreach ( $elements as $element ) {
			if ( 'widget' === ( $element['elType'] ?? '' ) && 'eas-conversational-funnel' === ( $element['widgetType'] ?? '' ) && $widget_id === ( $element['id'] ?? '' ) ) {
				return is_array( $element['settings'] ?? null ) ? $element['settings'] : array();
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$found = self::find_widget_settings( $element['elements'], $widget_id );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	private function resolve_submission_path( $steps, $answers ) {
		if ( empty( $steps ) ) {
			return array();
		}
		$map = array();
		foreach ( $steps as $index => $step ) {
			$step['_index']     = $index;
			$map[ $step['id'] ] = $step;
			$steps[ $index ]    = $step;
		}
		$path    = array();
		$current = $steps[0];
		$visited = array();
		$limit   = count( $steps ) + 2;
		for ( $guard = 0; $guard < $limit; $guard++ ) {
			$id = $current['id'];
			if ( isset( $visited[ $id ] ) ) {
				break;
			}
			$visited[ $id ]          = true;
			$next_id                 = sanitize_key( $current['next'] ?? '' );
			$current['_route_score'] = 0;
			$default_route           = null;
			foreach ( (array) ( $current['routes'] ?? array() ) as $route ) {
				$field_id = sanitize_key( $route['field'] ?? $id );
				$value    = $answers[ $field_id ] ?? '';
				$values   = array_map( 'strval', (array) $value );
				if ( '' === (string) $route['answer'] ) {
					$default_route = $route;
					continue;
				}
				if ( in_array( (string) $route['answer'], $values, true ) ) {
					$next_id                 = sanitize_key( $route['next'] );
					$current['_route_score'] = (int) $route['score'];
					$default_route           = null;
					break;
				}
			}
			if ( null !== $default_route ) {
				$next_id                 = sanitize_key( $default_route['next'] );
				$current['_route_score'] = (int) $default_route['score'];
			}
			$path[] = $current;
			if ( 'success' === $current['type'] ) {
				break;
			}
			if ( $next_id && isset( $map[ $next_id ] ) ) {
				$current = $map[ $next_id ];
				continue;
			}
			$next_index = (int) $current['_index'] + 1;
			if ( ! isset( $steps[ $next_index ] ) ) {
				break;
			}
			$current = $steps[ $next_index ];
		}
		return $path;
	}

	private function validate_answers( $steps, $answers ) {
		$result = array();
		foreach ( $steps as $step ) {
			if ( isset( $step['fields'] ) && is_array( $step['fields'] ) ) {
				foreach ( $step['fields'] as $field ) {
					if ( in_array( $field['type'], array( 'heading', 'description', 'button', 'result', 'html' ), true ) ) {
						continue;
					}
					$value     = 'hidden' === $field['type'] ? $field['default'] : ( $answers[ $field['id'] ] ?? '' );
					$validated = $this->validate_field_answer( $field, $value );
					if ( is_wp_error( $validated ) ) {
						return $validated;
					}
					if ( null !== $validated ) {
						$result[] = $validated;
					}
				}
				continue;
			}
			$id   = $step['id'];
			$type = $step['type'];
			if ( in_array( $type, array( 'welcome', 'success' ), true ) ) {
				continue;
			}
			$value = $answers[ $id ] ?? '';
			$empty = '' === $value || null === $value || array() === $value;
			if ( $step['required'] && $empty ) {
				/* translators: %s: Field or question label. */
				return new \WP_Error( 'required', sprintf( esc_html__( '%s is required.', 'apex-addons-for-elementor' ), $step['title'] ) );
			}
			if ( $empty ) {
				continue;
			}
			$score            = 0;
			$submitted_values = array_map( 'strval', (array) $value );
			$default_route    = null;
			foreach ( (array) ( $step['routes'] ?? array() ) as $route ) {
				if ( '' === (string) $route['answer'] ) {
					$default_route = $route;
					continue;
				}
				if ( in_array( (string) $route['answer'], $submitted_values, true ) ) {
					$score        += (int) $route['score'];
					$default_route = null;
					break;
				}
			}
			if ( null !== $default_route ) {
				$score += (int) $default_route['score'];
			}
			if ( in_array( $type, array( 'single', 'multiple' ), true ) ) {
				$values  = 'multiple' === $type ? (array) $value : array( $value );
				$allowed = array();
				foreach ( $step['choices'] as $choice ) {
					$allowed[ (string) $choice['value'] ] = $choice;
				}
				$clean = array();
				foreach ( $values as $item ) {
					$item = sanitize_text_field( $item );
					if ( ! isset( $allowed[ $item ] ) ) {
						return new \WP_Error( 'choice', esc_html__( 'One of the selected answers is invalid.', 'apex-addons-for-elementor' ) );
					}
					$clean[] = $allowed[ $item ]['label'];
					$score  += (int) $allowed[ $item ]['score'];
				}
				$value = 'multiple' === $type ? $clean : reset( $clean );
			} elseif ( 'email' === $type ) {
				$value = sanitize_email( $value );
				if ( ! is_email( $value ) ) {
					return new \WP_Error( 'email', esc_html__( 'Please enter a valid email address.', 'apex-addons-for-elementor' ) );
				}
			} elseif ( 'contact' === $type ) {
				$value            = is_array( $value ) ? $value : array();
				$value            = array(
					'name'  => sanitize_text_field( $value['name'] ?? '' ),
					'email' => sanitize_email( $value['email'] ?? '' ),
					'phone' => sanitize_text_field( $value['phone'] ?? '' ),
				);
				$contact_supplied = ! empty( $value['name'] ) || ! empty( $value['email'] ) || ! empty( $value['phone'] );
				if ( ( $step['required'] || $contact_supplied ) && ( ! $value['name'] || ! is_email( $value['email'] ) ) ) {
					return new \WP_Error( 'contact', esc_html__( 'Please provide your name and a valid email address.', 'apex-addons-for-elementor' ) );
				}
			} elseif ( 'textarea' === $type ) {
				$value = sanitize_textarea_field( $value );
			} elseif ( 'number' === $type ) {
				if ( ! is_numeric( $value ) ) {
					return new \WP_Error( 'number', esc_html__( 'Please enter a valid number.', 'apex-addons-for-elementor' ) );
				}
				$value = (float) $value;
			} else {
				$value = sanitize_text_field( $value );
			}
			$result[] = array(
				'id'    => $id,
				'label' => $step['title'],
				'value' => $value,
				'score' => $score,
			);
		}
		return $result;
	}

	private function validate_field_answer( $field, $value ) {
		$type  = $field['type'];
		$empty = '' === $value || null === $value || array() === $value || ( 'acceptance' === $type && ! $value );
		if ( $field['required'] && $empty ) {
			/* translators: %s is the configured field label. */
			return new \WP_Error( 'required', sprintf( esc_html__( '%s is required.', 'apex-addons-for-elementor' ), $field['label'] ) );
		}
		if ( $empty && 'hidden' !== $type ) {
			return null;
		}

		if ( in_array( $type, array( 'select', 'radio', 'checkbox' ), true ) ) {
			$values  = 'checkbox' === $type ? (array) $value : array( $value );
			$allowed = array();
			foreach ( $field['options'] as $option ) {
				$allowed[ (string) $option['value'] ] = $option['label'];
			}
			$clean = array();
			foreach ( $values as $item ) {
				$item = sanitize_text_field( $item );
				if ( ! isset( $allowed[ $item ] ) ) {
					return new \WP_Error( 'choice', esc_html__( 'One of the selected answers is invalid.', 'apex-addons-for-elementor' ) );
				}
				$clean[] = $allowed[ $item ];
			}
			$value = 'checkbox' === $type ? $clean : reset( $clean );
		} elseif ( 'email' === $type ) {
			$value = sanitize_email( $value );
			if ( ! is_email( $value ) ) {
				return new \WP_Error( 'email', esc_html__( 'Please enter a valid email address.', 'apex-addons-for-elementor' ) );
			}
		} elseif ( 'textarea' === $type ) {
			$value = sanitize_textarea_field( $value );
		} elseif ( 'number' === $type ) {
			if ( ! is_numeric( $value ) ) {
				return new \WP_Error( 'number', esc_html__( 'Please enter a valid number.', 'apex-addons-for-elementor' ) );
			}
			$value = (float) $value;
		} elseif ( 'acceptance' === $type ) {
			$value = esc_html__( 'Accepted', 'apex-addons-for-elementor' );
		} else {
			$value = sanitize_text_field( $value );
		}

		return array(
			'id'    => $field['id'],
			'label' => $field['label'] ?: $field['id'],
			'value' => $value,
			'score' => 0,
		);
	}

	private function send_notification( $settings, $funnel_name, $answers, $score ) {
		$to = sanitize_email( $settings['recipient_email'] ?? get_option( 'admin_email' ) );
		if ( ! $to ) {
			return;
		}
		$subject = sanitize_text_field( $settings['email_subject'] ?? esc_html__( 'New funnel lead: {funnel}', 'apex-addons-for-elementor' ) );
		$subject = str_replace( '{funnel}', $funnel_name, $subject );
		/* translators: %s: Funnel name. */
		$lines = array( sprintf( esc_html__( 'New lead from %s', 'apex-addons-for-elementor' ), $funnel_name ), '' );
		foreach ( $answers as $answer ) {
			$value   = is_array( $answer['value'] ) ? $this->flatten_value( $answer['value'] ) : $answer['value'];
			$lines[] = $answer['label'] . ': ' . $value;
		}
		if ( $score ) {
			$lines[] = '';
			/* translators: %d: Numeric lead score. */
			$lines[] = sprintf( esc_html__( 'Lead score: %d', 'apex-addons-for-elementor' ), $score );
		}
		wp_mail( $to, $subject, implode( "\n", $lines ) );
	}

	private function flatten_value( $value ) {
		$parts = array();
		foreach ( $value as $key => $item ) {
			$parts[] = is_string( $key ) ? $key . ': ' . sanitize_text_field( $item ) : sanitize_text_field( $item );
		}
		return implode( ', ', $parts );
	}

	private function client_ip() {
		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ) );
	}

	public function render_leads_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view these leads.', 'apex-addons-for-elementor' ) );
		}
		global $wpdb;
		$table = esc_sql( $this->table_name() );

		// Filters
		$search        = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$status_filter = isset( $_GET['status_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['status_filter'] ) ) : '';
		$date_from     = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		$date_to       = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';

		$where = array( '1=1' );
		$args  = array();

		if ( ! empty( $search ) ) {
			$where[] = '(submission_data LIKE %s OR meta_data LIKE %s)';
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$args[]  = $like;
			$args[]  = $like;
		}

		if ( ! empty( $status_filter ) ) {
			$where[] = 'status = %s';
			$args[]  = $status_filter;
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
			$entries = $wpdb->get_results( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$entries = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		}

		$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=apexadfo_funnel_export_leads' ), 'apexadfo_funnel_export_leads' );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Funnel Leads', 'apex-addons-for-elementor' ); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export CSV', 'apex-addons-for-elementor' ); ?></a>
			<hr class="wp-header-end">

			<!-- Filter Bar -->
			<form method="get" style="margin: 15px 0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; background: #fff; padding: 12px 16px; border: 1px solid #ccd0d4; border-radius: 6px;">
				<input type="hidden" name="page" value="apexadfo-funnel-leads" />

				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search leads...', 'apex-addons-for-elementor' ); ?>" style="min-width: 200px;" />

				<select name="status_filter">
					<option value=""><?php esc_html_e( 'All Statuses', 'apex-addons-for-elementor' ); ?></option>
					<option value="new" <?php selected( $status_filter, 'new' ); ?>><?php esc_html_e( 'New', 'apex-addons-for-elementor' ); ?></option>
					<option value="contacted" <?php selected( $status_filter, 'contacted' ); ?>><?php esc_html_e( 'Contacted', 'apex-addons-for-elementor' ); ?></option>
					<option value="converted" <?php selected( $status_filter, 'converted' ); ?>><?php esc_html_e( 'Converted', 'apex-addons-for-elementor' ); ?></option>
				</select>

				<label style="font-size: 13px; color: #50575e;"><?php esc_html_e( 'From:', 'apex-addons-for-elementor' ); ?>
					<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>" />
				</label>

				<label style="font-size: 13px; color: #50575e;"><?php esc_html_e( 'To:', 'apex-addons-for-elementor' ); ?>
					<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>" />
				</label>

				<button type="submit" class="button button-secondary"><?php esc_html_e( 'Filter', 'apex-addons-for-elementor' ); ?></button>
				<?php if ( ! empty( $search ) || ! empty( $status_filter ) || ! empty( $date_from ) || ! empty( $date_to ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=apexadfo-funnel-leads' ) ); ?>" class="button button-link" style="color: #d63638;"><?php esc_html_e( 'Reset Filters', 'apex-addons-for-elementor' ); ?></a>
				<?php endif; ?>
			</form>

			<!-- Bulk Actions Form -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="apexadfo_funnel_bulk_delete" />
				<?php wp_nonce_field( 'apexadfo_funnel_bulk_action' ); ?>

				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<select name="bulk_action">
							<option value=""><?php esc_html_e( 'Bulk Actions', 'apex-addons-for-elementor' ); ?></option>
							<option value="delete"><?php esc_html_e( 'Delete', 'apex-addons-for-elementor' ); ?></option>
						</select>
						<button type="submit" class="button action" onclick="return confirm('<?php echo esc_js( esc_html__( 'Are you sure you want to delete the selected items?', 'apex-addons-for-elementor' ) ); ?>')"><?php esc_html_e( 'Apply', 'apex-addons-for-elementor' ); ?></button>
					</div>
					<div class="tablenav-pages">
						<span class="displaying-num"><?php printf( esc_html__( '%d items', 'apex-addons-for-elementor' ), count( $entries ) ); ?></span>
					</div>
				</div>

				<table class="widefat striped">
					<thead>
						<tr>
							<td class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all" onclick="jQuery('.lead-cb').prop('checked', this.checked);" /></td>
							<th><?php esc_html_e( 'Date', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Funnel Name', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Lead Details', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Score', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Status', 'apex-addons-for-elementor' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'apex-addons-for-elementor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $entries ) ) : ?>
							<tr>
								<td colspan="7"><?php esc_html_e( 'No funnel leads found.', 'apex-addons-for-elementor' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $entries as $entry ) :
								$data = json_decode( $entry->submission_data, true );
								$meta = json_decode( $entry->meta_data, true );
								$name = $meta['funnel_name'] ?? get_the_title( $entry->funnel_id ?: $entry->page_id );
							?>
								<tr>
									<th scope="row" class="check-column"><input type="checkbox" name="lead_ids[]" class="lead-cb" value="<?php echo esc_attr( $entry->id ); ?>" /></th>
									<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $entry->created_at ) ); ?></td>
									<td><strong><?php echo esc_html( $name ); ?></strong></td>
									<td>
										<?php if ( is_array( $data ) && ! empty( $data ) ) : ?>
											<details>
												<summary style="cursor: pointer; color: #2271b1; font-weight: 600;"><?php printf( esc_html__( 'View Lead Data (%d fields)', 'apex-addons-for-elementor' ), count( $data ) ); ?></summary>
												<div style="margin-top: 8px; font-size: 12px; background: #f6f7f7; padding: 8px 12px; border-radius: 4px; border: 1px solid #dcdcde;">
													<?php foreach ( (array) $data as $answer ) : 
														$value = is_array( $answer['value'] ?? '' ) ? $this->flatten_value( $answer['value'] ) : ( $answer['value'] ?? '' );
													?>
														<div style="margin-bottom: 4px;"><strong><?php echo esc_html( $answer['label'] ?? '' ); ?>:</strong> <?php echo esc_html( $value ); ?></div>
													<?php endforeach; ?>
												</div>
											</details>
										<?php else : ?>
											<span style="color: #a7aaad;"><?php esc_html_e( 'No lead data', 'apex-addons-for-elementor' ); ?></span>
										<?php endif; ?>
									</td>
									<td><span class="badge" style="background: #ecfdf5; color: #047857; padding: 3px 8px; border-radius: 12px; font-weight: 700;"><?php echo esc_html( (int) ( $meta['score'] ?? 0 ) ); ?></span></td>
									<td>
										<span class="status-badge" style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-weight: 600; text-transform: uppercase; font-size: 11px;">
											<?php echo esc_html( $entry->status ?: 'NEW' ); ?>
										</span>
									</td>
									<td>
										<a class="button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=apexadfo_funnel_delete_lead&id=' . absint( $entry->id ) ), 'apexadfo_funnel_delete_lead_' . absint( $entry->id ) ) ); ?>" onclick="return confirm('<?php echo esc_js( esc_html__( 'Delete this lead permanently?', 'apex-addons-for-elementor' ) ); ?>')">
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

	public function bulk_delete_leads() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized request.', 'apex-addons-for-elementor' ) );
		}
		check_admin_referer( 'apexadfo_funnel_bulk_action' );

		$action = isset( $_POST['bulk_action'] ) ? sanitize_key( $_POST['bulk_action'] ) : ( isset( $_POST['bulk_action2'] ) ? sanitize_key( $_POST['bulk_action2'] ) : '' );
		$ids    = isset( $_POST['lead_ids'] ) ? array_map( 'absint', (array) $_POST['lead_ids'] ) : array();

		if ( 'delete' === $action && ! empty( $ids ) ) {
			global $wpdb;
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$table        = esc_sql( $this->table_name() );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		wp_safe_redirect( admin_url( 'admin.php?page=apexadfo-funnel-leads' ) );
		exit;
	}

	public function delete_lead() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! current_user_can( 'manage_options' ) || ! $id ) {
			wp_die( esc_html__( 'Unauthorized request.', 'apex-addons-for-elementor' ) );
		}
		check_admin_referer( 'apexadfo_funnel_delete_lead_' . $id );
		global $wpdb;
		// Delete directly from the plugin-owned lead table; the admin list is intentionally uncached.
		$wpdb->delete( $this->table_name(), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		wp_safe_redirect( admin_url( 'admin.php?page=apexadfo-funnel-leads' ) );
		exit;
	}

	public function export_leads() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized request.', 'apex-addons-for-elementor' ) );
		}
		check_admin_referer( 'apexadfo_funnel_export_leads' );
		global $wpdb;
		$table = esc_sql( $this->table_name() );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$entries = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC" );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=apex-funnel-leads-' . gmdate( 'Y-m-d' ) . '.csv' );
		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'ID', 'Date', 'Funnel', 'Page', 'Status', 'Lead data', 'Score', 'Source URL' ) );
		foreach ( $entries as $entry ) {
			$data  = json_decode( $entry->submission_data, true );
			$meta  = json_decode( $entry->meta_data, true );
			$parts = array();
			foreach ( (array) $data as $answer ) {
				$value   = is_array( $answer['value'] ?? '' ) ? $this->flatten_value( $answer['value'] ) : ( $answer['value'] ?? '' );
				$parts[] = ( $answer['label'] ?? '' ) . ': ' . $value;
			}
			$name = $meta['funnel_name'] ?? get_the_title( $entry->funnel_id ?: $entry->page_id );
			$row  = array( $entry->id, $entry->created_at, $name, get_the_title( $entry->page_id ), $entry->status, implode( ' | ', $parts ), (int) ( $meta['score'] ?? 0 ), $meta['source_url'] ?? '' );
			fputcsv( $output, array_map( array( $this, 'csv_safe' ), $row ) );
		}
		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closes the streamed php://output CSV response, not a filesystem file.
		exit;
	}

	public function csv_safe( $value ) {
		$value = (string) $value;
		return preg_match( '/^[=+\-@]/', $value ) ? "'" . $value : $value;
	}
}
