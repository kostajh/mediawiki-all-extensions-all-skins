<?php

namespace BlueSpice\Calumma\Tests\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNode\SpecialAllPages;
use BlueSpice\Calumma\IBreadcrumbRootNode;
use HashConfig;
use MediaWikiTestCase;
use Title;

/**
 * @group BlueSpice
 * @group BlueSpiceCalumma
 */
class SpecialAllPagesTest extends MediaWikiTestCase {

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\SpecialAllPages::factory
	 */
	public function testFactory() {
		$config = new HashConfig( [] );

		$node = SpecialAllPages::factory( $config );

		$this->assertInstanceOf(
			IBreadcrumbRootNode::class,
			$node,
			"Should implement IBreadcrumbRootNode"
		);
	}

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\SpecialAllPages::getHtml
	 */
	public function testGetHtmlOnSpecialPage() {
		$config = new HashConfig( [] );

		$node = SpecialAllPages::factory( $config );

		$title = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Allpages' );

		$html = $node->getHtml( $title );

		$this->assertEmpty( $html, 'Should be empty' );
	}

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\SpecialAllPages::getHtml
	 */
	public function testGetHtmlOnWikiPage() {
		$config = new HashConfig( [] );

		$node = SpecialAllPages::factory( $config );

		$title = Title::makeTitle( NS_HELP, 'SomePage' );
		$specialAllPages = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory()
			->getTitleForAlias( 'Allpages' );

		$html = $node->getHtml( $title );

		$this->assertNotEmpty( $html, 'Should not be empty' );
		$this->assertStringContainsString(
			$specialAllPages->getPrefixedText(),
			$html,
			'Should have namespace prefix'
		);
	}
}
