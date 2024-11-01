<?php
/**
 * Settings for Skydropx Shipping Method
 *
 * @package  Ecomerciar\Skydropx\ShippingMethod;
 */

namespace Ecomerciar\Skydropx\ShippingMethod;

defined( 'ABSPATH' ) || exit;

$settings = array(
	'title' => array(
		'title'       => __( 'Nombre Método Envío', 'skydropx' ),
		'type'        => 'text',
		'description' => __( 'Nombre con el que aparecerá el tipo de envío en tu tienda.', 'skydropx' ),
		'default'     => __( 'Skydropx', 'skydropx' ),
		'desc_tip'    => true,
	),

);

return $settings;
