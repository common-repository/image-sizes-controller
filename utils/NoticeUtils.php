<?php
namespace GPLSCore\GPLS_PLUGIN_ISSL\Utils;

/**
 * Helpers Trait.
 */
trait NoticeUtils {

	/**
	 * Message Array.
	 *
	 * @var array
	 */
	protected $notice_messages = array();

	/**
	 * Errors Array.
	 *
	 * @var array
	 */
	protected $notice_errors = array();

	/**
	 * Output messages + errors.
	 */
	public function show_messages() {
		if ( count( $this->notice_errors ) > 0 ) {
			foreach ( $this->notice_errors as $error ) {
				if ( ! empty( $error ) ) {
					echo '<div id="message" class="error notice inline is-dismissible"><p><strong>' . wp_kses_post( $error ) . '</strong></p></div>';
				}
			}
		} elseif ( count( $this->notice_messages ) > 0 ) {
			foreach ( $this->notice_messages as $message ) {
				if ( ! empty( $message ) ) {
					echo '<div id="message" class="updated notice inline is-dismissible"><p><strong>' . wp_kses_post( $message ) . '</strong></p></div>';
				}
			}
		}
	}

	/**
	 * Add Message.
	 *
	 * @param string $message
	 * @return void
	 */
	public function add_message( $message ) {
		$this->notice_messages[] = $message;
	}

	/**
	 * Add Error Message.
	 *
	 * @param string|array $message
	 * @return void
	 */
	public function add_error( $message ) {
		if ( is_array( $message ) ) {
			$this->notice_errors = array_merge( $this->notice_errors, $message );
		} else {
			$this->notice_errors[] = $message;
		}
	}


	/**
	 * Send AJax Response.
	 *
	 * @param string $message
	 * @param string $status
	 * @param string $context
	 * @return void
	 */
	protected function ajax_response( $message, $status = 'success', $context = '', $result = array(), $additional_data = array() ) {
		$response_arr = array(
			'status'  => $status,
			'result'  => $result,
			'context' => $context,
			'message' => ( 'success' === $status ) ? $this->success_message( $message ) : $this->error_message( $message ),
		);
		$response_arr = array_merge( $response_arr, $additional_data );
		wp_send_json_success( $response_arr );
	}

	/**
	 * Ajax Error Response.
	 *
	 * @param string $message
	 * @return void
	 */
	protected function ajax_error_response( $message ) {
		wp_send_json_error(
			array(
				'status'  => 'error',
				'context' => 'login',
				'message' => $this->error_message( $message ),
			)
		);
	}

	/**
	 * Expired Ajax Response.
	 *
	 * @return void
	 */
	protected function expired_response() {
		wp_send_json_success(
			array(
				'status'  => 'error',
				'context' => 'login',
				'message' => $this->error_message( 'The link has expired, please refresh the page!' ),
			)
		);
	}

	/**
	 * General Invalid Submitted Data Response.
	 *
	 * @return void
	 */
	protected function invalid_submitted_data_response() {
		wp_send_json_error(
			array(
				'message' => $this->error_message( 'Invalid submitted data' ),
			),
			400
		);
	}

	/**
	 * Error Message.
	 *
	 * @param string|array $message
	 * @param boolean $return
	 * @param string $html
	 * @return string|void
	 */
	public function error_message( $messages, $return = true, $html = '' ) {
		if ( ! is_array( $messages ) ) {
			$messages = (array) $messages;
		}
		if ( $return ) {
			ob_start();
		}
		?>
		<div class="<?php echo esc_attr( static::$plugin_info['classes_general'] . '-error-notice' ); ?> <?php echo esc_attr( static::$plugin_info['classes_general'] . '-notice' ); ?>">
			<ul class="errors-list">
				<?php foreach ( $messages as $message ) : ?>
				<li><?php printf( esc_html__( '%s', 'gpls-pyplss-paypal-subscriptions' ), $message ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php if ( ! empty( $html ) ) {
				wp_kses_post( $html );
			}
			?>
		</div>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Success Message.
	 *
	 * @param string|array $message
	 * @param boolean $return
	 * @param string $html
	 * @return string|void
	 */
	public function success_message( $messages, $return = true, $html = '' ) {
		if ( ! is_array( $messages ) ) {
			$messages = (array) $messages;
		}
		if ( $return ) {
			ob_start();
		}
		?>
		<div class="<?php echo esc_attr( static::$plugin_info['classes_general'] . '-success-notice' ); ?> <?php echo esc_attr( static::$plugin_info['classes_general'] . '-notice' ); ?>">
			<ul class="msgs-list">
				<?php foreach ( $messages as $message ) : ?>
				<li><?php printf( esc_html__( '%s', 'gpls-pyplss-paypal-subscriptions' ), $message ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php
			if ( ! empty( $html ) ) {
				wp_kses_post( $html );
			}
			?>
		</div>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Warning Message.
	 *
	 * @param string|array $message
	 * @param boolean $return
	 * @param string $html
	 * @return string|void
	 */
	public function warning_message( $messages, $return = true, $html = '' ) {
		if ( ! is_array( $messages ) ) {
			$messages = (array) $messages;
		}
		if ( $return ) {
			ob_start();
		}
		?>
		<div class="<?php echo esc_attr( static::$plugin_info['classes_general'] . '-warning-notice' ); ?> <?php echo esc_attr( static::$plugin_info['classes_general'] . '-notice' ); ?>">
			<ul class="warnings-list">
				<?php foreach ( $messages as $message ) : ?>
				<li><?php printf( esc_html__( '%s', 'gpls-pyplss-paypal-subscriptions' ), $message ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php
			if ( ! empty( $html ) ) {
				wp_kses_post( $html );
			}
			?>
		</div>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}
}
