<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL\pages;

use GPLSCore\GPLS_PLUGIN_ISSL\ImageSizes;
use GPLSCore\GPLS_PLUGIN_ISSL\modules\SelectImages\SelectImagesModule;

defined( 'ABSPATH' ) || exit;

/**
 * Sizes Controller Main Page.
 */
class SizesControllerMainPage extends AdminPage {

	/**
	 * Singular Instance.
	 *
	 * @var SizesControllerMainPage
	 */
	protected static $instance = null;

	/**
	 * page Path.
	 *
	 * @var string
	 */
	private static $_page_path;

	/**
	 * Select Images Module.
	 *
	 * @var SelectImagesModule.
	 */
	private $select_images_module;


	/**
	 * Constructor.
	 *
	 * @param Object    $core
	 * @param array     $plugin_info
	 * @param AdminPage $parent_page
	 * @param array     $other_pages
	 */
	public function __construct( $core, $plugin_info, $parent_page = null, $other_pages = array() ) {
		self::$core        = $core;
		self::$plugin_info = $plugin_info;
		parent::__construct();
		$this->hooks();
	}

    /**
     * Init Page Vars.
     *
     * @return void
     */
	protected function init() {
		$this->page_title    = esc_html__( 'Image Sizes Controller', 'image-sizes-controller' );
		$this->menu_title    = esc_html__( 'Image Sizes Controller', 'image-sizes-controller' );
		$this->cap           = 'manage_options';
		$this->menu_slug     = self::$plugin_info['name'];
		$this->page_slug     = admin_url( 'admin.php?page=' . $this->menu_slug );
		$this->parent_slug   = 'upload.php';
		$this->position      = 2;
		$this->template_name = 'main-subsizes-controller-template.php';
		self::$_page_path    = $this->page_slug;
		$this->tabs          = array(
			'sizes-controller' => array(
				'title'   => esc_html__( 'Sizes Controller', 'image-sizes-controller' ),
				'default' => true,
			),
			'bulk-subsizes'    => array(
				'title' => esc_html__( 'Bulk Sizes ', 'image-sizes-controller' ) . self::$core->new_keyword( 'Pro', true ),
			),
		);

		$this->select_images_module = new SelectImagesModule( self::$plugin_info, self::$core );
	}

	protected function enqueue_assets() {

	}

	/**
	 * Page Hooks.
	 *
	 * @return void
	 */
	protected function hooks() {
	}

	/**
	 * Page Assets.
	 *
	 * @return void
	 */
	protected function set_assets() {
		$this->assets = array(
			array(
				'type'   => 'css',
				'handle' => self::$plugin_info['name'] . '-bootstrap-css',
				'url'    => self::$core->core_assets_lib( 'bootstrap', 'css' ),
			),
			array(
				'type'   => 'css',
				'handle' => self::$plugin_info['name'] . '-settings-styles',
				'url'    => self::$plugin_info['url'] . 'assets/dist/css/styles.min.css',
			),
			array(
				'type'        => 'css',
				'handle'      => self::$plugin_info['name'] . '-select2-css',
				'url'         => self::$plugin_info['url'] . 'includes/Core/assets/libs/select2.min.css',
				'conditional' => array(
					'tab' => 'bulk-subsizes',
				),
			),
			array(
				'type'        => 'js',
				'handle'      => self::$plugin_info['name'] . '-select2-actions',
				'url'         => self::$plugin_info['url'] . 'includes/Core/assets/libs/select2.full.min.js',
				'conditional' => array(
					'tab' => 'bulk-subsizes',
				),
			),
            array(
				'type'        => 'css',
				'handle'      => self::$plugin_info['name'] . '-bulk-sizes-styles-css',
				'url'         => self::$plugin_info['url'] . 'assets/dist/css/bulk-sizes-styles.min.css',
				'conditional' => array(
					'tab' => 'bulk-subsizes',
				),
			),
		);
	}

	/**
	 * Page Template.
	 */
	public function page_output_function() {
		$args     = array(
			'template_page' => $this,
			'plugin_info'   => self::$plugin_info,
			'core'          => self::$core,
		);
		$template = self::$plugin_info['path'] . 'templates/pages/sizes-controller-template.php';
		if ( empty( $_GET['tab'] ) || ( ! empty( $_GET['tab'] ) ) && ( 'sizes-controller' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) ) {
			// Sizes Controller Page.
			$template = self::$plugin_info['path'] . 'templates/pages/sizes-controller-template.php';
		} elseif ( ! empty( $_GET['tab'] ) && ( 'bulk-subsizes' === sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) ) {
			// Bulk Subsizes Page.
			$template                    = self::$plugin_info['path'] . 'templates/pages/bulk-subsizes-template.php';
			$args['select_image_module'] = $this->select_images_module;
		}

		load_template(
			$template,
			true,
			$args
		);
	}
}
