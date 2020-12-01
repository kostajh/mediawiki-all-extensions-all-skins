<?php

class GooglePlacesHooks {

	/**
	 * @param Parser &$parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'googleplaces', 'GooglePlacesHooks::googlePlaces' );
		$parser->setFunctionHook( 'googleplacestype', 'GooglePlacesHooks::googlePlacesType' );
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param Parser &$parser
	 * @param string|null $placeIDSent
	 * @param string|null $resultPathSent
	 * @return string
	 */
	public static function googlePlaces( Parser &$parser, $placeIDSent = null, $resultPathSent = null ) {
		static $placeID = '';
		if ( $placeIDSent ) {
			// Allow users to omit the first parameter. Use the place ID sent by last call.
			$placeID = $placeIDSent;
		}
		static $resultPath = '';
		if ( $resultPathSent ) {
			// Allow users to omit the second parameter. Use the result path sent by last call.
			$resultPath = $resultPathSent;
		}

		$details = self::getDetails( $placeID );

		if ( empty( $details['result'] ) || !is_array( $details['result'] ) ) {
			return '';
		}

		$output = self::getArrayElementFromPath( $details['result'], $resultPath );

		if ( is_array( $output ) ) {
			$output = self::htmlEscapeImplode( ';', $output );
		}

		self::insertPoweredBy();
		$output .= self::getTOSRequiredHTML( $details );

		return $output;
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param Parser &$parser
	 * @param string|null $placeIDSent
	 * @param string|null $typeSent
	 * @param string|null $field
	 * @return string
	 */
	public static function googlePlacesType( Parser &$parser, $placeIDSent = null, $typeSent = null,
		$field = null ) {
		static $placeID = '';
		if ( $placeIDSent ) {
			// Allow users to omit the first parameter. Use the place ID sent by last call.
			$placeID = $placeIDSent;
		}
		static $type = '';
		if ( $typeSent ) {
			// Allow users to omit the second parameter. Use the type sent by last call.
			$type = $typeSent;
		}

		$details = self::getDetails( $placeID );

		if ( empty( $details['result'] ) || !is_array( $details['result'] ) ) {
			return '';
		}

		$output = self::getArrayElementFromType(
				$details['result']['address_components'], $type, $field );

		self::insertPoweredBy();
		$output .= self::getTOSRequiredHTML( $details );

		return $output;
	}

	/**
	 * @global string $wgGooglePlacesAPIKey
	 * @global int $wgGooglePlacesExpiry
	 * @global array $wgFooterIcons
	 * @param string $placeID
	 * @return array
	 */
	private static function getDetails( $placeID ) {
		global $wgGooglePlacesAPIKey, $wgGooglePlacesExpiry;

		$details = GooglePlacesCache::getCache( $wgGooglePlacesAPIKey, $placeID );

		if ( !$details ) {
			$details = self::getDetailsFromGoogleAPI( $wgGooglePlacesAPIKey, $placeID );

			if ( !empty( $details['errors'] ) ) {
				return self::getAnyErrors( $details['errors'] );
			}

			GooglePlacesCache::setCache( $wgGooglePlacesAPIKey, $placeID, $details, $wgGooglePlacesExpiry );
		}
		return $details;
	}

	/**
	 * @param string $googlePlacesAPIKey
	 * @param string $placeID
	 * @return array
	 */
	private static function getDetailsFromGoogleAPI( $googlePlacesAPIKey, $placeID ) {
		$googlePlaces = new Mills\GooglePlaces\googlePlaces( $googlePlacesAPIKey );
		/** @todo Provide user option? */
		$googlePlaces->setCurloptSslVerifypeer( false );
		$googlePlaces->setPlaceId( $placeID );
		/**
		 * @todo Google has its own language codes, but maybe there's some way to convert from the
		 * content language to Google's codes.
		 * $googlePlaces->setLanguage($language);
		 */
		return $googlePlaces->details();
	}

	/**
	 * @param array $errors
	 * @return string HTML error message
	 */
	private static function getAnyErrors( array $errors ) {
		/** @todo throw something */
		$error = 'Error!';
		// Not sure, but maybe there can be more than one errormessage here
		foreach ( $errors as $errorMessage ) {
			$error .= ' ' . $errorMessage;
		}
		return Html::element(
				'strong', [ 'class' => [ 'error', 'googleplaces-error' ] ], $error );
	}

	/**
	 * @return array
	 */
	private static function getFooterIcon() {
		return [
			'id' => 'powered-by-google',
			'src' => '//maps.gstatic.com/mapfiles/api-3/images/powered-by-google-on-white2.png',
			'alt' => 'Powered by Google',
			'width' => 104,
			'height' => 16,
			'style' => "background-color: white;"
		];
	}

	/**
	 * Get an array element from a (potentially) muti-dimensional array based on a string path,
	 * with each array element separated by a delimiter
	 *
	 * Example: To access $array['stuff']['vehicles']['car'], the path would be 'stuff;vehicles;car'
	 *  (assuming the default delimiter)
	 *
	 * @param array $array
	 * @param string $path
	 * @param string $delimiter
	 * @return string
	 */
	private static function getArrayElementFromPath( array $array, $path, $delimiter = ';' ) {
		# http://stackoverflow.com/a/2951721
		$paths = explode( $delimiter, $path );
		$item = $array;
		foreach ( $paths as $index ) {
			if ( isset( $item[$index] ) ) {
				$item = $item[$index];
			} else {
				return '';
			}
		}
		return $item;
	}

	/**
	 * Recursively look for an array that has this type in it.
	 *
	 * @param array $result
	 * @param string $type
	 * @param string $field
	 * @return string
	 */
	private static function getArrayElementFromType( $result, $type, $field ) {
		$element = self::getElementWithMatchingType( $result, $type, $field );
		if ( $element !== '' ) {
			return $element;
		} else {
			foreach ( $result as $value ) {
				if ( is_array( $value ) ) {
					$element = self::getArrayElementFromType( $value, $type, $field );
					if ( $element !== '' ) {
						return $element;
					}
				}
			}
		}
		return '';
	}

	/**
	 * Search an array for an element with key 'types' whose value is an array containing the value
	 * in $types. If found, return the value of sibling key $element.
	 * If $element is unset, return the first value of that sibling array.
	 *
	 * @param array $array
	 * @param array $types
	 * @param string $element
	 * @return string
	 */
	private static function getElementWithMatchingType( array $array, $types, $element = '' ) {
		if ( isset( $array['types'] ) && in_array( $types, $array['types'] ) ) {
			if ( $element === '' || !isset( $array[$element] ) ) {
				$return = array_shift( $array );
				return $return;
			} else {
				return $array[$element];
			}
		} else {
			return '';
		}
	}

	/**
	 * @param array $result
	 * @return string
	 */
	private static function getTOSRequiredHTML( array $result ) {
		// Required by TOS - Not sure this ever happens but if it does, it will mess stuff up.
		// Maybe show elsewhere on the page.
		if ( !empty( $result['html'] ) ) {
			return $result['html'];
		} else {
			return '';
		}
	}

	/**
	 * @global array $wgFooterIcons
	 */
	private static function insertPoweredBy() {
		global $wgFooterIcons;
		// Required by TOS
		/** @todo Needs to show icon even for skins that only show text here or that show nothing */
		/** @todo If a Google map is being shown on the page, this is not required */
		if ( !isset( $wgFooterIcons['poweredby']['googleplaces'] ) ) {
			$wgFooterIcons['poweredby']['googleplaces'] = self::getFooterIcon();
		}
	}

	/**
	 * Implodes an array into a string, but also escapes instances of $glue into its HTML
	 * ampersand representation.
	 *
	 * @param string $glue
	 * @param array $pieces
	 * @return string
	 */
	private static function htmlEscapeImplode( $glue, array $pieces ) {
		foreach ( $pieces as &$piece ) {
			$piece = str_replace( $glue, '&#' . ord( $glue ) . ';', $piece );
		}
		return implode( $glue, $pieces );
	}
}
