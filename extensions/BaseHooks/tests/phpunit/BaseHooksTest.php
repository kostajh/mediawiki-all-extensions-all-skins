<?php

/**
 * @covers BaseHooksHooks
 */
class BaseHooksTest extends MediaWikiLangTestCase {
	public function setUp() : void {
		parent::setUp();
		$this->markTestSkipped(
			'Broken test'
		);
		$this->setMwGlobals( 'wgBaseHooksAfterBottomScriptsStrings', [ 'XYZT test' ] );
		$this->setMwGlobals( 'wgBaseHooksAfterBottomScriptsFiles', [ __DIR__ . '/test.inc' ] );
	}

	/**
	 * @return Skin
	 */
	private function mockSkin( $title = 'Main Page' ) {
		$skin = $this->getMockBuilder( 'SkinFallback' )
			->disableOriginalConstructor()
			->setMethods( [ 'getUser', 'getTitle' ] )
			->getMock();

		return $skin;
	}

	public function testString() {
		$text = '';
		BaseHooksHooks::onSkinAfterBottomScripts( $this->mockSkin(), $text );
		$this->assertContains( 'XYZT test', $text );
		$this->assertContains( 'INC test', $text );
	}
}
