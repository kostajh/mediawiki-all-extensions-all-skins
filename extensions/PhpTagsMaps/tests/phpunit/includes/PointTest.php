<?php

namespace MultiMaps;

class PointTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var Point
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() : void {
		$this->object = new Point( 123, 321 );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() : void {
	}

	/**
	 * @covers MultiMaps\Point::__set
	 * @covers MultiMaps\Point::getData
	 * @covers MultiMaps\Point::isValid
	 * @covers MultiMaps\Point::__get
	 * @covers MultiMaps\Point::__set
	 */
	public function test__set__get() {
		$this->assertEquals( $this->object->lat, 123 );
		$this->assertEquals( $this->object->lon, 321 );

		$this->object->lat = "789";
		$this->object->lon = 987;

		$this->assertEquals( $this->object->lat, 789 );
		$this->assertEquals( $this->object->lon, 987 );
		$this->assertTrue( $this->object->isValid() );

		$this->object->hello = "wassup!!!";
		$this->assertNull( $this->object->hello );
		$this->assertTrue( $this->object->isValid() );

		$this->object->lat = "hello";
		$this->assertFalse( $this->object->lat );
		$this->assertFalse( $this->object->isValid() );

		$this->object->lat = "789";
		$this->assertTrue( $this->object->isValid() );

		$this->object->lon = "hello";
		$this->assertFalse( $this->object->lon );
		$this->assertFalse( $this->object->isValid() );

		$this->assertNull( $this->object->getData() );
	}

	/**
	 * @covers MultiMaps\Point::move
	 * @covers MultiMaps\Point::getData
	 */
	public function testMove() {
		$this->object->move( 12345, -67890 );

		$this->assertEquals(
			$this->object->getData(),
			[ 'lat' => 123.11108317216, 'lon' => 322.11643133104 ]
		);
	}

	/**
	 * @covers MultiMaps\Point::isValid
	 */
	public function testIsValidNew() {
		$this->assertTrue( $this->object->isValid() );

		$this->object = new Point();

		$this->assertFalse( $this->object->isValid() );
	}

	/**
	 * @covers MultiMaps\Point::isValid
	 * @covers \MultiMaps\Point::parse
	 */
	public function testIsValidParse() {
		$this->assertTrue( $this->object->isValid() );

		$this->object->parse( "123456" );

		$this->assertFalse( $this->object->isValid() );

		$this->object->parse( "123,456" );

		$this->assertTrue( $this->object->isValid() );
	}

	/**
	 * @covers MultiMaps\Point::getData
	 */
	public function testGetData() {
		$this->assertEquals(
			$this->object->getData(),
			[ 'lat' => 123, 'lon' => 321 ]
		);
	}

}
