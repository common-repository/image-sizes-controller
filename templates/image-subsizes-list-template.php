<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL;

use GPLSCore\GPLS_PLUGIN_ISSL\ImageSubsizes;

$attachment_id = $attachment_post->ID;
?>

<div class="container-fluid">
	<!-- Subsizes Images List -->
	<div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 w-100 subsizes-list-container">
		<?php
		$img_metadata  = wp_get_attachment_metadata( $attachment_id );
		$missing_sizes = ImageSubsizes::get_image_missing_sizes( $attachment_id );
		if ( ! empty( $img_metadata['sizes'] ) ) :
			$sizes = $img_metadata['sizes'];
			foreach ( $sizes as $size_name => $size_arr ) :
				// Subsize Item HTML.
				ImageSubsizes::generated_subsize_html( $attachment_id, $img_metadata, $size_name, $size_arr, false, true );
			endforeach;
		endif;
		?>
	</div>
	<!-- Missing Subsizes -->
	<div class="col-12">
		<div class="row w-100 border p-2 my-4">
			<div class="col-12">
				<h6 class="border p-4 bg-light d-flex align-items-center">
					<span><?php esc_html_e( 'Missing sizes', 'image-sizes-controller' ); ?></span>
					<span class="ms-4 pro-feature">
						<?php self::$core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
					</span>
				</h6>
				<div class="missing-subsizes-list mt-4">
					<?php if ( empty( $missing_sizes ) ) : ?>
					<h6 class="text-muted muted p-2"><?php esc_html_e( 'No missing sizes', 'image-sizes-controller' ); ?></h6>
					<?php endif; ?>
					<ul class="list-group">
					<?php foreach ( $missing_sizes as $subsize_name => $subsize_arr ) : ?>
						<?php ImageSubsizes::missing_subsize_html( $attachment_id, $img_metadata, $subsize_name, $subsize_arr, true ); ?>
					<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<!-- Create Custom Size -->
	<div class="col-12">
		<div class="row w-100 border p-2 my-4">
			<div class="col-12">
				<h6 class="border p-4 bg-light">
					<span><?php esc_html_e( 'Create custom sub-size', 'image-sizes-controller' ); ?></span>
					<span class="ms-4 pro-feature">
						<?php self::$core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
					</span>
				</h6>
				<div class="create-custom-subsize mt-4">
					<div class="d-flex flex-column align-items-start p-2 create-new-subsize-form">
						<!-- Subsize Name -->
						<div class="row mb-4 w-100">
							<div class="col-md-2 mb-2">
								<span><?php esc_html_e( 'Size name', 'image-sizes-controller' ); ?></span>
							</div>
							<div class="col-md-10 mb-2">
								<input disabled type="text" class="regular-text <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-subsize-name' ); ?>" >
							</div>
						</div>
						<!-- Width -->
						<div class="row mb-4 w-100">
							<div class="col-md-2 mb-2">
								<span><?php esc_html_e( 'Width' ); ?></span>
							</div>
							<div class="col-md-10 mb-2">
								<input disabled min="0" type="number" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-subsize-w' ); ?>" style="width:100px;" value="0">
								<span><?php echo esc_html( 'px' ); ?></span>
							</div>
						</div>
						<!-- Height -->
						<div class="row mb-4 w-100">
							<div class="col-md-2 mb-2">
								<span><?php esc_html_e( 'Height' ); ?></span>
							</div>
							<div class="col-md-10 mb-2">
								<input disabled min="0" type="number" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-subsize-h' ); ?>" style="width:100px;" value="0">
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
									<select disabled class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-subsize-crop' ); ?>">
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
						<div class="create-subsize-action-wrapper d-flex justify-content-start mb-2">
							<button disabled data-attachmentid="<?php echo absint( esc_attr( $attachment_id ) ); ?>" data-context="create-subsize" disabled class="d-block button button-primary mt-2 ms-2 mb-1 py-1 px-4 text-white <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-create-custom-subsize' ); ?>"><?php esc_html_e( 'Create sub-size', 'image-sizes-controller' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="loader position-absolute w-100 h-100 bg-light left-0 top-0 opacity-75 d-none">
	<div class="spinner-holder position-relative w-100 h-100">
		<img class="position-fixed start-50 top-50 translate-middle" src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ); ?>" alt="spinner">
	</div>
</div>
<div class="toast <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-toast' ); ?> bg-primary border-0 text-white fixed-top collapse justify-content-center mt-5 align-items-center top-0 start-50 translate-middle-x" role="alert" aria-live="assertive" aria-atomic="true" >
	<div class="d-flex">
		<div class="toast-body">
			<p class="toast-msg m-0"></p>
		</div>
		<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
	</div>
</div>
