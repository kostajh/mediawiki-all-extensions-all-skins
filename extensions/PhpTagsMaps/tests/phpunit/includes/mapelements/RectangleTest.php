<?php

namespace MultiMaps;

class RectangleTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var Rectangle
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() : void {
		$this->object = new Rectangle;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() : void {
	}

	/**
	 * @covers MultiMaps\Rectangle::getElementName
	 */
	public function testGetElementName() {
		$this->assertEquals(
			$this->object->getElementName(),
			'Rectangle'
		);
	}

	/**
	 * @covers MultiMaps\Rectangle::getElementName
	 */
	public function testParseCoordinates() {
		$this->assertEquals(
			$this->object->getElementName(),
			'Rectangle'
		);
	}
}
