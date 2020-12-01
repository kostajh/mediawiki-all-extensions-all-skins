<?php

namespace BlueSpice\Social\Data\Entity;

use BlueSpice\Social\EntityConfig;

class Schema extends \BlueSpice\Data\Entity\Schema {
	/**
	 *
	 * @return EntityConfig[]
	 */
	protected function getEntityConfigs() {
		$entityConfigs = parent::getEntityConfigs();
		return array_filter( $entityConfigs, function ( $entityConfig ) {
			return $entityConfig instanceof EntityConfig;
		} );
	}
}
