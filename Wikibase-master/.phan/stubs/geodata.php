<?php

/**
 * Minimal set of classes necessary to fulfill needs of parts of Wikibase relying on
 * the GeoData extension.
 * @codingStandardsIgnoreFile
 */

namespace GeoData;

class Coord {
	public $primary = false;
	/**
	 * @param float $lat
	 * @param float $lon
	 * @param string|null $globe
	 * @param array $extraFields
	 */
	public function __construct( $lat, $lon, $globe = null, $extraFields = [] ) {
	}
}

class CoordinatesOutput {
	public function addPrimary( Coord $c ) {
	}

	/**
	 * @return Coord|false
	 */
	public function getPrimary() {
	}

	public function hasPrimary(): bool {
	}

	public function addSecondary( Coord $c ) {
	}

	public static function getOrBuildFromParserOutput(
		\ParserOutput $parserOutput
	): CoordinatesOutput {
	}

	/**
	 * @param \ParserOutput $parserOutput
	 * @return CoordinatesOutput|null
	 */
	public static function getFromParserOutput( \ParserOutput $parserOutput ) {
	}

	/**
	 * @param \ParserOutput $parserOutput
	 */
	public function setToParserOutput( \ParserOutput $parserOutput ) {
	}
}

class GeoData {
}
