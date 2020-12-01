<?php

namespace FundraisingTranslateWorkflow\Tests;

use ApiTestCase;
use ApiUsageException;
use ExtensionRegistry;
use WikiMessageGroup;

/**
 * @coversDefaultClass FundraisingTranslateWorkflow\TranslateHooks
 *
 * @group Database
 * @group medium
 */
class HooksTest extends ApiTestCase {

	private const NORMAL_MESSAGE_GROUP = 'testgroup';
	private const FUNDRAISING_MESSAGE_GROUP = 'page-Fundraising/Foo';

	public function setUp() : void {
		global $wgFundraisingTranslateWorkflowPublishRight;

		parent::setUp();

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'Translate' ) ) {
			$this->markTestSkipped( 'Can only run test with Extension:Translate enabled' );
		}

		$this->setMwGlobals( [
			'wgTranslateWorkflowStates' => [
				'progress' => [ 'color' => 'd33' ],
				'published' => [ 'color' => 'aea' ],
			],
		] );

		// Mark the group review table to be reset for each test.
		$this->tablesUsed[] = 'translate_groupreviews';

		// Populate message group fixtures.
		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			function ( &$groups, &$deps, &$autoload ) {
				$normalGroup = new WikiMessageGroup( self::NORMAL_MESSAGE_GROUP, 'wewgweg' );
				$frGroup = new WikiMessageGroup( self::FUNDRAISING_MESSAGE_GROUP, 'wewgweg' );
				$groups[self::NORMAL_MESSAGE_GROUP] = $normalGroup;
				$groups[self::FUNDRAISING_MESSAGE_GROUP] = $frGroup;
			}
		);

		// Everyone can do normal review.
		$this->setGroupPermissions( 'user', 'translate-groupreview', true );
		// Nobody can do fundraising review.
		$this->setGroupPermissions( 'user', $wgFundraisingTranslateWorkflowPublishRight, false );
	}

	/**
	 * Normal message groups are unaffected.
	 *
	 * @covers ::onModifyMessageGroupStates
	 */
	public function testOnModifyMessageGroupStates_nonMatch() {
		list( $result ) = $this->doApiRequestWithToken( [
			'action' => 'groupreview',
			'group' => self::NORMAL_MESSAGE_GROUP,
			'language' => 'es',
			'state' => 'published',
		] );
		$expectedResult = [
			'group' => self::NORMAL_MESSAGE_GROUP,
			'language' => 'es',
			'state' => 'published',
		];
		$this->assertEquals( $expectedResult, $result['groupreview']['review'] );
	}

	/**
	 * Users without the right can't publish fundraising messages.
	 *
	 * @covers ::onModifyMessageGroupStates
	 */
	public function testOnModifyMessageGroupStates_matchDeny() {
		$this->expectException( ApiUsageException::class );
		$this->expectExceptionMessage( 'You don\'t have permission to manage message groups.' );
		list( $result ) = $this->doApiRequestWithToken( [
			'action' => 'groupreview',
			'group' => self::FUNDRAISING_MESSAGE_GROUP,
			'language' => 'es',
			'state' => 'published',
		] );
	}

	/**
	 * We have special permission to edit this group.
	 *
	 * @covers ::onModifyMessageGroupStates
	 */
	public function testOnModifyMessageGroupStates_matchAllow() {
		global $wgFundraisingTranslateWorkflowPublishRight;
		$this->setGroupPermissions( 'user', $wgFundraisingTranslateWorkflowPublishRight, true );

		list( $result ) = $this->doApiRequestWithToken( [
			'action' => 'groupreview',
			'group' => self::FUNDRAISING_MESSAGE_GROUP,
			'language' => 'es',
			'state' => 'published',
		] );
		$expectedResult = [
			'group' => self::FUNDRAISING_MESSAGE_GROUP,
			'language' => 'es',
			'state' => 'published',
		];
		$this->assertEquals( $expectedResult, $result['groupreview']['review'] );
	}

}
