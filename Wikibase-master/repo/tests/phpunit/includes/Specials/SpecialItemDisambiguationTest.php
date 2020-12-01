<?php

namespace Wikibase\Repo\Tests\Specials;

use FauxRequest;
use InvalidArgumentException;
use SpecialPageTestBase;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Term;
use Wikibase\Lib\LanguageNameLookup;
use Wikibase\Lib\StaticContentLanguages;
use Wikibase\Lib\TermIndexEntry;
use Wikibase\Repo\Api\EntitySearchHelper;
use Wikibase\Repo\ItemDisambiguation;
use Wikibase\Repo\Specials\SpecialItemDisambiguation;

/**
 * @covers \Wikibase\Repo\Specials\SpecialItemDisambiguation
 * @covers \Wikibase\Repo\Specials\SpecialWikibasePage
 *
 * @group Wikibase
 * @group SpecialPage
 * @group WikibaseSpecialPage
 *
 * @group Database
 *        ^---- needed because we rely on Title objects internally
 *
 * @license GPL-2.0-or-later
 */
class SpecialItemDisambiguationTest extends SpecialPageTestBase {

	/**
	 * @return ItemDisambiguation
	 */
	private function getMockItemDisambiguation() {
		$mock = $this->getMockBuilder( ItemDisambiguation::class )
			->disableOriginalConstructor()
			->getMock();
		$mock->expects( $this->any() )
			->method( 'getHTML' )
			->will( $this->returnCallback( function ( $searchResult ) {
				return '<span class="mock-span" >ItemDisambiguationHTML-' . count( $searchResult ) . '</span>';
			} ) );

		return $mock;
	}

	/**
	 * @return EntitySearchHelper
	 */
	private function getMockSearchHelper() {
		$searchResults = [
			[
				'entityId' => new ItemId( 'Q2' ),
				'matchedTermType' => 'label',
				'matchedTerm' => new Term( 'fr', 'Foo' ),
				'displayTerms' => [
					TermIndexEntry::TYPE_DESCRIPTION => new Term( 'en', 'DisplayDescription' ),
				],
			],
			[
				'entityId' => new ItemId( 'Q3' ),
				'matchedTermType' => 'label',
				'matchedTerm' => new Term( 'fr', 'Foo' ),
				'displayTerms' => [
					TermIndexEntry::TYPE_LABEL => new Term( 'en', 'DisplayLabel' ),
				],
			],
		];
		$mock = $this->createMock( EntitySearchHelper::class );

		$mock->expects( $this->any() )
			->method( 'getRankedSearchResults' )
			->will( $this->returnCallback(
				function( $text, $lang, $entityType ) use ( $searchResults ) {
					if ( $lang !== 'fr' ) {
						throw new InvalidArgumentException( 'Not a valid language code' );
					}

					if ( $text === 'Foo' && $entityType === 'item' ) {
						return $searchResults;
					}

					return [];
				}
			) );

		return $mock;
	}

	private function getContentLanguages() {
		return new StaticContentLanguages( [ 'ar', 'de', 'en', 'fr' ] );
	}

	/**
	 * @return LanguageNameLookup
	 */
	private function getMockLanguageNameLookup() {
		$mock = $this->createMock( LanguageNameLookup::class );
		$mock->expects( $this->any() )
			->method( 'getName' )
			->will( $this->returnValue( '<LANG>' ) );

		return $mock;
	}

	protected function newSpecialPage() {
		return new SpecialItemDisambiguation(
			$this->getContentLanguages(),
			$this->getMockLanguageNameLookup(),
			$this->getMockItemDisambiguation(),
			$this->getMockSearchHelper()
		);
	}

	public function testForm() {
		list( $html, ) = $this->executeSpecialPage( '', null, 'qqx' );

		$this->assertStringContainsString( '(wikibase-itemdisambiguation-lookup-language)', $html );
		$this->assertStringContainsString( 'name=\'language\'', $html );
		$this->assertStringContainsString( 'id=\'wb-itemdisambiguation-languagename\'', $html );
		$this->assertStringContainsString( 'wb-language-suggester', $html );

		$this->assertStringContainsString( '(wikibase-itemdisambiguation-lookup-label)', $html );
		$this->assertStringContainsString( 'name=\'label\'', $html );
		$this->assertStringContainsString( 'id=\'labelname\'', $html );

		$this->assertStringContainsString( '(wikibase-itemdisambiguation-submit)', $html );
		$this->assertStringContainsString( 'id=\'wb-itembytitle-submit\'', $html );
	}

	public function testRequestParameters() {
		$request = new FauxRequest( [
			'language' => '<LANGUAGE>',
			'label' => '<LABEL>',
		] );
		list( $html, ) = $this->executeSpecialPage( '', $request );

		$this->assertStringContainsString( '&lt;LANGUAGE&gt;', $html );
		$this->assertStringContainsString( '&lt;LABEL&gt;', $html );
		$this->assertStringNotContainsString( '<LANGUAGE>', $html );
		$this->assertStringNotContainsString( '<LABEL>', $html );
		$this->assertStringNotContainsString( '&amp;', $html, 'no double escaping' );
	}

	public function testSubPageParts() {
		list( $html, ) = $this->executeSpecialPage( '<LANGUAGE>/<LABEL>' );

		$this->assertStringContainsString( '&lt;LANGUAGE&gt;', $html );
		$this->assertStringContainsString( '&lt;LABEL&gt;', $html );
	}

	public function testNoLanguage() {
		list( $html, ) = $this->executeSpecialPage( '', null, 'qqx' );

		$this->assertStringNotContainsString( 'mock-span', $html );
	}

	public function testInvalidLanguage() {
		list( $html, ) = $this->executeSpecialPage( 'invalid/Foo', null, 'qqx' );

		$this->assertStringContainsString( '(wikibase-itemdisambiguation-invalid-langcode)', $html );
	}

	public function testNoLabel() {
		list( $html, ) = $this->executeSpecialPage( 'fr', null, 'qqx' );

		$this->assertStringNotContainsString( 'mock-span', $html );
	}

	public function testUnknownLabel() {
		list( $html, ) = $this->executeSpecialPage( 'fr/Unknown', null, 'qqx' );

		$this->assertStringContainsString( 'value=\'fr\'', $html );
		$this->assertStringContainsString( 'value=\'Unknown\'', $html );
		$this->assertStringContainsString( '(wikibase-itemdisambiguation-nothing-found)', $html );
	}

	public function testKnownLabel() {
		list( $html, ) = $this->executeSpecialPage( 'fr/Foo', null, 'qqx' );

		$this->assertStringContainsString( '<span class="mock-span" >ItemDisambiguationHTML-2</span>', $html );
	}

}
