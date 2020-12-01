<?php
/**
 * ImportArticles
 * ImportArticles MediaWiki hooks
 *
 * @author: Kris Blair (Cblair91)
 * @license: GPL-3.0-only http://www.gnu.org/licenses/gpl.html
 * @package: ImportArticles
 * @link: https://mediawiki.org/wiki/Extension:ImportArticles
 */

class ImportArticles {
	/**
	 * @var	bool
	 */
	private static $initialized = false;

	/**
	 * Initiates the loading of the JavaScript
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, $skin ) {
		if ( !self::$initialized ) {
			$out->addModules( 'ext.importarticles' );
			self::$initialized = true;
		}
	}
}
