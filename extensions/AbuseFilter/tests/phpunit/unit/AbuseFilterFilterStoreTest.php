<?php

use MediaWiki\Extension\AbuseFilter\ChangeTags\ChangeTagsManager;
use MediaWiki\Extension\AbuseFilter\FilterCompare;
use MediaWiki\Extension\AbuseFilter\FilterLookup;
use MediaWiki\Extension\AbuseFilter\FilterProfiler;
use MediaWiki\Extension\AbuseFilter\FilterStore;
use MediaWiki\Extension\AbuseFilter\FilterValidator;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * @group Test
 * @group AbuseFilter
 * @coversDefaultClass \MediaWiki\Extension\AbuseFilter\FilterStore
 * @todo Expand this. FilterStore is tightly bound to a Database, so it's not easy.
 */
class AbuseFilterFilterStoreTest extends MediaWikiUnitTestCase {
	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$this->assertInstanceOf(
			FilterStore::class,
			new FilterStore(
				[],
				$this->createMock( ILoadBalancer::class ),
				$this->createMock( FilterProfiler::class ),
				$this->createMock( FilterLookup::class ),
				$this->createMock( ChangeTagsManager::class ),
				$this->createMock( FilterValidator::class ),
				$this->createMock( FilterCompare::class )
			)
		);
	}
}
