<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Integration\CRUD;

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\WikispeechSpeechDataCollector\CRUD\AbstractCRUD;
use MediaWiki\WikispeechSpeechDataCollector\CRUD\CLUD;
use MediaWiki\WikispeechSpeechDataCollector\Tests\Domain\PersistentCompleteOneBuilder;
use MediaWiki\WikispeechSpeechDataCollector\Tests\Domain\PersistentCompleteTwoBuilder;
use MediaWiki\WikispeechSpeechDataCollector\Tests\Domain\PersistentEqualsConstraintFactory;
use MediaWiki\WikispeechSpeechDataCollector\Tests\Domain\PersistentSetNullableNull;
use MediaWikiIntegrationTestCase;
use Psr\Log\LoggerInterface;
use Wikimedia\Rdbms\ILoadBalancer;
use Wikimedia\TestingAccessWrapper;

/**
 * Class AbstractCRUDTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Test\Integration\CRUD
 *
 * @todo Add helpers for testing listing and getting object by indices.
 *
 * @since 0.1.0
 * @group Database
 * @covers \MediaWiki\WikispeechSpeechDataCollector\CRUD\AbstractCRUD
 * @covers \MediaWiki\WikispeechSpeechDataCollector\CRUD\CLUD
 */
abstract class AbstractCRUDTest extends MediaWikiIntegrationTestCase {

	/** @var AbstractCRUD */
	private $crud;

	/** @var TestingAccessWrapper|AbstractCRUD */
	private $crudWrapper;

	/** @var LoggerInterface */
	private $logger;

	/** @var ILoadBalancer */
	private $dbLoadBalancer;

	/** @var CLUD */
	private $clud;

	protected function setUp(): void {
		parent::setUp();
		$this->logger = LoggerFactory::getInstance( __CLASS__ );
		$this->dbLoadBalancer = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$this->crud = $this->crudFactory( $this->dbLoadBalancer );
		$this->crudWrapper = TestingAccessWrapper::newFromObject( $this->crud );
		$this->tablesUsed[] = $this->crudWrapper->getTable();
		$this->clud = new CLUD( $this->dbLoadBalancer );
	}

	/**
	 * Create-Read-Update-Delete tests using CRUD.
	 * Ensures that serialization and deserialization match.
	 */
	public function testCRUD() {
		// create initial

		$instance = $this->crud->instanceFactory();
		$instance->accept( new PersistentCompleteOneBuilder() );
		$this->assertNull( $instance->getIdentity() );

		// initial instance

		$this->crud->create( $instance );
		$this->assertNotNull( $instance->getIdentity() );

		$readInstance = $this->crud->read( $instance->getIdentity() );
		$this->logger->debug( 'Instance @ CRUD read#1 is ' . $readInstance );
		$this->assertThat( $instance,
			$readInstance->accept( new PersistentEqualsConstraintFactory() ) );

		// update instance fields

		$readInstance->accept( new PersistentCompleteTwoBuilder() );
		$this->crud->update( $readInstance );

		$updatedInstance = $this->crud->read( $instance->getIdentity() );
		$this->logger->debug( 'Instance @ CRUD read#2 is ' . $updatedInstance );
		$this->assertThat( $readInstance,
			$updatedInstance->accept( new PersistentEqualsConstraintFactory() ) );

		// delete instance

		$this->crud->delete( $instance->getIdentity() );
		$deletedInstance = $this->crud->read( $instance->getIdentity() );
		$this->logger->debug( 'Instance @ CRUD read#4 is ' . $deletedInstance );
		$this->assertNull( $deletedInstance );
	}

	/**
	 * Ensures that serialization and deserialization match
	 * for all nullable fields when set to null.
	 */
	public function testCRUD_setNullableNull_deserializationMatches() {
		$instance = $this->crud->instanceFactory();
		$instance->accept( new PersistentCompleteTwoBuilder() );
		$instance->accept( new PersistentSetNullableNull() );
		$this->crud->create( $instance );
		$readInstance = $this->crud->read( $instance->getIdentity() );
		$this->assertThat( $instance,
			$readInstance->accept( new PersistentEqualsConstraintFactory() ) );
	}

	/**
	 * Create-Load-Update-Delete tests using CLUD.
	 * Ensures that serialization and deserialization match.
	 *
	 * As the CLUD is just a generic facade against all CRUDs,
	 * there is no need to execute further tests on CLUD
	 * (e.g. testCLUD_setNullableNull_deserializationMatches)
	 * to ensure specific features in regard to persistent instances.
	 */
	public function testCLUD() {
		// create instance

		$instance = $this->crud->instanceFactory();
		$instance->accept( new PersistentCompleteOneBuilder() );
		$this->assertNull( $instance->getIdentity() );

		// initial instance

		$this->clud->create( $instance );
		$this->assertNotNull( $instance->getIdentity() );

		$readInstance = $this->crud->instanceFactory();
		$readInstance->setIdentity( $instance->getIdentity() );
		$this->assertTrue( $this->clud->load( $readInstance ) );
		$this->logger->debug( 'Instance @ CLUD read#1 is ' . $readInstance );
		$this->assertSame( $instance->getIdentity(), $readInstance->getIdentity() );
		$this->assertThat( $instance,
			$readInstance->accept( new PersistentEqualsConstraintFactory() ) );

		// update instance fields

		$readInstance->accept( new PersistentCompleteTwoBuilder() );
		$this->clud->update( $readInstance );

		$updatedInstance = $this->crud->instanceFactory();
		$updatedInstance->setIdentity( $instance->getIdentity() );
		$this->assertTrue( $this->clud->load( $updatedInstance ) );
		$this->logger->debug( 'Instance @ CLUD read#2 is ' . $updatedInstance );
		$this->assertSame( $instance->getIdentity(), $updatedInstance->getIdentity() );
		$this->assertThat( $readInstance,
			$updatedInstance->accept( new PersistentEqualsConstraintFactory() ) );

		// delete instance

		$deletedInstance = $this->crud->instanceFactory();
		$deletedInstance->setIdentity( $instance->getIdentity() );
		$this->clud->delete( $deletedInstance );
		$this->assertFalse( $this->clud->load( $deletedInstance ) );
	}

	/**
	 * @param ILoadBalancer $dbLoadBalancer
	 * @return AbstractCRUD
	 */
	abstract protected function crudFactory(
		ILoadBalancer $dbLoadBalancer
	): AbstractCRUD;

}
