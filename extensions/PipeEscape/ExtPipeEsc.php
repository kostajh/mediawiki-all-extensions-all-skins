<?php

class ExtPipeEsc {
	private static $parserFunctions = array('!' => 'pipeChar');

	public static function setup( &$parser ) {
		// register each hook
		foreach( self::$parserFunctions as $hook => $function )
			$parser->setFunctionHook( $hook,
				array( __CLASS__, $function ), Parser::SFH_OBJECT_ARGS );
		return true;
	}

	public static function pipeChar( &$parser, $frame, $args ) {
		$output = array_shift( $args );
		// no parameters means we're done.  spit out an empty string
		if ( !isset( $output ) )
			return '';
		// expand the first argument
		$output = $frame->expand( $output );
		// get the rest of the arguments, expand each one, prefix each expansion
		// with a pipe character, and append it to the output string.
		for ( $arg = array_shift( $args );
			isset( $arg );
			$arg = array_shift( $args ) )
		{
			$output .= '|' . $frame->expand( $arg );
		}
		//return '<pre><nowiki>'. trim( $output ) . '</nowiki></pre>';
		return trim( $output );
	}
}
