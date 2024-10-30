<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL;

use GPLSCore\GPLS_PLUGIN_ISSL\Editor;
use GPLSCore\GPLS_PLUGIN_ISSL\Utils;

/**
 * Images Subsizes Class.
 */
class ImageSizes {

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
	 * Plugin Core object.
	 *
	 * @var object
	 */
	protected static $core;

	/**
	 * Image Sizes Meta Key.
	 *
	 * @var string
	 */
	private static $image_sizes_meta_key;

	/**
	 * Disabled Image Sizes Meta Key.
	 *
	 * @var string
	 */
	private static $disabled_image_sizes_meta_key;

	/**
	 * Hide Disabled Sizes from registered Meta Key.
	 *
	 * @var string
	 */
	private static $hide_disabled_sizes_meta_key;

	/**
	 * Settings Key.
	 *
	 * @var string
	 */
	private static $settings_key;

	/**
	 * Default Settings.
	 *
	 * @var array
	 */
	private static $default_settings = array(
		'disable_big_image_threshold' => false,
	);

	/**
	 * Crop Value Mapping.
	 *
	 * @var array
	 */
	private static $crop_mapping = array(
		0  => false,
		1  => false,
		2  => array( 'left', 'top' ),
		3  => array( 'center', 'top' ),
		4  => array( 'right', 'top' ),
		5  => array( 'left', 'center' ),
		6  => array( 'center', 'center' ),
		7  => array( 'right', 'center' ),
		8  => array( 'left', 'bottom' ),
		9  => array( 'center', 'bottom' ),
		10 => array( 'right', 'bottom' ),
	);

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
	 * Get Crop Mapping.
	 *
	 * @return array
	 */
	public static function get_crop_mapping() {
		return self::$crop_mapping;
	}

	/**
	 * Constructor.
	 *
	 * @param array $plugin_info Plugin Info Array.
	 */
	private function __construct( $plugin_info, $core ) {
		self::$plugin_info                            = $plugin_info;
		self::$core                                   = $core;
		self::$settings_key                           = self::$plugin_info['name'] . '-settings';
		self::$image_sizes_meta_key                   = self::$plugin_info['name'] . '-image-sizes-list';
		self::$disabled_image_sizes_meta_key          = self::$plugin_info['name'] . '-disabled-image-sizes-list';
		self::$hide_disabled_sizes_meta_key           = self::$plugin_info['name'] . '-hide-disabled-sizes';
		$this->hooks();
	}

	/**
	 * Hooks Function.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		// Add image size.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-add-image-size', array( $this, 'ajax_add_image_size' ) );

		// Delete image size.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-delete-image-size', array( $this, 'ajax_delete_image_size' ) );

		// Disable image sizes.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-disable-image-sizes', array( $this, 'ajax_disable_image_sizes' ) );

		// Update image size.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-update-image-size', array( $this, 'ajax_update_image_size' ) );

		// additional settings.
		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-settings', array( $this, 'ajax_update_settings' ) );

		// Hook the custom image sizes.
		add_filter( 'after_setup_theme', array( $this, 'hook_custom_image_sizes' ), PHP_INT_MAX );

		// List all image sizes in select image settings.
		add_filter( 'image_size_names_choose', array( $this, 'select_field_all_image_size' ), 1000, 1 );

		// Remove Disabled sizes before subsizing.
		add_filter( 'intermediate_image_sizes_advanced', array( get_called_class(), 'remove_disabled_sizes' ), 10000, 2 );

		// Remove disabled sizes from intermediate sizes.
		add_filter( 'intermediate_image_sizes', array( $this, 'hide_disabled_sizes' ), 100, 1 );

		// big image threshold filter.
		add_filter( 'big_image_size_threshold', array( $this, 'filter_big_img_threshold' ), 1000, 4 );

		// Main settings link.
		add_filter( 'plugin_action_links_' . self::$plugin_info['basename'], array( $this, 'main_settings_page_link' ), 1000 );
	}

	public function main_settings_page_link( $links ) {
		$links[] = '<a href="' . esc_url( admin_url('upload.php?page=' . self::$plugin_info['name'] ) ) . '" >' . esc_html__( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	 * Filter the big image threshold.
	 *
	 * @param int $threshold_val
	 * @param array $imgsize
	 * @param string $file
	 * @param int $attachment_id
	 * @return int
	 */
	public function filter_big_img_threshold( $threshold_val, $imgsize, $file, $attachment_id ) {
		$settings = self::get_settings();
		if ( is_array( $settings ) && $settings['disable_big_image_threshold'] ) {
			return false;
		}
		return $threshold_val;
	}

	/**
	 * Hide disabled Sizes from Registered Sizes.
	 *
	 * @param array $registered_sizes
	 * @return array
	 */
	public function hide_disabled_sizes( $registered_sizes ) {
		if ( ! empty( $GLOBALS[ self::$plugin_info['name'] . '-bypass-disabled-sizes' ] ) ) {
			return $registered_sizes;
		}
		if ( ! self::is_hide_disabled_sizes() ) {
			return $registered_sizes;
		}
		$disabled_sizes = self::get_disabled_image_sizes();
		return array_diff( $registered_sizes, $disabled_sizes );
	}

	/**
	 * Hide DIsabled Sizes.
	 *
	 * @return boolean
	 */
	public static function is_hide_disabled_sizes() {
		return get_option( self::$hide_disabled_sizes_meta_key, false );
	}

	/**
	 * Remove Disabled Sizes before making subsizing on uploaded images.
	 *
	 * @param array $new_sizes
	 * @param array $image_meta
	 * @return array
	 */
	public static function remove_disabled_sizes( $new_sizes, $image_meta ) {
		// Remove Disabled Sizes.
		$disabled_sizes = self::get_disabled_image_sizes();
		foreach ( $disabled_sizes as $size_name ) {
			if ( ! empty( $new_sizes[ $size_name ] ) ) {
				unset( $new_sizes[ $size_name ] );
			}
		}
		return $new_sizes;
	}

	/**
	 * Get Registered Sizes.
	 *
	 * @param boolean $include_disabled
	 * @return array
	 */
	public static function get_registered_sizes( $include_disabled = true ) {
		if ( $include_disabled ) {
			$GLOBALS[ self::$plugin_info['name'] . '-bypass-disabled-sizes' ] = true;
		}

		$registered_sizes = wp_get_registered_image_subsizes();

		unset( $GLOBALS[ self::$plugin_info['name'] . '-bypass-disabled-sizes' ] );

		return $registered_sizes;
	}

	/**
	 * Check if the size is disabled.
	 *
	 * @param string $size_name
	 * @return boolean
	 */
	public static function is_disabled_size( $size_name ) {
		$disabled_sizes = self::get_disabled_image_sizes();
		return in_array( $size_name, $disabled_sizes );
	}

	/**
	 * Settings Page Assets.
	 *
	 * @return void
	 */
	public function assets() {
		$current_screen = get_current_screen();
		if ( is_object( $current_screen ) && ( ( ( 'media_page_' . self::$plugin_info['options_page'] ) === $current_screen->base ) ) ) {
			wp_enqueue_style( self::$plugin_info['name'] . '-bootstrap-css', self::$core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-settings-styles', self::$plugin_info['url'] . 'assets/dist/css/styles.min.css', array(), self::$plugin_info['version'], 'all' );
			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			wp_enqueue_script( self::$plugin_info['name'] . '-bootstrap-js', self::$core->core_assets_lib( 'bootstrap.bundle', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-actions', self::$plugin_info['url'] . 'assets/dist/js/admin/settings-actions.min.js', array( 'jquery' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-actions',
				str_replace( '-', '_', self::$plugin_info['name'] . '_localize_vars' ),
				array(
					'name'                    => self::$plugin_info['name'],
					'prefix'                  => self::$plugin_info['classes_prefix'],
					'ajaxUrl'                 => admin_url( 'admin-ajax.php' ),
					'spinner'                 => admin_url( 'images/spinner.gif' ),
					'nonce'                   => wp_create_nonce( self::$plugin_info['name'] . '-ajax-image-sizes-nonce' ),
					'addImageSizeAction'      => self::$plugin_info['name'] . '-add-image-size',
					'deleteImageSizeAction'   => self::$plugin_info['name'] . '-delete-image-size',
					'disableImageSizesAction' => self::$plugin_info['name'] . '-disable-image-sizes',
					'updateSizeAction'        => self::$plugin_info['name'] . '-update-image-size',
					'settingsAction'          => self::$plugin_info['name'] . '-settings',
					'classes_prefix'          => self::$plugin_info['classes_prefix'],
					'labels'                  => array(
						'deleteSize' => esc_html__( 'You are about to delete this image size. proceed ?', 'image-sizes-controller' ),
						'createSize' => esc_html__( 'Create size', 'image-sizes-controller' ),
						'editSize'   => esc_html__( 'Edit size', 'image-sizes-controller' ),
					),
				)
			);
		}
	}

	/**
	 * Select all image sizes options for classis editor.
	 *
	 * @param array $sizes
	 * @return array
	 */
	public function select_field_all_image_size( $sizes ) {
		$img_sizes = self::get_custom_image_sizes();
		foreach ( $img_sizes as $size_name => $size_arr ) {
			$sizes[ $size_name ] = $size_arr['title'];
		}
		return $sizes;
	}

	/**
	 * Add all custom image sizes.
	 *
	 * @return void
	 */
	public function hook_custom_image_sizes() {
		$image_sizes = self::get_custom_image_sizes();
		foreach ( $image_sizes as $size_name => $size_arr ) {
			add_image_size( $size_name, $size_arr['width'], $size_arr['height'], $size_arr['crop'] );
		}
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public static function get_settings() {
		return get_option( self::$settings_key, self::$default_settings );
	}

	/**
	 * Ajax update Settings.
	 *
	 * @return void
	 */
	public function ajax_update_settings() {
		// 1) Check Nonce.
		check_admin_referer( self::$plugin_info['name'] . '-ajax-image-sizes-nonce', 'nonce' );

		// 2) check user cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$settings = self::$default_settings;

		// 3) Disable Threshold.
		if ( ! empty( $_POST['threshold_status'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['threshold_status'] ) ) ) {
			$settings['disable_big_image_threshold'] = true;
		}

		update_option( self::$settings_key, $settings );

		wp_send_json_success(
			array(
				'result'  => true,
				'message' => esc_html__( 'Settings have been saved successfully!', 'image-sizes-controller' ),
			)
		);
	}

	/**
	 * Ajax Add image Size.
	 *
	 * @return void
	 */
	public function ajax_add_image_size() {
		// 1) Check Nonce.
		check_admin_referer( self::$plugin_info['name'] . '-ajax-image-sizes-nonce', 'nonce' );

		// 2) check user cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			self::invalid_data_response( 'You are not allowed to perform this action' );
		}

		// 3) Check POST Data.
		if ( ! empty( $_POST['subsizeName'] ) && ! empty( $_POST['width'] ) && ! empty( $_POST['height'] ) ) {
			$subsize_title   = sanitize_text_field( wp_unslash( $_POST['subsizeName'] ) );
			$subsize_name    = sanitize_title( $subsize_title );
			$width           = absint( sanitize_text_field( wp_unslash( $_POST['width'] ) ) );
			$height          = absint( sanitize_text_field( wp_unslash( $_POST['height'] ) ) );
			$crop            = ! empty( $_POST['crop'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['crop'] ) ) ) : 1;
			$crop            = in_array( $crop, array_keys( self::$crop_mapping ) ) ? self::$crop_mapping[ $crop ] : self::$crop_mapping[1];

			// validate resize.
			if ( 0 === $width || 0 === $height ) {
				self::invalid_data_response( 'Invalid resize dimensions' );
			}

			$all_sizes = self::get_registered_sizes( true );

			if ( in_array( $subsize_name, array_keys( $all_sizes ) ) ) {
				self::invalid_data_response( 'Subsize name already exists!' );
			}

			$subsize_details = array(
				'name'     => $subsize_name,
				'title'    => $subsize_title,
				'width'    => $width,
				'height'   => $height,
				'crop'     => $crop,
			);

			// Add the image size.
			self::update_custom_image_sizes( $subsize_details );

			wp_send_json_success(
				array(
					'result'         => true,
					'subsize'        => $subsize_name,
					'newSizeHTML'    => self::custom_image_size_html( $subsize_details, false ),
					'disabledSizes'  => self::disabled_sizes_list_html( false ),
					'message'        => esc_html__( 'subsize image has been created successfully!', 'image-sizes-controller' ),
				)
			);
		}
		self::invalid_data_response( 'Invalid resize data' );
	}

	/**
	 * Ajax Update Image Size.
	 *
	 * @return void
	 */
	public function ajax_update_image_size() {
		// 3) Check POST Data.
		if ( ! empty( $_POST['subsizeName'] ) && ! empty( $_POST['width'] ) && ! empty( $_POST['height'] ) ) {
			$subsize_name    = sanitize_text_field( wp_unslash( $_POST['subsizeName'] ) );
			$width           = absint( sanitize_text_field( wp_unslash( $_POST['width'] ) ) );
			$height          = absint( sanitize_text_field( wp_unslash( $_POST['height'] ) ) );
			$crop            = ! empty( $_POST['crop'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['crop'] ) ) ) : 1;
			$crop            = in_array( $crop, array_keys( self::$crop_mapping ) ) ? self::$crop_mapping[ $crop ] : self::$crop_mapping[1];

			// validate resize.
			if ( 0 === $width || 0 === $height ) {
				self::invalid_data_response( 'Invalid resize dimensions' );
			}

			$img_sizes = self::get_custom_image_sizes();
			if ( empty( $img_sizes[ $subsize_name ] ) ) {
				self::invalid_data_response( 'Invalid size name' );
			}

			$subsize_data             = $img_sizes[ $subsize_name ];
			$subsize_data['width']    = $width;
			$subsize_data['height']   = $height;
			$subsize_data['crop']     = $crop;

			// Add the image size.
			self::update_custom_image_sizes( $subsize_data );

			wp_send_json_success(
				array(
					'result'  => true,
					'subsize' => $subsize_name,
					'message' => esc_html__( 'subsize image has been updated successfully!', 'image-sizes-controller' ),
				)
			);
		}
		self::invalid_data_response( 'Invalid resize data' );
	}

	/**
	 * Ajax Delete Image Size.
	 *
	 * @return void
	 */
	public function ajax_delete_image_size() {
		// 1) Check Nonce.
		check_admin_referer( self::$plugin_info['name'] . '-ajax-image-sizes-nonce', 'nonce' );

		// 2) check user cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			self::invalid_data_response( 'You are not allowed to perform this action' );
		}

		// 3) Check POST Data.
		if ( ! empty( $_POST['subsizeName'] ) ) {
			$subsize_name = sanitize_text_field( wp_unslash( $_POST['subsizeName'] ) );

			// Add the image size.
			self::update_custom_image_sizes( $subsize_name, 'remove' );

			wp_send_json_success(
				array(
					'result'         => true,
					'subsize'        => $subsize_name,
					'disabledSizes'  => self::disabled_sizes_list_html( false ),
					'message'        => esc_html__( 'Image size has been removed successfully!', 'image-sizes-controller' ),
				)
			);
		}
		self::invalid_data_response( 'Invalid subsize name' );
	}

	/**
	 * Ajax Disable image sizes.
	 *
	 * @return void
	 */
	public function ajax_disable_image_sizes() {
		// 1) Check Nonce.
		check_admin_referer( self::$plugin_info['name'] . '-ajax-image-sizes-nonce', 'nonce' );

		// 2) check user cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			self::invalid_data_response( 'You are not allowed to perform this action' );
		}

		// Check if hide disabled sizes.
		if ( ! empty( $_POST['hideDisabled'] ) ) {
			$is_hide_disabled = sanitize_text_field( wp_unslash( $_POST['hideDisabled'] ) );
			$is_hide_disabled = 'true' === $is_hide_disabled ? true : false;
			update_option( self::$hide_disabled_sizes_meta_key, $is_hide_disabled, true );
		}

		// 3) Check POST Data.
		if ( ! empty( $_POST['subsizesNames'] ) && is_array( $_POST['subsizesNames'] ) ) {
			$subsize_names = array_map( 'sanitize_text_field', wp_unslash( $_POST['subsizesNames'] ) );

			// Add the image size.
			self::update_disabled_image_sizes( $subsize_names );
			wp_send_json_success(
				array(
					'result'   => true,
					'subsizes' => $subsize_names,
					'message'  => esc_html__( 'Selected sizes have been disabled successfully!', 'image-sizes-controller' ),
				)
			);
		} else {
			// Add the image size.
			self::update_disabled_image_sizes( array() );
			wp_send_json_success(
				array(
					'result'   => true,
					'subsizes' => array(),
					'message'  => esc_html__( 'Selected sizes have been disabled successfully!', 'image-sizes-controller' ),
				)
			);
		}
	}

	/**
	 * Set disabled image sizes.
	 *
	 * @param array $subsizes_names Array of subsizes names.
	 * @return void
	 */
	private static function update_disabled_image_sizes( $subsizes_names ) {
		update_option( self::$disabled_image_sizes_meta_key, $subsizes_names );
	}

	/**
	 * Get Disabled image sizes.
	 *
	 * @return array
	 */
	public static function get_disabled_image_sizes() {
		return get_option( self::$disabled_image_sizes_meta_key, array() );
	}

	/**
	 * Image Size HTML.
	 *
	 * @param array $size_details Image Size Details Array.
	 * @param bool  $is_new  Is it new Size.
	 * @param bool  $is_echo echo or return
	 * @return void|string
	 */
	public static function image_size_html( $size_details, $is_echo = false, $is_custom = false ) {
		ob_start();
		$img_sizes  = self::get_custom_image_sizes();
		$size_title = $is_custom ? $img_sizes[ $size_details['name'] ]['title'] : $size_details['name'];
		?>
		<ul id="img-size-<?php echo esc_attr( $size_details['name'] ); ?>" class="list-group list-group-horizontal d-flex flex-wrap <?php echo esc_attr( $is_custom ? 'custom-size' : '' ); ?>">
			<li class="col-md-4 list-group-item flex-fill d-flex align-items-center mb-0">
				<?php echo esc_html( $size_title ); ?>
				<?php if ( self::is_disabled_size( $size_details['name'] ) ) : ?>
				<span class="disabled-title ms-3 badge bg-danger"><?php esc_html_e( 'Disabled', 'image-sizes-controller' ); ?></span>
				<?php endif; ?>
			</li>
			<li class="col-md-2 list-group-item flex-fill d-flex align-items-center mb-0">
				<?php echo esc_html( $size_details['width'] ); ?>
			</li>
			<li class="col-md-2 list-group-item flex-fill d-flex align-items-center mb-0">
				<?php echo esc_html( $size_details['height'] ); ?>
			</li>
			<li class="col-md-2 list-group-item flex-fill d-flex align-items-center mb-0">
				<?php
				if ( is_array( $size_details['crop'] ) && ( 2 === count( $size_details['crop'] ) ) ) {
					echo esc_html( '( ' . $size_details['crop'][0] . ' , ' . $size_details['crop'][1] . ' )' );
				} else {
					if ( $size_details['crop'] ) {
						esc_html_e( 'Yes', 'image-sizes-controller' );
					} else {
						esc_html_e( 'No', 'image-sizes-controller' );
					}
				}
				?>
			</li>
			<?php if ( $is_custom ) : ?>
			<li class="col-md-2 list-group-item flex-fill d-flex align-items-center justify-content-between mb-0">
				<!-- Custom Size -->
				<div class="custom-size-edit-options-wrapper">
					<button class="btn btn-warning edit-size text-white" data-size="<?php echo esc_attr( $size_details['name'] ); ?>"><?php esc_html_e( 'Edit size', 'image-sizes-controller' ); ?></button>
					<button class="btn btn-danger remove-size" data-size="<?php echo esc_attr( $size_details['name'] ); ?>"><?php esc_html_e( 'Delete size', 'image-sizes-controller' ); ?></button>
				</div>
			</li>
			<?php endif; ?>
		</ul>
		<?php
		$result = ob_get_clean();
		if ( $is_echo ) {
			echo wp_kses_post( $result );
		} else {
			return $result;
		}
	}

	/**
	 * Image Size HTML.
	 *
	 * @param array $size_details Image Size Details Array.
	 * @param bool  $is_new  Is it new Size.
	 * @param bool  $is_echo echo or return
	 * @return void|string
	 */
	public static function custom_image_size_html( $size_details, $is_echo = false ) {
		ob_start();
		$size_name = $size_details['name'];
		?>
		<div class="size-item mb-3" id="img-size-<?php echo esc_attr( $size_name ); ?>" data-size="<?php echo esc_attr( $size_name ); ?>">
			<li class="size-item-head list-group-item active w-auto rounded d-flex flex-row justify-content-between m-0 align-items-center" type="button">
				<div class="size-title-wrapper d-flex align-items-center">
					<span class="size-title"><?php echo esc_html( $size_details['title'] ); ?></span>
					<button class="btn accordion-toggler ms-3 text-white remove-size" data-size="<?php echo esc_attr( $size_details['name'] ); ?>" ><?php esc_html_e( 'Delete', 'image-sizes-controller' ); ?></button>
					<span class="d-none unsaved-changes text-danger bg-white ps-1 pe-2 border rounded-pill pt-1 pb-1 ms-4 d-flex justify-content-center align-items-center">
						<span class="dashicons dashicons-info me-1"></span>
						<small><?php esc_html_e( 'Changes not saved', 'image-sizes-controller' ); ?></small>
					</span>
				</div>
				<span class="actions">
					<span type="button" class="dashicons dashicons-arrow-down size-item-toggle btn btn-primary rounded-circle ms-3 me-3 accordion-toggler"></span>
				</span>
			</li>
			<div class="size-item-details border my-2 collapse">
				<!-- Width -->
				<div class="row mb-4 w-100 px-3 py-2">
					<div class="col-md-2 mb-2">
						<span><?php esc_html_e( 'Width' ); ?></span>
					</div>
					<div class="col-md-10 mb-2">
						<input min="0" type="number" class="subsize-w" style="width:100px;" value="<?php echo absint( esc_attr( $size_details['width'] ) ); ?>">
						<span><?php echo esc_html( 'px' ); ?></span>
					</div>
				</div>
				<!-- Height -->
				<div class="row mb-4 w-100 px-3 py-2">
					<div class="col-md-2 mb-2">
						<span><?php esc_html_e( 'Height' ); ?></span>
					</div>
					<div class="col-md-10 mb-2">
						<input min="0" type="number" class="subsize-h" style="width:100px;" value="<?php echo absint( esc_attr( $size_details['height'] ) ); ?>">
						<span><?php echo esc_html( 'px' ); ?></span>
					</div>
				</div>
				<!-- Crop -->
				<div class="row mb-4 w-100 px-3 py-2">
					<div class="col-md-2 mb-2">
						<span><?php echo esc_html( 'Crop (x, y)' ); ?></span>
					</div>
					<div class="col-md-10 mb-2">
						<label>
							<select class="subsize-crop">
								<option <?php echo esc_attr( 1 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="1" selected><?php esc_html_e( 'No crop', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 2 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="2"><?php esc_html_e( 'Left Top', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 3 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="3"><?php esc_html_e( 'Center Top', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 4 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="4"><?php esc_html_e( 'Right Top', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 5 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="5"><?php esc_html_e( 'Left Center', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 6 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="6"><?php esc_html_e( 'Center Center', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 7 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="7"><?php esc_html_e( 'Right Center', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 8 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="8"><?php esc_html_e( 'Left Bottom', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 9 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="9"><?php esc_html_e( 'Center Bottom', 'image-sizes-controller' ); ?></option>
								<option <?php echo esc_attr( 10 === self::get_crop_index( $size_details['crop'] ) ? 'selected' : '' ); ?> value="10"><?php esc_html_e( 'Right Bottom', 'image-sizes-controller' ); ?></option>
							</select>
						</label>
					</div>
				</div>
				<!-- Force Upscale -->
				<div class="row mb-4 w-100 px-3 py-2">
					<div class="col-md-2 mb-2">
						<span><?php esc_html_e( 'Allow Upscale' ); ?></span>
					</div>
					<div class="col-md-10 mb-2">
						<input disabled type="checkbox" class="force-upscale" <?php echo ( esc_attr( ! empty( $size_details['upscale'] ) ? 'checked' : '' ) ); ?> >
						<small class="text-muted"><?php esc_html_e( 'The size will be created even if the original image is smaller than the size dimensions', 'image-sizes-controller' ); ?></small>
						<?php self::$core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
					</div>
				</div>
				<!-- Force Exact Resize -->
				<div class="row mb-4 w-100 px-3 py-2">
					<div class="col-md-2 mb-2">
						<span><?php esc_html_e( 'Force Exact Dimensions' ); ?></span>
					</div>
					<div class="col-md-10 mb-2">
						<input disabled type="checkbox" class="force-exact-dimensions" <?php echo ( esc_attr( ! empty( $size_details['exactDim'] ) ? 'checked' : '' ) ); ?> >
						<small class="text-muted"><?php esc_html_e( 'The size will be created with the exact width and height ignoring the image aspect ratio', 'image-sizes-controller' ); ?></small>
						<?php self::$core->pro_btn( 'https://grandplugins.com/product/image-sizes-controller/?utm_source=free', 'Premium' ); ?>
					</div>
				</div>
				<div class="update-subsize-action-wrapper d-flex justify-content-start mb-2 px-3 py-2">
					<button data-context="update-subsize" data-size="<?php echo esc_attr( $size_name ); ?>" class="d-block button button-primary mt-2 ms-2 mb-1 py-1 px-4 text-white <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-update-subsize' ); ?>"><?php esc_html_e( 'Update', 'image-sizes-controller' ); ?></button>
				</div>
			</div>
		</div>
		<?php
		$result = ob_get_clean();
		if ( $is_echo ) {
			echo $result;
		} else {
			return $result;
		}
	}

	public static function disabled_sizes_list_html( $is_echo = true ) {
		ob_start();
		$registered_sizes = self::get_all_sizes( true );
		$disabled_sizes   = self::get_disabled_image_sizes();
		foreach ( $registered_sizes as $size_name => $size_arr ) :
			?>
			<li class="list-group-item d-flex align-items-center my-0 flex-row justify-content-start">
				<input type="checkbox" class="disable-size" data-size="<?php echo esc_attr( $size_name ); ?>" <?php echo esc_attr( in_array( $size_name, $disabled_sizes ) ? 'checked' : '' ); ?> >
				<span class="mb-1"><?php echo esc_html( ! empty( $registered_sizes[ $size_name ]['title'] ) ? $registered_sizes[ $size_name ]['title'] : $size_name ); ?></span>
			</li>
			<?php
		endforeach;
		$result = ob_get_clean();
		if ( $is_echo ) {
			echo $result;
		} else {
			return $result;
		}
	}

	/**
	 * Get all sizes.
	 *
	 * @return array
	 */
	public static function get_all_sizes( $include_disabled = false ) {
		$registered_size = $include_disabled ? self::get_registered_sizes( true ) : wp_get_registered_image_subsizes();
		$custom_sizes    = self::get_custom_image_sizes();
		return array_merge( $registered_size, $custom_sizes );
	}

	/**
	 * Conditional Apply Sizes HTML.
	 *
	 * @param boolean $is_echo
	 * @return void
	 */
	public static function conditional_apply_sizes_list_html( $is_echo = true ) {
		$registered_sizes = self::get_all_sizes( true );
		ob_start();
		$index = 0;
		foreach ( $registered_sizes as $size_name => $size_arr ) :
			?>
			<li class="conditional-apply-size list-group-item d-flex align-items-center flex-row justify-content-between fw-bolder bg-light" data-size="<?php echo esc_attr( $size_name ); ?>">
				<span class="mb-1"><?php echo esc_html( ! empty( $registered_sizes[ $size_name ]['title'] ) ? $registered_sizes[ $size_name ]['title'] : $size_name ); ?></span>
				<span class="dashicons dashicons-arrow-down conditional-apply-toggler accordion-toggler btn btn-primary rounded-circle ms-3"></span>
			</li>
			<div class="conditional-apply-filters collapse <?php echo esc_attr( ! $index ? 'show' : '' ); ?>" >
				<div class="col-12 px-md-5 my-4 border py-4 bg-ligh">
					<!-- Action -->
					<div class="row mb-4 w-100 border p-2">
						<div class="col-md-2 mb-2 d-flex align-items-center">
							<span><?php esc_html_e( 'Action', 'gpls-issl-images-subsizes-list' ); ?></span>
						</div>
						<div class="col-md-10 mb-2">
							<label>
								<input type="radio" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-' . $size_name . '-pro-field' ); ?>" disabled="disabled">
								<span><?php esc_html_e( 'Create' ); ?></span>
							</label>
							<label class="ms-3">
								<input type="radio" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-' . $size_name . '-pro-field' ); ?>" disabled="disabled">
								<span><?php esc_html_e( 'Remove' ); ?></span>
							</label>
							<small class="text-muted d-block mt-1 mb-3"><?php esc_html_e( 'Choose the action ( Create - Remove ) size if the uploaded image matches selected filters.', 'gpls-issl-images-subsizes-list' ); ?></small>
						</div>
					</div>
					<!-- Image name prefix -->
					<div class="row mb-4 w-100 border p-2">
						<div class="col-md-2 mb-2 d-flex align-items-center">
							<span><?php esc_html_e( 'Image name prefix', 'gpls-issl-images-subsizes-list' ); ?></span>
						</div>
						<div class="col-md-10 mb-2">
							<input disabled="disabled" type="text" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field disabled' ); ?>">
							<small class="text-muted"><?php esc_html_e( 'The size will be ( created | removed ) for uploaded images when the image name starts with that prefix', 'gpls-issl-images-subsizes-list' ); ?><br/><?php esc_html_e( 'Example:', 'gpls-issl-images-subsizes-list' ); ?> <strong><?php echo esc_html( 'prefix-' ); ?></strong><?php esc_html_e( 'image-name.png', 'gpls-issl-images-subsizes-list' ); ?></small>
						</div>
					</div>
					<!-- Image path -->
					<div class="row mb-4 w-100 border p-2">
						<div class="col-md-2 mb-2 d-flex align-items-center">
							<div class="label">
								<span><?php esc_html_e( 'Image Path', 'gpls-issl-images-subsizes-list' ); ?></span>
								<small class="d-block mt-3 text-muted"><?php esc_html_e( 'Suitable for media directories', 'gpls-issl-images-subsizes-list' ); ?></small>
							</div>
						</div>
						<?php
							$uploads       = wp_get_upload_dir();
							$starting_path = substr( $uploads['basedir'], strpos( $uploads['basedir'], '/wp-content' ) );
						?>
						<div class="col-md-10 mb-2">
							<small class="d-block text-muted my-2"><?php esc_html_e( 'The size will be ( created | removed ) for uploaded images to any of added paths below', 'gpls-issl-images-subsizes-list' ); ?><span class="ms-3"><?php printf( esc_html( '...%s' ), trailingslashit( $starting_path ) ); esc_html_e( '[... IMAGE PATH ...]/image-name.png', 'gpls-issl-images-subsizes-list' ); ?></span><br/></small>
							<small class="d-block  p-2 bg-light my-1"><span class="me-2"><?php esc_html_e( 'Example:', 'gpls-issl-images-subsizes-list' ); ?></span> <?php printf( esc_html( '...%s' ), $starting_path ); esc_html_e( '/woo/products/img-name.png => The value will be: woo/products/', 'gpls-issl-images-subsizes-list' ); ?></small>
							<small class="d-block p-2 bg-light my-1"><?php printf( esc_html( 'Use slash only ( / ) for targeting the main "...%s" path.  Example: ...%simage-name.png  => The value witll be: /', 'gpls-issl-images-subsizes-list' ), trailingslashit( $starting_path ), trailingslashit( $starting_path ) ); ?></small>
							<!-- Image Paths -->
							<div class="border p-2 mt-4 img-paths-list">
								<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-img-path-field-placeholder' ); ?> d-none d-flex align-items-center my-2">
									<input type="text" class="form-control <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field' ); ?>" value="">
									<span class="ms-3 p-1 border <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field' ); ?> btn">❌</span>
								</div>
							</div>
							<button disabled="disabled" class="d-block btn btn-primary mb-1 mt-2 disabled <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-add-img-path' ); ?>"><?php esc_html_e( 'Add image path', 'gpls-issl-images-subsizes-list' ); ?></button>
						</div>
					</div>
					<!-- Upload location -->
					<div class="row mb-4 w-100 border p-2">
						<div class="col-md-2 mb-2 d-flex align-items-center">
							<span><?php esc_html_e( 'Upload Location', 'gpls-issl-images-subsizes-list' ); ?></span>
						</div>
						<div class="col-md-10 mb-2">
							<?php
							$cpts = self::get_cpts();
							foreach ( $cpts as $cpt_slug ) :
								$cpt_obj = get_post_type_object( $cpt_slug );
								?>
								<label class="cpt-field me-4">
									<input disabled="disabled" type="checkbox" class="disabled cpt-field-check <?php echo esc_attr( $cpt_slug . '-cpt-field-check' ); ?>" data-cpt="<?php echo esc_attr( $cpt_slug ); ?>" >
									<span><?php echo esc_html( $cpt_obj->label ); ?></span>
								</label>
							<?php endforeach; ?>
							<small class="d-block text-muted"><?php esc_html_e( 'The size will be ( created | removed ) for images when uploaded in posts at selected post types above', 'gpls-issl-images-subsizes-list' ); ?></small>
						</div>
					</div>
				</div>
			</div>
			<?php
			++$index;
		endforeach;
		$result = ob_get_clean();
		if ( $is_echo ) {
			echo $result;
		} else {
			return $result;
		}
	}

	/**
	 * Get Custom Image Sizes.
	 *
	 * @return array
	 */
	public static function get_custom_image_sizes() {
		return get_option( self::$image_sizes_meta_key, array() );
	}

	/**
	 * Check if the size needs to be exact dimensions.
	 *
	 * @param string $size_name
	 * @return boolean
	 */
	public static function is_exact_dim( $size_name ) {
		$sizes = self::get_custom_image_sizes();
		if ( ! empty( $sizes[ $size_name ] ) && ! empty( $sizes[ $size_name ]['exactDim'] ) && $sizes[ $size_name ]['exactDim'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the image size is a custom size.
	 *
	 * @param string $size_name
	 * @return boolean
	 */
	public static function is_custom_image_size( $size_name ) {
		$image_sizes = self::get_custom_image_sizes();
		return in_array( $size_name, array_keys( $image_sizes ) );
	}

	/**
	 * Update Custom Image Sizes list.
	 *
	 * @param array $image_size
	 * @return void
	 */
	protected static function update_custom_image_sizes( $image_size, $action = 'add' ) {
		$sizes_list = self::get_custom_image_sizes();
		if ( 'add' === $action ) {
			$sizes_list[ $image_size['name'] ] = $image_size;
		} elseif ( 'remove' === $action ) {
			global $_wp_additional_image_sizes;
			unset( $_wp_additional_image_sizes[ $image_size ] );
			unset( $sizes_list[ $image_size ] );
		}
		update_option( self::$image_sizes_meta_key, $sizes_list );
	}

	/**
	 * Get crop integer value based on the crop value.
	 *
	 * @param boolean|array $crop
	 * @return int
	 */
	public static function get_crop_index( $crop ) {
		if ( ! $crop ) {
			return 1;
		}
		if ( is_array( $crop ) && ( 2 === count( $crop ) ) ) {
			$crop_arrs = self::$crop_mapping;
			unset( $crop_arrs[1] );
			unset( $crop_arrs[0] );
			foreach ( $crop_arrs as $crop_index => $crop_value ) {
				if ( $crop_value[0] === $crop[0] && $crop_value[1] === $crop[1] ) {
					return $crop_index;
				}
			}
		}
		return 1;
	}


	/**
	 * Sizes switch list.
	 *
	 * @return void
	 */
	public static function sizes_switch_list() {
		?>
		<div class="sizes-switch">
			<div class="container py-5">
				<div class="sizes-switch-list">
					<?php self::get_sizes_switch_row( array(), 1 ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get Size Switch Row.
	 *
	 * @param array $size_switch
	 * @return void
	 */
	private static function get_sizes_switch_row( $size_switch = array(), $index = 0 ) {
		?>
		<div class="row size-switch-row <?php echo esc_attr( ! $index  ? 'row-placeholder d-none' : '' ); ?> position-relative py-5 bg-light mt-3" data-index="<?php echo esc_attr( $index ); ?>">
			<div class="col-md-6">
				<label>
					<span><?php esc_html_e( 'Subsize to replace', 'gpls-issl-images-subsizes-list' ); ?></span>
					<select class="source-subsize">
						<?php
						$all_sizes = self::get_all_sizes( true );
						foreach ( $all_sizes as $size_name => $size_arr ) :
							?>
							<option value="<?php echo esc_attr( $size_name ); ?>"><?php echo esc_html( ! empty( $size_arr[ $size_name ]['title'] ) ? $size_arr[ $size_name ]['title'] : $size_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<div class="col-md-6">
				<label>
					<span><?php esc_html_e( 'Target subsize', 'gpls-issl-images-subsizes-list' ); ?></span>
					<select class="target-subsize">
						<?php
						$all_sizes = self::get_all_sizes( true );
						foreach ( $all_sizes as $size_name => $size_arr ) :
							?>
							<option value="<?php echo esc_attr( $size_name ); ?>"><?php echo esc_html( ! empty( $size_arr[ $size_name ]['title'] ) ? $size_arr[ $size_name ]['title'] : $size_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<div class="col-12 mt-3">
				<div class="col-12 accordion-container bg-white">
					<h6 class="border p-4 bg-light d-flex align-items-center">
						<span><?php esc_html_e( 'Specific pages', 'gpls-issl-images-subsizes-list' ); ?></span>
					</h6>
					<small class="ms-2 text-muted"><?php esc_html_e( 'Apply the filter on specific pages only. leave empty to be applied at every page in frontend.', 'gpls-issl-images-subsizes-list' ); ?></small>
					<div class="specific-pages-wrapper mt-4 p-1">
						<div class="accordion-body collapse show">
							<div class="specific-pages container">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="position-absolute top-0 end-0 bg-black" style="border-radius:50%;padding:4px 5px;margin:5px;width:auto;">
				<button type="button" class="disabled size-switch-btn-close btn-close btn btn-close-white" aria-label="Close" style="opacity:1;"></button>
			</div>
		</div>
		<?php
	}
}
