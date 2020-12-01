<?php

namespace BlueSpice\Bookshelf\Special;

use BlueSpice\Special\ManagerBase;
use MediaWiki\MediaWikiServices;
use stdClass;

class BookshelfBookManager extends ManagerBase {

	public function __construct() {
		parent::__construct(
			'BookshelfBookManager',
			'bookshelfbookmanager-viewspecialpage',
			true
		);
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-bookshelf-managerpanel';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.bookshelf.manager'
		];
	}

	/**
	 *
	 * @return array
	 */
	protected function getJSVars() {
		$config = new stdClass();
		$config->dependencies = [
			'ext.bluespice.extjs'
		];
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSBookshelfBookManager', [
			$this,
			$this->getOutput(),
			$config
		] );

		return [
			'bsBookshelfBookManagerConfig' => $config
		];
	}
}
