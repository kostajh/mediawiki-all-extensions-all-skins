<?php

namespace BlueSpice\ExtendedStatistics\Data\Snapshot;

use BlueSpice\Data\DatabaseReader;
use BlueSpice\Data\Entity\IReader;
use BlueSpice\Data\Filter\Numeric;
use BlueSpice\Data\ReaderParams;
use BlueSpice\EntityConfig;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;

class Reader extends DatabaseReader implements IReader {

	/**
	 *
	 * @param ReaderParams $params
	 * @return PrimaryDataProvider
	 */
	protected function makePrimaryDataProvider( $params ) {
		return new PrimaryDataProvider( $this->db, $this->getSchema() );
	}

	/**
	 *
	 * @return null
	 */
	protected function makeSecondaryDataProvider() {
		return null;
	}

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @param int $id
	 * @param EntityConfig $entityConfig
	 * @return \stdClass
	 */
	public function resolveNativeDataFromID( $id, EntityConfig $entityConfig ) {
		$store = new Store();
		$result = $store->getReader()->read( new ReaderParams( [
			ReaderParams::PARAM_FILTER => [
				(object)[
					Numeric::KEY_PROPERTY => Record::ID,
					Numeric::KEY_VALUE => (int)$id,
					Numeric::KEY_COMPARISON => Numeric::COMPARISON_EQUALS,
					Numeric::KEY_TYPE => 'numeric'
				]
			]
		] ) );
		foreach ( $result->getRecords() as $record ) {
			$data = (object)[
				Snapshot::ATTR_TYPE => Snapshot::TYPE,
				Snapshot::ATTR_ID => $record->get( Record::ID ),
				Snapshot::ATTR_COLLECTION => $record->get( Record::DATA ),
				Snapshot::ATTR_TIMESTAMP_CREATED => $record->get( Record::TIMESTAMP ),
			];
		}
		return $data;
	}
}
