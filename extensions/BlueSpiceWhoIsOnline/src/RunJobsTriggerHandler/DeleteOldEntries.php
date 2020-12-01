<?php

namespace BlueSpice\WhoIsOnline\RunJobsTriggerHandler;

use BlueSpice\RunJobsTriggerHandler;
use BlueSpice\RunJobsTriggerHandler\Interval;
use BlueSpice\RunJobsTriggerHandler\Interval\OnceADay;
use BlueSpice\Timestamp;
use DateTime;
use Exception;
use Status;

class DeleteOldEntries extends RunJobsTriggerHandler {

	/**
	 *
	 * @return Interval
	 */
	public function getInterval() {
		return new OnceADay();
	}

	/**
	 * @return Status
	 */
	protected function doRun() {
		$status = Status::newGood();

		$oneHourAgo = Timestamp::getInstance(
			( new DateTime() )->modify( '-1 day' )
		);
		try {
			$this->loadBalancer->getConnection( DB_MASTER )->delete(
				'bs_whoisonline',
				[ "wo_log_ts < {$oneHourAgo->getTimestamp( TS_MW )}" ],
				__METHOD__
			);
		} catch ( Exception $e ) {
			$status->fatal( $e->getMessage() );
		}

		return $status;
	}

}
