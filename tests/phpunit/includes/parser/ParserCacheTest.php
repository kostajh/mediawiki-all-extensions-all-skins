<?php

namespace MediaWiki\Tests\Parser;

use BagOStuff;
use EmptyBagOStuff;
use HashBagOStuff;
use InvalidArgumentException;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Json\JsonCodec;
use MediaWiki\Tests\Json\JsonUnserializableSuperClass;
use MediaWikiIntegrationTestCase;
use MWTimestamp;
use NullStatsdDataFactory;
use ParserCache;
use ParserOptions;
use ParserOutput;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use TestLogger;
use Title;
use User;
use Wikimedia\TestingAccessWrapper;
use WikiPage;

/**
 * @covers ParserCache
 * @group Database
 * @package MediaWiki\Tests\Parser
 */
class ParserCacheTest extends MediaWikiIntegrationTestCase {

	/** @var int */
	private $time;

	/** @var string */
	private $cacheTime;

	/** @var WikiPage */
	private $page;

	protected function setUp() : void {
		parent::setUp();

		$this->time = time();
		MWTimestamp::setFakeTime( $this->time );
		$this->page = $this->getExistingTestPage( __CLASS__ );
		$this->page->clear();
		$this->page->loadPageData();

		$this->cacheTime = MWTimestamp::convert( TS_MW, time() + 1 );

		// Clean up these tables after each test
		$this->tablesUsed = [
			'page',
			'revision',
			'comment',
			'text',
			'content'
		];
	}

	protected function tearDown() : void {
		MWTimestamp::setFakeTime( null );
		parent::tearDown();
	}

	/**
	 * @param HookContainer|null $hookContainer
	 * @param BagOStuff|null $storage
	 * @param LoggerInterface|null $logger
	 * @return ParserCache
	 */
	private function createParserCache(
		HookContainer $hookContainer = null,
		BagOStuff $storage = null,
		LoggerInterface $logger = null
	): ParserCache {
		return new ParserCache(
			'test',
			$storage ?: new HashBagOStuff(),
			'19900220000000',
			$hookContainer ?: $this->createHookContainer( [] ),
			new JsonCodec(),
			new NullStatsdDataFactory(),
			$logger ?: new NullLogger()
		);
	}

	/**
	 * @return array
	 */
	private function getDummyUsedOptions(): array {
		return array_slice(
			ParserOptions::allCacheVaryingOptions(),
			0,
			2
		);
	}

	/**
	 * @return ParserOutput
	 */
	private function createDummyParserOutput(): ParserOutput {
		$parserOutput = new ParserOutput();
		$parserOutput->setText( 'TEST' );
		foreach ( $this->getDummyUsedOptions() as $option ) {
			$parserOutput->recordOption( $option );
		}
		$parserOutput->updateCacheExpiry( 4242 );
		return $parserOutput;
	}

	/**
	 * @covers ParserCache::getMetadata
	 * @covers ParserCache::getKey
	 */
	public function testGetMetadataMissing() {
		$this->hideDeprecated( 'ParserCache::getKey' );
		$cache = $this->createParserCache();

		$metadataFromCache = $cache->getMetadata( $this->page, ParserCache::USE_CURRENT_ONLY );
		$this->assertFalse( $cache->getKey( $this->page,
			ParserOptions::newFromAnon(), ParserCache::USE_CURRENT_ONLY ) );
		$this->assertNotFalse( $cache->getKey( $this->page, ParserOptions::newFromAnon() ) );
		$this->assertNull( $metadataFromCache );
	}

	/**
	 * @covers ParserCache::getMetadata
	 * @covers ParserCache::getKey
	 */
	public function testGetMetadataAllGood() {
		$this->hideDeprecated( 'ParserCache::getKey' );
		$cache = $this->createParserCache();
		$parserOutput = $this->createDummyParserOutput();
		$cache->save( $parserOutput, $this->page, ParserOptions::newFromAnon(), $this->cacheTime );

		$metadataFromCache = $cache->getMetadata( $this->page, ParserCache::USE_CURRENT_ONLY );
		$this->assertNotNull( $metadataFromCache );
		$this->assertNotFalse( $cache->getKey( $this->page,
			ParserOptions::newFromAnon(), ParserCache::USE_CURRENT_ONLY ) );
		$this->assertSame( $this->getDummyUsedOptions(), $metadataFromCache->getUsedOptions() );
		$this->assertSame( 4242, $metadataFromCache->getCacheExpiry() );
		$this->assertSame( $this->page->getRevisionRecord()->getId(),
			$metadataFromCache->getCacheRevisionId() );
		$this->assertSame( $this->cacheTime, $metadataFromCache->getCacheTime() );
	}

	/**
	 * @covers ParserCache::getMetadata
	 * @covers ParserCache::getKey
	 */
	public function testGetMetadataExpired() {
		$this->hideDeprecated( 'ParserCache::getKey' );
		$cache = $this->createParserCache();
		$parserOutput = $this->createDummyParserOutput();
		$cache->save( $parserOutput, $this->page, ParserOptions::newFromAnon(), $this->cacheTime );
		$this->page->getTitle()->invalidateCache( MWTimestamp::convert( TS_MW, time() + 10000 ) );
		$this->page->clear();

		$this->assertNull( $cache->getMetadata( $this->page, ParserCache::USE_CURRENT_ONLY ) );
		$this->assertFalse( $cache->getKey( $this->page,
			ParserOptions::newFromAnon(), ParserCache::USE_CURRENT_ONLY ) );
		$metadataFromCache = $cache->getMetadata( $this->page, ParserCache::USE_EXPIRED );
		$this->assertNotFalse( $cache->getKey( $this->page,
			ParserOptions::newFromAnon(), ParserCache::USE_EXPIRED ) );
		$this->assertNotNull( $metadataFromCache );
		$this->assertSame( $this->getDummyUsedOptions(), $metadataFromCache->getUsedOptions() );
		$this->assertSame( 4242, $metadataFromCache->getCacheExpiry() );
		$this->assertSame( $this->page->getRevisionRecord()->getId(),
			$metadataFromCache->getCacheRevisionId() );
		$this->assertSame( $this->cacheTime, $metadataFromCache->getCacheTime() );
	}

	/**
	 * @covers ParserCache::getMetadata
	 * @covers ParserCache::getKey
	 */
	public function testGetMetadataOutdated() {
		$this->hideDeprecated( 'ParserCache::getKey' );
		$cache = $this->createParserCache();
		$parserOutput = $this->createDummyParserOutput();
		$cache->save( $parserOutput, $this->page, ParserOptions::newFromAnon(), $this->cacheTime );
		$this->editPage( $this->page->getTitle()->getDBkey(), 'Edit!' );
		$this->page->clear();

		$this->assertNull( $cache->getMetadata( $this->page, ParserCache::USE_CURRENT_ONLY ) );
		$this->assertFalse( $cache->getKey( $this->page,
			ParserOptions::newFromAnon(), ParserCache::USE_CURRENT_ONLY ) );
		$this->assertNull( $cache->getMetadata( $this->page, ParserCache::USE_EXPIRED ) );
		$this->assertFalse( $cache->getKey( $this->page,
			ParserOptions::newFromAnon(), ParserCache::USE_EXPIRED ) );
		$metadataFromCache = $cache->getMetadata( $this->page, ParserCache::USE_OUTDATED );
		$this->assertNotFalse( $cache->getKey( $this->page,
			ParserOptions::newFromAnon(), ParserCache::USE_OUTDATED ) );
		$this->assertSame( $this->getDummyUsedOptions(), $metadataFromCache->getUsedOptions() );
		$this->assertSame( 4242, $metadataFromCache->getCacheExpiry() );
		$this->assertNotSame( $this->page->getRevisionRecord()->getId(),
			$metadataFromCache->getCacheRevisionId() );
		$this->assertSame( $this->cacheTime, $metadataFromCache->getCacheTime() );
	}

	/**
	 * @covers ParserCache::makeParserOutputKey
	 */
	public function testMakeParserOutputKey() {
		$cache = $this->createParserCache();

		$options1 = ParserOptions::newFromAnon();
		$options1->setOption( $this->getDummyUsedOptions()[0], 'value1' );
		$key1 = $cache->makeParserOutputKey( $this->page, $options1, $this->getDummyUsedOptions() );
		$this->assertNotNull( $key1 );

		$options2 = ParserOptions::newFromAnon();
		$options2->setOption( $this->getDummyUsedOptions()[0], 'value2' );
		$key2 = $cache->makeParserOutputKey( $this->page, $options2, $this->getDummyUsedOptions() );
		$this->assertNotNull( $key2 );
		$this->assertNotSame( $key1, $key2 );
	}

	/*
	 * Test that fetching without storing first returns false.
	 * @covers ParserCache::get
	 */
	public function testGetEmpty() {
		$cache = $this->createParserCache();
		$options = ParserOptions::newFromAnon();

		$this->assertFalse( $cache->get( $this->page, $options ) );
	}

	/**
	 * Test that fetching with the same options return the saved value.
	 * @covers ParserCache::get
	 * @covers ParserCache::save
	 */
	public function testSaveGetSameOptions() {
		$cache = $this->createParserCache();
		$parserOutput = new ParserOutput( 'TEST_TEXT' );

		$options1 = ParserOptions::newFromAnon();
		$options1->setOption( $this->getDummyUsedOptions()[0], 'value1' );
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );

		$savedOutput = $cache->get( $this->page, $options1 );
		$this->assertInstanceOf( ParserOutput::class, $savedOutput );
		// ParserCache adds a comment to the HTML, so check if the result starts with page content.
		$this->assertStringStartsWith( 'TEST_TEXT', $savedOutput->getText() );
		$this->assertSame( $this->cacheTime, $savedOutput->getCacheTime() );
		$this->assertSame( $this->page->getRevisionRecord()->getId(), $savedOutput->getCacheRevisionId() );
	}

	/**
	 * Test that fetching with different unused option returns a value.
	 * @covers ParserCache::get
	 * @covers ParserCache::save
	 */
	public function testSaveGetDifferentUnusedOption() {
		$cache = $this->createParserCache();
		$optionName = $this->getDummyUsedOptions()[0];
		$parserOutput = new ParserOutput( 'TEST_TEXT' );

		$options1 = ParserOptions::newFromAnon();
		$options1->setOption( $optionName, 'value1' );
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );

		$options2 = ParserOptions::newFromAnon();
		$options2->setOption( $optionName, 'value2' );
		$savedOutput = $cache->get( $this->page, $options2 );
		$this->assertInstanceOf( ParserOutput::class, $savedOutput );
		// ParserCache adds a comment to the HTML, so check if the result starts with page content.
		$this->assertStringStartsWith( 'TEST_TEXT', $savedOutput->getText() );
		$this->assertSame( $this->cacheTime, $savedOutput->getCacheTime() );
		$this->assertSame( $this->page->getRevisionRecord()->getId(), $savedOutput->getCacheRevisionId() );
	}

	/**
	 * Test that non-cacheable output is not stored
	 * @covers ParserCache::save
	 */
	public function testDoesNotStoreNonCacheable() {
		$cache = $this->createParserCache();
		$parserOutput = new ParserOutput( 'TEST_TEXT' );
		$parserOutput->updateCacheExpiry( 0 );

		$options1 = ParserOptions::newFromAnon();
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );

		$this->assertFalse( $cache->get( $this->page, $options1 ) );
		$this->assertFalse( $cache->get( $this->page, $options1, true ) );
		$this->assertFalse( $cache->getDirty( $this->page, $options1 ) );
	}

	/**
	 * Test that fetching with different used option don't return a value.
	 * @covers ParserCache::get
	 * @covers ParserCache::save
	 */
	public function testSaveGetDifferentUsedOption() {
		$cache = $this->createParserCache();
		$parserOutput = new ParserOutput( 'TEST_TEXT' );
		$optionName = $this->getDummyUsedOptions()[0];
		$parserOutput->recordOption( $optionName );

		$options1 = ParserOptions::newFromAnon();
		$options1->setOption( $optionName, 'value1' );
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );

		$options2 = ParserOptions::newFromAnon();
		$options2->setOption( $optionName, 'value2' );
		$this->assertFalse( $cache->get( $this->page, $options2 ) );
	}

	/**
	 * Test that output with expired metadata can be retrieved with getDirty
	 * @covers ParserCache::getDirty
	 * @covers ParserCache::get
	 */
	public function testGetExpiredMetadata() {
		$cache = $this->createParserCache();
		$parserOutput = new ParserOutput( 'TEST_TEXT' );
		$parserOutput->updateCacheExpiry( 10 );

		$options1 = ParserOptions::newFromAnon();
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );

		MWTimestamp::setFakeTime( $this->time + 15 * 1000 );
		$this->assertFalse( $cache->get( $this->page, $options1 ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->get( $this->page, $options1, true ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->getDirty( $this->page, $options1 ) );
	}

	/**
	 * Test that expired output with not expired metadata can be retrieved with getDirty
	 * @covers ParserCache::getDirty
	 * @covers ParserCache::get
	 */
	public function testGetExpiredContent() {
		$cache = $this->createParserCache();
		$optionName = $this->getDummyUsedOptions()[0];

		$parserOutput1 = new ParserOutput( 'TEST_TEXT1' );
		$parserOutput1->recordOption( $optionName );
		$parserOutput1->updateCacheExpiry( 10 );
		$options1 = ParserOptions::newFromAnon();
		$options1->setOption( $optionName, 'value1' );
		$cache->save( $parserOutput1, $this->page, $options1, $this->cacheTime );

		$parserOutput2 = new ParserOutput( 'TEST_TEXT2' );
		$parserOutput2->recordOption( $optionName );
		$parserOutput2->updateCacheExpiry( 100500600 );
		$options2 = ParserOptions::newFromAnon();
		$options2->setOption( $optionName, 'value2' );
		$cache->save( $parserOutput2, $this->page, $options2, $this->cacheTime );

		MWTimestamp::setFakeTime( $this->time + 15 * 1000 );
		$this->assertFalse( $cache->get( $this->page, $options1 ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->get( $this->page, $options1, true ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->getDirty( $this->page, $options1 ) );
	}

	/**
	 * Test that output with outdated metadata can be retrieved with getDirty
	 * @covers ParserCache::getDirty
	 * @covers ParserCache::get
	 */
	public function testGetOutdatedMetadata() {
		$cache = $this->createParserCache();
		$parserOutput = new ParserOutput( 'TEST_TEXT' );

		$options1 = ParserOptions::newFromAnon();
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->get( $this->page, $options1 ) );

		$this->editPage( $this->page->getTitle()->getDBkey(), 'Test edit!' );
		$this->page->clear();

		$this->assertFalse( $cache->get( $this->page, $options1 ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->get( $this->page, $options1, true ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->getDirty( $this->page, $options1 ) );
	}

	/**
	 * Test that outdated output with good metadata can be retrieved with getDirty
	 * @covers ParserCache::getDirty
	 * @covers ParserCache::get
	 */
	public function testGetOutdatedContent() {
		$cache = $this->createParserCache();
		$optionName = $this->getDummyUsedOptions()[0];

		$parserOutput1 = new ParserOutput( 'TEST_TEXT' );
		$parserOutput1->recordOption( $optionName );
		$options1 = ParserOptions::newFromAnon();
		$options1->setOption( $optionName, 'value1' );
		$cache->save( $parserOutput1, $this->page, $options1, $this->cacheTime );

		$this->editPage( $this->page->getTitle()->getDBkey(), 'Test edit!' );
		$this->page->clear();

		$parserOutput2 = new ParserOutput( 'TEST_TEXT' );
		$parserOutput2->recordOption( $optionName );
		$options2 = ParserOptions::newFromAnon();
		$options2->setOption( $optionName, 'value2' );
		$cache->save( $parserOutput2, $this->page, $options2, $this->cacheTime );

		$this->assertFalse( $cache->get( $this->page, $options1 ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->get( $this->page, $options1, true ) );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->getDirty( $this->page, $options1 ) );
	}

	/**
	 * Test that fetching after deleting a key returns false.
	 * @covers ParserCache::deleteOptionsKey
	 */
	public function testDeleteOptionsKey() {
		$cache = $this->createParserCache();
		$parserOutput = new ParserOutput( 'TEST_TEXT' );

		$options1 = ParserOptions::newFromAnon();
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );
		$this->assertInstanceOf( ParserOutput::class,
			$cache->get( $this->page, $options1 ) );
		$cache->deleteOptionsKey( $this->page );

		$this->assertFalse( $cache->get( $this->page, $options1 ) );
	}

	/**
	 * Test that RejectParserCacheValue hook can reject ParserOutput
	 * @covers ParserCache::get
	 */
	public function testRejectedByHook() {
		$parserOutput = new ParserOutput( 'TEST_TEXT' );
		$options = ParserOptions::newFromAnon();
		$options->setOption( $this->getDummyUsedOptions()[0], 'value1' );
		$hookContainer = $this->createHookContainer( [
			'RejectParserCacheValue' =>
				function ( ParserOutput $value, WikiPage $hookPage, ParserOptions $popts )
				use ( $parserOutput, $options ) {
					$this->assertSame( $parserOutput, $value );
					$this->assertSame( $this->page, $hookPage );
					$this->assertSame( $options, $popts );
					return false;
				}
		] );
		$cache = $this->createParserCache( $hookContainer );
		$cache->save( $parserOutput, $this->page, $options, $this->cacheTime );
		$this->assertFalse( $cache->get( $this->page, $options ) );
	}

	/**
	 * Test that ParserCacheSaveComplete hook is run
	 * @covers ParserCache::save
	 */
	public function testParserCacheSaveCompleteHook() {
		$parserOutput = new ParserOutput( 'TEST_TEXT' );
		$options = ParserOptions::newFromAnon();
		$options->setOption( $this->getDummyUsedOptions()[0], 'value1' );
		$hookContainer = $this->createHookContainer( [
			'ParserCacheSaveComplete' =>
				function ( ParserCache $hookCache, ParserOutput $value,
						   Title $hookTitle, ParserOptions $popts, int $revId )
				use ( $parserOutput, $options ) {
					$this->assertSame( $parserOutput, $value );
					$this->assertSame( $this->page->getTitle(), $hookTitle );
					$this->assertSame( $options, $popts );
					$this->assertSame( 42, $revId );
				}
		] );
		$cache = $this->createParserCache( $hookContainer );
		$cache->save( $parserOutput, $this->page, $options, $this->cacheTime, 42 );
	}

	/**
	 * Tests that parser cache respects 'WikiPage::checkTouched'
	 * @covers ParserCache::get
	 */
	public function testRespectsCheckTouched() {
		$cache = $this->createParserCache();
		$mockPage = $this->createNoOpMock( WikiPage::class, [ 'checkTouched' ] );
		$mockPage->method( 'checkTouched' )
			->willReturn( false );
		$this->assertFalse( $cache->get( $mockPage, ParserOptions::newFromAnon() ) );
	}

	/**
	 * Tests that getCacheStorage returns underlying BagOStuff
	 * @covers ParserCache::getCacheStorage
	 */
	public function testGetCacheStorage() {
		$storage = new EmptyBagOStuff();
		$cache = $this->createParserCache( null, $storage );
		$this->assertSame( $storage, $cache->getCacheStorage() );
	}

	/**
	 * @covers ParserCache::save
	 */
	public function testSaveNoText() {
		$this->expectException( InvalidArgumentException::class );
		$this->createParserCache()->save(
			new ParserOutput( null ),
			$this->page,
			ParserOptions::newFromAnon()
		);
	}

	public function provideCorruptData() {
		yield 'PHP serialization, bad data' => [ false, 'bla bla' ];
		yield 'JSON serialization, bad data' => [ true, 'bla bla' ];
		yield 'JSON serialization, no _class_' => [ true, '{"test":"test"}' ];
		yield 'JSON serialization, non-existing _class_' => [ true, '{"_class_":"NonExistentBogusClass"}' ];
		$wrongInstance = new JsonUnserializableSuperClass( 'test' );
		yield 'JSON serialization, wrong class' => [ true, json_encode( $wrongInstance->jsonSerialize() ) ];
	}

	/**
	 * Test that we handle corrupt data gracefully.
	 * This is important for forward-compatibility with JSON serialization.
	 * We want to be sure that we don't crash horribly if we have to roll
	 * back to a version of the code that doesn't know about JSON.
	 *
	 * @dataProvider provideCorruptData
	 * @covers ParserCache::get
	 * @covers ParserCache::restoreFromJson
	 * @param bool $json
	 * @param string $data
	 */
	public function testCorruptData( bool $json, string $data ) {
		$bag = new HashBagOStuff();
		$cache = $this->createParserCache( null, $bag );
		$cache->setJsonSupport( $json, $json );
		$parserOutput = new ParserOutput( 'TEST_TEXT' );

		$options1 = ParserOptions::newFromAnon();
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );

		$outputKey = $cache->makeParserOutputKey(
			$this->page,
			$options1,
			$parserOutput->getUsedOptions()
		);

		$bag->set( $outputKey, $data );

		// just make sure we don't crash and burn
		$this->assertFalse( $cache->get( $this->page, $options1 ) );
	}

	/**
	 * Test that we handle corrupt data gracefully.
	 * This is important for forward-compatibility with JSON serialization.
	 * We want to be sure that we don't crash horribly if we have to roll
	 * back to a version of the code that doesn't know about JSON.
	 *
	 * @covers ParserCache::getMetadata
	 * @covers ParserCache::getKey
	 */
	public function testCorruptMetadata() {
		$this->hideDeprecated( 'ParserCache::getKey' );
		$bag = new HashBagOStuff();
		$cache = $this->createParserCache( null, $bag );
		$parserOutput = new ParserOutput( 'TEST_TEXT' );

		$options1 = ParserOptions::newFromAnon();
		$cache->save( $parserOutput, $this->page, $options1, $this->cacheTime );

		$optionsKey = TestingAccessWrapper::newFromObject( $cache )->makeMetadataKey(
			$this->page
		);

		$bag->set( $optionsKey, 'bad data' );

		// just make sure we don't crash and burn
		$this->assertNull( $cache->getMetadata( $this->page ) );
		$this->assertFalse( $cache->getKey( $this->page, $options1, false ) );
	}

	/**
	 * Test whats happen when we turn on JSON support but there
	 * are still old entries in the cache.
	 *
	 * @covers ParserCache::get
	 */
	public function testMigrationToJson() {
		$cache = $this->createParserCache();
		$cache->setJsonSupport( false, false );

		$parserOutput1 = new ParserOutput( 'Lorem Ipsum' );

		$options = ParserOptions::newFromAnon();
		$cache->save( $parserOutput1, $this->page, $options, $this->cacheTime );

		// emulate migration to JSON
		$cache->setJsonSupport( true, true );

		// make sure we can load non-json cache data
		$cachedOutput = $cache->get( $this->page, $options );
		$this->assertEquals( $parserOutput1, $cachedOutput );

		// now test that the cache works when using JSON
		$parserOutput2 = new ParserOutput( 'dolor sit amet' );
		$cache->save( $parserOutput2, $this->page, $options, $this->cacheTime );

		// make sure we can load json cache data
		$cachedOutput = $cache->get( $this->page, $options );
		$this->assertEquals( $parserOutput2, $cachedOutput );
	}

	/**
	 * Test whats happen when we have to roll back from supporting JSON
	 * to not supporting JSON.
	 *
	 * @covers ParserCache::get
	 */
	public function testRollbackFromJson() {
		$cache = $this->createParserCache();
		$cache->setJsonSupport( true, true );

		$parserOutput1 = new ParserOutput( 'Lorem Ipsum' );

		$options = ParserOptions::newFromAnon();
		$cache->save( $parserOutput1, $this->page, $options, $this->cacheTime );

		// emulate rolling back to not writing but still reading JSON
		$cache->setJsonSupport( true, false );

		// make sure we can load json cache data
		$cachedOutput = $cache->get( $this->page, $options );
		$this->assertEquals( $parserOutput1, $cachedOutput );

		// emulate rolling back to not reading JSON
		$cache->setJsonSupport( false, false );

		// make sure we don't crash and burn
		$cachedOutput = $cache->get( $this->page, $options );
		$this->assertFalse( $cachedOutput );

		// now test that the cache works without JSON
		$parserOutput2 = new ParserOutput( 'dolor sit amet' );
		$cache->save( $parserOutput2, $this->page, $options, $this->cacheTime );

		// make sure we can load non-json cache data
		$cachedOutput = $cache->get( $this->page, $options );
		$this->assertEquals( $parserOutput2, $cachedOutput );
	}

	/**
	 * @covers ParserCache::encodeAsJson
	 */
	public function testNonSerializableJsonIsReported() {
		$testLogger = new TestLogger( true );
		$cache = $this->createParserCache( null, null, $testLogger );
		$cache->setJsonSupport( true, true );

		$parserOutput = $this->createDummyParserOutput();
		$parserOutput->setExtensionData( 'test', new User() );
		$cache->save( $parserOutput, $this->page, ParserOptions::newFromAnon() );
		$this->assertArrayEquals(
			[ [ LogLevel::ERROR, 'Unable to serialize JSON' ] ],
			$testLogger->getBuffer()
		);
	}

	/**
	 * @covers ParserCache::encodeAsJson
	 */
	public function testCyclicStructuresDoNotBlowUpInJson() {
		$testLogger = new TestLogger( true );
		$cache = $this->createParserCache( null, null, $testLogger );
		$cache->setJsonSupport( true, true );

		$parserOutput = $this->createDummyParserOutput();
		$cyclicArray = [ 'a' => 'b' ];
		$cyclicArray['c'] = &$cyclicArray;
		$parserOutput->setExtensionData( 'test', $cyclicArray );
		$cache->save( $parserOutput, $this->page, ParserOptions::newFromAnon() );
		$this->assertArrayEquals(
			[ [ LogLevel::ERROR, 'Unable to serialize JSON' ] ],
			$testLogger->getBuffer()
		);
	}

	/**
	 * Tests that unicode characters are not \u escaped
	 * @covers ParserCache::encodeAsJson
	 */
	public function testJsonEncodeUnicode() {
		$unicodeCharacter = "Э";
		$cache = $this->createParserCache( null, new HashBagOStuff() );
		$cache->setJsonSupport( true, true );
		$parserOutput = $this->createDummyParserOutput();
		$parserOutput->setText( $unicodeCharacter );
		$cache->save( $parserOutput, $this->page, ParserOptions::newFromAnon() );
		$json = $cache->getCacheStorage()->get(
			$cache->makeParserOutputKey( $this->page, ParserOptions::newFromAnon() )
		);
		$this->assertStringNotContainsString( "\u003E", $json );
		$this->assertStringContainsString( $unicodeCharacter, $json );
	}
}
