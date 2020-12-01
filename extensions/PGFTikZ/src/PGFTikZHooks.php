<?php

/**
 * PGFTikZ parser hooks
 */
class PGFTikZHooks {

	/**
	 * Register PGFTikZ hook
	 */
	public static function onPGFTikZParserInit( $parser ) {
		$parser->setHook( 'PGFTikZ', 'PGFTikZParser::PGFTikZParse' );
		return true;
	}

	/**
	 * After tidy hook - restore content
	 */
	public static function onPGFTikZAfterTidy(&$parser, &$text) {
		// find markers in $text
		// replace markers with actual output
		global $markerList;
		if ( is_array( $markerList ) ) {
			for ( $i = 0; $i < count( $markerList ); $i++ ) {
				$text = preg_replace( '/xx-marker' . $i . '-xx/',
				                      $markerList[$i], $text );
			}
		}
		return true;
	}

}

