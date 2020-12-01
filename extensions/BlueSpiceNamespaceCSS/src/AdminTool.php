<?php

namespace BlueSpice\NamespaceCSS;

use BlueSpice\IAdminTool;
use Message;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'BlueSpiceNamespaceCSSManager' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return Message
	 */
	public function getDescription() {
		return wfMessage( 'bs-namespacecss-desc' );
	}

	/**
	 *
	 * @return Message
	 */
	public function getName() {
		return wfMessage( 'bluespicenamespacecssmanager' );
	}

	/**
	 *
	 * @return array
	 */
	public function getClasses() {
		return [
			'bs-icon-painting-roll'
		];
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
		return [
			'editinterface'
		];
	}

}
