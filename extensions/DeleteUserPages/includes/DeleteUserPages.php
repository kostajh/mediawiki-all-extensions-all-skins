<?php
/**
 * DeleteUserPages extension
 *
 * @file
 * @ingroup Extensions
 * @author Ryan Schmidt
 * @license MIT
 * @link https://www.mediawiki.org/wiki/Extension:DeleteUserPages Documentation
 */

use MediaWiki\MediaWikiServices;

class DeleteUserPages {

	/**
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param array &$errors
	 * @param bool $doExpensiveQueries
	 * @param bool $short
	 * @return bool
	 */
	public static function onTitleQuickPermissions( $title, $user, $action, &$errors, $doExpensiveQueries, $short ) {
		if ( $action !== 'delete' || count( $errors ) > 0 ) {
			return true;
		}

		$ns = $title->getNamespace();
		$userName = $user->getName();
		$root = $title->getRootText();
		$text = $title->getText();

		if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
			// MW 1.33+
			$userCan = MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan( 'edit', $user, $title );
		} else {
			$userCan = $title->userCan( 'edit' );
		}

		if (
			( $ns === NS_USER || $ns === NS_USER_TALK )
			&& $userName === $root
			&& $userCan
		) {
			if ( $root === $text && $user->isAllowed( 'delete-rootuserpages' ) ) {
				return false;
			} elseif ( $root !== $text && $user->isAllowed( 'delete-usersubpages' ) ) {
				return false;
			}
		}

		return true;
	}
}
