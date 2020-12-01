<?php

namespace BlueSpice\Social\Tags\Hook\BSEntityConfigAttributeDefinitions;

use BlueSpice\Data\Entity\Schema;
use BlueSpice\Data\FieldType;
use BlueSpice\Hook\BSEntityConfigAttributeDefinitions;
use BlueSpice\Social\EntityConfig;

/**
 * Adds tags to the entity attribute definitions
 */
class AddTags extends BSEntityConfigAttributeDefinitions {

	protected function skipProcessing() {
		if ( !$this->entityConfig instanceof EntityConfig ) {
			return true;
		}
		// TODO ->get( 'CanBeChild' ) as child entities can not be tagged
		if ( !$this->entityConfig->get( 'IsTagable' ) ) {
			return true;
		}
		return parent::skipProcessing();
	}

	protected function doProcess() {
		$this->attributeDefinitions['tags'] = [
			Schema::FILTERABLE => true,
			Schema::SORTABLE => true,
			Schema::TYPE => FieldType::LISTVALUE,
			Schema::INDEXABLE => true,
			Schema::STORABLE => true,
		];
		return true;
	}
}
