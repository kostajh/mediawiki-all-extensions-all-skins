<?php

class Sarcasm {

	/**
	 * Hook our callback function into the parser
	 *
	 * @param Parser $parser
	 */
	static function wfSarcasmParserInit( Parser $parser ) {
		// When the parser sees the <sarcasm> tag, it executes
		// the wfSarcasmRender function (see below)
		$parser->setHook( 'sarcasm', [ __CLASS__, 'wfSarcasmRender' ] );
	}

	/**
	 * Code for tags
	 *
	 * @param string $text
	 * @param string[] $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	static function wfSarcasmRender( $text, array $args, Parser $parser, PPFrame $frame ) {
		$output = $parser->recursiveTagParse( $text, $frame );
		return '<span class="sarcasm">' . $output . '</span>';
	}
}
