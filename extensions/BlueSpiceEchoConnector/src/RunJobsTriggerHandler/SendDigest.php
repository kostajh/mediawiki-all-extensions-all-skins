<?php

namespace BlueSpice\EchoConnector\RunJobsTriggerHandler;

use BlueSpice\RunJobsTriggerHandler;
use MediaWiki\MediaWikiServices;

class SendDigest extends RunJobsTriggerHandler {
	protected $batchSize = 300;

	protected function doRun() {
		$status = \Status::newGood();

		$startUserId = 0;
		$count = $this->batchSize;

		wfDebugLog( 'BlueSpiceEchoConnector', "Sending digest mails - batch size: {$this->batchSize}" );
		while ( $count === $this->batchSize ) {
			$count = 0;

			$res = \BlueSpice\EchoConnector\EchoEmailBatch::getUsersToNotify(
				$startUserId,
				$this->batchSize
			);

			$updated = false;
			foreach ( $res as $row ) {
				$userId = intval( $row->eeb_user_id );
				wfDebugLog( 'BlueSpiceEchoConnector', "Processing user_id $userId" );

				if ( $userId && $userId > $startUserId ) {
					$emailBatch = \BlueSpice\EchoConnector\EchoEmailBatch::newFromUserId(
						$userId,
						false
					);
					if ( $emailBatch ) {
						$emailBatch->process();
					}
					$startUserId = $userId;
					$updated = true;
				}
				$count++;
			}

			$echoCluster = MediaWikiServices::getInstance()->getMainConfig()->get(
				'EchoCluster'
			);
			MediaWikiServices::getInstance()->getDBLoadBalancerFactory()->waitForReplication( [
				'cluster' => $echoCluster
			] );
			// This is required since we are updating user properties in main wikidb
			MediaWikiServices::getInstance()->getDBLoadBalancerFactory()->waitForReplication();

			// double check to make sure that the id is updated
			if ( !$updated ) {
				break;
			}
		}

		wfDebugLog( 'BlueSpiceEchoConnector', "Completed sending digest mails" );

		return $status;
	}

	/**
	 * For clarity
	 * @return RunJobsTriggerHandler\Interval|RunJobsTriggerHandler\Interval\OnceADay
	 */
	public function getInterval() {
		return new RunJobsTriggerHandler\Interval\OnceADay();
	}

}
