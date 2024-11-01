<?php
/**
 * Class API Interface
 *
 * @package  Ecomerciar\Skydropx\Api
 */

namespace Ecomerciar\Skydropx\Api;

interface ApiInterface {
	public function get( string $endpoint, array $body = array(), array $headers = array());
	public function post( string $endpoint, array $body = array(), array $headers = array());
	public function put( string $endpoint, array $body = array(), array $headers = array());
	public function delete( string $endpoint, array $body = array(), array $headers = array());
}
