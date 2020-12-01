<?php

namespace Wikibase\MediaInfo\Tests\MediaWiki\DataAccess\Scribunto;

use Wikibase\Client\Tests\Integration\DataAccess\Scribunto\Scribunto_LuaWikibaseLibraryTestCase;

/**
 * @covers \Wikibase\MediaInfo\DataAccess\Scribunto\Scribunto_LuaWikibaseMediaInfoEntityLibrary
 */
class Scribunto_LuaWikibaseMediaInfoEntityLibraryTest extends Scribunto_LuaWikibaseLibraryTestCase {

	protected static $moduleName = 'LuaWikibaseMediaInfoEntityLibraryTests';

	protected function getTestModules() {
		return parent::getTestModules() + [
			'LuaWikibaseMediaInfoEntityLibraryTests' => __DIR__ . '/LuaWikibaseMediaInfoEntityLibraryTests.lua',
		];
	}

}
