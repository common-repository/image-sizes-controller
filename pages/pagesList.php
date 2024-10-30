<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL\pages;

use GPLSCore\GPLS_PLUGIN_ISSL\pages\SizesControllerMainPage;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Init Pages.
 */
function setup_pages( $core, $plugin_info ) {
	SizesControllerMainPage::get_instance( $core, $plugin_info );
}
