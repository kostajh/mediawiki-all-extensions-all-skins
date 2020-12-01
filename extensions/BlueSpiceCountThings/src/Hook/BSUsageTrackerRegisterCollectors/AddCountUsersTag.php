<?php

namespace BlueSpice\CountThings\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddCountUsersTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:countusers'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-bs:countusers'
			]
		];
	}

}
