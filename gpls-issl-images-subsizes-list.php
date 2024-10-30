<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL;

/**
 * Plugin Name:     Image Sizes Controller [[GrandPlugins]]
 * Description:     Create custom image sizes, disable image sizes, List All image created subsizes, Fix missing image subsizes and more.
 * Author:          GrandPlugins
 * Author URI:      https://grandplugins.com
 * Text Domain:     image-sizes-controller
 * Std Name:        gpls-issl-images-subsizes-list
 * Version:         1.0.8
 *
 * @package         Images_Subsize_List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GPLSCore\GPLS_PLUGIN_ISSL\Core\Core;
use GPLSCore\GPLS_PLUGIN_ISSL\ImageSubsizes;
use GPLSCore\GPLS_PLUGIN_ISSL\ImageSizes;

use function GPLSCore\GPLS_PLUGIN_ISSL\pages\setup_pages;

if ( ! class_exists( __NAMESPACE__ . '\GPLS_ISSL_Class' ) ) :

	/**
	 * Main Class.
	 */
	class GPLS_ISSL_Class {

		/**
		 * The class Single Instance.
		 *
		 * @var object
		 */
		private static $instance;

		/**
		 * Plugin Info
		 *
		 * @var array
		 */
		private static $plugin_info;

		/**
		 * Core Object
		 *
		 * @var object
		 */
		private static $core;

		/**
		 * Initialize the class instance.
		 *
		 * @return object
		 */
		public static function init() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Core Actions Hook.
		 *
		 * @return void
		 */
		public static function core_actions( $action_type ) {
			self::includes();
			self::$core = Core::start( self::$plugin_info );
			if ( 'activated' === $action_type ) {
				self::$core->plugin_activated();
			} elseif ( 'deactivated' === $action_type ) {
				self::$core->plugin_deactivated();
			} elseif ( 'uninstall' === $action_type ) {
				self::$core->plugin_uninstalled();
			}
		}

		/**
		 * Disable Duplicate Free/Pro.
		 *
		 * @return void
		 */
		private static function disable_duplicate() {
			if ( ! empty( self::$plugin_info['duplicate_base'] ) && is_plugin_active( self::$plugin_info['duplicate_base'] ) ) {
				deactivate_plugins( self::$plugin_info['duplicate_base'] );
			}
		}

		/**
		 * Plugin Activated Function
		 *
		 * @return void
		 */
		public static function plugin_activated() {
			self::setup_plugin_info();
			self::disable_duplicate();
			self::core_actions( 'activated' );
			register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_ISSL_Class', 'plugin_uninstalled' ) );
		}

		/**
		 * Plugin Deactivated Hook.
		 *
		 * @return void
		 */
		public static function plugin_deactivated() {
			self::setup_plugin_info();
			self::core_actions( 'deactivated' );
		}

		/**
		 * Plugin Installed hook.
		 *
		 * @return void
		 */
		public static function plugin_uninstalled() {
			self::setup_plugin_info();
			self::core_actions( 'uninstall' );
		}

		/**
		 * Class Constructor.
		 */
		public function __construct() {
			self::setup_plugin_info();
			self::includes();
			$this->load_languages();
			$this->load();
		}

		/**
		 * Load Classes.
		 *
		 * @return void
		 */
		public function load() {
			self::$core = Core::start( self::$plugin_info );
			setup_pages( self::$core, self::$plugin_info );
			ImageSubsizes::init( self::$plugin_info, self::$core );
			ImageSizes::init( self::$plugin_info, self::$core );
		}

		/**
		 * Define Constants
		 *
		 * @param string $key
		 * @param string $value
		 * @return void
		 */
		public function define( $key, $value ) {
			if ( ! defined( $key ) ) {
				define( $key, $value );
			}
		}

		/**
		 * Set Plugin Info
		 *
		 * @return void
		 */
		public static function setup_plugin_info() {
			$plugin_data = get_file_data(
				__FILE__,
				array(
					'Version'     => 'Version',
					'Name'        => 'Plugin Name',
					'URI'         => 'Plugin URI',
					'SName'       => 'Std Name',
					'text_domain' => 'Text Domain',
				),
				false
			);

			self::$plugin_info = array(
				'id'              => 1303,
				'basename'        => plugin_basename( __FILE__ ),
				'version'         => $plugin_data['Version'],
				'name'            => $plugin_data['SName'],
				'text_domain'     => $plugin_data['text_domain'],
				'file'            => __FILE__,
				'plugin_url'      => $plugin_data['URI'],
				'public_name'     => $plugin_data['Name'],
				'path'            => trailingslashit( plugin_dir_path( __FILE__ ) ),
				'url'             => trailingslashit( plugin_dir_url( __FILE__ ) ),
				'options_page'    => $plugin_data['SName'],
				'localize_var'    => str_replace( '-', '_', $plugin_data['SName'] ) . '_localize_data',
				'type'            => 'free',
				'classes_prefix'  => 'gpls-issl',
				'classes_general' => 'gpls-general',
				'duplicate_base'  => 'gpls-issl-images-subsizes-list/gpls-issl-images-subsizes-list.php',
				'pro_link'        => 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free',
			);
		}

		/**
		 * Include plugin files
		 *
		 * @return void
		 */
		public static function includes() {
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'vendor/autoload.php';
		}

		/**
		 * Load languages Folder.
		 *
		 * @return void
		 */
		public function load_languages() {
			load_plugin_textdomain( self::$plugin_info['text_domain'], false, self::$plugin_info['path'] . 'languages/' );
		}

	}

	add_action( 'plugins_loaded', array( __NAMESPACE__ . '\GPLS_ISSL_Class', 'init' ), 1 );
	register_activation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_ISSL_Class', 'plugin_activated' ) );
	register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_ISSL_Class', 'plugin_deactivated' ) );

endif;
