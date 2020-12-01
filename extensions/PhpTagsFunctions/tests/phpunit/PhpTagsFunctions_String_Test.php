<?php
namespace PhpTags;

class PhpTagsFunctions_String_Test extends \PHPUnit\Framework\TestCase {

	public function testRun_constant_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo CRYPT_SALT_LENGTH;' ),
				[ CRYPT_SALT_LENGTH ]
				);
	}

	public function testRun_printf_exception_1() {
		$this->assertEquals(
				Runtime::runSource( 'spRintf();', [ 'Page' ] ),
				[ '<span class="error">PhpTags Warning:  sprintf() expects at least 1 parameter, 0 given in Page on line 1</span><br />' ]
			);
	}

	public function testRun_printf_exception_2() {
		$this->assertEquals(
				Runtime::runSource( 'sprintf("%d обезьян сидят на %s", new DateTime(), "дереве");', [ 'Page' ] ),
				[ '<span class="error">PhpTags Warning:  sprintf() expects parameter 2 to be not object, DateTime given in Page on line 1</span><br />' ]
			);
	}

}
