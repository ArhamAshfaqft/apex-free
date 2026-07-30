<?php
/**
 * Apex Addons - Onboarding Setup Wizard Class
 *
 * Provides a clean, modern setup wizard for new installations and updates.
 *
 * @package ApexAddonsForElementor
 */

namespace ArhamAshfaq\ApexAddonsForElementor\Free;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup_Wizard Class
 */
class Setup_Wizard {

	/**
	 * Instance
	 *
	 * @var Setup_Wizard
	 */
	private static $instance = null;

	/**
	 * Get Instance
	 *
	 * @return Setup_Wizard
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'register_wizard_page' ], 25 );
		add_action( 'admin_head', [ $this, 'hide_wizard_menu_item' ] );
		add_action( 'admin_init', [ $this, 'maybe_redirect_to_wizard' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_wizard_assets' ] );
		add_action( 'wp_ajax_apexadfo_save_wizard', [ $this, 'ajax_save_wizard' ] );
	}

	/**
	 * Register Admin Page for Setup Wizard
	 */
	public function register_wizard_page() {
		add_submenu_page(
			'apexadfo-addons',
			esc_html__( 'Setup Wizard', 'apex-addons-for-elementor' ),
			esc_html__( 'Setup Wizard', 'apex-addons-for-elementor' ),
			'manage_options',
			'apexadfo-setup-wizard',
			[ $this, 'render_wizard_page' ]
		);
	}

	/**
	 * Hide Setup Wizard item from WP Admin sidebar menu
	 */
	public function hide_wizard_menu_item() {
		echo '<style>#adminmenu a[href$="page=apexadfo-setup-wizard"] { display: none !important; }</style>';
	}

	/**
	 * Redirect to Setup Wizard once on plugin activation
	 */
	public function maybe_redirect_to_wizard() {
		if ( get_transient( 'apexadfo_activation_redirect' ) ) {
			delete_transient( 'apexadfo_activation_redirect' );

			if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_safe_redirect( admin_url( 'admin.php?page=apexadfo-setup-wizard' ) );
				exit;
			}
		}
	}

	/**
	 * Enqueue Assets for the Setup Wizard
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_wizard_assets( $hook ) {
		if ( isset( $_GET['page'] ) && 'apexadfo-setup-wizard' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_style(
				'apexadfo-admin-dashboard-css',
				plugins_url( 'assets/css/admin-dashboard.css', dirname( __FILE__ ) ),
				[],
				APEXADFO_VERSION
			);

			wp_enqueue_script(
				'apexadfo-admin-dashboard-js',
				plugins_url( 'assets/js/admin-dashboard.js', dirname( __FILE__ ) ),
				[ 'jquery' ],
				APEXADFO_VERSION,
				true
			);

			wp_enqueue_style(
				'apexadfo-wizard-css',
				plugins_url( 'assets/css/admin-wizard.css', dirname( __FILE__ ) ),
				[],
				APEXADFO_VERSION
			);

			wp_enqueue_script(
				'apexadfo-wizard-js',
				plugins_url( 'assets/js/admin-wizard.js', dirname( __FILE__ ) ),
				[ 'jquery' ],
				APEXADFO_VERSION,
				true
			);

			wp_localize_script(
				'apexadfo-wizard-js',
				'ApexAdfoWizard',
				[
					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'nonce'       => wp_create_nonce( 'apexadfo_wizard_nonce' ),
					'redirect_url'=> admin_url( 'admin.php?page=apexadfo-addons' ),
					'builder_url' => admin_url( 'post-new.php?post_type=page' ),
				]
			);
		}
	}

	/**
	 * Get All Available Widgets and Extensions List
	 *
	 * @return array List of elements grouped by category.
	 */
	public function get_wizard_elements() {
		$addons = \ArhamAshfaq\ApexAddonsForElementor\Free\Loader::get_instance()->get_addons();

		$categories = [
			'widgets' => [
				'title'    => esc_html__( 'Widgets & Single Template Suites', 'apex-addons-for-elementor' ),
				'elements' => [],
			],
			'extensions' => [
				'title'    => esc_html__( 'Container Motion Extensions', 'apex-addons-for-elementor' ),
				'elements' => [],
			],
			'backgrounds' => [
				'title'    => esc_html__( 'Background Effects', 'apex-addons-for-elementor' ),
				'elements' => [],
			],
			'icons' => [
				'title'    => esc_html__( 'Bundled Icon Libraries', 'apex-addons-for-elementor' ),
				'elements' => [],
			],
		];

		foreach ( $addons as $id => $data ) {
			$cat = $data['category'] ?? 'widgets';
			$item_info = [
				'title' => $data['title'],
				'pro'   => ! empty( $data['pro'] ),
			];
			if ( isset( $categories[ $cat ] ) ) {
				$categories[ $cat ]['elements'][ $id ] = $item_info;
			} else {
				$categories['widgets']['elements'][ $id ] = $item_info;
			}
		}

		return array_filter( $categories, function( $c ) {
			return ! empty( $c['elements'] );
		} );
	}

	/**
	 * Get Preset Configuration Maps
	 *
	 * @return array Presets and their enabled keys.
	 */
	public function get_presets() {
		$all_elements = [];
		foreach ( $this->get_wizard_elements() as $cat ) {
			foreach ( $cat['elements'] as $key => $name ) {
				$all_elements[] = $key;
			}
		}

		return [
			'basic' => [
				'form_widget', 'comparison_table', 'nested_slider', 'nested_content_switcher',
				'glass_card', 'conversational_funnel', 'quiz_builder', 'team_member',
				'portfolio_showcase', 'flex_accordion', 'dual_heading', 'svg_icon',
				'scroll_parallax_text', 'nav_menu', 'interactive_image_hotspots',
				'singular_widgets', 'magnetic_effect',
				'icon_lineawesome', 'icon_materialdesign', 'icon_ion', 'icon_icofont',
			],
			'performance' => [
				'form_widget', 'comparison_table', 'nested_slider', 'nested_content_switcher',
				'dual_heading', 'svg_icon', 'nav_menu', 'flex_accordion',
			],
			'complete' => $all_elements,
		];
	}

	/**
	 * AJAX Handler to Save Setup Wizard Choices
	 */
	public function ajax_save_wizard() {
		check_ajax_referer( 'apexadfo_wizard_nonce', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'apex-addons-for-elementor' ) ] );
		}

		$selected_elements = isset( $_POST['elements'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['elements'] ) ) : [];

		// Build final active options array for ALL registered addons
		$all_addons    = \ArhamAshfaq\ApexAddonsForElementor\Free\Loader::get_instance()->get_addons();
		$active_addons = [];

		foreach ( $all_addons as $id => $data ) {
			$active_addons[ $id ] = in_array( $id, $selected_elements, true );
		}

		update_option( 'apexadfo_active_addons', $active_addons );
		update_option( 'apexadfo_wizard_completed', '1' );

		wp_send_json_success( [
			'message' => esc_html__( 'Setup saved successfully!', 'apex-addons-for-elementor' ),
		] );
	}

	/**
	 * Render Setup Wizard Main HTML
	 */
	public function render_wizard_page() {
		$elements_grouped = $this->get_wizard_elements();
		$active_addons    = get_option( 'apexadfo_active_addons', null );
		?>
		<div class="apexadfo-wizard-wrap">
			<!-- Top Header Bar -->
			<header class="apexadfo-wizard-header">
				<div class="apexadfo-wizard-brand">
					<img src="<?php echo esc_url( plugins_url( 'assets/images/apex-addons-logo.png', dirname( __FILE__ ) ) ); ?>" alt="Apex Addons Logo" class="apexadfo-wizard-logo" />
					<span class="apexadfo-wizard-version">v<?php echo esc_html( APEXADFO_VERSION ); ?></span>
				</div>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=apexadfo-addons' ) ); ?>" class="apexadfo-wizard-skip">
					<?php esc_html_e( 'Skip Setup', 'apex-addons-for-elementor' ); ?> &rarr;
				</a>
			</header>

			<!-- Step Progress Indicator Bar -->
			<div class="apexadfo-wizard-nav">
				<div class="apexadfo-wizard-step-indicator active" data-step="1">
					<span class="apexadfo-step-num">1</span>
					<span class="apexadfo-step-title"><?php esc_html_e( 'Getting Started', 'apex-addons-for-elementor' ); ?></span>
				</div>
				<div class="apexadfo-wizard-step-indicator" data-step="2">
					<span class="apexadfo-step-num">2</span>
					<span class="apexadfo-step-title"><?php esc_html_e( 'Configuration', 'apex-addons-for-elementor' ); ?></span>
				</div>
				<div class="apexadfo-wizard-step-indicator" data-step="3">
					<span class="apexadfo-step-num">3</span>
					<span class="apexadfo-step-title"><?php esc_html_e( 'Elements', 'apex-addons-for-elementor' ); ?></span>
				</div>
				<div class="apexadfo-wizard-step-indicator" data-step="4">
					<span class="apexadfo-step-num">4</span>
					<span class="apexadfo-step-title"><?php esc_html_e( 'Ready!', 'apex-addons-for-elementor' ); ?></span>
				</div>
			</div>

			<!-- Wizard Body Container -->
			<div class="apexadfo-wizard-body">

				<!-- STEP 1: WELCOME SCREEN -->
				<div class="apexadfo-wizard-step active" id="apexadfo-step-1">
					<div class="apexadfo-wizard-hero">
						<div class="apexadfo-hero-badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
							<?php esc_html_e( 'Welcome to Apex Addons', 'apex-addons-for-elementor' ); ?>
						</div>
						<h1><?php esc_html_e( 'Supercharge Your Elementor Experience', 'apex-addons-for-elementor' ); ?></h1>
						<p><?php esc_html_e( 'Thank you for choosing Apex Addons for Elementor! Follow this quick setup wizard to configure your preferred features, optimize page loading speeds, and launch your next website.', 'apex-addons-for-elementor' ); ?></p>
						
						<div class="apexadfo-wizard-actions">
							<button type="button" class="apexadfo-btn apexadfo-btn-primary apexadfo-next-step">
								<?php esc_html_e( 'Get Started', 'apex-addons-for-elementor' ); ?> &rarr;
							</button>
						</div>
					</div>
				</div>

				<!-- STEP 2: PRESET CONFIGURATION -->
				<div class="apexadfo-wizard-step" id="apexadfo-step-2">
					<div class="apexadfo-wizard-section-header">
						<h2><?php esc_html_e( 'Choose Your Preferred Mode', 'apex-addons-for-elementor' ); ?></h2>
						<p><?php esc_html_e( 'Select a preset mode that fits your website goals. You can customize individual widgets at any time later.', 'apex-addons-for-elementor' ); ?></p>
					</div>

					<div class="apexadfo-preset-cards">
						<label class="apexadfo-preset-card selected">
							<input type="radio" name="apexadfo_preset" value="basic" checked />
							<div class="apexadfo-preset-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.71 1.26-1.5 1.76-2.31c-1.39-.41-2.61-1.39-3.26-2.69c-.81.5-1.6 1.05-2.31 1.76z"></path><path d="M15 9l-6 6"></path><path d="M9 15c.5-2.5 3-7 10-10c-3 7-7.5 9.5-10 10z"></path></svg>
							</div>
							<h3><?php esc_html_e( 'Basic (Recommended)', 'apex-addons-for-elementor' ); ?></h3>
							<p><?php esc_html_e( 'Activates popular core widgets & extensions while maintaining crisp page performance.', 'apex-addons-for-elementor' ); ?></p>
						</label>

						<label class="apexadfo-preset-card">
							<input type="radio" name="apexadfo_preset" value="performance" />
							<div class="apexadfo-preset-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
							</div>
							<h3><?php esc_html_e( 'Ultra Performance', 'apex-addons-for-elementor' ); ?></h3>
							<p><?php esc_html_e( 'Enables only essential lightweight widgets for maximum speed and instant page loads.', 'apex-addons-for-elementor' ); ?></p>
						</label>

						<label class="apexadfo-preset-card">
							<input type="radio" name="apexadfo_preset" value="complete" />
							<div class="apexadfo-preset-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.912 5.813a2 2 0 001.272 1.272L21 12l-5.813 1.912a2 2 0 00-1.272 1.272L12 21l-1.912-5.813a2 2 0 00-1.272-1.272L3 12l5.813-1.912a2 2 0 001.272-1.272L12 3z"></path></svg>
							</div>
							<h3><?php esc_html_e( 'All Features', 'apex-addons-for-elementor' ); ?></h3>
							<p><?php esc_html_e( 'Unlocks all 30+ interactive widgets, Theme Builder elements, and container motion extensions.', 'apex-addons-for-elementor' ); ?></p>
						</label>

						<label class="apexadfo-preset-card">
							<input type="radio" name="apexadfo_preset" value="custom" />
							<div class="apexadfo-preset-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
							</div>
							<h3><?php esc_html_e( 'Custom Selection', 'apex-addons-for-elementor' ); ?></h3>
							<p><?php esc_html_e( 'Handpick exactly which widgets and container extensions you want active on your site.', 'apex-addons-for-elementor' ); ?></p>
						</label>
					</div>

					<div class="apexadfo-wizard-actions">
						<button type="button" class="apexadfo-btn apexadfo-btn-secondary apexadfo-prev-step">
							&larr; <?php esc_html_e( 'Previous', 'apex-addons-for-elementor' ); ?>
						</button>
						<button type="button" class="apexadfo-btn apexadfo-btn-primary apexadfo-next-step">
							<?php esc_html_e( 'Next Step', 'apex-addons-for-elementor' ); ?> &rarr;
						</button>
					</div>
				</div>

				<!-- STEP 3: ELEMENTS SELECTION -->
				<div class="apexadfo-wizard-step" id="apexadfo-step-3">
					<div class="apexadfo-wizard-section-header">
						<h2><?php esc_html_e( 'Customize Active Elements', 'apex-addons-for-elementor' ); ?></h2>
						<p><?php esc_html_e( 'Enable or disable specific widgets and extensions for your site.', 'apex-addons-for-elementor' ); ?></p>
					</div>

					<div class="apexadfo-elements-container">
						<?php foreach ( $elements_grouped as $group_key => $group ) : ?>
							<div class="apexadfo-elements-group">
								<div class="apexadfo-elements-group-header">
									<h3><?php echo esc_html( $group['title'] ); ?></h3>
									<div class="apexadfo-elements-group-actions">
										<button type="button" class="apexadfo-link-btn apexadfo-select-all" data-group="<?php echo esc_attr( $group_key ); ?>"><?php esc_html_e( 'Enable All', 'apex-addons-for-elementor' ); ?></button>
										<span>|</span>
										<button type="button" class="apexadfo-link-btn apexadfo-deselect-all" data-group="<?php echo esc_attr( $group_key ); ?>"><?php esc_html_e( 'Disable All', 'apex-addons-for-elementor' ); ?></button>
									</div>
								</div>

								<div class="apexadfo-elements-grid" data-group-grid="<?php echo esc_attr( $group_key ); ?>">
									<?php
									foreach ( $group['elements'] as $element_key => $element_data ) :
										$element_title = is_array( $element_data ) ? $element_data['title'] : $element_data;
										$is_pro_item   = is_array( $element_data ) ? ! empty( $element_data['pro'] ) : false;
										$is_pro_locked = $is_pro_item && ( ! defined( 'APEXADFO_PRO_VERSION' ) || ! class_exists( '\ArhamAshfaq\ApexAddonsForElementor\Pro\Loader' ) );
										$is_checked    = $is_pro_locked ? false : ( is_null( $active_addons ) ? true : ( isset( $active_addons[ $element_key ] ) ? (bool) $active_addons[ $element_key ] : true ) );
										?>
										<label class="apexadfo-element-item<?php echo $is_pro_locked ? ' eas-card-pro-locked eas-pro-trigger' : ''; ?>"<?php echo $is_pro_locked ? ' data-pro-locked="1"' : ''; ?>>
											<span class="apexadfo-element-name"><?php echo esc_html( $element_title ); ?></span>
											<input type="checkbox" class="<?php echo $is_pro_locked ? 'eas-pro-trigger-checkbox' : 'apexadfo-toggle-input'; ?>" name="apexadfo_elements[]" value="<?php echo esc_attr( $element_key ); ?>" <?php echo $is_pro_locked ? 'disabled' : ''; ?> <?php checked( $is_checked ); ?> />
											<span class="apexadfo-toggle-switch<?php echo $is_pro_locked ? ' eas-slider-pro-locked' : ''; ?>">
												<?php if ( $is_pro_locked ) : ?>
													<span class="eas-pro-knob-crown">
														<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="#334155" viewBox="0 0 256 256"><path d="M248,80a28,28,0,1,0-51.12,15.77l-26.79,33L146,73.4a28,28,0,1,0-36.06,0L85.91,128.74l-26.79-33a28,28,0,1,0-26.6,12L47,194.63A16,16,0,0,0,62.78,208H193.22A16,16,0,0,0,209,194.63l14.47-86.85A28,28,0,0,0,248,80ZM128,40a12,12,0,1,1-12,12A12,12,0,0,1,128,40ZM24,80A12,12,0,1,1,36,92,12,12,0,0,1,24,80ZM193.22,192H62.78L48.86,108.52,81.79,149A8,8,0,0,0,88,152a7.83,7.83,0,0,0,1.08-.07,8,8,0,0,0,6.26-4.74l29.3-67.4a27,27,0,0,0,6.72,0l29.3,67.4a8,8,0,0,0,6.26,4.74A7.83,7.83,0,0,0,168,152a8,8,0,0,0,6.21-3l32.93-40.52ZM220,92a12,12,0,1,1,12-12A12,12,0,0,1,220,92Z"></path></svg>
													</span>
												<?php endif; ?>
											</span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="apexadfo-wizard-actions">
						<button type="button" class="apexadfo-btn apexadfo-btn-secondary apexadfo-prev-step">
							&larr; <?php esc_html_e( 'Previous', 'apex-addons-for-elementor' ); ?>
						</button>
						<button type="button" class="apexadfo-btn apexadfo-btn-primary apexadfo-save-finish">
							<span class="apexadfo-btn-text"><?php esc_html_e( 'Save & Continue', 'apex-addons-for-elementor' ); ?> &rarr;</span>
							<span class="apexadfo-btn-spinner" style="display:none;">
								<svg class="apexadfo-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path></svg>
							</span>
						</button>
					</div>
				</div>

				<!-- STEP 4: READY / FINISH -->
				<div class="apexadfo-wizard-step" id="apexadfo-step-4">
					<div class="apexadfo-wizard-hero">
						<div class="apexadfo-success-icon">
							<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
						</div>
						<h1><?php esc_html_e( 'You Are All Set!', 'apex-addons-for-elementor' ); ?></h1>
						<p><?php esc_html_e( 'Your Apex Addons configuration has been saved successfully. You are now ready to build stunning pages with Elementor.', 'apex-addons-for-elementor' ); ?></p>
						
						<div class="apexadfo-finish-cards">
							<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="apexadfo-finish-card">
								<div class="apexadfo-finish-icon">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
								</div>
								<h3><?php esc_html_e( 'Create New Page', 'apex-addons-for-elementor' ); ?></h3>
								<p><?php esc_html_e( 'Launch Elementor editor and start designing.', 'apex-addons-for-elementor' ); ?></p>
							</a>

							<a href="<?php echo esc_url( admin_url( 'admin.php?page=apexadfo-addons' ) ); ?>" class="apexadfo-finish-card">
								<div class="apexadfo-finish-icon">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
								</div>
								<h3><?php esc_html_e( 'Apex Dashboard', 'apex-addons-for-elementor' ); ?></h3>
								<p><?php esc_html_e( 'Manage global settings, submissions, and conditions.', 'apex-addons-for-elementor' ); ?></p>
							</a>
						</div>
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
}

// Initialize Singleton
Setup_Wizard::get_instance();
