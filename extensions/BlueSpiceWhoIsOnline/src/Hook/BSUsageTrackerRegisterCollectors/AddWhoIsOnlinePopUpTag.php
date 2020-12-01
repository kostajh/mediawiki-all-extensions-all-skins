<?php

namespace BlueSpice\WhoIsOnline\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddWhoIsOnlinePopUpTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:whoisonline:popup'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-userslink'
			]
		];
	}

}
