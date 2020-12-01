<?php

namespace MultiMaps;

class BoundsTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var Bounds
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() : void {
		$this->object = new Bounds;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() : void {
	}

	/**
	 * @covers MultiMaps\Bounds::getCenter
	 * @covers MultiMaps\Bounds::getData
	 * @covers MultiMaps\Bounds::extend
	 */
	public function testGetCenterAndExtend() {
		$this->assertFalse( $this->object->getCenter() );
		$this->assertNull( $this->object->getData() );

		$point = new Point( 10, 2 );
		$this->object->extend( $point );
		$this->assertEquals( $this->object->getCenter(), $point );

		$this->object->extend( [ new Point( 20, 1 ) ] );
		$this->assertEquals( $this->object->getCenter(), new Point( 15, 1.5 ) );

		$this->assertEquals(
			$this->object->getData(),
			[
				'ne' => [ 'lat' => 20, 'lon' => 2 ],
				'sw' => [ 'lat' => 10, 'lon' => 1 ],
			]
		);

		$this->assertEquals(
			"{$this->object->diagonal}",
			"1116519.1690062"
		);

		$pointWithBounds = new Point();
		$bounds1 = new Bounds( [ new Point( 40, 40 ), new Point( 30, 30 ) ] );
		$pointWithBounds->bounds = $bounds1;
		$this->object->extend( $pointWithBounds );
		$this->assertEquals(
			$this->object->getData(),
			[
				'ne' => [ 'lat' => 40, 'lon' => 40 ],
				'sw' => [ 'lat' => 10, 'lon' => 1 ],
			]
		);

		$bounds2 = new Bounds( [ new Point( -40, 0 ), new Point( 0, -30 ) ] );
		$pointWithBounds->bounds = $bounds2;
		$this->object->extend( $pointWithBounds );
		$this->assertEquals(
			$this->object->getData(),
			[
				'ne' => [ 'lat' => 40, 'lon' => 40 ],
				'sw' => [ 'lat' => -40, 'lon' => -30 ],
			]
		);
	}

	/**
	 * @covers MultiMaps\Bounds::isValid
	 * @covers MultiMaps\Bounds::extend
	 */
	public function testIsValid() {
		$this->assertFalse( $this->object->isValid(), false );

		$this->object->extend( [ new Point( 14, 41 ) ] );

		$this->assertTrue( $this->object->isValid() );
	}

	/**
	 * @covers MultiMaps\Bounds::__get
	 */
	public function test__get() {
		$point = new Point( 123, 456 );
		$this->object->extend( [ $point ] );

		$this->assertEquals( $this->object->ne, $this->object->sw );
		$this->assertEquals( $this->object->ne, $this->object->center );
		$this->assertEquals( $this->object->ne, $point );

		$this->assertNull( $this->object->tralala );
	}

}
