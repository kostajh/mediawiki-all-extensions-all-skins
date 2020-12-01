<?php
namespace MultiMaps;

/**
 * Bounds class for determine the boundaries of the map elements
 *
 * @file Bounds.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 * @property-read Point $ne North East point
 * @property-read Point $sw South West point
 * @property-read Point $center Center point of bounds
 * @property-read float $diagonal Diagonal of bounds
 */
class Bounds {

	/**
	 * North East Point
	 * @var Point
	 */
	protected $northEast = false;

	/**
	 * South West Point
	 * @var Point
	 */
	protected $southWest = false;

	function __construct( $coordinates = null ) {
		if ( $coordinates !== null ) {
			$this->extend( $coordinates );
		}
	}

	/**
	 * Extend bounds
	 * @param array $coordinates Array of Point objects
	 */
	public function extend( $coordinates ) {
		if ( $coordinates instanceof Point ) {
			$coordinates = [ $coordinates ];
		}
		foreach ( $coordinates as $point ) {
			$bounds = $point->bounds;
			if ( !$this->isValid() ) {
				if ( $bounds ) {
					$this->northEast = $bounds->ne;
					$this->southWest = $bounds->sw;
				} else {
					$this->northEast = new Point( $point->lat, $point->lon );
					$this->southWest = new Point( $point->lat, $point->lon );
				}
			} else {
				if ( $bounds != false ) {
					if ( $bounds->sw->lat < $this->southWest->lat ) {
						$this->southWest->lat = $bounds->sw->lat;
					} elseif ( $bounds->ne->lat > $this->northEast->lat ) {
						$this->northEast->lat = $bounds->ne->lat;
					}

					if ( $bounds->sw->lon < $this->southWest->lon ) {
						$this->southWest->lon = $bounds->sw->lon;
					} elseif ( $bounds->ne->lon > $this->northEast->lon ) {
						$this->northEast->lon = $bounds->ne->lon;
					}
				} else {
					if ( $point->lat < $this->southWest->lat ) {
						$this->southWest->lat = $point->lat;
					} elseif ( $point->lat > $this->northEast->lat ) {
						$this->northEast->lat = $point->lat;
					}

					if ( $point->lon < $this->southWest->lon ) {
						$this->southWest->lon = $point->lon;
					} elseif ( $point->lon > $this->northEast->lon ) {
						$this->northEast->lon = $point->lon;
					}
				}
			}
		}
	}

	/**
	 * Returns center of bounds
	 * @return bool|\MultiMaps\Point
	 */
	public function getCenter() {
		if ( !$this->isValid() ) {
			return false;
		}

		return new \MultiMaps\Point(
			( $this->southWest->lat + $this->northEast->lat ) / 2,
			( $this->southWest->lon + $this->northEast->lon ) / 2
		);
	}

	/**
	 * Checks if the object is valid
	 * @return bool
	 */
	public function isValid() {
		return ( $this->northEast !== false && $this->southWest !== false );
	}

	/**
	 * Returns an array of data
	 * @return array
	 */
	public function getData() {
		if ( $this->isValid() ) {
			return [
				'ne' => $this->northEast->getData(),
				'sw' => $this->southWest->getData(),
			];
		}
	}

	public function __get( $name ) {
		$name = strtolower( $name );
		switch ( $name ) {
			case 'ne':
				return $this->northEast;
			case 'sw':
				return $this->southWest;
			case 'center':
				return $this->getCenter();
			case 'diagonal':
				return GeoCoordinate::getDistanceInMeters( $this->northEast->lat, $this->northEast->lon, $this->southWest->lat, $this->southWest->lon );
		}
		return null;
	}

}
