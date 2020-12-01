<?php

class PageAssignmentsHooks {

	/**
	 * Hook handler for MediaWiki 'TitleMoveComplete' hook. Adapts assignments in case of article move.
	 * @param Title &$old
	 * @param Title &$nt
	 * @param User $user
	 * @param int $pageid
	 * @param int $redirid
	 * @param string $reason
	 * @return bool Always true to keep other hooks running.
	 */
	public static function onTitleMoveComplete( &$old, &$nt, $user, $pageid, $redirid, $reason ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->update(
			'bs_pageassignments',
			[
				'pa_page_id' => $nt->getArticleID()
			],
			[
				'pa_page_id' => $old->getArticleID()
			]
		);
		return true;
	}

	/**
	 * Clears assignments
	 * @param WikiPage &$wikiPage
	 * @param User &$user
	 * @param string $reason
	 * @param int $id
	 * @param Content $content
	 * @param ManualLogEntry $logEntry
	 * @return bool
	 */
	public static function onArticleDeleteComplete( &$wikiPage, &$user, $reason,
		$id, $content, $logEntry ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->delete(
			'bs_pageassignments',
			[
				'pa_page_id' => $wikiPage->getId()
			]
		);
		return true;
	}

	/**
	 * Deletes all page assignments on user deleted.
	 * @param UserManager $oUserManager
	 * @param User $oUser
	 * @param Status &$oStatus
	 * @param User $oPerformer
	 * @return bool
	 */
	public static function onBSUserManagerAfterDeleteUser( $oUserManager, $oUser,
		&$oStatus, $oPerformer ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->delete(
			'bs_pageassignments',
			[
				'pa_assignee_key' => $oUser->getName(),
				'pa_assignee_type' => 'user'
			]
		);
		return true;
	}

	/**
	 * Updates all page assignments on group name change.
	 * @param string $sGroup
	 * @param string $sNewGroup
	 * @return bool
	 */
	public static function onBSGroupManagerGroupNameChanged( $sGroup, $sNewGroup ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->update(
			'bs_pageassignments',
			[
				'pa_assignee_key' => $sNewGroup,
			],
			[
				'pa_assignee_key' => $sGroup,
				'pa_assignee_type' => 'group'
			]
		);
		return true;
	}

	/**
	 * Deletes all page assignments on group deleted.
	 * @param string $sGroup
	 * @return bool
	 */
	public static function onBSGroupManagerGroupDeleted( $sGroup ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->delete(
			'bs_pageassignments',
			[
				'pa_assignee_key' => $sGroup,
				'pa_assignee_type' => 'group'
			]
		);
		return true;
	}

}
