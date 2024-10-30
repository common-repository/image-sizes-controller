<?php

namespace GPLSCore\GPLS_PLUGIN_ISSL;

use GPLSCore\GPLS_PLUGIN_ISSL\ImageSubsizes;

/**
 * Generic Functions Trait.
 */
trait Utils {

	/**
	 * Allowed HTML Tags array for kses filter.
	 *
	 * @var array
	 */
	private static $allowed_html_tags = array();

	/**
	 * Get Image configs.
	 *
	 * @param string $image_path
	 * @return array
	 */
	public static function get_image_conf( $image_path ) {
		$img_details = getimagesize( $image_path );
		if ( ! $img_details ) {
			return false;
		}

		return array(
			'width'     => $img_details[0],
			'height'    => $img_details[1],
			'mime'      => $img_details['mime'],
			'dimension' => $img_details[0] . 'x' . $img_details[1],
			'ext'       => str_replace( 'image/', '', $img_details['mime'] ),
			'size'      => size_format( filesize( $image_path ) ),
		);
	}

	/**
	 * Add additional Tags to kses post.
	 *
	 * @param array $allowed_tags
	 * @return array
	 */
	public static function allow_kses_more_tags( $allowed_tags ) {
		if ( ! empty( self::$allowed_html_tags ) ) {
			$allowed_tags = array_merge( $allowed_tags, self::$allowed_html_tags );
		}
		return $allowed_tags;
	}

	/**
	 * Image configuration HTML.
	 *
	 * @param string $image_path
	 * @return void
	 */
	public static function image_conf_html( $image_path ) {
		if ( ! file_exists( $image_path ) ) {
			return;
		}
		$img_details = self::get_image_conf( $image_path );
		if ( ! $img_details ) {
			return;
		}
		?>
		<div class="d-flex justify-content-center flex-lg-row mx-auto flex-md-column flex-wrap">
			<span class="badge bg-light text-dark shadow-sm p-3 fs-6 text-left"><?php esc_html_e( 'Dimensions', 'image-sizes-controller' ); ?> : <?php echo esc_html( $img_details['dimension'] ); ?></span>
			<span class="badge bg-light text-dark shadow-sm p-3 fs-6 text-left"><?php esc_html_e( 'Size', 'image-sizes-controller' ); ?> : <?php echo esc_html( $img_details['size'] ); ?></span>
		</div>
		<?php
	}

	/**
	 * Invalid Data Ajax Response.
	 *
	 * @param string $message Response Message.
	 * @return void
	 */
	protected static function invalid_data_response( $message ) {
		wp_send_json_success(
			array(
				'result'  => false,
				'status'  => 'bg-danger',
				'message' => sprintf( esc_html( '%s', 'image-sizes-controller' ), $message ),
			)
		);
	}

	/**
	 * Get Post Types.
	 *
	 * @return array
	 */
	public static function get_cpts() {
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
