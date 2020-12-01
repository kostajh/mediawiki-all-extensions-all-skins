<?php

namespace BlueSpice\PageAssignments\Entity\Collection;

use BlueSpice\ExtendedStatistics\Entity\Collection;

class AssignedPages extends Collection {
	const TYPE = 'assignedpages';

	const ATTR_NAMESPACE_NAME = 'namespacename';
	const ATTR_ASSIGNED_PAGES = 'assignedpages';
	const ATTR_UNASSIGNED_PAGES = 'unassignedpages';
	const ATTR_ASSIGNED_PAGES_AGGREGATED = 'assignedpagesaggregated';
	const ATTR_UNASSIGNED_PAGES_AGGREGATED = 'unassignedpagesaggregated';
}
