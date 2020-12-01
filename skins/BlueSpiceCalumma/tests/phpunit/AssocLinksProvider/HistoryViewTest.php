<?php

namespace BlueSpice\Calumma\Tests\AssocLinksProvider;

use BlueSpice\Calumma\AssocLinksProvider\HistoryView;
use BlueSpice\Html\Descriptor\ILink;
use HashConfig;
use IContextSource;
use MediaWikiTestCase;
use Title;
use WebRequest;

/**
 * @group BlueSpice
 * @group BlueSpiceCalumma
 */
class HistoryViewTest extends MediaWikiTestCase {

	/**
	 * @covers BlueSpice\Calumma\AssocLinksProvider\HistoryView::factory
	 */
	public function testFactory() {
		$requestMock = $this->createMock( WebRequest::class );
		$requestMock->method( 'getVal' )
			->willReturn( '' );

		$title = Title::newFromText( 'UTPage' );

		$contextMock = $this->createMock( IContextSource::class );
		$contextMock->expects( $this->any() )
			->method( 'getRequest' )
			->willReturn( $requestMock );
		$contextMock->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( $title );

		$config = new HashConfig( [] );

		$links = HistoryView::factory( $contextMock, $config );

		$this->assertArrayHasKey( 'history', $links, "Should have item 'history'" );
		$this->assertInstanceOf(
			ILink::class, $links['history'],
			"Item ''history'' should implement ILink"
		);
		$this->assertStringContainsString(
			'action=history',
			$links['history']->getHref(),
			"Should return proper querystring parameter"
		);
	}

	/**
	 * @covers BlueSpice\Calumma\AssocLinksProvider\HistoryView::factory
	 */
	public function testFactoryEmptyResultBecauseOfAction() {
		$requestMock = $this->createMock( WebRequest::class );
		$requestMock->method( 'getVal' )
			->willReturn( 'history' );

		$mainPageTitle = Title::newMainPage();

		$contextMock = $this->createMock( IContextSource::class );
		$contextMock->expects( $this->any() )
			->method( 'getRequest' )
			->willReturn( $requestMock );
		$contextMock->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( $mainPageTitle );

		$config = new HashConfig( [] );

		$links = HistoryView::factory( $contextMock, $config );

		$this->assertSame( 0, count( $links ), "Should not contain an item" );
	}

	/**
	 * @covers BlueSpice\Calumma\AssocLinksProvider\HistoryView::factory
	 */
	public function testFactoryEmptyResultBecauseOfNonExistingTitle() {
		$requestMock = $this->createMock( WebRequest::class );
		$requestMock->method( 'getVal' )
			->willReturn( '' );

		$title = Title::newFromText( 'Rand' . time() );

		$contextMock = $this->createMock( IContextSource::class );
		$contextMock->expects( $this->any() )
			->method( 'getRequest' )
			->willReturn( $requestMock );
		$contextMock->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( $title );

		$config = new HashConfig( [] );

		$links = HistoryView::factory( $contextMock, $config );

		$this->assertSame( 0, count( $links ), "Should not contain an item" );
	}

	/**
	 * @covers BlueSpice\Calumma\AssocLinksProvider\HistoryView::factory
	 */
	public function testFactoryEmptyResultBecauseOfSpecialPage() {
		$requestMock = $this->createMock( WebRequest::class );
		$requestMock->method( 'getVal' )
			->willReturn( '' );

		$title = Title::makeTitle( NS_SPECIAL, 'Allpages' );

		$contextMock = $this->createMock( IContextSource::class );
		$contextMock->expects( $this->any() )
			->method( 'getRequest' )
			->willReturn( $requestMock );
		$contextMock->expects( $this->any() )
			->method( 'getTitle' )
			->willReturn( $title );

		$config = new HashConfig( [] );

		$links = HistoryView::factory( $contextMock, $config );

		$this->assertSame( 0, count( $links ), "Should not contain an item" );
	}
}
