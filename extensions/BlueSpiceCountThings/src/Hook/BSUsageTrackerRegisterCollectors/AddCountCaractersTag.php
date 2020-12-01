<?php

namespace BlueSpice\CountThings\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddCountCaractersTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:countcharacters'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-bs:countcharacters'
			]
		];
	}

}
