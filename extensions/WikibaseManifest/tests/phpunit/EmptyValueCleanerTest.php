<?php

namespace MediaWiki\Extension\WikibaseManifest;

use PHPUnit\Framework\TestCase;

class EmptyValueCleanerTest extends TestCase {

	public function dataProvider() {
		return [
			[
				[ 'cat' => 'dog' ],
				[ 'cat' => 'dog' ]
			],
			[
				[ 'cat' => '0' ],
				[ 'cat' => '0' ]
			],
			[
				[ 'cat' => 'false' ],
				[ 'cat' => 'false' ]
			],
			[
				[ 'cat' => '' ],
				[]
			],
			[
				[ 'cat' => [] ],
				[]
			],
			[
				[ 'cat' => null ],
				[]
			],
			[
				[ 'cat' => [], 'sheep' => 'goat' ],
				[ 'sheep' => 'goat' ]
			],
			[
				[ 'cat' => [ 'dog' => [], 'turtle' => 'slow' ], 'sheep' => 'goat' ],
				[ 'cat' => [ 'turtle' => 'slow' ], 'sheep' => 'goat' ],
			],
			[
				[ 'equiv' => [ 'wikidata' => [ 'props' => [], 'items' => [ 'goat' => 'sheep' ] ] ] ],
				[ 'equiv' => [ 'wikidata' => [ 'items' => [ 'goat' => 'sheep' ] ] ] ],
			]
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testClean( $input, $expected ) {
		$cleaner = new EmptyValueCleaner();
		$actual = $cleaner->omitEmptyValues( $input );
		$this->assertEquals( $expected, $actual );
	}
}
