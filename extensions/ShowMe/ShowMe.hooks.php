<?php
/**
 * Hooks for ShowMe extension
 *
 * @file
 * @ingroup Extensions
 */

class ShowMeHooks {

	/**
	 * @param Parser &$parser
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setHook( 'showme', [ __CLASS__, 'showMeRender' ] );
	}

	/**
	 * @staticvar int $num
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string Output HTML
	 */
	public static function showMeRender( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->getOutput()->addModules( 'ext.showMe' );

		$options = [];
		$lines = explode( "\n", $input );
		foreach ( $lines as $line ) {
			$bits = explode( '|', $line, 3 );
			if ( !isset( $bits[1] ) ) {
				continue;
				// throw error instead?
			}
			$options[trim( $bits[0] )] = trim( $bits[1] );
		}

		// If the user supplied an ID, use it. Otherwise, generate one.
		if ( isset( $args['name'] ) ) {
			$name = $args['name'];
		} else {
			static $num = 0;
			// Add a num to the HTML ID in case there is more than one ShowMe field on this page.
			// Iterate after getting num.
			$name = 'showme-dropdown' . '-' . $num++;
		}

		$showMe = new ShowMe( 'dropdown', $name, $options, $parser->getOutput() );
		return $showMe->getHTML();
	}
}
