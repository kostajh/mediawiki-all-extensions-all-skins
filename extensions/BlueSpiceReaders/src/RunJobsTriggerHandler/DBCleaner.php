<?php

namespace BlueSpice\Readers\RunJobsTriggerHandler;

use BlueSpice\RunJobsTriggerHandler;
use MediaWiki\MediaWikiServices;
use MWTimestamp;
use Status;

class DBCleaner extends RunJobsTriggerHandler {

	/**
	 * @var int
	 */
	private $secondsInDay = 86400;

	/**
	 * @return Status
	 */
	protected function doRun() {
		$status = Status::newGood();

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		if ( $config->get( 'ReadersCleanData' ) !== true ) {
			return $status;
		}

		$daysToLive = (int)$config->get( 'ReadersCleanDataTTL' );

		if ( $daysToLive <= 0 ) {
			return $status;
		}

		$currentTS = MWTimestamp::getInstance( time() - $daysToLive * $this->secondsInDay )
			->getTimestamp( TS_MW );

		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$entriesCount = $dbr->select(
			'bs_readers',
			[ 'readers_id' ],
			[ 'readers_ts < ' . $currentTS ]
		)->numRows();

		if ( $entriesCount < 1 ) {
			return $status;
		}

		MediaWikiServices::getInstance()
			->getDBLoadBalancer()
			->getConnection( DB_MASTER )
			->delete(
				'bs_readers',
				[ 'readers_ts < ' . $currentTS ]
			);

		wfDebugLog(
			'BlueSpiceReaders',
			$entriesCount . ' old entries has been deleted from bs_readers table'
		);

		return $status;
	}

}
