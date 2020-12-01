<?php
/**
 * @group SideBarMenu
 */

namespace SideBarMenu\Tests;

use InvalidArgumentException;
use SideBarMenu\MenuParser;

class MenuParserTest extends \MediaWikiTestCase {

	/**
	 * @var MenuParser
	 */
	private $menuParser;
	/**
	 * @var array
	 */
	private $config;

	protected function setUp() : void {
		parent::setUp();
		$this->config = array( SBM_EXPANDED => true );
		$this->menuParser = new MenuParser( $this->config );
	}

	public function testValidInputWhenNull() {
		$this->assertFalse( $this->menuParser->isValidInput( null ) );
	}

	public function testValidInputWhenEmpty() {
		$this->assertFalse( $this->menuParser->isValidInput( "" ) );
	}

	public function testValidInput() {
		$this->assertTrue( $this->menuParser->isValidInput( "+MenuItem" ) );
	}

	public function testGetLevelWhenNull() {
		$this->assertEquals( 0, $this->menuParser->getLevel( null ) );
	}

	public function testGetLevelWhenEmpty() {
		$this->assertEquals( 0, $this->menuParser->getLevel( "" ) );
	}

	public function testGetLevelWhenValidButNoStars() {
		$this->assertEquals( 0, $this->menuParser->getLevel( "" ) );
	}

	public function testGetLevelWithValid() {
		$this->assertEquals( 3, $this->menuParser->getLevel( "***MenuItem" ) );
	}

	public function testGetExpandedParameterWhenNoneSupplied() {
		$menuParser = new MenuParser( array( SBM_EXPANDED => true ) );
		$this->assertNull( $menuParser->getExpandedParameter( "MenuItem" ) );
		$menuParser = new MenuParser( array( SBM_EXPANDED => false ) );
		$this->assertNull( $menuParser->getExpandedParameter( "MenuItem" ) );
	}

	public function testGetExpandedParameterWhenNotExpanded() {
		$this->assertFalse( $this->menuParser->getExpandedParameter( "-MenuItem" ) );
	}

	public function testGetExpandedParameterWhenExpanded() {
		$this->assertTrue( $this->menuParser->getExpandedParameter( "+MenuItem" ) );
	}

	public function testGetTextParameter() {
		$this->assertEquals( "MenuItem", $this->menuParser->getTextParameter( "+***MenuItem" ) );
		$this->assertEquals( "+MenuItem", $this->menuParser->getTextParameter( "+***+MenuItem" ) );
		$this->assertEquals( "MenuItem", $this->menuParser->getTextParameter( "-MenuItem" ) );
		$this->assertEquals( "MenuItem", $this->menuParser->getTextParameter( "-*MenuItem" ) );
		$this->assertEquals( "MenuItem", $this->menuParser->getTextParameter( "MenuItem" ) );
		$this->assertEquals( "+*MenuItem", $this->menuParser->getTextParameter( "+***+*MenuItem" ) );
		$this->assertEquals( "[[MenuItem|menu]]", $this->menuParser->getTextParameter( "+*[[MenuItem|menu]]" ) );
	}

	public function testGetMenuItemWhenInputIsNull() {
		$this->expectException( InvalidArgumentException::class );
		$this->assertNull( $this->menuParser->getMenuItem( null ) );
	}

	public function testGetMenuItemWhenInputIsEmpty() {
		$this->expectException( InvalidArgumentException::class );
		$this->assertNull( $this->menuParser->getMenuItem( "" ) );
	}

	public function testGetMenuItemWhenInputIsValid() {
		$data = "MenuItem";
		$menuItem = $this->menuParser->getMenuItem( $data );
		$this->assertNotNull( $menuItem );
		$this->assertEquals( $data, $menuItem->getText() );
		$this->assertTrue( $menuItem->isExpanded() );
	}

	public function testGetMenuItemWhenInputIsValidAndExpandIsSet() {
		$text = "MenuItem";
		$data = "+" . $text;
		$menuItem = $this->menuParser->getMenuItem( $data );
		$this->assertNotNull( $menuItem );
		$this->assertEquals( $text, $menuItem->getText() );
		$this->assertTrue( $menuItem->isExpanded() );
	}

	public function testParseDataIntoHierarchicalArray() {
		$data = "MenuItem";
		$array = $this->menuParser->parseDataIntoHierarchicalArray( $data );
		$this->assertNotNull( $array );
		$this->assertEquals( $data, $array[ 0 ] );
	}

	public function testParseDataIntoHierarchicalArrayWithSubLevel() {
		$lines = array( "MenuItem", "*SubMenuItem" );
		$data = join( "\n", $lines );
		$array = $this->menuParser->parseDataIntoHierarchicalArray( $data );
		$this->assertNotNull( $array );
		$this->assertArrayHasKey( $lines[ 0 ], $array );
		$this->assertEquals(
			array( 'MenuItem' => array( '*SubMenuItem' ) ),
			$array
		);
	}

	public function testParseDataIntoHierarchicalArrayWithSeveralSubLevels() {
		$lines = array( "MenuItem", "*SubMenuItem", "*SubMenuItem2", "**SubMenuItemOf2" );
		$data = join( "\n", $lines );
		$array = $this->menuParser->parseDataIntoHierarchicalArray( $data );
		$this->assertNotNull( $array );
		$this->assertEquals(
			array(
				'MenuItem' => array(
					'*SubMenuItem',
					'*SubMenuItem2' => array( '**SubMenuItemOf2' )
				)
			),
			$array
		);
	}

	public function testParseDataIntoHierarchicalArrayWithSubLevelAndBack() {
		$lines = array( "MenuItem", "*SubMenuItem", "MenuItem2" );
		$data = join( "\n", $lines );
		$array = $this->menuParser->parseDataIntoHierarchicalArray( $data );
		$this->assertNotNull( $array );
		$this->assertEquals(
			array(
				'MenuItem' => array( '*SubMenuItem' ),
				'MenuItem2'
			),
			$array );
	}

	public function testParseDataIntoHierarchicalArrayWithSubLevelAndBackSeveralLevels() {
		$lines = array( "MenuItem", "*SubMenuItem1", "**SubMenuItem2", "***SubMenuItem3", "MenuItem2" );
		$data = join( "\n", $lines );
		$array = $this->menuParser->parseDataIntoHierarchicalArray( $data );
		$this->assertNotNull( $array );
		$this->assertEquals(
			array(
				'MenuItem' => array(
					'*SubMenuItem1' => array(
						'**SubMenuItem2' => array( '***SubMenuItem3' )
					)
				),
				'MenuItem2'
			),
			$array );
	}

	public function testGetMenuWithInvalidInput() {
		$this->assertNull( $this->menuParser->getMenuTree( null ) );
		$this->assertNull( $this->menuParser->getMenuTree( "" ) );
	}

	public function testGetMenuWithValidInput() {
		$menu = $this->menuParser->getMenuTree( "MenuItem" );
		$this->assertNotNull( $menu );
		$this->assertTrue( $menu->isRoot() );
		$this->assertEquals( 1, sizeof( $menu->getChildren() ) );

		$children = $menu->getChildren();
		$this->assertEquals( "MenuItem", $children[ 0 ]->getText() );
	}

	public function testGetMenuWithValidComplexInput() {
		$data = array(
			'MenuItem1',
			'*SubMenuItem1',
			'*SubMenuItem2',
			'*SubMenuItem3',
			'**SubMenuItem1Of1',
			'**SubMenuItem2Of1',
			'MenuItem2',
			'*SubMenuItem1OfMenuItem2'
		);
		$menu = $this->menuParser->getMenuTree( join( "\n", $data ) );
		$this->assertNotNull( $menu );
		$this->assertEquals( 2, sizeof( $menu->getChildren() ) );

	}

	public function testGetMenuWithSeveralLineBreaks() {
		$data = array( '', 'MenuItem1', '', '' );
		$menu = $this->menuParser->getMenuTree( join( "\n", $data ) );
		$this->assertNotNull( $menu );
		$this->assertEquals( 1, sizeof( $menu->getChildren() ) );

		$children = $menu->getChildren();
		$this->assertEquals( "MenuItem1", $children[ 0 ]->getText() );
	}

	public function testIndividualElementStyling() {
		$data = array( 'MenuItem1|style=color: red;', );
		$children = $this->menuParser->getMenuTree( join( "\n", $data ) )->getChildren();
		$this->assertEquals( "MenuItem1", $children[ 0 ]->getText() );
		$this->assertEquals( "color: red;", $children[ 0 ]->getCustomCSSStyle() );
	}

	public function testIndividualElementClass() {
		$data = array( 'MenuItem1|class=somecssclass anothercssclass', );
		$children = $this->menuParser->getMenuTree( join( "\n", $data ) )->getChildren();
		$this->assertEquals( "MenuItem1", $children[ 0 ]->getText() );
		$this->assertEquals( "somecssclass anothercssclass", $children[ 0 ]->getCustomCSSClasses() );
	}

	public function testMenuItemTextExpandsSubtreeOnClick() {
		$data = array( '@MenuItem1', );
		$children = $this->menuParser->getMenuTree( join( "\n", $data ) )->getChildren();
		$this->assertEquals( "MenuItem1", $children[ 0 ]->getText() );
		$this->assertTrue( $children[ 0 ]->isExpandAction() );
	}

}
