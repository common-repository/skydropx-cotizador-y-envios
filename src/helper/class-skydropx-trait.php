<?php
/**
 * Skydropx Trait
 *
 * @package  Ecomerciar\Skydropx\Helper
 */

namespace Ecomerciar\Skydropx\Helper;

trait SkydropxTrait {
	public static function get_status() {
		return array(
			'CREATING_LABEL'   => 'Por crear',
			'FULFILLMENT'      => 'Creado',
			'CREATED'          => 'Creado',
			'PICKED_UP'        => 'Recolectado',
			'IN_TRANSIT'       => 'En camino',
			'LAST_MILE'        => 'Por llegar',
			'DELIVERED'        => 'Entregado',
			'DELIVERY_ATTEMPT' => 'Con Incidencia',
			'EXCEPTION'        => 'Por cancelar',
			'REVIEWING'        => 'Por cancelar',
			'PENDING'          => 'Por crear',
			'CANCELLED'        => 'Por cancelar',
			'ERROR'            => 'Datos con error',
			'RESTORED'         => 'Con Incidencia',
		);
	}

}
