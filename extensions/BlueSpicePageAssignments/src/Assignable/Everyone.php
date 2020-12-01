<?php

namespace BlueSpice\PageAssignments\Assignable;

use BlueSpice\PageAssignments\Data\Assignable\Everyone\Store;
use MediaWiki\MediaWikiServices;

class Everyone extends \BlueSpice\PageAssignments\Assignable {

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
		return "\\BlueSpice\\PageAssignments\\Assignment\\Everyone";
	}

	/**
	 *
	 * @return string
	 */
	public function getTypeMessageKey() {
		return "bs-pageassignments-assignee-type-everyone";
	}
}
