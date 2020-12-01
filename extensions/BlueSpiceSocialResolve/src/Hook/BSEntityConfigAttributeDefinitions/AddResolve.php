<?php

namespace BlueSpice\Social\Resolve\Hook\BSEntityConfigAttributeDefinitions;

use BlueSpice\Data\Entity\Schema;
use BlueSpice\Data\FieldType;
use BlueSpice\Hook\BSEntityConfigAttributeDefinitions;
use BlueSpice\Social\EntityConfig;

class AddResolve extends BSEntityConfigAttributeDefinitions {

	protected function skipProcessing() {
		if ( !$this->entityConfig instanceof EntityConfig ) {
			return true;
		}
		if ( !$this->entityConfig->get( 'IsResolvable' ) ) {
			return true;
		}
		return parent::skipProcessing();
	}

	protected function doProcess() {
		$this->attributeDefinitions['resolved'] = [
			Schema::FILTERABLE => true,
			Schema::SORTABLE => true,
			Schema::TYPE => FieldType::BOOLEAN,
			Schema::INDEXABLE => true,
			Schema::STORABLE => true,
		];
		return true;
	}
}
