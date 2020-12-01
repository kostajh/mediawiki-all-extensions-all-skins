<?php

namespace BlueSpice\Bookshelf\Tests\LineProcessor;

use BlueSpice\Bookshelf\LineProcessor\WikiTextLinkNoRedirect;
use BlueSpice\Bookshelf\TreeNode;
use MediaWikiTestCase;

/**
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceBookshelf
 */
class WikiTextLinkNoRedirectTest extends MediaWikiTestCase {

	protected function setUp() : void {
		parent::setUp();

		$this->insertPage( 'Redirect Origin', '#REDIRECT [[Redirect Destination]]' );
		$this->insertPage( 'Redirect Destination' );
	}

	/**
	 *
	 * @covers BlueSpice\Bookshelf\LineProcessor\WikiTextLinkNoRedirect::process
	 */
	public function testProcess() {
		$parser = new WikiTextLinkNoRedirect();
		$node = $parser->process( '[[Redirect Origin]]' );

		$this->assertInstanceOf( TreeNode::class, $node );
		$this->assertEquals(
			-1,
			$node['is-redirect'],
			"Field 'is-redirect' should still have default value"
		);
		$this->assertEquals( 'Redirect Origin', $node['title'] );
	}
}
