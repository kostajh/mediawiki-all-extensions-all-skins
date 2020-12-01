<?php
/**
 * UserPageEditProtection
 *
 * @file
 * @ingroup Extensions
 *
 * @license GPL-2.0-or-later
 *
 */

class UserPageEditProtection {

	/**
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param array|IApiMessage &$result
	 *
	 * @return bool
	 */
	public static function onUserCan( $title, $user, $action, &$result ) {
		global $wgOnlyUserEditUserPage;
		$lTitle = explode( '/', $title->getText() );
		if ( !( $action == 'edit' || $action == 'move' ) ) {
			$result = null;
		return true;
		}
		if ( $title->mNamespace !== NS_USER ) {
			$result = null;
			return true;
		}
		if ( $wgOnlyUserEditUserPage ) {
			if ( $user->isAllowed( 'editalluserpages' ) || ( $user->getname() == $lTitle[0] ) ) {
				$result = null;
				return true;
			} else {
				$result = false;
				return false;
			}
		}
		$result = null;
		return true;
	}
}
