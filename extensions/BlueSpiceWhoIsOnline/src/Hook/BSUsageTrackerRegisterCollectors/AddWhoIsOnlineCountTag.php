<?php

namespace BlueSpice\WhoIsOnline\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddWhoIsOnlineCountTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:whoisonline:count'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-userscount'
			]
		];
	}

}
