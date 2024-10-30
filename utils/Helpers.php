<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL\Utils;

/**
 * Helpers Trait.
 */
trait Helpers {

	/**
	 * Loader HTML Code.
	 *
	 * @return void
	 */
	public static function loader_html( $prefix = null ) {
		?>
		<div class="loader w-100 h-100 position-absolute left-0 top-0 d-none <?php echo esc_attr( ! empty( $prefix ) ? $prefix . '-loader' : '' ); ?>">
			<div class="text-white wrapper text-center position-absolute d-block w-100 ">
				<img src="<?php echo esc_url_raw( admin_url( 'images/spinner-2x.gif' ) ); ?>"  />
			</div>
			<div class="overlay position-absolute d-block w-100 h-100"></div>
		</div>
		<?php
	}

	/**
	 * Convert Image URL to PATH.
	 *
	 * @param string $img_url
	 * @return string
	 */
	public static function convert_url_to_path( $img_url ) {
		$uploads  = wp_get_upload_dir();
		$img_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $img_url );
		return $img_path;
	}

	/**
	 * Check if current Page.
	 *
	 * @param string $page_slug Page Slug.
	 *
	 * @return boolean
	 */
	public function is_current_page( $page_slug = null ) {

		if ( wp_doing_ajax() && ! is_null( $this->action ) && ! empty( $_POST['pageAction'] ) && ( sanitize_text_field( wp_unslash( $_POST['pageAction'] ) ) === $this->action ) ) {
			return true;
		}

		if ( ! is_null( $page_slug ) ) {
			return is_page( $page_slug );
		}
		if ( property_exists( $this, 'page_slug' ) && ! is_null( $this->page_slug ) ) {
			return is_page( $this->page_slug );
		}
		return false;
	}

	/**
	 * Parse Arguments recursevly.
	 *
	 * @param array $args
	 * @param array $defaults
	 * @return array
	 */
	private static function rec_parse_args( $args, $defaults ) {
		$new_args = (array) $defaults;
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
				$new_args[ $key ] = self::rec_parse_args( $value, $new_args[ $key ] );
			} else {
				$new_args[ $key ] = $value;
			}
		}
		return $new_args;
	}

	/**
	 * Map sanitize function name to field type.
	 *
	 * @param string $field_type
	 * @return string
	 */
	protected static function sanitize_functions_mapping( $field_type ) {
		$fields_sanitize_mapping = array(
			'text'     => 'sanitize_text_field',
			'textarea' => 'sanitize_textarea',
			'url'      => 'esc_url_raw',
			'email'    => 'sanitize_email',
		);
		return ( ! empty( $fields_sanitize_mapping[ $field_type ] ) ? $fields_sanitize_mapping[ $field_type ] : 'sanitize_text_field' );
	}

	/**
	 * Apply function on array.
	 *
	 * @param array        $arr
	 * @param string|array $func_name
	 * @param string       $casting
	 * @return array
	 */
	protected static function mapping_func( $arr, $func_name, $casting = null ) {
		foreach ( $arr as $key => $value ) {
			$func_name = (array) $func_name;
			foreach ( $func_name as $func ) {
				$value = call_user_func( $func, $value );
				if ( ! is_null( $casting ) ) {
					settype( $value, $casting );
				}
			}
			$arr[ $key ] = $value;
		}
		return $arr;
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return boolean
	 */
	public static function is_woocommerce_active() {
		require_once \ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'woocommerce/woocommerce.php' ) && class_exists( '\WooCommerce' );
	}

	/**
	 * Check if current user is admin.
	 *
	 * @return boolean
	 */
	protected static function is_admin_user() {
		return current_user_can( 'administrator' );
	}

	/**
	 * Currency Symbols.
	 *
	 * @return array
	 */
	protected static function get_currency_symbols() {
		return array(
				'AED' => '&#x62f;.&#x625;',
				'AFN' => '&#x60b;',
				'ALL' => 'L',
				'AMD' => 'AMD',
				'ANG' => '&fnof;',
				'AOA' => 'Kz',
				'ARS' => '&#36;',
				'AUD' => '&#36;',
				'AWG' => 'Afl.',
				'AZN' => 'AZN',
				'BAM' => 'KM',
				'BBD' => '&#36;',
				'BDT' => '&#2547;&nbsp;',
				'BGN' => '&#1083;&#1074;.',
				'BHD' => '.&#x62f;.&#x628;',
				'BIF' => 'Fr',
				'BMD' => '&#36;',
				'BND' => '&#36;',
				'BOB' => 'Bs.',
				'BRL' => '&#82;&#36;',
				'BSD' => '&#36;',
				'BTC' => '&#3647;',
				'BTN' => 'Nu.',
				'BWP' => 'P',
				'BYR' => 'Br',
				'BYN' => 'Br',
				'BZD' => '&#36;',
				'CAD' => '&#36;',
				'CDF' => 'Fr',
				'CHF' => '&#67;&#72;&#70;',
				'CLP' => '&#36;',
				'CNY' => '&yen;',
				'COP' => '&#36;',
				'CRC' => '&#x20a1;',
				'CUC' => '&#36;',
				'CUP' => '&#36;',
				'CVE' => '&#36;',
				'CZK' => '&#75;&#269;',
				'DJF' => 'Fr',
				'DKK' => 'DKK',
				'DOP' => 'RD&#36;',
				'DZD' => '&#x62f;.&#x62c;',
				'EGP' => 'EGP',
				'ERN' => 'Nfk',
				'ETB' => 'Br',
				'EUR' => '&euro;',
				'FJD' => '&#36;',
				'FKP' => '&pound;',
				'GBP' => '&pound;',
				'GEL' => '&#x20be;',
				'GGP' => '&pound;',
				'GHS' => '&#x20b5;',
				'GIP' => '&pound;',
				'GMD' => 'D',
				'GNF' => 'Fr',
				'GTQ' => 'Q',
				'GYD' => '&#36;',
				'HKD' => '&#36;',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G',
				'HUF' => '&#70;&#116;',
				'IDR' => 'Rp',
				'ILS' => '&#8362;',
				'IMP' => '&pound;',
				'INR' => '&#8377;',
				'IQD' => '&#x62f;.&#x639;',
				'IRR' => '&#xfdfc;',
				'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				'ISK' => 'kr.',
				'JEP' => '&pound;',
				'JMD' => '&#36;',
				'JOD' => '&#x62f;.&#x627;',
				'JPY' => '&yen;',
				'KES' => 'KSh',
				'KGS' => '&#x441;&#x43e;&#x43c;',
				'KHR' => '&#x17db;',
				'KMF' => 'Fr',
				'KPW' => '&#x20a9;',
				'KRW' => '&#8361;',
				'KWD' => '&#x62f;.&#x643;',
				'KYD' => '&#36;',
				'KZT' => '&#8376;',
				'LAK' => '&#8365;',
				'LBP' => '&#x644;.&#x644;',
				'LKR' => '&#xdbb;&#xdd4;',
				'LRD' => '&#36;',
				'LSL' => 'L',
				'LYD' => '&#x644;.&#x62f;',
				'MAD' => '&#x62f;.&#x645;.',
				'MDL' => 'MDL',
				'MGA' => 'Ar',
				'MKD' => '&#x434;&#x435;&#x43d;',
				'MMK' => 'Ks',
				'MNT' => '&#x20ae;',
				'MOP' => 'P',
				'MRU' => 'UM',
				'MUR' => '&#x20a8;',
				'MVR' => '.&#x783;',
				'MWK' => 'MK',
				'MXN' => '&#36;',
				'MYR' => '&#82;&#77;',
				'MZN' => 'MT',
				'NAD' => 'N&#36;',
				'NGN' => '&#8358;',
				'NIO' => 'C&#36;',
				'NOK' => '&#107;&#114;',
				'NPR' => '&#8360;',
				'NZD' => '&#36;',
				'OMR' => '&#x631;.&#x639;.',
				'PAB' => 'B/.',
				'PEN' => 'S/',
				'PGK' => 'K',
				'PHP' => '&#8369;',
				'PKR' => '&#8360;',
				'PLN' => '&#122;&#322;',
				'PRB' => '&#x440;.',
				'PYG' => '&#8370;',
				'QAR' => '&#x631;.&#x642;',
				'RMB' => '&yen;',
				'RON' => 'lei',
				'RSD' => '&#1088;&#1089;&#1076;',
				'RUB' => '&#8381;',
				'RWF' => 'Fr',
				'SAR' => '&#x631;.&#x633;',
				'SBD' => '&#36;',
				'SCR' => '&#x20a8;',
				'SDG' => '&#x62c;.&#x633;.',
				'SEK' => '&#107;&#114;',
				'SGD' => '&#36;',
				'SHP' => '&pound;',
				'SLL' => 'Le',
				'SOS' => 'Sh',
				'SRD' => '&#36;',
				'SSP' => '&pound;',
				'STN' => 'Db',
				'SYP' => '&#x644;.&#x633;',
				'SZL' => 'E',
				'THB' => '&#3647;',
				'TJS' => '&#x405;&#x41c;',
				'TMT' => 'm',
				'TND' => '&#x62f;.&#x62a;',
				'TOP' => 'T&#36;',
				'TRY' => '&#8378;',
				'TTD' => '&#36;',
				'TWD' => '&#78;&#84;&#36;',
				'TZS' => 'Sh',
				'UAH' => '&#8372;',
				'UGX' => 'UGX',
				'USD' => '&#36;',
				'UYU' => '&#36;',
				'UZS' => 'UZS',
				'VEF' => 'Bs F',
				'VES' => 'Bs.S',
				'VND' => '&#8363;',
				'VUV' => 'Vt',
				'WST' => 'T',
				'XAF' => 'CFA',
				'XCD' => '&#36;',
				'XOF' => 'CFA',
				'XPF' => 'Fr',
				'YER' => '&#xfdfc;',
				'ZAR' => '&#82;',
				'ZMW' => 'ZK',
		);
	}

	/**
	 * Get Currency Symbol.
	 *
	 * @param string $currency
	 * @return string
	 */
	protected static function get_currency_symbol( $currency ) {
		$symbols = self::get_currency_symbols();
		$symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';
		return $symbol;
	}
}
