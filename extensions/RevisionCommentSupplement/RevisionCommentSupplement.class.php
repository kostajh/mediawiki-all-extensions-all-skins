<?php
/**
 * Copyright (C) 2012 Burthsceh <burthsceh@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

class RevisionCommentSupplement {

	/**
	 * @param int|string $revId: revision id
	 * @param string $newsupplement: a new supplementary comment
	 * @param string $reason: reason
	 * @param User $user: user
	 * @return bool
	 */
	public static function insert( $revId, $newsupplement, $reason, User $user ) {
		$oldsupplement = '';
		$action = 'create';
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp',
			'rcs_supplement',
			array( 'rcs_rev_id' => $revId ),
			__METHOD__
		);

		if ( isset( $dbRow ) && isset( $dbRow->rcs_supplement ) ) {
			$oldsupplement = $dbRow->rcs_supplement;
			$action = 'modify';
		}

		$historyId = 0;
		$dbw = wfGetDB( DB_MASTER );
		$timestamp = wfTimestamp( TS_MW );
		$row = array(
			'rcs_rev_id' => intval( $revId ),
			'rcs_user' => $user->getId(),
			'rcs_user_text' => $user->getName(),
			'rcs_timestamp' => $dbw->timestamp( $timestamp ),
			'rcs_supplement' => $newsupplement,
		);

		if ( RevisionCommentSupplementSetting::getHistorySetting() ) {
			$historyRow = array(
				'rcsh_id' => $dbw->nextSequenceValue( 'rev_comment_supp_history_id_seq' ),
				'rcsh_rev_id' => intval( $revId ),
				'rcsh_user' => $user->getId(),
				'rcsh_user_text' => $user->getName(),
				'rcsh_timestamp' => $dbw->timestamp( $timestamp ),
				'rcsh_supplement' => $newsupplement,
				'rcsh_reason' => $reason,
			);
			$ret = $dbw->insert( 'rev_comment_supp_history', $historyRow, __METHOD__ );
			if ( !$ret ) {
				return false;
			}
			$historyId = $dbw->insertId();
			$row = array_merge( $row, array( 'rcs_latest' => $historyId ) );
		}

		$dbw->replace( 'rev_comment_supp', array( 'rcs_rev_id' ), $row, __METHOD__ );

		self::insertLog(
			$revId,
			$action,
			$oldsupplement,
			$newsupplement,
			$reason,
			$timestamp,
			$user,
			$historyId
		);

		return true;
	}

	/**
	 * @param string $revId: revision id
	 * @param string $action: log action, log subtype 'create', 'modify', 'delete'
	 * @param string $oldsupplement: a old supplementary comment
	 * @param string $newsupplement: a new supplementary comment
	 * @param string $reason: reason
	 * @param string $timestamp: timestamp
	 * @param User $user: user
	 * @param string $historyId: history entry id
	 * @return int: ID of inserted log entry
	 */
	public static function insertLog(
		$revId,
		$action,
		$oldsupplement,
		$newsupplement,
		$reason,
		$timestamp,
		$user,
		$historyId = 0
	) {
		if ( !RevisionCommentSupplementSetting::getLogSetting( $action ) ) {
			return 0;
		}
		$args = array( '4::revid' => $revId );
		if ( RevisionCommentSupplementSetting::getLogSupplementSetting() ) {
			$args = array_merge( $args, array(
					'5::oldsupplement' => $oldsupplement,
					'6::newsupplement' => $newsupplement,
				)
			);
		} else {
			$action .= '2';
		}
		if ( RevisionCommentSupplementSetting::getHistorySetting() && $historyId ) {
			$args = array_merge( $args, array(
					'7::historyid' => $historyId,
				)
			);
		}

		$logEntry = new ManualLogEntry( 'revisioncommentsupplement', $action );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget(
			Title::makeTitleSafe( NS_SPECIAL, "RevisionCommentSupplement/{$revId}" )
		);
		$logEntry->setComment( $reason );
		$logEntry->setTimestamp( $timestamp );
		$logEntry->setParameters( $args );
		$logid = $logEntry->insert();
		$to = RevisionCommentSupplementSetting::getLogPublishSetting( $action );
		if ( $to === true ) {
			$logEntry->publish( $logid );
		} elseif ( $to ) {
			$logEntry->publish( $logid, $to );
		}
		return $logid;
	}

	/**
	 * @param string $revId: revision id
	 * @param User $user: user
	 * @param string $reason: reason
	 * @return bool
	 */
	public static function delete( $revId, $user, $reason = '' ) {
		$oldsupplement = '';
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp',
			'rcs_supplement',
			array( 'rcs_rev_id' => $revId ),
			__METHOD__
		);
		if ( isset( $dbRow ) && isset( $dbRow->rcs_supplement ) ) {
			$oldsupplement = $dbRow->rcs_supplement;
		} else {
			return false;
		}

		$dbw = wfGetDB( DB_MASTER );
		$ret = $dbw->delete( 'rev_comment_supp', array( 'rcs_rev_id' => $revId ), __METHOD__ );
		if ( !$ret ) {
			return false;
		}

		$rcslogid = self::insertLog(
			$revId,
			'delete',
			$oldsupplement,
			'',
			$reason,
			wfTimestamp( TS_MW ),
			$user
		);

		return true;
	}

	/**
	 * @param int $revId: revision id
	 * @return object|bool
	 */
	public static function getRow( $revId ) {
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp',
			'*',
			array( 'rcs_rev_id' => $revId ),
			__METHOD__
		);

		if ( isset( $dbRow ) && isset( $dbRow->rcs_rev_id ) && ( $dbRow->rcs_rev_id == $revId ) ) {
			return $dbRow;
		}

		return false;
	}

	/**
	 * @param int $revId: revision id
	 * @return bool
	 */
	public static function isExistRow( $revId ) {
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp',
			'rcs_rev_id',
			array( 'rcs_rev_id' => $revId ),
			__METHOD__
		);

		if ( isset( $dbRow ) && isset( $dbRow->rcs_rev_id ) && ( $dbRow->rcs_rev_id == $revId ) ) {
			return true;
		}

		return false;
	}
}

class RevisionCommentSupplementHistory {

	const HIDDEN_SUPPLEMENT = 1;
	const HIDDEN_REASON = 2;
	const HIDDEN_USER = 4;
	const HIDDEN_RESTRICTED = 8;
	const HIDDEN_ROW = 64;

	# references:
	# DeleteLogFormatter::getMessageParameters in LogFormatter.php
	# RevisionDeleter::getChanges in RevisionDeleter.php
	public static $flags = array(
		self::HIDDEN_SUPPLEMENT => array(
			'name' => 'supplement',
			'hidden' => 'revcs-log-hidehistory-supplement-hidden',
			'unhidden' => 'revcs-log-hidehistory-supplement-unhidden',
		),
		self::HIDDEN_REASON => array(
			'name' => 'reason',
			'hidden' => 'revcs-log-hidehistory-reason-hidden',
			'unhidden' => 'revcs-log-hidehistory-reason-unhidden',
		),
		self::HIDDEN_USER => array(
			'name' => 'user',
			'hidden' => 'revdelete-uname-hid',
			'unhidden' => 'revdelete-uname-unhid',
		),
		self::HIDDEN_RESTRICTED => array(
			'name' => 'restricted',
			'hidden' => 'revcs-log-hidehistory-restricted',
			'unhidden' => 'revcs-log-hidehistory-unrestricted',
		),
		self::HIDDEN_ROW => array(
			'name' => 'row',
			'hidden' => 'revcs-log-hidehistory-row-hidden',
			'unhidden' => 'revcs-log-hidehistory-row-unhidden',
		),
	);

	/**
	 * @param int $id: history entry id
	 * @return object|bool
	 */
	public static function getRow( $id ) {
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp_history',
			'*',
			array( 'rcsh_id' => $id ),
			__METHOD__
		);

		if ( isset( $dbRow ) && isset( $dbRow->rcsh_id ) && ( $dbRow->rcsh_id == $id ) ) {
			return $dbRow;
		}

		return false;
	}

	/**
	 * @param int $id: history entry id
	 * @return bool
	 */
	public static function isExistRow( $id ) {
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp_history',
			'rcsh_id',
			array( 'rcsh_id' => $id ),
			__METHOD__
		);

		if ( isset( $dbRow ) && isset( $dbRow->rcsh_id ) && ( $dbRow->rcsh_id == $id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $revId: revision id
	 * @return bool
	 */
	public static function isExistHistory( $revId ) {
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp_history',
			'rcsh_rev_id',
			array( 'rcsh_rev_id' => $revId ),
			__METHOD__
		);

		if ( isset( $dbRow ) && isset( $dbRow->rcsh_rev_id ) && ( $dbRow->rcsh_rev_id == $revId ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $item: 'supplement', 'reason', 'user', 'restricted', 'row'
	 * @param int $flag: deleted flag
	 * @return bool
	 */
	public static function isHidden( $item, $flag ) {
		switch( $item ) {
			case 'supplement':
				return ( $flag & self::HIDDEN_SUPPLEMENT ) == self::HIDDEN_SUPPLEMENT;
			case 'reason':
				return ( $flag & self::HIDDEN_REASON ) == self::HIDDEN_REASON;
			case 'user':
				return ( $flag & self::HIDDEN_USER ) == self::HIDDEN_USER;
			case 'restricted':
				return ( $flag & self::HIDDEN_RESTRICTED ) == self::HIDDEN_RESTRICTED;
			case 'row':
				return ( $flag & self::HIDDEN_ROW ) == self::HIDDEN_ROW;
				bleak;
			default:
				throw new MWException( "Unknown type $item!" );
		}
	}

	/**
	 * @param string $id: history entry id
	 * @param User $user
	 * @param string $reason: reason
	 * @param int $hide: flag of delete log entry or suppress (or oversight) log entry
	 * @return bool
	 */
	public static function hide( $id, $user, $reason = '', $hide = 0 ) {
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp_history',
			'rcsh_rev_id,rcsh_hidden',
			array( 'rcsh_id' => $id ),
			__METHOD__
		);
		$hidden1 = 0;
		$revId = 0;
		if ( isset( $dbRow ) && isset( $dbRow->rcsh_hidden ) && isset( $dbRow->rcsh_rev_id ) ) {
			$hidden1 = $dbRow->rcsh_hidden;
			$revId = $dbRow->rcsh_rev_id;
		} else {
			return false;
		}

		$dbw = wfGetDB( DB_MASTER );
		$ret = $dbw->update(
			'rev_comment_supp_history',
			array( 'rcsh_hidden' => $hide ),
			array( 'rcsh_id' => $id ),
			__METHOD__
		);
		if ( !$ret ) {
			return false;
		}

		$logType = 'revisioncommentsupplement';
		$logAction = 'hidehistory';
		if ( self::isHidden( 'restricted', $hidden1 ) || self::isHidden( 'restricted', $hide ) ) {
			$logType = 'suppress';
			$logAction = 'revcommentsupplementhidehistory';
		}

		$logEntry = new ManualLogEntry( $logType, $logAction );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget(
			Title::makeTitleSafe( NS_SPECIAL, "RevisionCommentSupplement/{$revId}" )
		);
		$logEntry->setComment( $reason );
		$logEntry->setParameters( array(
				'4::revid' => $revId,
				'5::oldhidden' => $hidden1,
				'6::newhidden' => $hide,
				'7::historyid' => $id,
			)
		);
		$logid = $logEntry->insert();
		$to = RevisionCommentSupplementSetting::getLogPublishSetting( 'hidehistory' );
		if ( $to === true ) {
			$logEntry->publish( $logid );
		} elseif ( $to ) {
			$logEntry->publish( $logid, $to );
		}

		return true;
	}
}
