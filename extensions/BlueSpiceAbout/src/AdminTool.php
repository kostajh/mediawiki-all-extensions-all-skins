<?php

namespace BlueSpice\About;

use BlueSpice\IAdminTool;
use RequestContext;

class AdminTool implements IAdminTool {

	/**
	 *
	 * @return string
	 */
	public function getURL() {
		$tool = \SpecialPage::getTitleFor( 'BlueSpiceAbout' );
		return $tool->getLocalURL();
	}

	/**
	 *
	 * @return \Message
	 */
	public function getDescription() {
		return RequestContext::getMain()->msg( 'bs-bluespiceabout-desc' );
	}

	/**
	 *
	 * @return \Message
	 */
	public function getName() {
		return RequestContext::getMain()->msg( 'bs-bluespiceabout-about-bluespice' );
	}

	/**
	 *
	 * @return string[]
	 */
	public function getClasses() {
		return [ 'icon-admin-bluespiceabout' ];
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
			'bluespiceabout-viewspecialpage'
		];
		return $permissions;
	}

}
