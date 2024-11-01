<?php
/**
 * Class Skydropx Shipping Class
 *
 * @package  Ecomerciar\Skydropx\ShippingMethod;
 */

namespace Ecomerciar\Skydropx\ShippingMethod;

use WC_Shipping_Method;
use Ecomerciar\Skydropx\SDK\SkydropxSdk;
use Ecomerciar\Skydropx\Helper\Helper;

defined( 'ABSPATH' ) || class_exists( '\WC_Shipping_Method' ) || exit;

class WC_Skydropx extends \WC_Shipping_Method {
	/**
	 * Default constructor
	 *
	 * @param int $instance_id Shipping Method Instance from Order
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'skydropx';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Skydropx', 'skydropx' );
		$this->method_description = __( 'Permite a tus clientes calcular el costo del envío por Skydropx.', 'skydropx' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->init();
	}

	/**
	 * Init user set variables.
	 *
	 * @return void
	 */
	public function init() {
		$this->instance_form_fields = include 'settings.php';
		$this->title                = $this->get_option( 'title' );

		// Save settings in admin if you have any defined
		add_action(
			'woocommerce_update_options_shipping_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
	}

	/**
	 * Calculate the shipping costs.
	 *
	 * @param array $package Package of items from cart.
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		$rateDefaults = array(
			'label'    => $this->get_option( 'title' ), // Label for the rate
			'cost'     => '0', // Amount for shipping or an array of costs (for per item shipping)
			'taxes'    => '', // Pass an array of taxes, or pass nothing to have it calculated for you, or pass 'false' to calculate no tax for this method
			'calc_tax' => 'per_order', // Calc tax per_order or per_item. Per item needs an array of costs passed via 'cost'
			'package'  => $package,
			'term'     => '',
		);

		Helper::log( 'Quotation' );

		$items = Helper::get_items_from_cart( WC()->cart );
		if ( $items === false ) {
			Helper::log( 'No items' );

			return;
		}

		$zipTo = $package['destination']['postcode'];
		Helper::log( $package['destination'] );
		if ( empty( $zipTo ) ) {
			Helper::log( 'No Zipcode' );
			return;
		}

		$country = $package['destination']['country'];
		if ( empty( $country ) || ( $country <> 'MX' && $country <> 'CO' ) ) {
			Helper::log( 'No Country / Only Mexico & Colombia' );
			return;
		}

		$zipFrom = get_option( 'woocommerce_store_postcode' );

		$sdk = new SkydropxSdk();
		$res = $sdk->quoteList( $items, $zipFrom, $zipTo );
		if ( $res ) {

			// evaluate if all costs are 0
			$quote_free = true;
			foreach ( $res as $quote ) {
				if ( floatval( $quote['total_pricing'] ) > 0 ) {
					$quote_free = false;
				}
			}
			if ( $quote_free ) {
				$rate              = $rateDefaults;
				$rate['id']        = $this->get_rate_id() . '_free';
				$rate['label']     = __( 'Skydropx', 'skydropx' );
				$rate['cost']      = 0;
				$rate['label']     = $rate['label'] . __( ' - ¡Gratis!' );
				$rate['meta_data'] = array(
					'Proveedor'                => '',
					'Nombre Nivel de Servicio' => '',
					'Código Nivel de Servicio' => '',
					'Tiempo Estimado'          => '',
					'Costo de Envío'           => 0,
					'Moneda'                   => '',
				);
				$this->add_rate( $rate );
			} else {
				foreach ( $res as $quote ) {
					$days = intval( $quote['days'] );
					if ( $days > 1 ) {
						$promiseString = sprintf( __( 'El pedido llega en %s días.', 'skydropx' ), $days );
					} else {
						if ( $days == 1 ) {
							$promiseString = __( 'El pedido llega mañana.', 'skydropx' );
						} else {
							$promiseString = __( 'El pedido llega hoy.', 'skydropx' );
						}
					}

					$rate          = $rateDefaults;
					$rate['id']    = $this->get_rate_id() . '_' . $quote['provider'] . '_' . $quote['service_level_code'];
					$rate['label'] = $quote['provider'] . ' - ' . $quote['service_level_name'] . ' - ' . $promiseString;
					$rate['cost']  = floatval( $quote['total_pricing'] );
					if ( floatval( $quote['total_pricing'] ) === 0 || empty( floatval( $quote['total_pricing'] ) ) ) {
						$rate['label'] = $rate['label'] . __( ' - ¡Gratis!' );
					}
					$rate['meta_data'] = array(
						'Proveedor'                => $quote['provider'],
						'Nombre Nivel de Servicio' => $quote['service_level_name'],
						'Código Nivel de Servicio' => $quote['service_level_code'],
						'Tiempo Estimado'          => $quote['days'],
						'Costo de Envío'           => $quote['total_pricing'],
						'Moneda'                   => $quote['currency_local'],
					);
					$this->add_rate( $rate );
				}
			}
		}

	}

}
