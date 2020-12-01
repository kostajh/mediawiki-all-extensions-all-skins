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

class RevisionCommentSupplementHook {

	public static function onPageHistoryLineEnding( HistoryPager $history, $revRow, &$s ) {
		$revId = (int)$revRow->rev_id;
		$dbr = wfGetDB( DB_REPLICA );
		$dbRow = $dbr->selectRow(
			'rev_comment_supp',
			'rcs_supplement',
			array( 'rcs_rev_id' => $revId ),
			__METHOD__
		);

		if ( isset( $dbRow ) && isset( $dbRow->rcs_supplement ) ) {
			if ( $dbRow->rcs_supplement && ( $dbRow->rcs_supplement != '*' ) ) {
				# section link
				$supplement = Linker::formatComment(
					$dbRow->rcs_supplement/*,
					$history->getTitle(),
					false*/
				);
				$s .= '<span class="revcs-supplement">' .
					$history->msg( 'revcs-action-history-supplement' )
						->rawParams( $supplement )->escaped() .
					'</span>';
			}
		}

		return true;
	}

	public static function onLogLine( $log_type, $log_action, $title, $paramArray, &$comment, &$revert, $time ) {
		if (
			( $log_type == 'revisioncommentsupplement' && $log_action == 'hidehistory' )
			|| ( $log_type == 'suppress' && $log_action == 'revcommentsupplementhidehistory' )
		) {
			$params = unserialize( $paramArray[0] );
			$revert = '(' .
				Linker::linkKnown(
					SpecialPage::getTitleFor(
						'RevisionCommentSupplement',
						'hidehistory/' . intval( $params['7::historyid'] )
					),
					wfMessage( 'revdel-restore' )->plain()
				) . ')';
		}

		return true;
	}

	/**
	 * Hook on LoadExtensionSchemaUpdates
	 */
	# from Extension:TitleKey, author Brion Vibber
	public static function runUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__ . '/sql';
		$dbType = $updater->getDB()->getType();

		if( $updater->getDB()->tableExists( 'rev_comment_supp' ) ) {
			$updater->output( "...rev_comment_supp table already exists.\n" );
			if (
				$updater->getDB()->fieldExists( 'rev_comment_supp', 'rcs_user_name', __METHOD__ )
			) {
				$updater->output( "...update rev_comment_supp table on version 0.3.0...\n" );
				if ( $dbType == 'mysql' ) {
					$updater->addExtensionUpdate(
						array(
							'modifyField',
							'rev_comment_supp',
							'rcs_user_name',
							"$dir/patch-rev_comment_supp.sql",
							true
						)
					);
				} elseif ( $dbType == 'postgres' ) {
					$updater->addExtensionUpdate(
						array(
							'modifyField',
							'rev_comment_supp',
							'rcs_user_name',
							"$dir/patch-rev_comment_supp.pg.sql",
							true
						)
					);
				}
			}
			if (
				$updater->getDB()->fieldExists( 'rev_comment_supp', 'rcs_comment', __METHOD__ )
			) {
				$updater->output( "...update rev_comment_supp table on version 0.4.0...\n" );
				if ( $dbType == 'mysql' ) {
					$updater->addExtensionUpdate(
						array(
							'modifyField',
							'rev_comment_supp',
							'rcs_comment',
							"$dir/patch-supplement.sql",
							true
						)
					);
				} elseif ( $dbType == 'postgres' ) {
					$updater->addExtensionUpdate(
						array(
							'modifyField',
							'rev_comment_supp',
							'rcs_comment',
							"$dir/patch-supplement.pg.sql",
							true
						)
					);
				}
			}
			if (
				RevisionCommentSupplementSetting::getHistorySetting()
				&& !$updater->getDB()->tableExists( 'rev_comment_supp_history' )
			) {
				$updater->output( "...create rev_comment_supp_history table...\n" );
				if ( $dbType == 'mysql' ) {
					$updater->addExtensionUpdate(
						array(
							'addTable',
							'rev_comment_supp_history',
							"$dir/patch-rev_comment_supp_history.sql",
							true
						)
					);
				} elseif ( $dbType == 'postgres' ) {
					$updater->addExtensionUpdate(
						array(
							'addTable',
							'rev_comment_supp_history',
							"$dir/patch-rev_comment_supp_history.pg.sql",
							true
						)
					);
				}
			}
		} else {
			$updater->output( "...create rev_comment_supp table...\n" );
			if ( $dbType == 'mysql' ) {
				$updater->addExtensionUpdate(
					array(
						'addTable',
						'rev_comment_supp',
						"$dir/rev_comment_supp.sql",
						true
					)
				);
			} elseif ( $dbType == 'postgres' ) {
				$updater->addExtensionUpdate(
					array(
						'addTable',
						'rev_comment_supp',
						"$dir/rev_comment_supp.pg.sql",
						true
					)
				);
			}
			if ( RevisionCommentSupplementSetting::getHistorySetting() ) {
				$updater->output( "...create rev_comment_supp_history table...\n" );
				if ( $dbType == 'mysql' ) {
					$updater->addExtensionUpdate(
						array(
							'addTable',
							'rev_comment_supp_history',
							"$dir/rev_comment_supp_history.sql",
							true
						)
					);
				} elseif ( $dbType == 'postgres' ) {
					$updater->addExtensionUpdate(
						array(
							'addTable',
							'rev_comment_supp_history',
							"$dir/rev_comment_supp_history.pg.sql",
							true
						)
					);
				}
			}
		}

		return true;
	}
}
