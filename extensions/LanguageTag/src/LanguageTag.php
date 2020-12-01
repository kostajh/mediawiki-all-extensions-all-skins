<?php

/**
 * LanguageTag extension from D250 Laboratories
 *
 * @file
 * @ingroup Extensions
 * @author István Király <LaKing@D250.hu>
 */

/**
 * It is assumed that the $wgLanguageTagLanguages is defined in LocalSettings.php, in a line before
 * this file is included with require.
 * This extension is used together with the LanguageSelector extension, ideally
 * $wgLanguageTagLanguages is identical with $wgLanguageSelectorLanguages.
 *
 * Since the tags are not accessible in the hooked render functions (no way to access that parameter
 * at runtime without altering the MediaWiki base code) render-functions for all language-tag's will
 * be created. First setting the hooks.
 */
class LanguageTag {

	/**
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		global $wgLanguageTagLanguages;

		foreach ( $wgLanguageTagLanguages as $tag ) {
			$parser->setHook( $tag, [ __CLASS__, 'LanguageTagRender_' . $tag ] );
		}
	}

	/**
	 * This function is helpful in checking against the passed Language
	 *
	 * @param string $input
	 * @param string $lang
	 * @return string
	 */
	private static function languageTagCheck( $input, $lang ) {
		$output = htmlspecialchars( $input );

		global $wgLang;

		if ( $wgLang->getCode() === $lang ) {
		   // Match. The Language (set by LanguageSelector) is equal to the language tag.
		   return $output;
		} else {
		   // Other wise we return the text as html-comment, thus not visible in the browser.
		   return '<!-- ' . $lang . ': ' . $output . ' -->';
		}
	}

	/**
	 * This will catch all LanguageTagRender_XX functions attached to the hooks.
	 * LanguageTagRender_ this is 18 characters,
	 * we get the language code from the function's name,
	 * and access everything else passed in via $arguments.
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return string
	 */
	public static function __callStatic( $name, $arguments ) {
		/** @var Parser $parser */
		[ $input, , $parser, $frame ] = $arguments;
		return $parser->recursiveTagParse(
			self::languageTagCheck( $input, substr( $name, 18 ) ),
			$frame
		);
	}
}
