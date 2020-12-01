<?php

namespace BlueSpice\CategoryManager;

use BlueSpice\IAdminTool;
use Message;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'BlueSpiceCategoryManager' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getDescription() {
		return wfMessage( 'bluespicecategorymanager-desc' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getName() {
		return wfMessage( 'bs-categorymanager-label' );
	}

	/**
	 *
	 * @return string[]
	 */
	public function getClasses() {
		$classes = [
			'bs-icon-tag'
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
			'categorymanager-viewspecialpage'
		];
		return $permissions;
	}

}
