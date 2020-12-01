<?php
/**
 * NoBogusUserpages
 *
 * @file
 * @ingroup Extensions
 *
 * @author Daniel Friesen (Dantman) <mediawiki@danielfriesen.name>
 *
 * @license GPL-2.0-or-later
 *
 */

class NoBogusUserpages {

	/**
	 * @param Title $title
	 * @param User $user
	 * @param string $action
	 * @param string &$result
	 * @return bool
	 */
	public static function onGetUserPermissionsErrors( $title, $user, $action, &$result ) {
		// If we're not in the user namespace,
		// or we're not trying to edit,
		// or the page already exists,
		// or we are allowed to create bogus userpages
		// then just let MediaWiki continue.
		if (
			$title->getNamespace() != NS_USER ||
			$action != 'create' ||
			$user->isAllowed( 'createbogususerpage' )
		) {
			return true;
		}

		$userTitle = explode( '/', $title->getText(), 2 );
		$userName = $userTitle[0];

		// Don't block the creation of IP userpages if the page is for a IPv4 or IPv6 page.
		if ( User::isIP( $userName ) ) {
			return true;
		}

		// Check if the user exists, if it says the user is anon,
		// but we know we're not on an IP page, then the user does not exist.
		// And therefore, we block creation.
		$user = User::newFromName( $userName );
		if ( $user->isAnon() ) {
			$result = 'badaccess-bogususerpage';
			return false;
		}

		return true;
	}

}
