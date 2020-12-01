<?php

use BlueSpice\Special\ManagerBase;

/**
 * Special page for GroupManager of BlueSpice (MediaWiki)
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    BlueSpiceExtensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
class SpecialGroupManager extends ManagerBase {

	public function __construct() {
		parent::__construct( 'GroupManager', 'groupmanager-viewspecialpage' );
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-groupmanager-grid';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.groupManager'
		];
	}
}
