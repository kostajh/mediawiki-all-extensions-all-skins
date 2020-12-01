<?php

namespace ConfigManager;

use BlueSpice\IAdminTool;
use Message;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'ConfigManager' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getDescription() {
		return wfMessage( 'configmanager-desc' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getName() {
		return wfMessage( 'configmanager-admintool-label' );
	}

	/**
	 *
	 * @return string[]
	 */
	public function getClasses() {
		$classes = [
			'bs-icon-wrench'
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
	 * @return string[]
	 */
	public function getPermissions() {
		$permissions = [
			'configmanager-viewspecialpage'
		];
		return $permissions;
	}

}
