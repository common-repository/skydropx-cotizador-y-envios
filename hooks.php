<?php
/**
 * Hooks Files
 *
 * @package  Ecomerciar\Skydropx\Hooks
 */

defined( 'ABSPATH' ) || exit;

// --- Shipment Method
add_filter( 'woocommerce_shipping_methods', array( 'SkydropxWoo', 'add_shipping_method' ) );

// --- Add Skydropx Panel
add_action( 'admin_menu', array( '\Ecomerciar\Skydropx\Panel\Main', 'create_menu_option' ) );

// --- Post Order
add_action( 'woocommerce_order_status_changed', array( '\Ecomerciar\Skydropx\Orders\ProcessNewOrder', 'handle_order_status' ), 10, 4 );

// --- Webhook
add_action( 'woocommerce_api_wc-skydropx-order-status', array( '\Ecomerciar\Skydropx\Orders\Webhook', 'listener' ) );
