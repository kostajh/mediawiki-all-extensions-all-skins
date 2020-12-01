<?php

declare( strict_types = 1 );
namespace Wikibase\Repo\Tests\Hooks;

use HtmlArmor;
use Language;
use RequestContext;
use SpecialPage;
use Title;
use Wikibase\Lib\Store\EntityUrlLookup;
use Wikibase\Repo\WikibaseRepo;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \Wikibase\Repo\Hooks\HtmlPageLinkRendererEndHookHandler
 *
 * @group Database
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class HtmlPageLinkRendererEndHookHandlerTest extends HtmlPageLinkRendererEndHookHandlerTestBase {

	/**
	 * @dataProvider validContextProvider
	 */
	public function testDoHtmlPageLinkRendererBegin_validContext( RequestContext $context ) {
		$handler = $this->newInstance();

		$title = $this->newTitle( self::ITEM_WITH_LABEL );
		$text = $title->getFullText();
		$customAttribs = [];

		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$expectedHtml = '<span class="wb-itemlink">'
			. '<span class="wb-itemlink-label" lang="en" dir="ltr">' . self::DUMMY_LABEL . '</span> '
			. '<span class="wb-itemlink-id">(' . self::ITEM_WITH_LABEL . ')</span></span>';

		$this->assertTrue( $ret );
		$this->assertInstanceOf( HtmlArmor::class, $text );
		$this->assertEquals( $expectedHtml, HtmlArmor::getHtml( $text ) );

		$this->assertStringContainsString( self::DUMMY_LABEL, $customAttribs['title'] );
		$this->assertStringContainsString( self::DUMMY_DESCRIPTION, $customAttribs['title'] );

		$this->assertContains( 'wikibase.common', $context->getOutput()->getModuleStyles() );
	}

	/**
	 * @dataProvider invalidContextProvider
	 */
	public function testDoHtmlPageLinkRendererBegin_invalidContext( RequestContext $context ) {
		$handler = $this->newInstance();

		$title = $this->newTitle( self::ITEM_WITH_LABEL );
		$titleText = $title->getFullText();
		$text = $titleText;
		$customAttribs = [];

		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$this->assertTrue( $ret );
		$this->assertEquals( $titleText, $text );
		$this->assertEquals( [], $customAttribs );
	}

	public function overrideSpecialNewEntityLinkProvider() {
		$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();

		foreach ( $entityContentFactory->getEntityTypes() as $entityType ) {
			$entityHandler = $entityContentFactory->getContentHandlerForType( $entityType );
			$specialPage = $entityHandler->getSpecialPageForCreation();

			if ( $specialPage !== null ) {
				yield [ $specialPage ];
			}
		}
	}

	/**
	 * @dataProvider overrideSpecialNewEntityLinkProvider
	 * @param string $linkTitle
	 */
	public function testDoHtmlPageLinkRendererBegin_overrideSpecialNewEntityLink( $linkTitle ) {
		$handler = $this->newInstance();

		$title = Title::makeTitle( NS_MAIN, $linkTitle );
		$text = $title->getFullText();
		$context = $this->newContext();
		$attribs = [];
		$html = null;

		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $attribs, $context, $html );

		$specialPageTitle = SpecialPage::getTitleFor( $linkTitle );

		$this->assertFalse( $ret );
		$this->assertStringContainsString(
			$this->getLinkRenderer()->makeKnownLink( $specialPageTitle ),
			$html
		);
		$this->assertStringContainsString( $specialPageTitle->getFullText(), $html );
	}

	public function testDoHtmlPageLinkRendererBegin_nonEntityTitleLink() {
		$handler = $this->newInstance();

		$title = Title::newMainPage();
		$title->resetArticleID( 1 );
		$this->assertTrue( $title->exists() ); // sanity check

		$titleText = $title->getFullText();
		$text = $titleText;
		$customAttribs = [];

		$context = $this->newContext();
		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$this->assertTrue( $ret );
		$this->assertEquals( $titleText, $text );
		$this->assertEquals( [], $customAttribs );
	}

	public function testDoHtmlPageLinkRendererBegin_deleteItem() {
		$handler = $this->newInstance( "foo", true );

		$title = $this->newTitle( self::ITEM_DELETED, false );
		$titleText = $title->getFullText();
		$text = $titleText;
		$customAttribs = [];

		$context = $this->newContext();
		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$this->assertTrue( $ret );
		$this->assertEquals( $titleText, $text );
	}

	public function testDoHtmlPageLinkRendererBegin_itemHasNoLabel() {
		$handler = $this->newInstance( "Item:Q11", false );

		$title = $this->newTitle( self::ITEM_WITHOUT_LABEL );
		$text = $title->getFullText();
		$customAttribs = [];

		$context = $this->newContext();
		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$expected = '<span class="wb-itemlink">'
			. '<span class="wb-itemlink-label" lang="en" dir="ltr"></span> '
			. '<span class="wb-itemlink-id">(' . self::ITEM_WITHOUT_LABEL . ')</span></span>';

		$this->assertTrue( $ret );
		$this->assertInstanceOf( HtmlArmor::class, $text );
		$this->assertEquals( $expected, HtmlArmor::getHtml( $text ) );
		$this->assertArrayHasKey( 'title', $customAttribs );
		$this->assertNotNull( $customAttribs['title'] );
		$this->assertStringContainsString( self::ITEM_WITHOUT_LABEL, $customAttribs['title'] );
	}

	public function testDoHtmlPageLinkRendererBegin_itemHasNoDescription() {
		$handler = $this->newInstance();

		$title = $this->newTitle( self::ITEM_LABEL_NO_DESCRIPTION );
		$text = $title->getFullText();
		$customAttribs = [];

		$context = $this->newContext();
		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$expected = '<span class="wb-itemlink">'
			. '<span class="wb-itemlink-label" lang="en" dir="ltr">' . self::DUMMY_LABEL . '</span> '
			. '<span class="wb-itemlink-id">(' . self::ITEM_LABEL_NO_DESCRIPTION . ')</span></span>';

		$lang = Language::factory( 'en' );
		$this->assertTrue( $ret );
		$this->assertInstanceOf( HtmlArmor::class, $text );
		$this->assertEquals( $expected, HtmlArmor::getHtml( $text ) );
		$this->assertEquals(
			$lang->getDirMark() . 'linkbegin-label' . $lang->getDirMark(),
			$customAttribs['title']
		);
	}

	public function testDoHtmlPageLinkRendererBegin_itemIsRedirected() {
		$handler = $this->newInstance();
		$title = $this->newTitle( self::ITEM_LABEL_NO_DESCRIPTION );
		$title->mRedirect = true;
		$text = $title->getFullText();
		$customAttribs = [];
		$context = $this->newContext();

		$entityUrl = 'http://www.wikidata.org/wiki/Item:Q1';
		$expectedHref = $entityUrl . '?redirect=no';
		$this->entityUrlLookup->expects( $this->once() )
			->method( 'getLinkUrl' )
			->willReturn( $entityUrl );

		$ret = $handler->doHtmlPageLinkRendererEnd(
		$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$this->assertTrue( $ret );
		$this->assertSame( $customAttribs['href'], $expectedHref );
	}

	public function testGivenIdFromOtherSourcesWithLabelAndDesc_labelAndIdAreUsedAsLinkTextAndLabelAndDescAreUsedInLinkTitle() {
		$handler = $this->newInstance();

		$title = Title::makeTitle(
			NS_MAIN,
			'Special:EntityPage/' . self::ITEM_FOREIGN_NO_PREFIX,
			'',
			self::FOREIGN_REPO_PREFIX
		);
		$text = $title->getFullText();
		$customAttribs = [];
		$context = $this->newContext();

		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$expectedHtml = '<span class="wb-itemlink">'
			. '<span class="wb-itemlink-label" lang="en" dir="ltr">' . self::DUMMY_LABEL_FOREIGN_ITEM . '</span> '
			. '<span class="wb-itemlink-id">('
			. self::ITEM_FOREIGN_NO_PREFIX
			. ')</span></span>';

		$this->assertTrue( $ret );
		$this->assertInstanceOf( HtmlArmor::class, $text );
		$this->assertSame( $expectedHtml, HtmlArmor::getHtml( $text ) );

		$this->assertStringContainsString( self::DUMMY_LABEL_FOREIGN_ITEM, $customAttribs['title'] );
		$this->assertStringContainsString( self::DUMMY_DESCRIPTION_FOREIGN_ITEM, $customAttribs['title'] );
	}

	public function testGivenIdFromOtherSourceWithoutLabelAndDesc_idIsUsedAsLinkTextAndWikitextLinkIsUsedInLinkTitle() {
		$prefixedText = 'expectedPrefixedText';
		$handler = $this->newInstance( $prefixedText );

		$title = Title::makeTitle(
			NS_MAIN,
			'Special:EntityPage/' . self::ITEM_FOREIGN_NO_DATA_NO_PREFIX,
			'',
			self::FOREIGN_REPO_PREFIX
		);
		$text = $title->getFullText();
		$customAttribs = [];
		$context = $this->newContext();

		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$expectedHtml = '<span class="wb-itemlink">'
			. '<span class="wb-itemlink-label" lang="en" dir="ltr"></span> '
			. '<span class="wb-itemlink-id">('
			. self::ITEM_FOREIGN_NO_DATA_NO_PREFIX
			. ')</span></span>';

		$this->assertTrue( $ret );
		$this->assertInstanceOf( HtmlArmor::class, $text );
		$this->assertSame( $expectedHtml, HtmlArmor::getHtml( $text ) );

		$this->assertSame(
			$prefixedText,
			$customAttribs['title']
		);
	}

	public function testGivenEntityPageOnUnknownEntitySource_entityPageIsUsedAsLinkTextAndThereIsNoLinkTitle() {
		$handler = $this->newInstance();

		$title = Title::makeTitle(
			NS_MAIN,
			'Special:EntityPage/' . self::ITEM_FOREIGN_NO_PREFIX,
			'',
			self::UNKNOWN_FOREIGN_REPO
		);
		$text = $title->getFullText();
		$customAttribs = [];
		$context = $this->newContext();

		$ret = $handler->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(), $title, $text, $customAttribs, $context );

		$this->assertTrue( $ret );
		$this->assertSame(
			self::UNKNOWN_FOREIGN_REPO . ':Special:EntityPage/' . self::ITEM_FOREIGN_NO_PREFIX,
			$text
		);

		$this->assertArrayNotHasKey( 'title', $customAttribs );
	}

	/**
	 * @dataProvider entityUrlProvider
	 */
	public function testOverridesUrlForEntityLinks( string $entityId, string $expectedUrl, bool $isDeleted ) {
		$customAttribs = [ 'href' => 'will-be-overridden' ];

		$this->entityUrlLookup = $this->createMock( EntityUrlLookup::class );
		$this->entityUrlLookup->expects( $this->once() )
			->method( 'getLinkUrl' )
			->with( $this->callback( function ( $id ) use ( $entityId ) {
				$this->assertSame( $entityId, $id->getSerialization() );
				return true;
			} ) )
			->willReturn( $expectedUrl );

		$context = $this->newContext();
		$this->newInstance( null, $isDeleted )->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(),
			$this->newTitle( $entityId ),
			$text,
			$customAttribs,
			$context
		);

		$this->assertEquals( $expectedUrl, $customAttribs['href'] );
	}

	public function entityUrlProvider() {
		yield 'existing entity' => [
			'entityId' => self::ITEM_WITH_LABEL,
			'expectedUrl' => 'some-wiki/wiki/Item:' . self::ITEM_WITH_LABEL,
			'isDeleted' => false
		];
		yield 'deleted entity' => [
			'entityId' => self::ITEM_DELETED,
			'expectedUrl' => 'some-wiki/wiki/Item:' . self::ITEM_DELETED,
			'isDeleted' => true
		];
	}

	public function testRemovesRedLinkClassForExistingEntities() {
		$customAttribs = [ 'class' => 'new some-other-class' ];

		$context = $this->newContext();
		$this->newInstance()->doHtmlPageLinkRendererEnd(
			$this->getLinkRenderer(),
			$this->newTitle( self::ITEM_WITH_LABEL ),
			$text,
			$customAttribs,
			$context
		);

		$this->assertEquals( 'some-other-class', $customAttribs['class'] );
	}

	/**
	 * @dataProvider linkTargetProvider
	 */
	public function testExtractForeignIdString( $linkTarget, $expectedOutput ) {
		$wrapper = TestingAccessWrapper::newFromObject( $this->newInstance() );
		$output = $wrapper->extractForeignIdString( $linkTarget );
		$this->assertSame( $expectedOutput, $output );
	}

	public function linkTargetProvider() {
		return [
			'NS=MAIN, title=null' => [ Title::makeTitle( NS_MAIN, null ), null ], // T260853
			'NS=SPECIAL, title=null' => [ Title::makeTitle( NS_SPECIAL, null ), null ],
			'NS=SPECIAL, title=EntityPage/Q123' => [ Title::newFromText( 'Special:EntityPage/Q123' ), 'Q123' ],
			'NS=MAIN, title=Special:EntityPage/Q123' => [ Title::newFromText( 'wikidata:Special:EntityPage/Q123' ), 'Q123' ],
		];
	}

}
