<?php
/**
 * Class Skydropx Sdk
 *
 * @package  Ecomerciar\Skydropx\Sdk
 */

namespace Ecomerciar\Skydropx\Sdk;

use Ecomerciar\Skydropx\Api\SkydropxApi;
use Ecomerciar\Skydropx\Helper\Helper;
use Ecomerciar\Skydropx\ShippingMethod\WC_Skydropx;

class SkydropxSdk {

	/**
	 * Constructor Method
	 *
	 * @return Void
	 */
	public function __construct() {
		$this->api         = new SkydropxApi();
		$this->token       = get_option( 'skydropx_token' );
		$this->userOnboard = false;
	}

	public function registerOrLogin( array $data ) {
		$endpoint = '/v1/gateway/oauth';

		$body           = array();
		$body['shopId'] = isset($data['shopId'])? $data['shopId'] : '';
		$body['fullName'] = isset($data['fullName'])? $data['fullName'] : '';

		$headers                 = array();
		$headers['Content-Type'] = 'application/json';

		$res = $this->api->post( $endpoint, $body, $headers );
		$res = $this->handle_response( $res, __FUNCTION__ );

		if ( $res ) {
			update_option( 'skydropx_token', $res['token'] );
			return array(
				'token'         => $res['token'],
				'userOnboarded' => $res['userOnboard'],
			);
		}

		return false;
	}

	public function quoteList( $items, $zipFrom, $zipTo ) {
		$endpoint = '/v1/gateway/quotation?shop=' . $this->token;

		$grouped_items = Helper::group_items( $items );
		$total_weight  = 0;
		$total_height  = 0;
		$total_width   = 0;
		$total_length  = 0;
		foreach ( $grouped_items as $item ) {
			if ( empty( $item['weight'] ) || empty( $item['height'] ) || empty( $item['width'] ) || empty( $item['length'] ) ) {
				Helper::log_error( __( 'Helper -> Error obteniendo productos de la orden, producto con malas dimensiones - ID: ', 'skydropx' ) . $item['id'] );
				return false;
			}
			$total_weight = $total_weight + floatval( $item['weight'] ) * floatval( $item['quantity'] );
			$total_height = ( $total_height < floatval( $item['height'] ) )? floatval( $item['height'] ) : $total_height ;
			$total_width  = $total_width + floatval( $item['width'] ) * floatval( $item['quantity'] );
			$total_length = ( $total_length < floatval( $item['length'] ) )? floatval( $item['length'] ) : $total_length ;
		}

		$body = array();
		// $body['zip_from'] =  $zipFrom;
		$body['zip_to'] = $zipTo;
		$body['parcel'] = array(
			'weight' => $total_weight,
			'height' => $total_height,
			'width'  => $total_width,
			'length' => $total_length,
		);

		$headers                 = array();
		$headers['Content-Type'] = 'application/json';

		$res = $this->api->post( $endpoint, $body, $headers );
		$res = $this->handle_response( $res, __FUNCTION__ );

		if ( $res ) {
			return $res['data'];
		}

		return false;
	}

	public function postOrder( \WC_Order $order ) {
		$endpoint = '/v1/gateway/order?shop=' . $this->token;

		$customer = Helper::get_customer_from_order( $order );

		$itemList = array();
		$items    = Helper::get_items_from_order( $order );

		$order->get_shipping_methods();
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return;
		}
		$shipping_method = array_shift( $shipping_methods );
		if ( 'skydropx' === $shipping_method->get_method_id() ) {
			$shipping_method_pickit = new WC_Skydropx( $shipping_method->get_instance_id() );
			$provider               = $shipping_method->get_meta( 'Proveedor' );
			$serviceLevelName       = $shipping_method->get_meta( 'Nombre Nivel de Servicio' );
			$serviceLevelCode       = $shipping_method->get_meta( 'Código Nivel de Servicio' );
			$estimatedTime          = $shipping_method->get_meta( 'Tiempo Estimado' );
			$shippingCost           = $shipping_method->get_meta( 'Costo de Envío' );
			$currency               = $shipping_method->get_meta( 'Moneda' );
		} else {
			$provider         = '';
			$serviceLevelName = '';
			$serviceLevelCode = '';
			$estimatedTime    = '';
			$shippingCost     = 0;
			$currency         = '';
		}

		$body                   = array();
		$body['orderId']        = $order->get_ID();
		$body['trackingStatus'] = 'PENDING';
		$body['status']         = 'PENDING';
		$body['address_to']     = array(
			'province'  => $customer['province'],
			'city'      => $customer['locality'],
			'name'      => $customer['full_name'],
			'zip'       => $customer['cp'],
			'country'   => $customer['country'],
			'address1'  => trim( $customer['address_1'] ),
			'company'   => '',
			'address2'  => empty( trim( $customer['address_2'] ) ) ? trim( $customer['address_1'] ) : trim( $customer['address_2'] ),
			'phone'     => preg_replace( '/[^0-9 ]/', '', $customer['phone'] ),
			'email'     => $customer['email'],
			'reference' => $customer['extra_info'],
			'contents'  => '',
		);

		$body['webhookURL'] = get_site_url( null, '/wc-api/wc-skydropx-order-status' );

		$grouped_items = Helper::group_items( $items );
		foreach ( $grouped_items as $item ) {
			$body['items'][] = array(
				'quantity'     => floatval( $item['quantity'] ),
				'productName'  => $item['description'],
				'productPrice' => floatval( $item['price'] ),
				'height'       => floatval( $item['height'] ), // cm
				'weight'       => floatval( $item['weight'] ), // gramos
				'width'        => floatval( $item['width'] ),
				'length'       => floatval( $item['length'] ),
			);
		}

		$body['deliveryService'] = array(
			'provider'         => $provider,
			'serviceLevelName' => $serviceLevelName,
			'serviceLevelCode' => $serviceLevelCode,
			'estimatedTime'    => $estimatedTime,
			'shippingCost'     => $shippingCost,
			'currency'         => $currency,
		);

		$headers                 = array();
		$headers['Content-Type'] = 'application/json';

		$res = $this->api->post( $endpoint, $body, $headers );
		$res = $this->handle_response( $res, __FUNCTION__ );

		if ( $res ) {
			return true;
		}

		return false;
	}

	protected function handle_response( $response, string $function_name ) {
		if ( ! isset( $response['success'] ) ) {
			return false;
		}

		if ( $response['success'] == false ) {
			return false;
		}

		if ( 'postOrder' === $function_name ) {
			return $response['success'];
		}
		return $response;
	}

}
