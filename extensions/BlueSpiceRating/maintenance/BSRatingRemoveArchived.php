<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once "$IP/maintenance/Maintenance.php";

class BSRatingRemoveArchived extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return true
	 */
	protected function doDBUpdates() {
		$this->getDB( DB_MASTER )->delete(
			'bs_rating',
			[ 'rat_archived' => '1' ],
			__METHOD__
		);

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_rating-removearchived';
	}

}
