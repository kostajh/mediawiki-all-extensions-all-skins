<?php

class PageBlockHooks {
	/**
	 * If an IP address is blacklisted, don't let them edit.
	 *
	 * @param Title &$title Title being acted upon
	 * @param User &$user User performing the action
	 * @param string $action Action being performed
	 * @param array &$result Will be filled with block status if blocked
	 * @return bool
	 */
	public static function onGetUserPermissionsErrorsExpensive( &$title, &$user, $action, &$result ) {
		if ( $action !== 'edit' && $action !== 'move' ) {
			// Quick exit, these are the only actions we
			// support right now.
			return true;
		}
		$expiry = PageBlock::isUserBlockedFromPage( $user, $title, $action );
		if ( $expiry ) {
			$msg = "pageblock-blocked-$action";
			$msg .= $expiry === 'infinity' ? '-indef' : '';
			$lang = RequestContext::getMain()->getLanguage();
			$result = [ $msg, $user->getName(), $title->getPrefixedText(), $lang->formatExpiry( $expiry ) ];

			return false;
		}

		return true;
	}

	/**
	 * @param WikiPage &$article
	 * @param User &$user
	 * @param string $reason
	 * @param int $id
	 * @return bool
	 */
	public static function onArticleDeleteComplete( &$article, User &$user, $reason, $id ) {
		// @todo: Figure out if this will already get deleted.
		// If not, add a hook handler for it.
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'page_restrictions',
			[ 'pr_page' => $id ],
			__METHOD__
		);

		return true;
	}

}
