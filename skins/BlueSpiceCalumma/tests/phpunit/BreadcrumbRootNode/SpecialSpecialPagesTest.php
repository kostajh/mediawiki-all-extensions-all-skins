<?php

namespace BlueSpice\Calumma\Tests\BreadcrumbRootNode;

use BlueSpice\Calumma\BreadcrumbRootNode\SpecialSpecialPages;
use BlueSpice\Calumma\IBreadcrumbRootNode;
use HashConfig;
use MediaWikiTestCase;
use Title;

/**
 * @group BlueSpice
 * @group BlueSpiceCalumma
 */
class SpecialSpecialPagesTest extends MediaWikiTestCase {

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\SpecialSpecialPages::factory
	 */
	public function testFactory() {
		$config = new HashConfig( [] );

		$node = SpecialSpecialPages::factory( $config );

		$this->assertInstanceOf(
			IBreadcrumbRootNode::class,
			$node,
			"Should implement IBreadcrumbRootNode"
		);
	}

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\SpecialSpecialPages::getHtml
	 */
	public function testGetHtmlOnSpecialPage() {
		$config = new HashConfig( [] );

		$node = SpecialSpecialPages::factory( $config );

		$factory = \MediaWiki\MediaWikiServices::getInstance()
			->getSpecialPageFactory();
		$title = $factory->getTitleForAlias( 'Allpages' );
		$specialSpecialPages = $factory->getTitleForAlias( 'Specialpages' );

		$html = $node->getHtml( $title );

		$this->assertNotEmpty( $html, 'Should not be empty' );
		$this->assertStringContainsString(
			$specialSpecialPages->getPrefixedText(),
			$html,
			'Should have namespace prefix'
		);
	}

	/**
	 * @covers \BlueSpice\Calumma\BreadcrumbRootNode\SpecialSpecialPages::getHtml
	 */
	public function testGetHtmlOnWikiPage() {
		$config = new HashConfig( [] );

		$node = SpecialSpecialPages::factory( $config );

		$title = Title::makeTitle( NS_HELP, 'SomePage' );

		$html = $node->getHtml( $title );

		$this->assertEmpty( $html, 'Should be empty' );
	}
}
