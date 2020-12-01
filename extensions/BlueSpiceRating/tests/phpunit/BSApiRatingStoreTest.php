<?php

namespace BlueSpice\Rating\Tests;

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group Broken
 * @group medium
 * @group api
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceRating
 */
class BSApiRatingStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 2;

	protected $tablesUsed = [ 'page', 'bs_rating' ];

	protected function skipAssertTotal() {
		return true;
	}

	protected function getStoreSchema() {
		return [
			'rat_ref' => [
				'type' => 'integer'
			],
			'rat_reftype' => [
				'type' => 'string'
			],
			'vote' => [
				'type' => 'integer'
			],
			'votes' => [
				'type' => 'integer'
			],
			'refcontent' => [
				'type' => 'string'
			],
			'page_title' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
	}

	public function addDBData() {
		$oTitle = \Title::newFromId( 1 );

		$oUserSysop = self::$users['sysop']->getUser();
		$oUserUploader = self::$users['uploader']->getUser();
		$oDbw = $this->db;
		$oDbw->insert( 'bs_rating', [
			'rat_ref' => $oTitle->getArticleId(),
			'rat_reftype' => 'article',
			'rat_userid' => $oUserSysop->getID(),
			'rat_userip' => $oUserSysop->getName(),
			'rat_value' => 4
		] );

		$oDbw->insert( 'bs_rating', [
			'rat_ref' => $oTitle->getArticleId(),
			'rat_reftype' => 'article',
			'rat_userid' => $oUserUploader->getID(),
			'rat_userip' => $oUserUploader->getName(),
			'rat_value' => 2
		] );

		$oNewTitle = $this->insertPage( 'DummyPage' )['title'];
		$oDbw->insert( 'bs_rating', [
			'rat_ref' => $oNewTitle->getArticleId(),
			'rat_reftype' => 'article',
			'rat_userid' => $oUserSysop->getID(),
			'rat_userip' => $oUserSysop->getName(),
			'rat_value' => 5
		] );

		return 2;
	}

	protected function getModuleName() {
		return 'bs-rating-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by page_title' => [ 'string', 'eq', 'page_title', 'DummyPage', 1 ]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by page_title and vote' => [
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
						'field' => 'vote',
						'value' => '5.0'
					]
				],
				1
			]
		];
	}

	public function provideKeyItemData() {
		return [
			'Test page UTPage: vote' => [ "vote", "3.0" ],
			'Test page UTPage: votes' => [ "votes", "2" ],
			'Test user DummyPage: votes' => [ "votes", "1" ]
		];
	}
}
