<?php
/**
 * Plugin Name: Apex Addons for Elementor
 * Description: Unique, high-quality interactive widgets and extensions for Elementor.
 * Version:     1.3.1
 * Author:      arhamashfaq
 * Author URI:  https://www.arhamashfaq.com/
 * Text Domain: apex-addons-for-elementor
 * Requires PHP: 7.4
 * Requires at least: 5.8
 * Requires Plugins: elementor
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Elementor tested up to: 4.1.4
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- The theme-canvas handoff uses a fully plugin-prefixed global shared across two isolated template scopes.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Submission administration uses a dedicated custom table; fresh reads are required after CRUD actions.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'APEXADFO_VERSION', '1.3.1' );

// Load conditions engine globally (before init) so AJAX handlers can always use it
require_once __DIR__ . '/class-apex-conditions-engine.php';

/**
 * Main Loader Class for the Free Version
 */
final class Loader {

	/**
	 * Instance
	 *
	 * @var Loader
	 */
	private static $_instance = null;

	/** @var array|null Sanitized preloader configuration for this request. */
	private $preloader_context = null;

	/** @var string WordPress hook suffix for the Theme Builder admin screen. */
	private $theme_builder_page_hook = '';

	/** @var string WordPress hook suffix for the Pro Showcase admin screen. */
	private $pro_showcase_page_hook = '';

	/** @var array|null Matching popup template IDs for the current request. */
	private $matching_popup_templates = null;

	/** Temporarily hide the unfinished popup builder from the Theme Builder UI. */
	private const THEME_BUILDER_POPUPS_VISIBLE = false;

	/**
	 * Get Instance
	 *
	 * @return Loader
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin
	 */
	public function init() {
		$this->migrate_legacy_identifiers();

		// Check if Elementor is active
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_elementor' ] );
			return;
		}

		// Funnel definitions live inside each Elementor widget. The manager owns
		// only authoritative validation, notifications, and the secure lead inbox.
		require_once __DIR__ . '/includes/class-funnel-manager.php';
		\ArhamAshfaq\ApexAddonsForElementor\Free\Funnel_Manager::get_instance();
		require_once __DIR__ . '/includes/class-quiz-manager.php';
		\ArhamAshfaq\ApexAddonsForElementor\Free\Quiz_Manager::get_instance();
		require_once __DIR__ . '/includes/class-setup-wizard.php';
		\ArhamAshfaq\ApexAddonsForElementor\Free\Setup_Wizard::get_instance();

		// Register categories
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );

		// Register editor branding styles
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_editor_styles' ] );

		// Register widgets (only active ones)
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

		// Load custom icon manager
		require_once __DIR__ . '/class-apex-icons-manager.php';

		// Add admin settings page hook
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_menu', [ $this, 'add_pro_showcase_submenu_page' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

		// AJAX callbacks for settings dashboard
		add_action( 'wp_ajax_apexadfo_toggle_addon', [ $this, 'ajax_toggle_addon' ] );
		add_action( 'wp_ajax_apexadfo_bulk_toggle_addons', [ $this, 'ajax_bulk_toggle_addons' ] );
		add_action( 'wp_ajax_apexadfo_delete_submission', [ $this, 'ajax_delete_submission' ] );
		add_action( 'wp_ajax_apexadfo_export_csv', [ $this, 'ajax_export_csv' ] );

		// AJAX callbacks for form submissions
		add_action( 'wp_ajax_nopriv_apexadfo_form_submit', [ $this, 'ajax_handle_form_submit' ] );
		add_action( 'wp_ajax_apexadfo_form_submit', [ $this, 'ajax_handle_form_submit' ] );

		// AJAX callbacks for Theme Builder conditions
		add_action( 'wp_ajax_apexadfo_save_conditions', [ $this, 'ajax_save_conditions' ] );
		add_action( 'wp_ajax_apexadfo_get_conditions', [ $this, 'ajax_get_conditions' ] );
		add_action( 'wp_ajax_apexadfo_get_sub_name_options', [ $this, 'ajax_get_sub_name_options' ] );
		add_action( 'wp_ajax_apexadfo_get_sub_id_options', [ $this, 'ajax_get_sub_id_options' ] );

		// Run the database check only when the schema version changes.
		if ( '1.0' !== get_option( 'apexadfo_db_version' ) ) {
			$this->maybe_create_table();
		}



		add_action( 'init', [ $this, 'register_assets' ] );
		add_action( 'elementor/init', [ $this, 'maybe_refresh_elementor_render_cache' ], 99 );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_nested_slider_editor_assets' ] );


		// Register Container Horizontal Scroll extension hooks (only if active)
		if ( self::is_addon_active( 'container_hscroll' ) ) {
			add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_container_hscroll_story_controls' ], 10, 2 );
			add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_container_hscroll_style_controls' ], 11, 2 );
			add_action( 'elementor/frontend/container/before_render', [ $this, 'before_element_hscroll_story_render' ] );
			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_hscroll_editor_assets' ] );
		}

		// Pinned Vertical Scroll is an independent container extension. It keeps
		// the selected container in the document flow while its direct children
		// travel vertically inside a native sticky stage.
		if ( self::is_addon_active( 'pinned_vertical_scroll' ) ) {
			add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_pinned_vertical_scroll_controls' ], 12, 2 );
			add_action( 'elementor/frontend/container/before_render', [ $this, 'before_pinned_vertical_scroll_render' ] );
			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_pinned_vertical_scroll_editor_assets' ] );
		}

		// Classic Scroll Stack is a complete Free extension. Pro enhances this
		// same section through hooks, without replacing or weakening Free.
		if ( self::is_addon_active( 'container_stack' ) ) {
			add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_container_stack_controls' ], 10, 2 );
			add_action( 'elementor/frontend/container/before_render', [ $this, 'before_container_stack_render' ] );
			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_stack_editor_assets' ] );
		}

		// Section Transitions turns the current container into a scroll-linked
		// reveal over the section immediately before it. Free includes the full
		// Curtain Up experience; companion plugins may add more modes via hooks.
		if ( self::is_addon_active( 'section_transitions' ) ) {
			add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_section_transition_controls' ], 12, 2 );
			add_action( 'elementor/frontend/container/before_render', [ $this, 'before_section_transition_render' ] );
			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_section_transition_editor_assets' ] );
		}

		// Register Magnetic Attraction extension hooks (only if active)
		if ( self::is_addon_active( 'magnetic_effect' ) ) {
			add_action( 'elementor/element/after_section_end', [ $this, 'register_magnetic_controls' ], 10, 2 );
			add_action( 'elementor/frontend/widget/before_render', [ $this, 'before_element_magnetic_render' ] );
			add_action( 'elementor/frontend/container/before_render', [ $this, 'before_element_magnetic_render' ] );
		}

		// Register Cinematic Background Slideshow hooks (only if active)
		if ( self::is_addon_active( 'cinematic_slideshow' ) ) {
			add_action( 'elementor/element/after_section_end', [ $this, 'register_slideshow_background_controls' ], 10, 2 );
			add_action( 'elementor/frontend/section/before_render', [ $this, 'before_element_slideshow_render' ] );
			add_action( 'elementor/frontend/column/before_render', [ $this, 'before_element_slideshow_render' ] );
			add_action( 'elementor/frontend/container/before_render', [ $this, 'before_element_slideshow_render' ] );
			add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_slideshow_editor_assets' ] );
		}

		// Load editor global settings UI script
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_global_settings_js' ] );

		// Enqueue frontend scripts (Smooth Scroll / Cursors) based on active Kit settings
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_global_settings' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_preloader_assets' ], 30 );

		// Output dynamic global scrollbar CSS
		add_action( 'wp_enqueue_scripts', [ $this, 'output_global_scrollbar_css' ], 20 );

		// AJAX callback to save global settings from the custom sidebar panel
		add_action( 'wp_ajax_apexadfo_save_global_settings', [ $this, 'ajax_save_global_settings' ] );
		add_action( 'wp_ajax_apexadfo_save_basic_preloader', [ $this, 'ajax_save_basic_preloader' ] );

		// Register Template CPT and AJAX creation triggers for Theme Builder
		add_action( 'init', [ $this, 'register_template_cpt' ] );
		add_action( 'wp_ajax_apexadfo_create_template', [ $this, 'ajax_create_template' ] );

		// Frontend Theme Builder Injection Hooks
		add_action( 'wp_body_open', [ $this, 'render_custom_preloader' ], 1 );
		add_action( 'wp_body_open', [ $this, 'render_custom_header' ] );
		add_action( 'wp_footer', [ $this, 'render_custom_footer' ], 5 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_theme_builder_template_styles' ], 50 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_theme_builder_popup_assets' ], 25 );
		add_action( 'wp_footer', [ $this, 'render_theme_builder_popups' ], 20 );
		add_filter( 'body_class', [ $this, 'add_theme_builder_body_classes' ] );
		add_filter( 'template_include', [ $this, 'override_theme_template' ], 99 );

		// WooCommerce Theme Builder Preview Context Setup (Restricted to Element Render Cycles)
		add_action( 'elementor/frontend/element/before_render', [ $this, 'setup_woocommerce_editor_preview_context' ] );
		add_action( 'elementor/widget/before_render_content', [ $this, 'setup_woocommerce_editor_preview_context' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'register_editor_frontend_style_placeholder' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'sync_current_editor_template_type' ] );
		add_action( 'admin_init', [ $this, 'sync_current_editor_template_type' ] );

		// Register Custom Dynamic Tags Hook
		add_action( 'elementor/dynamic_tags/register', [ $this, 'register_custom_dynamic_tags' ] );

		// Trigger action hook so Pro plugin can initialize or attach itself
		do_action( 'apexadfo/free_loaded', $this );
	}

	/**
	 * Migrate identifiers used by development builds before the WordPress.org
	 * review required a longer, collision-resistant prefix.
	 */
	private function migrate_legacy_identifiers() {
		if ( '1' === get_option( 'apexadfo_identifier_migration_130' ) ) {
			return;
		}

		$option_map = [
			'eas_db_version'          => 'apexadfo_db_version',
			'eas_free_render_schema'  => 'apexadfo_free_render_schema',
			'eas_active_addons'       => 'apexadfo_active_addons',
			'eas_global_settings'     => 'apexadfo_global_settings',
			'eas_basic_preloader'     => 'apexadfo_basic_preloader',
			'eas_funnel_db_version'   => 'apexadfo_funnel_db_version',
			'eas_quiz_db_version'     => 'apexadfo_quiz_db_version',
		];
		foreach ( $option_map as $legacy_key => $current_key ) {
			$legacy_value = get_option( $legacy_key, null );
			if ( null !== $legacy_value && false === get_option( $current_key, false ) ) {
				update_option( $current_key, $legacy_value, false );
			}
			delete_option( $legacy_key );
		}

		$legacy_templates = get_posts(
			[
				'post_type'      => 'eas_template',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		);
		foreach ( $legacy_templates as $template_id ) {
			wp_update_post( [ 'ID' => $template_id, 'post_type' => 'apexadfo_template' ] );
			foreach ( [ '_eas_template_type' => '_apexadfo_template_type', '_eas_template_conditions' => '_apexadfo_template_conditions' ] as $legacy_meta => $current_meta ) {
				$value = get_post_meta( $template_id, $legacy_meta, true );
				if ( '' !== $value ) {
					update_post_meta( $template_id, $current_meta, $value );
				}
				delete_post_meta( $template_id, $legacy_meta );
			}
		}

		global $wpdb;
		foreach ( [ 'eas_submissions' => 'apexadfo_submissions', 'eas_funnel_entries' => 'apexadfo_funnel_entries', 'eas_quiz_entries' => 'apexadfo_quiz_entries' ] as $legacy_suffix => $current_suffix ) {
			$legacy_table  = $wpdb->prefix . $legacy_suffix;
			$current_table = $wpdb->prefix . $current_suffix;
			$legacy_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $legacy_table ) ) );
			$current_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $current_table ) ) );
			if ( $legacy_table === $legacy_exists && $current_table !== $current_exists ) {
				// Both identifiers are composed exclusively from the trusted WordPress
				// table prefix and fixed plugin-owned suffixes above.
				$wpdb->query( "RENAME TABLE `{$legacy_table}` TO `{$current_table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}
		}

		update_option( 'apexadfo_identifier_migration_130', '1', false );
	}

	/**
	 * Describe companion features without registering disabled controls or code.
	 */
	private function add_companion_features_notice( $element, $control_id, $features, $condition = [] ) {
		if ( defined( 'APEXADFO_PRO_VERSION' ) ) {
			return;
		}

		$element->add_control(
			$control_id,
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<strong>' . esc_html__( 'Available in Apex Pro', 'apex-addons-for-elementor' ) . '</strong><br>' . esc_html( $features ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => $condition,
			]
		);
	}

	/**
	 * Clear cached element markup once when a render schema changes.
	 */
	public function maybe_refresh_elementor_render_cache() {
		$schema = '1.3.0-extension-split-2';
		if ( $schema === get_option( 'apexadfo_free_render_schema' ) ) {
			return;
		}

		// Never clear Elementor's global file cache from a frontend/plugin schema
		// migration. That cache contains the generated CSS for every Elementor
		// document on the site; deleting it here can leave existing pages unstyled
		// until each document is opened and saved again. Runtime render changes are
		// versioned through this option and the plugin's own asset versions instead.
		update_option( 'apexadfo_free_render_schema', $schema, false );
	}

	/**
	 * Check if a specific addon is active.
	 * Defaults to true if the option is not set.
	 */
	public static function is_addon_active( $addon_id ) {
		$active_addons = get_option( 'apexadfo_active_addons', null );

		// If no option has been saved yet, all addons are active by default
		if ( is_null( $active_addons ) ) {
			return true;
		}

		return isset( $active_addons[ $addon_id ] ) ? (bool) $active_addons[ $addon_id ] : true;
	}

	/**
	 * Missing Elementor notice
	 */
	public function admin_notice_missing_elementor() {
		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'apex-addons-for-elementor' ),
			'<strong>' . esc_html__( 'Apex Addons for Elementor', 'apex-addons-for-elementor' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'apex-addons-for-elementor' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses( $message, [ 'strong' => [] ] ) );
	}

	/**
	 * Register Custom Category
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'elementor-addon-suite-category',
			[
				'title' => esc_html__( 'Apex Addons', 'apex-addons-for-elementor' ),
				'icon'  => 'fa fa-plug',
			]
		);

		$elements_manager->add_category(
			'single',
			[
				'title' => esc_html__( 'Single', 'apex-addons-for-elementor' ),
				'icon'  => 'fa fa-newspaper-o',
			]
		);

		$elements_manager->add_category(
			'eas-preloader-category',
			[
				'title' => esc_html__( 'Apex Preloader', 'apex-addons-for-elementor' ),
				'icon'  => 'fa fa-clock-o',
			]
		);

		$elements_manager->add_category(
			'eas-typography-category',
			[
				'title' => esc_html__( 'Apex Typography', 'apex-addons-for-elementor' ),
				'icon'  => 'fa fa-font',
			]
		);
	}

	/**
	 * Register Widgets
	 */
	public function register_widgets( $widgets_manager ) {
		// Load active Free widgets (General Interactive Widgets First)
		if ( self::is_addon_active( 'form_widget' ) ) {
			require_once __DIR__ . '/widgets/form-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Form_Widget() );
		}

		if ( self::is_addon_active( 'comparison_table' ) ) {
			require_once __DIR__ . '/widgets/comparison-table-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Comparison_Table_Widget() );
		}

		if ( self::is_addon_active( 'nested_slider' ) ) {
			require_once __DIR__ . '/widgets/nested-slider-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Nested_Slider_Widget() );
		}

		if ( self::is_addon_active( 'nested_content_switcher' ) ) {
			require_once __DIR__ . '/widgets/nested-content-switcher-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Nested_Content_Switcher_Widget() );
		}

		if ( self::is_addon_active( 'glass_card' ) ) {
			require_once __DIR__ . '/widgets/glass-card-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Glass_Card_Widget() );
		}

		if ( self::is_addon_active( 'conversational_funnel' ) ) {
			require_once __DIR__ . '/widgets/conversational-funnel-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Conversational_Funnel_Widget() );
		}

		if ( self::is_addon_active( 'quiz_builder' ) ) {
			require_once __DIR__ . '/widgets/quiz-builder-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Quiz_Builder_Widget() );
		}

		if ( self::is_addon_active( 'team_member' ) ) {
			require_once __DIR__ . '/widgets/team-member-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Team_Member_Widget() );
		}

		if ( self::is_addon_active( 'portfolio_showcase' ) ) {
			require_once __DIR__ . '/widgets/portfolio-showcase-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Portfolio_Showcase_Widget() );
		}

		if ( self::is_addon_active( 'flex_accordion' ) ) {
			require_once __DIR__ . '/widgets/flex-accordion-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Flex_Accordion_Widget() );
		}

		if ( self::is_addon_active( 'dual_heading' ) ) {
			require_once __DIR__ . '/widgets/dual-heading-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Dual_Heading_Widget() );
		}

		if ( self::is_addon_active( 'svg_icon' ) ) {
			require_once __DIR__ . '/widgets/svg-icon-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\SVG_Icon_Widget() );
		}

		if ( self::is_addon_active( 'blob_background' ) ) {
			require_once __DIR__ . '/widgets/blob-background-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Blob_Background_Widget() );
		}

		if ( self::is_addon_active( 'scroll_parallax_text' ) ) {
			require_once __DIR__ . '/widgets/scroll-parallax-text-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Scroll_Parallax_Text_Widget() );
		}

		if ( self::is_addon_active( 'text_reveal' ) ) {
			require_once __DIR__ . '/widgets/text-reveal-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Text_Reveal_Widget() );
		}

		if ( self::is_addon_active( 'premium_typography' ) ) {
			require_once __DIR__ . '/widgets/premium-typography-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Premium_Typography_Widget() );
		}

		if ( self::is_addon_active( 'fan_carousel' ) ) {
			require_once __DIR__ . '/widgets/fan-carousel-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Fan_Carousel_Widget() );
		}

		if ( self::is_addon_active( 'nav_menu' ) ) {
			require_once __DIR__ . '/widgets/nav-menu-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Nav_Menu_Widget() );
		}

		if ( self::is_addon_active( 'header_search' ) ) {
			require_once __DIR__ . '/widgets/header-search-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Header_Search_Widget() );
		}

		if ( self::is_addon_active( 'before_after_image_comparison' ) ) {
			require_once __DIR__ . '/widgets/before-after-image-comparison-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Before_After_Image_Comparison_Widget() );
		}

		if ( self::is_addon_active( 'interactive_image_hotspots' ) ) {
			require_once __DIR__ . '/widgets/interactive-image-hotspots-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Interactive_Image_Hotspots_Widget() );
		}

		if ( self::is_addon_active( 'advanced_timeline' ) ) {
			require_once __DIR__ . '/widgets/advanced-timeline-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Advanced_Timeline_Widget() );
		}

		// Single Post & WooCommerce Theme Builder Widgets (Registered Last)
		if ( self::is_addon_active( 'singular_widgets' ) ) {
			require_once __DIR__ . '/widgets/post-title-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Post_Title_Widget() );

			require_once __DIR__ . '/widgets/post-excerpt-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Post_Excerpt_Widget() );

			require_once __DIR__ . '/widgets/post-content-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Post_Content_Widget() );

			require_once __DIR__ . '/widgets/featured-image-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Featured_Image_Widget() );

			require_once __DIR__ . '/widgets/table-of-contents-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Table_Of_Contents_Widget() );

			require_once __DIR__ . '/widgets/author-box-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Author_Box_Widget() );

			require_once __DIR__ . '/widgets/post-comments-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Post_Comments_Widget() );

			require_once __DIR__ . '/widgets/post-navigation-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Post_Navigation_Widget() );

			require_once __DIR__ . '/widgets/post-info-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Post_Info_Widget() );

			require_once __DIR__ . '/widgets/progress-tracker-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Progress_Tracker_Widget() );

			require_once __DIR__ . '/widgets/archive-loop-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Archive_Loop_Widget() );

			require_once __DIR__ . '/widgets/site-logo-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Site_Logo_Widget() );

			// Apex WooCommerce Single Product Widgets
			require_once __DIR__ . '/widgets/product-title-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Title_Widget() );

			require_once __DIR__ . '/widgets/product-images-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Images_Widget() );

			require_once __DIR__ . '/widgets/product-price-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Price_Widget() );

			require_once __DIR__ . '/widgets/product-add-to-cart-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Add_To_Cart_Widget() );

			require_once __DIR__ . '/widgets/product-rating-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Rating_Widget() );

			require_once __DIR__ . '/widgets/product-meta-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Meta_Widget() );

			require_once __DIR__ . '/widgets/product-short-description-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Short_Description_Widget() );

			require_once __DIR__ . '/widgets/product-data-tabs-widget.php';
			$widgets_manager->register( new \ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Product_Data_Tabs_Widget() );
		}

		// Let Pro plugin register its active widgets
		do_action( 'apexadfo/register_widgets', $widgets_manager );
	}

	/**
	 * Add Admin Menu Settings Page
	 */
	public function add_settings_page() {
		add_menu_page(
			esc_html__( 'Apex Addons', 'apex-addons-for-elementor' ),
			esc_html__( 'Apex Addons', 'apex-addons-for-elementor' ),
			'manage_options',
			'apexadfo-addons',
			[ $this, 'render_settings_page' ],
			plugin_dir_url( __FILE__ ) . 'assets/images/apex-menu-icon.png',
			90
		);

		add_submenu_page(
			'apexadfo-addons',
			esc_html__( 'Dashboard', 'apex-addons-for-elementor' ),
			esc_html__( 'Dashboard', 'apex-addons-for-elementor' ),
			'manage_options',
			'apexadfo-addons',
			[ $this, 'render_settings_page' ]
		);

		$this->theme_builder_page_hook = add_submenu_page(
			'apexadfo-addons',
			esc_html__( 'Theme Builder', 'apex-addons-for-elementor' ),
			esc_html__( 'Theme Builder', 'apex-addons-for-elementor' ),
			'manage_options',
			'apexadfo-theme-builder',
			[ $this, 'render_theme_builder_page' ]
		);
	}

	/**
	 * Add Get Apex Pro Submenu Page at priority 99 (renders at the bottom of the submenu list)
	 */
	public function add_pro_showcase_submenu_page() {
		$this->pro_showcase_page_hook = add_submenu_page(
			'apexadfo-addons',
			esc_html__( 'Get Apex Pro', 'apex-addons-for-elementor' ),
			'<span style="color: #a855f7; font-weight: 600;">' . esc_html__( 'Get Apex Pro ⚡', 'apex-addons-for-elementor' ) . '</span>',
			'manage_options',
			'apexadfo-get-pro',
			[ $this, 'render_get_pro_page' ]
		);
	}

	/**
	 * Enqueue Admin Assets (only on our dashboard page and Theme Builder page)
	 */
	public function enqueue_admin_assets( $hook ) {
		$is_theme_builder = '' !== $this->theme_builder_page_hook && $this->theme_builder_page_hook === $hook;
		$is_pro_showcase  = '' !== $this->pro_showcase_page_hook && $this->pro_showcase_page_hook === $hook;
		if ( 'toplevel_page_apexadfo-addons' !== $hook && ! $is_theme_builder && ! $is_pro_showcase ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'apexadfo-admin-dashboard-css',
			plugins_url( 'assets/css/admin-dashboard.css', __FILE__ ),
			[],
			'1.0.59'
		);

		wp_enqueue_script(
			'apexadfo-admin-dashboard-js',
			plugins_url( 'assets/js/admin-dashboard.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.58',
			true
		);

		wp_localize_script( 'apexadfo-admin-dashboard-js', 'apexadfoAdmin', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'apexadfo_admin_nonce' ),
		] );

		// Theme Builder interactions are rendered with the page below. Do not
		// enqueue a second, non-existent script here.
	}

	/**
	 * Get the Free addon list. Companion plugins may append their own entries.
	 */
	public function get_addons() {
		$addons = [
			// Free Widgets
			'singular_widgets' => [
				'title' => esc_html__( 'Single Templates Suite', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Full suite of 10 dynamic widgets for custom singular templates (Post Title, Excerpt, Content, Featured Image, Table of Contents, Author Box, Navigation, Comments, Info, and Scroll Progress).', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-welcome-widgets-menus',
				'pro' => false,
			],
			'dual_heading' => [
				'title' => esc_html__( 'Dual Heading', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'An advanced dual heading widget with multiple styling splits.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-editor-textcolor',
				'pro' => false,
			],
			'glass_card' => [
				'title' => esc_html__( 'Glass Card', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Stunning card with modern CSS glassmorphism overlay.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-welcome-widgets-menus',
				'pro' => false,
			],
			'blob_background' => [
				'title' => esc_html__( 'Blob Background', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Beautiful organic vector shapes that float and warp in the background.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-art',
				'pro' => false,
			],
			// Free Extensions
			'container_stack' => [
				'title' => esc_html__( 'Classic Scroll Stack', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc' => esc_html__( 'Build polished sticky card stacks with scroll-linked movement, scale, spacing, dimming, and responsive controls.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-table-row-after',
				'pro' => false,
			],
			'section_transitions' => [
				'title' => esc_html__( 'Section Transitions', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc' => esc_html__( 'Reveal one Elementor container over the previous section with a polished, scroll-linked curtain transition.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-arrow-up-alt2',
				'pro' => false,
			],
			'nested_slider' => [
				'title' => esc_html__( 'Apex Nested Motion Carousel', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Build responsive carousels with real nested Elementor content, smooth movement, accessible navigation, and complete visual styling.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-images-alt2',
				'pro' => false,
			],
			'nested_content_switcher' => [
				'title' => esc_html__( 'Nested Content Switcher', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Switch between fully designable nested Elementor panels with accessible navigation, deep links, responsive layouts, and polished transitions.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-screenoptions',
				'pro' => false,
			],
			'form_widget' => [
				'title' => esc_html__( 'Form Builder', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Build secure responsive forms with advanced fields, multi-step flows, conditional logic, file uploads, validation, email delivery, anti-spam protection, saved submissions, CSV export, and complete styling controls.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-feedback',
				'pro' => false,
			],
			'conversational_funnel' => [
				'title' => esc_html__( 'Conversational Funnel', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Build guided lead funnels directly in Elementor with a secure lead inbox, email notifications, responsive presentation modes, and accessible step-by-step interactions.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-filter',
				'pro' => false,
			],
			'quiz_builder' => [
				'title' => esc_html__( 'Quiz Builder', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Build scored quizzes with ordered steps, accessible questions, lead fields, secure server scoring, saved responses, and complete styling.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-welcome-learn-more',
				'pro' => false,
			],
			'svg_icon' => [
				'title' => esc_html__( 'Apex SVG & Icon', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Use locally bundled icon libraries or safely render custom SVG markup with no visitor-side API request.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-editor-code',
				'pro' => false,
			],
			'premium_typography' => [
				'title' => esc_html__( 'Motion Typography', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Liquid Morph and Matrix Scramble typography effects with responsive styling controls.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-editor-textcolor',
				'pro' => false,
			],
			'scroll_parallax_text' => [
				'title' => esc_html__( 'Scroll Marquee', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Smooth scroll-driven horizontal marquee with responsive speed and direction controls.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-leftright',
				'pro' => false,
			],
			'container_hscroll' => [
				'title' => esc_html__( 'Horizontal Scroll Section', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc' => esc_html__( 'Convert any Container and its direct child elements into full-screen horizontal scrolling panels.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-leftright',
				'pro' => false,
			],
			'pinned_vertical_scroll' => [
				'title' => esc_html__( 'Pinned Vertical Scroll', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc' => esc_html__( 'Pin any Elementor container while its naturally sized direct child containers travel vertically with the page scroll.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-arrow-up-alt2',
				'pro' => false,
			],
			'text_reveal' => [
				'title' => esc_html__( 'Text Highlight Reveal', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Animate text highlight, scaling, or transitions dynamically as words or characters enter the viewport.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-editor-textcolor',
				'pro' => false,
			],
			'portfolio_showcase' => [
				'title' => esc_html__( 'Portfolio Hover Showcase', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Interactive list layout with a dynamic floating media preview that follows the cursor with organic spring inertia and tilt.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-portfolio',
				'pro' => false,
			],
			'magnetic_effect' => [
				'title' => esc_html__( 'Magnetic Cursor Attraction', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc' => esc_html__( 'Add responsive magnetic spring attraction and multi-layered parallax pull to any Elementor widget, button, or container.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-pointer',
				'pro' => false,
			],
			'flex_accordion' => [
				'title' => esc_html__( 'Apex Flex Accordion', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Fluid expandable card grid that transitions columns on hover or click with hardware-accelerated animations.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-columns',
				'pro' => false,
			],
			'team_member' => [
				'title' => esc_html__( 'Apex Team Member Showcase', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Showcase your team inside an interactive responsive grid or swiper carousel featuring premium animations and overlay controls.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-groups',
				'pro' => false,
			],
			'cinematic_slideshow' => [
				'title' => esc_html__( 'Cinematic Background Slideshow', 'apex-addons-for-elementor' ),
				'category' => 'backgrounds',
				'desc' => esc_html__( 'Add a slideshow background with Ken Burns panning and interactive side pagination dots to any section, column, or container.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-images-alt',
				'pro' => false,
			],
			'fan_carousel' => [
				'title' => esc_html__( 'Poker Fan Carousel', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Display list items in an interactive, curved hand-fan layout that loops dynamically on scroll or clicks.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-images-alt2',
				'pro' => false,
			],
			'site_logo' => [
				'title' => esc_html__( 'Site Logo', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Add your site logo or a custom image with advanced controls and responsive styling.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
			],
			'nav_menu' => [
				'title' => esc_html__( 'Navigation Menu', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Fully customizable navigation menu with advanced hover effects and mobile drawer.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-menu-alt',
				'pro' => false,
			],
			'header_search' => [
				'title' => esc_html__( 'Header Search', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Add a search button that opens in an expandable bar or a full-screen overlay.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-search',
				'pro' => false,
			],
			'before_after_image_comparison' => [
				'title' => esc_html__( 'Before/After Image Comparison', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Compare two images with interactive drag, click, hover, and keyboard controls in horizontal or vertical modes.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-images-alt',
				'pro' => false,
			],
			'interactive_image_hotspots' => [
				'title' => esc_html__( 'Interactive Image Hotspots', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Add interactive pins and rich tooltip popups with icons, text badges, images, and links to any image layout.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-location',
				'pro' => false,
			],
			'advanced_timeline' => [
				'title' => esc_html__( 'Advanced Timeline', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Display vertical or horizontal milestone timelines with alternating card layouts, custom node icons, date badges, and progress lines.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-backup',
				'pro' => false,
			],
			'comparison_table' => [
				'title' => esc_html__( 'Comparison Table', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Compare products, pricing plans, and service features with highlighted columns, tooltips, custom cell values, and responsive card stacking.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-table-col-delete',
				'pro' => false,
			],
			'advanced_nested_switcher' => [
				'title' => esc_html__( 'Advanced Nested Switcher', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc' => esc_html__( 'Switch dynamically between nested Elementor container drop zones with animated sliding indicators, iOS physical switches, or segmented controls.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-forms',
				'pro' => false,
			],
			'icon_themify' => [
				'title' => esc_html__( 'Themify Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'A set of clean, elegant line-drawn icons for modern web interfaces.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '351 Icons',
			],
			'icon_linearicons' => [
				'title' => esc_html__( 'Linearicons (Free)', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Ultra-clean vector line icon set designed for creative UI layouts.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '170 Icons',
			],
			'icon_simpleline' => [
				'title' => esc_html__( 'Simple Line Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Simple and minimal line icons, extremely popular in dashboard interfaces.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '189 Icons',
			],
			'icon_lineawesome' => [
				'title' => esc_html__( 'Line Awesome Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'A beautiful line-art icon set designed as a direct replacement for Font Awesome.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '2,004 Icons',
			],
			'icon_ion' => [
				'title' => esc_html__( 'Ionicons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Premium open-source icons for designers and developer web applications.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '696 Icons',
			],
			'icon_materialdesign' => [
				'title' => esc_html__( 'Material Design Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Google\'s official material design icon pack with thousands of modern icons.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '5,346 Icons',
			],
			'icon_elegant' => [
				'title' => esc_html__( 'Elegant Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Classic, elegant icon library for high-end portfolio and editorial designs.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '360 Icons',
			],
			'icon_elusive' => [
				'title' => esc_html__( 'Elusive Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Sleek and robust open-source vector icon set for web navigation menus.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '303 Icons',
			],
			'icon_icofont' => [
				'title' => esc_html__( 'Icofont Regular', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Massive icon font pack with categories for mobile, medical, food, and e-commerce.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '2,095 Icons',
			],
			'icon_icofont-duotone' => [
				'title' => esc_html__( 'Icofont Duotone', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Beautiful duotone/two-tone versions of popular Icofont icons.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '318 Icons',
			],
			'icon_icomoon' => [
				'title' => esc_html__( 'IcoMoon Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Free open-source vector icons loaded through the classic IcoMoon wrapper.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '491 Icons',
			],
			'icon_iconic' => [
				'title' => esc_html__( 'Iconic Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Ultra-sharp, micro-optimized vector line icons for clean grid structures.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '172 Icons',
			],
			'icon_devicons' => [
				'title' => esc_html__( 'Devicons Developer Icons', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Dedicated developer, compiler, language, and technology icon sets.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '191 Icons',
			],
			'icon_openiconic' => [
				'title' => esc_html__( 'Open Iconic Pack', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Super-lightweight open-source icon set designed for speed and clarity.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '223 Icons',
			],
			'icon_line' => [
				'title' => esc_html__( 'Line Icons Set', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Beautiful modern line icons with perfect pixel-snapping and consistent weight.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '511 Icons',
			],
			'icon_phosphor_regular' => [
				'title' => esc_html__( 'Phosphor Regular', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Modern, flexible regular weight outline icons for modern UI designs.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '1,512 Icons',
			],
			'icon_phosphor_bold' => [
				'title' => esc_html__( 'Phosphor Bold', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Bold weight outline icons for high-contrast visibility.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '1,512 Icons',
			],
			'icon_phosphor_fill' => [
				'title' => esc_html__( 'Phosphor Fill', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Solid, filled versions of Phosphor icons for selected or highlighted states.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '1,512 Icons',
			],
			'icon_phosphor_duotone' => [
				'title' => esc_html__( 'Phosphor Duotone', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Two-tone/duotone styled Phosphor icons for a uniquely stylized visual appearance.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '1,512 Icons',
			],
			'icon_phosphor_light' => [
				'title' => esc_html__( 'Phosphor Light', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Elegant, thin-line light weight icons for clean minimalist designs.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '1,512 Icons',
			],
			'icon_phosphor_thin' => [
				'title' => esc_html__( 'Phosphor Thin', 'apex-addons-for-elementor' ),
				'category' => 'icons',
				'desc' => esc_html__( 'Ultra-thin vector line icons for high-end editorial and typographic interfaces.', 'apex-addons-for-elementor' ),
				'icon' => 'dashicons-format-image',
				'pro' => false,
				'icon_count' => '1,512 Icons',
			],

			// Pro Widgets
			'spatial_carousel' => [
				'title'    => esc_html__( 'Spatial 3D Carousel', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Full 3D spatial cylinder carousel with interactive depth perspective, auto-orbit, and drag rotation.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-gallery',
				'pro'      => true,
			],
			'coverflow_carousel' => [
				'title'    => esc_html__( 'Coverflow 3D Carousel', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Apple-style Coverflow slider with 3D depth rotation, reflection, shadow depth, and touch swipe.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-images-alt',
				'pro'      => true,
			],
			'liquid_glass' => [
				'title'    => esc_html__( 'Liquid Glass Morphism', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Apple iOS glassmorphic blur with dynamic refraction, metallic border sheen, and mouse shine.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-admin-appearance',
				'pro'      => true,
			],
			'physics_sandbox' => [
				'title'    => esc_html__( 'Physics Gravity Sandbox', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Real-time Matter.js physical gravity canvas. Drag, toss, bounce, and collide element badges.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-move',
				'pro'      => true,
			],
			'morphing_gallery' => [
				'title'    => esc_html__( 'Morphing Gallery', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Scroll-linked organic SVG metaball morphing transitions with smooth liquid card morphs.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-shapes',
				'pro'      => true,
			],
			'motion_typography_pro' => [
				'title'    => esc_html__( 'Motion Typography Pro', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Scroll-driven kinetic text, split-line reveal, variable font weights, and wave typography.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-editor-textcolor',
				'pro'      => true,
			],
			'generative_art_loader' => [
				'title'    => esc_html__( 'Generative Art Loader', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Procedural HTML5 canvas math loader with smooth fluid particle loops and color gradients.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-art',
				'pro'      => true,
			],
			'hypnotic_track_loader' => [
				'title'    => esc_html__( 'Hypnotic Track Loader', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Concentric spinning ring preloader with glowing neon tracks and customizable spin speeds.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-update-alt',
				'pro'      => true,
			],
			'shatter_particle_loader' => [
				'title'    => esc_html__( 'Shatter Particle Loader', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Interactive canvas particle explosion effect that shatters into physics particles on complete.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-forms',
				'pro'      => true,
			],
			'floating_dock' => [
				'title'    => esc_html__( 'macOS Floating Dock', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'macOS-style interactive magnetic bottom dock with smooth icon magnification and tooltip badges.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-menu-alt3',
				'pro'      => true,
			],
			'audio_visualizer' => [
				'title'    => esc_html__( 'Web Audio API Visualizer', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Real-time HTML5 Web Audio API frequency bar visualizer for audio tracks, podcasts, and music.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-audio',
				'pro'      => true,
			],
			'bento_grid' => [
				'title'    => esc_html__( 'Bento Grid Layout', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Modern Apple-style bento grid card layout with hover spotlights and variable span controls.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-grid-view',
				'pro'      => true,
			],
			'terminal_console' => [
				'title'    => esc_html__( 'Retro Terminal Console', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Interactive retro command-line terminal console with typing effect, custom commands, and output logs.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-editor-code',
				'pro'      => true,
			],
			'card_deck_pro' => [
				'title'    => esc_html__( 'Interactive Card Deck', 'apex-addons-for-elementor' ),
				'category' => 'widgets',
				'desc'     => esc_html__( 'Interactive stack card swiper. Drag, swipe, and rotate cards with smooth gesture controls.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-images-alt2',
				'pro'      => true,
			],

			// Pro Extensions
			'pinned_vertical_scroll_pro' => [
				'title'    => esc_html__( 'Pinned Vertical Scroll Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Pin containers in sticky viewport stages while inner elements travel vertically on scroll.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-sticky',
				'pro'      => true,
			],
			'container_stack_pro' => [
				'title'    => esc_html__( '3D Card Scroll Stack Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( '3D card stack container effect. Inner containers stack over each other with 3D depth and tilt.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-layer-group',
				'pro'      => true,
			],
			'section_transitions_pro' => [
				'title'    => esc_html__( '3D Section Transitions Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Scroll-linked page section reveals with Curtain Up, Zoom, and 3D perspective flip transitions.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-slides',
				'pro'      => true,
			],
			'magnetic_effect_pro' => [
				'title'    => esc_html__( 'Magnetic Attraction Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Magnetic cursor attraction effect for buttons, cards, and icons with custom pull radius.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-update',
				'pro'      => true,
			],
			'custom_cursor_pro' => [
				'title'    => esc_html__( 'Custom Cursor Suite Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Spring follower, inverse color circle, ring & dot follower, glow blob, and custom SVG cursors.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-admin-cursor',
				'pro'      => true,
			],
			'kenburns_slideshow_pro' => [
				'title'    => esc_html__( 'Ken Burns Slideshow Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Background pan & zoom Ken Burns slideshow for containers with sleek dot & line navigation.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-image',
				'pro'      => true,
			],
		];

		return apply_filters( 'apexadfo_register_addons', $addons );
	}

	/**
	 * Render Settings Page
	 */
	public function render_settings_page() {
		$addons = $this->get_addons();
		
		// Group addons by category
		$categories = [
			'widgets'    => [ 'title' => esc_html__( 'Widgets', 'apex-addons-for-elementor' ), 'items' => [] ],
			'extensions' => [ 'title' => esc_html__( 'Extensions', 'apex-addons-for-elementor' ), 'items' => [] ],
			'backgrounds'=> [ 'title' => esc_html__( 'Background Effects', 'apex-addons-for-elementor' ), 'items' => [] ],
			'icons'      => [ 'title' => esc_html__( 'Icons', 'apex-addons-for-elementor' ), 'items' => [] ],
		];

		foreach ( $addons as $id => $data ) {
			$cat = $data['category'] ?? 'widgets';
			if ( isset( $categories[ $cat ] ) ) {
				$categories[ $cat ]['items'][ $id ] = $data;
			}
		}

		$logo_url = plugins_url( 'assets/images/apex-addons-logo.png', __FILE__ );
		?>
		<div class="eas-admin-wrap">
			<!-- Header -->
			<header class="eas-admin-header">
				<div class="eas-admin-logo-title">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="Apex Logo" class="eas-admin-logo-img" />
					<div>
						<h1 class="eas-admin-title-h1"><?php esc_html_e( 'Apex Addons Dashboard', 'apex-addons-for-elementor' ); ?></h1>
						<p class="eas-admin-title-desc"><?php esc_html_e( 'Configure, toggle, and search interactive Elementor widgets and extensions.', 'apex-addons-for-elementor' ); ?></p>
					</div>
				</div>
			</header>

			<!-- Toolbar (Search & Bulk actions) -->
			<div class="eas-admin-toolbar">
				<div class="eas-admin-search-wrapper">
					<span class="dashicons dashicons-search eas-admin-search-icon"></span>
					<input type="text" id="eas-admin-search" class="eas-admin-search-input" placeholder="<?php esc_attr_e( 'Search addons...', 'apex-addons-for-elementor' ); ?>" />
				</div>
				<div class="eas-admin-bulk-actions">
					<button class="eas-admin-btn eas-admin-bulk-trigger" data-action="enable"><?php esc_html_e( 'Enable All', 'apex-addons-for-elementor' ); ?></button>
					<button class="eas-admin-btn eas-admin-bulk-trigger" data-action="disable"><?php esc_html_e( 'Disable All', 'apex-addons-for-elementor' ); ?></button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=apexadfo-setup-wizard' ) ); ?>" class="eas-admin-btn" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
						<span class="dashicons dashicons-controls-repeat" style="font-size: 14px; width: 14px; height: 14px; line-height: 1;"></span>
						<?php esc_html_e( 'Setup Wizard', 'apex-addons-for-elementor' ); ?>
					</a>
				</div>
			</div>

			<!-- Tabs Navigation -->
			<nav class="eas-admin-tabs-nav">
				<?php 
				$first_tab = true;
				foreach ( $categories as $key => $tab ) : 
				?>
					<button class="eas-admin-tab-trigger<?php echo $first_tab ? ' active' : ''; ?>" data-tab="<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $tab['title'] ); ?>
					</button>
				<?php 
					$first_tab = false;
				endforeach; 
				?>
				<button class="eas-admin-tab-trigger" data-tab="submissions">
					<?php esc_html_e( 'Form Submissions', 'apex-addons-for-elementor' ); ?>
				</button>
			</nav>

			<!-- Tabs Content -->
			<?php 
			$first_panel = true;
			foreach ( $categories as $key => $tab ) : 
			?>
				<div id="eas-tab-<?php echo esc_attr( $key ); ?>" class="eas-admin-tab-content<?php echo $first_panel ? ' active' : ''; ?>">
					<div class="eas-admin-grid">
						<?php if ( empty( $tab['items'] ) ) : ?>
							<p><?php esc_html_e( 'No addons registered in this category.', 'apex-addons-for-elementor' ); ?></p>
						<?php else : ?>
							<?php foreach ( $tab['items'] as $id => $data ) : 
								$is_pro_item   = ! empty( $data['pro'] );
								$is_pro_locked = $is_pro_item && ( ! defined( 'APEXADFO_PRO_VERSION' ) || ! class_exists( '\ArhamAshfaq\ApexAddonsForElementor\Pro\Loader' ) );
								$is_active     = self::is_addon_active( $id );
								$card_classes  = 'eas-admin-card' . ( $is_pro_locked ? ' eas-card-pro-locked' : '' );
							?>
								<div class="<?php echo esc_attr( $card_classes ); ?>"<?php echo $is_pro_locked ? ' data-pro-locked="1"' : ''; ?>>
									<div class="eas-admin-lock-container">
										<div class="eas-admin-card-header">
											<div class="eas-admin-card-title-group">
												<?php if ( ! empty( $data['icon'] ) ) : ?>
													<span class="dashicons <?php echo esc_attr( $data['icon'] ); ?> eas-admin-card-icon"></span>
												<?php endif; ?>
												<div>
													<h3 class="eas-admin-card-title"><?php echo esc_html( $data['title'] ); ?></h3>
													<?php if ( ! empty( $data['icon_count'] ) ) : ?>
														<span class="eas-admin-badge eas-admin-badge-count"><?php echo esc_html( $data['icon_count'] ); ?></span>
													<?php endif; ?>
												</div>
											</div>
											<!-- Toggle Switch -->
											<label class="eas-admin-switch<?php echo $is_pro_locked ? ' eas-pro-trigger' : ''; ?>">
												<input type="checkbox" 
													class="<?php echo $is_pro_locked ? 'eas-pro-trigger-checkbox' : 'eas-addon-toggle'; ?>" 
													data-addon-id="<?php echo esc_attr( $id ); ?>"
												<?php echo $is_pro_locked ? 'disabled' : ''; ?>
												<?php checked( $is_active && ! $is_pro_locked ); ?> />
												<span class="eas-admin-slider<?php echo $is_pro_locked ? ' eas-slider-pro-locked' : ''; ?>">
													<?php if ( $is_pro_locked ) : ?>
														<span class="eas-pro-knob-crown">
															<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="#334155" viewBox="0 0 256 256"><path d="M248,80a28,28,0,1,0-51.12,15.77l-26.79,33L146,73.4a28,28,0,1,0-36.06,0L85.91,128.74l-26.79-33a28,28,0,1,0-26.6,12L47,194.63A16,16,0,0,0,62.78,208H193.22A16,16,0,0,0,209,194.63l14.47-86.85A28,28,0,0,0,248,80ZM128,40a12,12,0,1,1-12,12A12,12,0,0,1,128,40ZM24,80A12,12,0,1,1,36,92,12,12,0,0,1,24,80ZM193.22,192H62.78L48.86,108.52,81.79,149A8,8,0,0,0,88,152a7.83,7.83,0,0,0,1.08-.07,8,8,0,0,0,6.26-4.74l29.3-67.4a27,27,0,0,0,6.72,0l29.3,67.4a8,8,0,0,0,6.26,4.74A7.83,7.83,0,0,0,168,152a8,8,0,0,0,6.21-3l32.93-40.52ZM220,92a12,12,0,1,1,12-12A12,12,0,0,1,220,92Z"></path></svg>
														</span>
													<?php endif; ?>
												</span>
											</label>
										</div>
										<p class="eas-admin-card-desc"><?php echo esc_html( $data['desc'] ); ?></p>
								</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<!-- No Results Placeholder -->
					<div class="eas-admin-no-results">
						<span class="dashicons dashicons-search" style="font-size: 24px; width: 24px; height: 24px; margin-bottom: 8px;"></span>
						<p><?php esc_html_e( 'No addons match your search query in this category.', 'apex-addons-for-elementor' ); ?></p>
					</div>
				</div>
			<?php 
				$first_panel = false;
			endforeach; 
			?>

			<!-- Submissions Tab Content -->
			<div id="eas-tab-submissions" class="eas-admin-tab-content">
				<div class="eas-submissions-viewer">
					<div class="eas-submissions-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
						<div>
							<h2 class="eas-submissions-title"><?php esc_html_e( 'Form Submissions Log', 'apex-addons-for-elementor' ); ?></h2>
							<p class="eas-submissions-subtitle"><?php esc_html_e( 'Securely view, manage, and delete form entry submissions collected by the Form Widget.', 'apex-addons-for-elementor' ); ?></p>
						</div>
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=apexadfo_export_csv' ), 'apexadfo_export_csv_nonce', 'nonce' ) ); ?>" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500;">
							<span class="dashicons dashicons-download" style="font-size: 16px; width: 16px; height: 16px; line-height: 1;"></span>
							<?php esc_html_e( 'Export CSV', 'apex-addons-for-elementor' ); ?>
						</a>
					</div>

					<div class="eas-submissions-table-wrap">
						<?php 
						global $wpdb;
						$table_name = esc_sql( $wpdb->prefix . 'apexadfo_submissions' );
						$entries = [];
						$table_exists = $wpdb->get_var(
							$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )
						);
						if ( $table_exists === $table_name ) {
							// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is derived only from the trusted WordPress database prefix.
							$entries = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 100" );
						}
						?>
						<?php if ( empty( $entries ) ) : ?>
							<div class="eas-submissions-empty-state">
								<span class="dashicons dashicons-database" style="font-size: 32px; width: 32px; height: 32px; color: #94a3b8; margin-bottom: 12px; display: inline-block;"></span>
								<p><?php esc_html_e( 'No form submissions logged yet.', 'apex-addons-for-elementor' ); ?></p>
							</div>
						<?php else : ?>
							<table class="eas-submissions-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Date', 'apex-addons-for-elementor' ); ?></th>
										<th><?php esc_html_e( 'Form Name', 'apex-addons-for-elementor' ); ?></th>
										<th><?php esc_html_e( 'Submission Data', 'apex-addons-for-elementor' ); ?></th>
										<th><?php esc_html_e( 'IP / Details', 'apex-addons-for-elementor' ); ?></th>
										<th><?php esc_html_e( 'Actions', 'apex-addons-for-elementor' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $entries as $entry ) : 
										$data = json_decode( $entry->submission_data, true );
										$meta = json_decode( $entry->meta_data, true );
										$formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->created_at ) );
									?>
										<tr id="eas-submission-row-<?php echo intval( $entry->id ); ?>">
											<td class="eas-sub-date"><?php echo esc_html( $formatted_date ); ?></td>
											<td class="eas-sub-name"><strong><?php echo esc_html( $entry->form_name ); ?></strong></td>
											<td class="eas-sub-data">
												<div class="eas-sub-fields-summary">
													<?php 
													if ( is_array( $data ) ) {
														foreach ( $data as $field ) {
															$label = $field['label'] ?? '';
															$value = $field['value'] ?? '';

															if ( strpos( $value, 'data:image/png;base64' ) === 0 ) {
																echo '<div class="eas-sub-field-item"><strong>' . esc_html( $label ) . ':</strong> <a href="#" class="eas-view-sig-trigger" data-sig="' . esc_attr( $value ) . '">' . esc_html__( 'View Signature', 'apex-addons-for-elementor' ) . '</a></div>';
															} else {
																echo '<div class="eas-sub-field-item"><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value ) . '</div>';
															}
														}
													} else {
														echo esc_html( $entry->submission_data );
													}
													?>
												</div>
											</td>
											<td class="eas-sub-meta">
												<div class="eas-sub-meta-item">IP: <?php echo esc_html( $meta['ip'] ?? 'N/A' ); ?></div>
												<div class="eas-sub-meta-item" title="<?php echo esc_attr( $meta['user_agent'] ?? '' ); ?>">Browser: <?php echo esc_html( substr( $meta['user_agent'] ?? 'N/A', 0, 30 ) ) . '...'; ?></div>
											</td>
											<td>
												<button class="eas-delete-submission-btn" data-id="<?php echo intval( $entry->id ); ?>">
													<span class="dashicons dashicons-trash"></span>
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Modal for Viewing Signature -->
			<div class="eas-signature-modal" id="eas-sig-modal" style="display: none;">
				<div class="eas-signature-modal-content">
					<span class="eas-signature-modal-close" id="eas-sig-modal-close">&times;</span>
					<h3><?php esc_html_e( 'Recorded Signature', 'apex-addons-for-elementor' ); ?></h3>
					<div class="eas-signature-modal-body" style="text-align: center;">
						<img id="eas-sig-modal-img" src="" alt="Signature" style="max-width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff;" />
					</div>
				</div>
			</div>

			<!-- Pro Upsell Modal -->
			<div id="eas-pro-upsell-modal" class="eas-modal-overlay" style="display: none;">
				<div class="eas-modal-container">
					<button type="button" class="eas-modal-close" id="eas-pro-modal-close" aria-label="<?php esc_attr_e( 'Close', 'apex-addons-for-elementor' ); ?>">&times;</button>
					<div class="eas-modal-header">
						<div class="eas-modal-crown-badge">
							<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#7c3aed" viewBox="0 0 256 256"><path d="M248,80a28,28,0,1,0-51.12,15.77l-26.79,33L146,73.4a28,28,0,1,0-36.06,0L85.91,128.74l-26.79-33a28,28,0,1,0-26.6,12L47,194.63A16,16,0,0,0,62.78,208H193.22A16,16,0,0,0,209,194.63l14.47-86.85A28,28,0,0,0,248,80ZM128,40a12,12,0,1,1-12,12A12,12,0,0,1,128,40ZM24,80A12,12,0,1,1,36,92,12,12,0,0,1,24,80ZM193.22,192H62.78L48.86,108.52,81.79,149A8,8,0,0,0,88,152a7.83,7.83,0,0,0,1.08-.07,8,8,0,0,0,6.26-4.74l29.3-67.4a27,27,0,0,0,6.72,0l29.3,67.4a8,8,0,0,0,6.26,4.74A7.83,7.83,0,0,0,168,152a8,8,0,0,0,6.21-3l32.93-40.52ZM220,92a12,12,0,1,1,12-12A12,12,0,0,1,220,92Z"></path></svg>
						</div>
						<h2><?php esc_html_e( 'Unlock the PRO Features', 'apex-addons-for-elementor' ); ?></h2>
						<p><?php esc_html_e( 'Upgrade to Apex Addons PRO and gain access to advanced 3D elements, physics sandboxes, and motion extensions to build websites more efficiently.', 'apex-addons-for-elementor' ); ?></p>
					</div>
					<div class="eas-modal-features-list">
						<div class="eas-modal-feat-item">
							<span class="eas-modal-check-badge">✓</span>
							<span><?php esc_html_e( 'Customization Flexibility in Design with Premium Creative Elements.', 'apex-addons-for-elementor' ); ?></span>
						</div>
						<div class="eas-modal-feat-item">
							<span class="eas-modal-check-badge">✓</span>
							<span><?php esc_html_e( 'Advanced 3D & Physics Gravity Canvas Widgets.', 'apex-addons-for-elementor' ); ?></span>
						</div>
						<div class="eas-modal-feat-item">
							<span class="eas-modal-check-badge">✓</span>
							<span><?php esc_html_e( 'Cutting-edge Extensions Like Pinned Scroll, 3D Stack & Custom Cursors.', 'apex-addons-for-elementor' ); ?></span>
						</div>
					</div>
					<div class="eas-modal-footer">
						<a href="<?php echo esc_url( apply_filters( 'apexadfo_pro_checkout_url', 'https://checkout.freemius.com/mode/dialog/plugin/36225/' ) ); ?>" target="_blank" rel="noopener noreferrer" class="eas-pro-btn-primary eas-modal-upgrade-btn">
							<?php esc_html_e( 'Upgrade to PRO', 'apex-addons-for-elementor' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX Callback: Save single addon toggle status
	 */
	public function ajax_toggle_addon() {
		check_ajax_referer( 'apexadfo_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Forbidden', 403 );
		}

		$addon_id = isset( $_POST['addon_id'] ) ? sanitize_key( wp_unslash( $_POST['addon_id'] ) ) : '';
		$status = isset( $_POST['status'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['status'] ) );

		if ( empty( $addon_id ) ) {
			wp_send_json_error( 'Invalid Addon ID', 400 );
		}

		$active_addons = get_option( 'apexadfo_active_addons', [] );
		if ( ! is_array( $active_addons ) ) {
			$active_addons = [];
		}

		$active_addons[ $addon_id ] = $status;
		update_option( 'apexadfo_active_addons', $active_addons );

		wp_send_json_success();
	}

	/**
	 * AJAX Callback: Save bulk addons toggle status
	 */
	public function ajax_bulk_toggle_addons() {
		check_ajax_referer( 'apexadfo_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Forbidden', 403 );
		}

		$list_raw = isset( $_POST['addons_list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['addons_list'] ) ) : '';
		$list = json_decode( $list_raw, true );

		if ( ! is_array( $list ) ) {
			wp_send_json_error( 'Invalid Data List', 400 );
		}

		$active_addons = get_option( 'apexadfo_active_addons', [] );
		if ( ! is_array( $active_addons ) ) {
			$active_addons = [];
		}

		foreach ( $list as $item ) {
			$id = sanitize_text_field( $item['id'] ?? '' );
			if ( ! empty( $id ) ) {
				$active_addons[ $id ] = (bool) $item['status'];
			}
		}

		update_option( 'apexadfo_active_addons', $active_addons );

		wp_send_json_success();
	}

	/**
	 * Create submissions database table if it doesn't exist
	 */
	public function maybe_create_table() {
		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'apexadfo_submissions' );

		$table_exists = $wpdb->get_var(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) )
		);

		if ( $table_exists !== $table_name ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id bigint(20) NOT NULL AUTO_INCREMENT,
				form_id varchar(100) NOT NULL,
				form_name varchar(255) NOT NULL,
				submission_data longtext NOT NULL,
				meta_data longtext NOT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		update_option( 'apexadfo_db_version', '1.0', false );
	}

	/**
	 * AJAX Callback: Delete submission
	 */
	public function ajax_delete_submission() {
		check_ajax_referer( 'apexadfo_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Forbidden', 403 );
		}

		$submission_id = isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0;

		if ( $submission_id <= 0 ) {
			wp_send_json_error( 'Invalid Submission ID', 400 );
		}

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'apexadfo_submissions' );
		$deleted = $wpdb->delete( $table_name, [ 'id' => $submission_id ], [ '%d' ] );

		if ( $deleted === false ) {
			wp_send_json_error( 'Database Error', 500 );
		}

		wp_send_json_success();
	}

	/**
	 * AJAX Callback: Handle Form Widget submission
	 */
	public function ajax_handle_form_submit() {
		require_once __DIR__ . '/widgets/form-widget.php';
		\ArhamAshfaq\ApexAddonsForElementor\Free\Widgets\Form_Widget::handle_form_submit();
	}

	/**
	 * AJAX Callback: Export submissions as CSV download
	 */
	public function ajax_export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'apexadfo_export_csv_nonce', 'nonce' );

		global $wpdb;
		$table_name = esc_sql( $wpdb->prefix . 'apexadfo_submissions' );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is derived only from the trusted WordPress database prefix.
		$rows = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY created_at DESC", ARRAY_A );

		// Collect all unique field keys across submissions
		$all_keys = [];
		$parsed_rows = [];
		foreach ( $rows as $row ) {
			$data = json_decode( $row['submission_data'], true );
			if ( ! is_array( $data ) ) {
				$data = [];
			}
			foreach ( array_keys( $data ) as $k ) {
				if ( ! in_array( $k, $all_keys, true ) ) {
					$all_keys[] = $k;
				}
			}
			$parsed_rows[] = [
				'id'         => $row['id'],
				'form_name'  => $row['form_name'],
				'created_at' => $row['created_at'],
				'data'       => $data,
			];
		}

		// Stream CSV
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=apex-form-submissions-' . gmdate( 'Y-m-d' ) . '.csv' );
		$output = fopen( 'php://output', 'w' );

		// Header row
		$header = array_map( [ self::class, 'sanitize_csv_cell' ], array_merge( [ 'ID', 'Form Name', 'Date' ], $all_keys ) );
		fputcsv( $output, $header );

		// Data rows
		foreach ( $parsed_rows as $pr ) {
			$csv_row = [ $pr['id'], $pr['form_name'], $pr['created_at'] ];
			foreach ( $all_keys as $key ) {
				$val = isset( $pr['data'][ $key ] ) ? $pr['data'][ $key ] : '';
				// Flatten arrays or signature data
				if ( is_array( $val ) ) {
					$val = implode( ', ', $val );
				}
				if ( strpos( (string) $val, 'data:image/' ) === 0 ) {
					$val = '[Signature]';
				}
				$csv_row[] = $val;
			}
			fputcsv( $output, array_map( [ self::class, 'sanitize_csv_cell' ], $csv_row ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- This closes the temporary php://output stream, not a filesystem path.
		fclose( $output );
		exit;
	}

	/**
	 * Prevent spreadsheet applications from evaluating submission values as
	 * formulas when an administrator opens an exported CSV file.
	 */
	public static function sanitize_csv_cell( $value ) {
		$value = (string) $value;
		if ( preg_match( '/^[=+\-@\t\r]/', ltrim( $value ) ) ) {
			return "'" . $value;
		}

		return $value;
	}

	/**
	 * Register Editor branding styles
	 */
	public function enqueue_editor_styles() {
		wp_enqueue_style( 'apexadfo-editor-sidebar-css' );
	}

	/**
	 * Register frontend assets
	 */
	public function register_assets() {
		wp_register_script(
			'apexadfo-advanced-nested-switcher-js',
			plugins_url( 'assets/js/advanced-nested-switcher.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-advanced-nested-switcher-css',
			plugins_url( 'assets/css/advanced-nested-switcher.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_script(
			'apexadfo-comparison-table-js',
			plugins_url( 'assets/js/comparison-table.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-comparison-table-css',
			plugins_url( 'assets/css/comparison-table.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_script(
			'apexadfo-advanced-timeline-js',
			plugins_url( 'assets/js/advanced-timeline.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-advanced-timeline-css',
			plugins_url( 'assets/css/advanced-timeline.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_script(
			'apexadfo-interactive-image-hotspots-js',
			plugins_url( 'assets/js/interactive-image-hotspots.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-interactive-image-hotspots-css',
			plugins_url( 'assets/css/interactive-image-hotspots.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_script(
			'apexadfo-before-after-image-comparison-js',
			plugins_url( 'assets/js/before-after-image-comparison.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-before-after-image-comparison-css',
			plugins_url( 'assets/css/before-after-image-comparison.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_script(
			'apexadfo-theme-builder-popup-js',
			plugins_url( 'assets/js/theme-builder-popup.js', __FILE__ ),
			[],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-theme-builder-popup-css',
			plugins_url( 'assets/css/theme-builder-popup.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_style(
			'apexadfo-archive-loop-css',
			plugins_url( 'assets/css/archive-loop.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_script(
			'apexadfo-conversational-funnel-js',
			plugins_url( 'assets/js/conversational-funnel.js', __FILE__ ),
			[],
			APEXADFO_VERSION,
			true
		);

		wp_register_script(
			'apexadfo-preloader',
			plugins_url( 'assets/js/preloader.js', __FILE__ ),
			[],
			APEXADFO_VERSION,
			true
		);
		wp_register_style(
			'apexadfo-preloader',
			plugins_url( 'assets/css/preloader.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_localize_script( 'apexadfo-conversational-funnel-js', 'apexadfoFunnelConfig', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'i18n'    => [
				'required' => esc_html__( 'Please complete this step.', 'apex-addons-for-elementor' ),
				'email'    => esc_html__( 'Please enter a valid email address.', 'apex-addons-for-elementor' ),
				'error'    => esc_html__( 'Something went wrong. Please try again.', 'apex-addons-for-elementor' ),
				'sending'  => esc_html__( 'Sendingâ€¦', 'apex-addons-for-elementor' ),
			],
		] );

		wp_register_style(
			'apexadfo-conversational-funnel-css',
			plugins_url( 'assets/css/conversational-funnel.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);
		wp_register_script(
			'apexadfo-quiz-builder-js',
			plugins_url( 'assets/js/quiz-builder.js', __FILE__ ),
			[],
			APEXADFO_VERSION,
			true
		);
		wp_localize_script( 'apexadfo-quiz-builder-js', 'apexadfoQuizConfig', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'i18n' => [
				'email' => esc_html__( 'Please enter a valid email address.', 'apex-addons-for-elementor' ),
				'error' => esc_html__( 'Something went wrong. Please try again.', 'apex-addons-for-elementor' ),
				'sending' => esc_html__( 'Calculatingâ€¦', 'apex-addons-for-elementor' ),
			],
		] );
		wp_register_style(
			'apexadfo-quiz-builder-css',
			plugins_url( 'assets/css/quiz-builder.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);
		wp_register_script(
			'apexadfo-container-stack-js',
			plugins_url( 'assets/js/container-stack.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-container-stack-css',
			plugins_url( 'assets/css/container-stack.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_script(
			'apexadfo-section-transitions-js',
			plugins_url( 'assets/js/section-transitions.js', __FILE__ ),
			[],
			APEXADFO_VERSION . '-st-2',
			true
		);

		wp_register_style(
			'apexadfo-section-transitions-css',
			plugins_url( 'assets/css/section-transitions.css', __FILE__ ),
			[],
			APEXADFO_VERSION . '-st-2'
		);

		wp_register_script(
			'apexadfo-container-carousel-js',
			plugins_url( 'assets/js/container-carousel.js', __FILE__ ),
			[ 'jquery', 'swiper' ],
			'1.0.42',
			true
		);

		wp_register_script(
			'apexadfo-nested-slider-js',
			plugins_url( 'assets/js/nested-slider.js', __FILE__ ),
			[ 'jquery', 'swiper' ],
			APEXADFO_VERSION . '.10',
			true
		);

		wp_register_style(
			'apexadfo-nested-slider-css',
			plugins_url( 'assets/css/nested-slider.css', __FILE__ ),
			[],
			APEXADFO_VERSION . '.10'
		);

		wp_register_script(
			'apexadfo-nested-content-switcher-js',
			plugins_url( 'assets/js/nested-content-switcher.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);

		wp_register_style(
			'apexadfo-nested-content-switcher-css',
			plugins_url( 'assets/css/nested-content-switcher.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_style(
			'apexadfo-container-carousel-css',
			plugins_url( 'assets/css/container-carousel.css', __FILE__ ),
			[],
			'1.0.22'
		);

		wp_register_script(
			'apexadfo-form-widget-js',
			plugins_url( 'assets/js/form-widget.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.22',
			true
		);

		wp_register_style(
			'apexadfo-form-widget-css',
			plugins_url( 'assets/css/form-widget.css', __FILE__ ),
			[],
			'1.0.22'
		);

		wp_register_style(
			'apexadfo-svg-icon-css',
			plugins_url( 'assets/css/svg-icon.css', __FILE__ ),
			[],
			'1.0.22'
		);

		wp_register_script(
			'apexadfo-premium-typography-js',
			plugins_url( 'assets/js/premium-typography.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);

		wp_register_style(
			'apexadfo-premium-typography-css',
			plugins_url( 'assets/css/premium-typography.css', __FILE__ ),
			[],
			'1.0.22'
		);

		wp_register_style(
			'apexadfo-widget-structural',
			plugins_url( 'assets/css/widget-structural.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);

		wp_register_style( 'apexadfo-global-inline', false, [], APEXADFO_VERSION );

		wp_register_script(
			'apexadfo-scroll-parallax-text-js',
			plugins_url( 'assets/js/scroll-parallax-text.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);

		wp_register_style(
			'apexadfo-scroll-parallax-text-css',
			plugins_url( 'assets/css/scroll-parallax-text.css', __FILE__ ),
			[],
			'1.0.22'
		);

		wp_register_script(
			'apexadfo-container-hscroll-js',
			plugins_url( 'assets/js/container-hscroll.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.59',
			true
		);

		wp_register_style(
			'apexadfo-container-hscroll-css',
			plugins_url( 'assets/css/container-hscroll.css', __FILE__ ),
			[],
			'1.0.59'
		);

		wp_register_script(
			'apexadfo-pinned-vertical-scroll-js',
			plugins_url( 'assets/js/pinned-vertical-scroll.js', __FILE__ ),
			[],
			APEXADFO_VERSION . '-pvs-2',
			true
		);

		wp_register_style(
			'apexadfo-pinned-vertical-scroll-css',
			plugins_url( 'assets/css/pinned-vertical-scroll.css', __FILE__ ),
			[],
			APEXADFO_VERSION . '-pvs-2'
		);

		wp_register_script(
			'apexadfo-text-reveal-js',
			plugins_url( 'assets/js/text-reveal.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.33',
			true
		);

		wp_register_style(
			'apexadfo-text-reveal-css',
			plugins_url( 'assets/css/text-reveal.css', __FILE__ ),
			[],
			'1.0.33'
		);

		// Fan Carousel Widget Assets
		wp_register_script(
			'apexadfo-fan-carousel-js',
			plugins_url( 'assets/js/fan-carousel.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.5',
			true
		);
		wp_register_style(
			'apexadfo-fan-carousel-css',
			plugins_url( 'assets/css/fan-carousel.css', __FILE__ ),
			[],
			'1.0.5'
		);

		// Global Smooth Scroll
		wp_register_script(
			'apexadfo-smooth-scroll-js',
			plugins_url( 'assets/js/smooth-scroll.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);

		// Global Custom Cursors
		wp_register_script(
			'apexadfo-custom-cursor-js',
			plugins_url( 'assets/js/custom-cursor.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);
		wp_register_style(
			'apexadfo-custom-cursor-css',
			plugins_url( 'assets/css/custom-cursor.css', __FILE__ ),
			[],
			'1.0.33'
		);

		// Editor global settings script
		wp_register_script(
			'apexadfo-editor-global-settings-js',
			plugins_url( 'assets/js/editor-global-settings.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);

		// Portfolio Hover Showcase assets
		wp_register_script(
			'apexadfo-portfolio-showcase-js',
			plugins_url( 'assets/js/portfolio-showcase.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);
		wp_register_style(
			'apexadfo-portfolio-showcase-css',
			plugins_url( 'assets/css/portfolio-showcase.css', __FILE__ ),
			[],
			'1.0.42'
		);

		// Magnetic Attraction assets
		wp_register_script(
			'apexadfo-magnetic-js',
			plugins_url( 'assets/js/magnetic-effect.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);

		// Flex Accordion assets
		wp_register_script(
			'apexadfo-flex-accordion-js',
			plugins_url( 'assets/js/flex-accordion.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.42',
			true
		);
		wp_register_style(
			'apexadfo-flex-accordion-css',
			plugins_url( 'assets/css/flex-accordion.css', __FILE__ ),
			[],
			'1.0.42'
		);

		// Team Member Showcase assets
		wp_register_script(
			'apexadfo-team-member-js',
			plugins_url( 'assets/js/team-member.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.50',
			true
		);
		wp_register_style(
			'apexadfo-team-member-css',
			plugins_url( 'assets/css/team-member.css', __FILE__ ),
			[],
			'1.0.50'
		);

		// Cinematic Slideshow Background assets
		wp_register_script(
			'apexadfo-kenburns-js',
			plugins_url( 'assets/js/kenburns-slideshow.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.55',
			true
		);
		wp_register_style(
			'apexadfo-kenburns-css',
			plugins_url( 'assets/css/kenburns-slideshow.css', __FILE__ ),
			[],
			'1.0.55'
		);

		// Site Logo Widget Assets
		wp_register_style(
			'apexadfo-site-logo-css',
			plugins_url( 'assets/css/site-logo.css', __FILE__ ),
			[],
			'1.0.0'
		);

		// Nav Menu Widget Assets
		wp_register_script(
			'apexadfo-nav-menu-js',
			plugins_url( 'assets/js/nav-menu.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.16',
			true
		);
		wp_register_style(
			'apexadfo-nav-menu-css',
			plugins_url( 'assets/css/nav-menu.css', __FILE__ ),
			[],
			'1.0.16'
		);

		// Header Search Widget Assets
		wp_register_script(
			'apexadfo-header-search-js',
			plugins_url( 'assets/js/header-search.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.1',
			true
		);
		wp_register_style(
			'apexadfo-header-search-css',
			plugins_url( 'assets/css/header-search.css', __FILE__ ),
			[],
			'1.0.1'
		);

		// Table of Contents Assets
		wp_register_script(
			'apexadfo-table-of-contents-js',
			plugins_url( 'assets/js/table-of-contents.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.0',
			true
		);
		wp_register_style(
			'apexadfo-table-of-contents-css',
			plugins_url( 'assets/css/table-of-contents.css', __FILE__ ),
			[],
			'1.0.0'
		);

		// Progress Tracker Assets
		wp_register_script(
			'apexadfo-progress-tracker-js',
			plugins_url( 'assets/js/progress-tracker.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.0',
			true
		);
		wp_register_style(
			'apexadfo-progress-tracker-css',
			plugins_url( 'assets/css/progress-tracker.css', __FILE__ ),
			[],
			'1.0.0'
		);
		wp_register_style(
			'apexadfo-author-box-css',
			plugins_url( 'assets/css/author-box.css', __FILE__ ),
			[],
			'1.0.0'
		);
		wp_register_style(
			'apexadfo-product-images-css',
			plugins_url( 'assets/css/product-images.css', __FILE__ ),
			[],
			APEXADFO_VERSION
		);
		wp_register_script(
			'apexadfo-product-images-js',
			plugins_url( 'assets/js/product-images.js', __FILE__ ),
			[ 'jquery' ],
			APEXADFO_VERSION,
			true
		);
	}



	/**
	 * Register the complete Classic Scroll Stack controls.
	 */
	public function register_container_stack_controls( $element, $section_id ) {
		$element->start_controls_section(
			'section_eas_container_stack',
			[
				'label' => esc_html__( 'Apex Scroll Stack', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control( 'eas_container_stack', [
			'label' => esc_html__( 'Enable Scroll Stack', 'apex-addons-for-elementor' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'label_on' => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
			'label_off' => esc_html__( 'No', 'apex-addons-for-elementor' ),
			'return_value' => 'yes', 'default' => 'no',
			'description' => esc_html__( 'A complete classic sticky-card stack with smooth scroll-linked movement and scale.', 'apex-addons-for-elementor' ),
		] );

		$slider_controls = [
			'eas_container_stack_sticky_top' => [ esc_html__( 'Sticky Top Offset (px)', 'apex-addons-for-elementor' ), 0, 300, 5, 80 ],
			'eas_container_stack_offset'     => [ esc_html__( 'Card Stacking Offset (px)', 'apex-addons-for-elementor' ), 0, 120, 5, 30 ],
			'eas_container_stack_scale_up'   => [ esc_html__( 'Initial Scale', 'apex-addons-for-elementor' ), 1, 1.25, 0.01, 1.08 ],
			'eas_container_stack_travel'     => [ esc_html__( 'Entry Travel (px)', 'apex-addons-for-elementor' ), 0, 400, 10, 100 ],
			'eas_container_stack_end_offset' => [ esc_html__( 'Animation End Offset (px)', 'apex-addons-for-elementor' ), 0, 300, 10, 100 ],
			'eas_container_stack_dim'        => [ esc_html__( 'Stacked Card Dim', 'apex-addons-for-elementor' ), 0, 0.5, 0.01, 0.10 ],
		];
		foreach ( $slider_controls as $control_id => $control ) {
			$element->add_control( $control_id, [
				'label' => $control[0],
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [ 'px' => [ 'min' => $control[1], 'max' => $control[2], 'step' => $control[3] ] ],
				'default' => [ 'size' => $control[4] ],
				'condition' => [ 'eas_container_stack' => 'yes' ],
			] );
		}

		$element->add_control( 'eas_container_stack_border_radius', [
			'label' => esc_html__( 'Card Border Radius (px)', 'apex-addons-for-elementor' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 0, 'max' => 80, 'step' => 1 ] ],
			'default' => [ 'size' => 12 ],
			'selectors' => [ '{{WRAPPER}} > .e-con-inner > .eas-container-stack-card, {{WRAPPER}} > .eas-container-stack-card' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;' ],
			'condition' => [ 'eas_container_stack' => 'yes' ],
		] );

		foreach ( [
			'eas_container_stack_disable_tablet' => esc_html__( 'Disable on Tablet', 'apex-addons-for-elementor' ),
			'eas_container_stack_disable_mobile' => esc_html__( 'Disable on Mobile', 'apex-addons-for-elementor' ),
			'eas_container_stack_reduced_motion' => esc_html__( 'Respect Reduced Motion', 'apex-addons-for-elementor' ),
		] as $control_id => $label ) {
			$element->add_control( $control_id, [
				'label' => $label,
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off' => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default' => 'eas_container_stack_reduced_motion' === $control_id ? 'yes' : 'no',
				'condition' => [ 'eas_container_stack' => 'yes' ],
			] );
		}

		$this->add_companion_features_notice(
			$element,
			'apexadfo_stack_companion_notice',
			esc_html__( 'Cinematic stack modes, per-card choreography, scene progress, synchronized backgrounds, and advanced snapping.', 'apex-addons-for-elementor' ),
			[ 'eas_container_stack' => 'yes' ]
		);

		// Companion extensions can append additional stack controls.
		do_action( 'apexadfo_stack_register_controls', $element );
		$element->end_controls_section();
	}

	/**
	 * Add Classic Stack attributes while allowing Pro to enrich the config.
	 */
	public function before_container_stack_render( $element ) {
		$settings = $element->get_settings_for_display();
		if ( 'yes' !== ( $settings['eas_container_stack'] ?? 'no' ) ) {
			return;
		}

		$config = [
			'stickyTop' => intval( $settings['eas_container_stack_sticky_top']['size'] ?? 80 ),
			'stackOffset' => intval( $settings['eas_container_stack_offset']['size'] ?? 30 ),
			'scaleUp' => floatval( $settings['eas_container_stack_scale_up']['size'] ?? 1.08 ),
			'travel' => intval( $settings['eas_container_stack_travel']['size'] ?? 100 ),
			'endOffset' => intval( $settings['eas_container_stack_end_offset']['size'] ?? 100 ),
			'dimFactor' => floatval( $settings['eas_container_stack_dim']['size'] ?? 0.10 ),
			'disableTablet' => sanitize_key( $settings['eas_container_stack_disable_tablet'] ?? 'no' ),
			'disableMobile' => sanitize_key( $settings['eas_container_stack_disable_mobile'] ?? 'no' ),
			'respectReducedMotion' => sanitize_key( $settings['eas_container_stack_reduced_motion'] ?? 'yes' ),
		];
		$config = apply_filters( 'apexadfo_container_stack_config', $config, $settings, $element );

		wp_enqueue_script( 'apexadfo-container-stack-js' );
		wp_enqueue_style( 'apexadfo-container-stack-css' );
		$element->add_render_attribute( '_wrapper', 'class', 'eas-container-stack-active' );
		if ( 'yes' === $config['disableTablet'] ) $element->add_render_attribute( '_wrapper', 'class', 'eas-stack-disable-tablet' );
		if ( 'yes' === $config['disableMobile'] ) $element->add_render_attribute( '_wrapper', 'class', 'eas-stack-disable-mobile' );
		$element->add_render_attribute( '_wrapper', 'data-eas-stack-active', 'yes' );
		$element->add_render_attribute( '_wrapper', 'data-eas-stack-config', wp_json_encode( $config ) );
	}

	/**
	 * Register the complete Free Curtain Up section transition.
	 */
	public function register_section_transition_controls( $element, $section_id ) {
		$element->start_controls_section(
			'section_eas_section_transitions',
			[
				'label' => esc_html__( 'Apex Section Transitions', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control( 'eas_section_transition', [
			'label' => esc_html__( 'Enable Section Transition', 'apex-addons-for-elementor' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'label_on' => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
			'label_off' => esc_html__( 'No', 'apex-addons-for-elementor' ),
			'return_value' => 'yes',
			'default' => 'no',
			'description' => esc_html__( 'The current container rises over the previous sibling section as the page scrolls.', 'apex-addons-for-elementor' ),
		] );

		$element->add_control( 'eas_section_transition_mode', [
			'label' => esc_html__( 'Transition', 'apex-addons-for-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
			'default' => 'curtain_up',
			'options' => [ 'curtain_up' => esc_html__( 'Curtain Up', 'apex-addons-for-elementor' ) ],
			'condition' => [ 'eas_section_transition' => 'yes' ],
		] );

		$element->add_control( 'eas_section_transition_pin_previous', [
			'label' => esc_html__( 'Hold Previous Section', 'apex-addons-for-elementor' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'label_on' => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
			'label_off' => esc_html__( 'No', 'apex-addons-for-elementor' ),
			'return_value' => 'yes',
			'default' => 'yes',
			'description' => esc_html__( 'Keeps the previous sibling section behind this reveal. It automatically falls back safely when the layout is incompatible.', 'apex-addons-for-elementor' ),
			'condition' => [ 'eas_section_transition' => 'yes' ],
		] );

		foreach ( [
			'eas_section_transition_start' => [ esc_html__( 'Start at Viewport (%)', 'apex-addons-for-elementor' ), 55, 120, 1, 100 ],
			'eas_section_transition_end' => [ esc_html__( 'Finish at Viewport (%)', 'apex-addons-for-elementor' ), -20, 70, 1, 10 ],
			'eas_section_transition_offset' => [ esc_html__( 'Entry Lift (px)', 'apex-addons-for-elementor' ), 0, 300, 1, 80 ],
			'eas_section_transition_radius' => [ esc_html__( 'Entry Corner Radius (px)', 'apex-addons-for-elementor' ), 0, 100, 1, 28 ],
			'eas_section_transition_smoothing' => [ esc_html__( 'Motion Smoothing', 'apex-addons-for-elementor' ), 0.04, 1, 0.01, 0.16 ],
		] as $control_id => $control ) {
			$element->add_control( $control_id, [
				'label' => $control[0],
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [ 'px' => [ 'min' => $control[1], 'max' => $control[2], 'step' => $control[3] ] ],
				'default' => [ 'size' => $control[4] ],
				'condition' => [ 'eas_section_transition' => 'yes' ],
			] );
		}

		$element->add_control( 'eas_section_transition_pin_top', [
			'label' => esc_html__( 'Pinned Top Offset (px)', 'apex-addons-for-elementor' ),
			'type' => \Elementor\Controls_Manager::SLIDER,
			'range' => [ 'px' => [ 'min' => 0, 'max' => 300, 'step' => 1 ] ],
			'default' => [ 'size' => 0 ],
			'condition' => [ 'eas_section_transition' => 'yes', 'eas_section_transition_pin_previous' => 'yes' ],
		] );

		foreach ( [
			'eas_section_transition_disable_tablet' => esc_html__( 'Disable on Tablet', 'apex-addons-for-elementor' ),
			'eas_section_transition_disable_mobile' => esc_html__( 'Disable on Mobile', 'apex-addons-for-elementor' ),
			'eas_section_transition_reduced_motion' => esc_html__( 'Respect Reduced Motion', 'apex-addons-for-elementor' ),
		] as $control_id => $label ) {
			$element->add_control( $control_id, [
				'label' => $label,
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off' => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default' => 'eas_section_transition_reduced_motion' === $control_id ? 'yes' : 'no',
				'condition' => [ 'eas_section_transition' => 'yes' ],
			] );
		}

		$this->add_companion_features_notice(
			$element,
			'apexadfo_section_transition_companion_notice',
			esc_html__( 'Viewport Cover, Section Push, Scale Reveal, Hero Zoom Through, split reveals, masks, and advanced transition choreography.', 'apex-addons-for-elementor' ),
			[ 'eas_section_transition' => 'yes' ]
		);

		do_action( 'apexadfo_section_transitions_register_controls', $element );
		$element->end_controls_section();
	}

	/**
	 * Add frontend configuration for an enabled section transition.
	 */
	public function before_section_transition_render( $element ) {
		$settings = $element->get_settings_for_display();
		if ( 'yes' !== ( $settings['eas_section_transition'] ?? 'no' ) ) {
			return;
		}

		$config = [
			'mode' => 'curtain_up',
			'pinPrevious' => sanitize_key( $settings['eas_section_transition_pin_previous'] ?? 'yes' ),
			'pinTop' => floatval( $settings['eas_section_transition_pin_top']['size'] ?? 0 ),
			'start' => floatval( $settings['eas_section_transition_start']['size'] ?? 100 ),
			'end' => floatval( $settings['eas_section_transition_end']['size'] ?? 10 ),
			'entryOffset' => floatval( $settings['eas_section_transition_offset']['size'] ?? 80 ),
			'entryRadius' => floatval( $settings['eas_section_transition_radius']['size'] ?? 28 ),
			'smoothing' => floatval( $settings['eas_section_transition_smoothing']['size'] ?? 0.16 ),
			'disableTablet' => sanitize_key( $settings['eas_section_transition_disable_tablet'] ?? 'no' ),
			'disableMobile' => sanitize_key( $settings['eas_section_transition_disable_mobile'] ?? 'no' ),
			'respectReducedMotion' => sanitize_key( $settings['eas_section_transition_reduced_motion'] ?? 'yes' ),
		];
		$config = apply_filters( 'apexadfo_section_transitions_config', $config, $settings, $element );

		wp_enqueue_script( 'apexadfo-section-transitions-js' );
		wp_enqueue_style( 'apexadfo-section-transitions-css' );
		$element->add_render_attribute( '_wrapper', 'class', 'eas-section-transition' );
		$element->add_render_attribute( '_wrapper', 'data-eas-section-transition', wp_json_encode( $config ) );
	}

	/**
	 * Enqueue nested slider assets inside Elementor editor preview iframe
	 */
	public function enqueue_nested_slider_editor_assets() {
		wp_enqueue_style( 'apexadfo-nested-slider-css' );
		wp_enqueue_script( 'apexadfo-nested-slider-js' );
		wp_enqueue_style( 'apexadfo-nested-content-switcher-css' );
		wp_enqueue_script( 'apexadfo-nested-content-switcher-js' );
	}

	/**
	 * Enqueue Classic Stack and its live editor bridge.
	 */
	public function enqueue_stack_editor_assets() {
		wp_enqueue_style( 'apexadfo-container-stack-css' );
		wp_enqueue_script( 'apexadfo-container-stack-js' );
		wp_enqueue_script(
			'apexadfo-container-stack-editor-js',
			plugins_url( 'assets/js/container-stack-editor.js', __FILE__ ),
			[ 'jquery', 'apexadfo-container-stack-js' ],
			APEXADFO_VERSION,
			true
		);
	}

	/**
	 * Enqueue Section Transitions in Elementor's preview iframe.
	 */
	public function enqueue_section_transition_editor_assets() {
		wp_enqueue_style( 'apexadfo-section-transitions-css' );
		wp_enqueue_script( 'apexadfo-section-transitions-js' );
	}

	/**
	 * Enqueue background slideshow assets inside Elementor editor preview iframe
	 */
	public function enqueue_slideshow_editor_assets() {
		wp_enqueue_style( 'apexadfo-kenburns-css' );
		wp_enqueue_script( 'apexadfo-kenburns-js' );
	}

	/**
	 * Register the complete Free Pinned Vertical Scroll controls.
	 */
	public function register_pinned_vertical_scroll_controls( $element, $section_id ) {
		$element->start_controls_section(
			'section_apexadfo_pvs',
			[
				'label' => esc_html__( 'Apex Pinned Vertical Scroll', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control(
			'apexadfo_pvs_enable',
			[
				'label'        => esc_html__( 'Enable Pinned Vertical Scroll', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Pins this container while its direct child containers move vertically. Child heights remain controlled by their own Elementor settings.', 'apex-addons-for-elementor' ),
			]
		);

		$element->add_control(
			'apexadfo_pvs_notice',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<strong>' . esc_html__( 'How to build it', 'apex-addons-for-elementor' ) . '</strong><br>' . esc_html__( 'Each direct child container becomes one naturally sized vertical story panel. Design every panel with normal Elementor controls.', 'apex-addons-for-elementor' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$element->add_responsive_control(
			'apexadfo_pvs_stage_height',
			[
				'label'      => esc_html__( 'Visible Stage Height', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'vh' ],
				'range'      => [ 'vh' => [ 'min' => 35, 'max' => 100, 'step' => 1 ] ],
				'default'    => [ 'size' => 100, 'unit' => 'vh' ],
				'description'=> esc_html__( 'The visible window inside the pinned container. Header and bottom offsets are subtracted automatically.', 'apex-addons-for-elementor' ),
				'condition'  => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$element->add_responsive_control(
			'apexadfo_pvs_top_offset',
			[
				'label'      => esc_html__( 'Sticky Top Offset', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 300, 'step' => 1 ] ],
				'default'    => [ 'size' => 0, 'unit' => 'px' ],
				'condition'  => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$element->add_responsive_control(
			'apexadfo_pvs_bottom_offset',
			[
				'label'      => esc_html__( 'Viewport Bottom Offset', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 300, 'step' => 1 ] ],
				'default'    => [ 'size' => 0, 'unit' => 'px' ],
				'condition'  => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$element->add_responsive_control(
			'apexadfo_pvs_gap',
			[
				'label'      => esc_html__( 'Gap Between Children', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'rem', 'vw' ],
				'range'      => [
					'px'  => [ 'min' => 0, 'max' => 240, 'step' => 1 ],
					'rem' => [ 'min' => 0, 'max' => 15, 'step' => 0.1 ],
					'vw'  => [ 'min' => 0, 'max' => 20, 'step' => 0.1 ],
				],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'condition'  => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'apexadfo_pvs_direction',
			[
				'label'     => esc_html__( 'Movement Direction', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'up',
				'options'   => [
					'up'   => esc_html__( 'Bottom to Top', 'apex-addons-for-elementor' ),
					'down' => esc_html__( 'Top to Bottom', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'apexadfo_pvs_distance',
			[
				'label'       => esc_html__( 'Scroll Distance', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '1',
				'options'     => [
					'0.65' => esc_html__( 'Short / Fast', 'apex-addons-for-elementor' ),
					'1'    => esc_html__( 'Natural', 'apex-addons-for-elementor' ),
					'1.5'  => esc_html__( 'Long / Smooth', 'apex-addons-for-elementor' ),
					'2'    => esc_html__( 'Cinematic', 'apex-addons-for-elementor' ),
				],
				'description' => esc_html__( 'Controls how much page scrolling is used to move through the complete inner content.', 'apex-addons-for-elementor' ),
				'condition'   => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$element->add_control(
			'apexadfo_pvs_smoothing',
			[
				'label'       => esc_html__( 'Motion Smoothing', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'range'       => [ '' => [ 'min' => 0, 'max' => 100, 'step' => 1 ] ],
				'default'     => [ 'size' => 18 ],
				'description' => esc_html__( 'Zero follows the scrollbar immediately. Higher values create a heavier cinematic catch-up.', 'apex-addons-for-elementor' ),
				'condition'   => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		foreach ( [ 'tablet' => 'Tablet', 'mobile' => 'Mobile' ] as $device => $label ) {
			$element->add_control(
				'apexadfo_pvs_disable_' . $device,
				[
					/* translators: %s: responsive device name. */
					'label'        => sprintf( esc_html__( 'Disable Pinning on %s', 'apex-addons-for-elementor' ), $label ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'no',
					'condition'    => [ 'apexadfo_pvs_enable' => 'yes' ],
				]
			);
		}

		$element->add_control(
			'apexadfo_pvs_accessible_name',
			[
				'label'       => esc_html__( 'Accessible Region Name', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Vertical scrolling story', 'apex-addons-for-elementor' ),
				'label_block' => true,
				'condition'   => [ 'apexadfo_pvs_enable' => 'yes' ],
			]
		);

		$this->add_companion_features_notice(
			$element,
			'apexadfo_pvs_companion_notice',
			esc_html__( 'Pinned Split Scene layouts, panel snapping, navigation, deep links, synchronized targets, active-panel transitions, and nested-content choreography.', 'apex-addons-for-elementor' ),
			[ 'apexadfo_pvs_enable' => 'yes' ]
		);

		do_action( 'apexadfo_pinned_vertical_scroll_register_controls', $element, $section_id );
		$element->end_controls_section();
	}

	/**
	 * Add sanitized Pinned Vertical Scroll configuration to the container.
	 */
	public function before_pinned_vertical_scroll_render( $element ) {
		$settings = $element->get_settings_for_display();
		if ( 'yes' !== ( $settings['apexadfo_pvs_enable'] ?? 'no' ) ) {
			return;
		}

		wp_enqueue_script( 'apexadfo-pinned-vertical-scroll-js' );
		wp_enqueue_style( 'apexadfo-pinned-vertical-scroll-css' );

		$device_slider = static function ( $key, $fallback, $min, $max ) use ( $settings ) {
			$read_size = static function ( $value, $default ) {
				return isset( $value['size'] ) && '' !== (string) $value['size']
					? floatval( $value['size'] )
					: $default;
			};
			$desktop = max( $min, min( $max, $read_size( $settings[ $key ] ?? [], $fallback ) ) );
			$tablet  = max( $min, min( $max, $read_size( $settings[ $key . '_tablet' ] ?? [], $desktop ) ) );
			$mobile  = max( $min, min( $max, $read_size( $settings[ $key . '_mobile' ] ?? [], $tablet ) ) );
			return [ 'desktop' => $desktop, 'tablet' => $tablet, 'mobile' => $mobile ];
		};

		$gap_unit = static function ( $value, $fallback = '24px' ) {
			$size = isset( $value['size'] ) && '' !== (string) $value['size'] ? floatval( $value['size'] ) : null;
			$unit = isset( $value['unit'] ) && in_array( $value['unit'], [ 'px', 'rem', 'vw' ], true ) ? $value['unit'] : 'px';
			return null === $size ? $fallback : max( 0, min( 240, $size ) ) . $unit;
		};

		$desktop_gap = $gap_unit( $settings['apexadfo_pvs_gap'] ?? [] );
		$tablet_gap  = $gap_unit( $settings['apexadfo_pvs_gap_tablet'] ?? [], $desktop_gap );
		$mobile_gap  = $gap_unit( $settings['apexadfo_pvs_gap_mobile'] ?? [], $tablet_gap );
		$distance    = (string) ( $settings['apexadfo_pvs_distance'] ?? '1' );
		$config      = [
			'layoutMode'     => 'direct',
			'direction'     => in_array( $settings['apexadfo_pvs_direction'] ?? 'up', [ 'up', 'down' ], true ) ? $settings['apexadfo_pvs_direction'] : 'up',
			'distance'      => in_array( $distance, [ '0.65', '1', '1.5', '2' ], true ) ? floatval( $distance ) : 1,
			'smoothing'     => max( 0, min( 100, floatval( $settings['apexadfo_pvs_smoothing']['size'] ?? 18 ) ) ),
			'stageHeight'   => $device_slider( 'apexadfo_pvs_stage_height', 100, 35, 100 ),
			'topOffset'     => $device_slider( 'apexadfo_pvs_top_offset', 0, 0, 300 ),
			'bottomOffset'  => $device_slider( 'apexadfo_pvs_bottom_offset', 0, 0, 300 ),
			'gap'           => [ 'desktop' => $desktop_gap, 'tablet' => $tablet_gap, 'mobile' => $mobile_gap ],
			'disableTablet' => 'yes' === ( $settings['apexadfo_pvs_disable_tablet'] ?? 'no' ) ? 'yes' : 'no',
			'disableMobile' => 'yes' === ( $settings['apexadfo_pvs_disable_mobile'] ?? 'no' ) ? 'yes' : 'no',
		];

		$config = apply_filters( 'apexadfo_pinned_vertical_scroll_config', $config, $settings, $element );
		$element->add_render_attribute( '_wrapper', 'class', 'apexadfo-pvs-active' );
		$element->add_render_attribute( '_wrapper', 'class', 'apexadfo-pvs-mode-' . $config['layoutMode'] );
		$element->add_render_attribute( '_wrapper', 'data-apexadfo-pvs-config', wp_json_encode( $config ) );
		$element->add_render_attribute( '_wrapper', 'role', 'region' );
		$element->add_render_attribute( '_wrapper', 'aria-label', sanitize_text_field( $settings['apexadfo_pvs_accessible_name'] ?? esc_html__( 'Vertical scrolling story', 'apex-addons-for-elementor' ) ) );
	}

	/**
	 * Load the Free runtime in Elementor's preview iframe. The runtime deliberately
	 * keeps the editor DOM unpinned so every nested child remains selectable.
	 */
	public function enqueue_pinned_vertical_scroll_editor_assets() {
		wp_enqueue_style( 'apexadfo-pinned-vertical-scroll-css' );
		wp_enqueue_script( 'apexadfo-pinned-vertical-scroll-js' );
	}

	/**
	 * Register the Free Horizontal Storytelling controls.
	 *
	 * The legacy control IDs are intentionally retained so existing pages upgrade
	 * without losing their saved behavior.
	 */
	public function register_container_hscroll_story_controls( $element, $section_id ) {
		$element->start_controls_section(
			'section_eas_container_hscroll',
			[
				'label' => esc_html__( 'Apex Horizontal Storytelling', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control(
			'eas_container_hscroll',
			[
				'label'        => esc_html__( 'Enable Horizontal Story', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Direct child containers become automatically sized horizontal story panels. No manual 100VW or 100VH setup is required.', 'apex-addons-for-elementor' ),
			]
		);

		$element->add_control(
			'eas_hscroll_story_notice',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<div style="background:#172033;border-left:3px solid #7c3aed;padding:10px 12px;border-radius:4px;font-size:12px;line-height:1.5;color:#eee;"><strong style="color:#c4b5fd;">'
					. esc_html__( 'Automatic Story Layout', 'apex-addons-for-elementor' )
					. '</strong><br>'
					. esc_html__( 'Apex handles panel sizing, viewport height, pinning, scroll distance and reduced-motion fallback automatically.', 'apex-addons-for-elementor' )
					. '</div>',
				'content_classes' => 'elementor-panel-alert',
				'condition'       => [ 'eas_container_hscroll' => 'yes' ],
			]
		);

		$element->add_control(
			'eas_hscroll_speed',
			[
				'label'       => esc_html__( 'Story Scroll Distance', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '1',
				'options'     => [
					'0.5' => esc_html__( 'Short / Fast', 'apex-addons-for-elementor' ),
					'1'   => esc_html__( 'Balanced', 'apex-addons-for-elementor' ),
					'1.5' => esc_html__( 'Long / Cinematic', 'apex-addons-for-elementor' ),
					'2'   => esc_html__( 'Extra Long', 'apex-addons-for-elementor' ),
				],
				'description' => esc_html__( 'Controls how much vertical scrolling advances the horizontal story.', 'apex-addons-for-elementor' ),
				'condition'   => [ 'eas_container_hscroll' => 'yes' ],
			]
		);

		$element->add_control(
			'eas_hscroll_snap',
			[
				'label'        => esc_html__( 'Soft Panel Snap', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Gently settles on the nearest panel when ordinary scrolling stops.', 'apex-addons-for-elementor' ),
				'condition'    => [ 'eas_container_hscroll' => 'yes' ],
			]
		);

		$element->add_control(
			'eas_hscroll_progress',
			[
				'label'     => esc_html__( 'Progress Indicator', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'bar',
				'options'   => [
					'none' => esc_html__( 'None', 'apex-addons-for-elementor' ),
					'bar'  => esc_html__( 'Progress Bar', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'eas_container_hscroll' => 'yes' ],
			]
		);

		$element->add_control(
			'eas_hscroll_direction',
			[
				'label'     => esc_html__( 'Story Direction', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'auto',
				'options'   => [
					'auto' => esc_html__( 'Automatic (Site Direction)', 'apex-addons-for-elementor' ),
					'ltr'  => esc_html__( 'Left to Right', 'apex-addons-for-elementor' ),
					'rtl'  => esc_html__( 'Right to Left', 'apex-addons-for-elementor' ),
				],
				'condition' => [ 'eas_container_hscroll' => 'yes' ],
			]
		);

		foreach ( [ 'tablet' => 'Tablet', 'mobile' => 'Mobile' ] as $device => $label ) {
			$element->add_control(
				'eas_hscroll_disable_' . $device,
				[
					/* translators: %s: device name. */
					'label'        => sprintf( esc_html__( 'Disable Pinning on %s', 'apex-addons-for-elementor' ), $label ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'no',
					'condition'    => [ 'eas_container_hscroll' => 'yes' ],
				]
			);
		}

		$element->add_control(
			'eas_hscroll_mobile_fallback',
			[
				'label'       => esc_html__( 'Disabled Device Fallback', 'apex-addons-for-elementor' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'vertical',
				'options'     => [
					'vertical' => esc_html__( 'Natural Vertical Stack', 'apex-addons-for-elementor' ),
					'swipe'    => esc_html__( 'Native Horizontal Swipe', 'apex-addons-for-elementor' ),
				],
				'description' => esc_html__( 'Also used when the visitor requests reduced motion.', 'apex-addons-for-elementor' ),
				'condition'   => [ 'eas_container_hscroll' => 'yes' ],
			]
		);

		$this->add_companion_features_notice(
			$element,
			'apexadfo_hscroll_companion_notice',
			esc_html__( 'Advanced panel transitions, synchronized backgrounds, cinematic inner reveals, navigation styles, deep links, and per-panel story data.', 'apex-addons-for-elementor' ),
			[ 'eas_container_hscroll' => 'yes' ]
		);

		$element->end_controls_section();
	}

	/**
	 * Register Free progress styling. Pro appends advanced navigation styling.
	 */
	public function register_container_hscroll_style_controls( $element, $section_id ) {
		$element->start_controls_section(
			'section_eas_hscroll_style',
			[
				'label'     => esc_html__( 'Horizontal Story Style', 'apex-addons-for-elementor' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => [ 'eas_container_hscroll' => 'yes' ],
			]
		);

		$element->add_control(
			'eas_hscroll_bar_color',
			[
				'label'     => esc_html__( 'Progress Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#7c3aed',
				'selectors' => [ '{{WRAPPER}}' => '--eas-hscroll-accent: {{VALUE}};' ],
				'condition' => [ 'eas_hscroll_progress' => 'bar' ],
			]
		);

		$element->add_control(
			'eas_hscroll_bar_height',
			[
				'label'      => esc_html__( 'Progress Height', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 2, 'max' => 12 ] ],
				'default'    => [ 'size' => 4, 'unit' => 'px' ],
				'selectors'  => [ '{{WRAPPER}}' => '--eas-hscroll-progress-size: {{SIZE}}{{UNIT}};' ],
				'condition'  => [ 'eas_hscroll_progress' => 'bar' ],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Render the Free story configuration and allow Pro to append enhancements.
	 */
	public function before_element_hscroll_story_render( $element ) {
		$settings = $element->get_settings_for_display();
		if ( empty( $settings['eas_container_hscroll'] ) || 'yes' !== $settings['eas_container_hscroll'] ) {
			return;
		}

		wp_enqueue_script( 'apexadfo-container-hscroll-js' );
		wp_enqueue_style( 'apexadfo-container-hscroll-css' );

		$speed = (string) ( $settings['eas_hscroll_speed'] ?? '1' );
		$config = [
			'speed'          => in_array( $speed, [ '0.5', '1', '1.5', '2', 'snap_one' ], true ) ? $speed : '1',
			'snap'           => 'yes' === ( $settings['eas_hscroll_snap'] ?? 'no' ) ? 'yes' : 'no',
			'progress'       => in_array( $settings['eas_hscroll_progress'] ?? 'bar', [ 'none', 'bar' ], true ) ? $settings['eas_hscroll_progress'] : 'bar',
			'direction'      => in_array( $settings['eas_hscroll_direction'] ?? 'auto', [ 'auto', 'ltr', 'rtl' ], true ) ? $settings['eas_hscroll_direction'] : 'auto',
			'disableTablet'  => 'yes' === ( $settings['eas_hscroll_disable_tablet'] ?? 'no' ) ? 'yes' : 'no',
			'disableMobile'  => 'yes' === ( $settings['eas_hscroll_disable_mobile'] ?? 'no' ) ? 'yes' : 'no',
			'mobileFallback' => in_array( $settings['eas_hscroll_mobile_fallback'] ?? 'vertical', [ 'vertical', 'swipe' ], true ) ? $settings['eas_hscroll_mobile_fallback'] : 'vertical',
		];

		$config = apply_filters( 'apexadfo_container_hscroll_config', $config, $settings );
		$element->add_render_attribute( '_wrapper', 'data-eas-hscroll-config', wp_json_encode( $config ) );
		$element->add_render_attribute( '_wrapper', 'class', 'eas-container-hscroll-active' );
		$element->add_render_attribute( '_wrapper', 'role', 'region' );
		$element->add_render_attribute( '_wrapper', 'aria-label', esc_attr__( 'Horizontal story', 'apex-addons-for-elementor' ) );
		$element->add_render_attribute( '_wrapper', 'aria-roledescription', esc_attr__( 'horizontal story', 'apex-addons-for-elementor' ) );
	}

	/**
	 * Register Container Horizontal Scroll controls section on Container elements
	 *
	 * @deprecated 1.0.52 Kept temporarily so third-party method calls do not fatal.
	 */
	public function register_container_hscroll_controls( $element, $section_id ) {
		$element->start_controls_section(
			'section_eas_container_hscroll',
			[
				'label' => esc_html__( 'Apex Horizontal Scroll Section', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			]
		);

		$element->add_control(
			'eas_container_hscroll',
			[
				'label'        => esc_html__( 'Enable Horizontal Scroll', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'Converts child containers into full-screen horizontal sliding panels. Each child must be set to 100VW width and 100VH height for the effect to work correctly.', 'apex-addons-for-elementor' ),
			]
		);

		$element->add_control(
			'eas_hscroll_notice',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => '<div style="background:#1a1a2e;border-left:3px solid #e94560;padding:10px 12px;border-radius:4px;font-size:12px;line-height:1.5;color:#eee;">'
					. '<strong style="color:#e94560;">âš  Important:</strong><br>'
					. '1. Set this container height to <strong>100VH</strong><br>'
					. '2. Set each child container to <strong>100VW</strong> width and <strong>100VH</strong> height<br>'
					. '3. Design your content inside each child panel freely'
					. '</div>',
				'content_classes' => 'elementor-panel-alert',
				'condition'       => [
					'eas_container_hscroll' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_hscroll_speed',
			[
				'label'   => esc_html__( 'Scroll Speed', 'apex-addons-for-elementor' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'0.5'      => esc_html__( 'Fast', 'apex-addons-for-elementor' ),
					'1'        => esc_html__( 'Normal', 'apex-addons-for-elementor' ),
					'1.5'      => esc_html__( 'Slow', 'apex-addons-for-elementor' ),
					'2'        => esc_html__( 'Very Slow', 'apex-addons-for-elementor' ),
					'snap_one' => esc_html__( 'One-Scroll Smooth Snap', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'eas_container_hscroll' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_hscroll_disable_tablet',
			[
				'label'        => esc_html__( 'Disable on Tablet', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'eas_container_hscroll' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_hscroll_disable_mobile',
			[
				'label'        => esc_html__( 'Disable on Mobile', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'eas_container_hscroll' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Inject Horizontal Scroll config attributes in frontend
	 */
	public function before_element_hscroll_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( ! empty( $settings['eas_container_hscroll'] ) && 'yes' === $settings['eas_container_hscroll'] ) {
			wp_enqueue_script( 'apexadfo-container-hscroll-js' );
			wp_enqueue_style( 'apexadfo-container-hscroll-css' );

			$hscroll_config = [
				'speed'         => esc_attr( $settings['eas_hscroll_speed'] ?? '1' ),
				'disableTablet' => esc_attr( $settings['eas_hscroll_disable_tablet'] ?? 'no' ),
				'disableMobile' => esc_attr( $settings['eas_hscroll_disable_mobile'] ?? 'no' ),
			];

			$hscroll_config = apply_filters( 'apexadfo_container_hscroll_config', $hscroll_config, $settings );

			$element->add_render_attribute( '_wrapper', 'data-eas-hscroll-config', wp_json_encode( $hscroll_config ) );
			$element->add_render_attribute( '_wrapper', 'class', 'eas-container-hscroll-active' );
		}
	}

	/**
	 * Enqueue Horizontal Scroll assets inside Elementor editor preview iframe
	 */
	public function enqueue_hscroll_editor_assets() {
		wp_enqueue_style(
			'apexadfo-container-hscroll-css',
			plugins_url( 'assets/css/container-hscroll.css', __FILE__ ),
			[],
			'1.0.59'
		);
		wp_enqueue_script(
			'apexadfo-container-hscroll-js',
			plugins_url( 'assets/js/container-hscroll.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.59',
			true
		);
		wp_enqueue_script(
			'apexadfo-container-hscroll-editor-js',
			plugins_url( 'assets/js/container-hscroll-editor.js', __FILE__ ),
			[ 'jquery', 'apexadfo-container-hscroll-js' ],
			'1.0.59',
			true
		);
	}

	/**
	 * Enqueue Editor-only global settings script and sidebar styling
	 */
	public function enqueue_editor_global_settings_js() {
		// Enqueue the custom sidebar panel stylesheet inside the editor window
		wp_enqueue_style(
			'apexadfo-editor-sidebar-css',
			plugins_url( 'assets/css/editor-sidebar.css', __FILE__ ),
			[],
			'1.0.41'
		);

		wp_enqueue_script(
			'apexadfo-editor-global-settings-js',
			plugins_url( 'assets/js/editor-global-settings.js', __FILE__ ),
			[ 'jquery' ],
			'1.0.41',
			true
		);

		// Localize the current saved options to populate form inputs dynamically
		wp_localize_script(
			'apexadfo-editor-global-settings-js',
			'apexadfoGlobalSettingsData',
			[
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'apexadfo_global_settings_nonce' ),
				'settings'    => get_option( 'apexadfo_global_settings', [] ),
				'favicon_url' => plugins_url( 'assets/images/apex-favicon.png', __FILE__ ),
			]
		);

		// Enqueue nested slider editor helper script
		wp_enqueue_script(
			'apexadfo-nested-slider-editor-js',
			plugins_url( 'assets/js/nested-slider-editor.js', __FILE__ ),
			[ 'jquery', 'nested-elements' ],
			APEXADFO_VERSION,
			true
		);

		// Register the switcher with Elementor's nested element editor model and view.
		if ( self::is_addon_active( 'nested_content_switcher' ) ) {
			wp_enqueue_script(
				'apexadfo-nested-switcher-editor-js',
				plugins_url( 'assets/js/nested-content-switcher-editor.js', __FILE__ ),
				[ 'jquery', 'nested-elements' ],
				APEXADFO_VERSION,
				true
			);
		}
	}

	/**
	 * Enqueue frontend global assets based on settings
	 */
	public function enqueue_frontend_global_settings() {
		// Verify Elementor is active
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}
		wp_enqueue_style( 'apexadfo-widget-structural' );

		// Disable smooth scroll and cursors inside the editor panel preview iframe
		$is_preview_mode = isset( \Elementor\Plugin::$instance->preview )
			&& method_exists( \Elementor\Plugin::$instance->preview, 'is_preview_mode' )
			&& \Elementor\Plugin::$instance->preview->is_preview_mode();
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || $is_preview_mode ) {
			return;
		}

		$settings = get_option( 'apexadfo_global_settings', [] );
		if ( empty( $settings ) ) {
			return;
		}

		// Enqueue smooth scroll if active
		if ( ! empty( $settings['smooth_scroll'] ) && 'yes' === $settings['smooth_scroll'] ) {
			wp_enqueue_script( 'apexadfo-smooth-scroll-js' );

			$smooth_duration = isset( $settings['smooth_duration'] ) ? (float) $settings['smooth_duration'] : 0.8;
			$smooth_duration = max( 0.1, min( 3.0, $smooth_duration ) );

			$smooth_config = [
				'enabled'  => 'yes',
				'duration' => $smooth_duration,
				'mode'     => 'lerp',
			];
			wp_add_inline_script( 'apexadfo-smooth-scroll-js', 'window.apexadfoSmoothScrollConfig = ' . wp_json_encode( $smooth_config ) . ';', 'before' );
		}

		// Enqueue custom cursor if active
		if ( ! empty( $settings['cursor_style'] ) && 'none' !== $settings['cursor_style'] ) {
			wp_enqueue_script( 'apexadfo-custom-cursor-js' );
			wp_enqueue_style( 'apexadfo-custom-cursor-css' );

			$cursor_config = [
				'style' => esc_attr( $settings['cursor_style'] ),
				'color' => esc_attr( $settings['cursor_color'] ?? '#a855f7' ),
			];
			wp_add_inline_script( 'apexadfo-custom-cursor-js', 'window.apexadfoCustomCursorConfig = ' . wp_json_encode( $cursor_config ) . ';', 'before' );
		}
	}

	/**
	 * Output inline webkit scrollbar overrides inside wp_head
	 */
	public function output_global_scrollbar_css() {
		$settings = get_option( 'apexadfo_global_settings', [] );
		if ( empty( $settings ) || empty( $settings['scrollbar_styling'] ) || 'yes' !== $settings['scrollbar_styling'] ) {
			return;
		}

		$width = max( 1, min( 40, absint( $settings['scrollbar_width'] ?? 10 ) ) );
		$bg    = sanitize_hex_color( $settings['scrollbar_bg'] ?? '#1e1e1e' ) ?: '#1e1e1e';
		$thumb = sanitize_hex_color( $settings['scrollbar_thumb'] ?? '#a855f7' ) ?: '#a855f7';
		$css   = '::-webkit-scrollbar{width:' . $width . 'px;height:' . $width . 'px}'
			. '::-webkit-scrollbar-track{background-color:' . $bg . '}'
			. '::-webkit-scrollbar-thumb{background-color:' . $thumb . ';border-radius:10px}';
		wp_enqueue_style( 'apexadfo-global-inline' );
		wp_add_inline_style( 'apexadfo-global-inline', $css );
	}

	/**
	 * AJAX endpoint to securely save global settings from the custom sidebar dashboard
	 */
	public function ajax_save_global_settings() {
		// Check nonce and capabilities
		check_ajax_referer( 'apexadfo_global_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Unauthorized user context.', 'apex-addons-for-elementor' ) ] );
		}

		// Sanitize settings payload
		$raw_settings = isset( $_POST['settings'] ) ? map_deep( (array) wp_unslash( $_POST['settings'] ), 'sanitize_text_field' ) : [];
		$smooth_duration = isset( $raw_settings['smooth_duration'] ) ? (float) $raw_settings['smooth_duration'] : 0.8;
		$smooth_duration = max( 0.1, min( 3.0, $smooth_duration ) );

		$sanitized_settings = [
			'scrollbar_styling' => sanitize_text_field( $raw_settings['scrollbar_styling'] ?? 'no' ),
			'scrollbar_width'   => sanitize_text_field( $raw_settings['scrollbar_width'] ?? '10' ),
			'scrollbar_bg'      => sanitize_text_field( $raw_settings['scrollbar_bg'] ?? '#1e1e1e' ),
			'scrollbar_thumb'   => sanitize_text_field( $raw_settings['scrollbar_thumb'] ?? '#a855f7' ),
			'smooth_scroll'     => sanitize_text_field( $raw_settings['smooth_scroll'] ?? 'no' ),
			'smooth_duration'   => (string) $smooth_duration,
			'cursor_style'      => sanitize_text_field( $raw_settings['cursor_style'] ?? 'none' ),
			'cursor_color'      => sanitize_text_field( $raw_settings['cursor_color'] ?? '#a855f7' ),
		];

		// Save options
		update_option( 'apexadfo_global_settings', $sanitized_settings );

		wp_send_json_success( [ 'message' => esc_html__( 'Settings successfully synchronized.', 'apex-addons-for-elementor' ) ] );
	}

	/**
	 * Register controls for Global Magnetic Cursor Attraction
	 */
	public function register_magnetic_controls( $element, $section_id ) {
		// Target the advanced tab style/layout section for widgets and containers
		if ( 'section_custom_css' !== $section_id && '_section_style' !== $section_id && 'section_layout' !== $section_id ) {
			return;
		}

		// Prevent duplicate registration on the same element
		if ( ! empty( $element->get_controls( 'eas_magnetic_enable' ) ) ) {
			return;
		}

		$element->start_controls_section(
			'section_eas_magnetic',
			[
				'label' => esc_html__( 'Apex Magnetic Attraction', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			]
		);

		$element->add_control(
			'eas_magnetic_enable',
			[
				'label'        => esc_html__( 'Enable Magnetic Attraction', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$element->add_control(
			'eas_magnetic_radius',
			[
				'label'      => esc_html__( 'Attraction Radius (px)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 20,
						'max'  => 400,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 80,
				],
				'condition'  => [
					'eas_magnetic_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_magnetic_strength',
			[
				'label'      => esc_html__( 'Attraction Strength', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0.05,
						'max'  => 0.80,
						'step' => 0.01,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 0.20,
				],
				'condition'  => [
					'eas_magnetic_enable' => 'yes',
				],
				'description'=> esc_html__( 'Higher values pull the element faster and make it stiffer. Lower values feel more loose and fluid.', 'apex-addons-for-elementor' ),
			]
		);

		$element->add_control(
			'eas_magnetic_text',
			[
				'label'        => esc_html__( 'Multi-Layer Pull (Pull Text/Icon)', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'condition'    => [
					'eas_magnetic_enable' => 'yes',
				],
				'description'  => esc_html__( 'Pulls the inner text/icons slightly further to create a depth parallax effect.', 'apex-addons-for-elementor' ),
			]
		);

		$element->add_control(
			'eas_magnetic_mobile',
			[
				'label'        => esc_html__( 'Disable on Mobile', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'eas_magnetic_enable' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Inject Magnetic config attributes in frontend rendering
	 */
	public function before_element_magnetic_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( ! empty( $settings['eas_magnetic_enable'] ) && 'yes' === $settings['eas_magnetic_enable'] ) {
			wp_enqueue_script( 'apexadfo-magnetic-js' );

			$magnetic_config = [
				'radius'   => floatval( $settings['eas_magnetic_radius']['size'] ?? 80 ),
				'strength' => floatval( $settings['eas_magnetic_strength']['size'] ?? 0.2 ),
				'pullText' => ( ! empty( $settings['eas_magnetic_text'] ) && 'yes' === $settings['eas_magnetic_text'] ) ? 'yes' : 'no',
				'mobile'   => ( ! empty( $settings['eas_magnetic_mobile'] ) && 'yes' === $settings['eas_magnetic_mobile'] ) ? 'yes' : 'no',
			];

			$element->add_render_attribute( '_wrapper', 'data-eas-magnetic-config', wp_json_encode( $magnetic_config ) );
			$element->add_render_attribute( '_wrapper', 'class', 'eas-magnetic-active' );
		}
	}

	/**
	 * Register Cinematic Background Slideshow controls
	 */
	public function register_slideshow_background_controls( $element, $section_id ) {
		$element_name = $element->get_name();
		if ( ! in_array( $element_name, [ 'section', 'column', 'container' ], true ) ) {
			return;
		}

		if ( 'section_background' !== $section_id && 'section_style' !== $section_id ) {
			return;
		}

		$element->start_controls_section(
			'section_eas_kb_slideshow',
			[
				'label' => esc_html__( 'Apex Background Slideshow', 'apex-addons-for-elementor' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$element->add_control(
			'eas_kb_slideshow_enable',
			[
				'label'        => esc_html__( 'Enable Slideshow', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$element->add_control(
			'eas_kb_slideshow_gallery',
			[
				'label'      => esc_html__( 'Add Images', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::GALLERY,
				'show_label' => false,
				'condition'  => [
					'eas_kb_slideshow_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_duration',
			[
				'label'      => esc_html__( 'Slide Duration (ms)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 2000,
						'max'  => 15000,
						'step' => 500,
					],
				],
				'default'    => [
					'size' => 5000,
				],
				'condition'  => [
					'eas_kb_slideshow_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_transition',
			[
				'label'      => esc_html__( 'Transition Duration (ms)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'range'      => [
					'px' => [
						'min'  => 500,
						'max'  => 5000,
						'step' => 100,
					],
				],
				'default'    => [
					'size' => 1500,
				],
				'condition'  => [
					'eas_kb_slideshow_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_zoom',
			[
				'label'     => esc_html__( 'Zoom Direction', 'apex-addons-for-elementor' ),
				'type'     => \Elementor\Controls_Manager::SELECT,
				'default'   => 'alternate',
				'options'   => [
					'in'        => esc_html__( 'Zoom In', 'apex-addons-for-elementor' ),
					'out'       => esc_html__( 'Zoom Out', 'apex-addons-for-elementor' ),
					'alternate' => esc_html__( 'Alternate', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'eas_kb_slideshow_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_dots',
			[
				'label'        => esc_html__( 'Show Side Navigation', 'apex-addons-for-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'apex-addons-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'apex-addons-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'eas_kb_slideshow_enable' => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_nav_style',
			[
				'label'     => esc_html__( 'Navigation Style', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'dots',
				'options'   => [
					'dots'    => esc_html__( 'Sleek Dots', 'apex-addons-for-elementor' ),
					'lines'   => esc_html__( 'Scaling Lines', 'apex-addons-for-elementor' ),
					'numbers' => esc_html__( 'Stacked Numbers', 'apex-addons-for-elementor' ),
					'ring'    => esc_html__( 'Ring and Dot', 'apex-addons-for-elementor' ),
				],
				'condition' => [
					'eas_kb_slideshow_enable' => 'yes',
					'eas_kb_slideshow_dots'   => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_dots_color',
			[
				'label'     => esc_html__( 'Active Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .eas-kb-dots .eas-kb-dot.eas-kb-dot-active' => 'background-color: {{VALUE}}; box-shadow: 0 0 8px {{VALUE}}; color: {{VALUE}}; border-color: {{VALUE}};',
				],
				'condition' => [
					'eas_kb_slideshow_enable' => 'yes',
					'eas_kb_slideshow_dots'   => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_dots_bg',
			[
				'label'     => esc_html__( 'Inactive Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.3)',
				'selectors' => [
					'{{WRAPPER}} .eas-kb-dots .eas-kb-dot:not(.eas-kb-dot-active)' => 'background-color: {{VALUE}}; color: {{VALUE}}; border-color: {{VALUE}};',
				],
				'condition' => [
					'eas_kb_slideshow_enable' => 'yes',
					'eas_kb_slideshow_dots'   => 'yes',
				],
			]
		);
		$element->add_control(
			'eas_kb_slideshow_dots_size',
			[
				'label'      => esc_html__( 'Navigation Item Size', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 4,
						'max'  => 30,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 8,
				],
				'selectors' => [
					'{{WRAPPER}} .eas-kb-dots' => '--eas-kb-dot-size: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'eas_kb_slideshow_enable' => 'yes',
					'eas_kb_slideshow_dots'   => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_dots_gap',
			[
				'label'      => esc_html__( 'Navigation Spacing (Gap)', 'apex-addons-for-elementor' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 4,
						'max'  => 40,
						'step' => 1,
					],
				],
				'default'    => [
					'size' => 12,
				],
				'selectors' => [
					'{{WRAPPER}} .eas-kb-dots' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'eas_kb_slideshow_enable' => 'yes',
					'eas_kb_slideshow_dots'   => 'yes',
				],
			]
		);

		$element->add_control(
			'eas_kb_slideshow_overlay_color',
			[
				'label'     => esc_html__( 'Background Overlay Color', 'apex-addons-for-elementor' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => 'rgba(0,0,0,0.25)',
				'selectors' => [
					'{{WRAPPER}} .eas-kb-slideshow-overlay' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'eas_kb_slideshow_enable' => 'yes',
				],
			]
		);

		$element->end_controls_section();
	}

	/**
	 * Render Cinematic Background Slideshow frontend attributes and assets
	 */
	public function before_element_slideshow_render( $element ) {
		$settings = $element->get_settings_for_display();

		if ( ! empty( $settings['eas_kb_slideshow_enable'] ) && 'yes' === $settings['eas_kb_slideshow_enable'] ) {
			$gallery = $settings['eas_kb_slideshow_gallery'] ?? [];

			if ( ! empty( $gallery ) ) {
				wp_enqueue_script( 'apexadfo-kenburns-js' );
				wp_enqueue_style( 'apexadfo-kenburns-css' );

				// Gather all image URLs
				$image_urls = [];
				foreach ( $gallery as $img ) {
					if ( ! empty( $img['url'] ) ) {
						$image_urls[] = esc_url( $img['url'] );
					}
				}

				// Only run slide setup if images exist
				if ( ! empty( $image_urls ) ) {
					$slideshow_config = [
						'duration'   => intval( $settings['eas_kb_slideshow_duration']['size'] ?? 5000 ),
						'transition' => intval( $settings['eas_kb_slideshow_transition']['size'] ?? 1500 ),
						'zoom'       => $settings['eas_kb_slideshow_zoom'] ?? 'alternate',
						'dots'       => ( ! empty( $settings['eas_kb_slideshow_dots'] ) && 'yes' === $settings['eas_kb_slideshow_dots'] ) ? 'yes' : 'no',
						'navStyle'   => $settings['eas_kb_slideshow_nav_style'] ?? 'dots',
						'images'     => $image_urls,
					];

					$element->add_render_attribute( '_wrapper', 'data-eas-kb-config', wp_json_encode( $slideshow_config ) );
					$element->add_render_attribute( '_wrapper', 'class', 'eas-has-kb-slideshow' );
				}
			}
		}
	}

	/**
	 * Register Custom Post Type for Theme Builder Templates
	 */
	public function register_template_cpt() {
		$labels = [
			'name'               => esc_html__( 'Apex Templates', 'apex-addons-for-elementor' ),
			'singular_name'      => esc_html__( 'Apex Template', 'apex-addons-for-elementor' ),
			'menu_name'          => esc_html__( 'Apex Templates', 'apex-addons-for-elementor' ),
			'add_new'            => esc_html__( 'Add New', 'apex-addons-for-elementor' ),
			'add_new_item'       => esc_html__( 'Add New Template', 'apex-addons-for-elementor' ),
			'edit_item'          => esc_html__( 'Edit Template', 'apex-addons-for-elementor' ),
			'new_item'           => esc_html__( 'New Template', 'apex-addons-for-elementor' ),
			'view_item'          => esc_html__( 'View Template', 'apex-addons-for-elementor' ),
			'search_items'       => esc_html__( 'Search Templates', 'apex-addons-for-elementor' ),
			'not_found'          => esc_html__( 'No templates found', 'apex-addons-for-elementor' ),
			'not_found_in_trash' => esc_html__( 'No templates found in trash', 'apex-addons-for-elementor' ),
		];

		$args = [
			'labels'              => $labels,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false, // Managed strictly via custom Theme Builder dashboard page
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => [ 'title', 'editor', 'elementor' ],
			'has_archive'         => false,
			'rewrite'             => false,
		];

		register_post_type( 'apexadfo_template', $args );
	}

	/**
	 * AJAX Callback: Create Theme Builder Template post
	 */
	public function ajax_create_template() {
		check_ajax_referer( 'apexadfo_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Insufficient permissions.', 'apex-addons-for-elementor' ) ] );
		}

		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$type  = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'header';
		$allowed_types = [ 'header', 'footer', 'single_post', 'single_page', 'archive', 'not_found_404', 'product_single', 'product_archive', 'preloader', 'popup' ];
		if ( ! in_array( $type, $allowed_types, true ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid template type.', 'apex-addons-for-elementor' ) ], 400 );
		}

		if ( empty( $title ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Template title is required.', 'apex-addons-for-elementor' ) ] );
		}

		// Insert post
		$post_id = wp_insert_post( [
			'post_title'  => $title,
			'post_type'   => 'apexadfo_template',
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
		}

		// Save meta config
		update_post_meta( $post_id, '_apexadfo_template_type', $type );
		update_post_meta( $post_id, '_apexadfo_template_priority', 10 );
		$this->sync_elementor_template_type_meta( $post_id );
		if ( 'popup' === $type ) {
			update_post_meta( $post_id, '_apexadfo_popup_settings', $this->get_default_popup_settings() );
		}
		update_post_meta( $post_id, '_apexadfo_template_conditions', [
			[
				'type'     => 'include',
				'name'     => 'general',
				'sub_name' => '',
				'sub_id'   => '',
			],
		] );
		
		// Set Elementor edit mode active
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );

		// Return editor redirect URL
		$editor_url = admin_url( 'post.php?post=' . $post_id . '&action=elementor' );

		wp_send_json_success( [ 'redirect' => $editor_url ] );
	}

	/**
	 * AJAX: Save template conditions
	 */
	public function ajax_save_conditions() {
		if ( ! check_ajax_referer( 'apexadfo_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Forbidden' ] );
		}

		$template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( $_POST['template_id'] ) ) : 0;

		if ( ! isset( $_POST['conditions_data'] ) || empty( $_POST['conditions_data'] ) ) {
			$conditions = [];
		} else {
			$conditions_raw = sanitize_textarea_field( wp_unslash( $_POST['conditions_data'] ) );
			$conditions = json_decode( $conditions_raw, true );
			if ( ! is_array( $conditions ) ) {
				wp_send_json_error( [ 'message' => 'Invalid conditions JSON', 'error' => json_last_error_msg() ] );
			}
		}

		if ( $template_id <= 0 || 'apexadfo_template' !== get_post_type( $template_id ) || ! current_user_can( 'edit_post', $template_id ) ) {
			wp_send_json_error( [ 'message' => 'Invalid template ID' ] );
		}

		// Sanitize each condition
		$sanitized = [];
		foreach ( $conditions as $cond ) {
			$sanitized[] = [
				'type'     => in_array( $cond['type'] ?? '', [ 'include', 'exclude' ] ) ? $cond['type'] : 'include',
				'name'     => sanitize_text_field( $cond['name'] ?? 'general' ),
				'sub_name' => sanitize_text_field( $cond['sub_name'] ?? '' ),
				'sub_id'   => sanitize_text_field( $cond['sub_id'] ?? '' ),
			];
		}

		$priority = isset( $_POST['priority'] ) ? min( 1000, absint( wp_unslash( $_POST['priority'] ) ) ) : 10;
		update_post_meta( $template_id, '_apexadfo_template_conditions', $sanitized );
		update_post_meta( $template_id, '_apexadfo_template_priority', $priority );

		$response = [
			'conditions' => $sanitized,
			'priority'   => $priority,
		];

		if ( 'popup' === get_post_meta( $template_id, '_apexadfo_template_type', true ) ) {
			$popup_settings = $this->get_default_popup_settings();
			if ( isset( $_POST['popup_settings_data'] ) ) {
				$decoded_settings = json_decode( sanitize_textarea_field( wp_unslash( $_POST['popup_settings_data'] ) ), true );
				if ( is_array( $decoded_settings ) ) {
					$popup_settings = $this->sanitize_popup_settings( $decoded_settings );
				}
			}
			update_post_meta( $template_id, '_apexadfo_popup_settings', $popup_settings );
			$response['popup_settings'] = $popup_settings;
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX: Get template conditions
	 */
	public function ajax_get_conditions() {
		if ( ! check_ajax_referer( 'apexadfo_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Forbidden' ] );
		}

		$template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( $_POST['template_id'] ) ) : 0;
		if ( $template_id <= 0 || 'apexadfo_template' !== get_post_type( $template_id ) || ! current_user_can( 'edit_post', $template_id ) ) {
			wp_send_json_error( [ 'message' => 'Invalid template ID' ] );
		}

		$has_saved = metadata_exists( 'post', $template_id, '_apexadfo_template_conditions' );
		if ( $has_saved ) {
			$conditions = get_post_meta( $template_id, '_apexadfo_template_conditions', true );
			if ( ! is_array( $conditions ) ) {
				$conditions = [];
			}
		} else {
			$conditions = [
				[
					'type'     => 'include',
					'name'     => 'general',
					'sub_name' => '',
					'sub_id'   => '',
				],
			];
		}

		$priority = absint( get_post_meta( $template_id, '_apexadfo_template_priority', true ) );
		if ( ! metadata_exists( 'post', $template_id, '_apexadfo_template_priority' ) ) {
			$priority = 10;
		}

		$response = [
			'conditions' => $conditions,
			'priority'   => $priority,
		];
		if ( 'popup' === get_post_meta( $template_id, '_apexadfo_template_type', true ) ) {
			$settings = get_post_meta( $template_id, '_apexadfo_popup_settings', true );
			$response['popup_settings'] = $this->sanitize_popup_settings( is_array( $settings ) ? $settings : [] );
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX: Get sub_name options for a given condition name
	 */
	public function ajax_get_sub_name_options() {
		if ( ! check_ajax_referer( 'apexadfo_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Forbidden' ] );
		}

		$name = isset( $_POST['name'] ) ? sanitize_key( wp_unslash( $_POST['name'] ) ) : 'general';
		$options = Conditions_Engine::get_sub_name_options_for( $name );

		wp_send_json_success( [ 'options' => $options ] );
	}

	/**
	 * AJAX: Get sub_id options for a given sub_name
	 */
	public function ajax_get_sub_id_options() {
		if ( ! check_ajax_referer( 'apexadfo_admin_nonce', 'nonce', false ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Forbidden' ] );
		}

		$sub_name = isset( $_POST['sub_name'] ) ? sanitize_key( wp_unslash( $_POST['sub_name'] ) ) : '';
		$options = Conditions_Engine::get_sub_id_options( $sub_name );

		wp_send_json_success( [ 'options' => $options ] );
	}

	/**
	 * Render Theme Builder admin page UI
	 */
	public function render_theme_builder_page() {
		require_once __DIR__ . '/class-apex-conditions-engine.php';
		$engine = Conditions_Engine::class;

		// Fetch existing templates CPT posts
		$query = new \WP_Query( [
			'post_type'      => 'apexadfo_template',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		] );

		$templates_by_type = [
			'header'      => [],
			'footer'      => [],
			'single_post' => [],
			'single_page' => [],
			'archive'     => [],
			'not_found_404' => [],
			'woocommerce' => [],
			'preloader'   => [],
			'popup'       => [],
		];

		if ( ! self::THEME_BUILDER_POPUPS_VISIBLE ) {
			unset( $templates_by_type['popup'] );
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				$type = get_post_meta( $post_id, '_apexadfo_template_type', true ) ?: 'header';
				
				// Group woocommerce product details under woocommerce category
				$group = $type;
				if ( in_array( $type, [ 'product_single', 'product_archive' ] ) ) {
					$group = 'woocommerce';
				}
				if ( 'single' === $type ) {
					$group = 'single_post'; // Legacy fallback
				}

				if ( isset( $templates_by_type[ $group ] ) ) {
					$conditions = get_post_meta( $post_id, '_apexadfo_template_conditions', true );
					if ( ! is_array( $conditions ) ) {
						$conditions = [
							[ 'type' => 'include', 'name' => 'general', 'sub_name' => '', 'sub_id' => '' ],
						];
						$cond_label = esc_html__( 'Entire Site', 'apex-addons-for-elementor' );
					} elseif ( empty( $conditions ) ) {
						$cond_label = esc_html__( 'No Conditions (Disabled)', 'apex-addons-for-elementor' );
					} else {
						$cond_label = Conditions_Engine::get_sub_name_options_for( $conditions[0]['name'] )[ $conditions[0]['sub_name'] ] ?? esc_html__( 'Entire Site', 'apex-addons-for-elementor' );
					}
					$templates_by_type[ $group ][] = [
						'id'         => $post_id,
						'title'      => get_the_title(),
						'type'       => $type,
						'conditions' => $conditions,
						'cond_label' => $cond_label,
						'edit_url'   => admin_url( 'post.php?post=' . $post_id . '&action=elementor' ),
						'priority'   => metadata_exists( 'post', $post_id, '_apexadfo_template_priority' ) ? absint( get_post_meta( $post_id, '_apexadfo_template_priority', true ) ) : 10,
					];
				}
			}
			wp_reset_postdata();
		}

		$logo_url = plugins_url( 'assets/images/apex-addons-logo.png', __FILE__ );
		$nonce    = wp_create_nonce( 'apexadfo_admin_nonce' );
		?>
		<div class="eas-admin-wrap eas-tb-wrap">
			<!-- Header -->
			<header class="eas-admin-header">
				<div class="eas-admin-logo-title">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="Apex Logo" class="eas-admin-logo-img" />
					<div>
						<h1><?php esc_html_e( 'Apex Theme Builder', 'apex-addons-for-elementor' ); ?></h1>
						<p class="eas-admin-subtitle"><?php esc_html_e( 'Create and manage headers, footers, singular layouts, archives, and 404 pages.', 'apex-addons-for-elementor' ); ?></p>
					</div>
				</div>
				<button class="eas-admin-btn eas-tb-new-btn" id="eas-tb-trigger-modal">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php esc_html_e( 'Add New Template', 'apex-addons-for-elementor' ); ?>
				</button>
			</header>

			<!-- Tabs Navigation -->
			<div class="eas-admin-tabs-nav">
				<button class="eas-admin-tab-trigger active" data-tab="tab-header"><?php esc_html_e( 'Headers', 'apex-addons-for-elementor' ); ?></button>
				<button class="eas-admin-tab-trigger" data-tab="tab-footer"><?php esc_html_e( 'Footers', 'apex-addons-for-elementor' ); ?></button>
				<button class="eas-admin-tab-trigger" data-tab="tab-single_post"><?php esc_html_e( 'Single Post', 'apex-addons-for-elementor' ); ?></button>
				<button class="eas-admin-tab-trigger" data-tab="tab-single_page"><?php esc_html_e( 'Single Page', 'apex-addons-for-elementor' ); ?></button>
				<button class="eas-admin-tab-trigger" data-tab="tab-archive"><?php esc_html_e( 'Archives', 'apex-addons-for-elementor' ); ?></button>
				<button class="eas-admin-tab-trigger" data-tab="tab-not_found_404"><?php esc_html_e( '404 Page', 'apex-addons-for-elementor' ); ?></button>
				<button class="eas-admin-tab-trigger" data-tab="tab-woocommerce"><?php esc_html_e( 'WooCommerce', 'apex-addons-for-elementor' ); ?></button>
				<button class="eas-admin-tab-trigger" data-tab="tab-preloader"><?php esc_html_e( 'Preloaders', 'apex-addons-for-elementor' ); ?></button>
				<?php if ( self::THEME_BUILDER_POPUPS_VISIBLE ) : ?>
					<button class="eas-admin-tab-trigger" data-tab="tab-popup"><?php esc_html_e( 'Popups', 'apex-addons-for-elementor' ); ?></button>
				<?php endif; ?>
			</div>

			<!-- Tabs Content -->
			<div class="eas-admin-tabs-content">
				<?php foreach ( $templates_by_type as $type_key => $list ) : ?>
					<div class="eas-admin-tab-panel <?php echo 'header' === $type_key ? 'active' : ''; ?>" id="tab-<?php echo esc_attr( $type_key ); ?>">
						<?php if ( empty( $list ) ) : ?>
							<!-- Empty State Layout -->
							<div class="eas-tb-empty-state">
								<span class="dashicons dashicons-layout eas-tb-empty-icon"></span>
								<h3><?php /* translators: %s: template type. */ printf( esc_html__( 'No %s Templates Found', 'apex-addons-for-elementor' ), esc_html( ucwords( str_replace( '_', ' ', $type_key ) ) ) ); ?></h3>
								<p><?php esc_html_e( 'Design a customizable template and apply display conditions globally across your website.', 'apex-addons-for-elementor' ); ?></p>
								<button class="eas-admin-btn eas-tb-create-first-btn" data-type="<?php echo esc_attr( $type_key ); ?>">
									<?php esc_html_e( 'Create Your First Template', 'apex-addons-for-elementor' ); ?>
								</button>
							</div>
						<?php else : ?>
							<!-- Grid Cards Layout -->
							<div class="eas-tb-grid">
								<?php foreach ( $list as $item ) : ?>
									<?php
									$conditions   = $item['conditions'];
									$include_text = [];
									$exclude_text = [];
									foreach ( $conditions as $condition ) {
										$options = Conditions_Engine::get_sub_name_options_for( $condition['name'] );
										$label = ! empty( $condition['sub_name'] ) ? ( $options[ $condition['sub_name'] ] ?? $condition['sub_name'] ) : esc_html__( 'Entire Site', 'apex-addons-for-elementor' );
										if ( 'exclude' === $condition['type'] ) {
											$exclude_text[] = $label;
										} else {
											$include_text[] = $label;
										}
									}
									$cond_preview = '';
									if ( ! empty( $include_text ) ) {
										$cond_preview .= '<span class="eas-tb-cond-item">' . esc_html( implode( ' OR ', $include_text ) ) . '</span>';
									} elseif ( ! empty( $exclude_text ) ) {
										$cond_preview .= '<span class="eas-tb-cond-item">' . esc_html__( 'Entire Site', 'apex-addons-for-elementor' ) . '</span>';
									} else {
										$cond_preview .= '<span class="eas-tb-cond-item">' . esc_html__( 'No Conditions (Disabled)', 'apex-addons-for-elementor' ) . '</span>';
									}
									if ( ! empty( $exclude_text ) ) {
										$cond_preview .= '<span class="eas-tb-cond-and">' . esc_html__( 'EXCEPT', 'apex-addons-for-elementor' ) . '</span><span class="eas-tb-cond-item">' . esc_html( implode( ' OR ', $exclude_text ) ) . '</span>';
									}
									?>
									<div class="eas-tb-card">
										<div class="eas-tb-card-header-top">
											<h4><?php echo esc_html( $item['title'] ); ?></h4>
											<span class="eas-tb-meta-badge"><?php echo esc_html( ucwords( str_replace( '_', ' ', $item['type'] ) ) ); ?> · <?php /* translators: %d: template priority. */ printf( esc_html__( 'Priority %d', 'apex-addons-for-elementor' ), absint( $item['priority'] ) ); ?></span>
										</div>
										<div class="eas-tb-cond-preview">
											<?php echo wp_kses_post( $cond_preview ); ?>
										</div>
										<div class="eas-tb-card-actions">
											<a href="<?php echo esc_url( $item['edit_url'] ); ?>" class="eas-tb-action-btn edit-btn">
												<span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Edit with Elementor', 'apex-addons-for-elementor' ); ?>
											</a>
											<div class="eas-tb-card-actions-right">
												<button class="eas-tb-action-btn cond-btn eas-tb-edit-conditions" data-template-id="<?php echo intval( $item['id'] ); ?>" data-template-type="<?php echo esc_attr( $item['type'] ); ?>">
													<span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'Conditions', 'apex-addons-for-elementor' ); ?>
												</button>
												<a href="<?php echo esc_url( get_delete_post_link( $item['id'], '', true ) ); ?>" class="eas-tb-action-btn delete-btn" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this template?', 'apex-addons-for-elementor' ); ?>');">
													<span class="dashicons dashicons-trash"></span>
												</a>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( 'preloader' === $type_key ) : ?>
							<div style="margin-top: 40px; border-top: 2px solid #e2e8f0; padding-top: 30px;">
								<h3 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 20px;"><?php esc_html_e( 'Basic Preloader Settings', 'apex-addons-for-elementor' ); ?></h3>
								<?php $this->render_free_preloader_settings_panel(); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Template Creation Modal Popup -->
		<div class="eas-tb-modal" id="eas-tb-creation-modal">
			<div class="eas-tb-modal-backdrop"></div>
			<div class="eas-tb-modal-content">
				<h3><?php esc_html_e( 'Create New Template', 'apex-addons-for-elementor' ); ?></h3>
				<form id="eas-tb-create-form" method="POST">
					<div class="eas-tb-form-field">
					<label for="eas-tb-field-type"><?php esc_html_e( 'Template Type', 'apex-addons-for-elementor' ); ?></label>
						<select id="eas-tb-field-type" name="type">
							<option value="header"><?php esc_html_e( 'Header', 'apex-addons-for-elementor' ); ?></option>
							<option value="footer"><?php esc_html_e( 'Footer', 'apex-addons-for-elementor' ); ?></option>
							<option value="single_post"><?php esc_html_e( 'Single Post', 'apex-addons-for-elementor' ); ?></option>
							<option value="single_page"><?php esc_html_e( 'Single Page', 'apex-addons-for-elementor' ); ?></option>
							<option value="archive"><?php esc_html_e( 'Archive Page', 'apex-addons-for-elementor' ); ?></option>
							<option value="not_found_404"><?php esc_html_e( '404 Page', 'apex-addons-for-elementor' ); ?></option>
							<?php if ( class_exists( 'WooCommerce' ) ) : ?>
							<option value="product_single"><?php esc_html_e( 'WooCommerce Product Single', 'apex-addons-for-elementor' ); ?></option>
							<option value="product_archive"><?php esc_html_e( 'WooCommerce Product Archive', 'apex-addons-for-elementor' ); ?></option>
							<?php endif; ?>
							<option value="preloader"><?php esc_html_e( 'Preloader Screen', 'apex-addons-for-elementor' ); ?></option>
							<?php if ( self::THEME_BUILDER_POPUPS_VISIBLE ) : ?>
								<option value="popup"><?php esc_html_e( 'Popup Overlay', 'apex-addons-for-elementor' ); ?></option>
							<?php endif; ?>
						</select>
					</div>

					<div class="eas-tb-form-field">
						<label for="eas-tb-field-title"><?php esc_html_e( 'Template Name', 'apex-addons-for-elementor' ); ?></label>
						<input type="text" id="eas-tb-field-title" name="title" placeholder="<?php esc_attr_e( 'e.g. Primary Header Layout', 'apex-addons-for-elementor' ); ?>" required />
					</div>

					<div class="eas-tb-form-actions">
						<button type="button" class="eas-tb-btn-cancel" id="eas-tb-cancel-modal"><?php esc_html_e( 'Cancel', 'apex-addons-for-elementor' ); ?></button>
						<button type="submit" class="eas-tb-btn-submit" id="eas-tb-submit-form"><?php esc_html_e( 'Create & Edit', 'apex-addons-for-elementor' ); ?></button>
					</div>
				</form>
				<div class="eas-tb-modal-loading" style="display: none;">
					<div class="eas-tb-spinner"></div>
					<p><?php esc_html_e( 'Launching Elementor Editor...', 'apex-addons-for-elementor' ); ?></p>
				</div>
			</div>
		</div>

		<!-- Conditions Editor Modal -->
		<div class="eas-tb-modal" id="eas-tb-conditions-modal">
			<div class="eas-tb-modal-backdrop"></div>
			<div class="eas-tb-modal-content eas-tb-cond-modal-content">
				<h3><?php esc_html_e( 'Display Conditions', 'apex-addons-for-elementor' ); ?></h3>
				<p class="eas-tb-cond-desc"><?php esc_html_e( 'Set the conditions that determine where your Template is used throughout your site.', 'apex-addons-for-elementor' ); ?></p>
				<input type="hidden" id="eas-cond-template-id" value="0" />
				<input type="hidden" id="eas-cond-template-type" value="" />
				<div class="eas-tb-form-field">
					<label for="eas-cond-priority"><?php esc_html_e( 'Template Priority', 'apex-addons-for-elementor' ); ?></label>
					<input type="number" id="eas-cond-priority" min="0" max="1000" value="10" />
					<p class="description"><?php esc_html_e( 'When multiple templates match, the highest priority is used.', 'apex-addons-for-elementor' ); ?></p>
				</div>
				<div id="eas-tb-cond-repeater"></div>
				<div class="eas-tb-cond-add-row">
					<button type="button" class="eas-tb-cond-add-btn" id="eas-tb-add-condition"><?php esc_html_e( 'Add Condition', 'apex-addons-for-elementor' ); ?></button>
				</div>
				<div id="eas-tb-popup-settings" hidden>
					<div class="eas-popup-settings-section">
						<h4><?php esc_html_e( 'Layout & Presentation', 'apex-addons-for-elementor' ); ?></h4>
						<div class="eas-popup-settings-grid">
							<div class="eas-tb-form-field"><label for="eas-popup-width"><?php esc_html_e( 'Width', 'apex-addons-for-elementor' ); ?></label><div class="eas-popup-inline"><input type="number" id="eas-popup-width" min="1" max="2000" value="640" /><select id="eas-popup-width-unit"><option value="px">px</option><option value="percent">%</option><option value="vw">vw</option></select></div></div>
							<div class="eas-tb-form-field"><label for="eas-popup-height-mode"><?php esc_html_e( 'Height', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-height-mode"><option value="fit"><?php esc_html_e( 'Fit To Content', 'apex-addons-for-elementor' ); ?></option><option value="custom"><?php esc_html_e( 'Custom', 'apex-addons-for-elementor' ); ?></option><option value="fullscreen"><?php esc_html_e( 'Full Screen', 'apex-addons-for-elementor' ); ?></option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-height"><?php esc_html_e( 'Custom Height', 'apex-addons-for-elementor' ); ?></label><div class="eas-popup-inline"><input type="number" id="eas-popup-height" min="1" max="2000" value="500" /><select id="eas-popup-height-unit"><option value="px">px</option><option value="vh">vh</option></select></div></div>
							<div class="eas-tb-form-field"><label for="eas-popup-max-height"><?php esc_html_e( 'Maximum Height (vh)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-max-height" min="10" max="100" value="90" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-horizontal"><?php esc_html_e( 'Horizontal Position', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-horizontal"><option value="left"><?php esc_html_e( 'Left', 'apex-addons-for-elementor' ); ?></option><option value="center" selected><?php esc_html_e( 'Center', 'apex-addons-for-elementor' ); ?></option><option value="right"><?php esc_html_e( 'Right', 'apex-addons-for-elementor' ); ?></option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-vertical"><?php esc_html_e( 'Vertical Position', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-vertical"><option value="top"><?php esc_html_e( 'Top', 'apex-addons-for-elementor' ); ?></option><option value="center" selected><?php esc_html_e( 'Center', 'apex-addons-for-elementor' ); ?></option><option value="bottom"><?php esc_html_e( 'Bottom', 'apex-addons-for-elementor' ); ?></option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-offset-x"><?php esc_html_e( 'Horizontal Offset (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-offset-x" min="-500" max="500" value="0" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-offset-y"><?php esc_html_e( 'Vertical Offset (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-offset-y" min="-500" max="500" value="0" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-entrance"><?php esc_html_e( 'Entrance Animation', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-entrance"><option value="fade">Fade</option><option value="zoom">Zoom</option><option value="slide_up">Slide Up</option><option value="slide_down">Slide Down</option><option value="slide_left">Slide Left</option><option value="slide_right">Slide Right</option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-exit"><?php esc_html_e( 'Exit Animation', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-exit"><option value="fade">Fade</option><option value="zoom">Zoom</option><option value="slide_up">Slide Up</option><option value="slide_down">Slide Down</option><option value="slide_left">Slide Left</option><option value="slide_right">Slide Right</option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-animation-duration"><?php esc_html_e( 'Animation Duration (ms)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-animation-duration" min="0" max="5000" value="400" /></div>
						</div>
						<div class="eas-popup-checks"><label><input type="checkbox" id="eas-popup-show-overlay" checked /> <?php esc_html_e( 'Show overlay', 'apex-addons-for-elementor' ); ?></label><label><input type="checkbox" id="eas-popup-prevent-scroll" checked /> <?php esc_html_e( 'Prevent page scrolling', 'apex-addons-for-elementor' ); ?></label><label><input type="checkbox" id="eas-popup-show-close" checked /> <?php esc_html_e( 'Show close button', 'apex-addons-for-elementor' ); ?></label><label><input type="checkbox" id="eas-popup-overlay-close" checked /> <?php esc_html_e( 'Close on overlay click', 'apex-addons-for-elementor' ); ?></label><label><input type="checkbox" id="eas-popup-escape-close" checked /> <?php esc_html_e( 'Close with Escape', 'apex-addons-for-elementor' ); ?></label></div>
					</div>
					<div class="eas-popup-settings-section">
						<h4><?php esc_html_e( 'Popup Style', 'apex-addons-for-elementor' ); ?></h4>
						<div class="eas-popup-settings-grid">
							<div class="eas-tb-form-field"><label for="eas-popup-bg"><?php esc_html_e( 'Background', 'apex-addons-for-elementor' ); ?></label><input type="color" id="eas-popup-bg" value="#ffffff" /></div><div class="eas-tb-form-field"><label for="eas-popup-padding"><?php esc_html_e( 'Inner Padding (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-padding" min="0" max="300" value="0" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-radius"><?php esc_html_e( 'Border Radius (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-radius" min="0" max="300" value="0" /></div><div class="eas-tb-form-field"><label for="eas-popup-border-width"><?php esc_html_e( 'Border Width (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-border-width" min="0" max="30" value="0" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-border-color"><?php esc_html_e( 'Border Color', 'apex-addons-for-elementor' ); ?></label><input type="color" id="eas-popup-border-color" value="#e5e7eb" /></div><div class="eas-tb-form-field"><label for="eas-popup-shadow-blur"><?php esc_html_e( 'Shadow Blur (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-shadow-blur" min="0" max="200" value="80" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-shadow-opacity"><?php esc_html_e( 'Shadow Opacity (%)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-shadow-opacity" min="0" max="100" value="30" /></div><div class="eas-tb-form-field"><label for="eas-popup-overlay-color"><?php esc_html_e( 'Overlay Color', 'apex-addons-for-elementor' ); ?></label><input type="color" id="eas-popup-overlay-color" value="#0f172a" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-overlay-opacity"><?php esc_html_e( 'Overlay Opacity (%)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-overlay-opacity" min="0" max="100" value="68" /></div><div class="eas-tb-form-field"><label for="eas-popup-overlay-blur"><?php esc_html_e( 'Overlay Blur (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-overlay-blur" min="0" max="50" value="0" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-close-bg"><?php esc_html_e( 'Close Background', 'apex-addons-for-elementor' ); ?></label><input type="color" id="eas-popup-close-bg" value="#111827" /></div><div class="eas-tb-form-field"><label for="eas-popup-close-color"><?php esc_html_e( 'Close Icon Color', 'apex-addons-for-elementor' ); ?></label><input type="color" id="eas-popup-close-color" value="#ffffff" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-close-size"><?php esc_html_e( 'Close Button Size (px)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-close-size" min="24" max="100" value="42" /></div>
						</div>
					</div>
					<div class="eas-popup-settings-section">
						<h4><?php esc_html_e( 'Triggers', 'apex-addons-for-elementor' ); ?></h4>
						<p class="description"><?php esc_html_e( 'Enable one or more triggers. Popups can also be opened manually with the apexadfo:popup:open event.', 'apex-addons-for-elementor' ); ?></p>
						<div class="eas-popup-trigger-list">
							<label><input type="checkbox" id="eas-popup-trigger-load" checked /> <?php esc_html_e( 'On page load', 'apex-addons-for-elementor' ); ?> <input type="number" id="eas-popup-delay" min="0" max="60000" value="500" /> ms</label>
							<label><input type="checkbox" id="eas-popup-trigger-scroll" /> <?php esc_html_e( 'After scrolling', 'apex-addons-for-elementor' ); ?> <input type="number" id="eas-popup-scroll" min="1" max="100" value="50" />%</label>
							<label><input type="checkbox" id="eas-popup-trigger-element" /> <?php esc_html_e( 'When element enters view', 'apex-addons-for-elementor' ); ?> <input type="text" id="eas-popup-element-selector" placeholder="#pricing" /></label>
							<label><input type="checkbox" id="eas-popup-trigger-click" /> <?php esc_html_e( 'On element click', 'apex-addons-for-elementor' ); ?> <input type="text" id="eas-popup-selector" placeholder=".open-contact-popup" /></label>
							<label><input type="checkbox" id="eas-popup-trigger-inactivity" /> <?php esc_html_e( 'After inactivity', 'apex-addons-for-elementor' ); ?> <input type="number" id="eas-popup-inactivity" min="1" max="3600" value="30" /> <?php esc_html_e( 'seconds', 'apex-addons-for-elementor' ); ?></label>
							<label><input type="checkbox" id="eas-popup-trigger-exit" /> <?php esc_html_e( 'On page exit intent', 'apex-addons-for-elementor' ); ?></label>
						</div>
					</div>
					<div class="eas-popup-settings-section">
						<h4><?php esc_html_e( 'Advanced Rules', 'apex-addons-for-elementor' ); ?></h4>
						<div class="eas-popup-settings-grid">
							<div class="eas-tb-form-field"><label for="eas-popup-frequency"><?php esc_html_e( 'Frequency', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-frequency"><option value="always"><?php esc_html_e( 'Every Match', 'apex-addons-for-elementor' ); ?></option><option value="session" selected><?php esc_html_e( 'Once Per Session', 'apex-addons-for-elementor' ); ?></option><option value="once"><?php esc_html_e( 'Once Per Browser', 'apex-addons-for-elementor' ); ?></option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-max-shows"><?php esc_html_e( 'Maximum Displays (0 = unlimited)', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-max-shows" min="0" max="10000" value="0" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-min-views"><?php esc_html_e( 'Show After Page Views', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-min-views" min="0" max="10000" value="0" /></div><div class="eas-tb-form-field"><label for="eas-popup-min-sessions"><?php esc_html_e( 'Show After Sessions', 'apex-addons-for-elementor' ); ?></label><input type="number" id="eas-popup-min-sessions" min="0" max="10000" value="0" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-user-state"><?php esc_html_e( 'Visitors', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-user-state"><option value="all"><?php esc_html_e( 'All Visitors', 'apex-addons-for-elementor' ); ?></option><option value="logged_in"><?php esc_html_e( 'Logged-in Only', 'apex-addons-for-elementor' ); ?></option><option value="logged_out"><?php esc_html_e( 'Logged-out Only', 'apex-addons-for-elementor' ); ?></option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-browser"><?php esc_html_e( 'Browser', 'apex-addons-for-elementor' ); ?></label><select id="eas-popup-browser"><option value="all"><?php esc_html_e( 'All Browsers', 'apex-addons-for-elementor' ); ?></option><option value="chrome">Chrome</option><option value="firefox">Firefox</option><option value="safari">Safari</option><option value="edge">Edge</option></select></div>
							<div class="eas-tb-form-field"><label for="eas-popup-url-contains"><?php esc_html_e( 'Current URL Contains', 'apex-addons-for-elementor' ); ?></label><input type="text" id="eas-popup-url-contains" /></div><div class="eas-tb-form-field"><label for="eas-popup-referrer-contains"><?php esc_html_e( 'Referrer URL Contains', 'apex-addons-for-elementor' ); ?></label><input type="text" id="eas-popup-referrer-contains" /></div>
							<div class="eas-tb-form-field"><label for="eas-popup-schedule-start"><?php esc_html_e( 'Schedule Start', 'apex-addons-for-elementor' ); ?></label><input type="datetime-local" id="eas-popup-schedule-start" /></div><div class="eas-tb-form-field"><label for="eas-popup-schedule-end"><?php esc_html_e( 'Schedule End', 'apex-addons-for-elementor' ); ?></label><input type="datetime-local" id="eas-popup-schedule-end" /></div>
						</div>
						<div class="eas-popup-checks"><strong><?php esc_html_e( 'Devices:', 'apex-addons-for-elementor' ); ?></strong><label><input type="checkbox" id="eas-popup-device-desktop" checked /> <?php esc_html_e( 'Desktop', 'apex-addons-for-elementor' ); ?></label><label><input type="checkbox" id="eas-popup-device-tablet" checked /> <?php esc_html_e( 'Tablet', 'apex-addons-for-elementor' ); ?></label><label><input type="checkbox" id="eas-popup-device-mobile" checked /> <?php esc_html_e( 'Mobile', 'apex-addons-for-elementor' ); ?></label></div>
					</div>
				</div>
				<div class="eas-tb-form-actions">
					<button type="button" class="eas-tb-btn-cancel" id="eas-tb-cancel-conditions"><?php esc_html_e( 'Cancel', 'apex-addons-for-elementor' ); ?></button>
					<button type="button" class="eas-tb-btn-submit" id="eas-tb-save-conditions"><?php esc_html_e( 'Save Conditions', 'apex-addons-for-elementor' ); ?></button>
				</div>
				<div class="eas-tb-cond-saving" style="display:none;text-align:center;padding:20px;">
					<div class="eas-tb-spinner"></div>
					<p><?php esc_html_e( 'Saving...', 'apex-addons-for-elementor' ); ?></p>
				</div>
			</div>
		</div>

		<?php ob_start(); ?>
		jQuery(document).ready(function($) {
			$('.eas-admin-tab-trigger').on('click', function() {
				$('.eas-admin-tab-trigger').removeClass('active');
				$(this).addClass('active');
				var targetTab = $(this).data('tab');
				$('.eas-admin-tab-panel').removeClass('active');
				$('#' + targetTab).addClass('active');
			});

			var $createModal = $('#eas-tb-creation-modal');
			$('#eas-tb-trigger-modal, .eas-tb-create-first-btn').on('click', function() {
				var defaultType = $(this).data('type') || 'header';
				if (defaultType === 'woocommerce') defaultType = 'product_single';
				$('#eas-tb-field-type').val(defaultType);
				$('#eas-tb-field-title').val('');
				$createModal.addClass('active');
			});
			$('#eas-tb-cancel-modal, #eas-tb-creation-modal .eas-tb-modal-backdrop').on('click', function() {
				$createModal.removeClass('active');
			});

			$('#eas-tb-create-form').on('submit', function(e) {
				e.preventDefault();
				var $form = $(this), $loading = $('.eas-tb-modal-loading');
				$form.hide(); $loading.show();
				$.post(ajaxurl, {
					action: 'apexadfo_create_template',
					nonce: '<?php echo esc_attr( $nonce ); ?>',
					title: $('#eas-tb-field-title').val(),
					type: $('#eas-tb-field-type').val(),
				}, function(response) {
					if (response.success && response.data.redirect) {
						window.location.href = response.data.redirect;
					} else {
						alert(response.data.message || 'Error.');
						$loading.hide(); $form.show();
					}
				}).fail(function() {
					alert('Connection error.');
					$loading.hide(); $form.show();
				});
			});

			// Logo Uploader inside Preloader Settings
			$('#eas-logo-upload-btn').on('click', function(e) {
				e.preventDefault();
				var imageFrame = wp.media({
					title: 'Choose Preloader Logo',
					multiple: false,
					library: { type: 'image' }
				}).open().on('select', function() {
					var attachment = imageFrame.state().get('selection').first().toJSON();
					$('#eas-logo-url-field').val(attachment.url);
				});
			});

			// Toggle loader size and logo size rows depending on selection
			$('#eas-preloader-type-select').on('change', function() {
				var val = $(this).val();
				if (val === 'pulse_logo') {
					$('.logo-upload-row, .logo-size-row').show();
					$('.loader-size-row').hide();
				} else if (val === 'percentage') {
					$('.logo-upload-row, .logo-size-row, .loader-size-row').hide();
				} else {
					$('.logo-upload-row, .logo-size-row').hide();
					$('.loader-size-row').show();
					if (val === 'bar') {
						$('.eas-loader-size-label').text('Loader Width (px)');
					} else {
						$('.eas-loader-size-label').text('Loader Size (px)');
					}
				}
			});

			// Basic Preloader form submission
			$('#eas-tb-preloader-form').on('submit', function(e) {
				e.preventDefault();
				var $form = $(this), $submit = $form.find('.eas-btn-submit');
				$submit.prop('disabled', true);
				$.post(ajaxurl, $form.serialize(), function(response) {
					$submit.prop('disabled', false);
					if (response.success) {
						var $success = $form.find('.eas-settings-save-success');
						$success.fadeIn().delay(2000).fadeOut();
					} else {
						alert(response.data.message || 'Error saving settings.');
					}
				}).fail(function() {
					$submit.prop('disabled', false);
					alert('Connection error.');
				});
			});

			// Conditions Modal
			var $condModal = $('#eas-tb-conditions-modal');
			var $condRepeater = $('#eas-tb-cond-repeater');
			var $condTemplateId = $('#eas-cond-template-id');

			$('.eas-tb-edit-conditions').on('click', function() {
				var templateId = $(this).data('template-id');
				var templateType = $(this).data('template-type') || 'header';
				$condTemplateId.val(templateId);
				$('#eas-cond-template-type').val(templateType);
				loadConditions(templateId);
				$condModal.addClass('active');
			});

			$('#eas-tb-cancel-conditions, #eas-tb-conditions-modal .eas-tb-modal-backdrop').on('click', function() {
				$condModal.removeClass('active');
			});

			$('#eas-tb-save-conditions').on('click', function() {
				var templateId = $condTemplateId.val();
				var conditions = collectConditions();
				$('.eas-tb-cond-saving').show();
				$('#eas-tb-save-conditions, #eas-tb-cancel-conditions').hide();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'apexadfo_save_conditions',
						nonce: '<?php echo esc_attr( $nonce ); ?>',
						template_id: templateId,
						conditions_data: JSON.stringify(conditions),
						priority: $('#eas-cond-priority').val(),
						popup_settings_data: JSON.stringify(collectPopupSettings()),
					},
					success: function(response) {
						if (response.success) {
							$condModal.removeClass('active');
							location.reload();
						} else {
							var errMsg = response.data && response.data.message ? response.data.message : 'Unknown error';
							if (response.data && response.data.raw_received) {
								errMsg += '\n\nRaw: ' + response.data.raw_received;
							}
							if (response.data && response.data.json_error) {
								errMsg += '\nJSON Error: ' + response.data.json_error;
							}
							alert('Error: ' + errMsg);
						}
						$('.eas-tb-cond-saving').hide();
						$('#eas-tb-save-conditions, #eas-tb-cancel-conditions').show();
					},
					error: function(jqXHR) {
						alert('Connection error. Status: ' + jqXHR.status + ' - ' + jqXHR.statusText + '\nResponse: ' + jqXHR.responseText);
						$('.eas-tb-cond-saving').hide();
						$('#eas-tb-save-conditions, #eas-tb-cancel-conditions').show();
					}
				});
			});

			function loadConditions(templateId) {
				$.post(ajaxurl, {
					action: 'apexadfo_get_conditions',
					nonce: '<?php echo esc_attr( $nonce ); ?>',
					template_id: templateId,
				}, function(response) {
					if (response.success) {
						renderConditions(response.data.conditions);
						$('#eas-cond-priority').val(response.data.priority || 0);
						var isPopup = $('#eas-cond-template-type').val() === 'popup';
						$('#eas-tb-popup-settings').prop('hidden', !isPopup);
						if (isPopup) populatePopupSettings(response.data.popup_settings || {});
					}
				});
			}

			function populatePopupSettings(settings) {
				var legacyTrigger = settings.trigger || 'load';
				function value(id, key, fallback) { $('#' + id).val(settings[key] == null ? fallback : settings[key]); }
				function checked(id, key, fallback) { $('#' + id).prop('checked', settings[key] == null ? fallback : settings[key] === 'yes'); }
				value('eas-popup-width', 'width_value', 640); value('eas-popup-width-unit', 'width_unit', 'px');
				value('eas-popup-height-mode', 'height_mode', 'fit'); value('eas-popup-height', 'height_value', 500); value('eas-popup-height-unit', 'height_unit', 'px'); value('eas-popup-max-height', 'max_height', 90);
				value('eas-popup-horizontal', 'horizontal_position', 'center'); value('eas-popup-vertical', 'vertical_position', 'center'); value('eas-popup-offset-x', 'offset_x', 0); value('eas-popup-offset-y', 'offset_y', 0);
				value('eas-popup-entrance', 'entrance_animation', 'fade'); value('eas-popup-exit', 'exit_animation', 'fade'); value('eas-popup-animation-duration', 'animation_duration', 400);
				checked('eas-popup-show-overlay', 'show_overlay', true); checked('eas-popup-prevent-scroll', 'prevent_scroll', true); checked('eas-popup-show-close', 'show_close', true); checked('eas-popup-overlay-close', 'close_overlay', true); checked('eas-popup-escape-close', 'close_escape', true);
				value('eas-popup-bg', 'popup_bg', '#ffffff'); value('eas-popup-padding', 'popup_padding', 0); value('eas-popup-radius', 'border_radius', 0); value('eas-popup-border-width', 'border_width', 0); value('eas-popup-border-color', 'border_color', '#e5e7eb'); value('eas-popup-shadow-blur', 'shadow_blur', 80); value('eas-popup-shadow-opacity', 'shadow_opacity', 30);
				value('eas-popup-overlay-color', 'overlay_color', '#0f172a'); value('eas-popup-overlay-opacity', 'overlay_opacity', 68); value('eas-popup-overlay-blur', 'overlay_blur', 0); value('eas-popup-close-bg', 'close_bg', '#111827'); value('eas-popup-close-color', 'close_color', '#ffffff'); value('eas-popup-close-size', 'close_size', 42);
				checked('eas-popup-trigger-load', 'trigger_load', legacyTrigger === 'load'); checked('eas-popup-trigger-scroll', 'trigger_scroll', legacyTrigger === 'scroll'); checked('eas-popup-trigger-element', 'trigger_element', false); checked('eas-popup-trigger-click', 'trigger_click', legacyTrigger === 'click'); checked('eas-popup-trigger-inactivity', 'trigger_inactivity', false); checked('eas-popup-trigger-exit', 'trigger_exit', legacyTrigger === 'exit_intent');
				value('eas-popup-delay', 'delay', 500); value('eas-popup-scroll', 'scroll_percent', 50); value('eas-popup-element-selector', 'element_selector', ''); value('eas-popup-selector', 'click_selector', ''); value('eas-popup-inactivity', 'inactivity_seconds', 30);
				value('eas-popup-frequency', 'frequency', 'session'); value('eas-popup-max-shows', 'max_shows', 0); value('eas-popup-min-views', 'min_page_views', 0); value('eas-popup-min-sessions', 'min_sessions', 0); value('eas-popup-user-state', 'user_state', 'all'); value('eas-popup-browser', 'browser', 'all'); value('eas-popup-url-contains', 'url_contains', ''); value('eas-popup-referrer-contains', 'referrer_contains', ''); value('eas-popup-schedule-start', 'schedule_start', ''); value('eas-popup-schedule-end', 'schedule_end', '');
				var devices = String(settings.devices || 'desktop,tablet,mobile').split(',');
				['desktop', 'tablet', 'mobile'].forEach(function(device) { $('#eas-popup-device-' + device).prop('checked', devices.indexOf(device) !== -1); });
			}

			function collectPopupSettings() {
				function yes(id) { return $('#' + id).is(':checked') ? 'yes' : 'no'; }
				var devices = ['desktop', 'tablet', 'mobile'].filter(function(device) { return $('#eas-popup-device-' + device).is(':checked'); });
				var legacyTrigger = yes('eas-popup-trigger-load') === 'yes' ? 'load' : (yes('eas-popup-trigger-scroll') === 'yes' ? 'scroll' : (yes('eas-popup-trigger-click') === 'yes' ? 'click' : (yes('eas-popup-trigger-exit') === 'yes' ? 'exit_intent' : 'manual')));
				return {
					trigger: legacyTrigger, trigger_load: yes('eas-popup-trigger-load'), trigger_scroll: yes('eas-popup-trigger-scroll'), trigger_element: yes('eas-popup-trigger-element'), trigger_click: yes('eas-popup-trigger-click'), trigger_inactivity: yes('eas-popup-trigger-inactivity'), trigger_exit: yes('eas-popup-trigger-exit'),
					delay: $('#eas-popup-delay').val(), scroll_percent: $('#eas-popup-scroll').val(), element_selector: $('#eas-popup-element-selector').val(), click_selector: $('#eas-popup-selector').val(), inactivity_seconds: $('#eas-popup-inactivity').val(),
					width_value: $('#eas-popup-width').val(), width_unit: $('#eas-popup-width-unit').val(), height_mode: $('#eas-popup-height-mode').val(), height_value: $('#eas-popup-height').val(), height_unit: $('#eas-popup-height-unit').val(), max_height: $('#eas-popup-max-height').val(), horizontal_position: $('#eas-popup-horizontal').val(), vertical_position: $('#eas-popup-vertical').val(), offset_x: $('#eas-popup-offset-x').val(), offset_y: $('#eas-popup-offset-y').val(),
					entrance_animation: $('#eas-popup-entrance').val(), exit_animation: $('#eas-popup-exit').val(), animation_duration: $('#eas-popup-animation-duration').val(), show_overlay: yes('eas-popup-show-overlay'), prevent_scroll: yes('eas-popup-prevent-scroll'), show_close: yes('eas-popup-show-close'), close_overlay: yes('eas-popup-overlay-close'), close_escape: yes('eas-popup-escape-close'),
					popup_bg: $('#eas-popup-bg').val(), popup_padding: $('#eas-popup-padding').val(), border_radius: $('#eas-popup-radius').val(), border_width: $('#eas-popup-border-width').val(), border_color: $('#eas-popup-border-color').val(), shadow_blur: $('#eas-popup-shadow-blur').val(), shadow_opacity: $('#eas-popup-shadow-opacity').val(), overlay_color: $('#eas-popup-overlay-color').val(), overlay_opacity: $('#eas-popup-overlay-opacity').val(), overlay_blur: $('#eas-popup-overlay-blur').val(), close_bg: $('#eas-popup-close-bg').val(), close_color: $('#eas-popup-close-color').val(), close_size: $('#eas-popup-close-size').val(),
					frequency: $('#eas-popup-frequency').val(), max_shows: $('#eas-popup-max-shows').val(), min_page_views: $('#eas-popup-min-views').val(), min_sessions: $('#eas-popup-min-sessions').val(), user_state: $('#eas-popup-user-state').val(), browser: $('#eas-popup-browser').val(), url_contains: $('#eas-popup-url-contains').val(), referrer_contains: $('#eas-popup-referrer-contains').val(), schedule_start: $('#eas-popup-schedule-start').val(), schedule_end: $('#eas-popup-schedule-end').val(), devices: devices.join(',')
				};
			}

			function renderConditions(conditions) {
				$condRepeater.empty();
				if (conditions && conditions.length > 0) {
					conditions.forEach(function(cond, index) {
						addConditionRow(cond, index);
					});
				}
			}

			function addConditionRow(cond, index) {
				var templateType = $('#eas-cond-template-type').val() || 'header';
				var row = $('<div class="eas-tb-cond-row"></div>');
				var typeHtml = '<select class="eas-cond-type" data-index="' + index + '"><option value="include"' + (cond.type === 'include' ? ' selected' : '') + '>Include</option><option value="exclude"' + (cond.type === 'exclude' ? ' selected' : '') + '>Exclude</option></select>';
				var nameHtml = '<select class="eas-cond-name" data-index="' + index + '">';
				
				var topLevel = {};
				if ( templateType === 'single_post' || templateType === 'single_page' ) {
					topLevel = { singular: 'Singular' };
				} else if ( templateType === 'product_single' ) {
					topLevel = { singular: 'Singular'<?php echo class_exists( 'WooCommerce' ) ? ", woocommerce: 'WooCommerce'" : ''; ?> };
				} else if ( templateType === 'archive' ) {
					topLevel = { archive: 'Archives' };
				} else if ( templateType === 'not_found_404' ) {
					topLevel = { general: '404 Page' };
				} else if ( templateType === 'product_archive' ) {
					topLevel = { archive: 'Archives'<?php echo class_exists( 'WooCommerce' ) ? ", woocommerce: 'WooCommerce'" : ''; ?> };
				} else {
					topLevel = { general: 'Entire Site', singular: 'Singular', archive: 'Archives'<?php echo class_exists( 'WooCommerce' ) ? ", woocommerce: 'WooCommerce'" : ''; ?> };
				}

				Object.keys(topLevel).forEach(function(key) {
					nameHtml += '<option value="' + key + '"' + (cond.name === key ? ' selected' : '') + '>' + topLevel[key] + '</option>';
				});
				nameHtml += '</select>';
				row.append(typeHtml);
				row.append(nameHtml);

				var subSelect = $('<select class="eas-cond-sub-name" data-index="' + index + '"><option value="">All</option></select>');
				row.append(subSelect);

				var subIdWrap = $('<div class="eas-cond-sub-id-wrap" style="display:none;"></div>');
				var subIdSelect = $('<select class="eas-cond-sub-id" data-index="' + index + '"><option value="">All</option></select>');
				subIdWrap.append(subIdSelect);
				row.append(subIdWrap);

				var removeBtn = $('<button type="button" class="eas-cond-remove-btn dashicons dashicons-no-alt" title="Remove"></button>');
				removeBtn.on('click', function() { row.remove(); });
				row.append(removeBtn);

				$condRepeater.append(row);
				loadSubNames(row, cond.name, cond.sub_name, cond.sub_id);
			}

			function loadSubNames($row, name, selectedSub, selectedSubId) {
				var $subSelect = $row.find('.eas-cond-sub-name');
				$row.find('.eas-cond-sub-id').attr('data-selected', selectedSubId || '');
				$.post(ajaxurl, {
					action: 'apexadfo_get_sub_name_options',
					nonce: '<?php echo esc_attr( $nonce ); ?>',
					name: name,
				}, function(response) {
					if (response.success) {
						$subSelect.empty();
						var opts = response.data.options;
						if (name === 'general') {
							$subSelect.append('<option value="">Entire Site</option>');
						} else {
							Object.keys(opts).forEach(function(key) {
								$subSelect.append('<option value="' + key + '"' + (selectedSub === key ? ' selected' : '') + '>' + opts[key] + '</option>');
							});
						}
						$subSelect.trigger('change');
					}
				});
			}

			$condRepeater.on('change', '.eas-cond-name', function() {
				var $row = $(this).closest('.eas-tb-cond-row');
				loadSubNames($row, $(this).val(), '', '');
			});

			$condRepeater.on('change', '.eas-cond-sub-name', function() {
				var $row = $(this).closest('.eas-tb-cond-row');
				var subName = $(this).val();
				var $subIdWrap = $row.find('.eas-cond-sub-id-wrap');
				var $subIdSelect = $row.find('.eas-cond-sub-id');
				var savedSubId = $subIdSelect.attr('data-selected') || '';
				if (!subName) {
					$subIdWrap.hide();
					return;
				}
				$.post(ajaxurl, {
					action: 'apexadfo_get_sub_id_options',
					nonce: '<?php echo esc_attr( $nonce ); ?>',
					sub_name: subName,
				}, function(response) {
					if (response.success) {
						var opts = response.data.options;
						$subIdSelect.empty();
						if (Object.keys(opts).length > 0) {
							$subIdSelect.append('<option value="">All</option>');
							Object.keys(opts).forEach(function(key) {
								var selected = (key == savedSubId) ? ' selected' : '';
								$subIdSelect.append('<option value="' + key + '"' + selected + '>' + opts[key] + '</option>');
							});
							$subIdWrap.show();
						} else {
							$subIdWrap.hide();
						}
						$subIdSelect.removeAttr('data-selected');
					}
				});
			});

			$('#eas-tb-add-condition').on('click', function() {
				var index = $condRepeater.children().length;
				var templateType = $('#eas-cond-template-type').val() || 'header';
				var defaultName = 'general';
				if ( templateType === 'single_post' || templateType === 'single_page' || templateType === 'product_single' ) {
					defaultName = 'singular';
				} else if ( templateType === 'archive' || templateType === 'product_archive' ) {
					defaultName = 'archive';
				} else if ( templateType === 'not_found_404' ) {
					defaultName = 'general';
				}
				addConditionRow({ type: 'include', name: defaultName, sub_name: '', sub_id: '' }, index);
			});

			function collectConditions() {
				var conditions = [];
				$condRepeater.find('.eas-tb-cond-row').each(function() {
					var cond = {
						type: $(this).find('.eas-cond-type').val(),
						name: $(this).find('.eas-cond-name').val(),
						sub_name: $(this).find('.eas-cond-sub-name').val(),
						sub_id: $(this).find('.eas-cond-sub-id').val() || '',
					};
					conditions.push(cond);
				});
				return conditions;
			}
		});
		<?php
		$theme_builder_script = ob_get_clean();
		wp_add_inline_script( 'apexadfo-admin-dashboard-js', $theme_builder_script );
	}

	/**
	 * Render the Get Apex Pro Showcase Page
	 */
	public function render_get_pro_page() {
		$logo_url     = plugins_url( 'assets/images/apex-addons-logo.png', __FILE__ );
		$checkout_url = apply_filters( 'apexadfo_pro_checkout_url', 'https://checkout.freemius.com/mode/dialog/plugin/36225/' );

		$pro_widgets = [
			// 3D & Advanced Motion Widgets
			'spatial_carousel' => [
				'title'    => esc_html__( 'Spatial 3D Carousel', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Full 3D spatial cylinder carousel with interactive depth perspective, auto-orbit, drag velocity, and inertial navigation.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-gallery',
				'tags'     => [ '3D Spatial', 'Cylinder', 'Interactive' ],
			],
			'coverflow_carousel' => [
				'title'    => esc_html__( 'Coverflow 3D Carousel', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Apple-style Coverflow slider where active cards expand and others collapse into vertical slats with 3D depth.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-images-alt',
				'tags'     => [ 'Coverflow', '3D Slider', 'Swiper' ],
			],
			'coverflow_gallery' => [
				'title'    => esc_html__( 'Coverflow Gallery', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Premium 3D Coverflow gallery slider with adjustable side tilt, depth perspective, and text content overlays.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-gallery',
				'tags'     => [ '3D Gallery', 'Coverflow', 'Tilt' ],
			],
			'liquid_glass' => [
				'title'    => esc_html__( 'Liquid Glass Morphism', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'WebGL metaballs liquid glass refraction background with typography hero sections, metallic sheen, and mouse shine.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-admin-appearance',
				'tags'     => [ 'Glassmorphism', 'Refraction', 'Shine' ],
			],
			'physics_sandbox' => [
				'title'    => esc_html__( 'Kinetic Physics Sandbox', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Real-time Matter.js physical gravity canvas. Drag, toss, bounce, and collide element badges with gravity controls.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-move',
				'tags'     => [ 'Matter.js', 'Physics', 'Gravity' ],
			],
			'morphing_gallery' => [
				'title'    => esc_html__( 'Morphing Card Gallery', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Transform structured content cards between circle, wave, stagger, grid, fan, and 3D depth scenes with pointer parallax and pinned scroll morphing.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-shapes',
				'tags'     => [ 'SVG Morph', 'Metaball', 'GSAP' ],
			],
			'kinetic_text' => [
				'title'    => esc_html__( 'Kinetic Fluid Text', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Premium typography that warps and flows dynamically like liquid on mouse hover.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-editor-textcolor',
				'tags'     => [ 'Kinetic Text', 'Typography', 'Fluid' ],
			],
			'infinite_grid' => [
				'title'    => esc_html__( 'Infinite Canvas Grid', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'An infinite, mouse drag-to-pan project grid showcase with inertia momentum and wrap-around tiling.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-grid-view',
				'tags'     => [ 'Infinite Grid', 'Drag Pan', 'Canvas' ],
			],
			'proximity_orbit' => [
				'title'    => esc_html__( 'Proximity Orbit 3D', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Interactive circular perimeter orbiting showcase with parallax scroll momentum.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-update',
				'tags'     => [ 'Proximity', 'Orbit', 'Parallax' ],
			],
			'scroll_bloom_orbit' => [
				'title'    => esc_html__( 'Scroll Bloom Orbit', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Scroll-driven circular expansion showcase that twists open like a blooming flower.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-art',
				'tags'     => [ 'Scroll Bloom', 'Orbit', 'Animation' ],
			],
			'motion_typography_pro' => [
				'title'    => esc_html__( 'Motion Typography Pro', 'apex-addons-for-elementor' ),
				'category' => 'motion',
				'desc'     => esc_html__( 'Scroll-driven kinetic text, split-line reveal, variable font weights, and wave typography.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-editor-textcolor',
				'tags'     => [ 'Kinetic Text', 'Typography', 'SplitType' ],
			],

			// Pro Extensions & Container Motion FX
			'pinned_vertical_scroll_pro' => [
				'title'    => esc_html__( 'Pinned Vertical Scroll Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Pin containers in sticky viewport stages while inner elements travel vertically on scroll with deep links and panel snapping.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-sticky',
				'tags'     => [ 'Sticky Pin', 'Panel Snap', 'Deep Links' ],
			],
			'container_stack_pro' => [
				'title'    => esc_html__( '3D Card Scroll Stack Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( '3D card stack container effect. Inner containers stack over each other with 3D depth, tilt, and inner reveals.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-layer-group',
				'tags'     => [ '3D Card Stack', 'Scroll Depth', 'Choreography' ],
			],
			'section_transitions_pro' => [
				'title'    => esc_html__( '3D Section Transitions Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Scroll-linked page section reveals with Cover, Push, Scale, Zoom, Split, and 3D perspective flip transitions.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-slides',
				'tags'     => [ 'Curtain Reveal', '3D Section', 'Mask' ],
			],
			'magnetic_effect_pro' => [
				'title'    => esc_html__( 'Magnetic Attraction Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Magnetic cursor attraction effect for buttons, cards, and icons with custom pull radius and smooth spring physics.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-update',
				'tags'     => [ 'Magnetic Pull', 'Cursor FX', 'Spring Physics' ],
			],
			'custom_cursor_pro' => [
				'title'    => esc_html__( 'Custom Cursor Suite Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Spring follower, inverse color circle, ring & dot follower, glow blob, and custom SVG cursors.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-admin-cursor',
				'tags'     => [ 'Custom Cursor', 'Spring Follower', 'Glow' ],
			],
			'kenburns_slideshow_pro' => [
				'title'    => esc_html__( 'Ken Burns Slideshow Pro', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Background pan & zoom Ken Burns slideshow for containers with sleek dot & line navigation.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-image',
				'tags'     => [ 'Ken Burns', 'Background Slideshow', 'Zoom' ],
			],
			'container_parallax' => [
				'title'    => esc_html__( 'Page-Scroll Parallax', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Link the container carousel movement directly to the webpage scrollbar.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-controls-play',
				'tags'     => [ 'Page Scroll', 'Parallax', 'Container' ],
			],
			'particles' => [
				'title'    => esc_html__( 'Interactive 3D Particles', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Add responsive 3D floating nodes that warp and connect on mouse hover in section backgrounds.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-admin-site-alt3',
				'tags'     => [ '3D Particles', 'Hover Warp', 'Background' ],
			],
			'parallax_gallery' => [
				'title'    => esc_html__( 'Parallax Flying Columns', 'apex-addons-for-elementor' ),
				'category' => 'extensions',
				'desc'     => esc_html__( 'Add flying multi-column scroll gallery behind your container content.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-gallery',
				'tags'     => [ 'Parallax Columns', 'Flying Gallery', 'Background' ],
			],

			// Interactive Canvas Loaders
			'generative_art_loader' => [
				'title'    => esc_html__( 'Generative Art Loader', 'apex-addons-for-elementor' ),
				'category' => 'loaders',
				'desc'     => esc_html__( 'Sophisticated calligraphic ribbon strands generative art canvas preloader with fluid particle loops.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-art',
				'tags'     => [ 'Canvas', 'Generative Art', 'Ribbon Strands' ],
			],
			'hypnotic_track_loader' => [
				'title'    => esc_html__( 'Hypnotic Track Loader', 'apex-addons-for-elementor' ),
				'category' => 'loaders',
				'desc'     => esc_html__( 'Concentric spinning ring preloader with hardware-accelerated SVG infinity path motion tracker loop.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-update-alt',
				'tags'     => [ 'Hypnotic', 'Preloader', 'SVG Loop' ],
			],
			'shatter_particle_loader' => [
				'title'    => esc_html__( 'Shatter Particle Loader', 'apex-addons-for-elementor' ),
				'category' => 'loaders',
				'desc'     => esc_html__( 'Interactive geometric shard particle preloader with explosive physics outro on complete.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-forms',
				'tags'     => [ 'Explosion', 'Shatter Shards', 'Physics Outro' ],
			],
			'text_particle_loader' => [
				'title'    => esc_html__( 'Text Particle Loader', 'apex-addons-for-elementor' ),
				'category' => 'loaders',
				'desc'     => esc_html__( 'Interactive HTML5 canvas particle typography preloader with mouse repulsion physics.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-editor-customchar',
				'tags'     => [ 'Particle Text', 'Canvas', 'Mouse Physics' ],
			],
			'blend_curve_loader' => [
				'title'    => esc_html__( 'Blend Curve Loader', 'apex-addons-for-elementor' ),
				'category' => 'loaders',
				'desc'     => esc_html__( 'CMYK concentric circles rotating with dynamic color-overlapping blend modes.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-image-filter',
				'tags'     => [ 'Blend Modes', 'CMYK', 'Preloader' ],
			],
			'terminal_console' => [
				'title'    => esc_html__( 'Retro Terminal Console', 'apex-addons-for-elementor' ),
				'category' => 'loaders',
				'desc'     => esc_html__( 'Retro-futuristic terminal log compiler preloader with glitch typewriter, custom commands, and scanlines.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-editor-code',
				'tags'     => [ 'CLI Terminal', 'Typing FX', 'Glitch' ],
			],

			// Advanced UI & Integrations
			'floating_dock' => [
				'title'    => esc_html__( 'macOS Floating Dock', 'apex-addons-for-elementor' ),
				'category' => 'ui',
				'desc'     => esc_html__( 'macOS-style interactive magnetic bottom dock with smooth icon magnification and tooltip badges.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-menu-alt3',
				'tags'     => [ 'macOS Dock', 'Magnification', 'Nav' ],
			],
			'audio_visualizer' => [
				'title'    => esc_html__( 'Web Audio API Visualizer', 'apex-addons-for-elementor' ),
				'category' => 'ui',
				'desc'     => esc_html__( 'Real-time HTML5 Web Audio API frequency bar visualizer for audio tracks, podcasts, and music.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-format-audio',
				'tags'     => [ 'Web Audio API', 'Equalizer', 'Podcasts' ],
			],
			'bento_grid' => [
				'title'    => esc_html__( 'Image Bento Grid Layout', 'apex-addons-for-elementor' ),
				'category' => 'ui',
				'desc'     => esc_html__( 'Modern Apple-style bento grid card layout with hover spotlights and variable span controls using GSAP Flip.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-grid-view',
				'tags'     => [ 'Bento Grid', 'Apple Style', 'GSAP Flip' ],
			],
			'card_deck_pro' => [
				'title'    => esc_html__( 'Interactive Card Deck', 'apex-addons-for-elementor' ),
				'category' => 'ui',
				'desc'     => esc_html__( 'Interactive stack card swiper with physics and inertial drag, swipe, and rotation controls.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-images-alt2',
				'tags'     => [ 'Card Deck', 'Swiper Cards', 'Physics' ],
			],
			'icon_iconify' => [
				'title'    => esc_html__( 'Iconify 300,000+ Icon Search API', 'apex-addons-for-elementor' ),
				'category' => 'ui',
				'desc'     => esc_html__( 'Dynamic live icon search connecting to Iconify API for 300,000+ open-source icons in Elementor.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-admin-site-alt',
				'tags'     => [ 'Iconify API', '300k Icons', 'Live Search' ],
			],
			'form_studio' => [
				'title'    => esc_html__( 'Form Studio & Webhook Automation', 'apex-addons-for-elementor' ),
				'category' => 'ui',
				'desc'     => esc_html__( 'Conditional recipient routing, autoresponders, signed webhooks, and submission metadata for Form Builder.', 'apex-addons-for-elementor' ),
				'icon'     => 'dashicons-feedback',
				'tags'     => [ 'Webhooks', 'Autoresponder', 'Form Studio' ],
			],
		];

		$categories = [
			'all'         => esc_html__( 'All Pro Features', 'apex-addons-for-elementor' ),
			'motion'      => esc_html__( '3D & Motion', 'apex-addons-for-elementor' ),
			'extensions'  => esc_html__( 'Pro Extensions', 'apex-addons-for-elementor' ),
			'loaders'     => esc_html__( 'Canvas Loaders', 'apex-addons-for-elementor' ),
			'ui'          => esc_html__( 'Advanced UI', 'apex-addons-for-elementor' ),
		];
		?>
		<div class="eas-admin-wrap eas-pro-showcase-wrap">
			<!-- Hero Section -->
			<div class="eas-pro-hero">
				<div class="eas-pro-hero-content">
					<div class="eas-pro-badge">
						<span class="dashicons dashicons-superhero"></span>
						<?php esc_html_e( 'UNLEASH FULL CREATIVE FREEDOM', 'apex-addons-for-elementor' ); ?>
					</div>
					<h1 class="eas-pro-hero-title">
						<?php esc_html_e( 'Supercharge Elementor with ', 'apex-addons-for-elementor' ); ?>
						<span class="eas-pro-gradient-text"><?php esc_html_e( 'Apex Addons Pro', 'apex-addons-for-elementor' ); ?></span>
					</h1>
					<p class="eas-pro-hero-desc">
						<?php esc_html_e( 'Unlock 30+ cutting-edge 3D widgets, interactive canvas loaders, complete WooCommerce Single Product builder, and advanced motion extensions built for modern web designers.', 'apex-addons-for-elementor' ); ?>
					</p>
					<div class="eas-pro-hero-cta">
						<a href="<?php echo esc_url( $checkout_url ); ?>" target="_blank" rel="noopener noreferrer" class="eas-pro-btn-primary">
							<?php esc_html_e( 'Upgrade to Apex Pro →', 'apex-addons-for-elementor' ); ?>
						</a>
						<a href="#eas-pro-comparison" class="eas-pro-btn-secondary">
							<?php esc_html_e( 'Compare Free vs Pro', 'apex-addons-for-elementor' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- Key Benefits Grid -->
			<div class="eas-pro-highlights">
				<div class="eas-pro-highlight-card">
					<div class="eas-pro-hl-icon"><span class="dashicons dashicons-format-gallery"></span></div>
					<h3><?php esc_html_e( '25+ Pro Interactive Widgets', 'apex-addons-for-elementor' ); ?></h3>
					<p><?php esc_html_e( 'Spatial 3D Cylinder Carousel, Coverflow Gallery, Liquid Glass, Physics Gravity Sandbox, Infinite Canvas Grid, Proximity Orbit, and Kinetic Fluid Text.', 'apex-addons-for-elementor' ); ?></p>
				</div>
				<div class="eas-pro-highlight-card">
					<div class="eas-pro-hl-icon"><span class="dashicons dashicons-layers"></span></div>
					<h3><?php esc_html_e( 'Pro Container Motion FX', 'apex-addons-for-elementor' ); ?></h3>
					<p><?php esc_html_e( 'Pinned Vertical Scroll, 3D Card Stack Studio, Section Transitions Pro, Page-Scroll Parallax, Flying Columns, 3D Particles, and Custom Cursors.', 'apex-addons-for-elementor' ); ?></p>
				</div>
				<div class="eas-pro-highlight-card">
					<div class="eas-pro-hl-icon"><span class="dashicons dashicons-art"></span></div>
					<h3><?php esc_html_e( 'Interactive Canvas Loaders', 'apex-addons-for-elementor' ); ?></h3>
					<p><?php esc_html_e( 'Generative Art, Hypnotic Ring Tracks, Shatter Particle Explosions, Text Particles, CMYK Blend Curves, and Retro Terminal Console.', 'apex-addons-for-elementor' ); ?></p>
				</div>
				<div class="eas-pro-highlight-card">
					<div class="eas-pro-hl-icon"><span class="dashicons dashicons-sos"></span></div>
					<h3><?php esc_html_e( 'VIP Priority Support & Integrations', 'apex-addons-for-elementor' ); ?></h3>
					<p><?php esc_html_e( 'Direct developer assistance, 300,000+ Iconify API live icon search, Form Automation Webhooks, priority feature requests, and instant updates.', 'apex-addons-for-elementor' ); ?></p>
				</div>
			</div>

			<!-- Filter Tabs -->
			<div class="eas-pro-tabs-header">
				<h2><?php esc_html_e( 'Explore All Pro Widgets & Extensions', 'apex-addons-for-elementor' ); ?></h2>
				<div class="eas-pro-tabs-nav">
					<?php foreach ( $categories as $key => $label ) : ?>
						<button class="eas-pro-tab-btn<?php echo 'all' === $key ? ' active' : ''; ?>" data-filter="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $label ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Widgets Grid -->
			<div class="eas-pro-widget-grid">
				<?php foreach ( $pro_widgets as $id => $widget ) : ?>
					<div class="eas-pro-widget-card" data-category="<?php echo esc_attr( $widget['category'] ); ?>">
						<div class="eas-pro-card-header">
							<div class="eas-pro-card-icon"><span class="dashicons <?php echo esc_attr( $widget['icon'] ); ?>"></span></div>
							<span class="eas-pro-card-badge">PRO</span>
						</div>
						<h3 class="eas-pro-card-title"><?php echo esc_html( $widget['title'] ); ?></h3>
						<p class="eas-pro-card-desc"><?php echo esc_html( $widget['desc'] ); ?></p>
						<div class="eas-pro-card-tags">
							<?php foreach ( $widget['tags'] as $tag ) : ?>
								<span class="eas-pro-tag"><?php echo esc_html( $tag ); ?></span>
							<?php endforeach; ?>
						</div>
						<div class="eas-pro-card-footer">
							<a href="<?php echo esc_url( $checkout_url ); ?>" target="_blank" rel="noopener noreferrer" class="eas-pro-card-btn">
								<?php esc_html_e( 'Get Pro Feature →', 'apex-addons-for-elementor' ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Comparison Table Section -->
			<div id="eas-pro-comparison" class="eas-pro-comparison-section">
				<div class="eas-pro-comp-header">
					<h2><?php esc_html_e( 'Compare Free vs Pro Features', 'apex-addons-for-elementor' ); ?></h2>
					<p><?php esc_html_e( 'See why upgrading to Apex Addons Pro is the ultimate choice for your website.', 'apex-addons-for-elementor' ); ?></p>
				</div>
				<table class="eas-pro-comp-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Features & Capabilities', 'apex-addons-for-elementor' ); ?></th>
							<th class="eas-comp-free"><?php esc_html_e( 'Apex Free', 'apex-addons-for-elementor' ); ?></th>
							<th class="eas-comp-pro"><?php esc_html_e( 'Apex Pro ⚡', 'apex-addons-for-elementor' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Interactive Widgets', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free">15 Essential Widgets</td>
							<td class="eas-comp-pro"><strong>30+ Advanced & 3D Widgets</strong></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Spatial 3D & Motion Engine', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free"><span class="dashicons dashicons-minus"></span></td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Included (Spatial 3D, Coverflow, Infinite Grid, Orbit)</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'WooCommerce Single Product Suite', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free"><span class="dashicons dashicons-yes-alt"></span> 8 Dynamic Widgets Included</td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Included + Pro Styles</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Interactive Canvas Preloader Screens', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free">Basic CSS Preloaders</td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> 6 Canvas & Particle Preloaders</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Theme Builder & Display Conditions Engine', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free"><span class="dashicons dashicons-yes-alt"></span> Included</td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Included + Pro Templates</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Physics & Matter.js Sandbox', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free"><span class="dashicons dashicons-minus"></span></td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Included</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Liquid Glass Morphism', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free"><span class="dashicons dashicons-minus"></span></td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Included</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Iconify 300,000+ Icon Search API', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free"><span class="dashicons dashicons-minus"></span></td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Included (300k+ Icons)</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Form Automation & Signed Webhooks', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free">Basic Form Submissions</td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Autoresponders & Webhooks</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'VIP Support & Direct Upgrades', 'apex-addons-for-elementor' ); ?></strong></td>
							<td class="eas-comp-free">Community Forum</td>
							<td class="eas-comp-pro"><span class="dashicons dashicons-yes-alt"></span> Priority Ticket & VIP Support</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Bottom CTA Banner -->
			<div class="eas-pro-bottom-cta">
				<div class="eas-pro-bottom-cta-inner">
					<h2><?php esc_html_e( 'Ready to Transform Your Elementor Websites?', 'apex-addons-for-elementor' ); ?></h2>
					<p><?php esc_html_e( 'Join thousands of web designers and agencies creating award-winning websites with Apex Addons Pro.', 'apex-addons-for-elementor' ); ?></p>
					<div class="eas-pro-bottom-btn-wrap">
						<a href="<?php echo esc_url( $checkout_url ); ?>" target="_blank" rel="noopener noreferrer" class="eas-pro-btn-primary eas-pro-btn-large">
							<?php esc_html_e( 'Get Apex Addons Pro Now →', 'apex-addons-for-elementor' ); ?>
						</a>
					</div>
					<div class="eas-pro-guarantee">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( '14-Day Money Back Guarantee • Instant License Activation', 'apex-addons-for-elementor' ); ?>
					</div>
				</div>
			</div>
		</div>

		<?php ob_start(); ?>
		jQuery(document).ready(function($) {
			$('.eas-pro-tab-btn').on('click', function() {
				$('.eas-pro-tab-btn').removeClass('active');
				$(this).addClass('active');
				var filter = $(this).data('filter');
				if (filter === 'all') {
					$('.eas-pro-widget-card').show();
				} else {
					$('.eas-pro-widget-card').hide();
					$('.eas-pro-widget-card[data-category="' + filter + '"]').show();
				}
			});
		});
		<?php
		$pro_showcase_script = ob_get_clean();
		wp_add_inline_script( 'apexadfo-admin-dashboard-js', $pro_showcase_script );
	}

	/**
	 * Return sanitized defaults for a popup template.
	 *
	 * @return array
	 */
	private function get_default_popup_settings() {
		return [
			'trigger'            => 'load',
			'trigger_load'       => 'yes',
			'trigger_scroll'     => 'no',
			'trigger_element'    => 'no',
			'trigger_click'      => 'no',
			'trigger_inactivity' => 'no',
			'trigger_exit'       => 'no',
			'delay'              => 500,
			'scroll_percent'     => 50,
			'element_selector'   => '',
			'click_selector'     => '',
			'inactivity_seconds' => 30,
			'width_value'        => 640,
			'width_unit'         => 'px',
			'height_mode'        => 'fit',
			'height_value'       => 500,
			'height_unit'        => 'px',
			'max_height'         => 90,
			'horizontal_position' => 'center',
			'vertical_position'   => 'center',
			'offset_x'             => 0,
			'offset_y'             => 0,
			'show_overlay'       => 'yes',
			'prevent_scroll'     => 'yes',
			'show_close'         => 'yes',
			'close_overlay'      => 'yes',
			'close_escape'       => 'yes',
			'entrance_animation' => 'fade',
			'exit_animation'     => 'fade',
			'animation_duration' => 400,
			'popup_bg'           => '#ffffff',
			'popup_padding'      => 0,
			'border_radius'      => 0,
			'border_width'       => 0,
			'border_color'       => '#e5e7eb',
			'shadow_blur'        => 80,
			'shadow_opacity'     => 30,
			'overlay_color'      => '#0f172a',
			'overlay_opacity'    => 68,
			'overlay_blur'       => 0,
			'close_bg'           => '#111827',
			'close_color'        => '#ffffff',
			'close_size'         => 42,
			'frequency'          => 'session',
			'max_shows'          => 0,
			'min_page_views'     => 0,
			'min_sessions'       => 0,
			'user_state'         => 'all',
			'browser'            => 'all',
			'url_contains'       => '',
			'referrer_contains'  => '',
			'schedule_start'     => '',
			'schedule_end'       => '',
			'devices'            => 'desktop,tablet,mobile',
		];
	}

	/**
	 * Sanitize popup behavior settings.
	 *
	 * @param array $settings Raw popup settings.
	 * @return array
	 */
	private function sanitize_popup_settings( $settings ) {
		$defaults = $this->get_default_popup_settings();
		$settings = is_array( $settings ) ? $settings : [];
		if ( ! array_key_exists( 'trigger_load', $settings ) && isset( $settings['trigger'] ) ) {
			$legacy_trigger = sanitize_key( $settings['trigger'] );
			$settings['trigger_load']  = 'load' === $legacy_trigger ? 'yes' : 'no';
			$settings['trigger_scroll'] = 'scroll' === $legacy_trigger ? 'yes' : 'no';
			$settings['trigger_click']  = 'click' === $legacy_trigger ? 'yes' : 'no';
			$settings['trigger_exit']   = 'exit_intent' === $legacy_trigger ? 'yes' : 'no';
		}
		$settings = wp_parse_args( is_array( $settings ) ? $settings : [], $defaults );
		$yes_no = static function ( $value ) {
			return 'yes' === $value ? 'yes' : 'no';
		};
		$choice = static function ( $value, $allowed, $fallback ) {
			$value = sanitize_key( $value );
			return in_array( $value, $allowed, true ) ? $value : $fallback;
		};
		$color = static function ( $value, $fallback ) {
			$sanitized = sanitize_hex_color( $value );
			return $sanitized ? $sanitized : $fallback;
		};
		$devices = array_filter( array_map( 'sanitize_key', explode( ',', (string) $settings['devices'] ) ) );
		$devices = array_values( array_intersect( $devices, [ 'desktop', 'tablet', 'mobile' ] ) );
		if ( empty( $devices ) ) {
			$devices = [ 'desktop', 'tablet', 'mobile' ];
		}

		return [
			'trigger'              => $choice( $settings['trigger'], [ 'load', 'scroll', 'exit_intent', 'click', 'manual' ], $defaults['trigger'] ),
			'trigger_load'         => $yes_no( $settings['trigger_load'] ),
			'trigger_scroll'       => $yes_no( $settings['trigger_scroll'] ),
			'trigger_element'      => $yes_no( $settings['trigger_element'] ),
			'trigger_click'        => $yes_no( $settings['trigger_click'] ),
			'trigger_inactivity'   => $yes_no( $settings['trigger_inactivity'] ),
			'trigger_exit'         => $yes_no( $settings['trigger_exit'] ),
			'delay'                => min( 60000, absint( $settings['delay'] ) ),
			'scroll_percent'       => min( 100, max( 1, absint( $settings['scroll_percent'] ) ) ),
			'element_selector'     => sanitize_text_field( $settings['element_selector'] ),
			'click_selector'       => sanitize_text_field( $settings['click_selector'] ),
			'inactivity_seconds'   => min( 3600, max( 1, absint( $settings['inactivity_seconds'] ) ) ),
			'width_value'          => min( 5000, max( 1, absint( $settings['width_value'] ) ) ),
			'width_unit'           => $choice( $settings['width_unit'], [ 'px', 'percent', 'vw' ], $defaults['width_unit'] ),
			'height_mode'          => $choice( $settings['height_mode'], [ 'fit', 'custom', 'fullscreen' ], $defaults['height_mode'] ),
			'height_value'         => min( 5000, max( 1, absint( $settings['height_value'] ) ) ),
			'height_unit'          => $choice( $settings['height_unit'], [ 'px', 'vh' ], $defaults['height_unit'] ),
			'max_height'           => min( 100, max( 10, absint( $settings['max_height'] ) ) ),
			'horizontal_position'  => $choice( $settings['horizontal_position'], [ 'left', 'center', 'right' ], $defaults['horizontal_position'] ),
			'vertical_position'    => $choice( $settings['vertical_position'], [ 'top', 'center', 'bottom' ], $defaults['vertical_position'] ),
			'offset_x'             => min( 2000, max( -2000, intval( $settings['offset_x'] ) ) ),
			'offset_y'             => min( 2000, max( -2000, intval( $settings['offset_y'] ) ) ),
			'show_overlay'         => $yes_no( $settings['show_overlay'] ),
			'prevent_scroll'       => $yes_no( $settings['prevent_scroll'] ),
			'show_close'           => $yes_no( $settings['show_close'] ),
			'close_overlay'        => $yes_no( $settings['close_overlay'] ),
			'close_escape'         => $yes_no( $settings['close_escape'] ),
			'entrance_animation'   => $choice( $settings['entrance_animation'], [ 'none', 'fade', 'zoom', 'slide_up', 'slide_down', 'slide_left', 'slide_right' ], $defaults['entrance_animation'] ),
			'exit_animation'       => $choice( $settings['exit_animation'], [ 'none', 'fade', 'zoom', 'slide_up', 'slide_down', 'slide_left', 'slide_right' ], $defaults['exit_animation'] ),
			'animation_duration'   => min( 5000, max( 0, absint( $settings['animation_duration'] ) ) ),
			'popup_bg'             => $color( $settings['popup_bg'], $defaults['popup_bg'] ),
			'popup_padding'        => min( 300, absint( $settings['popup_padding'] ) ),
			'border_radius'        => min( 300, absint( $settings['border_radius'] ) ),
			'border_width'         => min( 30, absint( $settings['border_width'] ) ),
			'border_color'         => $color( $settings['border_color'], $defaults['border_color'] ),
			'shadow_blur'          => min( 300, absint( $settings['shadow_blur'] ) ),
			'shadow_opacity'       => min( 100, absint( $settings['shadow_opacity'] ) ),
			'overlay_color'        => $color( $settings['overlay_color'], $defaults['overlay_color'] ),
			'overlay_opacity'      => min( 100, absint( $settings['overlay_opacity'] ) ),
			'overlay_blur'         => min( 50, absint( $settings['overlay_blur'] ) ),
			'close_bg'             => $color( $settings['close_bg'], $defaults['close_bg'] ),
			'close_color'          => $color( $settings['close_color'], $defaults['close_color'] ),
			'close_size'           => min( 120, max( 24, absint( $settings['close_size'] ) ) ),
			'frequency'            => $choice( $settings['frequency'], [ 'always', 'session', 'once' ], $defaults['frequency'] ),
			'max_shows'            => min( 1000, absint( $settings['max_shows'] ) ),
			'min_page_views'       => min( 1000, absint( $settings['min_page_views'] ) ),
			'min_sessions'         => min( 1000, absint( $settings['min_sessions'] ) ),
			'user_state'           => $choice( $settings['user_state'], [ 'all', 'logged_in', 'logged_out' ], $defaults['user_state'] ),
			'browser'              => $choice( $settings['browser'], [ 'all', 'chrome', 'firefox', 'safari', 'edge' ], $defaults['browser'] ),
			'url_contains'         => sanitize_text_field( $settings['url_contains'] ),
			'referrer_contains'    => sanitize_text_field( $settings['referrer_contains'] ),
			'schedule_start'       => sanitize_text_field( $settings['schedule_start'] ),
			'schedule_end'         => sanitize_text_field( $settings['schedule_end'] ),
			'devices'              => implode( ',', $devices ),
		];
	}

	/**
	 * Get every matching template ID, ordered by priority and then creation order.
	 *
	 * Higher priority values win. Equal priorities use the oldest template first,
	 * which makes matching deterministic across database engines.
	 *
	 * @param string $type Template type.
	 * @return int[]
	 */
	private function get_matching_templates_for_current_page( $type ) {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return [];
		}

		if ( is_singular( 'apexadfo_template' ) ) {
			return [];
		}

		require_once __DIR__ . '/class-apex-conditions-engine.php';

		$templates = get_posts( [
			'post_type'      => 'apexadfo_template',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_key'       => '_apexadfo_template_type', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Template types are stored as metadata on a small, admin-managed template post type.
			'meta_value'     => $type, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Required to select the requested theme-template type.
		] );

		$matches = [];
		foreach ( $templates as $temp_id ) {
			$conditions = get_post_meta( $temp_id, '_apexadfo_template_conditions', true );

			if ( ! is_array( $conditions ) ) {
				$conditions = [
					[ 'type' => 'include', 'name' => 'general', 'sub_name' => '', 'sub_id' => '' ],
				];
			}

			if ( Conditions_Engine::is_template_match( $conditions, $type ) ) {
				$matches[] = absint( $temp_id );
			}
		}

		usort(
			$matches,
			static function ( $left, $right ) {
				$left_priority  = metadata_exists( 'post', $left, '_apexadfo_template_priority' ) ? absint( get_post_meta( $left, '_apexadfo_template_priority', true ) ) : 10;
				$right_priority = metadata_exists( 'post', $right, '_apexadfo_template_priority' ) ? absint( get_post_meta( $right, '_apexadfo_template_priority', true ) ) : 10;
				if ( $left_priority === $right_priority ) {
					return $left <=> $right;
				}
				return $right_priority <=> $left_priority;
			}
		);

		return $matches;
	}

	/**
	 * Get the highest-priority active template matching the current page.
	 *
	 * @param string $type Template type.
	 * @return int|false
	 */
	public function get_active_template_for_current_page( $type ) {
		$matches = $this->get_matching_templates_for_current_page( $type );

		return ! empty( $matches ) ? reset( $matches ) : false;
	}

	/**
	 * Pre-enqueue CSS styles for active Theme Builder templates on wp_enqueue_scripts
	 */
	public function enqueue_theme_builder_template_styles() {
		if ( is_admin() || ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}

		// Skip inside Elementor editor/preview iframe — Elementor handles its own CSS there.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview state check.
		if ( isset( $_GET['elementor-preview'] ) ) {
			return;
		}
		if ( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			return;
		}

		// Ensure Elementor's core frontend styles (elementor-frontend) are registered first
		if ( isset( \Elementor\Plugin::instance()->frontend ) ) {
			if ( method_exists( \Elementor\Plugin::instance()->frontend, 'register_styles' ) ) {
				\Elementor\Plugin::instance()->frontend->register_styles();
			}
			\Elementor\Plugin::instance()->frontend->enqueue_styles();
		}

		// Collect active matching templates
		$template_ids = [];

		$header_id = $this->get_active_template_for_current_page( 'header' );
		if ( $header_id ) {
			$template_ids[] = $header_id;
		}

		$footer_id = $this->get_active_template_for_current_page( 'footer' );
		if ( $footer_id ) {
			$template_ids[] = $footer_id;
		}

		if ( is_404() ) {
			$id = $this->get_active_template_for_current_page( 'not_found_404' );
			if ( $id ) {
				$template_ids[] = $id;
			}
		} elseif ( class_exists( 'WooCommerce' ) && is_product() ) {
			$id = $this->get_active_template_for_current_page( 'product_single' );
			if ( $id ) {
				$template_ids[] = $id;
			}
		} elseif ( class_exists( 'WooCommerce' ) && ( is_shop() || is_product_taxonomy() ) ) {
			$id = $this->get_active_template_for_current_page( 'product_archive' );
			if ( $id ) {
				$template_ids[] = $id;
			}
		} elseif ( is_page() ) {
			$id = $this->get_active_template_for_current_page( 'single_page' );
			if ( $id ) {
				$template_ids[] = $id;
			}
		} elseif ( is_single() ) {
			$id = $this->get_active_template_for_current_page( 'single_post' );
			if ( $id ) {
				$template_ids[] = $id;
			}
		} elseif ( is_archive() || is_search() || is_home() ) {
			$id = $this->get_active_template_for_current_page( 'archive' );
			if ( $id ) {
				$template_ids[] = $id;
			}
		}

		$popups = $this->get_matching_templates_for_current_page( 'popup' );
		if ( ! empty( $popups ) ) {
			$template_ids = array_merge( $template_ids, $popups );
		}

		// Enqueue CSS files for all active templates cleanly
		foreach ( array_unique( $template_ids ) as $tid ) {
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				$css_file = new \Elementor\Core\Files\CSS\Post( $tid );
				if ( $css_file ) {
					$css_file->enqueue();
				}
			}
		}
	}

	/** Enqueue popup runtime only when at least one popup matches this request. */
	public function enqueue_theme_builder_popup_assets() {
		$this->matching_popup_templates = $this->get_matching_templates_for_current_page( 'popup' );
		if ( empty( $this->matching_popup_templates ) ) {
			return;
		}
		wp_enqueue_style( 'apexadfo-theme-builder-popup-css' );
		wp_enqueue_script( 'apexadfo-theme-builder-popup-js' );
	}

	/** Render all matching popup templates in an inert, accessible overlay shell. */
	public function render_theme_builder_popups() {
		if ( null === $this->matching_popup_templates ) {
			$this->matching_popup_templates = $this->get_matching_templates_for_current_page( 'popup' );
		}
		foreach ( $this->matching_popup_templates as $template_id ) {
			$settings = get_post_meta( $template_id, '_apexadfo_popup_settings', true );
			$settings = $this->sanitize_popup_settings( is_array( $settings ) ? $settings : [] );
			$settings['is_logged_in'] = is_user_logged_in() ? 'yes' : 'no';
			$width_unit  = 'percent' === $settings['width_unit'] ? '%' : $settings['width_unit'];
			$popup_width = $settings['width_value'] . $width_unit;
			$popup_height = $settings['height_value'] . $settings['height_unit'];
			$style = sprintf(
				'--apexadfo-popup-width:%1$s;--apexadfo-popup-height:%2$s;--apexadfo-popup-max-height:%3$dvh;--apexadfo-popup-offset-x:%4$dpx;--apexadfo-popup-offset-y:%5$dpx;--apexadfo-popup-duration:%6$dms;--apexadfo-popup-bg:%7$s;--apexadfo-popup-padding:%8$dpx;--apexadfo-popup-radius:%9$dpx;--apexadfo-popup-border-width:%10$dpx;--apexadfo-popup-border-color:%11$s;--apexadfo-popup-shadow-blur:%12$dpx;--apexadfo-popup-shadow-alpha:%13$s;--apexadfo-popup-overlay-color:%14$s;--apexadfo-popup-overlay-alpha:%15$s;--apexadfo-popup-overlay-blur:%16$dpx;--apexadfo-popup-close-bg:%17$s;--apexadfo-popup-close-color:%18$s;--apexadfo-popup-close-size:%19$dpx;',
				esc_attr( $popup_width ), esc_attr( $popup_height ), $settings['max_height'], $settings['offset_x'], $settings['offset_y'], $settings['animation_duration'], esc_attr( $settings['popup_bg'] ), $settings['popup_padding'], $settings['border_radius'], $settings['border_width'], esc_attr( $settings['border_color'] ), $settings['shadow_blur'], esc_attr( $settings['shadow_opacity'] / 100 ), esc_attr( $settings['overlay_color'] ), esc_attr( $settings['overlay_opacity'] / 100 ), $settings['overlay_blur'], esc_attr( $settings['close_bg'] ), esc_attr( $settings['close_color'] ), $settings['close_size']
			);
			$classes = sprintf( 'apexadfo-popup apexadfo-popup--h-%1$s apexadfo-popup--v-%2$s apexadfo-popup--height-%3$s apexadfo-popup--enter-%4$s apexadfo-popup--exit-%5$s', $settings['horizontal_position'], $settings['vertical_position'], $settings['height_mode'], $settings['entrance_animation'], $settings['exit_animation'] );
			?>
			<div class="<?php echo esc_attr( $classes ); ?>" id="apexadfo-popup-<?php echo esc_attr( $template_id ); ?>" data-popup-id="<?php echo esc_attr( $template_id ); ?>" data-settings="<?php echo esc_attr( wp_json_encode( $settings ) ); ?>" style="<?php echo esc_attr( $style ); ?>" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php echo esc_attr( get_the_title( $template_id ) ); ?>" hidden>
				<?php if ( 'yes' === $settings['show_overlay'] ) : ?>
					<div class="apexadfo-popup__overlay" data-apexadfo-popup-close></div>
				<?php endif; ?>
				<div class="apexadfo-popup__positioner">
				<div class="apexadfo-popup__dialog" role="document" tabindex="-1">
					<?php if ( 'yes' === $settings['show_close'] ) : ?>
						<button type="button" class="apexadfo-popup__close" data-apexadfo-popup-close aria-label="<?php esc_attr_e( 'Close popup', 'apex-addons-for-elementor' ); ?>">&times;</button>
					<?php endif; ?>
					<div class="apexadfo-popup__content">
						<?php echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor returns trusted builder markup and enqueues its generated CSS. ?>
					</div>
				</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Inject custom header template at the top of the body
	 */
	public function render_custom_header() {
		// Skip inside Elementor editor preview iframe
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview state check.
		if ( isset( $_GET['elementor-preview'] ) || \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}

		$header_id = $this->get_active_template_for_current_page( 'header' );
		if ( ! $header_id ) {
			return;
		}

		// Ensure Elementor styles are registered before rendering
		if ( ! wp_style_is( 'elementor-frontend', 'registered' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			\Elementor\Plugin::$instance->frontend->register_styles();
		}

		// Output the Elementor builder content
		echo '<div class="eas-tb-header-wrap">';
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $header_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor returns trusted builder markup that must retain its authored structure.
		echo '</div>';
	}

	/**
	 * Inject custom footer template at the bottom of the body
	 */
	public function render_custom_footer() {
		// Skip inside Elementor editor preview iframe
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only preview state check.
		if ( isset( $_GET['elementor-preview'] ) || \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}

		$footer_id = $this->get_active_template_for_current_page( 'footer' );
		if ( ! $footer_id ) {
			return;
		}

		// Ensure Elementor styles are registered before rendering
		if ( ! wp_style_is( 'elementor-frontend', 'registered' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			\Elementor\Plugin::$instance->frontend->register_styles();
		}

		// Output the Elementor builder content
		echo '<div class="eas-tb-footer-wrap">';
		echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $footer_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor returns trusted builder markup that must retain its authored structure.
		echo '</div>';
	}

	/**
	 * Add scoped body classes for Theme Builder replacements.
	 *
	 * @param array $classes Existing body classes.
	 * @return array
	 */
	public function add_theme_builder_body_classes( $classes ) {
		if ( $this->get_active_template_for_current_page( 'header' ) ) {
			$classes[] = 'apexadfo-has-custom-header';
		}
		if ( $this->get_active_template_for_current_page( 'footer' ) ) {
			$classes[] = 'apexadfo-has-custom-footer';
		}
		if ( is_404() && $this->get_active_template_for_current_page( 'not_found_404' ) ) {
			$classes[] = 'apexadfo-has-custom-404';
		}
		return $classes;
	}

	/**
	 * Override theme template for matching Singular, Archives, and WooCommerce layouts
	 */
	public function override_theme_template( $template ) {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $template;
		}

		// Skip template override inside the Elementor editor iframe
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This only detects Elementor's read-only preview context; it does not process a value or mutate state.
		if ( isset( $_GET['elementor-preview'] ) ) {
			return $template;
		}

		if ( is_singular( 'apexadfo_template' ) ) {
			return $template;
		}

		$matched_template_id = false;

		// 1. Dedicated 404 page.
		if ( is_404() ) {
			$matched_template_id = $this->get_active_template_for_current_page( 'not_found_404' );
		}
		// 2. WooCommerce Product Single.
		elseif ( class_exists( 'WooCommerce' ) && is_product() ) {
			$matched_template_id = $this->get_active_template_for_current_page( 'product_single' );
		}
		// 3. WooCommerce Product Archive (Shop or Taxonomies).
		elseif ( class_exists( 'WooCommerce' ) && ( is_shop() || is_product_taxonomy() ) ) {
			$matched_template_id = $this->get_active_template_for_current_page( 'product_archive' );
		}
		// 4. General Singular Pages/Posts.
		elseif ( is_singular() ) {
			if ( is_page() ) {
				$matched_template_id = $this->get_active_template_for_current_page( 'single_page' );
			} else {
				$matched_template_id = $this->get_active_template_for_current_page( 'single_post' );
			}

			// Fallback to legacy single
			if ( ! $matched_template_id ) {
				$matched_template_id = $this->get_active_template_for_current_page( 'single' );
			}
		}
		// 5. General Archives / Blog Home / Search Results.
		elseif ( is_archive() || is_home() || is_search() ) {
			$matched_template_id = $this->get_active_template_for_current_page( 'archive' );
		}

		if ( $matched_template_id ) {
			$GLOBALS['apexadfo_active_theme_template_id'] = $matched_template_id;
			$custom_template = __DIR__ . '/templates/theme-builder-canvas.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}

		return $template;
	}

	/**
	 * Setup sample WooCommerce product context when editing or previewing a Product Single template
	 */
	public function setup_woocommerce_editor_preview_context() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$current_id = get_the_ID();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading template ID parameter in read-only preview context.
		$get_post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading template preview ID parameter in read-only preview context.
		$get_preview_id = isset( $_GET['preview_id'] ) ? absint( $_GET['preview_id'] ) : 0;

		if ( ! $current_id ) {
			$current_id = $get_post_id ? $get_post_id : $get_preview_id;
		}

		if ( ! $current_id ) {
			return;
		}

		$post_type = get_post_type( $current_id );
		if ( 'apexadfo_template' !== $post_type && 'elementor_library' !== $post_type ) {
			return;
		}

		$tmpl_type = get_post_meta( $current_id, '_apexadfo_template_type', true );
		if ( ! $tmpl_type ) {
			$tmpl_type = get_post_meta( $current_id, '_elementor_template_type', true );
		}

		if ( 'product_single' === $tmpl_type || 'product' === $tmpl_type || 'single-product' === $tmpl_type ) {
			// If global $product is already set and valid for a real product, do nothing
			if ( isset( $GLOBALS['product'] ) && is_object( $GLOBALS['product'] ) && $GLOBALS['product']->get_id() > 0 && 'product' === get_post_type( $GLOBALS['product']->get_id() ) ) {
				return;
			}

			// Query latest published WooCommerce product for live builder preview
			$sample_products = get_posts( [
				'post_type'      => 'product',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			] );

			if ( ! empty( $sample_products ) && function_exists( 'wc_get_product' ) ) {
				$sample_id          = $sample_products[0]->ID;
				$GLOBALS['product'] = wc_get_product( $sample_id );
			}
		}
	}

	/**
	 * Register `elementor-frontend` as a no-op style on the Elementor editor
	 * admin page.  Elementor's own editor initialisation enqueues per-post CSS
	 * files (e.g. `elementor-post-3072`) that declare `elementor-frontend` as a
	 * dependency, but it never calls `register_styles()` in the editor context
	 * (that method is hooked to the frontend-only `wp_enqueue_scripts`).
	 * WordPress 6.9+ validates dependencies at registration time and triggers a
	 * `_doing_it_wrong` notice for unregistered deps. Registering an empty
	 * placeholder here silences the notice; the real stylesheet is only needed
	 * on the public frontend, not inside the editor admin shell.
	 */
	public function register_editor_frontend_style_placeholder() {
		if ( ! wp_style_is( 'elementor-frontend', 'registered' ) ) {
			wp_register_style( 'elementor-frontend', false, [], null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Placeholder only; the real stylesheet is registered by Elementor on public pages.
		}
	}

	/**
	 * Sync current editor template post type meta for Elementor native widget previewing
	 */
	public function sync_current_editor_template_type() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading post ID parameter in read-only admin context.
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : get_the_ID();
		if ( $post_id ) {
			$this->sync_elementor_template_type_meta( $post_id );
		}
	}

	/**
	 * Map Apex Theme Builder template types to Elementor's native _elementor_template_type meta
	 *
	 * @param int $post_id Template post ID.
	 */
	public function sync_elementor_template_type_meta( $post_id ) {
		if ( ! $post_id || 'apexadfo_template' !== get_post_type( $post_id ) ) {
			return;
		}

		$apex_type = get_post_meta( $post_id, '_apexadfo_template_type', true );
		if ( ! $apex_type ) {
			return;
		}

		$elementor_type_map = [
			'header'          => 'header',
			'footer'          => 'footer',
			'single_post'     => 'single-post',
			'single_page'     => 'single-page',
			'archive'         => 'archive',
			'not_found_404'   => 'error-404',
			'product_single'  => 'product',
			'product_archive' => 'product-archive',
			'popup'           => 'popup',
		];

		if ( isset( $elementor_type_map[ $apex_type ] ) ) {
			$current_meta = get_post_meta( $post_id, '_elementor_template_type', true );
			if ( $current_meta !== $elementor_type_map[ $apex_type ] ) {
				update_post_meta( $post_id, '_elementor_template_type', $elementor_type_map[ $apex_type ] );
			}
		}

		// Set default preview settings for Elementor widgets
		if ( 'product_single' === $apex_type && class_exists( 'WooCommerce' ) ) {
			$page_settings = get_post_meta( $post_id, '_elementor_page_settings', true );
			if ( ! is_array( $page_settings ) ) {
				$page_settings = [];
			}
			if ( empty( $page_settings['preview_type'] ) ) {
				$sample_products = get_posts( [
					'post_type'      => 'product',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				] );
				$sample_id       = ! empty( $sample_products ) ? $sample_products[0]->ID : 0;

				$page_settings['preview_type'] = 'single/product';
				if ( $sample_id ) {
					$page_settings['preview_id'] = (string) $sample_id;
				}
				update_post_meta( $post_id, '_elementor_page_settings', $page_settings );
			}
		}
	}

	/**
	 * Register custom Elementor dynamic tags
	 */
	public function register_custom_dynamic_tags( $dynamic_tags_manager ) {
		require_once __DIR__ . '/class-apex-dynamic-tags.php';

		// Register core groups which are missing in Elementor Free
		$groups = [
			'post' => [
				'title' => esc_html__( 'Post', 'apex-addons-for-elementor' ),
			],
			'archive' => [
				'title' => esc_html__( 'Archive', 'apex-addons-for-elementor' ),
			],
			'site' => [
				'title' => esc_html__( 'Site', 'apex-addons-for-elementor' ),
			],
			'media' => [
				'title' => esc_html__( 'Media', 'apex-addons-for-elementor' ),
			],
			'author' => [
				'title' => esc_html__( 'Author', 'apex-addons-for-elementor' ),
			],
			'comments' => [
				'title' => esc_html__( 'Comments', 'apex-addons-for-elementor' ),
			],
			'woocommerce' => [
				'title' => esc_html__( 'WooCommerce', 'apex-addons-for-elementor' ),
			],
		];

		foreach ( $groups as $group_name => $group_settings ) {
			$dynamic_tags_manager->register_group( $group_name, $group_settings );
		}

		$tags = [
			// Post group
			'EAS_Post_Date_Tag',
			'EAS_Post_Excerpt_Tag',
			'EAS_Post_ID_Tag',
			'EAS_Post_Terms_Tag',
			'EAS_Post_Time_Tag',
			'EAS_Post_Title_Tag',
			'EAS_Post_Custom_Field_Tag',
			
			// Archive group
			'EAS_Archive_Description_Tag',
			'EAS_Archive_Meta_Tag',
			'EAS_Archive_Title_Tag',
			
			// Site group
			'EAS_Page_Title_Tag',
			'EAS_Site_Tagline_Tag',
			'EAS_Site_Title_Tag',
			'EAS_Current_Date_Time_Tag',
			'EAS_Request_Arg_Tag',
			'EAS_Shortcode_Tag',
			'EAS_User_Info_Tag',
			
			// Media group
			'EAS_Featured_Image_Data_Tag',
			
			// Author group
			'EAS_Author_Info_Tag',
			'EAS_Author_Meta_Tag',
			'EAS_Author_Name_Tag',
			
			// Comments group
			'EAS_Comments_Number_Tag',
		];

		// WooCommerce group (only register if WooCommerce class exists)
		if ( class_exists( 'WooCommerce' ) ) {
			$tags[] = 'EAS_Woo_Product_Price_Tag';
			$tags[] = 'EAS_Woo_Product_Rating_Tag';
			$tags[] = 'EAS_Woo_Product_Sale_Tag';
			$tags[] = 'EAS_Woo_Product_Content_Tag';
			$tags[] = 'EAS_Woo_Product_Short_Desc_Tag';
			$tags[] = 'EAS_Woo_Product_SKU_Tag';
			$tags[] = 'EAS_Woo_Product_Stock_Tag';
			$tags[] = 'EAS_Woo_Product_Terms_Tag';
			$tags[] = 'EAS_Woo_Product_Title_Tag';
		}

		foreach ( $tags as $tag_class ) {
			$full_class = '\\ArhamAshfaq\ApexAddonsForElementor\\' . $tag_class;
			if ( class_exists( $full_class ) ) {
				$dynamic_tags_manager->register( new $full_class() );
			}
		}
	}

	/**
	 * AJAX Callback: Save basic preloader settings
	 */
	public function ajax_save_basic_preloader() {
		check_ajax_referer( 'apexadfo_preloader_settings_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Forbidden', 'apex-addons-for-elementor' ) ] );
		}

		$enabled            = isset( $_POST['enabled'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) ? 'yes' : 'no';
		$loader_type        = isset( $_POST['loader_type'] ) ? sanitize_key( wp_unslash( $_POST['loader_type'] ) ) : 'spinner';
		$loader_type        = in_array( $loader_type, [ 'spinner', 'bar', 'pulse_logo', 'percentage' ], true ) ? $loader_type : 'spinner';
		$logo_url           = isset( $_POST['logo_url'] ) ? esc_url_raw( wp_unslash( $_POST['logo_url'] ) ) : '';
		$logo_size          = isset( $_POST['logo_size'] ) ? min( 400, max( 20, absint( $_POST['logo_size'] ) ) ) : 80;
		$bg_color           = isset( $_POST['bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['bg_color'] ) ) : '#ffffff';
		$accent_color       = isset( $_POST['accent_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['accent_color'] ) ) : '#0f172a';
		$text_color         = isset( $_POST['text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['text_color'] ) ) : '#0f172a';
		$display_on         = isset( $_POST['display_on'] ) ? sanitize_key( wp_unslash( $_POST['display_on'] ) ) : 'entire_site';
		$display_on         = in_array( $display_on, [ 'entire_site', 'homepage_only' ], true ) ? $display_on : 'entire_site';
		$min_duration       = isset( $_POST['min_duration'] ) ? min( 30, max( 0, (float) sanitize_text_field( wp_unslash( $_POST['min_duration'] ) ) ) ) : 1;
		$max_duration       = isset( $_POST['max_duration'] ) ? min( 60, max( $min_duration, (float) sanitize_text_field( wp_unslash( $_POST['max_duration'] ) ) ) ) : 5;
		$transition_speed   = isset( $_POST['transition_speed'] ) ? min( 5, max( 0.1, (float) sanitize_text_field( wp_unslash( $_POST['transition_speed'] ) ) ) ) : 0.6;
		$custom_text        = isset( $_POST['custom_text'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_text'] ) ) : '';
		$font_family        = isset( $_POST['font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['font_family'] ) ) : 'sans-serif';
		$custom_font_family = isset( $_POST['custom_font_family'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_font_family'] ) ) : '';
		$font_size          = isset( $_POST['font_size'] ) ? min( 200, max( 8, absint( $_POST['font_size'] ) ) ) : 14;
		$font_weight        = isset( $_POST['font_weight'] ) ? sanitize_text_field( wp_unslash( $_POST['font_weight'] ) ) : '600';
		$letter_spacing     = isset( $_POST['letter_spacing'] ) ? min( 50, max( -10, (float) sanitize_text_field( wp_unslash( $_POST['letter_spacing'] ) ) ) ) : 0;
		$loader_size        = isset( $_POST['loader_size'] ) ? min( 400, max( 10, absint( $_POST['loader_size'] ) ) ) : 50;
		$bg_color           = $bg_color ?: '#ffffff';
		$accent_color       = $accent_color ?: '#0f172a';
		$text_color         = $text_color ?: '#0f172a';

		update_option( 'apexadfo_basic_preloader', [
			'enabled'            => $enabled,
			'loader_type'        => $loader_type,
			'logo_url'           => $logo_url,
			'logo_size'          => $logo_size,
			'bg_color'           => $bg_color,
			'accent_color'       => $accent_color,
			'text_color'         => $text_color,
			'display_on'         => $display_on,
			'min_duration'       => $min_duration,
			'max_duration'       => $max_duration,
			'transition_speed'   => $transition_speed,
			'custom_text'        => $custom_text,
			'font_family'        => $font_family,
			'custom_font_family' => $custom_font_family,
			'font_size'          => $font_size,
			'font_weight'        => $font_weight,
			'letter_spacing'     => $letter_spacing,
			'loader_size'        => $loader_size,
		] );

		wp_send_json_success();
	}

	/**
	 * Render basic free settings panel inside dashboard
	 */
	public function render_free_preloader_settings_panel() {
		$settings = get_option( 'apexadfo_basic_preloader', [
			'enabled'            => 'no',
			'loader_type'        => 'spinner',
			'bg_color'           => '#ffffff',
			'accent_color'       => '#0f172a',
			'text_color'         => '#0f172a',
			'logo_url'           => '',
			'logo_size'          => '80',
			'display_on'         => 'entire_site',
			'min_duration'       => '1',
			'max_duration'       => '5',
			'transition_speed'   => '0.6',
			'custom_text'        => '',
			'font_family'        => 'sans-serif',
			'custom_font_family' => '',
			'font_size'          => '14',
			'font_weight'        => '600',
			'letter_spacing'     => '0',
			'loader_size'        => '50',
		] );
		$nonce              = wp_create_nonce( 'apexadfo_preloader_settings_nonce' );
		$display_on         = $settings['display_on'] ?? 'entire_site';
		$min_duration       = isset( $settings['min_duration'] ) ? floatval( $settings['min_duration'] ) : 1;
		$max_duration       = isset( $settings['max_duration'] ) ? floatval( $settings['max_duration'] ) : 5;
		$transition_speed   = isset( $settings['transition_speed'] ) ? floatval( $settings['transition_speed'] ) : 0.6;
		$custom_text        = $settings['custom_text'] ?? '';
		$font_family        = $settings['font_family'] ?? 'sans-serif';
		$custom_font_family = $settings['custom_font_family'] ?? '';
		$font_size          = isset( $settings['font_size'] ) ? intval( $settings['font_size'] ) : 14;
		$font_weight        = $settings['font_weight'] ?? '600';
		$letter_spacing     = isset( $settings['letter_spacing'] ) ? floatval( $settings['letter_spacing'] ) : 0;
		$loader_size        = isset( $settings['loader_size'] ) ? intval( $settings['loader_size'] ) : 50;

		$popular_fonts = [];
		if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
			$font_control = \Elementor\Plugin::instance()->controls_manager->get_control( 'font' );
			if ( $font_control ) {
				$popular_fonts = $font_control->get_settings( 'options' );
			}
		}

		if ( empty( $popular_fonts ) ) {
			$popular_fonts = [
				'sans-serif'        => esc_html__( 'Default System Sans-Serif', 'apex-addons-for-elementor' ),
				'serif'             => esc_html__( 'Default System Serif', 'apex-addons-for-elementor' ),
				'Inter'             => 'Inter',
				'Poppins'           => 'Poppins',
				'Roboto'            => 'Roboto',
				'Montserrat'        => 'Montserrat',
				'Lato'              => 'Lato',
				'Oswald'            => 'Oswald',
				'Raleway'           => 'Raleway',
				'Open Sans'         => 'Open Sans',
				'Playfair Display'  => 'Playfair Display',
				'Merriweather'      => 'Merriweather',
				'Lora'              => 'Lora',
				'Nunito'            => 'Nunito',
				'Ubuntu'            => 'Ubuntu',
				'PT Sans'           => 'PT Sans',
				'Work Sans'         => 'Work Sans',
				'Fira Sans'         => 'Fira Sans',
				'Quicksand'         => 'Quicksand',
				'Barlow'            => 'Barlow',
				'Manrope'           => 'Manrope',
				'Outfit'            => 'Outfit',
				'Syne'              => 'Syne',
				'Space Grotesk'     => 'Space Grotesk',
			];
		}
		?>
		<div class="eas-tb-preloader-settings-wrap" style="padding: 20px 0;">
			<div class="eas-tb-preloader-header-promo" style="display: flex; gap: 30px; margin-bottom: 30px; border-bottom: 1px solid #e2e8f0; padding-bottom: 25px;">
				<div class="eas-tb-promo-left" style="flex-grow: 1;">
					<h3 style="margin-top: 0; font-size: 20px; font-weight: 700; color: #0f172a;"><?php esc_html_e( 'Basic Preloader Settings', 'apex-addons-for-elementor' ); ?></h3>
					<p style="color: #64748b; font-size: 14px; margin-top: 5px;"><?php esc_html_e( 'Configure a lightweight default screen transition for your website.', 'apex-addons-for-elementor' ); ?></p>
				</div>
			</div>

			<form id="eas-tb-preloader-form" method="POST">
				<input type="hidden" name="action" value="apexadfo_save_basic_preloader" />
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Enable Preloader', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Show a preloader overlay on your website.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<label class="eas-admin-switch" style="position: relative; display: inline-block; width: 44px; height: 22px;">
							<input type="checkbox" name="enabled" value="yes" <?php checked( $settings['enabled'], 'yes' ); ?> style="opacity: 0; width: 0; height: 0;" />
							<span class="eas-admin-switch-slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 34px;"></span>
						</label>
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Display On', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Choose where to display the preloader.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<select name="display_on" style="min-width: 200px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px;">
							<option value="entire_site" <?php selected( $display_on, 'entire_site' ); ?>><?php esc_html_e( 'Entire Site', 'apex-addons-for-elementor' ); ?></option>
							<option value="homepage_only" <?php selected( $display_on, 'homepage_only' ); ?>><?php esc_html_e( 'Homepage Only', 'apex-addons-for-elementor' ); ?></option>
						</select>
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Loader Type', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Choose preloader animation.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<select name="loader_type" id="eas-preloader-type-select" style="min-width: 200px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px;">
							<option value="spinner" <?php selected( $settings['loader_type'], 'spinner' ); ?>><?php esc_html_e( 'Spinning Ring', 'apex-addons-for-elementor' ); ?></option>
							<option value="bar" <?php selected( $settings['loader_type'], 'bar' ); ?>><?php esc_html_e( 'Growing Loader Bar', 'apex-addons-for-elementor' ); ?></option>
							<option value="pulse_logo" <?php selected( $settings['loader_type'], 'pulse_logo' ); ?>><?php esc_html_e( 'Pulsing Brand Logo', 'apex-addons-for-elementor' ); ?></option>
							<option value="percentage" <?php selected( $settings['loader_type'], 'percentage' ); ?>><?php esc_html_e( 'Numeric Percentage Counter', 'apex-addons-for-elementor' ); ?></option>
							<option value="ripple" <?php selected( $settings['loader_type'], 'ripple' ); ?>><?php esc_html_e( 'Expanding Ripples', 'apex-addons-for-elementor' ); ?></option>
							<option value="wave" <?php selected( $settings['loader_type'], 'wave' ); ?>><?php esc_html_e( 'Dynamic Audio Wave', 'apex-addons-for-elementor' ); ?></option>
							<option value="orbit" <?php selected( $settings['loader_type'], 'orbit' ); ?>><?php esc_html_e( 'Orbiting Dots', 'apex-addons-for-elementor' ); ?></option>
						</select>
					</div>
				</div>

				<div class="eas-tb-settings-row logo-upload-row" style="<?php echo 'pulse_logo' === $settings['loader_type'] ? 'display: flex;' : 'display: none;'; ?> align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Upload Logo Image', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Provide the logo graphic to animate.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field" style="display: flex; align-items: center; gap: 10px;">
						<input type="text" name="logo_url" id="eas-logo-url-field" value="<?php echo esc_url( $settings['logo_url'] ); ?>" placeholder="https://..." style="min-width: 250px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px;" />
						<button type="button" class="eas-admin-btn" id="eas-logo-upload-btn" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 4px; cursor: pointer;"><?php esc_html_e( 'Choose Image', 'apex-addons-for-elementor' ); ?></button>
					</div>
				</div>

				<div class="eas-tb-settings-row logo-size-row" style="<?php echo 'pulse_logo' === $settings['loader_type'] ? 'display: flex;' : 'display: none;'; ?> align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Logo Width (px)', 'apex-addons-for-elementor' ); ?></label>
					</div>
					<div class="eas-tb-settings-field">
						<input type="number" name="logo_size" value="<?php echo intval( $settings['logo_size'] ); ?>" min="30" max="300" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;" />
					</div>
				</div>

				<?php
				$show_loader_size = in_array( $settings['loader_type'], [ 'spinner', 'bar', 'ripple', 'wave', 'orbit' ], true );
				?>
				<div class="eas-tb-settings-row loader-size-row" style="<?php echo $show_loader_size ? 'display: flex;' : 'display: none;'; ?> align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label class="eas-loader-size-label" style="font-weight: 600; color: #0f172a; display: block;">
							<?php echo 'bar' === $settings['loader_type'] ? esc_html__( 'Loader Width (px)', 'apex-addons-for-elementor' ) : esc_html__( 'Loader Size (px)', 'apex-addons-for-elementor' ); ?>
						</label>
					</div>
					<div class="eas-tb-settings-field">
						<input type="number" name="loader_size" id="eas-loader-size-input" value="<?php echo intval( $loader_size ); ?>" min="10" max="500" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Minimum Display Time (s)', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Forces loader to stay active for at least this long.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<input type="number" name="min_duration" value="<?php echo esc_attr( $min_duration ); ?>" min="0" max="10" step="0.1" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Maximum Load Timeout (s)', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Fades out automatically after this limit to prevent freezing.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<input type="number" name="max_duration" value="<?php echo esc_attr( $max_duration ); ?>" min="1" max="60" step="0.5" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Transition Speed (s)', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Controls how fast curtain panels slide away.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<input type="number" name="transition_speed" value="<?php echo esc_attr( $transition_speed ); ?>" min="0.1" max="5" step="0.1" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Custom Loading Text', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Display custom label under the loader.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<input type="text" name="custom_text" value="<?php echo esc_attr( $custom_text ); ?>" placeholder="<?php esc_attr_e( 'e.g. Loading...', 'apex-addons-for-elementor' ); ?>" style="min-width: 250px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Font Family', 'apex-addons-for-elementor' ); ?></label>
						<span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 4px;"><?php esc_html_e( 'Search and select any font registered in Elementor (including Google Fonts and Custom Fonts), or type a custom font family name.', 'apex-addons-for-elementor' ); ?></span>
					</div>
					<div class="eas-tb-settings-field">
						<input type="text" name="font_family" list="eas-font-families" value="<?php echo esc_attr( $font_family ); ?>" style="min-width: 250px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px;" placeholder="<?php esc_attr_e( 'Search or type font family...', 'apex-addons-for-elementor' ); ?>" autocomplete="off" />
						<datalist id="eas-font-families">
							<?php foreach ( $popular_fonts as $name => $value ) : ?>
								<option value="<?php echo esc_attr( $name ); ?>"></option>
							<?php endforeach; ?>
						</datalist>
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Font Size (px)', 'apex-addons-for-elementor' ); ?></label>
					</div>
					<div class="eas-tb-settings-field">
						<input type="number" name="font_size" value="<?php echo esc_attr( $font_size ); ?>" min="8" max="72" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Font Weight', 'apex-addons-for-elementor' ); ?></label>
					</div>
					<div class="eas-tb-settings-field">
						<select name="font_weight" style="min-width: 200px; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px;">
							<?php foreach ( [ '100', '200', '300', '400', '500', '600', '700', '800', '900' ] as $weight ) : ?>
				<option value="<?php echo esc_attr( $weight ); ?>" <?php selected( $font_weight, $weight ); ?>><?php echo esc_html( $weight ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Letter Spacing (px)', 'apex-addons-for-elementor' ); ?></label>
					</div>
					<div class="eas-tb-settings-field">
						<input type="number" name="letter_spacing" value="<?php echo esc_attr( $letter_spacing ); ?>" min="-5" max="20" step="0.5" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; width: 100px;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Background Color', 'apex-addons-for-elementor' ); ?></label>
					</div>
					<div class="eas-tb-settings-field">
						<input type="color" name="bg_color" value="<?php echo esc_attr( $settings['bg_color'] ); ?>" style="border: none; padding: 0; width: 44px; height: 32px; cursor: pointer;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Loader Accent Color', 'apex-addons-for-elementor' ); ?></label>
					</div>
					<div class="eas-tb-settings-field">
						<input type="color" name="accent_color" value="<?php echo esc_attr( $settings['accent_color'] ); ?>" style="border: none; padding: 0; width: 44px; height: 32px; cursor: pointer;" />
					</div>
				</div>

				<div class="eas-tb-settings-row" style="display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding: 15px 0;">
					<div class="eas-tb-settings-label" style="width: 250px; flex-shrink: 0; padding-right: 20px;">
						<label style="font-weight: 600; color: #0f172a; display: block;"><?php esc_html_e( 'Text Color', 'apex-addons-for-elementor' ); ?></label>
					</div>
					<div class="eas-tb-settings-field">
						<input type="color" name="text_color" value="<?php echo esc_attr( $settings['text_color'] ?? '#0f172a' ); ?>" style="border: none; padding: 0; width: 44px; height: 32px; cursor: pointer;" />
					</div>
				</div>

				<div class="eas-tb-form-actions-left" style="margin-top: 25px; display: flex; align-items: center;">
					<button type="submit" class="eas-admin-btn eas-btn-submit" style="background-color: #0f172a; color: #ffffff; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background-color 0.2s;"><?php esc_html_e( 'Save Settings', 'apex-addons-for-elementor' ); ?></button>
					<span class="eas-settings-save-success" style="display: none; color: #10b981; margin-left: 15px; font-weight: 600; font-size: 14px;"><span class="dashicons dashicons-yes"></span> Saved!</span>
				</div>
			</form>

		</div>
		<?php
	}

	/**
	 * Output and animate basic or Pro preloader canvas at the top of the body
	 */
	private function get_preloader_context() {
		if ( null !== $this->preloader_context ) {
			return $this->preloader_context;
		}
		$basic_settings = (array) get_option( 'apexadfo_basic_preloader', [] );
		$this->preloader_context = [];
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $this->preloader_context;
		}
		if ( isset( \Elementor\Plugin::instance()->editor ) && \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			return $this->preloader_context;
		}

		$template_id = absint( apply_filters( 'apexadfo_preloader_template_id', $this->get_active_template_for_current_page( 'preloader' ) ) );
		if ( $template_id ) {
			$page = (array) get_post_meta( $template_id, '_elementor_page_settings', true );
			$this->preloader_context = [
				'mode'             => 'template',
				'template_id'      => $template_id,
				'transition'       => sanitize_key( $page['eas_preloader_transition'] ?? 'split_vertical' ),
				'bg_color'         => sanitize_hex_color( $page['eas_preloader_bg_color'] ?? '#ffffff' ) ?: '#ffffff',
				'accent'           => '#0f172a',
				'text_color'       => '#0f172a',
				'min_duration'      => max( 0, min( 30, (float) ( $page['eas_preloader_min_duration']['size'] ?? 3 ) ) ),
				'max_duration'      => max( 1, min( 60, (float) ( $basic_settings['max_duration'] ?? 5 ) ) ),
				'transition_speed' => max( 0.1, min( 5, (float) ( $page['eas_preloader_anim_duration']['size'] ?? 0.6 ) ) ),
				'frequency'        => sanitize_key( $page['eas_preloader_frequency'] ?? 'always' ),
				'frequency_hours'  => max( 1, min( 8760, absint( $page['eas_preloader_frequency_hours'] ?? 24 ) ) ),
				'exit_trigger'     => sanitize_key( $page['eas_preloader_exit_trigger'] ?? 'page_load' ),
				'svg_draw'         => 'yes' === ( $page['eas_preloader_svg_draw'] ?? 'no' ),
				'svg_selector'     => sanitize_text_field( $page['eas_preloader_svg_selector'] ?? '.eas-preloader-center-content svg path' ),
				'svg_duration'     => max( 0.1, min( 15, (float) ( $page['eas_preloader_svg_duration'] ?? 2 ) ) ),
			];
			return $this->preloader_context;
		}

		$settings = (array) get_option( 'apexadfo_basic_preloader', [] );
		if ( 'yes' !== ( $settings['enabled'] ?? 'no' ) ) {
			return $this->preloader_context;
		}
		if ( 'homepage_only' === ( $settings['display_on'] ?? 'entire_site' ) && ! is_front_page() ) {
			return $this->preloader_context;
		}
		$loader_type = sanitize_key( $settings['loader_type'] ?? 'spinner' );
		if ( ! in_array( $loader_type, [ 'spinner', 'bar', 'pulse_logo', 'percentage', 'ripple', 'wave', 'orbit' ], true ) ) {
			$loader_type = 'spinner';
		}
		$loader_size = absint( $settings['loader_size'] ?? ( 'bar' === $loader_type ? 200 : 50 ) );
		$this->preloader_context = [
			'mode'             => 'basic',
			'template_id'      => 0,
			'transition'       => 'split_vertical',
			'loader_type'      => $loader_type,
			'loader_size'      => max( 20, min( 500, $loader_size ) ),
			'logo_url'         => esc_url_raw( $settings['logo_url'] ?? '' ),
			'logo_size'        => max( 20, min( 500, absint( $settings['logo_size'] ?? 120 ) ) ),
			'custom_text'      => sanitize_text_field( $settings['custom_text'] ?? '' ),
			'bg_color'         => sanitize_hex_color( $settings['bg_color'] ?? '#ffffff' ) ?: '#ffffff',
			'accent'           => sanitize_hex_color( $settings['accent_color'] ?? '#0f172a' ) ?: '#0f172a',
			'text_color'       => sanitize_hex_color( $settings['text_color'] ?? '#0f172a' ) ?: '#0f172a',
			'min_duration'      => max( 0, min( 30, (float) ( $settings['min_duration'] ?? 1 ) ) ),
			'max_duration'      => max( 1, min( 60, (float) ( $settings['max_duration'] ?? 5 ) ) ),
			'transition_speed' => max( 0.1, min( 5, (float) ( $settings['transition_speed'] ?? 0.6 ) ) ),
			'frequency'        => 'always',
			'frequency_hours'  => 24,
			'exit_trigger'     => 'page_load',
			'svg_draw'         => false,
			'svg_selector'     => '',
			'svg_duration'     => 0,
		];
		return $this->preloader_context;
	}

	public function enqueue_preloader_assets() {
		$context = $this->get_preloader_context();
		if ( empty( $context ) ) {
			return;
		}
		wp_enqueue_style( 'apexadfo-preloader' );
		wp_enqueue_script( 'apexadfo-preloader' );
		wp_localize_script(
			'apexadfo-preloader',
			'apexadfoPreloader',
			[
				'templateId'     => absint( $context['template_id'] ),
				'minDuration'    => (float) $context['min_duration'],
				'maxDuration'    => (float) $context['max_duration'],
				'transitionSpeed'=> (float) $context['transition_speed'],
				'frequency'      => sanitize_key( $context['frequency'] ),
				'frequencyHours' => absint( $context['frequency_hours'] ),
				'exitTrigger'    => sanitize_key( $context['exit_trigger'] ),
				'svgDraw'        => (bool) $context['svg_draw'],
				'svgSelector'    => sanitize_text_field( $context['svg_selector'] ),
				'svgDuration'    => (float) $context['svg_duration'],
			]
		);
	}

	/** Output the preloader markup. Assets and behavior are registered normally. */
	public function render_custom_preloader() {
		$context = $this->get_preloader_context();
		if ( empty( $context ) ) {
			return;
		}
		$style = '--eas-preloader-bg:' . $context['bg_color'] . ';--eas-preloader-accent:' . $context['accent'] . ';--eas-preloader-text:' . $context['text_color'] . ';--eas-preloader-size:' . absint( $context['loader_size'] ?? 50 ) . 'px;--eas-preloader-transition:' . (float) $context['transition_speed'] . 's;';
		echo '<div id="eas-preloader-wrap" class="eas-transition-' . esc_attr( $context['transition'] ) . '" style="' . esc_attr( $style ) . '">';
		echo '<div class="eas-preloader-panel eas-panel-left"></div><div class="eas-preloader-panel eas-panel-right"></div><div class="eas-preloader-center-content">';
		if ( 'template' === $context['mode'] ) {
			echo \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $context['template_id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor returns trusted builder markup that must retain its authored structure.
		} else {
			$type = $context['loader_type'];
			if ( 'spinner' === $type ) echo '<div class="eas-preloader-spinner"></div>';
			elseif ( 'bar' === $type ) echo '<div class="eas-preloader-bar-container"><div class="eas-preloader-bar"></div></div>';
			elseif ( 'pulse_logo' === $type && $context['logo_url'] ) echo '<img class="eas-preloader-logo" src="' . esc_url( $context['logo_url'] ) . '" alt="" style="width:' . absint( $context['logo_size'] ) . 'px">';
			elseif ( 'percentage' === $type ) echo '<div class="eas-preloader-percent" id="eas-preloader-percent-num">0%</div>';
			elseif ( 'ripple' === $type ) echo '<div class="eas-preloader-ripple"><span></span><span></span></div>';
			elseif ( 'wave' === $type ) echo '<div class="eas-preloader-wave"><span></span><span></span><span></span><span></span></div>';
			elseif ( 'orbit' === $type ) echo '<div class="eas-preloader-orbit"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>';
			if ( $context['custom_text'] ) echo '<div class="eas-preloader-text-label">' . esc_html( $context['custom_text'] ) . '</div>';
		}
		echo '</div></div>';
	}

}

// Instantiate Loader
Loader::get_instance();

// Register activation redirect for Setup Wizard
register_activation_hook( __FILE__, function() {
	set_transient( 'apexadfo_activation_redirect', true, 30 );
} );
