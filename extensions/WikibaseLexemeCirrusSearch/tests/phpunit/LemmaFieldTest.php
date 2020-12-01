<?php

namespace Wikibase\Lexeme\Search\Elastic\Tests;

use Wikibase\Lexeme\Search\Elastic\LemmaField;

/**
 * @covers \Wikibase\Lexeme\Search\Elastic\LemmaField
 */
class LemmaFieldTest extends LexemeFieldTest {

	/**
	 * @return array
	 */
	public function getTestData() {
		return [
			[
				new LemmaField(),
				[ "Test Lemma" ]
			]
		];
	}

}
