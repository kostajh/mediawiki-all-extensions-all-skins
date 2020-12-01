<?php

namespace MultiMaps;

class PolygonTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var Polygon
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() : void {
		$this->object = new Polygon;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() : void {
	}

	/**
	 * @covers MultiMaps\Polygon::getElementName
	 */
	public function testGetElementName() {
		// Remove the following lines when you implement this test.
		$this->assertEquals(
			$this->object->getElementName(),
			'Polygon'
		);
	}

	public function testSetProperty() {
		$this->assertNull( $this->object->fill );

		$this->object->fillcolor = '#FFFFFF';
		$this->assertTrue( $this->object->fill );
		$this->object->fill = false;
		$this->assertNull( $this->object->fillcolor );

		$this->object->fillopacity = '1';
		$this->assertTrue( $this->object->fill );
		$this->object->fill = 'no';
		$this->assertNull( $this->object->fillopacity );

		$illegalfillvalue = 'ha-ha-ha';
		$this->assertFalse( $this->object->setProperty( 'fill', $illegalfillvalue ) );

		$this->assertEquals(
			$this->object->getErrorMessages(),
			[ \wfMessage( 'multimaps-element-illegal-value', 'fill', $illegalfillvalue, '"' . implode( '", "', $this->object->getPropertyValidValues( 'fill' ) ) . '"' )->escaped() ]
		);
	}

	/**
	 * @covers MultiMaps\Polygon::getPropertyValidValues
	 */
	public function testGetPropertyValidValues() {
		$this->assertNull( Polygon::getPropertyValidValues( 'poiuygtfcvbnmnbgvfd' ) );
	}

}
