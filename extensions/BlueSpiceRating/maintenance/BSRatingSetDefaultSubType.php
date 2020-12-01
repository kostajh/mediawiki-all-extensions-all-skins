<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once "$IP/maintenance/Maintenance.php";

class BSRatingSetDefaultSubType extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return true
	 */
	protected function doDBUpdates() {
		$this->getDB( DB_MASTER )->update(
			'bs_rating',
			[ 'rat_subtype' => 'default' ],
			[ 'rat_subtype' => '' ],
			__METHOD__
		);

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_rating-setdefaultsubtype';
	}

}
