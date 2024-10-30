<?php

use GPLSCore\GPLS_PLUGIN_ISSL\ImageSizes;
use GPLSCore\GPLS_PLUGIN_ISSL\modules\SelectImages\Queries;

defined( 'ABSPATH' ) || exit;

$core        = $args['core'];
$plugin_info = $args['plugin_info'];
?>

<div class="select-images-module-wrapper">
	<div class="accordion mb-5">
		<h5 class="mb-3">
			<?php esc_html_e( 'Select Images', 'image-sizes-controller' ); ?>
		</h5>
		<!-- Select direct images. -->
		<div class="mb-3">
			<input disabled type="radio" id="select-images-direct" class="select-images-by-option" name="select-images-type" value="direct" >
			<label for="select-images-direct" class="mb-1"><?php esc_html_e( 'Select images directly', 'image-sizes-controller' ); ?></label>
			<small class="ms-4 d-block text-muted"><?php esc_html_e( 'Select images from media', 'image-sizes-controller' ); ?></small>
		</div>
		<!-- Select Images by post type -->
		<div class="mb-3">
			<input disabled type="radio" id="select-images-by-post-type" class="select-images-by-option" name="select-images-type" value="cpt" >
			<label for="select-images-by-post-type" class="mb-1"><?php esc_html_e( 'Select Images by posts', 'image-sizes-controller' ); ?></label>
			<small class="ms-4 d-block text-muted"><?php esc_html_e( 'Select images attached to posts [ images uploaded to posts ]', 'image-sizes-controller' ); ?></small>
		</div>
	</div>

	<!-- Sizes List select -->
	<div class="step-2 sizes-list collapse show">
		<div class="step-2-wrapper">
			<h4 class="mb-3">
				<span><?php esc_html_e( 'Select sizes', 'image-sizes-controller' ); ?></span>
			</h4>
			<?php $all_sizes = ImageSizes::get_all_sizes(); ?>
			<ul class="list-group px-2">
				<?php
				foreach ( $all_sizes as $size_name => $size_data ) :
					?>
				<li class="list-group-item mb-0 py-3 d-flex align-items-center flex-row justify-content-start bg-light">
					<input disabled type="checkbox" class="size-item my-0 ms-1 me-3" data-size="<?php echo esc_attr( $size_name ); ?>">
					<span><?php echo esc_html( ! empty( $size_data['title'] ) ? $size_data['title'] : $size_name ); ?></span>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<!-- Bulk Ation -->
	<div class="step-3 sizes-action collapse show">
		<div class="step-3-wrapper my-4">
			<h4 class="p-3"><?php esc_html_e( 'Select sizes action', 'image-sizes-controller' ); ?></h4>
			<div class="sizes-action px-4 my-3 border py-3 ms-3">
				<input disabled name="size-action" type="radio" class="sizes-action-field" value="add">
				<span><?php esc_html_e( 'Generate selected subsizes', 'image-sizes-controller' ); ?></span>
			</div>
			<div class="sizes-action px-4 my-3 border py-3 ms-3">
				<input disabled name="size-action" type="radio" class="sizes-action-field" value="remove">
				<span><?php esc_html_e( 'delete selected subsizes', 'image-sizes-controller' ); ?></span>
			</div>
		</div>
	</div>

</div>
