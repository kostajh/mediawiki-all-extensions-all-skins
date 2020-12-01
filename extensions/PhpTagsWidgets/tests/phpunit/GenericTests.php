<?php
namespace PhpTags;

class PhpTagsWidgets_GenericTest extends \MediaWikiTestCase {

	public function testRun_constant_1() {
		$this->assertEquals(
				Runtime::runSource('echo PHPTAGS_WIDGETS_VERSION;'),
				array( \ExtensionRegistry::getInstance()->getAllThings()['PhpTags Widgets']['version'] )
			);
	}

}
