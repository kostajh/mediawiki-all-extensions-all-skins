<?php

namespace BlueSpice\PageAssignments;

use BlueSpice\IAdminTool;
use Message;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'ManagePageAssignments' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-pageassignments-desc' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getName() {
		return wfMessage( 'managepageassignments' );
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses() {
		$classes = [
			'bs-admin-link bs-icon-profile'
		];

		return $classes;
	}

	/**
	 *
	 * @return array
	 */
	public function getDataAttributes() {
		return [];
	}

	/**
	 *
	 * @return array
	 */
	public function getPermissions() {
		$permissions = [
			'pageassignments'
		];
		return $permissions;
	}

}
