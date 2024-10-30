<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL\modules\SelectImages;

/**
 * Select Images Module
 *
 * Select Images Direclty or by posts.
 */
class SelectImagesModule {

	/**
	 * Core Object.
	 *
	 * @var object
	 */
	private $core;

	/**
	 * Plugin Info.
	 *
	 * @var array
	 */
	private $plugin_info;

	/**
	 * Constrcutor.
	 *
	 * @param array  $plugin_info
	 * @param object $core
	 */
	public function __construct( $plugin_info, $core ) {
		$this->core        = $core;
		$this->plugin_info = $plugin_info;
	}

	/**
	 * Frontend Template.
	 *
	 * @return void
	 */
	public function template() {
		load_template(
			$this->plugin_info['path'] . 'modules/SelectImages/templates/main-template.php',
			false,
			array(
				'core'        => $this->core,
				'plugin_info' => $this->plugin_info,
				'module'      => $this,
			)
		);
	}

	/**
	 * Get CPTs names.
	 *
	 * @return array
	 */
	public function get_cpts() {
		$pypass_cpts = array( 'wp_template', 'wp_block', 'acf-field-group', 'acf-field', 'attachment', 'nav_menu_item', 'custom_css', 'product_variation', 'shop_order', 'shop_order_refund', 'shop_coupon' );
		return array_filter(
			get_post_types(
				array(
					'can_export' => true,
				)
			),
			function( $cpt_slug ) use ( $pypass_cpts ) {
				return ! in_array( $cpt_slug, $pypass_cpts );
			}
		);
	}

}
