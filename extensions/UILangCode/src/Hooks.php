<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
namespace MediaWiki\Extension\UILangCode;

class Hooks {
	/**
	 * Hook: MagicWordwgVariableIDs
	 * @param array &$vars
	 */
	public static function onMagicWordwgVariableIDs( array &$vars ) {
		$vars[] = 'MAG_UILANGCODEx';
	}

	/**
	 * Hook: ParserGetVariableValueSwitch
	 * @param \Parser $parser
	 * @param array &$varCache
	 * @param string $index
	 * @param string &$ret
	 */
	public static function onParserGetVariableValueSwitch(
		\Parser $parser,
		array &$varCache,
		$index,
		&$ret
	) {
		if ( $index === 'MAG_UILANGCODEx' ) {
			$ret = $varCache[$index] = $parser->getOptions()->getUserLangObj()->getCode();
		}
	}
}
