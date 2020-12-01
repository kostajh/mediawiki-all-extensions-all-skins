<?php

namespace BlueSpice\PageAssignments\Tests;

use BlueSpice\PageAssignments\Data\Record;
use BlueSpice\Tests\BSApiTasksTestBase;
use BlueSpice\Tests\BSUserFixtures;
use BlueSpice\Tests\BSUserFixturesProvider;

/**
 * @group medium
 * @group Database
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignmentTasksTest extends BSApiTasksTestBase {

	protected $tablesUsed = [ 'bs_pageassignments' ];

	protected function setUp() : void {
		parent::setUp();
		new BSUserFixturesProvider();
		self::$userFixtures = new BSUserFixtures( $this );
	}

	protected function getModuleName() {
		return 'bs-pageassignment-tasks';
	}

	public function getTokens() {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	/**
	 * @covers \BlueSpice\PageAssignments\Api\Task\PageAssignments::task_edit
	 */
	public function testEdit() {
		$oData = $this->executeTask(
			'edit',
			[
				'pageId' => 1,
				'pageAssignments' => [
					'user/John',
					'group/sysop'
				]
			]
		);

		$this->assertTrue( $oData->success, "API returned failure state" );

		// Check if Assignment was added to database
		$this->assertSelect(
			'bs_pageassignments',
			[ 'pa_assignee_key', 'pa_assignee_type' ],
			[ 'pa_page_id = 1' ],
			[ [ 'John', 'user' ], [ 'sysop', 'group' ] ]
		);

		$oData = $this->executeTask(
			'edit',
			[
				'pageId' => 1,
				'pageAssignments' => [
				]
			]
		);

		$this->assertTrue( $oData->success, "API returned failure state" );

		// Check if Assignment was removed from database
		$this->assertSelect(
			'bs_pageassignments',
			[ 'pa_assignee_key', 'pa_assignee_type' ],
			[ 'pa_page_id = 1' ],
			[]
		);
	}

	/**
	 * @covers \BlueSpice\PageAssignments\Api\Task\PageAssignments::task_getForPage
	 */
	public function testGetForPage() {
		$oData = $this->executeTask(
			'edit',
			[
				'pageId' => 1,
				'pageAssignments' => [
					'user/John',
					'group/sysop'
				]
			]
		);

		$this->assertTrue( $oData->success, "API returned failure state" );

		$oData = $this->executeTask(
			'getForPage',
			[
				'pageId' => 1
			]
		);

		$this->assertTrue( $oData->success, "API returned failure state" );
		$this->assertArrayHasKey( 0, $oData->payload, "No assignment was returned" );
		$this->assertArrayHasKey( 1, $oData->payload, "Second assignment was not returned" );

		$aAssignment = $oData->payload[0];

		$this->assertArrayHasKey(
			Record::ASSIGNEE_TYPE,
			$aAssignment,
			"Assignment type is missing"
		);
		$this->assertEquals(
			'user',
			$aAssignment[Record::ASSIGNEE_TYPE],
			"Assignment type is not 'user'"
		);
		$this->assertArrayHasKey(
			Record::ID,
			$aAssignment,
			"Assignment id is missing"
		);
		$this->assertEquals(
			'user/John',
			$aAssignment[Record::ID],
			"Assignment id is not 'user/John'"
		);
		$this->assertArrayHasKey(
			Record::TEXT,
			$aAssignment,
			"Assignment text is missing"
		);
		$this->assertEquals(
			'John L.',
			$aAssignment[Record::TEXT],
			"Assignment text is not 'John L.'"
		);
		$this->assertArrayHasKey(
			Record::ANCHOR,
			$aAssignment,
			"Assignment anchor is missing"
		);
	}
}
