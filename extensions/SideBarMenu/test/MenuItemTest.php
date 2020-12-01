<?php
/**
 * @group SideBarMenu
 */

namespace SideBarMenu\Tests;

use SideBarMenu\MenuItem;

class MenuItemTest extends \MediaWikiTestCase {

	/**
	 * @var MenuItem
	 */
	private $menuItem;
	/**
	 * @var array
	 */
	private $config;

	protected function setUp() : void {
		parent::setUp();
		$this->config = array( SBM_EXPANDED => false );
		$this->menuItem = new MenuItem( $this->config );
	}

	public function testIsRoot() {
		$this->assertTrue( $this->menuItem->isRoot() );
	}

	public function testIsNotRoot() {
		$this->menuItem->setParent( new MenuItem( $this->config ) );
		$this->assertFalse( $this->menuItem->isRoot() );
	}

	public function testGetLevelWhenRoot() {
		$this->assertEquals( 0, $this->menuItem->getLevel() );
	}

	public function testGetLevelWhenChild() {
		$this->menuItem->setParent( new MenuItem( $this->config ) );
		$this->assertEquals( 1, $this->menuItem->getLevel() );
	}

	public function testGetLevelWhenSeveralChildren() {
		$prev = $this->menuItem;
		for ( $x = 0; $x < 10; $x ++ ) {
			$child = new MenuItem( $this->config );
			$child->setParent( $prev );
			$prev = $child;

		}
		$this->assertEquals( 10, $prev->getLevel() );
	}

	public function testAddChildAlsoAddsParent() {
		$child = new MenuItem( $this->config );
		$this->menuItem->addChild( $child );
		$this->assertContains( $child, $this->menuItem->getChildren() );
		$this->assertEquals( $child->getParent(), $this->menuItem );
	}

	public function testAddSameChildSeveralTimes() {
		$child = new MenuItem( $this->config );
		for ( $x = 0; $x < 3; $x ++ ) {
			$this->menuItem->addChild( $child );
		}
		$this->assertEquals( 1, sizeof( $this->menuItem->getChildren() ) );
	}

	public function testAddParentAlsoAddsChild() {
		$parent = new MenuItem( $this->config );
		$this->menuItem->setParent( $parent );
		$this->assertContains( $this->menuItem, $parent->getChildren() );
	}

	public function testToHTMLOnRootMenuItem() {
		$html = $this->menuItem->toHTML();
		$this->assertEquals( "", $html );
	}

	public function testToHTMLOnProperMenuItem() {
		$menuItemChild = new MenuItem( $this->config );
		$menuItemChild->setText( "MenuItem1" );
		$this->menuItem->addChild( $menuItemChild );
		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem1</span></div></li></ul>', $html );
	}

	public function testToHTMLOnSeveralMenuItems() {
		$menuItemChild1 = new MenuItem( $this->config );
		$menuItemChild1->setText( "MenuItem1" );
		$menuItemChild2 = new MenuItem( $this->config );
		$menuItemChild2->setText( "MenuItem2" );

		$this->menuItem->addChild( $menuItemChild1 );
		$this->menuItem->addChild( $menuItemChild2 );

		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem1</span></div></li><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem2</span></div></li></ul>', $html );
	}

	public function testToHTMLOnSeveralMenuItemsWithSublevels() {
		$menuItemChild1 = new MenuItem( $this->config );
		$menuItemChild1->setText( "MenuItem1" );
		$menuItemChild2 = new MenuItem( $this->config );
		$menuItemChild2->setText( "MenuItem2" );

		$this->menuItem->addChild( $menuItemChild1 );
		$this->menuItem->addChild( $menuItemChild2 );

		$subLevel1 = new MenuItem( $this->config );
		$subLevel1->setText( "SubMenuItem1" );
		$subLevel1->setParent( $menuItemChild2 );

		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem1</span></div></li><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-collapsed"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem2</span></div><ul class="sidebar-menu sidebar-menu-1"><li class="sidebar-menu-item sidebar-menu-item-2 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-2">SubMenuItem1</span></div></li></ul></li></ul>', $html );
	}

	public function testToHTMLOnSeveralMenuItemsWithSublevelsWhereExpandedIsTrue() {
		$menuItemChild = new MenuItem( $this->config );
		$menuItemChild->setText( "MenuItem" );
		$menuItemChild->setExpanded( true );

		$this->menuItem->addChild( $menuItemChild );

		$subLevel1 = new MenuItem( $this->config );
		$subLevel1->setText( "SubMenuItem1" );
		$subLevel1->setParent( $menuItemChild );

		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-expanded"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem</span><span class="sidebar-menu-item-controls"></span></div><ul class="sidebar-menu sidebar-menu-1"><li class="sidebar-menu-item sidebar-menu-item-2 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-2">SubMenuItem1</span></div></li></ul></li></ul>', $html );
	}

	public function testToHTMLOnSeveralMenuItemsWithSublevelsWhereExpandedIsFalse() {
		$menuItemChild = new MenuItem( $this->config );
		$menuItemChild->setText( "MenuItem" );
		$menuItemChild->setExpanded( false );

		$this->menuItem->addChild( $menuItemChild );

		$subLevel1 = new MenuItem( $this->config );
		$subLevel1->setText( "SubMenuItem1" );
		$subLevel1->setParent( $menuItemChild );

		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-collapsed"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem</span><span class="sidebar-menu-item-controls"></span></div><ul class="sidebar-menu sidebar-menu-1"><li class="sidebar-menu-item sidebar-menu-item-2 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-2">SubMenuItem1</span></div></li></ul></li></ul>', $html );
	}

	public function testToHTMLOnMenuItemWithStyling() {
		$menuItemChild = new MenuItem( $this->config );
		$menuItemChild->setText( "MenuItem1" );
		$menuItemChild->setCustomCSSStyle( 'color: red;' );
		$this->menuItem->addChild( $menuItemChild );
		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-last" style="color: red;"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem1</span></div></li></ul>', $html );
	}

	public function testToHTMLOnMenuItemWithCustomClasses() {
		$menuItemChild = new MenuItem( $this->config );
		$menuItemChild->setText( "MenuItem1" );
		$menuItemChild->setCustomCSSClasses( 'testclass1 testclass2' );
		$this->menuItem->addChild( $menuItemChild );
		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-last testclass1 testclass2"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1">MenuItem1</span></div></li></ul>', $html );
	}

	public function testToHTMLMenuItemWithExpandAction() {
		$menuItemChild = new MenuItem( $this->config );
		$menuItemChild->setText( "MenuItem1" );
		$menuItemChild->setExpandAction( true );
		$this->menuItem->addChild( $menuItemChild );
		$html = $this->menuItem->toHTML();
		$this->assertEquals( '<ul class="sidebar-menu sidebar-menu-0"><li class="sidebar-menu-item sidebar-menu-item-1 sidebar-menu-item-last"><div class="sidebar-menu-item-text-container"><span class="sidebar-menu-item-text sidebar-menu-item-text-1 sidebar-menu-item-expand-action">MenuItem1</span></div></li></ul>', $html );
	}
}
