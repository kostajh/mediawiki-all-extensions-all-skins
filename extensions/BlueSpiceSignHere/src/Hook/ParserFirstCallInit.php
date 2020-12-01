<?php

namespace BlueSpice\SignHere\Hook;

class ParserFirstCallInit {

	/**
	 *
	 * @param \Parser &$parser
	 * @return bool
	 */
	public static function handle( &$parser ) {
		$signHereTag = new \BlueSpice\SignHere\Tag\SignHere();
		$parser->setHook( 'signhere', [ $signHereTag, 'render' ] );
		$parser->setHook( 'bs:signhere', [ $signHereTag, 'render' ] );
		return true;
	}
}
