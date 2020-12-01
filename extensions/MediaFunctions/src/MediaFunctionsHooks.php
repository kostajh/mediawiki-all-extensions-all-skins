<?php

class MediaFunctionsHooks {

	/**
	 * Register function callbacks and add error messages to the message cache.
	 *
	 * @param Parser &$parser
	 */
	public static function efMediaFunctionsSetup( &$parser ) {
		$parser->setFunctionHook( 'mediamime', [ 'MediaFunctions', 'mediamime' ] );
		$parser->setFunctionHook( 'mediasize', [ 'MediaFunctions', 'mediasize' ] );
		$parser->setFunctionHook( 'mediaheight', [ 'MediaFunctions', 'mediaheight' ] );
		$parser->setFunctionHook( 'mediawidth', [ 'MediaFunctions', 'mediawidth' ] );
		$parser->setFunctionHook( 'mediadimensions', [ 'MediaFunctions', 'mediadimensions' ] );
		$parser->setFunctionHook( 'mediaexif', [ 'MediaFunctions', 'mediaexif' ] );
		$parser->setFunctionHook( 'mediapages', [ 'MediaFunctions', 'mediapages' ] );
	}
}
