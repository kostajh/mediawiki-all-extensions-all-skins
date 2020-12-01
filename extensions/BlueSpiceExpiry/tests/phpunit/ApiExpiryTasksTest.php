<?php

use BlueSpice\Tests\BSApiTasksTestBase;

/**
 * @group Broken
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExpiry
 */
class ApiExpiryTasksTest extends BSApiTasksTestBase {
	protected function getModuleName() {
		return 'bs-expiry-tasks';
	}

	public function setUp() : void {
		parent::setUp();
		$this->insertPage( 'Dummy' );
	}

	/**
	 * @covers \ApiExpiryTasks::task_saveExpiry
	 */
	public function testSaveExpiry() {
		// New expiry
		$oTitle = Title::newFromText( 'Dummy' );
		$iNextWeek = time() + ( 7 * 24 * 60 * 60 );
		$oResponse = $this->executeTask(
			'saveExpiry',
			[
				'articleId' => $oTitle->getArticleID(),
				'date' => $iNextWeek,
				'comment' => 'Test expiry'
			]
		);

		$this->assertTrue( $oResponse->success, 'SaveExpiry (create) task failed' );
		$this->assertSelect(
			'bs_expiry',
			[ 'exp_date', 'exp_comment' ],
			[ 'exp_page_id' => $oTitle->getArticleID() ],
			[
				[ date( "Y-m-d", $iNextWeek ), 'Test expiry' ]
			]
		);

		// Update expiry
		$iExpiryId = $this->getExpiryFromArticleID( $oTitle->getArticleID() );

		$this->assertGreaterThan( 0, $iExpiryId, 'Failed to retrieve expiry from DB' );

		$iLastWeek = time() - ( 7 * 24 * 60 * 60 );
		$oResponse = $this->executeTask(
			'saveExpiry',
			[
				'articleId' => $oTitle->getArticleID(),
				'date' => $iLastWeek,
				'comment' => 'Updated expiry',
				'id' => $iExpiryId
			]
		);
		$this->assertTrue( $oResponse->success, 'SaveExpiry (update) task failed' );
		$this->assertSelect(
			'bs_expiry',
			[ 'exp_date', 'exp_comment' ],
			[ 'exp_page_id' => $oTitle->getArticleID() ],
			[
				[ date( "Y-m-d", $iLastWeek ), 'Updated expiry' ]
			]
		);
	}

	/**
	 * @covers \ApiExpiryTasks::task_getDetailsForExpiry
	 */
	public function testGetDetailsForExpiry() {
		$oTitle = Title::newFromText( 'Dummy' );
		$oResponse = $this->executeTask(
			'getDetailsForExpiry',
			[
				'articleId' => $oTitle->getArticleID()
			]
		);

		$this->assertTrue( $oResponse->success, 'GetDetailsForExpiry task failed' );
		$aPayload = $oResponse->payload;
		$this->assertEquals(
			date( "Y-m-d", time() - ( 7 * 24 * 60 * 60 ) ),
			$aPayload['date'],
			'Returned expiry has unexpected date'
		);
	}

	/**
	 * @covers \ApiExpiryTasks::task_deleteExpiry
	 */
	public function testDeleteExpiry() {
		$oTitle = Title::newFromText( 'Dummy' );
		$iArticleId = $oTitle->getArticleID();
		$oResponse = $this->executeTask(
			'deleteExpiry',
			[
				'articleId' => $iArticleId,
				'expiryId' => $this->getExpiryFromArticleID( $iArticleId )
			]
		);

		$this->assertTrue( $oResponse->success, 'DeleteExpiry task failed' );
		$this->assertSame(
			0,
			$this->getExpiryFromArticleID( $iArticleId ),
			'DeleteExpiry task succeded, but expiry is not deleted'
		);
	}

	protected function getExpiryFromArticleID( $iArticleId ) {
		$res = wfGetDB( DB_REPLICA )->select(
			'bs_expiry',
			'exp_id',
			[
				'exp_page_id' => $iArticleId
			]
		);
		$iExpiryId = 0;
		if ( $res && $res->numRows() ) {
			$row = $res->fetchRow();
			$iExpiryId = (int)$row['exp_id'];
		}
		return $iExpiryId;
	}
}
