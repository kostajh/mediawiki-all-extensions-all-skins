<?php

namespace MediaWiki\Extension\AddHtmlMetaAndTitle;

use OutputPage;
use Parser;
use Skin;

class Hooks {

	/**
	 * @link https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 * @param Parser &$parser The global parser.
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$adder = Adder::getInstance();
		$parser->setHook( 'seo', [ $adder, 'renderSeoElement' ] );
	}

	/**
	 * Handle meta elements and page title modification.
	 * @link https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * @param OutputPage &$out The output page.
	 * @param Skin &$skin The current skin.
	 * @return bool
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$adder = Adder::getInstance();
		$adder->addMetaAndTitle( $out );
		return true;
	}
}
