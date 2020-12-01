<?php

namespace BlueSpice\Social\Hook\BSRSSFeederBeforeGetRecentChanges;

use BlueSpice\RSSFeeder\Hook\BSRSSFeederBeforeGetRecentChanges;

class RCRemoveSocial extends BSRSSFeederBeforeGetRecentChanges {
	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		if ( $this->feedType === 'namespace' ) {
			return true;
		}
		return false;
	}

	/**
	 * Do not show social entities in recent changes
	 * @return bool
	 */
	protected function doProcess() {
		switch ( $this->feedType ) {
			case 'recentchanges':
			case 'followOwn':
			case 'followPage':
				$this->conditions[] = 'rc_namespace != ' . NS_SOCIALENTITY;
				break;
			case 'category':
			case 'watchlist':
				$this->conditions[] = 'r.rc_namespace != ' . NS_SOCIALENTITY;
				break;
		}
		return true;
	}

}
