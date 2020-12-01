<?php

/**
 * @group BlueSpice
 * @group BlueSpiceBookmaker
 * @group BlueSpiceBookshelf
 * @group Database
 *
 * @covers PageHierarchyProvider
 */
class PageHierarchyProviderTest extends MediaWikiTestCase {

	/**
	 *
	 * @return Title
	 */
	private function getTestBookTitle() {
		return Title::makeTitle( NS_BOOK, 'UNIT TEST BOOK' );
	}

	/**
	 *
	 * @var PageHierarchyProvider
	 */
	protected $oPHP = null;

	public function addDBDataOnce() {
		$this->insertPage(
			$this->getTestBookTitle()->getPrefixedText(),
			file_get_contents( __DIR__ . '/data/Book_1.wiki' )
		);
	}

	protected function setUp() : void {
		parent::setUp();

		$this->oPHP = PageHierarchyProvider::getInstanceFor(
			$this->getTestBookTitle()->getPrefixedText()
		);
	}

	public function testGetExtendedTOCJSON() {
		$oTree = $this->oPHP->getExtendedTOCJSON();

		$this->assertSame(
			$this->getTestBookTitle()->getPrefixedText(),
			$oTree->articleTitle
		);

		$this->assertObjectHasAttribute( 'children', $oTree );
		$this->assertCount( 3, $oTree->children );

		$oChapterTwo = $oTree->children[1];
		$this->assertObjectHasAttribute( 'children', $oChapterTwo );
		$this->assertCount( 4, $oChapterTwo->children );

		$this->recursiveCheckTreeNode( $oTree );
	}

	public function recursiveCheckTreeNode( $oTreeNode ) {
		$this->assertObjectHasAttribute( 'text', $oTreeNode );
		# $this->assertObjectHasAttribute( 'bookshelf', $oTreeNode );

		$this->assertObjectHasAttribute( 'articleId', $oTreeNode );
		$this->assertObjectHasAttribute( 'articleTitle', $oTreeNode );
		$this->assertObjectHasAttribute( 'articleDisplayTitle', $oTreeNode );

		$this->assertObjectHasAttribute( 'children', $oTreeNode );
		$this->assertTrue( is_array( $oTreeNode->children ) );

		foreach ( $oTreeNode->children as $oChildNode ) {
			$this->recursiveCheckTreeNode( $oChildNode );
		}
	}

	public function testGetNumberFor() {
		$this->assertEquals(
			'2.1.1',
			$this->oPHP->getNumberFor( 'የከፈተውን' )
		);

		$this->assertEquals(
			'2.1.2',
			$this->oPHP->getNumberFor( 'Media:Test テスト.xls' )
		);
	}

	public function testGetEntryFor() {
		$aEntry = (array)$this->oPHP->getEntryFor( 'Dolor sit' );

		$this->assertEquals( 'Dolor sit', $aEntry['articleTitle'] );
		$this->assertEquals( 'amet', $aEntry['articleDisplayTitle'] );
		$this->assertFalse( $aEntry['articleIsRedirect'] );
		$this->assertSame( '2', $aEntry['articleNumber'] );
		$this->assertEquals( 'wikilink-with-alias', $aEntry['articleType'] );
	}
}
