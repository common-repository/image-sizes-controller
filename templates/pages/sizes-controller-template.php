<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL;

use GPLSCore\GPLS_PLUGIN_ISSL\ImageSizes;

$core                  = $args['core'];
$plugin_info           = $args['plugin_info'];
$template_page         = $args['template_page'];
$registered_sizes      = ImageSizes::get_registered_sizes( true );
$new_image_sizes       = ImageSizes::get_custom_image_sizes();
$registered_sizes_only = array_diff_key( $registered_sizes, $new_image_sizes );
$settings              = ImageSizes::get_settings();
$hide_disabled_size    = ImageSizes::is_hide_disabled_sizes();

?>
<!-- Image Sizes Controller-->
<div class="wrap <?php echo esc_attr( $plugin_info['classes_prefix'] . '-paypal-plan-edit-wrapper' ); ?> mt-0 bg-light p-3 mt-5 min-vh-100">
<?php $template_page->output_page_tabs_nav( true ); ?>
    <?php do_action( $plugin_info['name'] . '-general-top-notices' ); ?>
	<div class="notices">
		<?php $template_page->show_messages(); ?>
	</div>

	<div class="container-fluid mt-5">
		<div class="row">
			<!-- Registered Sizes -->
			<div class="col-12">
				<div class="w-100 border p-2 my-4">
					<div class="col-12 accordion-container bg-white">
						<h6 class="border p-4 bg-light d-flex align-items-center">
							<span><?php esc_html_e( 'Registered Sizes', 'image-sizes-controller' ); ?></span>
							<span class="dashicons dashicons-arrow-down accordion-toggle accordion-toggler btn btn-primary rounded-circle ms-3"></span>
						</h6>
						<small class="ms-2 text-muted"><?php esc_html_e( 'Sizes created by the WordPress core or by the theme and other plugins', 'image-sizes-controller' ); ?></small>
						<div class="registered-sizes  accordion-body collapse p-0">
							<ul class="list-group list-group-horizontal active mt-4 p-1">
								<li class="col-md-4 list-group-item active flex-fill"><?php esc_html_e( 'Size Name', 'image-sizes-controller' ); ?></li>
								<li class="col-md-2 list-group-item active flex-fill"><?php esc_html_e( 'Width', 'image-sizes-controller' ); ?></li>
								<li class="col-md-2 list-group-item active flex-fill"><?php esc_html_e( 'Height', 'image-sizes-controller' ); ?></li>
								<li class="col-md-2 list-group-item active flex-fill"><?php esc_html_e( 'Crop', 'image-sizes-controller' ); ?></li>
							</ul>
							<?php
							foreach ( $registered_sizes_only as $size_name => $size_arr ) :
								$size_arr['name'] = $size_name;
								$is_custom        = ImageSizes::is_custom_image_size( $size_name );
								ImageSizes::image_size_html( $size_arr, true, $is_custom );
							endforeach;
							?>
						</div>
					</div>
				</div>
			</div>
			<!-- Cerate New Sizes -->
			<div class="col-12">
				<div class="w-100 border p-2 my-4">
					<div class="col-12 accordion-container bg-white">
						<h6 class="border p-4 bg-light d-flex align-items-center">
							<span><?php esc_html_e( 'New sizes', 'image-sizes-controller' ); ?></span>
							<span class="dashicons dashicons-arrow-down accordion-toggle accordion-toggler btn btn-primary rounded-circle ms-3"></span>
						</h6>
						<small class="ms-2 text-muted"><?php esc_html_e( 'New sizes list', 'image-sizes-controller' ); ?></small>
						<!-- New Size List -->
						<div class="new-sizes-wrapper mt-4 p-1">
							<div class="new-sizes accordion-body p-0 collapse">
								<ul class="list-group list-group-horizontal active d-flex flex-column new-sizes-list">
									<?php
									foreach ( $new_image_sizes as $size_name => $size_arr ) :
										ImageSizes::custom_image_size_html( $size_arr, true );
									endforeach;
									?>
								</ul>
							</div>
						</div>
						<!-- New Size Form -->
						<div class="create-new-subsize-wrapper mt-4">
							<div class="accordion-body collapse p-0 bg-white">
								<div class="create-subsize-section pb-3 my-3 border">
									<h6 class="border p-2 bg-light d-flex align-items-center mb-3"><?php esc_html_e( 'Create new size', 'image-sizes-controller' ); ?></h6>
									<div class="d-flex flex-column align-items-start p-2 create-new-subsize-form">
										<!-- Subsize Name -->
										<div class="row mb-4 w-100">
											<div class="col-md-2 mb-2">
												<span><?php esc_html_e( 'Size name', 'image-sizes-controller' ); ?></span>
											</div>
											<div class="col-md-10 mb-2">
												<input type="text" class="regular-text <?php echo esc_attr( $plugin_info['classes_prefix'] . '-subsize-name' ); ?>" >
											</div>
										</div>
										<!-- Width -->
										<div class="row mb-4 w-100">
											<div class="col-md-2 mb-2">
												<span><?php esc_html_e( 'Width' ); ?></span>
											</div>
											<div class="col-md-10 mb-2">
												<input min="0" type="number" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-subsize-w' ); ?>" style="width:100px;" value="0">
												<span><?php echo esc_html( 'px' ); ?></span>
											</div>
										</div>
										<!-- Height -->
										<div class="row mb-4 w-100">
											<div class="col-md-2 mb-2">
												<span><?php esc_html_e( 'Height' ); ?></span>
											</div>
											<div class="col-md-10 mb-2">
												<input min="0" type="number" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-subsize-h' ); ?>" style="width:100px;" value="0">
												<span><?php echo esc_html( 'px' ); ?></span>
											</div>
										</div>
										<!-- Crop -->
										<div class="row mb-4 w-100">
											<div class="col-md-2 mb-2">
												<span><?php echo esc_html( 'Crop (x, y)' ); ?></span>
											</div>
											<div class="col-md-10 mb-2">
												<label>
													<select class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-subsize-crop' ); ?>">
														<option value="1" selected><?php esc_html_e( 'No crop', 'image-sizes-controller' ); ?></option>
														<option value="2"><?php esc_html_e( 'Left Top', 'image-sizes-controller' ); ?></option>
														<option value="3"><?php esc_html_e( 'Center Top', 'image-sizes-controller' ); ?></option>
														<option value="4"><?php esc_html_e( 'Right Top', 'image-sizes-controller' ); ?></option>
														<option value="5"><?php esc_html_e( 'Left Center', 'image-sizes-controller' ); ?></option>
														<option value="6"><?php esc_html_e( 'Center Center', 'image-sizes-controller' ); ?></option>
														<option value="7"><?php esc_html_e( 'Right Center', 'image-sizes-controller' ); ?></option>
														<option value="8"><?php esc_html_e( 'Left Bottom', 'image-sizes-controller' ); ?></option>
														<option value="9"><?php esc_html_e( 'Center Bottom', 'image-sizes-controller' ); ?></option>
														<option value="10"><?php esc_html_e( 'Right Bottom', 'image-sizes-controller' ); ?></option>
													</select>
												</label>
											</div>
										</div>
										<!-- Force Upscale -->
										<div class="row mb-4 w-100">
											<div class="col-md-2 mb-2">
												<span><?php esc_html_e( 'Allow up-scale' ); ?></span>
											</div>
											<div class="col-md-10 mb-2">
												<input disabled type="checkbox" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-force-upscale' ); ?>" >
												<small class="text-muted"><?php esc_html_e( 'The size will be created even if the original image is smaller than the size dimensions', 'image-sizes-controller' ); ?></small>
												<?php $core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
											</div>
										</div>
										<!-- Force Exact Resize -->
										<div class="row mb-4 w-100">
											<div class="col-md-2 mb-2">
												<span><?php esc_html_e( 'Force Exact Dimensions' ); ?></span>
											</div>
											<div class="col-md-10 mb-2">
												<input disabled type="checkbox" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-force-exact-dimensions' ); ?>" >
												<small class="text-muted"><?php esc_html_e( 'The size will be created with the exact width and height ignoring the image aspect ratio', 'image-sizes-controller' ); ?></small>
												<?php $core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
											</div>
										</div>
									</div>
									<div class="create-subsize-action-wrapper d-flex justify-content-start mb-2">
										<button data-context="create-subsize" disabled class="d-block button button-primary mt-2 ms-2 mb-1 py-1 px-4 text-white <?php echo esc_attr( $plugin_info['classes_prefix'] . '-create-new-subsize' ); ?>"><?php esc_html_e( 'Create Subsize', 'image-sizes-controller' ); ?></button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Disables Sizes -->
			<div class="col-12 disabled-sizes-col">
				<div class="w-100 border p-2 my-4">
					<div class="col-12 accordion-container bg-white">
						<h6 class="border p-4 bg-light d-flex align-items-center">
							<span><?php esc_html_e( 'Disabled Sizes', 'image-sizes-controller' ); ?></span>
							<span class="dashicons dashicons-arrow-down accordion-toggle accordion-toggler btn btn-primary rounded-circle ms-3"></span>
							<span class="d-none unsaved-changes text-danger bg-white ps-1 pe-2 border rounded-pill pt-1 pb-1 ms-4 d-flex justify-content-center align-items-center">
								<span class="dashicons dashicons-info me-1"></span>
								<small><?php esc_html_e( 'Changes not saved', 'image-sizes-controller' ); ?></small>
							</span>
						</h6>
						<small class="ms-2 text-muted"><?php esc_html_e( 'Check sizes to disable them. disabled sizes will not be created for new uploaded images', 'image-sizes-controller' ); ?></small>
						<div class="disabled-sizes-wrapper p-1 mt-4">
							<div class="accordion-body p-0 collapse">
								<div class="disabled-sizes-section py-3 my-3 border">
									<ul class="list-group px-2 disabled-sizes-list">
									<?php ImageSizes::disabled_sizes_list_html(); ?>
									</ul>
								</div>

								<div class="my-3 ms-1 hide-disabled-size-option">
									<input type="checkbox" class="hide-disabled-sizes" <?php echo esc_attr( $hide_disabled_size ? 'checked' : '' ); ?> >
									<span class="ms-1"><?php esc_html_e( 'Hide disabled sizes from registered sizes', 'image-sizes-controller' ); ?></span>
									<span class="ms-3"> ( <small class="text-muted text-small"><?php esc_html_e( 'check to hide disabled sizes from global registered sizes. useful when using other related plugins', 'image-sizes-controller' ); ?></small> ) </span>
								</div>

								<div class="disable-sizes-action-wrapper d-flex justify-content-start mb-2">
									<button data-context="disable-sizes" class="d-block button button-primary mt-2 ms-2 mb-1 py-1 px-4 text-white <?php echo esc_attr( $plugin_info['classes_prefix'] . '-save-disabled-sizes' ); ?>"><?php esc_html_e( 'Save', 'image-sizes-controller' ); ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Conditional Apply -->
			<div class="col-12 conditional-apply-sizes-col">
				<div class="w-100 border p-2 my-4">
					<div class="col-12 accordion-container bg-white">
						<h6 class="border p-4 bg-light d-flex align-items-center">
							<span><?php esc_html_e( 'Conditional Apply', 'image-sizes-controller' ); ?></span>
							<?php $core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
							<span class="dashicons dashicons-arrow-down accordion-toggle accordion-toggler btn btn-primary rounded-circle ms-3"></span>
							<span class="d-none unsaved-changes text-danger bg-white ps-1 pe-2 border rounded-pill pt-1 pb-1 ms-4">
								<span class="dashicons dashicons-info me-1"></span>
								<small><?php esc_html_e( 'Changes not saved', 'image-sizes-controller' ); ?></small>
							</span>
						</h6>
						<small class="ms-2 text-muted"><?php esc_html_e( 'Create/Remove sizes only if the uploaded image meets selected filters', 'gpls-issl-images-subsizes-list' ); ?></small>
						<div class="conditional-apply-wrapper mt-4 p-1">
							<div class="accordion-body p-0 collapse">
								<div class="conditional-apply-section py-3 my-3 border">
									<ul class="list-group px-2 conditional-apply-list">
									<?php ImageSizes::conditional_apply_sizes_list_html(); ?>
									</ul>
								</div>
								<div class="disable-sizes-action-wrapper d-flex justify-content-start mb-2">
									<button data-context="disable-sizes" class="d-block button button-primary mt-2 ms-2 mb-1 py-1 px-4 text-white <?php echo esc_attr( $plugin_info['classes_prefix'] . '-conditional-apply-sizes' ); ?>"><?php esc_html_e( 'Save', 'image-sizes-controller' ); ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Subsizes Switch -->
			<div class="col-12 subsizes-switch-sizes-col">
				<div class="w-100 border p-2 my-4">
					<div class="col-12 accordion-container bg-white">
						<h6 class="border p-4 bg-light d-flex align-items-center">
							<span><?php esc_html_e( 'Sizes switch', 'gpls-issl-images-subsizes-list' ); ?></span>
							<?php $core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
							<span class="dashicons dashicons-arrow-down accordion-toggle accordion-toggler btn btn-primary rounded-circle ms-3"></span>
							<span><?php $core->new_keyword( 'New', false ); ?></span>
						</h6>
						<small class="ms-2 text-muted"><?php esc_html_e( 'Switch subsize with another in frontend.', 'gpls-issl-images-subsizes-list' ); ?></small>
						<div class="sizes-switch-wrapper mt-4 p-1">
							<div class="accordion-body p-0 collapse">
								<div class="sizes-switch-section py-3 my-3 border position-relative">
									<?php ImageSizes::sizes_switch_list(); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Additional Settings -->
			<div class="col-12 additional-settings-col">
				<div class="w-100 border p-2 my-4">
					<div class="col-12">
						<h6 class="border p-4 bg-light d-flex align-items-center bg-white">
							<span><?php esc_html_e( 'Additional Settings', 'image-sizes-controller' ); ?></span>
							<span class="d-none unsaved-changes text-danger bg-white ps-1 pe-2 border rounded-pill pt-1 pb-1 ms-4">
								<span class="dashicons dashicons-info me-1"></span>
								<small><?php esc_html_e( 'Changes not saved', 'image-sizes-controller' ); ?></small>
							</span>
						</h6>
						<div class="additional-settings-wrapper row">
							<div class="settings-list col-12">
								<div class="col-12 my-3 bg-white shadow-sm">
									<div class="container-fluid border mt-4">
										<!-- Disable Max image size threshold -->
										<div class="settings-group my-4 py-4 col-md-12">
											<div class="row">
												<div class="col-md-3">
													<h6 class="mb-1"><?php esc_html_e( 'Disable WP "BIG image" threshold', 'image-sizes-controller' ); ?></h6>
												</div>
												<div class="col-md-9">
													<input type="checkbox" class="regular-text disable-big-image-threshold" <?php echo esc_attr( $settings['disable_big_image_threshold'] ? 'checked' : '' ); ?> >
													<small><?php esc_html_e( 'Disable the scaling down that WordPress does for any image more than 2560px in width or height', 'image-sizes-controller' ); ?></small>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="additional-settings-action-wrapper d-flex justify-content-start mb-2">
								<button data-context="update-settings" class="d-block button button-primary mt-2 mb-1 py-1 px-4 text-white <?php echo esc_attr( $plugin_info['classes_prefix'] . '-update-settings' ); ?>"><?php esc_html_e( 'Save', 'image-sizes-controller' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>

		<!-- loader -->
		<div class="loader position-absolute w-100 h-100 bg-light left-0 top-0 opacity-75 d-none">
			<div class="spinner-holder position-relative w-100 h-100">
				<img class="position-fixed start-50 top-50 translate-middle" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="spinner">
			</div>
		</div>
		<!-- Toast -->
		<div class="toast <?php echo esc_attr( $plugin_info['classes_prefix'] . '-toast' ); ?> bg-primary border-0 text-white fixed-top collapse justify-content-center mt-5 align-items-center top-0 start-50 translate-middle-x" role="alert" aria-live="assertive" aria-atomic="true" >
			<div class="d-flex">
				<div class="toast-body">
					<p class="toast-msg m-0"></p>
				</div>
				<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
			</div>
		</div>
	</div>

	<div style="margin-top:120px;">
		<?php $core->plugins_sidebar(); ?>
		<?php $core->review_notice( 'https://wordpress.org/support/plugin/image-sizes-controller/reviews/#new-post' ); ?>
	</div>
</div>
