<?php

namespace BlueSpice\CountThings\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddCountFilesTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:countfiles'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-bs:countfiles'
			]
		];
	}

}
