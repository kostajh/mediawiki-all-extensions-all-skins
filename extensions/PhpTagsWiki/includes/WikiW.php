<?php
namespace PhpTagsObjects;

use ExtensionRegistry;
use PhpTags\GenericObject;
use PhpTags\HookException;
use PhpTags\Hooks;
use PhpTags\Renderer;
use SpecialVersion;
use Title;

/**
 * Description of WikiW
 *
 * @author pastakhov
 */
class WikiW extends GenericObject {

	public static function getConstantValue( $constantName ) {
		switch ( $constantName ) {
			case 'PHPTAGS_WIKI_VERSION':
				return ExtensionRegistry::getInstance()->getAllThings()['PhpTags Wiki']['version'];
		}
		return parent::getConstantValue( $constantName );
	}

	public static function c_CONTENT_LANGUAGE() {
		global $wgLanguageCode;
		return $wgLanguageCode;
	}

	public static function c_CURRENT_VERSION() {
		static $value = false;
		if ( $value === false ) {
			$value = SpecialVersion::getVersion();
		}
		return $value;
	}

	public static function c_SCRIPT_PATH() {
		global $wgScriptPath;
		return $wgScriptPath;
	}

	public static function c_SERVER() {
		global $wgServer;
		return $wgServer;
	}

	public static function c_SERVER_NAME() {
		global $wgServer;
		$serverParts = wfParseUrl( $wgServer );
		return $serverParts && isset( $serverParts['host'] ) ? $serverParts['host'] : $wgServer;
	}

	public static function c_SITE_NAME() {
		global $wgSitename;
		return $wgSitename;
	}

	public static function c_STYLE_PATH() {
		global $wgStylePath;
		return $wgStylePath;
	}

	public static function c_MAIN_PAGE() {
		return Hooks::getObjectWithValue(
				'WTitle',
				Title::newMainPage()
			);
	}

	/**
	 * @return string
	 * @throws HookException
	 */
	public static function c_CURRENT_USER_NAME() {
		$user = Renderer::getParser()->getUser();
		Renderer::disableParserCache();
		return $user->getName();
	}

}

