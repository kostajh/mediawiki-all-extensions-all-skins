<?php

namespace BlueSpice\GroupManager;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return @return string String of the URL.
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'GroupManager' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return \Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-groupmanager-desc' );
	}

	/**
	 *
	 * @return \Message
	 */
	public function getName() {
		return wfMessage( 'groupmanager' );
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses() {
		$classes = [
			'bs-icon-group'
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
			'groupmanager-viewspecialpage'
		];
		return $permissions;
	}

}
