<?php
namespace PhpTags;

class PhpTagsWiki_Test extends \PHPUnit\Framework\TestCase {

	public function testRun_NS_FILE_constant() {
		$this->assertEquals(
				Runtime::runSource('echo NS_FILE;'),
				array(NS_FILE)
				);
	}

	public function testRun_constant_1() {
		$this->assertEquals(
				Runtime::runSource('echo PHPTAGS_WIKI_VERSION;'),
				array( \ExtensionRegistry::getInstance()->getAllThings()['PhpTags Wiki']['version'] )
			);
	}

	public function testRun_NS_CATEGORY_constant() {
		$this->assertEquals(
				Runtime::runSource('echo NS_CATEGORY;'),
				array(NS_CATEGORY)
				);
	}

	public function testRun_Constant_with_created_object() {
		$this->assertEquals(
				Runtime::runSource( '$obect = new WTitle( "zzzz" ); echo $obect::UNDEFINED_CONST;', array('test'), 1 ),
				array( '<span class="error">PhpTags Notice:  Undefined class constant: WTitle::UNDEFINED_CONST in test on line 1</span><br />', '' )
				);
	}

	public function testRun_get_static_property_from_created_object() {
		$this->assertEquals(
				Runtime::runSource( '$obect = new WTitle( "zzzz" ); echo $obect::$undeclaredStaticProperty;', array('test'), 1 ),
				array( '<span class="error">PhpTags Fatal error:  Access to undeclared static property: WTitle::$undeclaredStaticProperty in test on line 1</span><br />' )
				);
	}

	public function testRun_set_static_property_from_created_object() {
		$this->assertEquals(
				Runtime::runSource( '$obect = new WTitle( "zzzz" ); $obect::$undeclaredStaticProperty = "value";', array('test'), 1 ),
				array( '<span class="error">PhpTags Fatal error:  Access to undeclared static property: WTitle::$undeclaredStaticProperty in test on line 1</span><br />' )
				);
	}

	public function testRun_Title_fullUrl_1() {
		$title = \Title::makeTitleSafe( NS_MAIN, 'Test page' );
		$this->assertEquals(
				Runtime::runSource('$title = new WTitle( "Test page" ); echo $title->fullURL();'),
				array( $title->getFullURL() )
			);
	}

	public function testRun_Title_fullUrl_2() {
		$title = \Title::makeTitleSafe( NS_MAIN, 'Test page' );
		$this->assertEquals(
				Runtime::runSource('$title = new WTitle( "Test page" ); echo $title->fullURL( ["action"=>"edit"] );'),
				array( $title->getFullURL( array('action'=>'edit') ) )
			);
	}

}
