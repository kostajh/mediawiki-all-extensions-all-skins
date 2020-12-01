<?php
/**
 * Hooks for ScrollableTables extension
 *
 * @file
 * @ingroup Extensions
 */

class ScrollableTablesHooks {

	/**
	 * @param OutputPage &$out
	 * @param Skin &$skin
	 */
	public static function registerModules( OutputPage &$out, Skin &$skin ) {
		$out->addModules( [ 'ext.scrollableTables' ] );
	}
}
