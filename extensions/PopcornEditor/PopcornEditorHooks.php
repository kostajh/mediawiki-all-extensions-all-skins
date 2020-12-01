<?php
/**
 * PopcornEditor extension: hooks
 * @copyright 2010-2016 Brion Vibber <brion@pobox.com>
 */

use MediaWiki\MediaWikiServices;

class PopcornEditorHooks {
	/* Static Methods */

	/**
	 * BeforePageDisplay hook
	 *
	 * Adds the modules to the page
	 *
	 * @param OutputPage $out output page
	 * @param Skin $skin current skin
	 * @return bool
	 */
	public static function beforePageDisplay( $out, $skin ) {
		$title = $out->getTitle();
		$user = $out->getUser();
		$modules = [];
		if ( self::trigger( $title, $user ) ) {
			$modules[] = 'ext.popcorn.editButton';
		}
		if ( $modules ) {
			$out->addModules( $modules );
		}
		return true;
	}

	/**
	 * MakeGlobalVariablesScript hook
	 *
	 * Exports a setting if necessary.
	 *
	 * @param array &$vars array of vars
	 * @return bool
	 */
	public static function makeGlobalVariablesScript( &$vars ) {
		global $wgPopcornEditorUrl;
		$vars['wgPopcornEditorUrl'] = $wgPopcornEditorUrl;
		return true;
	}

	/**
	 * Should the editor links trigger on this page?
	 *
	 * @param Title $title
	 * @param User $user
	 * @return bool
	 */
	private static function trigger( $title, User $user ) {
		if ( !( $title && $title->getNamespace() == NS_FILE &&
			preg_match( '/\.popcorn$/', $title->getText() ) )
		) {
			return false;
		}

		// Only check permissions if everything else is fine, to avoid trying to check
		// if the user can upload to a page that isn't in the file namespace
		if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
			// MW 1.33+
			$permManager = MediaWikiServices::getInstance()->getPermissionManagar();
			return $permManager->userCan( 'edit', $user, $title ) &&
				$permManager->userCan( 'upload', $user, $title );
		} else {
			return $title->userCan( 'edit' ) && $title->userCan( 'upload' );
		}
	}

}
