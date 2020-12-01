<?php

namespace BlueSpice\ExtendedStatistics;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\Date;
use BlueSpice\Data\ReaderParams;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\Data\Snapshot\Record;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtendedStatistics\Util\SnapshotRange\Daily;
use BlueSpice\Timestamp;

class SnapshotFactory extends EntityFactory {

	/**
	 *
	 * @param Timestamp $ts
	 * @return Snapshot|null
	 */
	public function newFromTimestamp( Timestamp $ts ) {
		$range = new Daily( new Timestamp( clone $ts->timestamp ) );
		$config = $this->makeConfig( Snapshot::TYPE );
		$store = $this->makeStore( Snapshot::TYPE, $config );
		$res = $store->getReader()->read( new ReaderParams( [
			ReaderParams::PARAM_LIMIT => 1,
			ReaderParams::PARAM_FILTER => [
				(object)[
					Filter::KEY_COMPARISON => Date::COMPARISON_LOWER_THAN,
					Filter::KEY_PROPERTY => Record::TIMESTAMP,
					Filter::KEY_VALUE => $range->getStart()->getTimestamp( TS_MW ),
					Filter::KEY_TYPE => FieldType::DATE
				],
				(object)[
					Filter::KEY_COMPARISON => Date::COMPARISON_GREATER_THAN,
					Filter::KEY_PROPERTY => Record::TIMESTAMP,
					Filter::KEY_VALUE => $range->getEnd()->getTimestamp( TS_MW ),
					Filter::KEY_TYPE => FieldType::DATE
				],
			],
		] ) );

		$data = [
			Snapshot::ATTR_TYPE => Snapshot::TYPE,
			Snapshot::ATTR_TIMESTAMP_CREATED => $ts->getTimestamp( TS_MW ),
			Snapshot::ATTR_TIMESTAMP_TOUCHED => $ts->getTimestamp( TS_MW )
		];

		if ( count( $res->getRecords() ) > 0 ) {
			$record = $res->getRecords()[0];
			$data = array_merge( $data, [
				Snapshot::ATTR_ID => $record->get( Record::ID ),
				Snapshot::ATTR_COLLECTION => $record->get( Record::DATA ),
				Snapshot::ATTR_TIMESTAMP_CREATED => $record->get( Record::TIMESTAMP ),
				Snapshot::ATTR_TIMESTAMP_TOUCHED => $record->get( Record::TIMESTAMP ),
			] );
		}

		return $this->factory( Snapshot::TYPE, (object)$data, $config, $store );
	}

	/**
	 *
	 * @param Snapshot $snapshot
	 * @return Snapshot|null
	 */
	public function getPrevious( Snapshot $snapshot ) {
		$config = $this->makeConfig( Snapshot::TYPE );
		$store = $this->makeStore( Snapshot::TYPE, $config );
		$res = $store->getReader()->read( new ReaderParams( [
			ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE,
			ReaderParams::PARAM_FILTER => [
				(object)[
					Filter::KEY_COMPARISON => Date::COMPARISON_GREATER_THAN,
					Filter::KEY_PROPERTY => Record::TIMESTAMP,
					Filter::KEY_VALUE => $snapshot->get( Snapshot::ATTR_TIMESTAMP_CREATED ),
					Filter::KEY_TYPE => FieldType::DATE
				],
			],
		] ) );
		$data = [
			Snapshot::ATTR_TYPE => Snapshot::TYPE
		];
		foreach ( $res->getRecords() as $record ) {
			$data = array_merge( $data, [
				Snapshot::ATTR_ID => $record->get( Record::ID ),
				Snapshot::ATTR_COLLECTION => $record->get( Record::DATA ),
				Snapshot::ATTR_TIMESTAMP_CREATED => $record->get( Record::TIMESTAMP ),
				Snapshot::ATTR_TIMESTAMP_TOUCHED => $record->get( Record::TIMESTAMP ),
			] );
			$previous = $this->factory( Snapshot::TYPE, (object)$data, $config, $store );
			if ( !$previous || !$previous->exists() ) {
				continue;
			}
			return $previous;
		}
		return null;
	}

}
