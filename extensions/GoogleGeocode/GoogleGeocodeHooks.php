<?php
/*
 * Copyright (C) 2015 Ike Hecht <tosfos@yahoo.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

class GoogleGeocodeHooks {

	/**
	 * Sets up the googlegeocode parser hook
	 *
	 * @param Parser &$parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'googlegeocode', 'GoogleGeocodeHooks::geocode' );
	}

	/**
	 * Defines the geocode parser function
	 *
	 * @global string $wgGoogleGeocodeAPIKey
	 * @global string $wgGoogleGeocodeDelimiter
	 * @global integer $wgGoogleGeocodeExpiry
	 * @param Parser &$parser
	 * @param string|null $addressSent The address requested. Can also be a place name, etc.
	 * @param string|null $resultComponentSent The component requested
	 * @param string $resultPath The path type to use for the result. Can be set to: long_name, short_name or types.
	 * @return string
	 */
	public static function geocode( Parser &$parser, $addressSent = null, $resultComponentSent = null,
		$resultPath = 'long_name' ) {
		global $wgGoogleGeocodeAPIKey, $wgGoogleGeocodeDelimiter, $wgGoogleGeocodeExpiry;

		static $address = '';
		if ( $addressSent ) {
			// Allow users to omit the first parameter. Use the address sent by last call.
			$address = $addressSent;
		}
		static $resultComponent = '';
		if ( $resultComponentSent ) {
			// Allow users to omit the second parameter. Use the result component sent by last call.
			$resultComponent = $resultComponentSent;
		}

		$result = GoogleGeocodeCache::getCache( $address );
		if ( !$result ) {
			$result = self::getFromGoogleAPI( $address, $wgGoogleGeocodeAPIKey );
			if ( empty( $result ) ) {
				wfDebugLog( 'GoogleGeocode', __METHOD__ . ": Unable to locate a place with address $address" );
				return '';
			}
			GoogleGeocodeCache::setCache( $address, $result, $wgGoogleGeocodeExpiry );
		}

		$finalResult = self::getFinalResult( $result, $resultPath );

		if ( !isset( $finalResult[$resultComponent] ) ) {
			wfDebugLog( 'GoogleGeocode',
				__METHOD__ . ": Place found with address '$address', but unable to locate requested "
				. "component: $resultComponent" );
			return '';
		}

		if ( is_array( $finalResult[$resultComponent] ) ) {
			// Probably only here for the "types" component
			return str_replace( "_", " ",
				implode( $wgGoogleGeocodeDelimiter, $finalResult[$resultComponent] ) );
		}

		return $finalResult[$resultComponent];
	}

	/**
	 * Get the first result set from Google that matches the address
	 *
	 * @param string $address
	 * @param string|null $apiKey
	 * @return string|null Returns null if the address is not found
	 */
	public static function getFromGoogleAPI( $address, $apiKey = null ) {
		$geocoder = new GoogleMapsGeocoder( $address );

		/** @todo add lanaguage and region? */
		if ( $apiKey ) {
			$geocoder->setApiKey( $apiKey );
		}

		if ( isset( $geocoder->geocode()['results'][0] ) ) {
			return $geocoder->geocode()['results'][0];
		}

		return null;
	}

	/**
	 * Parse the result and flatten it to a one-dimensional array
	 *
	 * @param array $result
	 * @param string $resultPath
	 * @return array
	 */
	private static function getFinalResult( array $result, $resultPath ) {
		$finalResult = self::getAddressComponents( $result, $resultPath );
		unset( $result['address_components'] );

		$finalResult += self::getGeometryComponents( $result );
		/** @todo We are losing some of the geometry information here */
		unset( $result['geometry'] );

		// Now just add everything else in.
		$finalResult += $result;

		return $finalResult;
	}

	/**
	 * Get a flat array with all address components. The array key is the component name and the
	 * value is the component value.
	 *
	 * @param array $result
	 * @param string $resultPath
	 * @return array
	 */
	private static function getAddressComponents( array $result, $resultPath = 'long_name' ) {
		$addressComponents = [];
		foreach ( $result['address_components'] as $component ) {
			foreach ( $component['types'] as $type ) {
				$addressComponents[$type] = $component[$resultPath];
			}
		}
		return $addressComponents;
	}

	/**
	 * Get an array from this result, with the lat and lng set.
	 *
	 * @param array $result
	 * @return array
	 */
	private static function getGeometryComponents( array $result ) {
		$geometryComponents = [];
		$coordinates = $result['geometry']['location'];
		$geometryComponents['lat'] = $coordinates['lat'];
		$geometryComponents['lng'] = $coordinates['lng'];
		return $geometryComponents;
	}
}
