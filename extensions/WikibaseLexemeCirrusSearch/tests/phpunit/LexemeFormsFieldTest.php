<?php

namespace Wikibase\Lexeme\Search\Elastic\Tests;

use Wikibase\Lexeme\Search\Elastic\FormsField;

/**
 * @covers \Wikibase\Lexeme\Search\Elastic\FormsField
 */
class LexemeFormsFieldTest extends LexemeFieldTest {

	/**
	 * @return array
	 */
	public function getTestData() {
		return [
			[
				new FormsField(),
				[
					[
						'id' => 'L1-F1',
						'representation' => [ 'Color', 'colour' ],
						'features' => [ 'Q111', 'Q222' ],
					],
					[
						'id' => 'L1-F2',
						'representation' => [ 'testform', 'Test Form' ],
						'features' => [],
					],
				],
			],
		];
	}

}
