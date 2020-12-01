<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );
require_once "$IP/maintenance/Maintenance.php";

class BSRatingRemoveDuplicateEntries extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return true
	 */
	protected function doDBUpdates() {
		$ids = $ips = $deleteEntries = [];
		$res = $this->getDB( DB_MASTER )->select(
			'bs_rating',
			'*',
			[],
			__METHOD__,
			[ 'ORDER BY' => 'rat_touched desc' ]
		);
		foreach ( $res as $row ) {
			if ( isset( $ids
					[ $row->rat_reftype ]
					[ $row->rat_ref ]
					[ $row->rat_subtype ]
					[ $row->rat_context ]
				) && $ids
					[ $row->rat_reftype ]
					[ $row->rat_ref ]
					[ $row->rat_subtype ]
					[ $row->rat_context ]
				== $row->rat_userid
			) {
				$deleteEntries[] = $row->rat_id;
				continue;
			}
			if ( isset( $ips
					[ $row->rat_reftype ]
					[ $row->rat_ref ]
					[ $row->rat_subtype ]
					[ $row->rat_context ]
				) && $ips
					[ $row->rat_reftype ]
					[ $row->rat_ref ]
					[ $row->rat_subtype ]
					[ $row->rat_context ]
				== $row->rat_userid
			) {
				$deleteEntries[] = $row->rat_id;
				continue;
			}
			$ids
				[ $row->rat_reftype ]
				[ $row->rat_ref ]
				[ $row->rat_subtype ]
				[ $row->rat_context ]
				= $row->rat_userid;
			$ips
				[ $row->rat_reftype ]
				[ $row->rat_ref ]
				[ $row->rat_subtype ]
				[ $row->rat_context ]
				= $row->rat_userip;
		}
		if ( empty( $deleteEntries ) ) {
			return true;
		}
		$b = $this->getDB( DB_MASTER )->delete(
			'bs_rating',
			[ 'rat_id' => $deleteEntries ]
		);
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return 'bs_rating-removeduplicateentries';
	}

}
