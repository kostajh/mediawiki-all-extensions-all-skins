<?php

namespace BlueSpice\CountThings\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddCountArticlesTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:countarticles'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-bs:countarticles'
			]
		];
	}

}
