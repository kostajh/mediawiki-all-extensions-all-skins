<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\Language;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class LanguageEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param Language $expected
	 * @param Language $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'nativeName', $expected->getNativeName(), $actual->getNativeName() );
		$this->matchIsSame( 'iso639a1', $expected->getIso639a1(), $actual->getIso639a1() );
		$this->matchIsSame( 'iso639a2b', $expected->getIso639a2b(), $actual->getIso639a2b() );
		$this->matchIsSame( 'iso639a2t', $expected->getIso639a2t(), $actual->getIso639a2t() );
		$this->matchIsSame( 'iso639a3', $expected->getIso639a3(), $actual->getIso639a3() );
	}

}
