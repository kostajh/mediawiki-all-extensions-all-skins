<?php

namespace BlueSpice\PageAssignments\Data\DataCollector\AssignedPages;

use BlueSpice\Data\Record as BaseRecord;
use BlueSpice\PageAssignments\Entity\Collection\AssignedPages;

class Record extends BaseRecord {
	const ASSIGNED = AssignedPages::ATTR_ASSIGNED_PAGES;
	const UNASSIGNED = AssignedPages::ATTR_UNASSIGNED_PAGES;
	const ASSIGNED_AGGREGATED = AssignedPages::ATTR_ASSIGNED_PAGES_AGGREGATED;
	const UNASSIGNED_AGGREGATED = AssignedPages::ATTR_UNASSIGNED_PAGES_AGGREGATED;
	const NAMESPACE_NAME = AssignedPages::ATTR_NAMESPACE_NAME;
}
