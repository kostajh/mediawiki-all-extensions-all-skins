<?php
namespace MultiMaps;

/**
 * This class provides functions for working with geographic coordinates
 *
 * @file GeoCoordinate.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 */
class GeoCoordinate {

	/**
	 * WGS 84
	 * The length of the Earth's equator, meters
	 */
	const EQUATOR_LENGTH = 40075017;

	/**
	 * WGS 84
	 * The length of the Earth's meredian, meters
	 */
	const MEREDIAN_LENGTH = 20003930;

	/**
	 * Converts the string with geographic coordinates to a numeric representation
	 *
	 * @assert ("55.755831°, 37.617673°") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert ("N55.755831°, E37.617673°") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert ("55°45.34986'N, 37°37.06038'E") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert ("55°45'20.9916\"N, 37°37'3.6228\"E") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert (" 37°37'3.6228\"E, 55°45'20.9916\" ") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert (" 37°37'3.6228\", 55°45'20.9916\" N ") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert ("55°45'20.9916\"N, 37°37'3.6228\"") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert ("55°45'20.9916\", E37°37'3.6228\"") == array("lat" => 55.755831, "lon"=> 37.617673)
	 * @assert (" 10  , - 10 ") == array("lat" => 10, "lon"=> -10)
	 * @assert ("-10°,s10 °  ") == array("lat" => -10, "lon"=> -10)
	 * @assert ("s10.123456°,  -1.123°   ") == array("lat" => -10.123456, "lon"=> -1.123)
	 * @assert ("10.123456° N,  1.123° W  ") == array("lat" => 10.123456, "lon"=> -1.123)
	 * @assert ("10.12° W,  1.123° s  ") == array("lat" => -1.123, "lon"=> -10.12)
	 * @assert ("10.12° w,  1.123°") == array("lat" => 1.123, "lon"=> -10.12)
	 * @assert ("Z10.12°,  1.123°") === false
	 * @assert ("10.12°, X1.123°") === false
	 * @assert ("Tralala") === false
	 *
	 * @global string $egMultiMaps_OptionsSeparator
	 * @param string $coords
	 * @return array array( 'lat'=>(float), 'lon'=>(float) ) or FALSE on error
	 */
	public static function getLatLonFromString( $coords ) {
		global $egMultiMaps_OptionsSeparator;

		$matches = [];
		$subject = preg_replace( '/\s+/', '', $coords );

		$array = explode( $egMultiMaps_OptionsSeparator, $subject );
		if ( count( $array ) == 2 ) {
			$lat = false;
			$lon = false;
			$doubt = false;
			$matches = [];
			if ( preg_match( '/^[NSWE]|[NSWE]$/i', $array[0], $matches ) ) {
				$string = preg_replace( '/[NSWE]/i', '', $array[0], 1 );
				switch ( strtoupper( $matches[0] ) ) {
					case 'N':
						$lat = self::getFloatFromString( $string );
						break;
					case 'S':
						$lat = -1 * self::getFloatFromString( $string );
						break;
					case 'E':
						$lon = self::getFloatFromString( $string );
						break;
					case 'W':
						$lon = -1 * self::getFloatFromString( $string );
						break;
				}
			} else {
				$doubt = true;
				$lat = self::getFloatFromString( $array[0] );
			}

			if ( $lat === false && $lon === false ) {
				return false;
			}

			if ( preg_match( '/^[NSWE]|[NSWE]$/i', $array[1], $matches ) ) {
				$string = preg_replace( '/[NSWE]/i', '', $array[1], 1 );
				switch ( strtoupper( $matches[0] ) ) {
					case 'N':
						if ( !$lat || $doubt ) {
							if ( $doubt ) {
								$lon = $lat;
							}
							$lat = self::getFloatFromString( $string );
						}
						break;
					case 'S':
						if ( !$lat || $doubt ) {
							if ( $doubt ) {
								$lon = $lat;
							}
							$lat = -1 * self::getFloatFromString( $string );
						}
						break;
					case 'E':
						if ( !$lon ) {
							$lon = self::getFloatFromString( $string );
						}
						break;
					case 'W':
						if ( !$lon ) {
							$lon = -1 * self::getFloatFromString( $string );
						}
						break;
				}
			} else {
				if ( $lat !== false ) {
					$lon = self::getFloatFromString( $array[1] );
				} else {
					$lat = self::getFloatFromString( $array[1] );
				}
			}

			if ( $lat !== false && $lon !== false ) {
				return [ 'lat' => $lat, 'lon' => $lon ];
			}
		}
		return false;
	}

	/**
	 * Converts the string with coordinate to a float format
	 *
	 * @assert ('55.755831') == 55.755831
	 * @assert ('55.755831°') == 55.755831
	 * @assert ('55°45.34986\'') == 55.755831
	 * @assert ('55°45\'20.9916\"') == 55.755831
	 * @assert ('N55°45\'20.9916\"') === false
	 *
	 * @param string $string
	 * @return float
	 */
	private static function getFloatFromString( $string ) {
		$matches = [];
		// String contain float
		if ( preg_match( '/^((?:-)?\d{1,3}(?:\.\d{1,20})?)(?:°)?$/', $string, $matches ) ) {
			return (float)$matches[1];
		}
		// String contain DMS
		if ( preg_match( '/^((?:-)?\d{1,3})°(\d{1,2}(?:\.\d{1,20})?)(?:\′|\')(?:(\d{1,2}(?:\.\d{1,20})?)(?:″|"))?$/', $string, $matches ) ) {
			return (float)( abs( $matches[1] ) == $matches[1] ? 1 : -1 ) * ( abs( $matches[1] ) + ( isset( $matches[2] ) ? $matches[2] / 60 : 0 ) + ( isset( $matches[3] ) ? $matches[3] / 3600 : 0 ) );
		}
		return false;
	}

	/**
	 * Sets the geographical coordinates in new position according to a given offset on the north and east, in meters
	 * @param float &$lat Latitude of coordinates
	 * @param float &$lon Longitude of coordinates
	 * @param float $nord To the north (meters)
	 * @param float $east To the East (meters)
	 */
	public static function moveCoordinatesInMeters( &$lat, &$lon, $nord, $east ) {
		$lat += ( $nord / self::MEREDIAN_LENGTH ) * 180;
		$lon += ( $east / ( self::EQUATOR_LENGTH * cos( M_PI / 180 * $lat ) ) ) * 360;
	}

	/**
	 * Returns the distance between two geographical points
	 * @param float $lat1 Latitude geographical point 1
	 * @param float $lon1 Longitude geographical point 1
	 * @param float $lat2 Latitude geographical point 2
	 * @param float $lon2 Longitude geographical point 2
	 * @return float Distance, in meters
	 */
	public static function getDistanceInMeters( $lat1, $lon1, $lat2, $lon2 ) {
		$lat = abs( $lat1 - $lat2 );
		$lon = abs( $lon1 - $lon2 );
		$distance_lat = ( $lat / 180 ) * self::MEREDIAN_LENGTH;
		$distance_lon = ( $lon / 360 ) * self::EQUATOR_LENGTH * cos( M_PI / 180 * abs( ( $lat1 + $lat2 ) / 2 ) );
		return sqrt( pow( $distance_lat, 2 ) + pow( $distance_lon, 2 ) );
	}

}
