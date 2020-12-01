<?php
namespace PhpTags;

class PhpTagsStorage_Test extends \PHPUnit\Framework\TestCase {

	public function testRun_PHPTAGS_STORAGE_VERSION_constant() {
		$this->assertEquals(
				Runtime::runSource('echo PHPTAGS_STORAGE_VERSION;'),
				array(PHPTAGS_STORAGE_VERSION)
				);
	}

}
