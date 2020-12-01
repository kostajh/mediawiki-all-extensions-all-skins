<?php

namespace BlueSpice\CategoryManager\Entity\Collection;

use BlueSpice\ExtendedStatistics\Entity\Collection;

class CategorizedPages extends Collection {
	const TYPE = 'categorizedpages';

	const ATTR_NAMESPACE_NAME = 'namespacename';
	const ATTR_CATEGORIZED_PAGES = 'categorizedpages';
	const ATTR_UNCATEGORIZED_PAGES = 'uncategorizedpages';
	const ATTR_CATEGORIZED_PAGES_AGGREGATED = 'categorizedpagesaggregated';
	const ATTR_UNCATEGORIZED_PAGES_AGGREGATED = 'uncategorizedpagesaggregated';
}
