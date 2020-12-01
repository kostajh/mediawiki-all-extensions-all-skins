<?php

namespace BlueSpice\ExtendedStatistics\Data\Entity\Collection;

use BlueSpice\Data\Entity\Schema as EntitySchema;

class Schema extends EntitySchema {
	const PRIMARY = 'primary';

	/**
	 *
	 * @return array
	 */
	protected function getDefaultFieldDefinition() {
		return array_merge( parent::getDefaultFieldDefinition(), [
			static::PRIMARY => false,
		] );
	}

	/**
	 *
	 * @return EntityConfig[]
	 */
	protected function getEntityConfigs() {
		$entityConfigs = parent::getEntityConfigs();
		return array_filter( $entityConfigs, function ( $entityConfig ) {
			return $entityConfig->get( 'IsCollection' );
		} );
	}

	/**
	 * @return string[]
	 */
	public function getPrimaryFields() {
		return $this->filterFields( static::PRIMARY, false );
	}
}
