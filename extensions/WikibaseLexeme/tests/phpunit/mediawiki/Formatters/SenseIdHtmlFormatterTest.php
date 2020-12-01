<?php

namespace Wikibase\Lexeme\Tests\MediaWiki\Formatters;

use MediaWikiLangTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Title;
use Wikibase\Lexeme\Domain\Model\SenseId;
use Wikibase\Lexeme\Presentation\Formatters\SenseIdHtmlFormatter;
use Wikibase\Lexeme\Tests\Unit\DataModel\NewLexeme;
use Wikibase\Lexeme\Tests\Unit\DataModel\NewSense;
use Wikibase\Lib\ContentLanguages;
use Wikibase\Lib\LanguageFallbackIndicator;
use Wikibase\Lib\LanguageWithConversion;
use Wikibase\Lib\Store\EntityRevision;
use Wikibase\Lib\Store\EntityRevisionLookup;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikibase\Lib\Store\RevisionedUnresolvedRedirectException;
use Wikibase\Lib\TermLanguageFallbackChain;
use Wikibase\View\DummyLocalizedTextProvider;

/**
 * @covers \Wikibase\Lexeme\Presentation\Formatters\SenseIdHtmlFormatter
 *
 * @license GPL-2.0-or-later
 */
class SenseIdHtmlFormatterTest extends MediaWikiLangTestCase {

	/**
	 * @param SenseId $expectedSenseId
	 * @return MockObject|EntityTitleLookup
	 */
	private function getMockTitleLookup( SenseId $expectedSenseId ) {
		$title = $this->createMock( Title::class );
		$title->method( 'isLocal' )->willReturn( true );
		$title->method( 'getLinkUrl' )->willReturn( 'LOCAL-URL#SENSE' );

		/** @var EntityTitleLookup|MockObject $titleLookup */
		$titleLookup = $this->createMock( EntityTitleLookup::class );
		$titleLookup->method( 'getTitleForId' )
			->with( $expectedSenseId )
			->willReturn( $title );

		return $titleLookup;
	}

	/**
	 * @return MockObject|EntityRevisionLookup
	 */
	private function getMockRevisionLookup() {
		return $this->createMock( EntityRevisionLookup::class );
	}

	/**
	 * @return MockObject|LanguageFallbackIndicator
	 */
	private function getMockLanguageFallbackIndicator() {
		$mock = $this->createMock( LanguageFallbackIndicator::class );
		$mock->method( 'getHtml' )
			->willReturn( 'FB-INDICATOR' );
		return $mock;
	}

	private function getLanguageFallbackChain() {
		return new TermLanguageFallbackChain(
			[
				LanguageWithConversion::factory( 'en' ),
				LanguageWithConversion::factory( 'fr' ),
			],
			$this->getStubContentLanguages()
		);
	}

	private function getStubContentLanguages(): ContentLanguages {
		$mock = $this->createStub( ContentLanguages::class );
		$mock->method( 'hasLanguage' )
			->willReturn( true );
		return $mock;
	}

	private function getFormatter( $senseId, $lookup ) {
		return new SenseIdHtmlFormatter(
			$this->getMockTitleLookup( $senseId ),
			$lookup,
			new DummyLocalizedTextProvider(),
			$this->getLanguageFallbackChain(),
			$this->getMockLanguageFallbackIndicator()
		);
	}

	public function testFormatId_nonExistingEntity() {
		$senseId = new SenseId( 'L10-S20' );
		$lookup = $this->getMockRevisionLookup();
		$lookup->method( 'getEntityRevision' )
			->willReturn( null );
		$formatter = $this->getFormatter( $senseId, $lookup );

		$result = $formatter->formatEntityId( $senseId );

		$this->assertSame( '<a href="LOCAL-URL#SENSE">L10-S20</a>', $result );
	}

	public function testFormatId_redirectedEntity() {
		$senseId = new SenseId( 'L10-S20' );
		$exception = $this->createMock( RevisionedUnresolvedRedirectException::class );
		$lookup = $this->getMockRevisionLookup();
		$lookup->method( 'getEntityRevision' )
			->willThrowException( $exception );
		$formatter = $this->getFormatter( $senseId, $lookup );

		$result = $formatter->formatEntityId( $senseId );

		$this->assertSame( '<a href="LOCAL-URL#SENSE">L10-S20</a>', $result );
	}

	public function testFormatId_oneLemma_noGloss() {
		$senseId = new SenseId( 'L10-S20' );
		$lookup = $this->getMockRevisionLookup();
		$lookup->method( 'getEntityRevision' )
			->willReturnCallback( function ( $entityId ) use ( $senseId ) {
				$entity = NewLexeme::create()
					->withId( $entityId )
					->withLemma( 'en', 'lemma' )
					->withSense( NewSense::havingId( $senseId ) )
					->build();
				return new EntityRevision( $entity );
			} );
		$formatter = $this->getFormatter( $senseId, $lookup );

		$result = $formatter->formatEntityId( $senseId );

		$expected = '<a href="LOCAL-URL#SENSE">L10-S20</a>';
		$this->assertSame( $expected, $result );
	}

	public function testFormatId_oneLemma_ownGlossLanguage() {
		$senseId = new SenseId( 'L10-S20' );
		$lookup = $this->getMockRevisionLookup();
		$lookup->method( 'getEntityRevision' )
			->willReturnCallback( function ( $entityId ) use ( $senseId ) {
				$entity = NewLexeme::create()
					->withId( $entityId )
					->withLemma( 'en', 'lemma' )
					->withSense( NewSense::havingId( $senseId )
						->withGloss( 'fr', 'gloss' ) )
					->build();
				return new EntityRevision( $entity );
			} );
		$formatter = $this->getFormatter( $senseId, $lookup );

		$result = $formatter->formatEntityId( $senseId );

		// phpcs:ignore Generic.Files.LineLength
		$expected = '<a href="LOCAL-URL#SENSE">(wikibaselexeme-senseidformatter-layout: lemma, gloss)</a>FB-INDICATOR';
		$this->assertSame( $expected, $result );
	}

	public function testFormatId_threeLemmas_ownGlossLanguage() {
		$senseId = new SenseId( 'L10-S20' );
		$lookup = $this->getMockRevisionLookup();
		$lookup->method( 'getEntityRevision' )
			->willReturnCallback( function ( $entityId ) use ( $senseId ) {
				$entity = NewLexeme::create()
					->withId( $entityId )
					->withLemma( 'en', 'lemma' )
					->withLemma( 'de', 'Lemma' )
					->withLemma( 'el', 'λεμμα' )
					->withSense( NewSense::havingId( $senseId )
						->withGloss( 'fr', 'gloss' ) )
					->build();
				return new EntityRevision( $entity );
			} );
		$formatter = $this->getFormatter( $senseId, $lookup );

		$result = $formatter->formatEntityId( $senseId );

		// phpcs:ignore Generic.Files.LineLength
		$expected = '<a href="LOCAL-URL#SENSE">(wikibaselexeme-senseidformatter-layout: lemma(wikibaselexeme-presentation-lexeme-display-label-separator-multiple-lemma)Lemma(wikibaselexeme-presentation-lexeme-display-label-separator-multiple-lemma)λεμμα, gloss)</a>FB-INDICATOR';
		$this->assertSame( $expected, $result );
	}

	public function testFormatId_oneLemma_fallbackGlossLanguage() {
		$senseId = new SenseId( 'L10-S20' );
		$lookup = $this->getMockRevisionLookup();
		$lookup->method( 'getEntityRevision' )
			->willReturnCallback( function ( $entityId ) use ( $senseId ) {
				$entity = NewLexeme::create()
					->withId( $entityId )
					->withLemma( 'en', 'lemma' )
					->withSense( NewSense::havingId( $senseId )
						->withGloss( 'en', 'gloss' ) )
					->build();
				return new EntityRevision( $entity );
			} );
		$formatter = $this->getFormatter( $senseId, $lookup );

		$result = $formatter->formatEntityId( $senseId );

		// phpcs:ignore Generic.Files.LineLength
		$expected = '<a href="LOCAL-URL#SENSE">(wikibaselexeme-senseidformatter-layout: lemma, gloss)</a>FB-INDICATOR';
		$this->assertSame( $expected, $result );
	}

	public function testFormatEntityId_htmlEscapesText() {
		$senseId = new SenseId( 'L999-S666' );
		$lookup = $this->getMockRevisionLookup();
		$lookup->method( 'getEntityRevision' )
			->willReturnCallback( function ( $entityId ) use ( $senseId ) {
				$entity = NewLexeme::create()
					->withId( $entityId )
					->withLemma( 'en', 'lemma' )
					->withSense( NewSense::havingId( $senseId )
						->withGloss( 'en', '<script>alert("hi")</script>' ) )
					->build();
				return new EntityRevision( $entity );
			} );
		$formatter = $this->getFormatter( $senseId, $lookup );

		$result = $formatter->formatEntityId( $senseId );

		$this->assertSame(
			// phpcs:ignore Generic.Files.LineLength
			'<a href="LOCAL-URL#SENSE">(wikibaselexeme-senseidformatter-layout: lemma, &lt;script>alert("hi")&lt;/script>)</a>FB-INDICATOR',
			$result
		);
	}

}
