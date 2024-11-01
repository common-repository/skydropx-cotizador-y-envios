<?php
/**
 * Class Webhooks
 *
 * @package  Ecomerciar\Skydropx\Orders\Webhooks
 */

namespace Ecomerciar\Skydropx\Orders;

use Ecomerciar\Skydropx\Helper\Helper;

defined( 'ABSPATH' ) || exit();

/**
 * WebHook's base Class
 */
class Webhook {

	const OK    = 'HTTP/1.1 200 OK';
	const ERROR = 'HTTP/1.1 500 ERROR';

	/**
	 * Receives the webhook and check if it's valid to proceed
	 *
	 * @param string $data Webhook json Data for testing purpouses.
	 *
	 * @return bool
	 */
	public static function listener( string $data = null, string $data_header = null ) {

		// Takes raw data from the request.
		if ( is_null( $data ) || empty( $data ) ) {
			$json = file_get_contents( 'php://input' );
		} else {
			$json = $data;
		}

		$appkey = '';

		Helper::log_info( 'Webhook recibido' );
		Helper::log(
			__FUNCTION__ .
				__( '- Webhook recibido de Skydropx:', 'skydropx' ) .
				$json
		);
		// Helper::log(__('AppKey Recibido: ', 'skydropx') . esc_html($appkey) );

		$process = self::process_webhook( $json, $appkey );

		if ( is_null( $json ) || empty( $json ) ) {
			// Real Webhook.
			if ( $process ) {
				header( self::OK );
			} else {
				header( self::ERROR );
				wp_die(
					__( 'WooCommerce Skydropx Webhook no válido.', 'skydropx' ),
					'Skydropx Webhook',
					array( 'response' => 500 )
				);
			}
		} else {
			// For testing purpouse.
			return $process;
		}
	}


	/**
	 * Process Webhook
	 *
	 * @param json $json Webhook data for.
	 *
	 * @return bool
	 */
	public static function process_webhook( $json, $appkey ) {

		// Converts it into a PHP object.
		$data = json_decode( $json, true );

		if ( empty( $data ) || ! self::validate_input( $data ) /*|| ! self::validate_appkey( $appkey ) */ ) {
			return false;
		}
		return self::handle_webhook( $data );
	}

	/**
	 * Validates the incoming webhook
	 *
	 * @param array $data Webhook data to be validated.
	 *
	 * @return bool
	 */
	private static function validate_input( array $data = array() ) {
		$return = true;
		if ( ! isset( $data['userId'] ) || empty( $data['userId'] ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Webhook recibido sin userId.', 'skydropx' )
			);
			$return = false;
		} else {
			$userId = get_option( 'skydropx_user' );
			if ( $data['userId'] !== $userId ) {
				Helper::log(
					__FUNCTION__ .
						__( '- userId inválido.', 'skydropx' )
				);
				$return = false;
			}
		}

		if ( ! isset( $data['orderId'] ) || empty( $data['orderId'] ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Webhook recibido sin orderId.', 'skydropx' )
			);
			$return = false;
		}
		if ( ! isset( $data['status'] ) || empty( $data['status'] ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Webhook recibido sin status.', 'skydropx' )
			);
			$return = false;
		}
		return $return;
	}

	/**
	 * Handles and processes the webhook
	 *
	 * @param array $data webhook data to be processed.
	 *
	 * @return bool
	 */
	private static function handle_webhook( array $data = array() ) {

		$order = wc_get_order( $data['orderId'] );
		if ( empty( $order ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- No existe orden.', 'skydropx' )
			);
			return false;
		}
		$skydropxStatus = Helper::get_status();
		if ( ! isset( $skydropxStatus[ $data['status'] ] ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Status no válido.', 'skydropx' )
			);
			return false;
		}

		$order->get_shipping_methods();
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Orden no tiene método de envío.', 'skydropx' )
			);
			return false;
		}
		$shipping_method = array_shift( $shipping_methods );

		if ( 'skydropx' !== $shipping_method->get_method_id() ) {
			Helper::log(
				__FUNCTION__ .
					__( '- Orden no tiene Skydropx como método de envío.', 'skydropx' )
			);
			return false;
		}

		if ( 'processing' === $order->get_status() ) {
			if ( 'DELIVERED' === $data['status'] ) {
				$order->set_status( 'completed' );
			}
		}

		$order->add_order_note( sprintf( __( 'Skydropx> ( %s ).', 'skydropx' ), $skydropxStatus[ $data['status'] ] ) );
		$order->save();

		return true;
	}
}
