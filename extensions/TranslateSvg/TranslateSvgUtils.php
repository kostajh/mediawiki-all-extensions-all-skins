<?php
/**
 * This file contains classes with static helper functions for other classes.
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2012 Harry Burt
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * An essentially random collection of helper functions, similar to GlobalFunctions.php.
 */
class TranslateSvgUtils {
	/**
	 * Function used to determine if a message includes a property string
	 *
	 * @param string $translation Message which may or may not include a property string
	 * @return bool
	 */
	public static function hasPropertyString( $translation ) {
		global $wgTranslateSvgTemplateName;
		return ( preg_match( '/\{\{' . $wgTranslateSvgTemplateName . '.*\}\}[\s]*$/', $translation ) === 1 );
	}

	/**
	 * Function that returns only the property string from a given translation
	 *
	 * @param string $translation Translation which includes a property string
	 * @return string Just the property string, empty string on failure
	 */
	public static function extractPropertyString( $translation ) {
		global $wgTranslateSvgTemplateName;
		preg_match( '/\{\{' . $wgTranslateSvgTemplateName . '.*\}\}[\s]*$/', $translation, $matches );
		return isset( $matches[0] ) ? trim( $matches[0] ) : '';
	}

	/**
	 * Function that returns a translation minus its property string
	 * Useful for the TranslateFormatMessageBeforeTable hook
	 *
	 * @param string $translation Translation which may or may not include a property string
	 * @return string Message without property string
	 */
	public static function stripPropertyString( $translation ) {
		global $wgTranslateSvgTemplateName;
		return preg_replace( '/\{\{' . $wgTranslateSvgTemplateName . '.*\}\}[\s]*$/', '', $translation );
	}

	/**
	 * Simple function that quickly whittles down whether a title is that of
	 * a file description page for an SVG file
	 *
	 * @param Title $title The MediaWiki Title object in question
	 * @return bool True if it is, false if it isn't.
	 */
	public static function isSVGFilePage( Title $title ) {
		if ( $title->getNamespace() === NS_FILE ) {
			$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title );
			return ( $file && $file->getMimeType() === 'image/svg+xml' );
		}

		// Not a file description page
		return false;
	}

	/**
	 * Maps from the kind of <parameter name,value> combination used in
	 * a property string to the kind of <attribute name, value> combination
	 * used in an SVG file. Also validates to prevent arbitary input.
	 *
	 * @see self::mapFromAttribute()
	 * @param string $name Parameter name (e.g. color, bold)
	 * @param string $value Parameter value (e.g. black, yes)
	 * @return array Numerical array, [0] = attribute name, [1] = value
	 */
	public static function mapToAttribute( $name, $value ) {
		global $wgTranslateSvgDefaultProperties, $wgTranslateSvgOptionalProperties;
		$name = trim( $name );
		$value = trim( $value );

		$supported = array_merge(
			array_keys( $wgTranslateSvgDefaultProperties ),
			$wgTranslateSvgOptionalProperties
		);

		if ( !in_array( $name, $supported ) ) {
			// Quietly drop: injection attempt?
			return [ false, false ];
		}
		switch ( $name ) {
			case 'bold':
				$name = 'font-weight';
				$value = ( $value === 'yes' ) ? 'bold' : 'normal';
				break;
			case 'italic':
				$name = 'font-style';
				$value = ( $value === 'yes' ) ? 'italic' : 'normal';
				break;
			case 'underline':
				$name = 'text-decoration';
				$value = ( $value === 'yes' ) ? 'underline' : 'normal';
				break;
			case 'color':
				$name = 'fill';
				break;
		}
		if ( $value === '' ) {
			$value = false;
		}
		return [ $name, $value ];
	}

	/**
	 * Maps from the kind of <attribute name, value> combination used in
	 * an SVG file to the kind of <parameter name,value> combination used in
	 * a property string. Also validates to prevent arbitary input.
	 *
	 * @see self::mapToAttribute()
	 * @param string $name Attribute name (e.g. fill, font-weight)
	 * @param string $value Attribute value (e.g. black, bold)
	 * @return array Numerical array, [0] = parameter name, [1] = parameter value
	 */
	public static function mapFromAttribute( $name, $value ) {
		global $wgTranslateSvgOptionalProperties;
		$name = trim( $name );
		$value = trim( $value );

		$supported = array_merge(
			[
				'x', 'y', 'font-size', 'font-weight', 'font-style',
				'text-decoration', 'font-family', 'fill', 'style',
				'systemLanguage'
			],
			$wgTranslateSvgOptionalProperties
		);
		if ( !in_array( $name, $supported ) ) {
			// Not editable, so not suitable for extraction
			return [ false, false ];
		}

		switch ( $name ) {
			case 'font-weight':
				$name = 'bold';
				$value = ( $value === 'bold' ) ? 'yes' : 'no';
				break;
			case 'font-style':
				$name = 'italic';
				$value = ( $value === 'italic' ) ? 'yes' : 'no';
				break;
			case 'text-decoration':
				$name = 'underline';
				$value = ( $value === 'underline' ) ? 'yes' : 'no';
				break;
			case 'font-family':
				$map = [ 'Sans' => 'sans-serif' ];
				$value = $map[$value] ?? $value;
				break;
			case 'fill':
				$name = 'color';
				break;
		}
		if ( $value == '' ) {
			// Drop empty attributes altogether
			$value = false;
		}
		return [ $name, $value ];
	}

	/**
	 * One of several functions used to convert between TranslateSvg's
	 * three main formats for handling data (nodes, translations and arrays).
	 * This one converts between the translation and array formats.
	 *
	 * @param string $translation Translation to convert e.g. Blah{{Properties|foo=bar}}
	 * @return array An associative array of properties, including 'text'
	 */
	public static function translationToArray( $translation ) {
		// Start with text
		$array = [ 'text' => self::stripPropertyString( $translation ) ];

		// Build array from property string
		$propertyString = self::extractPropertyString( $translation );
		preg_match_all( '/\| *([a-z-]+) *= *([^|}]*)/', $propertyString, $parameters );
		$count = count( $parameters[0] );
		for ( $i = 0; $i < $count; $i++ ) {
			list( $attrName, $attrValue ) = self::mapToAttribute(
				$parameters[1][$i], $parameters[2][$i]
			);
			if ( $attrName !== false && $attrValue !== false && $attrValue !== 'other' ) {
				$array[$attrName] = $attrValue;
			}
		}
		if ( isset( $array['units'] ) ) {
			// Tack units onto font-size
			if ( isset( $array['font-size'] ) ) {
				$array['font-size'] .= $array['units'];
			}
			unset( $array['units'] );
		}
		return $array;
	}

	/**
	 * One of several functions used to convert between TranslateSvg's
	 * three main formats for handling data (nodes, translations and arrays).
	 * This one converts between the array and translation formats.
	 *
	 * @param array $array An associative array of properties, inc 'text'
	 * @return string Translation e.g. Blah{{Properties|foo=bar}}
	 */
	public static function arrayToTranslation( $array ) {
		global $wgTranslateSvgDefaultProperties, $wgTranslateSvgTemplateName;

		// Start with text
		$translation = $array['text'];
		unset( $array['text'] );

		// Fill $properties from defaults, array
		$properties = $wgTranslateSvgDefaultProperties;
		foreach ( $array as $attrName => $attrValue ) {
			list( $attrName, $attrValue ) = self::mapFromAttribute( $attrName, $attrValue );
			if ( $attrValue !== false ) {
				$properties[$attrName] = $attrValue;
			}
		}
		if ( isset( $properties['font-size'] ) ) {
			// Split font-size into font-size, units
			if ( preg_match( '/^([0-9]+)(.*)$/', $properties['font-size'], $matches ) ) {
				$properties['font-size'] = $matches[1];
				$properties['units'] = ( strlen( $matches[2] ) > 0 ) ? $matches[2] : 'px';
			}
		}

		// Build translation from properties
		$translation .= '{{' . $wgTranslateSvgTemplateName;
		foreach ( $properties as $attrName => $attrValue ) {
			$translation .= "|$attrName=$attrValue";
		}
		$translation .= '}}';
		return $translation;
	}

	/**
	 * Convert an OS locale (en_GB) to an internal language code (en-gb).
	 *
	 * @param string $os
	 * @return mixed
	 */
	public static function osToLangCode( $os ) {
		return str_replace( '_', '-', strtolower( $os ) );
	}

	/**
	 * Convert an internal language code (en-gb) to an OS locale (en_GB)
	 *
	 * @see self::osToLangCode
	 * @param string $langCode
	 * @return string
	 */
	public static function langCodeToOs( $langCode ) {
		if ( strpos( $langCode, '-' ) === false ) {
			// No territory specified, so no change to make (fr => fr)
			return $langCode;
		}
		list( $prefix, $suffix ) = explode( '-', $langCode, 2 );
		return $prefix . '_' . strtoupper( $suffix );
	}

	/**
	 * Implement our own wrapper around Language::fetchLanguageName, providing a more sensible
	 * fallback chain and our own interpretation of the "fallback" language code.
	 *
	 * @param string $langCode Language code (e.g. en-gb, fr)
	 * @param string $fallbackLanguage Code of the language for which the "fallback" magic word is equivalent
	 * @return string The autonym of the language with that code (English, français, Nederlands)
	 */
	public static function fetchLanguageName( $langCode, $fallbackLanguage ) {
		$langCode = ( $langCode === 'fallback' ) ? $fallbackLanguage : $langCode;
		$langName = Language::fetchLanguageName( $langCode );
		if ( $langName == '' ) {
			// Try searching for prefix only instead
			preg_match( '/^([a-z]+)/', $langCode, $matches );
			$langName = Language::fetchLanguageName( $matches[0] );
		}
		if ( $langName == '' ) {
			// Okay, seems the best we can do is return the language code
			$langName = $langCode;
		}
		return $langName;
	}
}
