<?php

class PageBlock {

	/**
	 * Simple cache to avoid repeated queries
	 * @var array
	 */
	protected static $cache = [];

	/**
	 * @param Title $title
	 * @param User $user
	 * @return string
	 */
	protected static function getCacheKey( $title, $user ) {
		$key = $title !== null ? $title->getPrefixedText() : '';
		$key .= '|'; // Delimiter to avoid weird stuff.
		$key .= $user !== null ? $user->getName() : '';
		return $key;
	}

	/**
	 * @param Title|null $title or null to get all restrictions for user
	 * @param User|null $user or null to get all restrictions for page
	 * @return array of user ids
	 * @throws MWException
	 */
	public static function getPageRestrictions( $title = null, $user = null ) {
		if ( !$title && !$user ) {
			throw new MWException( 'Either $title or $user must be provided to ' . __METHOD__ );
		}

		$key = self::getCacheKey( $title, $user );
		if ( isset( self::$cache[$key] ) ) {
			return self::$cache[$key];
		}

		$conds = [];
		if ( $title ) {
			$conds['pr_page'] = $title->getArticleID();
		}
		if ( $user ) {
			$conds['pr_user'] = $user->getId();
		} else {
			$conds[] = 'pr_user IS NOT NULl';
		}

		$dbr = wfGetDB( DB_REPLICA );
		$result = $dbr->select(
			[ 'page_restrictions' ],
			[ 'pr_user', 'pr_expiry', 'pr_type' ],
			$conds,
			__METHOD__
		);

		$restrictions = [];
		$now = wfTimestampNow();
		foreach ( $result as $row ) {
			$expiry = $dbr->decodeExpiry( $row->pr_expiry );
			if ( $expiry === 'infinity' || $now < $expiry ) {
				// Not expired. We'll purge the expired ones later.
				$restrictions[$row->pr_user][$row->pr_type] = $expiry;
			}
		}

		// Store it for later.
		self::$cache[$key] = $restrictions;

		return $restrictions;
	}

	/**
	 * @param User $user
	 * @param Title $title
	 * @param string $action
	 * @return bool|string false if not, expiry if true
	 * expiry is either a raw timestamp or the string "infinity"
	 */
	public static function isUserBlockedFromPage( User $user, Title $title, $action ) {
		$restrictions = self::getPageRestrictions( $title );
		return isset( $restrictions[$user->getId()][$action] ) ? $restrictions[$user->getId()][$action] : false;
	}

	/**
	 * @param User $actor the user who is placing the restriction
	 * @param string $reason
	 * @param Title $title the page being restricted
	 * @param User $user the user being restricted
	 * @param array $newRestrictions
	 * @param bool $existing
	 */
	public static function createLogEntry( User $actor, $reason, Title $title, User $user, array $newRestrictions, $existing = false ) {
		if ( !$newRestrictions ) {
			$subtype = 'remove';
		} elseif ( $existing ) {
			$subtype = 'modify';
		} else {
			$subtype = 'add';
		}

		$entry = new ManualLogEntry( 'restrict', $subtype );
		$entry->setPerformer( $actor );
		// Set the target as the user so we can easily view a log
		// of a user's restrictions.
		$entry->setTarget( $user->getUserPage() );
		$entry->setComment( $reason );

		// This is pretty icky. I'm not sure of a better way around this.
		$entry->setParameters( [
			'4::page' => $title->getPrefixedDBkey(),
			'5::edit' => isset( $newRestrictions['edit'] ) ? $newRestrictions['edit'] : '',
			'6::move' => isset( $newRestrictions['move'] ) ? $newRestrictions['move'] : '',
		] );

		$id = $entry->insert();
		$entry->publish( $id );
	}

	/**
	 * Inserts new restrictions into the database.
	 * @param Title $title
	 * @param User $user
	 * @param array $info assoc array of 'type' => 'expiry'
	 * @throws MWException
	 */
	public static function updateRestrictions( Title $title, User $user, array $info ) {
		// Clean up some expired stuff
		Title::purgeExpiredRestrictions();

		if ( $title->getArticleID() === 0 ) {
			throw new MWException( "Cannot add restrictions for \"{$title->getPrefixedText()}\", it does not exist." );
		}

		$dbw = wfGetDB( DB_MASTER );

		// First, clear out any existing restrictions.
		$dbw->delete(
			'page_restrictions',
			[
				'pr_page' => $title->getArticleID(),
				'pr_user' => $user->getId()
			],
			__METHOD__
		);

		$rows = [];
		foreach ( $info as $action => $expiry ) {
			$rows[] = [
				'pr_type' => $action,
				'pr_page' => $title->getArticleID(),
				'pr_user' => $user->getId(),
				'pr_expiry' => $dbw->encodeExpiry( $expiry )
			];
		}

		if ( $rows ) {
			// Now stick the new ones in!
			$dbw->insert(
				'page_restrictions',
				$rows,
				__METHOD__
			);
		}

		// Invalidate our internal cache.
		unset( self::$cache[self::getCacheKey( $title, $user )] );
	}

}
