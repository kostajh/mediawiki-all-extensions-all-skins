<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\User;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 */
class UserEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param User $expected
	 * @param User $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'mediaWikiUser', $expected->getMediaWikiUser(), $actual->getMediaWikiUser() );
		$this->matchIsSame( 'yearBorn', $expected->getYearBorn(), $actual->getYearBorn() );
	}

}
