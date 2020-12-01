<?php

namespace BlueSpice\PageAssignments\Tests;

use BlueSpice\Tests\BSApiExtJSStoreTestBase;

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignmentStoreTest extends BSApiExtJSStoreTestBase {
	/**
	 *
	 * @var int
	 */
	protected $iFixtureTotal = 7;

	/**
	 *
	 * @var array
	 */
	protected $aPages = [
		'UT_PageAssignmentStore_Test' => [ 'key' => 'sysop', 'type' => 'group' ],
		'UT_PageAssignmentStore_Test2' => [ 'key' => 'bureaucrat', 'type' => 'group' ],
		'UT_PageAssignmentStore_Test3' => [ 'key' => 'Apitestsysop', 'type' => 'user' ],
		'UT_PageAssignmentStore_Test4' => [ 'key' => 'UTSysop', 'type' => 'user' ],
		'UT_PageAssignmentStore_Test5' => [ 'key' => 'PASysop', 'type' => 'group' ],
		'UT_PageAssignmentStore_Test6' => [ 'key' => 'sysop', 'type' => 'group' ]
	];

	/**
	 *
	 * @return true
	 */
	protected function skipAssertTotal() {
		return true;
	}

	/**
	 *
	 * @return array
	 */
	protected function getStoreSchema() {
		return [
			'page_id' => [
				'type' => 'integer'
			],
			'page_prefixedtext' => [
				'type' => 'string'
			],
			'assignments' => [
				'type' => 'array'
			]
		];
	}

	/**
	 *
	 * @return bool
	 */
	protected function createStoreFixtureData() {
		$dbw = $this->db;

		$iCount = 1;
		foreach ( $this->aPages as $sPage => $aData ) {
			$aRes = $this->insertPage( $sPage );
			$iPageId = $aRes['id'];
			$this->assertGreaterThan( 0, $iPageId );
			$dbw->insert( 'bs_pageassignments', [
				'pa_page_id' => $iPageId,
				'pa_assignee_key' => $aData['key'],
				'pa_assignee_type' => $aData['type'],
				'pa_position' => $iCount
			] );

			$iCount++;
		}
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getModuleName() {
		return 'bs-pageassignment-store';
	}

	/**
	 *
	 * @return array
	 */
	public function provideSingleFilterData() {
		return [
			'Filter by page_prefixedtext' => [
				'string',
				'ct',
				'page_prefixedtext',
				"UT PageAssignmentStore Test",
				4
			],
			'Filter by page_prefixedtext' => [
				'string',
				'ct',
				'page_prefixedtext',
				"UT PageAssignmentStore Test3",
				1
			]
		];
	}

	/**
	 *
	 * @return array
	 */
	public function provideMultipleFilterData() {
		return [
			'Filter by page_prefixedtext and assignment' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'page_prefixedtext',
						'value' => 'UT PageAssignmentStore'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'assignments',
						'value' => 'PASysop'
					]
				],
				1
			]
		];
	}
}
