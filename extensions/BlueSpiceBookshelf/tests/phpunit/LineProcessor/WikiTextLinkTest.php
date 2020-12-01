<?php

namespace BlueSpice\Bookshelf\Tests\LineProcessor;

use BlueSpice\Bookshelf\LineProcessor\WikiTextLink;
use BlueSpice\Bookshelf\TreeNode;
use MediaWikiTestCase;

/**
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceBookshelf
 * @covers BlueSpice\Bookshelf\LineProcessor\WikiTextLink
 */
class WikiTextLinkTest extends MediaWikiTestCase {

	protected function setUp() : void {
		parent::setUp();

		$this->insertPage( 'Redirect Origin', '#REDIRECT [[Redirect Destination]]' );
		$this->insertPage( 'Redirect Destination' );
	}

	/**
	 * @param string $line
	 * @param string $expected
	 * @param string $message
	 * @covers BlueSpice\Bookshelf\LineProcessor\WikiTextLink::applies
	 * @dataProvider provideTestAppliesData
	 */
	public function testApplies( $line, $expected, $message ) {
		$processor = new WikiTextLink();

		$this->assertEquals( $expected, $processor->applies( $line ), $message );
	}

	/**
	 *
	 * @return array
	 */
	public function provideTestAppliesData() {
		return [
			[ '', false, 'Should not process emtpy lines' ],
			[ 'Any Value ', false, 'Should not process plain text' ],
			[ '[[Some link]]', true, 'Should process wikitext link' ],
			[ '[[Some link|With label]]', true, 'Should process wikitext link with label' ],
			[ '[http://www.blusepice.com]', false, 'Should not process external link' ]
		];
	}

	/**
	 *
	 * @param string $line
	 * @param array $expectedValues
	 * @covers BlueSpice\Bookshelf\LineProcessor\WikiTextLink::process
	 * @dataProvider provideTestProcessData
	 */
	public function testProcess( $line, $expectedValues ) {
		$parser = new WikiTextLink();
		$node = $parser->process( $line );

		$this->assertInstanceOf( TreeNode::class, $node );
		foreach ( $expectedValues as $fieldName => $expectedFieldValue ) {
			$this->assertEquals(
				$expectedFieldValue,
				$node[$fieldName],
				"Should have proper value set for field '$fieldName'"
			);
		}
	}

	/**
	 *
	 * @return array
	 */
	public function provideTestProcessData() {
		return [
			[ '[[Some link]]', [
				'title' => 'Some link',
				'display-title' => 'Some link'
			] ],
			[ '[[Some link ]]', [
				'title' => 'Some link',
				'display-title' => 'Some link'
			] ],
			[ '[[Some    link]]', [
				'title' => 'Some link',
				'display-title' => 'Some    link'
			] ],
			[ '[[Some <invalid> link]]', [
				'type' => 'plain-text',
				'title' => 'Some <invalid> link',
				'display-title' => 'Some <invalid> link'
			] ],
			[ '[[Some <invalid> link|With label]]', [
				'type' => 'plain-text',
				'title' => 'Some <invalid> link',
				'display-title' => 'With label'
			] ],
			[ '[[Redirect Origin]]', [
				'is-redirect' => true,
				'title' => 'Redirect Destination',
				'display-title' => 'Redirect Origin'
			] ],
		];
	}
}
