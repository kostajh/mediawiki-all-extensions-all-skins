<?php

namespace BlueSpice\Social\Rating\Hook\BSEntityConfigAttributeDefinitions;

use BlueSpice\Data\Entity\Schema;
use BlueSpice\Data\FieldType;
use BlueSpice\Hook\BSEntityConfigAttributeDefinitions;
use BlueSpice\Social\EntityConfig;

/**
 * Adds rating to the entity attribute definitions
 */
class AddRating extends BSEntityConfigAttributeDefinitions {

	protected function skipProcessing() {
		if ( !$this->entityConfig instanceof EntityConfig ) {
			return true;
		}
		if ( !$this->entityConfig->get( 'IsRateable' ) ) {
			return true;
		}
		return parent::skipProcessing();
	}

	protected function doProcess() {
		$this->attributeDefinitions['ratingcount'] = [
			Schema::FILTERABLE => true,
			Schema::SORTABLE => true,
			Schema::TYPE => FieldType::INT,
			Schema::INDEXABLE => true,
			Schema::STORABLE => false,
		];
		return true;
	}
}
