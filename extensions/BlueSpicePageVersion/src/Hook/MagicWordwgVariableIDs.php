<?php

namespace BS\PageVersion\Hook;

class MagicWordwgVariableIDs {

	/**
	 * Adds magic word ids to Parser
	 * @param array &$customVariableIds
	 * @return bool
	 */
	public function handle( &$customVariableIds ) {
		$customVariableIds[] = 'pageversion';
		$customVariableIds[] = 'pagerevisions';
		$customVariableIds[] = 'pagemajorrevisions';

		return true;
	}
}
