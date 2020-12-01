<?php

namespace MediaWiki\WikispeechSpeechDataCollector;

use InvalidArgumentException;

/**
 * UUID helper methods.
 *
 * @package MediaWiki\WikispeechSpeechDataCollector
 * @since 0.1.0
 */
class UUID {

	/**
	 * V4, pseudo-random UUID.
	 *
	 * @see UUID::v4HexFactory()
	 * @return string
	 */
	public static function v4BytesFactory() {
		return self::hexToBytes( self::v4HexFactory() );
	}

	/**
	 * Return a UUID (version 4) using random bytes
	 * Note that version 4 follows the format:
	 *     xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
	 * where y is one of: [8, 9, A, B]
	 *
	 * We use (random_bytes(1) & 0x0F) | 0x40 to force
	 * the first character of hex value to always be 4
	 * in the appropriate position.
	 *
	 * For 4: http://3v4l.org/q2JN9
	 * For Y: http://3v4l.org/EsGSU
	 * For the whole shebang: https://3v4l.org/LNgJb
	 *
	 * https://stackoverflow.com/a/31460273/2224584
	 * https://paragonie.com/b/JvICXzh_jhLyt4y3
	 *
	 * @param bool $addDashes If true then '-' is added after byte 4, 6, 8 and 10.
	 * @return string
	 */
	public static function v4HexFactory( bool $addDashes = false ) {
		return implode( $addDashes ? '-' : '', [
			bin2hex( random_bytes( 4 ) ),
			bin2hex( random_bytes( 2 ) ),
			bin2hex( chr( ( ord( random_bytes( 1 ) ) & 0x0F ) | 0x40 ) ) . bin2hex( random_bytes( 1 ) ),
			bin2hex( chr( ( ord( random_bytes( 1 ) ) & 0x3F ) | 0x80 ) ) . bin2hex( random_bytes( 1 ) ),
			bin2hex( random_bytes( 6 ) )
		] );
	}

	/**
	 * @param string|null $input A valid UUID as bytes or in hex form, or null.
	 * @return string|null
	 * @throws InvalidArgumentException If not null nor a valid UUID.
	 */
	public static function asBytes( ?string $input ): ?string {
		if ( $input === null ) {
			return null;
		} elseif ( self::isBytes( $input ) ) {
			return $input;
		} elseif ( self::isHex( $input ) ) {
			return self::hexToBytes( $input );
		}
		throw new InvalidArgumentException( "Not a valid v4 UUID: $input" );
	}

	/**
	 * @param string|null $input A valid UUID as bytes or in hex form, or null.
	 * @param bool $addDashes If true then '-' is added after byte 4, 6, 8 and 10.
	 * @return string|null
	 * @throws InvalidArgumentException If not null nor a valid UUID.
	 */
	public static function asHex( ?string $input, bool $addDashes = false ): ?string {
		if ( $input === null ) {
			return null;
		} elseif ( self::isBytes( $input ) ) {
			return self::bytesToHex( $input, $addDashes );
		} elseif ( self::isHex( $input ) ) {
			return self::hexToHex( $input, $addDashes );
		}
		throw new InvalidArgumentException( "Not a valid v4 UUID: $input" );
	}

	/**
	 * @param string $input
	 * @param bool $addDashes If true then '-' is added after byte 4, 6, 8 and 10.
	 * @return string
	 */
	private static function bytesToHex( string $input, bool $addDashes = false ): string {
		$hex = bin2hex( $input );
		if ( !$addDashes ) {
			return $hex;
		}
		return substr( $hex, 0, 8 ) . '-' .
			substr( $hex, 8, 4 ) . '-' .
			substr( $hex, 12, 4 ) . '-' .
			substr( $hex, 16, 4 ) . '-' .
			substr( $hex, 20, 12 );
	}

	/**
	 * @param string $input
	 * @param bool $addDashes If true then '-' is added after byte 4, 6, 8 and 10.
	 * @return string
	 */
	private static function hexToHex( string $input, bool $addDashes = false ): string {
		return self::bytesToHex( self::hexToBytes( $input ), $addDashes );
	}

	/**
	 * @param string $input
	 * @return string
	 */
	private static function hexToBytes( string $input ): string {
		$numberOfBytes = strlen( $input );
		if ( $numberOfBytes === 32 ) {
			return hex2bin( $input );
		}
		// only private internals call to this method, should be safe
		// elseif ( $numberOfBytes == 36 ) {
		$hex = substr( $input, 0, 8 ) .
			substr( $input, 9, 4 ) .
			substr( $input, 14, 4 ) .
			substr( $input, 19, 4 ) .
			substr( $input, 24, 12 );
		return hex2bin( $hex );
	}

	/**
	 * @param string $input
	 * @return bool True if input is a valid raw 16 byte UUID V4.
	 */
	public static function isBytes( string $input ): bool {
		return strlen( $input ) === 16
			&& self::isHex( self::bytesToHex( $input, true ) );
	}

	/**
	 * @param string $input
	 * @return bool True if input is a valid hex formatted UUID V4 with or without dashes.
	 */
	public static function isHex( string $input ): bool {
		return preg_match(
		'/^[0-9A-F]{8}(-?)[0-9A-F]{4}(\1)4[0-9A-F]{3}(\1)[89AB][0-9A-F]{3}(\1)[0-9A-F]{12}$/i',
			$input
		);
	}

}
