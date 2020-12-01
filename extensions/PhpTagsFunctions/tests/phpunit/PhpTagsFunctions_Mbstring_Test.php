<?php
namespace PhpTags;

class PhpTagsFunctions_Mbstring_Test extends \PHPUnit\Framework\TestCase {

	public function testRun_constant_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo MB_CASE_UPPER;' ),
				[ MB_CASE_UPPER ]
				);
	}

	public function testRun_constant_2() {
		$this->assertEquals(
				Runtime::runSource( 'echo MB_OVERLOAD_STRING;' ),
				[ MB_OVERLOAD_STRING ]
				);
	}

	public function testRun_mb_convert_case_1() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "mary had a Little lamb and she loved it so";
$str = mb_convert_case($str, MB_CASE_UPPER, "UTF-8");
echo $str;' ),
				[ 'MARY HAD A LITTLE LAMB AND SHE LOVED IT SO' ]
				);
	}

	public function testRun_mb_convert_case_2() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "mary had a Little lamb and she loved it so";
$str = mb_convert_case($str, MB_CASE_TITLE);
echo $str;' ),
				[ 'Mary Had A Little Lamb And She Loved It So' ]
				);
	}

	public function testRun_mb_convert_case_3() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός";
$str = mb_convert_case($str, MB_CASE_UPPER, "UTF-8");
echo $str;' ),
				[ 'ΤΆΧΙΣΤΗ ΑΛΏΠΗΞ ΒΑΦΉΣ ΨΗΜΈΝΗ ΓΗ, ΔΡΑΣΚΕΛΊΖΕΙ ΥΠΈΡ ΝΩΘΡΟΎ ΚΥΝΌΣ' ]
				);
	}

	public function testRun_mb_convert_case_4() {
		$this->assertEquals(
				Runtime::runSource( '
$str = mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
echo $str;' ),
				[ 'Τάχιστη Αλώπηξ Βαφήσ Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνόσ' ]
				);
	}

	public function testRun_mb_stripos_1() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "mary had a Little lamb and she loved it so";
echo mb_stripos($str, "LAMB");' ),
				[ '18' ]
				);
	}

	public function testRun_mb_stripos_2() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "Τάχιστη Αλώπηξ Βαφήσ Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνόσ";
echo mb_stripos($str, "ΨΗΜΈΝΗ");' ),
				[ '21' ]
				);
	}

	public function testRun_mb_strlen_1() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "mary had a Little lamb and she loved it so";
echo mb_strlen($str);' ),
				[ '42' ]
				);
	}

	public function testRun_mb_strlen_2() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "Τάχιστη Αλώπηξ Βαφήσ Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνόσ";
echo mb_strlen($str);' ),
				[ '61' ]
				);
	}

	public function testRun_mb_strpos_1() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "mary had a Little lamb and she loved it so";
echo mb_strpos($str, "lamb");' ),
				[ '18' ]
				);
	}

	public function testRun_mb_strpos_2() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "Τάχιστη Αλώπηξ Βαφήσ Ψημένη Γη, Δρασκελίζει Υπέρ Νωθρού Κυνόσ";
echo mb_strpos($str, "Ψημένη");' ),
				[ '21' ]
				);
	}

}
