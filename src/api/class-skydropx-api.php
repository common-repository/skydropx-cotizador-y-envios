<?php
/**
 * Class Skydropx API
 *
 * @package  Ecomerciar\Skydropx\Api
 */

namespace Ecomerciar\Skydropx\Api;

use Ecomerciar\Skydropx\Helper\Helper;

class SkydropxApi extends ApiConnector implements ApiInterface {
	// const API_BASE_URL = 'https://skydropx-woocommerce-api-docker.conexa.ai/api';
	const API_BASE_URL = 'https://skydropx-woocommerce-api.conexa.ai/api';

	const APPLICATION_JSON = 'application/json';

	/**
	 * Get Base API Url
	 *
	 * @return string
	 */
	public function get_base_url() {
		return self::API_BASE_URL;
	}

}
