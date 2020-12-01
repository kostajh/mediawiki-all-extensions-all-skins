<?php

namespace BlueSpice\HideTitle\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddHideTitleTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:hidetitle'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs_hidetitle'
			]
		];
	}

}
