<?php
namespace MultiMaps;

/**
 * Rectangle class for collection of map elements
 *
 * @file Rectangle.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 */
class Rectangle extends Polygon {

	/**
	 * Returns element name
	 * return string Element name
	 */
	public function getElementName() {
		return 'Rectangle'; // TODO i18n?
	}

	/**
	 * Parse coordinates for rectangle
	 *
	 * @assert ('10,10:20,20') === true
	 * @assert ('10,10:20,20:30,30') === false
	 * @assert ('10,10:20,20:30') === false
	 * @assert ('10,10:20') === false
	 * @assert ('10,10:') === false
	 * @assert ('10,10') === false
	 * @assert ('10') === false
	 *
	 * @global type $egMultiMaps_CoordinatesSeparator
	 * @param string $coordinates
	 * @param string|null $service Name of map service
	 * @return bool
	 */
	protected function parseCoordinates( $coordinates, $service = null ) {
		global $egMultiMaps_CoordinatesSeparator;

		$array = explode( $egMultiMaps_CoordinatesSeparator, $coordinates );

		if ( count( $array ) == 2 ) {
			$point1 = new Point();
			$point2 = new Point();
			if ( $point1->parse( $array[0], $service ) ) {
				if ( $point2->parse( $array[1], $service ) ) {
					$this->coordinates[] = $point1;
					$this->coordinates[] = $point2;
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-unable-parse-coordinates', $array[1] )->escaped();
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
					$this->coordinates[] = $bounds->ne;
					$this->coordinates[] = $bounds->sw;
				} else {
					$this->errormessages[] = \wfMessage( 'multimaps-square-wrong-number-points', count( $array ) )->escaped();
					return false;
				}
			} else {
				$this->errormessages[] = \wfMessage( 'multimaps-unable-parse-coordinates', $array[0] )->escaped();
				return false;
			}
		} else {
			$this->errormessages[] = \wfMessage( 'multimaps-square-wrong-number-points', count( $array ) )->escaped();
			return false;
		}
		return true;
	}

}
