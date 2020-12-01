<?php

namespace BS\PageVersion;

class Setup {

	/**
	 * Initializes the extension
	 */
	public static function init() {
		\Hooks::register( 'MagicWordwgVariableIDs', [ new Hook\MagicWordwgVariableIDs(), 'handle' ] );
		\Hooks::register( 'ParserGetVariableValueSwitch', [ new Hook\ParserGetVariableValueSwitch(), 'handle' ] );
		\Hooks::register( 'PageHistoryLineEnding', [ new Hook\PageHistoryLineEnding(), 'handle' ] );
	}
}
