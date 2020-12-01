<?php

namespace Wikibase\Repo\Tests\Api;

use ApiUsageException;
use FauxRequest;
use LogicException;
use MediaWiki\Debug\DeprecatablePropertyArray;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Status;
use User;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\ItemIdParser;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyNoValueSnak;
use Wikibase\Lib\Store\EntityStore;
use Wikibase\MediaInfo\DataModel\MediaInfo;
use Wikibase\MediaInfo\DataModel\MediaInfoId;
use Wikibase\Repo\Api\EntityLoadingHelper;
use Wikibase\Repo\Api\EntitySavingHelper;
use Wikibase\Repo\EditEntity\EditEntity;
use Wikibase\Repo\EditEntity\MediawikiEditEntityFactory;
use Wikibase\Repo\SummaryFormatter;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Repo\Api\EntitySavingHelper
 *
 * @group Database
 * @group Wikibase
 * @group WikibaseAPI
 *
 * @license GPL-2.0-or-later
 * @author Addshore
 * @author Daniel Kinzler
 */
class EntitySavingHelperTest extends EntityLoadingHelperTest {

	/**
	 * Skips a test of the given entity type is not enabled.
	 *
	 * @param string|null $requiredEntityType
	 */
	private function skipIfEntityTypeNotKnown( $requiredEntityType ) {
		if ( $requiredEntityType === null ) {
			return;
		}

		$enabledTypes = WikibaseRepo::getDefaultInstance()->getLocalEntityTypes();
		if ( !in_array( $requiredEntityType, $enabledTypes ) ) {
			$this->markTestSkipped( 'Entity type not enabled: ' . $requiredEntityType );
		}
	}

	/**
	 * @return SummaryFormatter
	 */
	private function getMockSummaryFormatter() {
		return $this->createMock( SummaryFormatter::class );
	}

	private function getMockEditEntity( ?int $calls, ?Status $status ): EditEntity {
		$mock = $this->createMock( EditEntity::class );
		$mock->expects( $calls === null ? $this->any() : $this->exactly( $calls ) )
			->method( 'attemptSave' )
			->willReturn( $status ?? Status::newGood() );
		return $mock;
	}

	private function getMockEditEntityFactory( ?int $calls, ?Status $status ): MediawikiEditEntityFactory {
		$mock = $this->createMock( MediawikiEditEntityFactory::class );
		$mock->expects( $calls === null ? $this->any() : $this->exactly( $calls ) )
			->method( 'newEditEntity' )
			->willReturn( $this->getMockEditEntity( $calls, $status ) );
		return $mock;
	}

	/**
	 * @return EntityStore
	 */
	private function getMockEntityStore() {
		$mock = $this->createMock( EntityStore::class );
		$mock->expects( $this->any() )
			->method( 'canCreateWithCustomId' )
			->will( $this->returnCallback( function ( EntityId $id ) {
				return $id->getEntityType() === 'mediainfo';
			} ) );
		$mock->expects( $this->any() )
			->method( 'assignFreshId' )
			->will( $this->returnCallback( function ( EntityDocument $entity ) {
				$entity->setId( new ItemId( 'Q333' ) );
			} ) );

		return $mock;
	}

	protected function getMockApiBase( array $params ) {
		$api = parent::getMockApiBase( $params );

		$api->expects( $this->any() )
			->method( 'getContext' )
			->will( $this->returnValue( $this->newContext( $params ) ) );

		return $api;
	}

	private function newContext( array $params ) {
		$context = new RequestContext();
		$context->setUser( $this->createMock( User::class ) );
		$context->setRequest( new FauxRequest( $params ) );

		return $context;
	}

	public function testLoadEntity_create_from_type() {
		$helper = $this->newEntitySavingHelper( [
			'allowCreation' => true,
			'params' => [ 'new' => 'item' ],
		] );

		$return = $helper->loadEntity();
		$this->assertInstanceOf( Item::class, $return );
		$this->assertNotNull( $return->getId(), 'New item should have a fresh ID' );

		$this->assertSame( 0, $helper->getBaseRevisionId() );
		$this->assertSame( EDIT_NEW, $helper->getSaveFlags() );

		$status = $helper->attemptSaveEntity( $return, 'Testing' );
		$this->assertTrue( $status->isGood(), 'isGood()' );
	}

	public function testLoadEntity_create_from_id() {
		$this->skipIfEntityTypeNotKnown( 'mediainfo' );

		$helper = $this->newEntitySavingHelper( [
			'allowCreation' => true,
			'params' => [ 'entity' => 'M7' ],
			'entityId' => new MediaInfoId( 'M7' ),
			'EntityIdParser' => WikibaseRepo::getEntityIdParser()
		] );

		$return = $helper->loadEntity();
		$this->assertInstanceOf( MediaInfo::class, $return );
		$this->assertSame( 'M7', $return->getId()->getSerialization() );

		$this->assertSame( 0, $helper->getBaseRevisionId() );
		$this->assertSame( EDIT_NEW, $helper->getSaveFlags() );

		$status = $helper->attemptSaveEntity( $return, 'Testing' );
		$this->assertTrue( $status->isGood(), 'isGood()' );
	}

	public function testLoadEntity_without_creation_support() {
		$helper = $this->newEntitySavingHelper( [
			'params' => [ 'new' => 'item' ],
			'dieErrorCode' => 'no-entity-id'
		] );

		$this->expectException( ApiUsageException::class );
		$helper->loadEntity();
	}

	public function provideLoadEntity_fail() {
		return [
			'no params' => [
				[],
				'no-entity-id'
			],
			'baserevid but no entity' => [
				[ 'baserevid' => 17 ],
				'param-illegal'
			],
			'new bad' => [
				[ 'new' => 'bad' ],
				'no-such-entity-type'
			],
			'unknown entity' => [
				[ 'entity' => 'Q123' ],
				'no-such-entity'
			],
		];
	}

	/**
	 * @dataProvider provideLoadEntity_fail
	 */
	public function testLoadEntity_fail( array $params, $dieErrorCode ) {
		$helper = $this->newEntityLoadingHelper( [
			'allowCreation' => true,
			'params' => $params,
			'dieErrorCode' => $dieErrorCode,
			'entityId' => isset( $params['entity'] ) ? new ItemId( $params['entity'] ) : null ,
		] );

		$this->expectException( ApiUsageException::class );
		$helper->loadEntity();
	}

	public function testLoadEntity_baserevid() {
		$itemId = new ItemId( 'Q1' );

		$revision = $this->getMockRevision();
		$revision->expects( $this->once() )
			->method( 'getRevisionId' )
			->will( $this->returnValue( 17 ) );

		$entity = $revision->getEntity();

		$helper = $this->newEntitySavingHelper( [
			'params' => [ 'baserevid' => 17 ],
			'entityId' => $itemId,
			'revision' => $revision,
		] );

		$return = $helper->loadEntity( $itemId );
		$this->assertSame( $entity, $return );

		$this->assertSame( 17, $helper->getBaseRevisionId() );
		$this->assertSame( EDIT_UPDATE, $helper->getSaveFlags() );
	}

	public function testAttemptSave() {
		$helper = $this->newEntitySavingHelper( [
			'newEditEntityCalls' => 1
		] );

		$entity = new Item();
		$entity->setId( new ItemId( 'Q444' ) );
		$entity->getFingerprint()->setLabel( 'en', 'Foo' );
		$entity->getSiteLinkList()->addNewSiteLink( 'enwiki', 'APage' );
		$entity->getStatements()->addNewStatement( new PropertyNoValueSnak( new PropertyId( 'P8' ) ) );

		$summary = 'A String Summary';
		$flags = 0;

		$status = $helper->attemptSaveEntity( $entity, $summary, $flags );
		$this->assertTrue( $status->isGood(), 'isGood()' );
	}

	public function testSaveThrowsException_onNonWriteMode() {
		$helper = $this->newEntitySavingHelper( [
			'writeMode' => false,
			'newEditEntityCalls' => 0
		] );

		$this->expectException( LogicException::class );
		$helper->attemptSaveEntity( new Item(), '' );
	}

	/**
	 * @dataProvider errorStatusProvider
	 */
	public function testGivenErroneousSaveStatus_attemptSaveDiesWithError( array $statusValue, string $expectedErrorCode ) {
		$status = Status::newFatal( 'sad' );

		// intentionally not using a vanilla array as the Status value, because this was the source of a regression -> T260869
		$status->value = new DeprecatablePropertyArray( $statusValue, [], __METHOD__ . ' status' );
		$helper = $this->newEntitySavingHelper( [
			'dieErrorCode' => $expectedErrorCode,
			'attemptSaveStatus' => $status,
		] );

		$this->expectException( ApiUsageException::class );
		$helper->attemptSaveEntity( new Item(), '' );
	}

	public function errorStatusProvider() {
		yield 'with errorFlags' => [
			[ 'errorFlags' => EditEntity::SAVE_ERROR ],
			'failed-save',
		];
		yield 'with concrete errorCode' => [
			[ 'errorCode' => 'sadness' ],
			'sadness',
		];
	}

	/**
	 * @param array $config Associative configuration array. Known keys:
	 *   - params: request parameters, as an associative array
	 *   - revision: revision ID for EntityRevision
	 *   - isWriteMode: return value for isWriteMode
	 *   - EntityIdParser: the parser to use for entity ids
	 *   - entityId: The ID expected by getEntityRevision
	 *   - revision: EntityRevision to return from getEntityRevisions
	 *   - exception: Exception to throw from getEntityRevisions
	 *   - dieErrorCode: The error code expected by dieError
	 *   - dieExceptionCode: The error code expected by dieException
	 *   - newEditEntityCalls: expected number of calls to newEditEntity
	 *   - attemptSaveStatus: Status object returned by the call to EditEntity::attemptSave
	 *
	 * @return EntitySavingHelper
	 */
	protected function newEntitySavingHelper( array $config ) {
		$apiModule = $this->getMockApiBase( $config['params'] ?? [] );
		$apiModule->expects( $this->any() )
			->method( 'isWriteMode' )
			->will( $this->returnValue( $config['writeMode'] ?? true ) );

		$helper = new EntitySavingHelper(
			$apiModule,
			$config['EntityIdParser'] ?? new ItemIdParser(),
			$this->getMockEntityRevisionLookup(
				$config['entityId'] ?? null,
				$config['revision'] ?? null,
				$config['exception'] ?? null
			),
			$this->getMockErrorReporter(
				$config['dieExceptionCode'] ?? null,
				$config['dieErrorCode'] ?? null
			),
			$this->getMockSummaryFormatter(),
			$this->getMockEditEntityFactory(
				$config['newEditEntityCalls'] ?? null,
				$config['attemptSaveStatus'] ?? null
			),
			MediaWikiServices::getInstance()->getPermissionManager()
		);

		if ( $config['allowCreation'] ?? false ) {
			$helper->setEntityFactory( WikibaseRepo::getDefaultInstance()->getEntityFactory() );
			$helper->setEntityStore( $this->getMockEntityStore() );
		}

		return $helper;
	}

	/**
	 * @param array $config Associative configuration array. Known keys:
	 *   - params: request parameters, as an associative array
	 *   - entityId: The ID expected by getEntityRevision
	 *   - revision: EntityRevision to return from getEntityRevisions
	 *   - exception: Exception to throw from getEntityRevisions
	 *   - dieErrorCode: The error code expected by dieError
	 *   - dieExceptionCode: The error code expected by dieException
	 *
	 * @return EntityLoadingHelper
	 */
	protected function newEntityLoadingHelper( array $config ) {
		return $this->newEntitySavingHelper( $config );
	}

}
