<?php
/**
 * Class Onboarding
 *
 * @package  Ecomerciar\Skydropx\Helper
 */

namespace Ecomerciar\Skydropx\Panel;

defined( 'ABSPATH' ) || exit;

use Ecomerciar\Skydropx\SDK\SkydropxSdk;
use Ecomerciar\Skydropx\Helper\Helper;

class Main {

	/**
	 * Register Onboarding Page
	 */
	public static function create_menu_option() {
		add_submenu_page(
			'woocommerce',
			__( 'Skydropx', 'skydropx' ),
			__( 'Skydropx', 'skydropx' ),
			'manage_woocommerce',
			'wc-skydropx-panel',
			array( __CLASS__, 'page_content' )
		);
	}

	/**
	 * Get content
	 */
	public static function page_content() {

		if ( ! is_admin() && ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			die( __( 'what are you doing here?', 'coolca' ) );
		}
		$shop_id = get_option( 'skydropx_user' );
		$site_url = get_site_url();

		if ( empty( $shop_id ) ) {			
			$shop_id = preg_replace( '/[^A-Za-z0-9 ]/', '', $site_url );
			update_option( 'skydropx_user', $shop_id );
		}

		$data = [
			'shopId' => $shop_id,
			'fullName' => $site_url
		];

		$sdk = new SkydropxSdk();
		$res = $sdk->registerOrLogin( $data );

		if ( $res ) {
			$data            = array();
			$data['shop_id'] = $shop_id;
			$data['token']   = $res['token'];
		    // $data['url']     = ( $res['userOnboarded'] ) ? 'https://skydropx-woocommerce-stage.conexa.ai/skydropx/woocommerce/panel/order?shop=' . $data['token'] : 'https://skydropx-woocommerce-stage.conexa.ai/skydropx/woocommerce/credentials?shop=' . $data['token'];
			$data['url'] = ( $res['userOnboarded'] ) ? 'https://skydropx-woocommerce.conexa.ai/skydropx/woocommerce/panel/order?shop=' . $data['token'] : 'https://skydropx-woocommerce.conexa.ai/skydropx/woocommerce/credentials?shop=' . $data['token'];

			helper::get_template_part( 'panel', 'content', $data );
		}
	}
}
