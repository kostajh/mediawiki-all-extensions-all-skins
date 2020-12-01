<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptPrompt;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class ManuscriptPromptEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param ManuscriptPrompt $expected
	 * @param ManuscriptPrompt $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'index', $expected->getIndex(), $actual->getIndex() );
		$this->matchIsSame( 'content', $expected->getContent(), $actual->getContent() );
		$this->matchIsSame( 'manuscript', $expected->getManuscript(), $actual->getManuscript() );
	}

}
