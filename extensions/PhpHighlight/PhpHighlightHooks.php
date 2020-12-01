<?php

class PhpHighlightHooks {

	/**
	 * @param Parser &$parser
	 */
	static function efSetPhp( &$parser ) {
		$parser->setHook( 'php', 'PhpHighlightHooks::efRenderPhp' );
	}

	/**
	 * Callback
	 *
	 * @param string $text
	 * @return string
	 */
	static function efRenderPhp( $text ) {
		$html = highlight_string( $text, true );
		return str_replace( '<br>', "<br>\n", $html );
	}
}
