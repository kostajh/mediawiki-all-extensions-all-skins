<?php
namespace PhpTags;

class PhpTagsFunctions_Array_Test extends \PHPUnit\Framework\TestCase {

	public function testRun_constant_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo M_PI;' ),
				[ M_PI ]
				);
	}

	public function testRun_array_change_key_case_1() {
		$this->assertEquals(
				Runtime::runSource( '$input_array = array("FirSt" => 1, "SecOnd" => 4); $res = array_change_key_case($input_array, CASE_UPPER); echo $res["FIRST"], $res["SECOND"];' ),
				[ '1', '4' ]
				);
	}

	public function testRun_array_change_key_case_2() {
		$this->assertEquals(
				Runtime::runSource( '$input_array = array("FirSt" => 1, "SecOnd" => 4); $res = array_change_key_case($input_array); echo $res["first"], $res["second"];' ),
				[ '1', '4' ]
				);
	}

	public function testRun_array_chunk_1() {
		$return = Runtime::runSource( '$input_array = array("a", "b", "c", "d", "e"); print_r( array_chunk($input_array, 2) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0 => [ 'a','b' ], 1 => [ 'c','d' ], 2 => [ 'e' ] ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_chunk_2() {
		$return = Runtime::runSource( 'print_r( array_chunk($input_array, 2, true) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0 => [ 0 => 'a',1 => 'b' ], 1 => [ 2 => 'c', 3 => 'd' ], 2 => [ 4 => 'e' ] ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_combine_1() {
		$return = Runtime::runSource( '$a = array("green", "red", "yellow"); $b = array("avocado", "apple", "banana"); $c = array_combine($a, $b); print_r($c);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'green' => 'avocado', 'red' => 'apple', 'yellow' => 'banana' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_combine_2() {
		$return = Runtime::runSource( '$a = array(); $b = array(); $c = array_combine($a, $b); print_r($c);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_combine_3() {
		$return = Runtime::runSource( '$a = array("green", "qt"=>true, 1); $b = array("avocado", "apple", "banana"); $c = array_combine($a, $b); print_r($c);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'green' => 'avocado', '1' => 'banana' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_combine_exception_1() {
		$return = Runtime::runSource( '$a = array(); $b = array("avocado", "apple", "banana"); $c = array_combine($a, $b); print_r($c);', [ 'Test' ] );
		$this->assertEquals(
				'<span class="error">PhpTags Warning:  array_combine(): Both parameters should have an equal number of elements in Test on line 1</span><br />',
				(string)$return[0]
			);
	}

	public function testRun_array_combine_exception_2() {
		$return = Runtime::runSource( '$a = array("green", array(), "yellow"); $b = array("avocado", "apple", "banana"); $c = array_combine($a, $b); print_r($c);', [ 'Test' ] );
		$this->assertEquals(
				(string)new PhpTagsException( PhpTagsException::NOTICE_ARRAY_TO_STRING, null, 1, 'Test' ),
				(string)$return[0]
			);
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'green' => 'avocado', 'Array' => 'apple', 'yellow' => 'banana' ], true ) ),
				(string)$return[1]
			);
	}

	public function testRun_array_combine_exception_3() {
		$return = Runtime::runSource( '$a = array("green", new DateTime(), "yellow"); $b = array("avocado", "apple", "banana"); $c = array_combine($a, $b); print_r($c);', [ 'Test' ] );
		$this->assertEquals(
				(string)new PhpTagsException( PhpTagsException::FATAL_OBJECT_COULD_NOT_BE_CONVERTED, [ 'DateTime', 'string' ], 1, 'Test' ),
				(string)$return[0]
			);
	}

	public function testRun_array_count_values_1() {
		$return = Runtime::runSource( '$array = array(1, "hello", 1, "world", "hello"); print_r(array_count_values($array));' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 1 => 2, 'hello' => 2, 'world' => 1 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_count_values_exception_1() {
		$this->assertEquals(
				[
					'<span class="error">PhpTags Warning:  array_count_values(): Can only count STRING and INTEGER values! in Test on line 1</span><br />',
					'<span class="error">PhpTags Warning:  array_count_values(): Can only count STRING and INTEGER values! in Test on line 1</span><br />',
					(string)new outPrint( null, print_r( [ 6 => 3, 7 => 1 ], true ) ),
				],
				Runtime::runSource( '$f = array_count_values( array(6,6,6,7, new Datetime(), new DateTime()) ); print_r( $f );', [ 'Test' ] )
			);
	}

	public function testRun_array_diff_assoc_1() {
		$return = Runtime::runSource( '
$array1 = array("a" => "green", "b" => "brown", "c" => "blue", "red");
$array2 = array("a" => "green", "yellow", "red");
$result = array_diff_assoc($array1, $array2);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'b' => 'brown', 'c' => 'blue', 0 => 'red' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_diff_assoc_2() {
		$return = Runtime::runSource( '
$array3 = array("red");
$result = array_diff_assoc($array1, $array2, $array3);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'b' => 'brown', 'c' => 'blue' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_diff_key_1() {
		$return = Runtime::runSource( '
$array1 = array("blue"  => 1, "red" => 2, "green" => 3, "purple" => 4);
$array2 = array("green" => 5, "blue" => 6, "yellow" => 7, "cyan" => 8);
print_r(array_diff_key($array1, $array2));' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'red' => 2, 'purple' => 4 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_diff_1() {
		$return = Runtime::runSource( '
$array1 = array("a" => "green", "red", "blue", "red");
$array2 = array("b" => "green", "yellow", "red");
$result = array_diff($array1, $array2);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 1 => 'blue' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_fill_keys_1() {
		$return = Runtime::runSource( '
$keys = array("foo", 5, 10, "bar");
$a = array_fill_keys($keys, "banana");
print_r($a);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'foo' => 'banana',5 => 'banana',10 => 'banana','bar' => 'banana' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_fill_keys_2() {
		$return = Runtime::runSource( '
$keys = array("foo", 5, array(), "bar");
$a = array_fill_keys($keys, "banana");
print_r($a);', [ 'Test' ] );
		$this->assertEquals(
				(string)(string)new PhpTagsException( PhpTagsException::NOTICE_ARRAY_TO_STRING, null, 3, 'Test' ),
				(string)$return[0]
			);
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'foo' => 'banana',5 => 'banana','Array' => 'banana','bar' => 'banana' ], true ) ),
				(string)$return[1]
			);
	}

	public function testRun_array_fill_1() {
		$return = Runtime::runSource( '
$a = array_fill(5, 3, "banana");
print_r($a);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 5 => 'banana',6 => 'banana',7 => 'banana' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_fill_2() {
		$return = Runtime::runSource( '
$a = array_FILL(5, -3, "banana");
print_r($a);', [ 'Test' ] );
		$this->assertEquals(
				'<span class="error">PhpTags Warning:  array_fill(): Number of elements must be positive in Test on line 2</span><br />',
				(string)$return[0]
			);
	}

	public function testRun_array_flip_1() {
		$return = Runtime::runSource( '
$trans = array("a" => 1, "b" => 1, "c" => 2);
$trans = array_flip($trans);
print_r($trans);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 1 => 'b', 2 => 'c' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_flip_2() {
		$return = Runtime::runSource( '
$trans = array("a" => 1, "b" => array(), "c" => 2);
$trans = array_flip($trans);
print_r($trans);', [ 'Test' ] );
		$this->assertEquals(
				'<span class="error">PhpTags Warning:  array_flip(): Can only count STRING and INTEGER values! in Test on line 3</span><br />',
				(string)$return[0]
			);
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 1 => 'a', 2 => 'c' ], true ) ),
				(string)$return[1]
			);
	}

	public function testRun_array_intersect_assoc_1() {
		$return = Runtime::runSource( '
$array1 = array("a" => "green", "b" => "brown", "c" => "blue", "red");
$array2 = array("a" => "green", "b" => "yellow", "blue", "red");
$result_array = array_intersect_assoc($array1, $array2);
print_r($result_array);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'a' => 'green' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_intersect_key_1() {
		$return = Runtime::runSource( '
$array1 = array("blue"  => 1, "red"  => 2, "green"  => 3, "purple" => 4);
$array2 = array("green" => 5, "blue" => 6, "yellow" => 7, "cyan"   => 8);
print_r(array_intersect_key($array1, $array2));' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'blue' => 1,'green' => 3 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_intersect_1() {
		$return = Runtime::runSource( '
$array1 = array("a" => "green", "red", "blue");
$array2 = array("b" => "green", "yellow", "red");
$result = array_intersect($array1, $array2);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'a' => 'green', 0 => 'red' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_key_exists_1() {
		$return = Runtime::runSource( '
$search_array = array("first" => 1, "second" => 4);
if (array_key_exists("first", $search_array)) {
    echo "The \"first\" element is in the array";
}' );
		$this->assertEquals(
				$return[0],
				'The "first" element is in the array'
			);
	}

	public function testRun_array_keys_1() {
		$return = Runtime::runSource( '
$array = array(0 => 100, "color" => "red");
print_r(array_keys($array));' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0, 'color' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_keys_2() {
		$return = Runtime::runSource( '
$array = array("blue", "red", "green", "blue", "blue");
print_r(array_keys($array, "blue"));' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0, 3, 4 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_keys_3() {
		$return = Runtime::runSource( '
$array = array("color" => array("blue", "red", "green"),
               "size"  => array("small", "medium", "large"));
print_r(array_keys($array));' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'color', 'size' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_merge_recursive_1() {
		$return = Runtime::runSource( '
$ar1 = array("color" => array("favorite" => "red"), 5);
$ar2 = array(10, "color" => array("favorite" => "green", "blue"));
$result = array_merge_recursive($ar1, $ar2);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'color' => [ 'favorite' => [ 'red','green' ], 0 => 'blue' ],0 => 5,1 => 10 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_merge_1() {
		$return = Runtime::runSource( '
$beginning = "foo";
$end = array(1 => "bar");
$result = array_merge((array)$beginning, (array)$end);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'foo', 'bar' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_merge_2() {
		$return = Runtime::runSource( '
$array1 = array("color" => "red", 2, 4);
$array2 = array("a", "b", "color" => "green", "shape" => "trapezoid", 4);
$result = array_merge($array1, $array2);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'color' => 'green', 0 => 2, 1 => 4, 2 => 'a', 3 => 'b', 'shape' => 'trapezoid', 4 => 4, ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_merge_3() {
		$return = Runtime::runSource( '
$array1 = array();
$array2 = array(1 => "data");
$result = array_merge($array1, $array2);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'data' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_merge_4() {
		$return = Runtime::runSource( '
$array1 = array(0 => "zero_a", 2 => "two_a", 3 => "three_a");
$array2 = array(1 => "one_b", 3 => "three_b", 4 => "four_b");
$result = $array1 + $array2;
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0 => 'zero_a', 2 => 'two_a', 3 => 'three_a', 1 => 'one_b', 4 => 'four_b' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_multisort_1() {
		$return = Runtime::runSource( '
$ar1 = array(10, 100, 100, 0);
$ar2 = array(1, 3, 2, 4);
array_multisort($ar1, $ar2);

print_r($ar1);
print_r($ar2);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0,10,100,100 ], true ) ),
				(string)$return[0]
			);
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 4,1,2,3 ], true ) ),
				(string)$return[1]
			);
	}

	public function testRun_array_multisort_2() {
		$return = Runtime::runSource( '
$ar = array(
       array("10", 11, 100, 100, "a"),
       array(   1,  2, "2",   3,   1)
      );
array_multisort($ar[0], SORT_ASC, SORT_STRING,
                $ar[1], SORT_NUMERIC, SORT_DESC);
print_r($ar);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ [ '10',100,100,11,'a' ], [ 1,3,'2',2,1 ] ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_multisort_3() {
		$return = Runtime::runSource( '
$ar1 = array(10, 100, 100, 0);
$ar2 = array(1, 3, 2, 4);
$ar3 = array(-1, -3, -2, -4);
$ar4 = array(777, 7777, 77777, 777777);
array_multisort($ar1, $ar2, $ar3, $ar4);

print_r($ar1);
print_r($ar2);
print_r($ar3);
print_r($ar4);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0,10,100,100 ], true ) ),
				(string)$return[0]
			);
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 4,1,2,3 ], true ) ),
				(string)$return[1]
			);
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ -4,-1,-2,-3 ], true ) ),
				(string)$return[2]
			);
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 777777,777,77777,7777 ], true ) ),
				(string)$return[3]
			);
	}

	public function testRun_array_pad_1() {
		$return = Runtime::runSource( '
$input = array(12, 10, 9);
print_r( array_pad($input, 5, 0) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 12, 10, 9, 0, 0 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_pad_2() {
		$return = Runtime::runSource( 'print_r( array_pad($input, -7, -1) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ -1, -1, -1, -1, 12, 10, 9 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_pad_3() {
		$return = Runtime::runSource( 'print_r( array_pad($input, 2, "noop") );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 12, 10, 9 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_pop_1() {
		$return = Runtime::runSource( '
$stack = array("orange", "banana", "apple", "raspberry");
$fruit = array_pop($stack);
print_r($stack);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'orange', 'banana', 'apple' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_product_1() {
		$return = Runtime::runSource( '
$a = array(2, 4, 6, 8);
echo "product(a) = " . array_product($a);
echo "product(array()) = " . array_product(array());' );
		$this->assertEquals(
				$return,
				[ 'product(a) = 384', 'product(array()) = 1' ]
			);
	}

	public function testRun_array_push_1() {
		$return = Runtime::runSource( '
$stack = array("orange", "banana");
array_push($stack, "apple", "raspberry");
print_r($stack);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'orange', 'banana', 'apple', 'raspberry' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_rand_1() {
		$return = Runtime::runSource( '
$input = array("Neo", "Morpheus", "Trinity", "Cypher", "Tank");
$rand_keys = array_rand($input, 2);
echo $input[$rand_keys[0]] . "\n";
echo $input[$rand_keys[1]] . "\n";' );
		$this->assertCount( 2, $return );
	}

	public function testRun_array_rand_2() {
		$return = Runtime::runSource( '
$input = array("Neo", "Morpheus", "Trinity", "Cypher", "Tank");
$rand_keys = array_rand($input, 20);', [ 'Test' ] );
		$this->assertEquals(
				'<span class="error">PhpTags Warning:  array_rand(): Second argument has to be between 1 and the number of elements in the array in Test on line 3</span><br />',
				(string)$return[0]
			);
	}

	public function testRun_array_replace_recursive_1() {
		$return = Runtime::runSource( '
$base = array("citrus" => array( "orange") , "berries" => array("blackberry", "raspberry"), );
$replacements = array("citrus" => array("pineapple"), "berries" => array("blueberry"));

$basket = array_replace_recursive($base, $replacements);
print_r($basket);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'citrus' => [ 'pineapple' ],'berries' => [ 'blueberry','raspberry' ] ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_replace_recursive_2() {
		$return = Runtime::runSource( '
$base = array("citrus" => array("orange") , "berries" => array("blackberry", "raspberry"), "others" => "banana" );
$replacements = array("citrus" => "pineapple", "berries" => array("blueberry"), "others" => array("litchis"));
$replacements2 = array("citrus" => array("pineapple"), "berries" => array("blueberry"), "others" => "litchis");
$basket = array_replace_recursive($base, $replacements, $replacements2);
print_r($basket);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'citrus' => [ 'pineapple' ],'berries' => [ 'blueberry','raspberry' ],'others' => 'litchis' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_replace_1() {
		$return = Runtime::runSource( '
$base = array("orange", "banana", "apple", "raspberry");
$replacements = array(0 => "pineapple", 4 => "cherry");
$replacements2 = array(0 => "grape");
$basket = array_replace($base, $replacements, $replacements2);
print_r($basket);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'grape','banana','apple','raspberry','cherry' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_reverse_1() {
		$return = Runtime::runSource( '
$input  = array("php", 4.0, array("green", "red"));
$reversed = array_reverse($input);
print_r($reversed);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ [ 'green','red' ],'4','php' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_reverse_2() {
		$return = Runtime::runSource( '
$input  = array("php", 4.0, array("green", "red"));
$preserved = array_reverse($input, true);
print_r($preserved);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 2 => [ 'green','red' ],1 => '4',0 => 'php' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_search_1() {
		$return = Runtime::runSource( '
$array = array(0 => "blue", 1 => "red", 2 => "green", 3 => "red");
echo array_search("green", $array); // $key = 2;
echo array_search("red", $array);   // $key = 1;' );
		$this->assertEquals(
				$return,
				[ 2, 1 ]
			);
	}

	public function testRun_array_shift_1() {
		$return = Runtime::runSource( '
$stack = array("orange", "banana", "apple", "raspberry");
$fruit = array_shift($stack);
print_r($stack);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "banana", "apple", "raspberry" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_slice_1() {
		$return = Runtime::runSource( '
$input = array("a", "b", "c", "d", "e");
print_r( array_slice($input, 2) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "c", "d", "e" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_slice_2() {
		$return = Runtime::runSource( 'print_r( array_slice($input, -2, 1) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "d" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_slice_3() {
		$return = Runtime::runSource( 'print_r( array_slice($input, 0, 3) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "a", "b", "c" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_slice_4() {
		$return = Runtime::runSource( 'print_r( array_slice($input, 2, -1) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "c", "d" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_slice_5() {
		$return = Runtime::runSource( 'print_r( array_slice($input, 2, -1, true) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 2 => "c", 3 => "d" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_splice_1() {
		$return = Runtime::runSource( '
$input = array("red", "green", "blue", "yellow");
array_splice($input, 2);
print_r( $input );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "red", "green" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_splice_2() {
		$return = Runtime::runSource( '
$input = array("red", "green", "blue", "yellow");
array_splice($input, 1, -1);
print_r( $input );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "red", "yellow" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_splice_3() {
		$return = Runtime::runSource( '
$input = array("red", "green", "blue", "yellow");
array_splice($input, 1, count($input), "orange");
print_r( $input );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "red", "orange" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_splice_4() {
		$return = Runtime::runSource( '
$input = array("red", "green", "blue", "yellow");
array_splice($input, -1, 1, array("black", "maroon"));
print_r( $input );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "red", "green", "blue", "black", "maroon" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_splice_5() {
		$return = Runtime::runSource( '
$input = array("red", "green", "blue", "yellow");
array_splice($input, 3, 0, "purple");
print_r( $input );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "red", "green", "blue", "purple", "yellow" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_sum_1() {
		$return = Runtime::runSource( '
$a = array(2, 4, 6, 8);
echo "sum(a) = " . array_sum($a);' );
		$this->assertEquals(
				$return,
				[ 'sum(a) = 20' ]
			);
	}

	public function testRun_array_sum_2() {
		$return = Runtime::runSource( '
$b = array("a" => 1.2, "b" => 2.3, "c" => 3.4);
echo "sum(b) = " . array_sum($b);' );
		$this->assertEquals(
				$return,
				[ 'sum(b) = 6.9' ]
			);
	}

	public function testRun_array_unique_1() {
		$return = Runtime::runSource( '
$input = array("a" => "green", "red", "b" => "green", "blue", "red");
$result = array_unique($input);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'a' => 'green', 0 => 'red', 1 => 'blue' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_unique_2() {
		$return = Runtime::runSource( '
$input = array(4, "4", "3", 4, 3, "3");
$result = array_unique($input);
print_r($result);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0 => 4, 2 => '3' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_values_1() {
		$return = Runtime::runSource( '
$array = array("size" => "XL", "color" => "gold");
print_r(array_values($array));' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'XL', 'gold' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_array_unshift_1() {
		$return = Runtime::runSource( '
$queue = array("orange", "banana");
array_unshift($queue, "apple", "raspberry");
print_r($queue);' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "apple", "raspberry", "orange", "banana" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_arsort_1() {
		$return = Runtime::runSource( '
$fruits = array("d" => "lemon", "a" => "orange", "b" => "banana", "c" => "apple");
arsort($fruits);
print_r( $fruits );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "a" => "orange", "d" => "lemon", "b" => "banana", "c" => "apple" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_asort_1() {
		$return = Runtime::runSource( '
$fruits = array("d" => "lemon", "a" => "orange", "b" => "banana", "c" => "apple");
asort($fruits);
print_r( $fruits );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "c" => "apple", "b" => "banana", "d" => "lemon", "a" => "orange" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_count_1() {
		$this->assertEquals(
				Runtime::runSource( '$transport = array("foot", "bike", "car", "plane"); echo count($transport);' ),
				[ '4' ]
				);
	}

	public function testRun_current_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo current($transport);' ),
				[ 'foot' ]
				);
	}

	public function testRun_next_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo next($transport), next($transport);' ),
				[ 'bike', 'car' ]
				);
	}

	public function testRun_current_2() {
		$this->assertEquals(
				Runtime::runSource( 'echo current($transport);' ),
				[ 'car' ]
				);
	}

	public function testRun_each_1() {
		$this->assertEquals(
				Runtime::runSource( '$a = each( $transport); echo $a[0], $a[1], $a["key"], $a["value"];' ),
				[ '2', 'car', '2', 'car' ]
				);
	}

	public function testRun_end_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo end($transport);' ),
				[ 'plane' ]
				);
	}

	public function testRun_prev_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo prev($transport);' ),
				[ 'car' ]
				);
	}

	public function testRun_key_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo key($transport);' ),
				[ '2' ]
				);
	}

	public function testRun_reset_1() {
		$this->assertEquals(
				Runtime::runSource( 'echo reset($transport);' ),
				[ 'foot' ]
				);
	}

	public function testRun_key_2() {
		$this->assertEquals(
				Runtime::runSource( 'echo key($transport);' ),
				[ '0' ]
				);
	}

	public function testRun_each_2() {
		$this->assertEquals(
				Runtime::runSource( '
$fruit = array("a" => "apple", "b" => "banana", "c" => "cranberry");
reset($fruit);
list($key, $val) = each($fruit);
echo "$key => $val";' ),
				[ 'a => apple' ]
				);
	}

	public function testRun_echo_list_while_1() {
		$this->assertEquals(
				Runtime::runSource( '
$fruit = array("a" => "apple", "b" => "banana", "c" => "cranberry");
reset($fruit);
while ( list($key, $val) = each($fruit) ) {
    echo "$key => $val";
}' ),
				[ 'a => apple', 'b => banana', 'c => cranberry' ]
				);
	}

	public function testRun_echo_next_while_1() {
		$this->assertEquals(
				Runtime::runSource( '
	$array = array(
    "fruit1" => "apple",
    "fruit2" => "orange",
    "fruit3" => "grape",
    "fruit4" => "apple",
    "fruit5" => "apple");
// this cycle echoes all associative array
// key where value equals "apple"
while ($fruit_name = current($array)) {
    if ($fruit_name == "apple") {
        echo key($array)."<br />";
    }
    next($array);
}' ),
				[ 'fruit1<br />', 'fruit4<br />', 'fruit5<br />' ]
			);
	}

	public function testRun_echo_while_function_1() {
		$this->assertEquals(
				Runtime::runSource( '
$foo = [1,2];
while ( count($foo) < 4 ) array_push($foo, 8);
echo $foo == [1,2,8,8] ? "true" : false;' ),
				[ 'true' ]
				);
	}

	public function testRun_in_array_1() {
		$this->assertEquals(
				Runtime::runSource( '
$os = array("Mac", "NT", "Irix", "Linux");
if (in_array("Irix", $os)) {
    echo "Нашел Irix";
}
if (in_array("mac", $os)) {
    echo "Нашел mac";
}' ),
				[ 'Нашел Irix' ]
				);
	}

	public function testRun_in_array_2() {
		$this->assertEquals(
				Runtime::runSource( '
$a = array("1.10", 12.4, 1.13);

if (in_array("12.4", $a, true)) {
    echo "12.4 найдено со строгой проверкой";
}

if (in_array(1.13, $a, true)) {
    echo "1.13 найдено со строгой проверкой";
}' ),
				[ '1.13 найдено со строгой проверкой' ]
				);
	}

	public function testRun_krsort_1() {
		$return = Runtime::runSource( '
$fruits = array("d"=>"lemon", "a"=>"orange", "b"=>"banana", "c"=>"apple");
krsort($fruits);
print_r( $fruits );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "d" => "lemon", "c" => "apple", "b" => "banana", "a" => "orange" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_ksort_1() {
		$return = Runtime::runSource( '
$fruits = array("d"=>"lemon", "a"=>"orange", "b"=>"banana", "c"=>"apple");
ksort($fruits);
print_r( $fruits );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ "a" => "orange", "b" => "banana", "c" => "apple", "d" => "lemon" ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_natcasesort_1() {
		$return = Runtime::runSource( '
$array2 = array("IMG0.png", "img12.png", "img10.png", "img2.png", "img1.png", "IMG3.png");
natcasesort($array2);
print_r( $array2 );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0 => 'IMG0.png', 4 => 'img1.png', 3 => 'img2.png', 5 => 'IMG3.png', 2 => 'img10.png', 1 => 'img12.png' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_natsort_1() {
		$return = Runtime::runSource( '
$array2 = array("img12.png", "img10.png", "img2.png", "img1.png");
natsort($array2);
print_r( $array2 );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 3 => 'img1.png', 2 => 'img2.png', 1 => 'img10.png', 0 => 'img12.png' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_range_1() {
		$return = Runtime::runSource( 'print_r( range(0, 3) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0, 1, 2, 3 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_range_2() {
		$return = Runtime::runSource( 'print_r( range(0, 30, 10) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0, 10, 20, 30 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_range_3() {
		$return = Runtime::runSource( 'print_r( range(0, 30, -10) );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 0, 10, 20, 30 ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_range_4() {
		$return = Runtime::runSource( 'print_r( range(0, 30, 100) );', [ 'Test' ] );
		$this->assertEquals(
				'<span class="error">PhpTags Warning:  range(): step exceeds the specified range in Test on line 1</span><br />',
				(string)$return[0]
			);
	}

	public function testRun_rsort_1() {
		$return = Runtime::runSource( '
$fruits = array("lemon", "orange", "banana", "apple");
rsort($fruits);
print_r( $fruits );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'orange', 'lemon', 'banana', 'apple' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_shuffle_1() {
		$return = Runtime::runSource( '
$numbers = range(1, 20);
echo shuffle($numbers) === true ? "true" : "false";' );
		$this->assertEquals(
				[ 'true' ],
				$return
			);
	}

	public function testRun_sort_1() {
		$return = Runtime::runSource( '
$fruits = array("lemon", "orange", "banana", "apple");
sort($fruits);
print_r( $fruits );' );
		$this->assertEquals(
				(string)new outPrint( null, print_r( [ 'apple', 'banana', 'lemon', 'orange' ], true ) ),
				(string)$return[0]
			);
	}

	public function testRun_echo_if_else_simple_function_1() {
		$this->assertEquals(
				Runtime::runSource( '$foo = [1,2,3]; if ( true ) array_pop($foo); else array_push($foo, 4); echo  $foo == [1,2] ? "true" : "false";' ),
				[ 'true' ]
				);
	}

	public function testRun_echo_if_else_simple_function_2() {
		$this->assertEquals(
				Runtime::runSource( '$foo = [1,2,3]; if ( false ) array_pop($foo); else array_push($foo, 4); echo  $foo == [1,2,3,4] ? "true" : "false";' ),
				[ 'true' ]
				);
	}

	public function testRun_echo_if_else_simple_function_3() {
		$this->assertEquals(
				Runtime::runSource( '$foo = [1,2,3]; if ( true ) array_pop($foo); else array_push($foo, 4); echo  $foo == [1,2] ? "true" : "false"; echo " always!";' ),
				[ 'true', ' always!' ]
				);
	}

	public function testRun_echo_if_else_simple_function_4() {
		$this->assertEquals(
				Runtime::runSource( '$foo = [1,2,3]; if ( false ) array_pop($foo); else array_push($foo, 4); echo  $foo == [1,2,3,4] ? "true" : "false"; echo " always!";' ),
				[ 'true', ' always!' ]
				);
	}

	public function testRun_echo_if_else_simple_variable_1() {
		$this->assertEquals(
				Runtime::runSource( 'if ( true ) $foo="true"; else $foo="false"; echo  $foo;' ),
				[ 'true' ]
				);
	}

	public function testRun_echo_if_else_simple_variable_2() {
		$this->assertEquals(
				Runtime::runSource( 'if ( false ) $foo="true"; else $foo="false"; echo  $foo;' ),
				[ 'false' ]
				);
	}

	public function testRun_echo_array_chunk_1() {
		$this->assertEquals(
				[
					(string)new outPrint( null, print_r( array_chunk( [ "a", "b", "c", "d", "e" ], 2 ), true ), true ),
				],
				Runtime::runSource( '$input_array = array("a", "b", "c", "d", "e"); print_r( array_chunk($input_array, 2) );', [ 'Test' ] )
			);
	}

	public function testRun_echo_array_chunk_exception_1() {
		$this->assertEquals(
				[
					'<span class="error">PhpTags Warning:  array_chunk(): Size parameter expected to be greater than 0 in Test on line 1</span><br />',
					new outPrint( true, print_r( null, true ) ),
				],
				Runtime::runSource( '$input_array = array("a", "b", "c", "d", "e"); print_r( array_chunk($input_array, 0) );', [ 'Test' ] )
			);
	}

	public function testRun_echo_array_chunk_exception_2() {
		$this->assertEquals(
				[
					new outPrint( true, print_r( null, true ) ),
				],
				Runtime::runSource( '$input_array = array("a", "b", "c", "d", "e"); @ print_r( array_chunk($input_array, 0) );', [ 'Test' ] )
			);
	}

	public function testRun_echo_array_chunk_exception_3() {
		$this->assertEquals(
				[
					'<span class="error">PhpTags Warning:  array_chunk() expects parameter 1 to be array, NULL given in Test on line 1</span><br />',
					new outPrint( true, print_r( null, true ) ),
				],
				Runtime::runSource( 'print_r( array_chunk( @ $itIsUndefined, 2 ) );', [ 'Test' ] )
			);
	}

	public function testRun_echo_array_chunk_exception_4() {
		$this->assertEquals(
				[
					new outPrint( true, print_r( null, true ) ),
				],
				Runtime::runSource( 'print_r( @ array_chunk( $itIsUndefined, 2) );', [ 'Test' ] )
			);
	}

	public function testRun_echo_array_chunk_exception_5() {
		$this->assertEquals(
				[
					new outPrint( true, print_r( null, true ) ),
				],
				Runtime::runSource( '@ print_r( array_chunk( $itIsUndefined, 2) );', [ 'Test' ] )
			);
	}

	public function testRun_echo_array_chunk_exception_6() {
		$this->assertEquals(
				[
					new outPrint( true, print_r( null, true ) ),
					(string)new PhpTagsException( PhpTagsException::NOTICE_UNDEFINED_VARIABLE, 'itIsUndefined', 1, 'Test' ),
					null,
				],
				Runtime::runSource( '@ print_r( array_chunk( $itIsUndefined, 2) ); echo $itIsUndefined;', [ 'Test' ] )
			);
	}

}
