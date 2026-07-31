<?php
/**
 * Apex Style Presets / Design System Manager
 * Apex Addons for Elementor
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Style_Preset_Manager {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// AJAX endpoints to save & delete presets
		add_action( 'wp_ajax_apexadfo_save_style_preset', [ $this, 'ajax_save_style_preset' ] );
		add_action( 'wp_ajax_apexadfo_delete_style_preset', [ $this, 'ajax_delete_style_preset' ] );

		// Enqueue Editor scripts & styles
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Fetch all saved presets from options
	 */
	public static function get_presets() {
		$presets = get_option( 'apexadfo_style_presets', [] );
		return is_array( $presets ) ? $presets : [];
	}

	/**
	 * Enqueue Editor JS & CSS and localize presets data
	 */
	public function enqueue_editor_assets() {
		wp_enqueue_style(
			'apexadfo-style-presets-css',
			plugins_url( 'assets/css/editor-style-presets.css', dirname( __FILE__ ) ),
			[],
			APEXADFO_VERSION
		);

		wp_enqueue_script(
			'apexadfo-style-presets-js',
			plugins_url( 'assets/js/editor-style-presets.js', dirname( __FILE__ ) ),
			[ 'jquery', 'elementor-editor' ],
			APEXADFO_VERSION,
			true
		);

		wp_localize_script(
			'apexadfo-style-presets-js',
			'apexadfoStylePresetsData',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'apexadfo_preset_nonce' ),
				'presets'  => self::get_presets(),
			]
		);
	}

	/**
	 * Save or Update a Style Preset
	 */
	public function ajax_save_style_preset() {
		check_ajax_referer( 'apexadfo_preset_nonce', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'apex-addons-for-elementor' ) ] );
		}

		$preset_id   = isset( $_POST['preset_id'] ) ? sanitize_key( $_POST['preset_id'] ) : 'preset_' . time();
		$title       = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : __( 'Untitled Preset', 'apex-addons-for-elementor' );
		$target_type = isset( $_POST['target_type'] ) ? sanitize_text_field( $_POST['target_type'] ) : 'container'; // container, widget, etc.
		$element_name= isset( $_POST['element_name'] ) ? sanitize_text_field( $_POST['element_name'] ) : 'container';
		$settings    = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? array_map( 'sanitize_text_field', $_POST['settings'] ) : [];

		if ( empty( $settings ) && isset( $_POST['settings_json'] ) ) {
			$json_raw = wp_unslash( $_POST['settings_json'] );
			$decoded  = json_decode( $json_raw, true );
			if ( is_array( $decoded ) ) {
				$settings = $decoded;
			}
		}

		if ( empty( $settings ) ) {
			wp_send_json_error( [ 'message' => __( 'No settings payload provided.', 'apex-addons-for-elementor' ) ] );
		}

		$presets = self::get_presets();

		$presets[ $preset_id ] = [
			'id'           => $preset_id,
			'title'        => $title,
			'target_type'  => $target_type,
			'element_name' => $element_name,
			'created_at'   => current_time( 'mysql' ),
			'settings'     => $settings,
		];

		update_option( 'apexadfo_style_presets', $presets );

		wp_send_json_success( [
			'message' => __( 'Preset saved successfully!', 'apex-addons-for-elementor' ),
			'presets' => $presets,
			'preset'  => $presets[ $preset_id ],
		] );
	}

	/**
	 * Delete a Style Preset
	 */
	public function ajax_delete_style_preset() {
		check_ajax_referer( 'apexadfo_preset_nonce', 'security' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'apex-addons-for-elementor' ) ] );
		}

		$preset_id = isset( $_POST['preset_id'] ) ? sanitize_key( $_POST['preset_id'] ) : '';

		if ( empty( $preset_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid preset ID.', 'apex-addons-for-elementor' ) ] );
		}

		$presets = self::get_presets();

		if ( isset( $presets[ $preset_id ] ) ) {
			unset( $presets[ $preset_id ] );
			update_option( 'apexadfo_style_presets', $presets );
		}

		wp_send_json_success( [
			'message' => __( 'Preset deleted successfully.', 'apex-addons-for-elementor' ),
			'presets' => $presets,
		] );
	}
}

// Initialize Preset Manager
Style_Preset_Manager::get_instance();
