<?php

namespace BlueSpice\SmartList\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddSmartlistTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:smartlist'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-smartlist'
			]
		];
	}

}
