<?php

namespace MediaWiki\WikispeechSpeechDataCollector\Tests\Integration\CRUD;

use MediaWiki\WikispeechSpeechDataCollector\CRUD\AbstractCRUD;
use MediaWiki\WikispeechSpeechDataCollector\CRUD\SkippedManuscriptPromptCRUD;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Class SkippedManuscriptPromptCRUDTest
 * @package MediaWiki\WikispeechSpeechDataCollector\Test\Integration\CRUD
 *
 * @since 0.1.0
 * @group Database
 * @covers \MediaWiki\WikispeechSpeechDataCollector\CRUD\SkippedManuscriptPromptCRUD
 */
class SkippedManuscriptPromptCRUDTest extends AbstractCRUDTest {
	protected function crudFactory(
		ILoadBalancer $dbLoadBalancer
	): AbstractCRUD {
		return new SkippedManuscriptPromptCRUD( $dbLoadBalancer );
	}
}
