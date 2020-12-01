<?php

namespace BlueSpice\ExtendedStatistics\Data\Snapshot;

use BlueSpice\Data\DatabaseWriter;
use BlueSpice\Data\Entity\IWriter;
use BlueSpice\Data\RecordSet;
use BlueSpice\Entity;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use FormatJson;
use Status;

class Writer extends DatabaseWriter implements IWriter {

	/**
	 *
	 * @return string[]
	 */
	protected function getIdentifierFields() {
		return [ Record::ID ];
	}

	/**
	 *
	 * @return string
	 */
	protected function getTableName() {
		return Schema::TABLE_NAME;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema;
	}

	/**
	 *
	 * @param Entity $entity
	 * @return Status
	 */
	public function writeEntity( Entity $entity ) {
		$entityData = $entity->getFullData();
		$status = Status::newGood();
		$data = [
			Record::TIMESTAMP => $entity->get( Snapshot::ATTR_TIMESTAMP_CREATED ),
			Record::DATA => FormatJson::encode( $entityData[Snapshot::ATTR_COLLECTION] ),
		];
		if ( $entity->exists() ) {
			$data[Record::ID] = $entity->get( Snapshot::ATTR_ID );
		}
		$result = $this->write( new RecordSet( [
			new Record( (object)$data ) ]
		) );
		foreach ( $result->getRecords() as $record ) {
			if ( $record->getStatus()->isOK() ) {
				continue;
			}
			return $record->getStatus();
		}
		$entity->set( Entity::ATTR_ID, $this->db->insertId() );

		return $status;
	}

}
