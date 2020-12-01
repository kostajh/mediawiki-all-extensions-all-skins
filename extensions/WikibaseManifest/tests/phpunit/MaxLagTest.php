<?php

namespace WikibaseManifest\Test;

use InvalidArgumentException;
use MediaWiki\Extension\WikibaseManifest\MaxLag;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\WikibaseManifest\MaxLag
 */
class MaxLagTest extends TestCase {

	public function dataProvider() {
		return [
			[ true, 23 ],
			[ false, 23.42 ],
			[ false, "23s" ],
			[ false, [ 23 ] ]
		];
	}

	/**
	 * @dataProvider dataProvider
	 * @param $expectedSuccess
	 * @param $configValue
	 */
	public function test( $expectedSuccess, $configValue ) {
		if ( !$expectedSuccess ) {
			$this->expectException( InvalidArgumentException::class );
		}
		$maxLag = new MaxLag( $configValue );
		$this->assertEquals( $configValue, $maxLag->getValue() );
	}

}
