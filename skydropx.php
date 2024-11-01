<?php
/**
 * Plugin Name: Skydropx: Cotizador y Envios
 * Description: Método de Envío Skydropx para WooCommerce.
 * Version: 1.0.12
 * Requires PHP: 7.0
 * Author: Conexa
 * Author URI: https://conexa.ai
 * Text Domain: skydropx
 * Domain Path: /i18n/languages/
 * WC requires at least: 5.4.1
 * WC tested up to: 5.4
 */

use Ecomerciar\Skydropx\Helper\Helper;

defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', array( 'SkydropxWoo', 'init' ) );
add_action( 'activated_plugin', array( 'SkydropxWoo', 'activation' ) );
add_action( 'deactivated_plugin', array( 'SkydropxWoo', 'deactivation' ) );

/**
 * Plugin's base Class
 */
class SkydropxWoo {

	const VERSION     = '1.0.12';
	const PLUGIN_NAME = 'Skydropx';
	const MAIN_FILE   = __FILE__;
	const MAIN_DIR    = __DIR__;

	/**
	 * Checks system requirements
	 *
	 * @return bool
	 */
	public static function check_system() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$system = self::check_components();

		if ( $system['flag'] ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>' . sprintf( __( '<strong>%1$s/strong> Requiere al menos %2$s versión %3$s o superior.', 'skydropx' ), self::PLUGIN_NAME, $system['flag'], $system['version'] ) . '</p>';
			echo '</div>';
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>' . sprintf( __( 'WooCommerce debe estar activo antes de usar <strong>%s</strong>', 'skydropx' ), self::PLUGIN_NAME ) . '</p>';
			echo '</div>';
			return false;
		}
		return true;
	}

	/**
	 * Check the components required for the plugin to work (PHP, WordPress and WooCommerce)
	 *
	 * @return array
	 */
	private static function check_components() {
		global $wp_version;
		$flag = $version = false;

		if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
			$flag    = 'PHP';
			$version = '7.0';
		} elseif ( version_compare( $wp_version, '5.4', '<' ) ) {
			$flag    = 'WordPress';
			$version = '5.4';
		} elseif ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, '4.3', '<' ) ) {
			$flag    = 'WooCommerce';
			$version = '4.3';
		}

		return array(
			'flag'    => $flag,
			'version' => $version,
		);
	}

	/**
	 * Inits our plugin
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! self::check_system() ) {
			return false;
		}

		spl_autoload_register(
			function ( $class ) {
				// Plugin base Namespace.
				if ( strpos( $class, 'Skydropx' ) === false || strpos( $class, 'Ecomerciar' ) === false ) {
					return;
				}
				$class     = str_replace( '\\', '/', $class );
				$parts     = explode( '/', $class );
				$classname = array_pop( $parts );

				$filename = $classname;
				$filename = str_replace( 'WooCommerce', 'Woocommerce', $filename );
				$filename = str_replace( 'WC_', 'Wc', $filename );
				$filename = str_replace( 'WC', 'Wc', $filename );
				$filename = preg_replace( '/([A-Z])/', '-$1', $filename );
				$filename = 'class' . $filename;
				$filename = strtolower( $filename );
				$folder   = strtolower( array_pop( $parts ) );
				if ( 'class-skydropx-woocommerce' === $filename ) {
					return;
				}
				require_once plugin_dir_path( __FILE__ ) . 'src/' . $folder . '/' . $filename . '.php';
			}
		);

		include_once __DIR__ . '/hooks.php';
		self::load_textdomain();
		Helper::init();
	}

	/**
	 * Adds our shipping method to WooCommerce
	 *
	 * @param array $shipping_methods
	 * @return array
	 */
	public static function add_shipping_method( $shipping_methods ) {
		$shipping_methods['skydropx'] = '\Ecomerciar\Skydropx\ShippingMethod\WC_Skydropx';
		return $shipping_methods;
	}

	/**
	 * Loads the plugin text domain
	 *
	 * @return void
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'skydropx', false, basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * Activation Plugin Actions
	 *
	 * @return void
	 */
	public static function activation( $plugin ) {
		if ( $plugin == plugin_basename( self::MAIN_FILE ) ) {
			if ( ! wp_is_json_request() ){
				exit( wp_redirect( admin_url( 'admin.php?page=wc-skydropx-panel' ) ) );
			}			
		}
	}

	/**
	 * DeActivation Plugin Actions
	 *
	 * @return void
	 */
	public static function deactivation( $plugin ) {
		delete_option( 'skydropx_user' );
	}

}

// --- HPOS WooCommerce Compatibility
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );