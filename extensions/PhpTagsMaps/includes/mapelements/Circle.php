<?php
namespace MultiMaps;

/**
 * Circle class for collection of map elements
 *
 * @file Circle.php
 * @ingroup Circle
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 * @property-read array $radiuses Radiuses of circles
 */
class Circle extends Polygon {

	/**
	 * Array radiuses of circles
	 * @var array
	 */
	protected $radiuses = [];

	/**
	 * Returns element name
	 * return string Element name
	 */
	public function getElementName() {
		return 'Circle'; // TODO i18n?
	}

	/**
	 * Filling property 'coordinates'
	 * @global string $egMultiMaps_CoordinatesSeparator
	 * @param string $coordinates
	 * @param string|null $service Name of map service
	 * @return bool
	 */
	protected function parseCoordinates( $coordinates, $service = null ) {
		global $egMultiMaps_CoordinatesSeparator;

		$array = explode( $egMultiMaps_CoordinatesSeparator, $coordinates );

		if ( count( $array ) == 2 ) {
			$point = new Point();
			if ( $point->parse( $array[0], $service ) ) {
				if ( is_numeric( $array[1] ) ) {
					$this->coordinates[] = $point;
					$this->radiuses[] = (float)$array[1];
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-unable-parse-radius', $array[1] )->escaped();
					return false;
				}
			} else {
				$this->errormessages[] = \wfMessage( 'multimaps-unable-parse-coordinates', $array[0] )->escaped();
				return false;
			}
		} elseif ( count( $array ) == 1 ) {
			$point = new Point();
			if ( $point->parse( $array[0], $service ) ) {
				$bounds = $point->bounds;
				if ( $bounds ) {
					$this->coordinates[] = $bounds->center;
					$this->radiuses[] = $bounds->diagonal / 2;
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-circle-radius-not-defined' )->escaped();
					return false;
				}
			} else {
				$this->errormessages[] = \wfMessage( 'multimaps-unable-parse-coordinates', $array[0] )->escaped();
				return false;
			}
		} else {
			$this->errormessages[] = \wfMessage( 'multimaps-circle-wrong-number-parameters', count( $array ) )->escaped();
			return false;
		}
		return true;
	}

	/**
	 * Initializes the object again, and makes it invalid
	 */
	public function reset() {
		parent::reset();
		$this->radiuses = [];
	}

	/**
	 * Returns an array of data
	 * @return array
	 */
	public function getData() {
		return array_merge(
				[ 'radius' => $this->radiuses ],
				parent::getData()
				);
	}

	public function getProperty( $name ) {
		switch ( $name ) {
			case 'radiuses':
				return $this->radiuses;
				break;
			default:
				return parent::getProperty( $name );
				break;
		}
	}

}
