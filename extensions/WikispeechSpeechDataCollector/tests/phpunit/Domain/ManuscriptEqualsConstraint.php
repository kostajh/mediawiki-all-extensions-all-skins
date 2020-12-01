<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Manuscript;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class ManuscriptEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param Manuscript $expected
	 * @param Manuscript $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'name', $expected->getName(), $actual->getName() );
		$this->matchIsTimestampSame( 'created', $expected->getCreated(), $actual->getCreated() );
		$this->matchIsTimestampSame( 'disabled', $expected->getDisabled(), $actual->getDisabled() );
		$this->matchIsSame( 'language', $expected->getLanguage(), $actual->getLanguage() );
		$this->matchIsSame( 'domain', $expected->getDomain(), $actual->getDomain() );
	}

}
