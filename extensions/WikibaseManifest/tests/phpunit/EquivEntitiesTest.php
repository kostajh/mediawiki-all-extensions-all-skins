<?php

namespace WikibaseManifest\Test;

use InvalidArgumentException;
use MediaWiki\Extension\WikibaseManifest\EquivEntities;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikibaseManifest\EquivEntities
 */
class EquivEntitiesTest extends TestCase {

	public function dataProvider() {
		return [
			[ true, [ 'properties' => [ 'P12' => 'P34' ] ] ],
			[ true, [ 'items' => [ 'Q42' => 'Q1' ] ] ],
			[ true, [ 'properties' => [ 'P12' => 'P34' ] ], [ 'items' => [ 'Q42' => 'Q1' ] ] ],
			[ false, [ 'properties' => [ 'P12' => 12 ] ] ],
			[ false, [ 'properties' => [ 45 => 'P34' ] ] ],
			[ false, [ 'properties' => [ 45 => null ] ] ],
			[ false, [ 'properties' => [ 'P12' => 'P34' ], 'Q42' => 'Q1' ] ],
			[ false, [ 'P12' => 'P34', 'Q42' => 'Q1' ] ],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function test( $expectedSuccess, $mapping ) {
		if ( !$expectedSuccess ) {
			$this->expectException( InvalidArgumentException::class );
		}
		$value = new EquivEntities( $mapping );
		$this->assertEquals( $mapping, $value->toArray() );
	}

}
