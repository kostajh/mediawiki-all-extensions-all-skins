<?php
namespace MultiMaps;

/**
 * Determines the point of the map elements
 *
 * @file Bounds.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 * @property-read float $lat Latitude coordinate
 * @property-read float $lon Longitude coordinate
 * @property Bounds $bounds Bounds associated with the point, used in the geocoding
 * @property-read array $pos Position as array( 'lat' => lat, 'lon' => lon)
 */
class Point {
	/**
	 * Latitude
	 * @var float
	 */
	protected $latitude = false;

	/**
	 * Longitude
	 * @var float
	 */
	protected $longitude = false;

	/**
	 * Bounds associated with the point, used in the geocoding
	 * @var Bounds
	 */
	protected $bounds = false;

	/**
	 * Constructor
	 * @param float $lat
	 * @param float $lon
	 */
	public function __construct( $lat = false, $lon = false ) {
		if ( is_numeric( $lat ) && is_numeric( $lon ) ) {
			$this->latitude = (float)$lat;
			$this->longitude = (float)$lon;
		}
	}

	public function __get( $name ) {
		switch ( $name ) {
			case 'lat':
				return $this->latitude;
				break;
			case 'lon':
				return $this->longitude;
				break;
			case 'bounds':
				return $this->bounds;
				break;
			case 'pos':
				if ( $this->isValid() ) {
					return [ 'lat' => $this->latitude, 'lon' => $this->longitude ];
				}
				break;
		}
		return null;
	}

	public function __set( $name, $value ) {
		switch ( $name ) {
			case 'lat':
				if ( is_numeric( $value ) ) {
					$this->latitude = (float)$value;
				} else {
					$this->latitude = false;
				}
				break;
			case 'lon':
				if ( is_numeric( $value ) ) {
					$this->longitude = (float)$value;
				} else {
					$this->longitude = false;
				}
				break;
			case 'bounds':
				if ( ( $value instanceof Bounds && $value->isValid() ) || $value === false ) {
					$this->bounds = $value;
				}
				break;
		}
	}

	/**
	 * Parse geographic coordinates
	 * @param string $string geographic coordinates
	 * @param string|null $service Name of map service
	 * @return bool
	 */
	public function parse( $string, $service = null ) {
		$coord = GeoCoordinate::getLatLonFromString( $string );
		if ( is_array( $coord ) === false ) {
			$coord = Geocoders::getCoordinates( $string, $service );
			if ( is_array( $coord ) === false ) {
				$this->latitude = false;
				$this->longitude = false;
				return false;
			}
			if ( isset( $coord['bounds'] ) ) {
				$this->bounds = $coord['bounds'];
			}
		}
		$this->lat = $coord['lat'];
		$this->lon = $coord['lon'];
		return true;
	}

	/**
	 * Move this point at a given distance in meters
	 * @param float $nord To the north (meters)
	 * @param float $east To the East (meters)
	 */
	public function move( $nord, $east ) {
		GeoCoordinate::moveCoordinatesInMeters( $this->latitude, $this->longitude, $nord, $east );
	}

	/**
	 * Checks if the object is valid
	 * @return bool
	 */
	public function isValid() {
		return ( $this->latitude !== false && $this->longitude !== false );
	}

	/**
	 * Returns an array of data
	 * @return array
	 */
	public function getData() {
		if ( $this->isValid() ) {
			return [ 'lat' => $this->latitude, 'lon' => $this->longitude ];
		}
		return null;
	}

}
