<?php

namespace BlueSpice\WhoIsOnline\DataCollector\StoreSourced;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Filter;
use BlueSpice\Data\Filter\Date;
use BlueSpice\Data\IRecord;
use BlueSpice\Data\IStore;
use BlueSpice\Data\RecordSet;
use BlueSpice\Data\Sort;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\DataCollector\StoreSourced;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtendedStatistics\Util\SnapshotRange\Daily;
use BlueSpice\Timestamp;
use BlueSpice\WhoIsOnline\Data\Record;
use BlueSpice\WhoIsOnline\Data\Store;
use BlueSpice\WhoIsOnline\Entity\Collection\UserLogin as Collection;
use Config;
use DateTime;
use DateTimeZone;
use MediaWiki\MediaWikiServices;

class UserLogin extends StoreSourced {

	/**
	 *
	 * @return RecordSet
	 */
	protected function doCollect() {
		$set = parent::doCollect();
		$data = [];
		foreach ( $this->extractUserNames( $set->getRecords() ) as $userName ) {
			$relevantTs = null;
			$relevantRecords = array_filter(
				$this->extractRecordsByUserName( $set->getRecords(), $userName ),
				function ( Record $record ) use ( &$relevantTs )  {
					if ( !$relevantTs ) {
						$relevantTs = $this->createRelevantTimestampFromRecord(
							$record
						);
						return true;
					}
					$res = false;
					$recordTs = $this->createTimestampFromRecord( $record );
					if ( $relevantTs < $recordTs ) {
						$res = true;
					}
					$relevantTs = $this->createRelevantTimestampFromRecord(
						$record
					);

					return $res;
				}
			);
			$data = array_merge( $data, $relevantRecords );
		}
		return new RecordSet( $data );
	}

	/**
	 *
	 * @param Record $record
	 * @return DateTime
	 */
	protected function createRelevantTimestampFromRecord( Record $record ) {
		$idleSeconds = $this->config->get( 'WhoIsOnlineMaxIdleTime' );
		$ts = $this->createTimestampFromRecord( $record );
		$ts->modify( "+ $idleSeconds seconds" );
		return $ts;
	}

	/**
	 *
	 * @param Record $record
	 * @return DateTime
	 */
	protected function createTimestampFromRecord( Record $record ) {
		return DateTime::createFromFormat(
			'YmdHis',
			$record->get( Record::TIMESTAMP ),
			new DateTimeZone( 'UTC' )
		);
	}

	/**
	 * @param Record[] $records
	 * @return string[]
	 */
	protected function extractUserNames( array $records ) {
		$userNames = [];
		foreach ( $records as $record ) {
			$userNames[] = $record->get( Record::USER_NAME );
		}
		return array_unique( $userNames );
	}

	/**
	 * @param Record[] $records
	 * @param string $userName
	 * @return Record[]
	 */
	protected function extractRecordsByUserName( array $records, $userName ) {
		return array_filter( $records, function ( Record $record ) use ( $userName ) {
			return $record->get( Record::USER_NAME ) === $userName;
		} );
	}

	/**
	 *
	 * @param string $type
	 * @param MediaWikiServices $services
	 * @param Snapshot $snapshot
	 * @param Config|null $config
	 * @param EntityFactory|null $factory
	 * @param IStore|null $store
	 * @return DataCollector
	 */
	public static function factory( $type, MediaWikiServices $services, Snapshot $snapshot,
		Config $config = null, EntityFactory $factory = null, IStore $store = null ) {
		if ( !$config ) {
			$config = $snapshot->getConfig();
		}
		if ( !$factory ) {
			$factory = $services->getService( 'BSEntityFactory' );
		}
		if ( !$store ) {
			$store = new Store();
		}
		return new static( $type, $snapshot, $config, $factory, $store );
	}

	/**
	 *
	 * @return array
	 */
	protected function getFilter() {
		$ts = new Timestamp( $this->snapshot->get( Snapshot::ATTR_TIMESTAMP_CREATED ) );
		$range = new Daily( $ts );
		return array_merge( parent::getFilter(), [
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
		] );
	}

	/**
	 *
	 * @return array
	 */
	protected function getSort() {
		return [ (object)[
			Sort::KEY_PROPERTY => Record::TIMESTAMP,
			Sort::KEY_DIRECTION => Sort::ASCENDING
		] ];
	}

	/**
	 *
	 * @param IRecord $record
	 * @return \stdClass
	 */
	protected function map( IRecord $record ) {
		return (object)[
			Collection::ATTR_TYPE => Collection::TYPE,
			Collection::ATTR_LOGIN => 1,
			Collection::ATTR_TIMESTAMP_CREATED => $record->get( Record::TIMESTAMP ),
		];
	}
}
