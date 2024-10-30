<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL;

use GPLSCore\GPLS_PLUGIN_ISSL\ImageSizes;

$core                  = $args['core'];
$plugin_info           = $args['plugin_info'];
$template_page         = $args['template_page'];
$select_image_module   = $args['select_image_module'];
$registered_sizes      = wp_get_registered_image_subsizes();
$new_image_sizes       = ImageSizes::get_custom_image_sizes();
$registered_sizes_only = array_diff_key( $registered_sizes, $new_image_sizes );
$settings              = ImageSizes::get_settings();
?>
<!-- Image Sizes Controller-->
<div class="wrap <?php echo esc_attr( $plugin_info['classes_prefix'] . '-paypal-plan-edit-wrapper' ); ?> mt-5 bg-light px-3 py-3">

	<?php $template_page->output_page_tabs_nav( true ); ?>


	<h1 class="p-4 my-5 bg-white"><?php esc_html_e( 'Apply subsizes actions on bulk images', 'image-sizes-controller' ); ?> <?php $core->pro_btn( '', 'Premium' ); ?>
</h1>

	<?php $select_image_module->template(); ?>

</div>
