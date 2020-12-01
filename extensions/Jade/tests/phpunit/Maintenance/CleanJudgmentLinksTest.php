<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace Jade\Tests\Maintenance;

use Jade\Maintenance\CleanJudgmentLinks;
use Jade\Tests\TestStorageHelper;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Tests\Maintenance\MaintenanceBaseTestCase;
use WikiPage;

/**
 * @group Jade
 * @group Database
 * @group medium
 * @covers \Jade\Maintenance\CleanJudgmentLinks
 * @coversDefaultClass \Jade\Maintenance\CleanJudgmentLinks
 */
class CleanJudgmentLinksTest extends MaintenanceBaseTestCase {

	// Include assertions to test judgment links.
	// use TestJudgmentLinkAssertions;

	public function getMaintenanceClass() {
		return CleanJudgmentLinks::class;
	}

	protected function setUp() : void {
		$this->markTestSkipped( 'not used' );
		parent::setUp();
		$this->tablesUsed[] = 'jade_diff_label';
		$this->tablesUsed[] = 'jade_revision_label';
	}

	private function getJudgmentContent( $entityType ) {
		return file_get_contents( __DIR__ . '/../../data/valid_' . $entityType . '_judgment.json' );
	}

	private function createRevisionRecord() {
		list( $page, $revisionRecord ) = TestStorageHelper::createNewEntity(
			$this->getTestUser()->getUser()
		);
		return $revisionRecord;
	}

	private function createJudgment( RevisionRecord $revisionRecord, $entityType ) {
		global $wgJadeEntityTypeNames;

		$status = TestStorageHelper::saveJudgment(
			$wgJadeEntityTypeNames[$entityType] . "/{$revisionRecord->getId()}",
			$this->getJudgmentContent( $entityType ),
			$this->getTestUser()->getUser()
		);
		$this->assertTrue( $status->isOK() );

		return WikiPage::newFromID( $status->value['revision-record']->getPageId() );
	}

	private function executeMaintenanceScript( $batchSize, $dryRun ) {
		$options = [ 'batch-size' => $batchSize ];
		if ( $dryRun ) {
			$options['dry-run'] = true;
		}

		$this->maintenance->loadParamsAndArgs(
			null,
			$options
		);
		$this->maintenance->execute();
	}

	/**
	 * Make sure that the starting state has no judgment link rows.
	 */
	public function testEmptyNoLinks() {
		$dbr = wfGetDB( DB_REPLICA );
		$result = $dbr->select(
			[ 'jade_diff_judgment' ],
			[ 'jaded_id' ],
			null,
			__METHOD__
		);
		$this->assertSame( 0, $result->numRows() );

		$result = $dbr->select(
			[ 'jade_revision_judgment' ],
			[ 'jader_id' ],
			null,
			__METHOD__
		);
		$this->assertSame( 0, $result->numRows() );
	}

	public function entityTypeDryRunProvider() {
		yield [ 'diff', false ];
		yield [ 'revision', false ];
		yield [ 'diff', 'dryRun' ];
		yield [ 'revision', 'dryRun' ];
	}

	/**
	 * @dataProvider entityTypeDryRunProvider
	 *
	 * @covers ::findAndDeleteOrphanedLinks
	 * @covers ::findOrphanedLinks
	 * @covers ::deleteOrphanedLinks
	 */
	public function testDeleteOrphanedLinks( $entityType, $dryRun ) {
		$noOfTestJudgements = 3;
		$revisionRecords = [];
		$pageIds = [];

		// Make sure page deletions don't auto-delete links
		$this->setTemporaryHook( 'ArticleDeleteComplete', false );

		$deleter = $this->getTestSysop()->getUser();
		for ( $i = 0; $i < $noOfTestJudgements; $i++ ) {
			$revisionRecord = $this->createRevisionRecord();
			$page = $this->createJudgment( $revisionRecord, $entityType );
			$pageId = $page->getId();

			// Orphan it by deleting the judgment page
			$page->doDeleteArticleReal( 'reasonable', $deleter );

			// Check that the link still exists.
			$this->assertJudgmentLink( $entityType, $revisionecord->getId(), $pageId );

			$revisionRecords[] = $revisionRecord;
			$pageIds[] = $pageId;
		}

		// Run the job (with a batch-size lower than $noOfTestJudgements)
		$this->executeMaintenanceScript( $noOfTestJudgements - 1, $dryRun );

		// Check that the links were deleted.
		for ( $i = 1; $i < $noOfTestJudgements; $i++ ) {
			if ( $dryRun ) {
				// Links should still be present if this is a dry run
				$this->assertJudgmentLink(
					$entityType,
					$revisionRecords[$i]->getId(),
					$pageIds[$i]
				);
			} else {
				$this->assertNoJudgmentLink(
					$entityType,
					$revisionRecords[$i]->getId(),
					$pageIds[$i]
				);
			}
		}
	}

	/**
	 * @dataProvider entityTypeDryRunProvider
	 *
	 * @covers ::findAndConnectUnlinkedJudgments
	 * @covers ::findUnlinkedJudgments
	 * @covers ::connectUnlinkedJudgments
	 */
	public function testConnectUnlinkedJudgments( $entityType, $dryRun ) {
		$noOfTestJudgements = 3;
		$revisionRecords = [];
		$pageIds = [];

		// Make sure created judgements are not linked
		$this->setTemporaryHook( 'PageSaveComplete', false );

		for ( $i = 0; $i < $noOfTestJudgements; $i++ ) {
			// Create judgment without link.
			$revisionRecord = $this->createRevisionRecord();
			$page = $this->createJudgment( $revisionRecord, $entityType );

			// Check that no link was created.
			$this->assertNoJudgmentLink(
				$entityType,
				$revisionRecord->getId(),
				$page->getId()
			);

			$revisionRecords[] = $revisionRecord;
			$pageIds[] = $page->getId();
		}

		// Run the job (with a batch-size lower than $noOfTestJudgements)
		$this->executeMaintenanceScript( $noOfTestJudgements - 1, $dryRun );

		// Check that the links were inserted.
		for ( $i = 1; $i < $noOfTestJudgements; $i++ ) {
			if ( !$dryRun ) {
				$this->assertJudgmentLink(
					$entityType,
					$revisionRecords[$i]->getId(),
					$pageIds[$i]
				);
			} else {
				// Links should still be absent if this is a dry run
				$this->assertNoJudgmentLink(
					$entityType,
					$revisionRecords[$i]->getId(),
					$pageIds[$i]
				);
			}
		}
	}

}
