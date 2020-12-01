<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\UserDialect;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class UserDialectEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param UserDialect $expected
	 * @param UserDialect $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'user', $expected->getUser(), $actual->getUser() );
		$this->matchIsSame( 'language', $expected->getLanguage(), $actual->getLanguage() );
		$this->matchIsSame( 'spokenProficiencyLevel',
			$expected->getSpokenProficiencyLevel(), $actual->getSpokenProficiencyLevel() );
		$this->matchIsSame( 'location', $expected->getLocation(), $actual->getLocation() );
	}

}
