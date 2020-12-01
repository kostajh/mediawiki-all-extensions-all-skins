<?php

namespace BlueSpice\Calumma\Tests\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNode\NamespaceMainPage;
use BlueSpice\Calumma\IBreadcrumbRootNode;
use HashConfig;
use MediaWikiTestCase;
use Message;
use Title;

/**
 * @group BlueSpice
 * @group BlueSpiceCalumma
 */
class NamespaceMainPageTest extends MediaWikiTestCase {

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\NamespaceMainPage::factory
	 */
	public function testFactory() {
		$config = new HashConfig( [] );

		$node = NamespaceMainPage::factory( $config );

		$this->assertInstanceOf(
			IBreadcrumbRootNode::class,
			$node,
			"Should implement IBreadcrumbRootNode"
		);
	}

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\NamespaceMainPage::getHtml
	 */
	public function testGetHtmlOnSpecialPage() {
		$config = new HashConfig( [] );

		$node = NamespaceMainPage::factory( $config );

		$title = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Allpages' );

		$html = $node->getHtml( $title );

		$this->assertEmpty( $html, 'Should be empty' );
	}

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\NamespaceMainPage::getHtml
	 */
	public function testGetHtmlOnWikiPageNsMain() {
		$config = new HashConfig( [] );

		$node = NamespaceMainPage::factory( $config );

		$title = Title::makeTitle( NS_MAIN, 'SomePage' );

		$html = $node->getHtml( $title );

		$nsText = Message::newFromKey( 'bs-ns_main' )->text();

		$this->assertNotEmpty( $html, 'Should not be empty' );
		$this->assertStringContainsString( $nsText, $html, 'Should have namespace prefix' );
	}

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\NamespaceMainPage::getHtml
	 */
	public function testGetHtmlOnWikiPageNsHelp() {
		$config = new HashConfig( [] );

		$node = NamespaceMainPage::factory( $config );

		$title = Title::makeTitle( NS_HELP, 'SomePage' );

		$html = $node->getHtml( $title );

		$this->assertEmpty( $html, 'Should be empty' );
	}

}
