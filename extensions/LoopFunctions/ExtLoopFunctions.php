<?php

class ExtLoopFunctions {
	public static $mMaxLoopCount = 100; // Maximum number of loops allowed per session
	private static $mCurrentLoopCount = 0; // number of executed loops this session

	/**
	 * @param Parser $parser
	 */
	public static function setup( $parser ) {
		global $wgExtLoopFunctions;
		$wgExtLoopFunctions = new ExtLoopFunctions();

		$parser->setFunctionHook( 'for', [ &$wgExtLoopFunctions, 'forHook' ], Parser::SFH_OBJECT_ARGS );
		$parser->setFunctionHook( 'foreach', [ __CLASS__, 'foreachHook' ], Parser::SFH_OBJECT_ARGS );
	}

	public function forHook( $parser, $frame, $args ) {
		$nbr_of_loops = isset( $args[0] ) ? abs( intval( trim( $frame->expand( $args[0] ) ) ) ) : 1;
		$text = isset( $args[1] ) ? $frame->expand( $args[1] ) : '';
		// If you want the text to be trimmed, comment out previous line and uncomment the next one.
		//$text = isset($args[1]) ? trim($frame->expand($args[1])) : '' ;
		$param = isset( $args[2] ) ? trim( $frame->expand( $args[2] ) ) : '$n$';
		$return = '';

		for ( $i = 0; $i < $nbr_of_loops; ++$i ) {
			if ( ++self::$mCurrentLoopCount > self::$mMaxLoopCount ) {
				return wfMessage( 'loopfunc_max_loops' );
			}

			$return .= strtr( $text, [ $param => $i + 1 ] );
		}

		return $parser->replaceVariables( $return, $frame, true );
	}

	public static function foreachHook( $parser, $frame, $args ) {
		$mask = isset( $args[0] ) ? trim( $frame->expand( $args[0] ) ) : '';
		$text = isset( $args[1] ) ? trim( $frame->expand( $args[1] ) ) : '';
		$param = isset( $args[2] ) ? trim( $frame->expand( $args[2] ) ) : '$n$';
		$variables = $frame->namedArgs + $frame->numberedArgs;
		list( $prefix , $suffix ) = $param == '' ? [ $mask , '' ] : explode( $param, $mask, 2 );
		$return = '';

		for ( $i = 0; array_key_exists( $prefix . ( $i + 1 ) . $suffix, $variables ); ++$i ) {
			if ( ++self::$mCurrentLoopCount > self::$mMaxLoopCount ) {
				return wfMessage( 'loopfunc_max_loops' );
			}

			$return .= strtr( $text, [ $param => $i + 1 ] );
		}

		return $parser->replaceVariables( $return, $frame, true );
	}

}
