<?php

namespace Wikibase\Lexeme\Search\Elastic\Tests;

use Wikibase\Lexeme\Search\Elastic\LexemeCategoryField;

/**
 * @covers \Wikibase\Lexeme\Search\Elastic\LexemeCategoryField
 */
class LexemeCategoryFieldTest extends LexemeFieldTest {

	/**
	 * @return array
	 */
	public function getTestData() {
		return [
			[
				new LexemeCategoryField(),
				self::CATEGORY_ID
			]
		];
	}

}
