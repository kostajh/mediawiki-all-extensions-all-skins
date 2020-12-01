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

$wgAutoloadClasses['RevisionCommentSupplement'] = __DIR__ . '/../RevisionCommentSupplement.class.php';
$wgAutoloadClasses['RevisionCommentSupplementHistory'] = __DIR__ . '/../RevisionCommentSupplement.class.php';
$wgAutoloadClasses['ViewRevisionCommentSupplementEdit'] = __DIR__ . '/ViewRevisionCommentSupplementEdit.php';
$wgAutoloadClasses['ViewRevisionCommentSupplementDelete'] = __DIR__ . '/ViewRevisionCommentSupplementDelete.php';
$wgAutoloadClasses['ViewRevisionCommentSupplementHideHistory'] = __DIR__ . '/ViewRevisionCommentSupplementHideHistory.php';

use MediaWiki\Revision\RevisionRecord;

class SpecialRevisionCommentSupplement extends SpecialPage {

	public $mTokenOk;
	public $mTokenOkExceptSuffix;

	public function __construct() {
		parent::__construct( 'RevisionCommentSupplement', 'supplementcomment', true );
	}

	public function doesWrites() {
		return true;
	}

	# from class SpecialAbuseFilter in SpecialAbuseFilter.php into extension AbuseFilter
	function execute( $subpage ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$user = $this->getUser();
		$view = 'error';
		$par = '';

		// Are we allowed?
		if( $user->isBlocked() ){
			throw new UserBlockedError( $user->getBlock() );
		}
		if ( !$user->isAllowed( 'supplementcomment' ) ) {
			throw new PermissionsError( 'supplementcomment' );
		}
		$this->checkReadOnly();

		$params = explode( '/', $subpage );

		// Filter by removing blanks.
		foreach ( $params as $index => $param ) {
			if ( $param === '' ) {
				unset( $params[$index] );
			}
		}
		$params = array_values( $params );

		if ( count( $params ) == 2 && $params[0] == 'delete' && is_numeric( $params[1] ) ) {
			$view = 'ViewRevisionCommentSupplementDelete';
			$par = $params[1];
		} elseif ( count( $params ) == 2 && $params[0] == 'hidehistory' && is_numeric( $params[1] ) ) {
			$view = 'ViewRevisionCommentSupplementHideHistory';
			$par = $params[1];
		} elseif ( count( $params ) == 2 && $params[0] == 'edit' && is_numeric( $params[1] ) ) {
			$view = 'ViewRevisionCommentSupplementEdit';
			$par = $params[1];
		} elseif ( count( $params ) == 1 && $params[0] == 'hidehistory' ) {
			$view = 'ViewRevisionCommentSupplementHideHistory';
		} elseif ( count( $params ) == 1 && $params[0] == 'edit' ) {
			$view = 'ViewRevisionCommentSupplementEdit';
		} elseif ( count( $params ) == 1 && is_numeric( $params[0] ) ) {
			$view = 'ViewRevisionCommentSupplementEdit';
			$par = $params[0];
		} elseif ( count( $params ) == 0 ) {
			$view = 'ViewRevisionCommentSupplementEdit';
		} else {
			$view = 'ViewRevisionCommentSupplementEdit';
			$par = 'error';
		}

		$v = new $view( $this, $par );
		$v->show();
	}

	/**
	 * @param int $revId: revision id
	 * @return string HTML
	 */
	function getRevisionCommentSupplement( $revId ) {
		$row = RevisionCommentSupplement::getRow( $revId );

		$s = "\n" . '<div class="revcs-rev-show">';

		if ( $row === false ) {
			$s = "\n<p>" . $this->msg( 'revcs-show-no-db-row', $revId )->escaped() . "</p>";
			$s .= "\n<ul>\n<li>" .
				$this->msg( 'revcs-show-revision' )
					->rawParams( $this->showRevisionHistoryLine( $revId ) )->escaped() .
				"</li>\n</ul>";
		} else {
			$supplement = '';
			if ( $row->rcs_supplement && ( $row->rcs_supplement != '*' ) ) {
				$supplement = Linker::formatComment( $row->rcs_supplement );
			}
			$s .= "\n<ul>\n";
			$s .= "<li>" .
				$this->msg( 'revcs-show-revision-id' )
					->rawParams(
						$row->rcs_rev_id,
						Linker::linkKnown(
							self::getTitleFor( 'Log' ),
							$this->msg( 'revcs-show-loglinktext' )->plain(),
							array(),
							array(
								'page' => "Special:RevisionCommentSupplement/{$row->rcs_rev_id}"
							)
						) . ' | ' .
						Linker::linkKnown(
							self::getTitleFor(
								'RevisionCommentSupplement', 'edit/' . $row->rcs_rev_id
							),
							$this->msg( 'revcs-show-editlinktext' )->plain()
						) . ' | ' .
						Linker::linkKnown(
							self::getTitleFor(
								'RevisionCommentSupplement', 'delete/' . $row->rcs_rev_id
							),
							$this->msg( 'revcs-show-deletelinktext' )->plain()
						)
					)
					->escaped() .
				"</li>\n";
			if ( RevisionCommentSupplementSetting::getHistorySetting() ) {
				if ( isset( $row->rcs_latest ) && $row->rcs_latest ) {
					$s .= "<li>" .
						$this->msg( 'revcs-show-history-id' )
							->rawParams(
								$row->rcs_latest,
								Linker::linkKnown(
									self::getTitleFor(
										'RevisionCommentSupplementList',
										'history/' . $row->rcs_rev_id
									),
									$this->msg( 'revcs-show-historylinktext' )->plain()
								)
							)
							->escaped() .
						"</li>\n";
				}
			}
			$s .= "<li>" .
				$this->msg( 'revcs-show-timestamp' )
					->rawParams(
						$this->getLanguage()->userTimeAndDate(
							$row->rcs_timestamp,
							$this->getUser()
						),
						$row->rcs_timestamp
					)
					->escaped() .
				"</li>\n";
			$s .= "<li>" .
				$this->msg( 'revcs-show-user' )
					->rawParams(
						Linker::userLink( $row->rcs_user, $row->rcs_user_text ),
						Linker::userToolLinks( $row->rcs_user, $row->rcs_user_text ),
						$row->rcs_user
					)
					->escaped() .
				"</li>\n";
			$s .= "<li>" .
				$this->msg( 'revcs-show-supplement-raw' )
					->plaintextParams( $row->rcs_supplement )->escaped() .
				"</li>\n";
			$s .= "<li>" .
				$this->msg( 'revcs-show-supplement-parsed' )->rawParams( $supplement )->escaped() .
				"</li>\n";
			$s .= "<li>" .
				$this->msg( 'revcs-show-revision' )
					->rawParams( $this->showRevisionHistoryLine( $revId ) )->escaped() .
				"</li>\n";
			$s .= "</ul>";
		}

		$s .= "\n</div>\n";

		return $s;
	}

	function getAlertMessage( $alert, $message ) {
		return Xml::element( 'p', array( 'class' => $alert ),
				$this->msg(
					"revcs-{$alert}", $this->msg( "revcs-alert-{$message}" )->plain()
				)->plain()
			) .
			"\n";
	}

	function getErrorMessage( $message ) {
		return Xml::element( 'p', array( 'class' => 'error' ),
				$this->msg( 'revcs-error', $this->msg( "revcs-error-{$message}" )->plain() )
					->plain()
			) .
			"\n";
	}

	# from EditPage::tokenOk in EditPage.php
	/**
	 * Make sure the form isn't faking a user's credentials.
	 *
	 * @param WebRequest $request
	 * @return bool
	 */
	function tokenOk( &$request ) {
		$user = $this->getUser();
		$token = $request->getVal( 'wpEditToken' );
		$this->mTokenOk = $user->matchEditToken( $token );
		$this->mTokenOkExceptSuffix = $user->matchEditTokenNoSuffix( $token );
		return $this->mTokenOk;
	}

	/**
	 * @param string|int $revId: revision id
	 * @return string HTML
	 */
	function showRevisionHistoryLine( $revId ) {
		$dbr = wfGetDB( DB_REPLICA );
		$db_row = $dbr->selectRow( 'revision', '*', array( 'rev_id' => $revId ), __METHOD__ );
		if ( !isset( $db_row ) || !isset( $db_row->rev_id ) ) {
			return $this->msg( 'revcs-alert-norevision' )->escaped();
		}
		if ( $db_row->rev_id != $revId ) {
			return $this->msg( 'revcs-error-unexpected' )->escaped();
		}

		# from HistoryPager::historyLine in HistryAction.php
		$rev = new Revision( $db_row );

		$s = '';
		$link = $this->revLink( $rev );
		$dirmark = $this->getLanguage()->getDirMark();

		$s .= "$link";
		$s .= $dirmark;
		$s .= ' <span class="history-user">' . Linker::revUserTools( $rev, true ) . "</span>";
		$s .= $dirmark;

		if ( $rev->isMinor() ) {
			$s .= ' ' . ChangesList::flag( 'minor' );
		}

		# from SpecialUndelete::formatRevisionRow in SpecialUndelete.php
		// Revision text size
		$size = $rev->getSize();
		if( !is_null( $size ) ) {
			$s .= ' ' . Linker::formatRevisionSize( $size );
		}

		$s .= Linker::revComment( $rev, false, true );

		return $s;
	}

	# from HistoryPager::revLink in HistryAction.php
	/**
	 * Create a link to view this revision of the page
	 *
	 * @param Revision $rev
	 * @return String
	 */
	function revLink( $rev ) {
		$date = $this->getLanguage()->userTimeAndDate( $rev->getTimestamp(), $this->getUser() );
		$date = htmlspecialchars( $date );
		if ( RevisionRecord::userCanBitfield(
			$rev->getVisibility(),
			RevisionRecord::DELETED_TEXT,
			$this->getUser()
		) ) {
			$link = Linker::linkKnown(
				$rev->getTitle(),
				$date,
				array(),
				array( 'oldid' => $rev->getId() )
			);
		} else {
			$link = $date;
		}
		if ( $rev->isDeleted( Revision::DELETED_TEXT ) ) {
			$link = "<span class=\"history-deleted\">$link</span>";
		}
		return $link;
	}

	protected function getGroupName() {
		return 'other';
	}
}
