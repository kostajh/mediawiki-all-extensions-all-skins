<?php

namespace Wikibase\Repo\Tests\Specials;

use Language;
use SpecialPageTestBase;
use Title;
use Wikibase\DataAccess\PrefetchingTermLookup;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\Lib\DataTypeFactory;
use Wikibase\Lib\LanguageFallbackChainFactory;
use Wikibase\Lib\LanguageNameLookup;
use Wikibase\Lib\Store\EntityTitleLookup;
use Wikibase\Lib\Store\LanguageFallbackLabelDescriptionLookup;
use Wikibase\Lib\Store\PropertyInfoLookup;
use Wikibase\Lib\Tests\Store\MockPropertyInfoLookup;
use Wikibase\Repo\EntityIdHtmlLinkFormatterFactory;
use Wikibase\Repo\Specials\SpecialListProperties;

/**
 * @covers \Wikibase\Repo\Specials\SpecialListProperties
 * @covers \Wikibase\Repo\Specials\SpecialWikibaseQueryPage
 * @covers \Wikibase\Repo\Specials\SpecialWikibasePage
 *
 * @group Database
 * @group SpecialPage
 * @group Wikibase
 * @group WikibaseSpecialPage
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 * @author Addshore
 */
class SpecialListPropertiesTest extends SpecialPageTestBase {

	private function getDataTypeFactory() {
		$dataTypeFactory = new DataTypeFactory( [
			'wikibase-item' => 'wikibase-item',
			'string' => 'string',
			'quantity' => 'quantity',
		] );

		return $dataTypeFactory;
	}

	private function getPropertyInfoStore() {
		$propertyInfoLookup = new MockPropertyInfoLookup( [
			'P789' => [ PropertyInfoLookup::KEY_DATA_TYPE => 'string' ],
			'P45' => [ PropertyInfoLookup::KEY_DATA_TYPE => 'wikibase-item' ],
			'P123' => [ PropertyInfoLookup::KEY_DATA_TYPE => 'wikibase-item' ],
		] );

		return $propertyInfoLookup;
	}

	/**
	 * @return PrefetchingTermLookup
	 */
	private function getPrefetchingTermLookup() {
		$lookup = $this->getMockBuilder( PrefetchingTermLookup::class )
			->disableOriginalConstructor()
			->getMock();
		$lookup->expects( $this->any() )
			->method( 'prefetchTerms' );
		$lookup->expects( $this->any() )
			->method( 'getLabels' )
			->will( $this->returnCallback( function( PropertyId $id ) {
				return [ 'en' => 'Property with label ' . $id->getSerialization() ];
			} ) );
		return $lookup;
	}

	/**
	 * @return EntityTitleLookup
	 */
	private function getEntityTitleLookup() {
		$entityTitleLookup = $this->createMock( EntityTitleLookup::class );
		$entityTitleLookup->expects( $this->any() )
			->method( 'getTitleForId' )
			->will( $this->returnCallback(
				function ( EntityId $id ) {
					$title = $this->createMock( Title::class );
					$title->expects( $this->any() )
						->method( 'exists' )
						->will( $this->returnValue( true ) );
					return $title;
				}
			) );

		return $entityTitleLookup;
	}

	protected function newSpecialPage() {
		$language = Language::factory( 'en-gb' );

		$languageNameLookup = $this->createMock( LanguageNameLookup::class );
		$languageNameLookup->expects( $this->never() )
			->method( 'getName' );
		$entityIdFormatterFactory = new EntityIdHtmlLinkFormatterFactory(
			$this->getEntityTitleLookup(),
			$languageNameLookup
		);
		$termLookup = $this->getPrefetchingTermLookup();
		$languageFallbackChainFactory = new LanguageFallbackChainFactory();
		$labelDescriptionLookup = new LanguageFallbackLabelDescriptionLookup(
			$termLookup,
			$languageFallbackChainFactory->newFromLanguage(
				$language,
				LanguageFallbackChainFactory::FALLBACK_ALL
			)
		);
		$entityIdFormatter = $entityIdFormatterFactory->getEntityIdFormatter(
			$language
		);
		$specialPage = new SpecialListProperties(
			$this->getDataTypeFactory(),
			$this->getPropertyInfoStore(),
			$labelDescriptionLookup,
			$entityIdFormatter,
			$this->getEntityTitleLookup(),
			$termLookup,
			$languageFallbackChainFactory
		);

		return $specialPage;
	}

	public function testExecute() {
		// This also tests that there is no fatal error, that the restriction handling is working
		// and doesn't block. That is, the default should let the user execute the page.
		list( $output, ) = $this->executeSpecialPage( '', null, 'qqx' );

		$this->assertIsString( $output );
		$this->assertStringContainsString( 'wikibase-listproperties-summary', $output );
		$this->assertStringContainsString( 'wikibase-listproperties-legend', $output );
		$this->assertStringNotContainsString( 'wikibase-listproperties-invalid-datatype', $output );
		$this->assertRegExp( '/P45.*P123.*P789/', $output ); // order is relevant
	}

	public function testOffsetAndLimit() {
		$request = new \FauxRequest( [ 'limit' => '1', 'offset' => '1' ] );
		list( $output, ) = $this->executeSpecialPage( '', $request, 'qqx' );

		$this->assertStringNotContainsString( 'P45', $output );
		$this->assertStringContainsString( 'P123', $output );
		$this->assertStringNotContainsString( 'P789', $output );
	}

	public function testExecute_empty() {
		list( $output, ) = $this->executeSpecialPage( 'quantity', null, 'qqx' );

		$this->assertStringContainsString( 'specialpage-empty', $output );
	}

	public function testExecute_error() {
		list( $output, ) = $this->executeSpecialPage( 'test<>', null, 'qqx' );

		$this->assertStringContainsString( 'wikibase-listproperties-invalid-datatype', $output );
		$this->assertStringContainsString( 'test&lt;&gt;', $output );
	}

	public function testExecute_wikibase_item() {
		// Use en-gb as language to test language fallback
		list( $output, ) = $this->executeSpecialPage( 'wikibase-item', null, 'en-gb' );

		$this->assertStringContainsString( 'Property with label P45', $output );
		$this->assertStringContainsString( 'Property with label P123', $output );
		$this->assertStringNotContainsString( 'P789', $output );

		$this->assertStringContainsString( 'lang="en"', $output );
		$this->assertStringNotContainsString( 'lang="en-gb"', $output );
	}

	public function testExecute_string() {
		list( $output, ) = $this->executeSpecialPage( 'string', null, 'en-gb' );

		$this->assertStringNotContainsString( 'P45', $output );
		$this->assertStringNotContainsString( 'P123', $output );
		$this->assertStringContainsString( 'Property with label P789', $output );
	}

	public function testSearchSubpages() {
		$specialPage = $this->newSpecialPage();
		$this->assertSame(
			[],
			$specialPage->prefixSearchSubpages( 'g', 10, 0 )
		);
		$this->assertEquals(
			[ 'string' ],
			$specialPage->prefixSearchSubpages( 'st', 10, 0 )
		);
		$this->assertEquals(
			[ 'wikibase-item' ],
			$specialPage->prefixSearchSubpages( 'wik', 10, 0 )
		);
	}

}
