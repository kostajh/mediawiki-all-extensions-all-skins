<?php

use BlueSpice\Special\ManagerBase;

class SpecialBlueSpiceCategoryManager extends ManagerBase {

	public function __construct() {
		parent::__construct( 'BlueSpiceCategoryManager', 'categorymanager-viewspecialpage' );
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-categorymanager-grid';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			"ext.bluespice.categorymanager"
		];
	}
}
