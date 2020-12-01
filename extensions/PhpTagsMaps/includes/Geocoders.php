<?php
namespace MultiMaps;

/**
 *
 *
 * @file Geocoders.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 * @license GPL-2.0-or-later
 */

class Geocoders {

	public static function getCoordinates( $address, $service, $params = null ) {
		switch ( $service ) {
			case 'google':
				return self::getCoordinatesUseGoogle( $address );
				break;
			case 'yandex':
				return self::getCoordinatesUseYandex( $address );
				break;
			case 'leaflet':
				return self::getCoordinatesUseMapquestNominatim( $address, $params );
				break;
		}
		return false;
	}

	private static function performRequest( $url, $urlArgs ) {
		return \Http::get( $url . wfArrayToCgi( $urlArgs ) );
	}

	private static function getCoordinatesUseGoogle( $address ) {
		$return = false;

		$urlArgs = [
			'sensor' => 'false',
			'address' => $address,
			];
		$response = self::performRequest( 'https://maps.googleapis.com/maps/api/geocode/json?', $urlArgs );

		if ( $response !== false ) {
			$data = \FormatJson::decode( $response );
			if ( $data !== null ) {
				if ( $data->status == 'OK' ) {
					$geometry = $data->results[0]->geometry;
					$location = $geometry->location;
					$lat = $location->lat;
					$lon = $location->lng;
					if ( $lat !== null && $lon !== null ) {
						$return = [ 'lat' => $lat, 'lon' => $lon ];
						$bounds = $geometry->bounds;
						if ( $bounds !== null ) {
							$bounds_ne = new Point( $bounds->northeast->lat, $bounds->northeast->lng );
							$bounds_sw = new Point( $bounds->southwest->lat, $bounds->southwest->lng );
							if ( $bounds_ne->isValid() && $bounds_sw->isValid() ) {
								$b = new Bounds( [ $bounds_ne, $bounds_sw ] );
								$return['bounds'] = $b;
							}
						}
					}
				}
			}
		}
		return $return;
	}

	private static function getCoordinatesUseYandex( $address ) {
		$return = false;

		$urlArgs = [
			'format' => 'json',
			'results' => '1',
			'geocode' => $address,
			];
		$response = self::performRequest( 'https://geocode-maps.yandex.ru/1.x/?', $urlArgs );

		if ( $response !== false ) {
			$data = \FormatJson::decode( $response );
			if ( $data !== null ) {
				$geoObjectCollection = $data->response->GeoObjectCollection;
				if ( $geoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0 ) {
					$geoObject = $geoObjectCollection->featureMember[0]->GeoObject;
					list( $lon, $lat ) = explode( ' ', $geoObject->Point->pos );
					$point = new Point( $lat, $lon );
					if ( $point->isValid() ) {
						$return = $point->pos;
						$envelope = $geoObject->boundedBy->Envelope;
						if ( $envelope !== null ) {
							list( $lon, $lat ) = explode( ' ', $envelope->upperCorner );
							$bounds_ne = new Point( $lat, $lon );
							list( $lon, $lat ) = explode( ' ', $envelope->lowerCorner );
							$bounds_sw = new Point( $lat, $lon );
							if ( $bounds_ne->isValid() && $bounds_sw->isValid() ) {
								$b = new Bounds( [ $bounds_ne, $bounds_sw ] );
								$return['bounds'] = $b;
							}
						}
					}
				}
			}
		}
		return $return;
	}

	public static function getCoordinatesUseMapquestNominatim( $address, $params ) {
		$return = false;
		$param_polygon = ( isset( $params['polygon'] ) && $params['polygon'] === true ) ? true : false;

		$urlArgs = [
			'format' => 'json',
			'addressdetails' => '0',
			'limit' => 1,
			'q' => $address,
			];
		if ( $param_polygon ) {
			$urlArgs['polygon'] = '1';
		}
		$response = self::performRequest( 'http://open.mapquestapi.com/nominatim/v1/search.php?', $urlArgs );

		if ( $response !== false ) {
			$data = \FormatJson::decode( $response );
			if ( isset( $data[0] ) ) {
				$data = $data[0];
				$lat = $data->lat;
				$lon = $data->lon;
				if ( $lat !== null && $lon !== null ) {
					$return = [ 'lat' => $lat, 'lon' => $lon ];
					$bounds = $data->boundingbox;
					if ( $bounds !== null ) {
						$bounds_ne = new Point( $bounds[1], $bounds[3] );
						$bounds_sw = new Point( $bounds[0], $bounds[2] );
						if ( $bounds_ne->isValid() && $bounds_sw->isValid() ) {
							$b = new Bounds( [ $bounds_ne, $bounds_sw ] );
							$return['bounds'] = $b;
						}
					}
					if ( $param_polygon ) {
						$polygonpoints = $data->polygonpoints;
						if ( count( $polygonpoints ) > 1 ) {
							$points = [];
							foreach ( $polygonpoints as $value ) {
								$p = new Point( $value[1], $value[0] );
								if ( $p->isValid() ) {
									$points[] = $p;
								}
							}
							if ( count( $points ) > 1 ) {
								$return['polygon'] = $points;
							}
						}
					}
				}
			}
		}
		return $return;
	}

}
