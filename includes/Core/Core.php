<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL\Core;

defined( 'ABSPATH' ) || exit();

/**
 * Core Class
 */
class Core {

	/**
	 * Singleton Instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Plugin Info
	 *
	 * @var array
	 */
	protected $plugin_info;

	/**
	 * Core Path
	 *
	 * @var string
	 */
	public $core_path;

	/**
	 * Core URL
	 *
	 * @var string
	 */
	public $core_url;

	/**
	 * Core Assets PATH
	 *
	 * @var string
	 */
	public $core_assets_path;

	/**
	 * Core Assets URL
	 *
	 * @var string
	 */
	public $core_assets_url;

	/**
	 * Core Version.
	 *
	 * @var string
	 */
	private $version = '2.0';

	/**
	 * Plugins List.
	 *
	 * @var array
	 */
	private $plugins_list = array();

	/**
	 * Singleton Init.
	 *
	 * @param array $plugin_info
	 * @return self
	 */
	public static function start( $plugin_info ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $plugin_info );
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @param array $plugin_info
	 */
	private function __construct( $plugin_info ) {
		$this->init( $plugin_info );
		$this->hooks();
	}

	/**
	 * Init constants and other variables.
	 *
	 * = Set the Plugin Update URL
	 *
	 * @return void
	 */
	private function init( $plugin_info ) {
		$this->plugin_info      = $plugin_info;
		$this->core_path        = plugin_dir_path( __FILE__ );
		$this->core_url         = plugin_dir_url( __FILE__ );
		$this->core_assets_path = $this->core_path . 'assets';
		$this->core_assets_url  = $this->core_url . 'assets';
		$this->plugins_list     = array(
			'woo-advanced-captcha' => array(
				'title'     => 'Advanced Captcha',
				'desc'      => '',
				'thumbnail' => 'advanced-captcha.png',
				'pro_link'  => 'https://grandplugins.com/product/woo-advanced-captcha/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'woo-advanced-bundles' => array(
				'title'     => 'Woo Advanced Bundles',
				'desc'      => '',
				'thumbnail' => 'woo-advanced-bundles.png',
				'pro_link'  => 'https://grandplugins.com/product/woo-advanced-add-to-cart/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'woo-cart-tracker' => array(
				'title'     => 'Woo Cart Tracker',
				'desc'      => '',
				'thumbnail' => 'woo-cart-tracker.webp',
				'pro_link'  => 'https://grandplugins.com/product/woo-cart-tracker/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'woo-sales-popup' => array(
				'title'     => 'Woo Sales popup',
				'desc'      => '',
				'thumbnail' => 'woo-sales-popup.webp',
				'pro_link'  => 'https://grandplugins.com/product/woo-sales-notification/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'avif-support' => array(
				'title'     => 'AVIF Support',
				'desc'      => '',
				'thumbnail' => 'avif-support.png',
				'free_link' => 'https://wordpress.org/plugins/avif-support/',
			),
			'image-type-converter' => array(
				'title'     => 'Image Type Converter',
				'desc'      => '',
				'thumbnail' => 'image-type-converter.png',
				'free_link' => 'https://wordpress.org/plugins/image-type-converter/',
				'pro_link'  => 'https://grandplugins.com/product/wp-image-converter/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'best-seller-for-woocommerce' => array(
				'title'     => 'Best Sellers for WooCommerce',
				'desc'      => '',
				'thumbnail' => 'woo-best-seller.png',
				'free_link' => 'https://wordpress.org/plugins/best-seller-for-woocommerce/',
				'pro_link'  => 'https://grandplugins.com/product/woo-best-seller/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'pdf-password-protect' => array(
				'title'     => 'PDF Password Protect',
				'desc'      => '',
				'thumbnail' => 'pdf-password-protect.gif',
				'free_link' => 'https://wordpress.org/plugins/pdf-password-protect/',
				'pro_link'  => 'https://grandplugins.com/product/pdf-password-protect/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'simple-countdown-timer' => array(
				'title'     => 'Simple Countdown Timer',
				'desc'      => '',
				'thumbnail' => 'simple-countdown-timer.gif',
				'free_link' => 'https://wordpress.org/plugins/simple-countdown/',
				'pro_link'  => 'https://grandplugins.com/product/simple-countdown-timer/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'wp-watermark-pdf'       => array(
				'title'     => 'WP Watermark PDF',
				'desc'      => '',
				'thumbnail' => 'wp-watermark-pdf.png',
				'free_link' => 'https://wordpress.org/plugins/watermark-pdf/',
				'pro_link'  => 'https://grandplugins.com/product/wp-watermark-pdf/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'woo-cart-limiter'       => array(
				'title'     => 'WooCommerce Cart Limiter',
				'desc'      => '',
				'thumbnail' => 'woo-cart-limiter.png',
				'free_link' => 'https://wordpress.org/plugins/cart-limiter/',
				'pro_link'  => 'https://grandplugins.com/product/woo-cart-limiter/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'woo-coming-soon'        => array(
				'title'     => 'WooCommerce Coming Soon Products',
				'desc'      => '',
				'thumbnail' => 'woo-coming-soon.png',
				'free_link' => 'https://wordpress.org/plugins/coming-soon-products-for-woocommerce/',
				'pro_link'  => 'https://grandplugins.com/product/woo-coming-soon-products/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'wp-watermark-images'    => array(
				'title'     => 'WP Watermark Images',
				'desc'      => '',
				'thumbnail' => 'wp-watermark-images.png',
				'free_link' => 'https://wordpress.org/plugins/watermark-images-for-wp-and-woo-grandpluginswp/',
				'pro_link'  => 'https://grandplugins.com/product/wp-images-watermark/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'woo-quick-view'         => array(
				'title'     => 'WooCommerce Quick View and Buy Now',
				'desc'      => '',
				'thumbnail' => 'woo-quick-view-and-buy-now.png',
				'free_link' => 'https://wordpress.org/plugins/quick-view-and-buy-now-for-woocommerce/',
				'pro_link'  => 'https://grandplugins.com/product/quick-view-and-buy-now-for-woocommerce/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'wp-gif-editor'   => array(
				'title'     => 'WP GIF Uploader',
				'desc'      => '',
				'thumbnail' => 'wp-gif-editor.gif',
				'free_link' => 'https://wordpress.org/plugins/gif-uploader-wp-grandplugins',
				'pro_link'  => 'https://grandplugins.com/product/wp-gif-editor/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
			'woo-maintenance-mode' => array(
				'title'     => 'Woo Maintenance Mode',
				'desc'      => '',
				'thumbnail' => 'woo-maintenance-mode.png',
				'free_link' => 'https://wordpress.org/plugins/ultimate-maintenance-mode-for-woocommerce/',
				'pro_link'  => 'https://grandplugins.com/product/woocommerce-maintenance-mode-pro/?utm_source=free&utm_medium=sidebar&utm_content=' . $this->plugin_info['name'],
			),
		);
	}

	/**
	 * Core Actions Hook.
	 *
	 * @return void
	 */
	public function core_actions( $action_type ) {
		if ( 'activated' === $action_type ) {
			$this->plugin_activated();
		} elseif ( 'deactivated' === $action_type ) {
			$this->plugin_deactivated();
		} elseif ( 'uninstall' === $action_type ) {
			$this->plugin_uninstalled();
		}
	}

	/**
	 * Core Hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 1000, 1 );
		add_filter( 'plugin_action_links_' . $this->plugin_info['basename'], array( $this, 'premium_link' ), 10, 1 );
	}

	/**
	 * Premium Link
	 *
	 * @param array $links
	 * @return array
	 */
	public function premium_link( $links ) {
		if ( ! empty( $this->plugin_info['pro_link'] ) ) {
			$links[] = '<a target="_blank" href="' . esc_url_raw( $this->plugin_info['pro_link'] ) .'" ><b style="color:#31cf1e;font-weight:bolder;">' . esc_html__( 'Premium' ) . '</b></a>';
		}
		return $links;
	}

	/**
	 * Core Admin Scripts.
	 *
	 * @param string $hook_prefix
	 *
	 * @return void
	 */
	public function admin_scripts( $hook_suffix ) {
		global $pagenow;

		if ( ! wp_style_is( 'gpls-core-plugins-general-admin-head-styles-' . $this->version ) ) {
			wp_enqueue_style( 'gpls-core-plugins-general-admin-head-styles-' . $this->version, $this->core_assets_file( 'admin-head', 'css', 'css' ), array(), 'all' );
		}
	}

	/**
	 * Get Core assets file
	 *
	 * @param string $asset_file    Assets File Name
	 * @param string $type          Assets File Folder Type [ js / css /images / etc.. ]
	 * @param string $suffix        Assets File Type [ js / css / png /jpg / etc ... ]
	 * @param string $prefix        [ .min ]
	 * @return string
	 */
	public function core_assets_file( $asset_file, $type, $suffix, $prefix = 'min' ) {
		return $this->core_assets_url . '/dist/' . $type . '/' . $asset_file . ( ! empty( $prefix ) ? ( '.' . $prefix ) : '' ) . '.' . $suffix;
	}

	/**
	 * Get Core assets lib file
	 *
	 * @param string $asset_file    Assets File Name
	 * @param string $suffix        Assets File Type [ js / css / png /jpg / etc ... ]
	 * @param string $prefix        [ .min ]
	 * @return string
	 */
	public function core_assets_lib( $asset_file, $suffix, $prefix = 'min' ) {
		return $this->core_assets_url . '/libs/' . $asset_file . ( ! empty( $prefix ) ? ( '.' . $prefix ) : '' ) . '.' . $suffix;
	}

	/**
	 * Plugin Activation Hub function
	 *
	 * @return void
	 */
	public function plugin_activated() {

		do_action( $this->plugin_info['name'] . '-core-activated', $this );
	}

	/**
	 * Plugin Deactivation Hub function
	 *
	 * @return void
	 */
	public function plugin_deactivated() {

		do_action( $this->plugin_info['name'] . '-core-deactivated', $this );
	}

	/**
	 * Uninstall the plugin hook.
	 *
	 * @return void
	 */
	public function plugin_uninstalled() {

		do_action( $this->plugin_info['name'] . '-core-uninstalled', $this );
	}

	/**
	 * Pro Button.
	 *
	 * @param string $pro_link
	 * @param string $btn_title
	 * @param string $additional_classes
	 * @param string $additional_css
	 * @return void
	 */
	public function pro_btn( $pro_link = '', $btn_title = 'Pro', $additional_classes = '', $additional_css = '', $return = false ) {

		if ( empty( $pro_link ) && empty( $this->plugin_info['pro_link'] ) ) {
			return;
		}
		$pro_link = empty( $pro_link ) ? $this->plugin_info['pro_link'] : $pro_link;

		if ( $return ) {
			ob_start();
		}
		?>
		<a target="_blank" class="ms-2 btn gpls-permium-btn-wave btn-primary <?php echo esc_attr( $additional_classes ); ?>" href="<?php echo esc_url_raw( $pro_link ); ?>" style="<?php echo esc_attr( $additional_css ); ?>">
			<span class="pro-title" style="position:relative;z-index:10;color:#FFF;"><?php printf( esc_html__( '%s ➤' ), $btn_title ); ?></span>
			<span class="wave"></span>
		</a>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Default Footer Section
	 *
	 * @return void
	 */
	public function default_footer_section() {
		?>
		<style>
		#wpfooter {display: block !important;}
		.wrap.woocommerce {position: relative;}
		.gpls-contact {position: absolute; bottom: 0px; right: 20px; max-width: 350px; z-index: 1000;}
		.gpls-contact .link { color: #acde86!important; }
		.gpls-contact .text { background-color: #176875!important; }
		</style>
		<div class="gpls-contact">
		  <p class="p-3 bg-light text-center text text-white"><?php esc_html_e( 'in case you want to report a bug, submit a new feature or request a custom plugin, Please' ); ?> <a class="link" target="_blank" href="https://grandplugins.com/contact-us"> <?php esc_html_e( 'contact us' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * Review Link.
	 *
	 * @param string $review_link
	 * @return void
	 */
	public function review_notice( $review_link = '', $is_dismissible = true, $small = false ) {
		if ( empty( $review_link ) && empty( $this->plugin_info['review_link'] ) ) {
			return;
		}
		$review_link = ! empty( $review_link ) ? $review_link : $this->plugin_info['review_link'];
		?>
		<p class="notice notice-success <?php echo esc_attr( $small ? 'p-2' : 'p-4' ); ?> <?php echo esc_attr( $is_dismissible ? 'is-dismissible' : '' ); ?>">
			<?php esc_html_e( 'We would love your feedback. leaving ' ); ?>
			<a class="text-decoration-none" href="<?php echo esc_url_raw( $review_link ); ?>" target="_blank">
				<u><?php esc_html_e( 'a review is much appreciated' ); ?></u>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</a>
			<?php esc_html_e( ':) Thanks!' ); ?>
		</p>
		<?php
	}

	/**
	 * New Keyword.
	 *
	 * @param string $title
	 * @param boolean $return
	 *
	 * @return string|void
	 */
	public function new_keyword( $title = 'new', $return = true ) {
		if ( $return ) {
			ob_start();
		}
		?>
		<span class="<?php echo esc_attr( $this->plugin_info['classes_general'] . '-new-keyword' ); ?> ms-1"><?php esc_html_e( ! empty( $title ) ? $title : 'New' ); ?></span>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Plugins Sidebar.
	 *
	 * @return void
	 */
	public function plugins_sidebar( $exclude = '' ) {
		?>
		<div class="gpls-core-recommended-section">
			<h6 class="shadow-sm border p-3 shadow-sm border rounded"><?php esc_html_e( 'Empower Your Website with Our Additional Plugins' ); ?></h6>
			<div class="section-body bg-light p-3 shadow-sm border rounded">
				<ul class="plugins-list list-group">
					<?php
					foreach ( $this->plugins_list as $plugin_key => $plugin_arr ) :
						if ( $plugin_key === $exclude ) {
							continue;
						}
						?>
					<li class="plugin-list-item list-group-item border rounded">
						<h6 class="border rounded p-1 mb-2 text-center py-3 shadow-sm fw-bolder"><?php echo esc_html( $plugin_arr['title'] ); ?></h6>
						<?php if ( ! empty( $plugin_arr['thumbnail'] ) ) : ?>
							<img width="200px" src="<?php echo esc_url_raw( $this->plugin_info['url'] . 'includes/Core/assets/images/' . $plugin_arr['thumbnail'] ); ?>" class="thumbnail img-thumbanil my-3 mx-auto">
						<?php endif; ?>
						<p><?php echo esc_html( $plugin_arr['desc'] ); ?></p>
						<div class="row border p-1 rounded gx-0">
							<?php if ( ! empty( $plugin_arr['pro_link'] ) ) : ?>
							<div class="col d-flex justify-content-center border-end">
								<a class="btn btn-primary text-decoration-underline" target="_blank" href="<?php echo esc_url_raw( $plugin_arr['pro_link'] ); ?>"><strong><?php esc_html_e( 'Pro' ); ?></strong></a>
							</div>
							<?php endif; ?>
							<?php if ( ! empty( $plugin_arr['free_link'] ) ) : ?>
							<div class="col d-flex justify-content-center">
								<a class="btn btn-success text-decoration-underline" target="_blank" href="<?php echo esc_url_raw( $plugin_arr['free_link'] ); ?>"><?php esc_html_e( 'Free' ); ?></a>
							</div>
							<?php endif; ?>
						</div>
					</li>
					<?php endforeach; ?>
				</ul>
				<a class="btn btn-primary d-block mt-3" target="_blank" href="https://grandplugins.com/product-category/plugin/?utm_source=free&utm_medium=sidebar&utm_content=<?php echo esc_attr( $this->plugin_info['name'] ); ?>"><?php esc_html_e( 'Browse All Plugins' ); ?></a>
			</div>
		</div>
		<style>
			.gpls-core-recommended-section .plugins-list {
				display: flex;flex-direction: row;padding-left: 0;margin-bottom: 0;border-radius: 0.25rem;flex-wrap: wrap;
			}
			.gpls-core-recommended-section .plugin-list-item {
				width: 375px;display: flex;flex-direction: column;justify-content: space-between;margin: 10px;
			}
		</style>
		<?php
	}
}
