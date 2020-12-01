<?php
namespace PhpTags;

class PhpTagsFunctions_PCRE_Test extends \PHPUnit\Framework\TestCase {

	public function testRun_constant_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo PREG_PATTERN_ORDER;' ),
				[ PREG_PATTERN_ORDER ]
				);
	}

	public function testRun_constant_2() {
		$this->assertEquals(
				Runtime::runSource( 'echo PREG_OFFSET_CAPTURE;' ),
				[ PREG_OFFSET_CAPTURE ]
				);
	}

	public function testRun_preg_filter_1() {
		$return = Runtime::runSource( '
$subject = array("1", "a", "2", "b", "3", "A", "B", "4");
$pattern = array("/\d/", "/[a-z]/", "/[1a]/");
$replace = array(\'A:$0\', \'B:$0\', \'C:$0\');
print_r( preg_filter($pattern, $replace, $subject) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'A:C:1', 'B:C:a', 'A:2', 'B:b', 'A:3', 7 => 'A:4' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_preg_replace_1() {
		$return = Runtime::runSource( ' print_r( preg_replace($pattern, $replace, $subject) ); ' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'A:C:1', 'B:C:a', 'A:2', 'B:b', 'A:3', 'A', 'B', 'A:4' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_preg_grep_1() {
		$return = Runtime::runSource( '
$array = array("foo", 5, 4.78, "bar", "7.89", "1.234foo");
print_r( preg_grep("/^(\d+)?\.\d+$/", $array) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 2 => '4.78', 4 => '7.89' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_pcre_preg_last_error_1() {
		$this->assertEquals(
				Runtime::runSource( '
preg_match("/(?:\D+|<\d+>)*[!?]/", "foobar foobar foobar");
if (preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) {
    print "Backtrack limit was exhausted!";
}' ),
				[ 'Backtrack limit was exhausted!' ]
			);
	}

	public function testRun_preg_match_all_1() {
		$this->assertEquals(
				Runtime::runSource( '
$out=[];
preg_match_all("|<[^>]+>(.*)</[^>]+>|U",
    "<b>example: </b><div align=left>this is a test</div>",
    $out, PREG_PATTERN_ORDER);
echo $out[0][0] . ", " . $out[0][1];
echo $out[1][0] . ", " . $out[1][1];' ),
				[ '<b>example: </b>, <div align=left>this is a test</div>', 'example: , this is a test' ]
			);
	}

	public function testRun_preg_match_all_2() {
		$this->assertEquals(
				Runtime::runSource( '
preg_match_all("|<[^>]+>(.*)</[^>]+>|U",
    "<b>example: </b><div align=\"left\">this is a test</div>",
    $out, PREG_SET_ORDER);
echo $out[0][0] . ", " . $out[0][1];
echo $out[1][0] . ", " . $out[1][1];' ),
				[ '<b>example: </b>, example: ', '<div align="left">this is a test</div>, this is a test' ]
			);
	}

	public function testRun_preg_match_1() {
		$this->assertEquals(
				Runtime::runSource( '
$matches=[];
// get host name from URL
preg_match("@^(?:http://)?([^/]+)@i",
    "http://www.php.net/index.html", $matches);
$host = $matches[1];

// get last two segments of host name
preg_match("/[^.]+\.[^.]+$/", $host, $matches);
echo "domain name is: {$matches[0]}";' ),
				[ 'domain name is: php.net' ]
				);
	}

	public function testRun_preg_match_2() {
		$this->assertEquals(
				Runtime::runSource( '
$str = "foobar: 2008";
preg_match("/(?P<name>\w+): (?P<digit>\d+)/", $str, $matches);
echo print_r($matches, true);' ),
				[ 'Array
(
    [0] => foobar: 2008
    [name] => foobar
    [1] => foobar
    [digit] => 2008
    [2] => 2008
)
' ]
				);
	}

	public function testRun_preg_quote_1() {
		$this->assertEquals(
				Runtime::runSource( '$keywords = "$40 for a g3/400"; $keywords = preg_quote($keywords, "/"); echo $keywords;' ),
				[ '\$40 for a g3\/400' ]
				);
	}

	public function testRun_preg_replace_2() {
		$this->assertEquals(
				Runtime::runSource( '
$string = "April 15, 2003";
$pattern = "/(\w+) (\d+), (\d+)/i";
$replacement = \'${1}1,$3\';
echo preg_replace($pattern, $replacement, $string);' ),
				[ 'April1,2003' ]
				);
	}

	public function testRun_preg_replace_3() {
		$this->assertEquals(
				Runtime::runSource( '
$string = "The quick brown fox jumped over the lazy dog.";
$patterns = array();
$patterns[0] = "/quick/";
$patterns[1] = "/brown/";
$patterns[2] = "/fox/";
$replacements = array();
$replacements[2] = "bear";
$replacements[1] = "black";
$replacements[0] = "slow";
echo preg_replace($patterns, $replacements, $string);' ),
				[ 'The bear black slow jumped over the lazy dog.' ]
				);
	}

	public function testRun_preg_split_1() {
		$this->assertEquals(
				Runtime::runSource( '$keywords = preg_split("/[\s,]+/", "hypertext language, programming"); echo print_r($keywords,true);' ),
				[ print_r( [ 'hypertext', 'language', 'programming' ], true ) ]
			);
	}

	public function testRun_preg_split_2() {
		$this->assertEquals(
				Runtime::runSource( '$str = "string";
$chars = preg_split("//", $str, -1, PREG_SPLIT_NO_EMPTY);
echo print_r($chars,true);' ),
				[ print_r( [ 's', 't', 'r', 'i', 'n', 'g' ], true ) ]
			);
	}

	public function testRun_preg_split_3() {
		$this->assertEquals(
				Runtime::runSource( '$str = "hypertext language programming";
$chars = preg_split("/ /", $str, -1, PREG_SPLIT_OFFSET_CAPTURE);
echo print_r($chars, true);' ),
				[ print_r( [ [ 'hypertext', 0 ], [ 'language', 10 ], [ 'programming', 19 ] ], true ) ]
			);
	}

}
