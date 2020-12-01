<?php

namespace BlueSpice\PageAssignments\Tests;

use BlueSpice\PageAssignments\Data\Record;
use BlueSpice\PageAssignments\Data\Schema;
use BlueSpice\Tests\BSApiExtJSStoreTestBase;
use BlueSpice\Tests\BSUserFixtures;
use BlueSpice\Tests\BSUserFixturesProvider;

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignableStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 9;

	protected $tablesUsed = [ 'page' ];

	protected function skipAssertTotal() {
		return true;
	}

	protected function setUp() : void {
		parent::setUp();
		new BSUserFixturesProvider();
		$this->insertPage( "Test", "Dummy content" );
	}

	protected function getStoreSchema() {
		return new Schema();
	}

	protected function createStoreFixtureData() {
		self::$userFixtures = new BSUserFixtures( $this );
		return true;
	}

	protected function getModuleName() {
		return 'bs-pageassignable-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by id' => [ 'string', 'ct', 'id', 'John', 1 ],
			'Filter by text' => [ 'string', 'eq', 'text', 'Ringo S.', 1 ]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by type and id' => [
				[
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => Record::ASSIGNEE_TYPE,
						'value' => 'group'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => Record::ID,
						'value' => 'bot'
					]
				],
				1
			]
		];
	}

	public function provideKeyItemData() {
		return [
			'Test user John: text' => [ "text", "John L." ],
			'Test user Paul: text' => [ "text", "Paul M." ]
		];
	}

	/**
	 * Allows subclasses to add custom parameters
	 * to the API calls
	 * @return array
	 */
	protected function getAdditionalParams() {
		return [ 'context' => \FormatJson::encode(
			$this->makeContextParams()
		) ];
	}

	protected function makeContextParams() {
		return (object)[
			'wgAction' => "view",
			'wgArticleId' => (int)\Title::newFromText( 'Test' )->getArticleID(),
			'wgCanonicalNamespace' => "",
			'wgCanonicalSpecialPageName' => false,
			'wgNamespaceNumber' => 0,
			'wgPageName' => "Test",
			'wgRedirectedFrom' => null,
			'wgRelevantPageName' => "Test",
			'wgTitle' => "Test"
		];
	}
}
