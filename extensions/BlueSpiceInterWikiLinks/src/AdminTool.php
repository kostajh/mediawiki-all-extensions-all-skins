<?php

namespace BlueSpice\InterWikiLinks;

use BlueSpice\IAdminTool;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'InterWikiLinks' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return \Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-interwikilinks-desc' );
	}

	/**
	 *
	 * @return \Message
	 */
	public function getName() {
		return wfMessage( 'bs-interwikilinks-label' );
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses() {
		$classes = [
			'bs-icon-chain'
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
			'interwikilinks-viewspecialpage'
		];
		return $permissions;
	}

}
