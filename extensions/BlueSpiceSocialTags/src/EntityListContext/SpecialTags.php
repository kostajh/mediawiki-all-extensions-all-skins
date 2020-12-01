<?php

namespace BlueSpice\Social\Tags\EntityListContext;

use BlueSpice\Social\Entity;
use BlueSpice\Social\EntityConfig\Action;

class SpecialTags extends \BlueSpice\Social\EntityListContext {

	/**
	 *
	 * @return array
	 */
	public function getAllowedTypes() {
		$entityTypes = [];
		foreach ( $this->getEntityConfigs() as $type => $config ) {
			if ( $config->get( 'IsTagable' ) || $config instanceof Action ) {
				$entityTypes[] = $type;
			}
		}
		return $entityTypes;
	}

	/**
	 *
	 * @return int
	 */
	public function getLimit() {
		return 20;
	}

	/**
	 *
	 * @return string
	 */
	protected function getSortProperty() {
		return Entity::ATTR_TIMESTAMP_CREATED;
	}

}
