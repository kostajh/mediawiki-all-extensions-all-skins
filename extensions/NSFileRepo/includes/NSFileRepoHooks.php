<?php

use MediaWiki\MediaWikiServices;

class NSFileRepoHooks {

	/**
	 * Initial setup
	 * @global array $wgLocalFileRepo
	 */
	public static function setup() {
		$GLOBALS['wgLocalFileRepo']['class'] = "NSLocalRepo";
	}

	public static function register() {
		require_once( __DIR__.'/DefaultSettings.php' );
	}

	/**
	 * Add JavaScript
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean true
	 */
	public static function onBeforePageDisplay( &$out, &$skin ) {
		if( $out->getTitle()->isSpecial( 'Upload' ) ) {
			$out->addModules( 'ext.nsfilerepo.special.upload' );
		}

		return true;
	}

	/**
	 * @param $path
	 * @param $name
	 * @param $filename
	 * @return bool
	 */
	public static function onImgAuthBeforeCheckFileExists( &$path, &$name, &$filename ) {
		$nsfrhelper = new NSFileRepoHelper();
		$title = $nsfrhelper->getTitleFromPath( $path );
		if( $title instanceof Title && $title->getNamespace() !== NS_MAIN ) {
			//Not using "$title->getPrefixedDBKey()" because "$wgCapitalLinkOverrides[NS_FILE]" may be "false"
			$name = $title->getNsText() . ':' . $name;
		}

		return true;
	}

	/**
	 * @param Title $title
	 * @param $path
	 * @param $name
	 * @param $result
	 * @return bool
	 */
	public static function onImgAuthBeforeStream( $title, $path, $name, &$result ) {
		$nsfrhelper = new NSFileRepoHelper();
		$authTitle = $nsfrhelper->getTitleFromPath( $path );

		if( $authTitle instanceof Title === false ) {
			$result = array('img-auth-accessdenied', 'img-auth-badtitle', $name);
			return false;
		}

		$context = RequestContext::getMain();
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( !$permissionManager->userCan( 'read', $context->getUser(), $authTitle ) ) {
			$result = array( 'img-auth-accessdenied', 'img-auth-noread', $name );
			return false;
		}

		return true;
	}

	/**
	 * Checks if the destination file name contains a valid namespace prefix
	 * @param string $destName
	 * @param string $tempPath
	 * @param string $error
	 * @return bool
	 */
	public static function onUploadVerification( $destName, $tempPath, &$error ) {
		$title = Title::newFromText( $destName );
		if( strpos( $title->getText(), ':' ) !== false ) { //There is a colon in the name but it was not a valid namespace prefix!
			$error = 'illegal-filename';
			return false;
		}
		return true;
	}
}
