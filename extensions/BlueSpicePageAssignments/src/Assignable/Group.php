<?php

namespace BlueSpice\PageAssignments\Assignable;

use BlueSpice\PageAssignments\Data\Assignable\Group\Store;
use MediaWiki\MediaWikiServices;

class Group extends \BlueSpice\PageAssignments\Assignable {

	/**
	 *
	 * @return Store
	 */
	public function getStore() {
		return new Store(
			$this->context,
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 *
	 * @return string
	 */
	public function getAssignmentClass() {
		return "\\BlueSpice\\PageAssignments\\Assignment\\Group";
	}

	/**
	 *
	 * @return string
	 */
	public function getTypeMessageKey() {
		return "bs-pageassignments-assignee-type-group";
	}
}
