<?php
/**
 * Class Process New Order
 *
 * @package  Ecomerciar\Skydropx\Helper
 */

namespace  Ecomerciar\Skydropx\Orders;

defined( 'ABSPATH' ) || exit;

use Ecomerciar\Skydropx\SDK\SkydropxSdk;
use Ecomerciar\Skydropx\Helper\Helper;
use \WC_Order;
use Ecomerciar\Skydropx\ShippingMethod\WC_Skydropx;
/*
* Main Plugin Process Class
*/
class ProcessNewOrder {


	public static function handle_order_status( int $order_id, string $status_from, string $status_to, \WC_Order $order ) {
		if ( empty( $order ) ) {
			return;
		}
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return;
		}
		/*
		$shipping_method = array_shift( $shipping_methods );
		if ( 'skydropx' !== $shipping_method->get_method_id() ) {
			return;
		}*/
		if ( 'processing' !== $order->get_status() ) {
			return;
		}
		Helper::log( 'Create Order' );
		$sdk = new SkydropxSdk();
		if ( $sdk->postOrder( $order ) ) {
			$order->add_order_note( esc_html( 'El pedido fue enviado al panel de Skydropx.', 'skydropx' ) );
		} else {
			$order->add_order_note( esc_html( 'No fue posible enviar el pedido al panel de Skydrop. Consulte el Log.', 'skydropx' ) );
		}

	}

}
