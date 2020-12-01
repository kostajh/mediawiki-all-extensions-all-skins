<?php

namespace MediaWiki\Extension\PageMagic;

use Parser;

class Hooks {

	/**
	 * @param Parser $parser
	 *
	 * @throws \MWException
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook(
			'fullpagenamefromid',
			[ PageMagic::class, 'fullpagenamefromid' ],
			Parser::SFH_NO_HASH
		);

		$parser->setFunctionHook(
			'pageidfromrevisionid',
			[ PageMagic::class, 'pageidfromrevisionid' ],
			Parser::SFH_NO_HASH
		);

		$parser->setFunctionHook(
			'fullpagenamefromrevisionid',
			[ PageMagic::class, 'fullpagenamefromrevisionid' ],
			Parser::SFH_NO_HASH
		);
	}

}
