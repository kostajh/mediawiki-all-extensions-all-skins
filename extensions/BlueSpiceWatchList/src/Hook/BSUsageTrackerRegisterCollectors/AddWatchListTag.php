<?php

namespace BlueSpice\WatchList\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddWatchListTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:watchlist'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-watchlist'
			]
		];
	}

}
