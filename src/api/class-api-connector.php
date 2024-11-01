<?php
/**
 * Class API
 *
 * @package  Ecomerciar\Skydropx\Api
 */

namespace Ecomerciar\Skydropx\Api;

use Ecomerciar\Skydropx\Helper\Helper;

abstract class ApiConnector {
	/**
	 * Executes API Request
	 *
	 * @param string $method
	 * @param string $url
	 * @param array  $body
	 * @param array  $headers
	 * @return string
	 */
	protected function exec( string $method, string $url, array $body, array $headers ) {

		$args['timeout'] = 10;
		$args['method']  = $method;
		$args['headers'] = $headers;
		if ( strtoupper( $method ) === 'GET' ) {
			$args['body'] = $body;
		} else {
			$args['body'] = wp_json_encode( $body, JSON_UNESCAPED_UNICODE );
		}

		$request = wp_safe_remote_request( $url, $args );
		Helper::log( '==============================================>' );
		Helper::log( $url );
		Helper::log( $headers );
		Helper::log( 'Request > ' );
		Helper::log( wp_json_encode( $body, JSON_UNESCAPED_UNICODE ) );

		if ( is_wp_error( $request ) ) {
			Helper::log( 'ERROR' );
			Helper::log( $request );
			return false;
		}

		$response = wp_remote_retrieve_body( $request );
		Helper::log( 'Response Code > ' . $request['response']['code'] );
		Helper::log( 'Response > ' );
		Helper::log( $response );

		if ( 200 === $request['response']['code'] ) {
			return json_decode( $response, true );
		} else {
			return array( 'errors' => json_decode( $response, true ) );
		}

	}

	/**
	 * Executes Post Request
	 *
	 * @param string $endpoint
	 * @param array  $body
	 * @param array  $headers
	 * @return string
	 */
	public function post( string $endpoint, array $body = array(), array $headers = array() ) {
		$url = $this->get_base_url() . $endpoint;
		return $this->exec( 'POST', $url, $body, $headers );
	}

	/**
	 * Executes Get Request
	 *
	 * @param string $endpoint
	 * @param array  $body
	 * @param array  $headers
	 * @return string
	 */
	public function get( string $endpoint, array $body = array(), array $headers = array() ) {
		$url = $this->get_base_url() . $endpoint;
		if ( ! empty( $body ) ) {
			$url .= '?' . http_build_query( $body );
		}
		return $this->exec( 'GET', $url, array(), $headers );
	}

	/**
	 * Executes Put Request
	 *
	 * @param string $endpoint
	 * @param array  $body
	 * @param array  $headers
	 * @return string
	 */
	public function put( string $endpoint, array $body = array(), array $headers = array() ) {
		$url = $this->get_base_url() . $endpoint;
		return $this->exec( 'PUT', $url, $body, $headers );
	}

	/**
	 * Executes Delete Request
	 *
	 * @param string $endpoint
	 * @param array  $body
	 * @param array  $headers
	 * @return string
	 */
	public function delete( string $endpoint, array $body = array(), array $headers = array() ) {
		$url = $this->get_base_url() . $endpoint;
		return $this->exec( 'DELETE', $url, $body, $headers );
	}

	/**
	 * Add Get Params to URL
	 *
	 * @param string $url
	 * @param array  $params
	 * @return string
	 */
	protected function add_params_to_url( $url, $params ) {
		if ( strpos( $url, '?' ) !== false ) {
			$url .= '&' . $params;
		} else {
			$url .= '?' . $params;
		}
		return $url;
	}

	/**
	 * Get API Base URL
	 *
	 * @return string
	 */
	abstract public function get_base_url();
}
