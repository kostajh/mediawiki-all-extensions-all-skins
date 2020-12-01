<?php

namespace Wikibase\Lexeme\Tests\MediaWiki\Scribunto;

use ExtensionRegistry;
use PHPUnit\Framework\TestSuite;
use Wikibase\Client\Tests\Integration\DataAccess\Scribunto\Scribunto_LuaWikibaseLibraryTestCase;
use Wikibase\Client\WikibaseClient;
use Wikibase\Lexeme\Tests\Unit\DataModel\NewLexeme;
use Wikibase\Lib\Tests\MockRepository;

if (
	!ExtensionRegistry::getInstance()->isLoaded( 'WikibaseClient' ) ||
	!ExtensionRegistry::getInstance()->isLoaded( 'Scribunto' )
) {
	/**
	 * Fake base class in case Scribunto or Wikibase client is not available.
	 */
	abstract class Scribunto_LuaWikibaseLexemeLibraryTestCase extends \PHPUnit\Framework\TestCase {

		protected function setUp() : void {
			$this->markTestSkipped( 'WikibaseClient and Scribunto extensions are needed to run the tests' );
		}

		public function testPlaceholder() {
			$this->fail( 'PHPunit expects this class to have tests. This should never run.' );
		}

	}

	return;
}

/**
 * @license GPL-2.0-or-later
 */
class Scribunto_LuaWikibaseLexemeLibraryTestCase extends Scribunto_LuaWikibaseLibraryTestCase {

	private static $originalLexemeEnableDataTransclusion;

	/**
	 * Make sure data transclusion is enabled regardless of wiki configuration.
	 */
	private static function doMock() {
		global $wgLexemeEnableDataTransclusion;
		self::$originalLexemeEnableDataTransclusion = $wgLexemeEnableDataTransclusion;
		$wgLexemeEnableDataTransclusion = true;
	}

	private static function unMock() {
		global $wgLexemeEnableDataTransclusion;
		$wgLexemeEnableDataTransclusion = self::$originalLexemeEnableDataTransclusion;
	}

	/**
	 * Set up stuff we need to have in place even before Scribunto does its stuff.
	 * And remove that again after suite is done, so that other test won't get
	 * affected.
	 *
	 * @param string $className
	 *
	 * @return TestSuite
	 */
	public static function suite( $className ) {
		self::doMock();

		$res = parent::suite( $className );

		self::unMock();

		return $res;
	}

	protected function setUp(): void {
		parent::setUp();

		self::doMock();

		/** @var MockRepository $mockRepository */
		$mockRepository = WikibaseClient::getDefaultInstance()->getStore()->getSiteLinkLookup();
		$mockRepository->putEntity(
			NewLexeme::havingId( 'L1' )
				->withLemma( 'en', 'English lemma' )
				->withLemma( 'en-gb', 'British English lemma' )
				->withLanguage( 'Q1' )
				->withLexicalCategory( 'Q2' )
				->build()
		);
	}

	protected function tearDown(): void {
		self::unMock();

		parent::tearDown();
	}

}
