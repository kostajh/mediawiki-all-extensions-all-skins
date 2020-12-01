<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\UserLanguageProficiencyLevel;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class UserLanguageProficiencyLevelEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param UserLanguageProficiencyLevel $expected
	 * @param UserLanguageProficiencyLevel $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'user', $expected->getUser(), $actual->getUser() );
		$this->matchIsSame( 'language', $expected->getLanguage(), $actual->getLanguage() );
		$this->matchIsSame( 'proficiencyLevel',
			$expected->getProficiencyLevel(), $actual->getProficiencyLevel() );
	}

}
