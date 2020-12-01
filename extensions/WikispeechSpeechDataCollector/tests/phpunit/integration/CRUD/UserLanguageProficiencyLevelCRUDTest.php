<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Integration\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\CRUD\AbstractCRUD;
use MediaWiki\WikispeechSpeechDataCollector\CRUD\UserLanguageProficiencyLevelCRUD;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Class UserLanguageProficiencyLevelCRUDTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Test\Integration\CRUD
 *
 * @since 0.1.0
 * @group Database
 * @covers \MediaWiki\WikispeechSpeechDataCollector\CRUD\UserLanguageProficiencyLevelCRUD
 */
class UserLanguageProficiencyLevelCRUDTest extends AbstractCRUDTest {
	protected function crudFactory(
		ILoadBalancer $dbLoadBalancer
	): AbstractCRUD {
		return new UserLanguageProficiencyLevelCRUD( $dbLoadBalancer );
	}
}
