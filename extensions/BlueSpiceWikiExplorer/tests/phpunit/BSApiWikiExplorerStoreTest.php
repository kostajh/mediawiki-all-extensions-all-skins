<?php

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group Broken
 * @group medium
 * @group api
 * @group BlueSpice
 * @group BlueSpiceWikiExplorer
 */
class BSApiWikiExplorerStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 3;

	protected function getStoreSchema() {
		return [
			'page_id' => [
				'type' => 'integer'
			],
			'page_namespace' => [
				'type' => 'integer'
			],
			'page_title' => [
				'type' => 'string'
			],
			'page_created' => [
				'type' => 'string'
			],
			'page_categories' => [
				'type' => 'array'
			],
			'page_links' => [
				'type' => 'array'
			],
			'page_linked_files' => [
				'type' => 'array'
			],
			'page_is_redirect' => [
				'type' => 'integer'
			],
			'page_is_new' => [
				'type' => 'integer'
			],
			'page_touched' => [
				'type' => 'string'
			],
			'page_len' => [
				'type' => 'integer'
			],
			'page_latest' => [
				'type' => 'integer'
			],
			'rev_comment' => [
				'type' => 'string'
			],
			'rev_user_text' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
		$this->insertPage( 'DummyPage', '15charactertext' );
		$this->insertPage( 'FakePage' );

		return 2;
	}

	protected function getModuleName() {
		return 'bs-wikiexplorer-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by page_title' => [ 'string', 'eq', 'page_title', 'DummyPage', 1 ]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by page_title and page_len' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'page_title',
						'value' => 'Page'
					],
					[
						'type' => 'numeric',
						'comparison' => 'eq',
						'field' => 'page_len',
						'value' => 15
					]
				],
				1
			]
		];
	}

	public function provideKeyItemData() {
		return [
			'Test page UTPage: page_namespace' => [ "page_namespace", 0 ],
			'Test page DummyPage: page_len' => [ "page_len", 15 ],
			'Test all pages: rev_user_text' => [ "rev_user_text", "UTSysop" ]
		];
	}
}
