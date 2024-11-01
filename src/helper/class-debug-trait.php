<?php
/**
 * Debug Trait
 *
 * @package  Ecomerciar\Skydropx\Helper
 */

namespace Ecomerciar\Skydropx\Helper;

trait DebugTrait {

	public static function log( $log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			self::log_debug( print_r( $log, true ) );
		} else {
			self::log_debug( $log );
		}
	}

}
