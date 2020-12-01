<?php
namespace PhpTags;

class PhpTagsFunctions_Useful_Test extends \PHPUnit\Framework\TestCase {

	public function testRun_constant_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo strlen( UUID );' ),
				[ '36' ]
			);
	}

	public function testRun_constant_2() {
		$this->assertEquals(
				Runtime::runSource( 'echo PHPTAGS_FUNCTIONS_VERSION;' ),
				[ \ExtensionRegistry::getInstance()->getAllThings()['PhpTags Functions']['version'] ]
			);
	}

	public function testRun_uuid_create_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo strlen( uuid_create() );' ),
				[ '36' ]
				);
	}

	public function testRun_uuid_mw_json_encode_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo mw_json_encode( ["foo"=>"bar"] );' ),
				[ '{"foo":"bar"}' ]
				);
	}

	public function testRun_uuid_mw_json_decode_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo ["foo"=>"bar"] == mw_json_decode( "{\"foo\":\"bar\"}" ) ? "true" : "false";' ),
				[ 'true' ]
				);
	}

	public function testRun_get_args_1() {
		$this->assertEquals(
				Runtime::runSource( '$tmp = get_args(); echo $tmp[1], $tmp["foo"];', [ 'TestPage', 'one', 'foo' => 'bar' ] ),
				[ 'one', 'bar' ]
				);
	}

	public function testRun_get_args_2() {
		$this->assertEquals(
				Runtime::runSource( 'echo implode( ", ", get_args() );', [ 'TestPage', 'one', 'foo' => 'bar' ] ),
				[ 'one, bar' ]
				);
	}

	public function testRun_get_arg_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo get_arg( 1 ), get_arg( "foo" ), get_arg( "bar", "not defined" );', [ 'TestPage', 'one', 'foo' => 'bar' ] ),
				[ 'one', 'bar', 'not defined' ]
				);
	}

	public function testRun_num_args_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo num_args(), $argc;', [ 'TestPage', 'one', 'foo' => 'bar' ] ),
				[ '2', '3' ]
				);
	}

}
