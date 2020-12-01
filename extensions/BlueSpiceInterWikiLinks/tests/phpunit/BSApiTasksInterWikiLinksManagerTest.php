<?php

use BlueSpice\Tests\BSApiTasksTestBase;
use MediaWiki\MediaWikiServices;

/**
 * @group Broken
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceInterWikiLinksManager
 */
class BSApiTasksInterWikiLinksManagerTest extends BSApiTasksTestBase {
	protected function setUp() : void {
		parent::setUp();
		$GLOBALS['wgGroupPermissions']['user']['wikiadmin'] = true;
		$this->tablesUsed[] = 'interwiki';
	}

	/**
	 *
	 * @return string
	 */
	protected function getModuleName() {
		return 'bs-interwikilinks-tasks';
	}

	/**
	 * @covers \BSApiTasksInterWikiLinksManager::task_editInterWikiLink
	 */
	public function testCreateInterWikiLink() {
		$createData = $this->executeTask(
			'editInterWikiLink',
			[
				'prefix' => 'dummylink',
				'url' => 'http://some.wiki.com/$1'
			]
		);

		$this->assertTrue(
			$createData->success,
			"The interwiki link could not be created."
		);
		$this->assertTrue(
			$this->existsWithValue( 'dummylink', 'http://some.wiki.com/$1' ),
			"The new interwiki link does not exist in the database."
		);

		// Cache reset is needed here, so that MW updates the interwiki list already
		// during the test run.
		$this->clearCache();
	}

	/**
	 * @covers \BSApiTasksInterWikiLinksManager::task_editInterWikiLink
	 */
	public function testEditInterWikiLink() {
		$editData = $this->executeTask(
			'editInterWikiLink',
			[
				'oldPrefix' => 'dummylink',
				'prefix' => 'fauxlink',
				'url' => 'http://some.wiki.com/wiki/$1'
			]
		);

		$this->assertTrue(
			$editData->success,
			"The interwiki link could not be edited."
		);
		$this->assertTrue(
			$this->isDeleted( 'dummylink' ),
			"The old interwiki link still exists in the database."
		);
		$this->assertTrue(
			$this->existsWithValue( 'fauxlink', 'http://some.wiki.com/wiki/$1' ),
			"The new interwiki link does not exist in the database."
		);

		// Cache reset is needed here, so that MW updates the interwiki list already
		// during the test run.
		$this->clearCache();
	}

	/**
	 * @covers \BSApiTasksInterWikiLinksManager::task_removeInterWikiLink
	 */
	public function testRemoveInterWikiLink() {
		$deleteData = $this->executeTask(
			'removeInterWikiLink',
			[
				'prefix' => 'fauxlink'
			]
		);

		$this->assertTrue(
			$deleteData->success,
			"The interwiki link could not be deleted"
		);
		$this->assertTrue(
			$this->isDeleted( 'fauxlink' ),
			"The interwiki link is still present"
		);
	}

	protected function isDeleted( $sValue ) {
		$res = $this->db->select(
			'interwiki',
			[ 'iw_prefix' ],
			[ 'iw_prefix' => $sValue ],
			wfGetCaller()
		);
		return ( $res->numRows() === 0 ) ? true : false;
	}

	protected function existsWithValue( $prefix, $value ) {
		$res = $this->db->select(
			'interwiki',
			[ 'iw_prefix', 'iw_url' ],
			[
				'iw_prefix' => $prefix,
				'iw_url' => $value
			],
			wfGetCaller()
		);
		return ( $res->numRows() > 0 ) ? true : false;
	}

	protected function clearCache() {
		MediaWikiServices::getInstance()->getInterwikiLookup()->resetLocalCache();
	}
}
