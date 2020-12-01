<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Jade\Tests\Hooks;

use Jade\Content\EntityContent;
use Jade\EntityLinkTable;
use Jade\EntityTarget;
use Jade\EntityType;
use Jade\Hooks\LinkSummaryHooks;
use Jade\Tests\TestStorageHelper;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Storage\EditResult;
use MediaWikiTestCase;
use Status;
use Title;
use WikiPage;

/**
 * @group Jade
 * @group Database
 * @group medium
 *
 * @coversDefaultClass \Jade\Hooks\LinkSummaryHooks
 */
class LinkSummaryHooksTest extends MediaWikiTestCase {

	// Include assertions to test Jade entity links.

	protected function setUp() : void {
		parent::setUp();

		$this->tablesUsed = [
			'jade_diff_label',
			'jade_revision_label',
			'page',
		];

		$this->mockStorage = $this->getMockBuilder( EntityLinkTable::class )
			->disableOriginalConstructor()->getMock();
		$this->setService( 'JadeEntityIndexStorage', $this->mockStorage );

		$this->targetRevId = mt_rand();

		$status = EntityType::sanitizeEntityType( 'revision' );
		$this->assertTrue( $status->isOK() );
		$this->revisionType = $status->value;

		$this->entityPageTitle = Title::newFromText( "Jade:Revision/{$this->targetRevId}" );

		$this->mockEntityPage = $this->createMock( WikiPage::class );
		$this->mockEntityPage->method( 'getTitle' )
			->willReturn( $this->entityPageTitle );

		$this->user = $this->getTestUser()->getUser();
	}

	/**
	 * @covers ::onPageSaveComplete
	 */
	public function testOnPageSaveComplete_success() {
		$flags = EDIT_UPDATE;
		$expectedSummaryValues = [
			'damaging' => false,
			'goodfaith' => true,
		];
		$this->mockStorage->expects( $this->once() )
			->method( 'updateSummary' )
			->with(
				new EntityTarget( $this->revisionType, $this->targetRevId ),
				$expectedSummaryValues )
			->willReturn( Status::newGood() );

		$contentText = TestStorageHelper::getJudgmentText( 'diff' );

		$this->mockEntityPage->method( 'getContent' )
			->willReturn( new EntityContent( $contentText ) );
		LinkSummaryHooks::onPageSaveComplete(
			$this->mockEntityPage,
			$this->user,
			'',
			$flags,
			$this->createMock( RevisionRecord::class ),
			$this->createMock( EditResult::class )
		);
	}

	/**
	 * @covers ::onPageSaveComplete
	 */
	public function testOnPageSaveComplete_insert() {
		$flags = EDIT_NEW;

		$this->mockStorage->expects( $this->once() )
			->method( 'insertIndex' )
			->with( new EntityTarget( $this->revisionType, $this->targetRevId ), $this->mockEntityPage );

		$contentText = TestStorageHelper::getJudgmentText( 'diff' );

		$this->mockEntityPage->method( 'getContent' )
			->willReturn( new EntityContent( $contentText ) );
		LinkSummaryHooks::onPageSaveComplete(
			$this->mockEntityPage,
			$this->user,
			'',
			$flags,
			$this->createMock( RevisionRecord::class ),
			$this->createMock( EditResult::class )
		);
	}

	/**
	 * @covers ::onPageSaveComplete
	 */
	public function testOnPageSaveComplete_noTarget() {
		$flags = EDIT_NEW;

		$this->mockStorage->expects( $this->never() )
			->method( 'insertIndex' );

		$nonEntityPage = $this->getExistingTestPage( __METHOD__ );

		LinkSummaryHooks::onPageSaveComplete(
			$nonEntityPage,
			$this->user,
			'',
			$flags,
			$this->createMock( RevisionRecord::class ),
			$this->createMock( EditResult::class )
		);
	}

	// TODO:
	// public function testOnPageSaveComplete_badTitle() {
	// public function testOnPageSaveComplete_badContent() {
	// public function testOnPageSaveComplete_cannotUpdateSummary() {

}
