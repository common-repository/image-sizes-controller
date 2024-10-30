<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL;

use GPLSCore\GPLS_PLUGIN_ISSL\Utils;
use GPLSCore\GPLS_PLUGIN_ISSL\ImageSizes;

/**
 * Images Subsizes Class.
 */
class ImageSubsizes {

	use Utils;

	/**
	 * Singular Instance.
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Plugin Info Array.
	 *
	 * @var array
	 */
	protected static $plugin_info;

	/**
	 * Plugin Core Object.
	 *
	 * @var object
	 */
	protected static $core;

	/**
	 * Constructor.
	 *
	 * @param array $plugin_info Plugin Info Array.
	 */
	private function __construct( $plugin_info, $core ) {
		self::$core        = $core;
		self::$plugin_info = $plugin_info;
		$this->hooks();
	}

	/**
	 * Init Function.
	 *
	 * @return mixed
	 */
	public static function init( $plugin_info, $core ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $plugin_info, $core );
		}
		return self::$instance;
	}

	/**
	 * Hooks Function.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'register_image_subsizes_metabox' ), 1000, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'metabox_assets' ) );

		// Generate subsize image.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-subsize-image-generate', array( $this, 'ajax_generate_subsize_image' ) );

		// Delete image subsize.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-delete-image-subsize', array( $this, 'ajax_delete_subsize_image' ) );

		// media page toast.
		add_action( 'admin_footer', array( $this, 'media_page_footer_html' ) );
	}

	/**
	 * Media Page Footer HTML.
	 *
	 * @return void
	 */
	public function media_page_footer_html() {
		$current_screen = get_current_screen();
		if ( is_object( $current_screen ) && ( ( 'upload' === $current_screen->base ) && ( 'attachment' === $current_screen->post_type ) ) ) {
			?>
		<!-- Toast -->
		<div class="toast <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-toast' ); ?> bg-primary border-0 text-white fixed-top collapse justify-content-center mt-5 align-items-center top-0 start-50 translate-middle-x" role="alert" aria-live="assertive" aria-atomic="true" >
			<div class="d-flex">
				<div class="toast-body">
					<div class="toast-msg m-0"></div>
				</div>
				<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
			</div>
		</div>
			<?php
		}
	}

	/**
	 * Ajax Generate Sub-size Image.
	 *
	 * @return void
	 */
	public function ajax_generate_subsize_image() {
		check_admin_referer( self::$plugin_info['name'] . '-ajax-nonce', 'nonce' );
		if ( ! empty( $_POST['attachment_id'] ) && ! empty( $_POST['subsize'] ) ) {
			$registered_sizes = ImageSizes::get_registered_sizes( true );
			$attachment_id    = absint( sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) ) );
			$subsize_name     = sanitize_text_field( wp_unslash( $_POST['subsize'] ) );

			// Check if id is attachment image id.
			if ( ! wp_attachment_is_image( $attachment_id ) ) {
				self::invalid_data_response( 'invalid attachment ID' );
			}
			// Check if the subsize is registered.
			if ( empty( $registered_sizes[ $subsize_name ] ) ) {
				self::invalid_data_response( 'invalid subsize' );
			}

			require_once \ABSPATH . \WPINC . '/class-wp-image-editor.php';

			$img_metadata             = wp_get_attachment_metadata( $attachment_id );
			$attachment_image_details = self::get_image_details( $attachment_id );
			$generated_img_metadata   = self::create_subsize( $attachment_id, $attachment_image_details['path'], $img_metadata, $subsize_name, $registered_sizes[ $subsize_name ] );

			if ( is_wp_error( $generated_img_metadata ) ) {
				wp_send_json_success(
					array(
						'result'        => false,
						'attachment_id' => $attachment_id,
						'subsize'       => $subsize_name,
						'status'        => 'bg-danger',
						'message'       => esc_html__( 'image mime type is not supported', 'image-sizes-controller' ),
					)
				);
			}

			$generated_img_metadata['url'] = str_replace( wp_basename( $attachment_image_details['url'] ), $generated_img_metadata['sizes'][ $subsize_name ]['file'], $attachment_image_details['url'] );

			wp_send_json_success(
				array(
					'result'        => true,
					'attachment_id' => $attachment_id,
					'subsize'       => $subsize_name,
					'subsizeHTML'   => self::generated_subsize_html( $attachment_id, $generated_img_metadata, $subsize_name, $generated_img_metadata['sizes'][ $subsize_name ], true ),
					'message'       => esc_html__( 'subsize image has been generated successfully!', 'image-sizes-controller' ),
				)
			);
		}
		self::invalid_data_response( 'Invalid Data, please refresh the page and try again' );
	}

	/**
	 * Ajax Delete image Subsize.
	 *
	 * @return void
	 */
	public function ajax_delete_subsize_image() {
		check_admin_referer( self::$plugin_info['name'] . '-ajax-nonce', 'nonce' );
		if ( ! empty( $_POST['attachment_id'] ) && ! empty( $_POST['subsize'] ) ) {
			$attachment_id = absint( sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) ) );
			$subsize_name  = sanitize_text_field( wp_unslash( $_POST['subsize'] ) );

			// Check if id is attachment image id.
			if ( ! wp_attachment_is_image( $attachment_id ) ) {
				self::invalid_data_response( 'invalid attachment ID' );
			}
			require_once \ABSPATH . \WPINC . '/class-wp-image-editor.php';
			// Get the image details.
			$attachment_image_details = self::get_image_details( $attachment_id );
			$img_metadata             = wp_get_attachment_metadata( $attachment_id );
			$registered_subsizes      = wp_get_registered_image_subsizes();

			// Delete the subsize.
			self::delete_subsize( $attachment_image_details['path'], $attachment_id, $subsize_name );

			wp_send_json_success(
				array(
					'result'             => true,
					'attachment_id'      => $attachment_id,
					'subsize'            => $subsize_name,
					'missingSubsizeHTML' => ! empty( $registered_subsizes[ $subsize_name ] ) ? self::missing_subsize_html( $attachment_id, $img_metadata, $subsize_name, $registered_subsizes[ $subsize_name ] ) : '',
					'message'            => esc_html__( 'Image subsize has been deleted successfully!', 'image-sizes-controller' ),
				)
			);
		}
		self::invalid_data_response( 'Invalid Data, please refresh the page and try again' );
	}

	/**
	 * Resize Subsize Image.
	 *
	 * @param int    $attachment_id
	 * @param string $image_path Original Image Full PATH.
	 * @param array  $img_metadata Image Metdata Array.
	 * @param array  $subsize_details New Subsize Details.
	 * @return array|\WP_Error
	 */
	private static function create_subsize( $attachment_id, $image_path, $img_metadata, $subsize_name, $subsize_details ) {
		// 1) Create subsize from the original image.
		$editor = wp_get_image_editor( $image_path );
		if ( is_wp_error( $editor ) ) {
			return $editor;
		}
		if ( method_exists( $editor, 'make_subsize' ) ) {
			$new_size_meta = $editor->make_subsize( $subsize_details );
			if ( is_wp_error( $new_size_meta ) ) {
				return $new_size_meta;
			}
			// 2) Update the image metadata.
			$img_metadata['sizes'][ $subsize_name ] = $new_size_meta;
			wp_update_attachment_metadata( $attachment_id, $img_metadata );
		} else {
			$new_size_meta = $editor->multi_resize( array( $subsize_name => $subsize_details ) );
			if ( ! empty( $new_size_meta ) ) {
				return $new_size_meta;
			}
			// 2) Update the image metadata.
			$img_metadata['sizes'] = array_merge( $img_metadata['sizes'], $new_size_meta );
			wp_update_attachment_metadata( $attachment_id, $img_metadata );
		}

		// 3) Return the image metadata.
		return $img_metadata;
	}

	/**
	 * Delete Image Subsize.
	 *
	 * @param string $image_path
	 * @param int    $attachment_id
	 * @param string $subsize_name
	 * @return true|\WP_Error
	 */
	public static function delete_subsize( $image_path, $attachment_id, $subsize_name ) {
		// 1) Get the attachment metadata.
		$image_metadata = wp_get_attachment_metadata( $attachment_id );

		// 2) delete the subsize.
		if ( ! empty( $image_metadata['sizes'] ) && ! empty( $image_metadata['sizes'][ $subsize_name ] ) ) {
			$subsize_data = $image_metadata['sizes'][ $subsize_name ];

			// 3) Check if the subsize image is used by other size, then delete the image if not.
			$subsize_path = str_replace( wp_basename( $image_path ), $subsize_data['file'], $image_path );
			if ( file_exists( $subsize_path ) && ! self::is_subsize_image_mutual( $subsize_name, $image_metadata ) ) {
				unlink( $subsize_path );
			}

			// 4) unset the subsize.
			unset( $image_metadata['sizes'][ $subsize_name ] );

			// 5) update the image metadata.
			wp_update_attachment_metadata( $attachment_id, $image_metadata );
		}

		return true;
	}

	/**
	 * Is subsize Image mutual between multiple subsizes.
	 *
	 * @param string $subsize_name Target Sugbsize Name.
	 * @param array  $image_metadata Image metadata Array.
	 * @return boolean
	 */
	private static function is_subsize_image_mutual( $subsize_name, $image_metadata ) {
		$target_subsize_data = $image_metadata['sizes'][ $subsize_name ];
		unset( $image_metadata['sizes'][ $subsize_name ] );
		foreach ( $image_metadata['sizes'] as $subsize_data ) {
			if ( ( $target_subsize_data['width'] === $subsize_data['width'] ) && ( $target_subsize_data['height'] === $subsize_data['height'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get Image Details.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array Image metadata Array.
	 */
	public static function get_image_details( $attachment_id ) {
		$img_full_path = wp_get_original_image_path( $attachment_id );
		$img_file_name = wp_basename( $img_full_path );
		$img_full_url  = wp_get_original_image_url( $attachment_id );
		$filetype      = wp_check_filetype( $img_file_name );

		return array(
			'id'        => $attachment_id,
			'path'      => $img_full_path,
			'url'       => $img_full_url,
			'filename'  => $img_file_name,
			'ext'       => $filetype['ext'],
			'mime_type' => $filetype['type'],
		);
	}

	/**
	 * Image Edit Page Assets.
	 *
	 * @return void
	 */
	public function metabox_assets() {
		$current_screen = get_current_screen();
		if ( is_object( $current_screen ) && ( ( 'post' === $current_screen->base ) && ( 'attachment' === $current_screen->post_type ) ) ) {
			wp_enqueue_style( self::$plugin_info['name'] . '-bootstrap-css', self::$core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_add_inline_style(
				self::$plugin_info['name'] . '-bootstrap-css',
				'
				#' . esc_html( self::$plugin_info['name'] ) . '-image-subsizes-list-metabox .new .card {
					border: 5px solid #48bb8b6e !important;
				}
				'
			);

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_script( self::$plugin_info['name'] . '-bootstrap-js', self::$core->core_assets_lib( 'bootstrap.bundle', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-actions', self::$plugin_info['url'] . 'assets/dist/js/admin/actions.min.js', array( 'jquery' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-actions',
				str_replace( '-', '_', self::$plugin_info['name'] . '_localize_vars' ),
				array(
					'name'                  => self::$plugin_info['name'],
					'prefix'                => self::$plugin_info['classes_prefix'],
					'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
					'spinner'               => admin_url( 'images/spinner.gif' ),
					'nonce'                 => wp_create_nonce( self::$plugin_info['name'] . '-ajax-nonce' ),
					'classes_prefix'        => self::$plugin_info['classes_prefix'],
					'subsizeGenerateAction' => self::$plugin_info['name'] . '-subsize-image-generate',
					'deleteSubsizeAction'   => self::$plugin_info['name'] . '-delete-image-subsize',
					'labels'                => array(
						'deleteSubsize' => esc_html__( 'You are about to delete an image subsize. proceed?', 'image-sizes-controller' ),
					),
				)
			);
		}
	}

	/**
	 * Register Images Sub-sizes metabox.
	 *
	 * @return void
	 */
	public function register_image_subsizes_metabox( $post_type, $post ) {
		if ( ! wp_attachment_is_image( $post ) || ( 'attachment' !== $post_type ) ) {
			return;
		}
		add_meta_box(
			self::$plugin_info['name'] . '-image-subsizes-list-metabox',
			esc_html__( 'Subsizes List', 'image-sizes-controller' ),
			array( $this, 'image_subsizes_list_metabox' ),
			'attachment',
			'normal',
			'low'
		);
	}

	/**
	 * Generated Subsize HTML.
	 *
	 * @param string $size_url Subsize URL.
	 * @return string
	 */
	public static function generated_subsize_html( $attachment_id, $img_metadata, $size_name, $size_arr, $is_new = false, $echo = false ) {
		$uploads                = wp_get_upload_dir();
		$img_sizes              = ImageSizes::get_custom_image_sizes();
		$original_relative_path = $img_metadata['file'];
		$original_path          = trailingslashit( $uploads['basedir'] ) . $original_relative_path;
		$img_url                = wp_get_attachment_url( $attachment_id );
		$size_url               = str_replace( wp_basename( $img_url ), $size_arr['file'], $img_url );
		$size_path              = str_replace( wp_basename( $original_relative_path ), $size_arr['file'], $original_path );
		$size_title             = $size_name;

		if ( ! file_exists( $size_path ) ) {
			$size_missing = true;
		} else {
			$size_missing = false;
		}
		$size_url = add_query_arg(
			array(
				'refresh' => wp_generate_password( 5, false, false ),
			),
			$size_url
		);

		if ( ! empty( $img_sizes[ $size_name ] ) ) {
			$size_title = $img_sizes[ $size_name ]['title'];
		}
		ob_start();
		?>
		<div class="col mb-4 subsize-item <?php echo esc_attr( $is_new ? 'new' : '' ); ?> justify-content-center d-flex" id="<?php echo esc_attr( 'subsize-' . $size_name . '-col' ); ?>">
			<div class="card h-100 shadow-sm border w-100">
				<div class="card-body d-flex flex-column justify-content-between">
					<p class="card-title text-center border p-4 bg-secondary text-white fw-bolder">
						<?php echo esc_html( $size_title ); ?>
					</p>
					<?php if ( $size_missing ) : ?>
					<div class="missing-subsize-image d-flex flex-column align-items-center">
						<span class="text-center alert alert-danger" role="alert"><?php esc_html_e( 'Subsize image is missing', 'image-sizes-controller' ); ?></span>
						<img width="100" height="100" src="<?php echo esc_url( self::$plugin_info['url'] . 'assets/images/missing.png' ); ?>" alt="image-subsize" class="card-img-bottom w-auto">
						<div class="subsize-action">
							<button data-context="subsize-image" data-attachmentid="<?php echo esc_attr( $attachment_id ); ?>" data-subsize="<?php echo esc_attr( $size_name ); ?>" class="mx-auto button button-primary mt-5 p-2 <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-missing-subsize-image-create' ); ?>"><?php esc_html_e( 'Create subsize image', 'image-sizes-controller' ); ?></button>
						</div>
					</div>
					<?php else : ?>
					<a class="align-self-center d-inline-block thumbnail border" href="<?php echo esc_url( $size_url ); ?>" target="_blank">
						<img width="150" height="150" src="<?php echo esc_url( $size_url ); ?>" alt="image-subsize" class="mx-auto">
					</a>
					<?php endif; ?>
					<!-- Image Details -->
					<div class="subsize-image-details">
						<div class="d-flex flex-row justify-content-between">
						<?php self::image_conf_html( $size_path ); ?>
						</div>
					</div>
					<!-- Actions -->
					<div class="subsize-action d-flex flex-column">
						<div class="btn-wrapper d-flex">
							<button data-context="delete-subsize" data-attachmentid="<?php echo esc_attr( $attachment_id ); ?>" data-subsize="<?php echo esc_attr( $size_name ); ?>" class="d-block mx-auto btn btn-danger mt-5 p-2 <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-delete-image-subsize' ); ?>" ><?php esc_html_e( 'Delete size', 'image-sizes-controller' ); ?></button>
							<button data-context="resize-subsize" data-attachmentid="<?php echo esc_attr( $attachment_id ); ?>" data-subsize="<?php echo esc_attr( $size_name ); ?>" class="d-block mx-auto btn btn-warning mt-5 p-2 text-white <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-resize-image-subsize' ); ?>"><?php esc_html_e( 'Resize', 'gpls-issl-images-subsizes-list' ); ?> <?php self::$core->new_keyword( 'Pro', false ); ?></button>
						</div>
						<div class="resize-subsize-box collapse">
							<?php $img_details = static::get_image_conf( $size_path ); ?>
							<div class="wrapper p-3 my-3 shadow-sm bg-light">
								<div class="row">
									<div class="col-md-6">
										<label>
											<span><?php esc_html_e( 'Width' ); ?>
											<input type="number" class="form-control resize-subsize-width" value="<?php echo esc_attr( $img_details['width'] ); ?>" />
										</label>
									</div>
									<div class="col-md-6">
										<label>
											<span><?php esc_html_e( 'Height' ); ?>
											<input type="number" class="form-control resize-subsize-height" value="<?php echo esc_attr( $img_details['height'] ); ?>" />
										</label>
									</div>
									<div class="col-md-12 mt-4 text-left">
										<span class="form-label col-md-6"><?php echo esc_html( 'Crop (x, y)' ); ?></span>
										<label class="col-md-10">
											<select class="resize-subsize-crop form-control">
												<option value="1" selected><?php esc_html_e( 'No crop', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="2"><?php esc_html_e( 'Left Top', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="3"><?php esc_html_e( 'Center Top', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="4"><?php esc_html_e( 'Right Top', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="5"><?php esc_html_e( 'Left Center', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="6"><?php esc_html_e( 'Center Center', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="7"><?php esc_html_e( 'Right Center', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="8"><?php esc_html_e( 'Left Bottom', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="9"><?php esc_html_e( 'Center Bottom', 'gpls-issl-images-subsizes-list' ); ?></option>
												<option value="10"><?php esc_html_e( 'Right Bottom', 'gpls-issl-images-subsizes-list' ); ?></option>
											</select>
										</label>
									</div>
									<div class="col-md-12 mt-4 text-left">
										<label>
											<span class="form-label col-md-6"><?php echo esc_html( 'Force exact dimensions?' ); ?></span>
											<input type="checkbox" class="form-control resize-subsize-exact" />
										</label>
									</div>
									<div class="d-flex mt-5">
										<button disabled data-attachmentid="<?php echo esc_attr( $attachment_id ); ?>" data-subsize="<?php echo esc_attr( $size_name ); ?>" class="d-block mx-auto btn btn-success  p-2 <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-apply-resize-image-subsize' ); ?>" ><?php esc_html_e( 'Apply resize' ); ?></button>
										<?php self::$core->pro_btn( '', 'Premium' ); ?>
									</div>
									<small class="text-small my-2"><?php esc_html_e( 'The subsize image will be resized but the image URL will stay the same in order to avoid any broken links.', 'gpls-issl-images-subsizes-list' ); ?></small>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		$result = ob_get_clean();
		if ( $echo ) {
			echo $result;
		} else {
			return $result;
		}
	}

	/**
	 * List Image Subsizes List metabox.
	 *
	 * @param \WP_Post $attachment_post Attachment Post.
	 * @return void
	 */
	public function image_subsizes_list_metabox( $attachment_post ) {
		if ( ! wp_attachment_is_image( $attachment_post->ID ) ) {
			return;
		}
		require_once self::$plugin_info['path'] . 'templates/image-subsizes-list-template.php';
	}

	/**
	 * Check if there is missing images for current image sizes.
	 *
	 * @param int $attachment_id
	 * @return array
	 */
	public static function get_broken_sizes_images( $attachment_id ) {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return array();
		}
		$img_metadata      = wp_get_attachment_metadata( $attachment_id );
		$uploads           = wp_upload_dir();
		$full_size_path    = trailingslashit( $uploads['basedir'] ) . $img_metadata['file'];
		$broken_sizes_imgs = array();
		foreach ( $img_metadata['sizes'] as $size_name => $size_data ) {
			$size_img_path = str_replace( wp_basename( $full_size_path ), $size_data['file'], $full_size_path );
			if ( ! file_exists( $size_img_path ) ) {
				$broken_sizes_imgs[ $size_name ] = array_merge(
					$size_data,
					array(
						'size_name' => $size_name,
						'path'      => $size_img_path,
					)
				);
			}
		}
		return $broken_sizes_imgs;
	}

	/**
	 * Get image missing sizes.
	 *
	 * @param int  $attachment_id
	 * @param bool $only_smaller
	 * @return array
	 */
	public static function get_image_missing_sizes( $attachment_id, $only_smaller = false ) {
		$img_metadata  = wp_get_attachment_metadata( $attachment_id );
		$missing_sizes = array_diff_key( ImageSizes::get_registered_sizes( true ), array_flip( array_keys( $img_metadata['sizes'] ) ) );
		if ( ! $only_smaller ) {
			return $missing_sizes;
		}
		foreach ( $missing_sizes as $size_name => $size_data ) {
			if ( self::is_subsize_bigger( $img_metadata, $size_data ) ) {
				unset( $missing_sizes[ $size_name ] );
			}
		}
		return $missing_sizes;
	}

	/**
	 * Missing Subsize Item HTML.
	 *
	 * @param int    $attachment_id
	 * @param array  $img_metadata
	 * @param string $subsize_name
	 * @param array  $subsize_arr
	 * @return string
	 */
	public static function missing_subsize_html( $attachment_id, $img_metadata, $subsize_name, $subsize_arr, $echo = false ) {
		ob_start();
		$subsize_title = $subsize_name;
		$img_sizes     = ImageSizes::get_custom_image_sizes();
		if ( ! empty( $img_sizes[ $subsize_name ] ) ) {
			$subsize_title = $img_sizes[ $subsize_name ]['title'];
		}
		?>
		<li id="missing-subsize-<?php echo esc_attr( $subsize_name ); ?>" class="missing-subsize-item list-group-item d-flex justify-content-between align-items-center">
			<div class="subsize-details">
				<strong><?php echo esc_html( $subsize_title ); ?></strong> <?php echo esc_html( '[' . $subsize_arr['width'] . 'x' . $subsize_arr['height'] . ']' ); ?>
				<?php if ( self::is_subsize_bigger( $img_metadata, $subsize_arr ) ) : ?>
					<span class="btn" type="button" data-bs-toggle="tooltip" data-bs-placement="right" title="<?php esc_attr_e( 'Subsize is bigger than the original image', 'image-sizes-controller' ); ?>">&#9888;</span>
				<?php endif; ?>
			</div>
			<div class="subsize-action">
				<button disabled class="disabled button"><?php esc_html_e( 'Create subsize', 'image-sizes-controller' ); ?></button>
			</div>
		</li>
		<?php
		$result = ob_get_clean();
		if ( $echo ) {
			echo $result;
		} else {
			return $result;
		}
	}

	/**
	 * Check if the subsize is bigger than the original image dimensions.
	 *
	 * @param array $image_meta Image Meta Array.
	 * @param array $subsize_data Subsize Data Array.
	 * @return boolean
	 */
	public static function is_subsize_bigger( $image_meta, $subsize_data ) {
		$orig_w = $image_meta['width'];
		$orig_h = $image_meta['height'];
		$dest_w = $subsize_data['width'];
		$dest_h = $subsize_data['height'];

		if ( empty( $dest_w ) ) {
			if ( $orig_h < $dest_h ) {
				return true;
			}
		} elseif ( empty( $dest_h ) ) {
			if ( $orig_w < $dest_w ) {
				return true;
			}
		} else {
			if ( $orig_w < $dest_w && $orig_h < $dest_h ) {
				return true;
			}
		}
		return false;
	}
}
