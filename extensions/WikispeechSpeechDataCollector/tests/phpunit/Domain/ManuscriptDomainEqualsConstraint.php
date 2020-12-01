<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Domain;

use MediaWiki\WikispeechSpeechDataCollector\Domain\ManuscriptDomain;

/**
 * @package MediaWiki\WikispeechSpeechDataCollector\Tests\Domain
 * @since 0.1.0
 */
class ManuscriptDomainEqualsConstraint extends PersistentEqualsContraint {

	/**
	 * @since 0.1.0
	 * @param ManuscriptDomain $expected
	 * @param ManuscriptDomain $actual
	 */
	protected function evaluateNonIdentityFields(
		$expected,
		$actual
	) {
		$this->matchIsSame( 'name', $expected->getName(), $actual->getName() );
		$this->matchIsSame( 'parent', $expected->getParent(), $actual->getParent() );
	}

}
