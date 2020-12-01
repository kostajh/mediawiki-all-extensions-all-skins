<?php

// namespace MultiMaps;

class GeoCoordinateTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var GeoCoordinate
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() : void {
		// $this->object = new GeoCoordinate;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() : void {
	}

	/**
	 * Generated from @assert ("55.755831°, 37.617673°") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( "55.755831°, 37.617673°" )
		);
	}

	/**
	 * Generated from @assert ("N55.755831°, E37.617673°") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString2() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( "N55.755831°, E37.617673°" )
		);
	}

	/**
	 * Generated from @assert ("55°45.34986'N, 37°37.06038'E") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString3() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( "55°45.34986'N, 37°37.06038'E" )
		);
	}

	/**
	 * Generated from @assert ("55°45'20.9916\"N, 37°37'3.6228\"E") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString4() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( "55°45'20.9916\"N, 37°37'3.6228\"E" )
		);
	}

	/**
	 * Generated from @assert (" 37°37'3.6228\"E, 55°45'20.9916\" ") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString5() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( " 37°37'3.6228\"E, 55°45'20.9916\" " )
		);
	}

	/**
	 * Generated from @assert (" 37°37'3.6228\", 55°45'20.9916\" N ") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString6() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( " 37°37'3.6228\", 55°45'20.9916\" N " )
		);
	}

	/**
	 * Generated from @assert ("55°45'20.9916\"N, 37°37'3.6228\"") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString7() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( "55°45'20.9916\"N, 37°37'3.6228\"" )
		);
	}

	/**
	 * Generated from @assert ("55°45'20.9916\", E37°37'3.6228\"") == array("lat" => 55.755831, "lon"=> 37.617673).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString8() {
		$this->assertEquals(
			[ "lat" => 55.755831, "lon" => 37.617673 ], MultiMaps\GeoCoordinate::getLatLonFromString( "55°45'20.9916\", E37°37'3.6228\"" )
		);
	}

	/**
	 * Generated from @assert (" 10  , - 10 ") == array("lat" => 10, "lon"=> -10).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString9() {
		$this->assertEquals(
			[ "lat" => 10, "lon" => -10 ], MultiMaps\GeoCoordinate::getLatLonFromString( " 10  , - 10 " )
		);
	}

	/**
	 * Generated from @assert ("-10°,s10 °  ") == array("lat" => -10, "lon"=> -10).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString10() {
		$this->assertEquals(
			[ "lat" => -10, "lon" => -10 ], MultiMaps\GeoCoordinate::getLatLonFromString( "-10°,s10 °  " )
		);
	}

	/**
	 * Generated from @assert ("s10.123456°,  -1.123°   ") == array("lat" => -10.123456, "lon"=> -1.123).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString11() {
		$this->assertEquals(
			[ "lat" => -10.123456, "lon" => -1.123 ], MultiMaps\GeoCoordinate::getLatLonFromString( "s10.123456°,  -1.123°   " )
		);
	}

	/**
	 * Generated from @assert ("10.123456° N,  1.123° W  ") == array("lat" => 10.123456, "lon"=> -1.123).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString12() {
		$this->assertEquals(
			[ "lat" => 10.123456, "lon" => -1.123 ], MultiMaps\GeoCoordinate::getLatLonFromString( "10.123456° N,  1.123° W  " )
		);
	}

	/**
	 * Generated from @assert ("10.12° W,  1.123° s  ") == array("lat" => -1.123, "lon"=> -10.12).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString13() {
		$this->assertEquals(
			[ "lat" => -1.123, "lon" => -10.12 ], MultiMaps\GeoCoordinate::getLatLonFromString( "10.12° W,  1.123° s  " )
		);
	}

	/**
	 * Generated from @assert ("10.12° w,  1.123°") == array("lat" => 1.123, "lon"=> -10.12).
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString14() {
		$this->assertEquals(
			[ "lat" => 1.123, "lon" => -10.12 ], MultiMaps\GeoCoordinate::getLatLonFromString( "10.12° w,  1.123°" )
		);
	}

	/**
	 * Generated from @assert ("Z10.12°,  1.123°") === false.
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString15() {
		$this->assertFalse(
			MultiMaps\GeoCoordinate::getLatLonFromString( "Z10.12°,  1.123°" )
		);
	}

	/**
	 * Generated from @assert ("10.12°, X1.123°") === false.
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString16() {
		$this->assertFalse(
			MultiMaps\GeoCoordinate::getLatLonFromString( "10.12°, X1.123°" )
		);
	}

	/**
	 * Generated from @assert ("Tralala") === false.
	 *
	 * @covers MultiMaps\GeoCoordinate::getLatLonFromString
	 */
	public function testGetLatLonFromString17() {
		$this->assertFalse(
			MultiMaps\GeoCoordinate::getLatLonFromString( "Tralala" )
		);
	}

}
