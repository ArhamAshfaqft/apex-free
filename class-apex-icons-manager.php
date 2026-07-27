<?php
namespace ArhamAshfaq\ApexAddonsForElementor\Free;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Apex Custom Icons Manager Class
 */
class Apex_Icons_Manager {

	/**
	 * Instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Get Instance
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
		add_filter( 'elementor/icons_manager/additional_tabs', [ $this, 'register_custom_icon_tabs' ] );
	}

	/**
	 * Register Custom Icon Tabs in Elementor
	 */
	public function register_custom_icon_tabs( $tabs ) {
		$icon_packs = [
			'themify' => [
				'label'         => esc_html__( 'Themify Icons', 'apex-addons-for-elementor' ),
				'prefix'        => '',
				'displayPrefix' => '',
				'labelIcon'     => 'ti-wand',
				'css'           => 'assets/css/themify.css',
				'json'          => 'assets/js/icons/themify.json',
			],
			'linearicons' => [
				'label'         => esc_html__( 'Linearicons (Free)', 'apex-addons-for-elementor' ),
				'prefix'        => 'lnr-',
				'displayPrefix' => 'lnr',
				'labelIcon'     => 'lnr lnr-linearicons',
				'css'           => 'assets/css/linearicons.css',
				'json'          => 'assets/js/icons/linearicons.json',
			],
			'simpleline' => [
				'label'         => esc_html__( 'Simple Line Icons', 'apex-addons-for-elementor' ),
				'prefix'        => 'icon-',
				'displayPrefix' => 'simple-line',
				'labelIcon'     => 'icon-user',
				'css'           => 'assets/css/simple-line-icons.css',
				'json'          => 'assets/js/icons/simpleline.json',
			],
			'lineawesome' => [
				'label'         => esc_html__( 'Line Awesome', 'apex-addons-for-elementor' ),
				'prefix'        => 'la-',
				'displayPrefix' => 'la',
				'labelIcon'     => 'la la-line-awesome',
				'css'           => 'assets/css/line-awesome.min.css',
				'json'          => 'assets/js/icons/lineawesome.json',
			],
			'ion' => [
				'label'         => esc_html__( 'Ionicons', 'apex-addons-for-elementor' ),
				'prefix'        => 'ion-',
				'displayPrefix' => 'ion',
				'labelIcon'     => 'ion-md-ionic',
				'css'           => 'assets/css/ionicons.min.css',
				'json'          => 'assets/js/icons/ion.json',
			],
			'materialdesign' => [
				'label'         => esc_html__( 'Material Design Icons', 'apex-addons-for-elementor' ),
				'prefix'        => 'mdi-',
				'displayPrefix' => 'mdi',
				'labelIcon'     => 'mdi mdi-material-design',
				'css'           => 'assets/css/materialdesignicons.min.css',
				'json'          => 'assets/js/icons/materialdesign.json',
			],
			'elegant' => [
				'label'         => esc_html__( 'Elegant Icons', 'apex-addons-for-elementor' ),
				'prefix'        => '',
				'displayPrefix' => '',
				'labelIcon'     => 'arrow_up',
				'css'           => 'assets/css/elegant.css',
				'json'          => 'assets/js/icons/elegant.json',
			],
			'elusive' => [
				'label'         => esc_html__( 'Elusive Icons', 'apex-addons-for-elementor' ),
				'prefix'        => 'el-',
				'displayPrefix' => 'el',
				'labelIcon'     => 'el-heart',
				'css'           => 'assets/css/elusive-icons.min.css',
				'json'          => 'assets/js/icons/elusive.json',
			],
			'icofont' => [
				'label'         => esc_html__( 'Icofont', 'apex-addons-for-elementor' ),
				'prefix'        => 'icofont-',
				'displayPrefix' => 'icofont',
				'labelIcon'     => 'icofont-bell',
				'css'           => 'assets/css/icofont.min.css',
				'json'          => 'assets/js/icons/icofont.json',
			],
			'icofont-duotone' => [
				'label'         => esc_html__( 'Icofont - Duotone', 'apex-addons-for-elementor' ),
				'prefix'        => 'icofont-duotone icofont-',
				'displayPrefix' => 'icofont',
				'labelIcon'     => 'icofont-duotone icofont-add-users',
				'css'           => 'assets/css/icofont.min.css',
				'json'          => 'assets/js/icons/icofont-duotone.json',
			],
			'icomoon' => [
				'label'         => esc_html__( 'IcoMoon Icons', 'apex-addons-for-elementor' ),
				'prefix'        => 'icon-',
				'displayPrefix' => 'icon',
				'labelIcon'     => 'icon-home',
				'css'           => 'assets/css/icomoon.css',
				'json'          => 'assets/js/icons/icomoon.json',
			],
			'iconic' => [
				'label'         => esc_html__( 'Iconic Icons', 'apex-addons-for-elementor' ),
				'prefix'        => 'iconic-',
				'displayPrefix' => 'iconic',
				'labelIcon'     => 'iconic-home',
				'css'           => 'assets/css/iconic.css',
				'json'          => 'assets/js/icons/iconic.json',
			],
			'devicons' => [
				'label'         => esc_html__( 'Devicons Icons', 'apex-addons-for-elementor' ),
				'prefix'        => 'devicons-',
				'displayPrefix' => 'devicons',
				'labelIcon'     => 'devicons-wordpress',
				'css'           => 'assets/css/devicons.min.css',
				'json'          => 'assets/js/icons/devicons.json',
			],
			'openiconic' => [
				'label'         => esc_html__( 'Open Iconic', 'apex-addons-for-elementor' ),
				'prefix'        => 'oi-',
				'displayPrefix' => 'oi',
				'labelIcon'     => 'oi-aperture',
				'css'           => 'assets/css/open-iconic.css',
				'json'          => 'assets/js/icons/openiconic.json',
			],
			'line' => [
				'label'         => esc_html__( 'Line Icons', 'apex-addons-for-elementor' ),
				'prefix'        => 'lni-',
				'displayPrefix' => 'lni',
				'labelIcon'     => 'lni-home',
				'css'           => 'assets/css/lineicons.css',
				'json'          => 'assets/js/icons/line.json',
			],
			'phosphor_regular' => [
				'label'         => esc_html__( 'Phosphor Regular', 'apex-addons-for-elementor' ),
				'prefix'        => 'ph-',
				'displayPrefix' => 'ph',
				'labelIcon'     => 'ph ph-smiley',
				'css'           => 'assets/css/phosphor-regular.css',
				'json'          => 'assets/js/icons/phosphor.json',
			],
			'phosphor_bold' => [
				'label'         => esc_html__( 'Phosphor Bold', 'apex-addons-for-elementor' ),
				'prefix'        => 'ph-bold ph-',
				'displayPrefix' => 'ph-bold',
				'labelIcon'     => 'ph-bold ph-smiley',
				'css'           => 'assets/css/phosphor-bold.css',
				'json'          => 'assets/js/icons/phosphor.json',
			],
			'phosphor_fill' => [
				'label'         => esc_html__( 'Phosphor Fill', 'apex-addons-for-elementor' ),
				'prefix'        => 'ph-fill ph-',
				'displayPrefix' => 'ph-fill',
				'labelIcon'     => 'ph-fill ph-smiley',
				'css'           => 'assets/css/phosphor-fill.css',
				'json'          => 'assets/js/icons/phosphor.json',
			],
			'phosphor_duotone' => [
				'label'         => esc_html__( 'Phosphor Duotone', 'apex-addons-for-elementor' ),
				'prefix'        => 'ph-duotone ph-',
				'displayPrefix' => 'ph-duotone',
				'labelIcon'     => 'ph-duotone ph-smiley',
				'css'           => 'assets/css/phosphor-duotone.css',
				'json'          => 'assets/js/icons/phosphor.json',
			],
			'phosphor_light' => [
				'label'         => esc_html__( 'Phosphor Light', 'apex-addons-for-elementor' ),
				'prefix'        => 'ph-light ph-',
				'displayPrefix' => 'ph-light',
				'labelIcon'     => 'ph-light ph-smiley',
				'css'           => 'assets/css/phosphor-light.css',
				'json'          => 'assets/js/icons/phosphor.json',
			],
			'phosphor_thin' => [
				'label'         => esc_html__( 'Phosphor Thin', 'apex-addons-for-elementor' ),
				'prefix'        => 'ph-thin ph-',
				'displayPrefix' => 'ph-thin',
				'labelIcon'     => 'ph-thin ph-smiley',
				'css'           => 'assets/css/phosphor-thin.css',
				'json'          => 'assets/js/icons/phosphor.json',
			],
		];

		foreach ( $icon_packs as $key => $config ) {
			// Check if this icon library is enabled in our settings page
			if ( Loader::is_addon_active( 'icon_' . $key ) ) {
				$tab_id = 'eas-icon-' . $key;
				$tabs[ $tab_id ] = [
					'name'          => $tab_id,
					'label'         => $config['label'],
					'labelIcon'     => $config['labelIcon'],
					'prefix'        => $config['prefix'],
					'displayPrefix' => $config['displayPrefix'],
					'url'           => plugins_url( $config['css'], __FILE__ ),
					'enqueue'       => [],
					'fetchJson'     => plugins_url( $config['json'], __FILE__ ),
					'ver'           => '1.0.43',
					'native'        => true,
				];
			}
		}

		return $tabs;
	}
}

// Initialize Custom Icons Manager
Apex_Icons_Manager::get_instance();
