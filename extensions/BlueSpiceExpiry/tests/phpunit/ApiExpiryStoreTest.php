<?php

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group Broken
 * @group medium
 * @group api
 * @group BlueSpice
 * @group BlueSpiceExpiry
 */
class ApiExpiryStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 2;

	protected function getStoreSchema() {
		return [
			'id' => [
				'type' => 'integer'
			],
			'page_title' => [
				'type' => 'string'
			],
			'page_link' => [
				'type' => 'string'
			],
			'expiry_date' => [
				'type' => 'string'
			],
			'article_id' => [
				'type' => 'integer'
			],
			'exp_comment' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
		$aArticleIds = [];
		$aArticleIds[] = $this->insertPage( 'DummyPage' )['id'];
		$aArticleIds[] = $this->insertPage( 'FakePage' )['id'];

		$oDbw = wfGetDB( DB_MASTER );
		// $oDbw->delete( 'bs_expiry', [ 'pa_page_id' => $oTitle->getArticleID() ] );
		$aExpiryData = [
				[
					'exp_page_id' => $aArticleIds[0],
					'exp_comment' => "DummyPage comment",
					'exp_date' => date( "Y-m-d", time() + ( 7 * 24 * 60 * 60 ) )
				],
				[
					'exp_page_id' => $aArticleIds[1],
					'exp_comment' => "FakePage comment",
					'exp_date' => date( "Y-m-d", time() - ( 21 * 24 * 60 * 60 ) )
				]
			];
		$oDbw->insert( 'bs_expiry', $aExpiryData, __METHOD__ );

		return 2;
	}

	protected function getModuleName() {
		return 'bs-expiry-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by page_title' => [ 'string', 'eq', 'page_title', 'DummyPage', 1 ]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by page_title and expiry_date' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'page_title',
						'value' => 'Page'
					],
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'expiry_date',
						'value' => date( "Y-m-d", time() - ( 21 * 24 * 60 * 60 ) )
					]
				],
				1
			]
		];
	}

	public function provideKeyItemData() {
		return [
			'Test page DummyPage: exp_comment' => [
				"exp_comment", "DummyPage comment"
			],
			'Test page DummyPage: expiry_date' => [
				"expiry_date",
				date( "Y-m-d", time() + ( 7 * 24 * 60 * 60 ) )
			]
		];
	}
}
