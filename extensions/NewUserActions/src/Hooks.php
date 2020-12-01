<?php

namespace MediaWiki\Extensions\NewUserActions;

use User;

class Hooks {

	/**
	 * Hook function to run specific actions when a new account is created.
	 *
	 * @param User $user User object of the user
	 * @param bool $byEmail if the account has been created by mail, true
	 * @return bool
	 */
	public static function onAddNewAccount( User $user, $byEmail ) {
		if ( class_exists( 'AuthManager' ) ) {
			// This feature can be triggered by LocalUserCreated if needed.
			return true;
		}

		Actions::run( $user );
		return true;
	}

	/**
	 * Hook function to run specific actions when an account is created
	 * through AuthManager.
	 *
	 * @param User $user User object of the user
	 * @param bool $autocreated
	 * @return bool
	 */
	public static function onLocalUserCreated( User $user, $autocreated ) {
		global $wgNewUserActionsOnAutoCreate;

		if ( $wgNewUserActionsOnAutoCreate || !$autocreated ) {
			Actions::run( $user );
		}

		return true;
	}

	/**
	 * Hook function to modify $wgReservedUsernames at run time.
	 *
	 * @param array &$reservedUsernames The list of reserved (can't be registered) usernames
	 * @return bool
	 */
	public static function onUserGetReservedNames( &$reservedUsernames ) {
		$reservedUsernames[] = 'msg:newuseractions-editor';
		return true;
	}

}
