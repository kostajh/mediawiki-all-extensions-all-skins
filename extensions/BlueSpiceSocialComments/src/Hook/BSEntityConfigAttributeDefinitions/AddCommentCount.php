<?php

namespace BlueSpice\Social\Comments\Hook\BSEntityConfigAttributeDefinitions;

use BlueSpice\Data\Entity\Schema;
use BlueSpice\Data\FieldType;
use BlueSpice\Hook\BSEntityConfigAttributeDefinitions;
use BlueSpice\Social\Comments\EntityConfig\Comment;
use BlueSpice\Social\EntityConfig;

/**
 * Adds commentcount to the entity attribute definitions
 */
class AddCommentCount extends BSEntityConfigAttributeDefinitions {

	protected function skipProcessing() {
		if ( !$this->entityConfig instanceof EntityConfig ) {
			return true;
		}
		if ( $this->entityConfig instanceof Comment ) {
			return true;
		}
		if ( !$this->entityConfig->get( 'CanHaveChildren' ) ) {
			return true;
		}
		return parent::skipProcessing();
	}

	protected function doProcess() {
		$this->attributeDefinitions['commentcount'] = [
			Schema::FILTERABLE => true,
			Schema::SORTABLE => true,
			Schema::TYPE => FieldType::INT,
			Schema::INDEXABLE => true,
			Schema::STORABLE => false,
		];
		return true;
	}
}
