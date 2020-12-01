<?php

namespace BlueSpice\ExtendedStatistics\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddStatisticsProgressTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['bs:statistics:progress'] = [
			'class' => 'Property',
			'config' => [
				'identifier' => 'bs-tag-statistics-progress'
			]
		];
	}

}
