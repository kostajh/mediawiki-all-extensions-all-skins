<?php

declare( strict_types = 1 );
namespace Wikibase\Repo\Tests\ChangeModification;

use IJobSpecification;
use JobQueueGroup;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use MWTimestamp;
use Psr\Log\LoggerInterface;
use Title;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\Lib\Changes\RepoRevisionIdentifier;
use Wikibase\Lib\Changes\RepoRevisionIdentifierFactory;
use Wikibase\Repo\ChangeModification\DispatchChangeDeletionNotificationJob;
use Wikibase\Repo\WikibaseRepo;
use WikiPage;

/**
 * @covers \Wikibase\Repo\ChangeModification\DispatchChangeDeletionNotificationJob
 *
 * @group Wikibase
 * @group Database
 *
 * @license GPL-2.0-or-later
 */
class DispatchChangeDeletionNotificationJobTest extends MediaWikiIntegrationTestCase {

	private $expectedLocalClientWikis;

	public function setUp(): void {
		parent::setUp();
		$this->tablesUsed[] = 'archive';
		$this->tablesUsed[] = 'page';
		$this->tablesUsed[] = 'text';

		$this->expectedLocalClientWikis = [ 'dewiki', 'enwiki', 'poolwiki' ];

		global $wgWBRepoSettings;
		$newRepoSettings = $wgWBRepoSettings;
		$newRepoSettings['localClientDatabases'] = $this->expectedLocalClientWikis;
		$newRepoSettings['deleteNotificationClientRCMaxAge'] = 1 * 24 * 3600; // (1 days)
		$this->setMwGlobals( 'wgWBRepoSettings', $newRepoSettings );
	}

	public function testShouldDispatchJobsForClientWikis() {
		$timestamp = wfTimestampNow();
		MWTimestamp::setFakeTime( $timestamp );
		list( $pageId, $revisionRecordId, $pageTitle ) = $this->initArchive();
		MWTimestamp::setFakeTime( false );

		$params = [ 'archivedRevisionCount' => 1, 'pageId' => $pageId ];
		$expectedRevIdentifiers = [
			new RepoRevisionIdentifier( "Q303", $timestamp, $revisionRecordId )
		];

		$logger = $this->createMock( LoggerInterface::class );
		$factory = $this->newJobQueueGroupFactory( $expectedRevIdentifiers );
		$job = $this->getJobAndInitialize( $pageTitle, $params, $logger, $factory );

		$this->assertTrue( $job->run() );
	}

	public function testShouldNotDispatchJobsWhenToOld() {
		MWTimestamp::setFakeTime( '20110401090000' );
		list( $pageId, $revisionRecordId, $pageTitle ) = $this->initArchive();
		MWTimestamp::setFakeTime( false );

		$params = [ 'archivedRevisionCount' => 1, 'pageId' => $pageId ];

		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->once() )
			->method( 'info' )
			->with( 'All archive records are too old. Aborting.' );

		$factory = function( $wikiId ) {
			$jobQueueGroup = $this->createMock( JobQueueGroup::class );
			$jobQueueGroup->expects( $this->never() )
				->method( 'push' );

			return $jobQueueGroup;
		};
		$job = $this->getJobAndInitialize( $pageTitle, $params, $logger, $factory );
		$this->assertTrue( $job->run() );
	}

	/**
	 * @param Title $title
	 * @param array $params
	 * @param LoggerInterface $logger
	 * @param Callable $factory
	 * @return DispatchChangeDeletionNotificationJob
	 */
	private function getJobAndInitialize( Title $title, array $params, $logger, $factory ): DispatchChangeDeletionNotificationJob {
		$mwServices = MediaWikiServices::getInstance();
		$repo = WikibaseRepo::getDefaultInstance();

		$job = new DispatchChangeDeletionNotificationJob( $title, $params );
		$job->initServices(
			$mwServices->getDBLoadBalancerFactory(),
			$repo->getEntityContentFactory(),
			$logger,
			$factory
		);

		return $job;
	}

	private function newJobQueueGroupFactory(
		array $expectedIds
	): callable {
		return function ( string $wikiId ) use ( $expectedIds ) {
			$this->assertSame( $wikiId, array_shift( $this->expectedLocalClientWikis ) );
			$jobQueueGroup = $this->createMock( JobQueueGroup::class );
			$jobQueueGroup->expects( $this->once() )
				->method( 'push' )
				->willReturnCallback( function ( IJobSpecification $job ) use ( $expectedIds ) {
					$this->assertInstanceOf( IJobSpecification::class, $job );
					$this->assertSame( 'ChangeDeletionNotification', $job->getType() );

					$actualIds = $this->unpackRevisionIdentifiers( $job->getParams()['revisionIdentifiersJson'] );

					$this->assertSameSize( $expectedIds, $actualIds );

					for ( $i = 0; $i < count( $expectedIds ); $i++ ) {
						$this->assertSame( $expectedIds[$i]->getEntityIdSerialization(), $actualIds[$i]->getEntityIdSerialization() );
						$this->assertSame( $expectedIds[$i]->getRevisionId(), $actualIds[$i]->getRevisionId() );
						$this->assertSame( $expectedIds[$i]->getRevisionTimestamp(), $actualIds[$i]->getRevisionTimestamp() );
					}
				} );

			return $jobQueueGroup;
		};
	}

	/**
	 * @param string $revisionIdentifiersJson
	 * @return RepoRevisionIdentifier[]
	 */
	private function unpackRevisionIdentifiers( string $revisionIdentifiersJson ): array {
		$repoRevisionFactory = new RepoRevisionIdentifierFactory();
		$revisionIdentifiers = [];

		$revisionIdentifiersArray = json_decode( $revisionIdentifiersJson, true );
		foreach ( $revisionIdentifiersArray as $revisionIdentifierArray ) {
			$revisionIdentifiers[] = $repoRevisionFactory->newFromArray( $revisionIdentifierArray );
		}

		return $revisionIdentifiers;
	}

	private function countArchive( array $conditions = null ): int {
		return $this->db->selectRowCount(
			'archive',
			'ar_rev_id',
			$conditions,
			__METHOD__
		);
	}

	/**
	 * @return WikiPage
	 */
	public function createItemWithPage() {
		$repo = WikibaseRepo::getDefaultInstance();

		$store = $repo->getEntityStore();

		// create a fake item
		$item = new Item( new ItemId( 'Q303' ) );
		$revision = $store->saveEntity( $item, 'Q303', $this->getTestUser()->getUser() );

		// get title
		$contentFactory = $repo->getEntityContentFactory();
		$title = $contentFactory->getTitleForId( $revision->getEntity()->getId() );

		// insert a wikipage for the item
		$entityNamespaceLookup = $repo->getEntityNamespaceLookup();
		$namespace = $entityNamespaceLookup->getEntityNamespace( $item->getId()->getEntityType() );
		$this->insertPage( $title->getText(), '{ "entity": "' . $item->getId()->getSerialization() . '" }', $namespace );

		// return page
		return $store->getWikiPageForEntity( $item->getId() );
	}

	protected function initArchive() : array {
		$page = $this->createItemWithPage();
		$revisionRecordId = $page->getRevisionRecord()->getId();
		$pageId = $page->getId();
		$error = '';

		$initialCount = $this->countArchive();

		// delete the page
		$status = $page->doDeleteArticleReal(
			'no reason', $this->getTestUser()->getUser(), false, null, $error,
			null, [], 'delete', false
		);
		$this->assertTrue( $status->isOK() && $status->isGood() );
		$this->assertEquals( $initialCount + 1, $this->countArchive() );

		return [ $pageId, $revisionRecordId, $page->getTitle() ];
	}
}
