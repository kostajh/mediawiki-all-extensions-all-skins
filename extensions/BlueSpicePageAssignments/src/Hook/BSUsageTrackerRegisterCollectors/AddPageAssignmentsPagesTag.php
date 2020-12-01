<?php

namespace BlueSpice\PageAssignments\Hook\BSUsageTrackerRegisterCollectors;

use BS\UsageTracker\Hook\BSUsageTrackerRegisterCollectors;

class AddPageAssignmentsPagesTag extends BSUsageTrackerRegisterCollectors {

	protected function doProcess() {
		$this->collectorConfig['pageassignments:pages'] = [
			'class' => 'Database',
			'config' => [
				'identifier' => 'bs-usagetracker-pageassignments',
				'descriptionKey' => 'bs-usagetracker-pageassignments',
				'table' => 'bs_pageassignments',
				'uniqueColumns' => [ 'pa_page_id' ]
			]
		];
	}

}
