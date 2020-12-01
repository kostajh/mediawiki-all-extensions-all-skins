<?php

use MediaWiki\MediaWikiServices;

/**
 * SVGEdit extension: hooks
 * @copyright 2010 Brion Vibber <brion@pobox.com>
 */

class SVGEditHooks {
	/* Static Methods */

	/**
	 * BeforePageDisplay hook
	 *
	 * Adds the modules to the page
	 *
	 * @param $out OutputPage output page
	 * @param $skin Skin current skin
	 */
	public static function beforePageDisplay( $out, $skin ) {
		global $wgRequest, $wgSVGEditInline;
		$title = $out->getTitle();
		$user = $out->getUser();
		$modules = array();
		if( self::trigger( $title, $user ) ) {
			$modules[] = 'ext.svgedit.editButton';
		}
		if ($wgSVGEditInline) {
			// Experimental inline edit trigger.
			// Potentially expensive and tricky as far as UI on article pages!
			if( $user->isAllowed( 'upload' ) ) {
				$modules[] = 'ext.svgedit.inline';
			}
		}
		if ($wgRequest->getVal('action') == 'edit') {
			if( $user->isAllowed( 'upload' ) ) {
				$modules[] = 'ext.svgedit.toolbar';
			}
		}
		if ($modules) {
			$out->addModules($modules);
		}
		return true;
	}

	/**
	 * MakeGlobalVariablesScript hook
	 *
	 * Exports a setting if necessary.
	 *
	 * @param $vars array of vars
	 */
	public static function makeGlobalVariablesScript( &$vars ) {
		global $wgSVGEditEditor;
		$vars['wgSVGEditEditor'] = $wgSVGEditEditor;
		return true;
	}

	/**
	 * Should the editor links trigger on this page?
	 *
	 * @param Title $title
	 * @param User $user
	 * @return boolean
	 */
	private static function trigger( $title, User $user ) {
		if ( $title && $title->getNamespace() == NS_FILE ) {
			if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
				// MW 1.33+
				$pm = MediaWikiServices::getInstance()->getPermissionManager();
				return $pm->userCan( 'edit', $user, $title ) &&
					$pm->userCan( 'upload', $user, $title );
			} else {
				return $title->userCan( 'edit' ) && $title->userCan( 'upload' );
			}
		}
		return false;
	}

}
