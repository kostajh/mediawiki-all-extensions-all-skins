<?php

use MediaWiki\MediaWikiServices;

class SkinPerPage {
	/**
	 * Register our extension tag and parser function
	 *
	 * @param Parser $parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		# Old one for backwards compatibility
		$parser->setHook( 'skin', [ __CLASS__, 'parserHook' ] );
		# Function-style one that users should actually use
		$parser->setFunctionHook( 'useskin', [ __CLASS__, 'useskinHook' ] );
		return true;
	}

	/**
	 * Handler for <skin>foo</skin>
	 * @param string $text
	 * @param array $attribs
	 * @param Parser $parser
	 * @return string
	 */
	public static function parserHook( $text, $attribs, Parser $parser ) {
		$parser->getOutput()->setExtensionData( 'spp_skin', $text );
		return '';
	}

	/**
	 * Reads the skin key from ParserOutput and sets it as the skin to use for display
	 *
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 * @return bool
	 */
	public static function onOutputPageParserOutput( OutputPage $out, ParserOutput $parserOutput ) {
		$key = $parserOutput->getExtensionData( 'spp_skin' ) ??
			// fallback to legacy property for old cache entries
			( $parserOutput->spp_skin ?? false );
		if ( $key !== false ) {
			$key = Skin::normalizeKey( strtolower( trim( $key ) ) );

			$skin = MediaWikiServices::getInstance()->getSkinFactory()->makeSkin( $key );

			RequestContext::getMain()->setSkin( $skin );
		}
		return true;
	}

	/**
	 * Handler for {{#useskin:foo}}
	 *
	 * @param Parser $parser
	 * @param string $skin
	 */
	public static function useskinHook( Parser $parser, $skin = '' ) {
		self::parserHook( $skin, [], $parser );
	}
}
