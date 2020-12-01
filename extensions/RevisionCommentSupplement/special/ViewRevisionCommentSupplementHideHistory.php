<?php
/**
 * Copyright (C) 2012-2013 Burthsceh <burthsceh@gmail.com>
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

class ViewRevisionCommentSupplementHideHistory extends ContextSource {

	public $row, $rId = '', $hId = '', $misshId = '', $otherReason = '', $listReason = 'other';
	public $hidePrimary = false, $hideComment = false, $hideUser = false, $hideRestricted = false;
	public $hideRow = false;
	public $formtype, $submit = false, $error = '', $exist = false, $suppress = true, $canHide = true;
	public $mPage;
	public $mParam;
	public $mTokenOk;
	public $mTokenOkExceptSuffix;

	# from abstract class AbuseFilterView in AbuseFilterView.php into extension AbuseFilter
	/**
	 * @param SpecialPage $page
	 * @param array $params
	 */
	function __construct( $page, $param ) {
		$this->mPage = $page;
		$this->mParam = $param;
		$this->setContext( $this->mPage->getContext() );
	}

	function show() {
		$out = $this->getOutput();
		$user = $this->getUser();

		$out->setPageTitle( $this->msg( 'revcs-hidehistory-heading' )->plain() );

		if ( !$user->isAllowed( 'supplementcomment-restricted' ) ) {
			throw new PermissionsError( 'supplementcomment-restricted' );
		}
/*
		global $wgVersion;
		$oldVersion = version_compare( $wgVersion, '1.20', '<' );
		if ( $oldVersion ) {
			if ( !$this->getUser()->isAllowed( 'deleterevision' ) ) {
				$out->permissionRequired( 'deleterevision' );
				return;
			}
		} else {
			if ( !$this->getUser()->isAllowed( 'deletelogentry' ) ) {
				$out->permissionRequired( 'deleterevision' );
				return;
			}
		}
*/
		$this->importFormData( $this->mParam );

		$output = '';
		if ( $this->error ) {
			if ( $this->error == 'hId' ) {
				$output = $this->msg( 'revcs-alert-history-id', $this->misshId )->escaped();
			} else {
				$output = $this->msg( 'revcs-error-unexpected' )->escaped();
			}
		} else {
			if( $this->submit && $this->exist && $this->canHide ) {
				$output = $this->hideRevisionCommentSupplementHistory();
			}
			$output .= $this->getRevisionCommentSupplementHistory();
		}

		$form = '';
		if ( $this->submit || $this->exist ) {
			$form = $this->createForm();
		}
		$get = '';
		if ( $this->rId ) {
			$get = '<h2>'
				. $this->msg( 'revcs-hidehistory-present-supplement', $this->rId )
					->numParams( $this->rId )->escaped()
				. "</h2>\n";
			$get .= $this->mPage->getRevisionCommentSupplement( $this->rId );
		}

		$out->addHTML( $output );
		$out->addHTML( $this->msg( 'revcs-hidehistory-desc' )->parseAsBlock() );
		if( $this->getUser()->isAllowed( 'suppressrevision' ) ) {
			$out->addHTML( $this->msg( 'revdelete-suppress-text' )->parseAsBlock() );
		}
		$out->addHTML( $form );
		$out->addHTML( $get );

		return true;
	}

	# from EditPage::importFormData in EditPage.php
	/**
	 * This function collects the form data and uses it to populate various member variables.
	 *
	 * @param string $par
	 */
	function importFormData( $par ) {
		$request = $this->getRequest();

		if ( $request->wasPosted() ) {
			# Truncate for whole multibyte characters.
			$language = $this->getLanguage();
			$this->listReason = $language->truncateForDatabase(
				$request->getText( 'wpDeleteReasonList', 'other' ), 255
			);
			$this->otherReason = $language->truncateForDatabase(
				$request->getText( 'wpReason' ), 255
			);

			$tempId = $request->getInt( 'hID' );
			if ( $tempId > 0 ) {
				$this->hId = $tempId;
				$this->row = RevisionCommentSupplementHistory::getRow( $tempId );
				if ( $this->row ) {
					$this->exist = true;
					$this->suppress = RevisionCommentSupplementHistory::isHidden(
						'restricted', $this->row->rcsh_hidden
					);
					if ( $this->suppress ) {
						$canHide = $this->getUser()->isAllowed( 'suppressrevision' );
					}
					$this->rId = $this->row->rcsh_rev_id;
				}
			} else {
				$this->hId = '';
				$this->misshId = $request->getText( 'hID' );
				$this->error = 'hId';
			}

			$this->hidePrimary = $request->getCheck( 'hideSupplement' );
			$this->hideComment = $request->getCheck( 'hideReason' );
			$this->hideUser = $request->getCheck( 'wpHideUser' );
			$this->hideRestricted = $request->getCheck( 'wpHideRestricted' );
			$this->hideRow = $request->getCheck( 'hideRow' );
			$this->submit = $request->getCheck( 'submit' );
/*
			global $wgVersion;
			$oldVersion = version_compare( $wgVersion, '1.20', '<' );
			if ( $oldVersion ) {
				if ( !$this->getUser()->isAllowed( 'deleterevision' ) ) {
					$this->hidePrimary = false;
					$this->hideComment = false;
					$this->hideUser = false;
				}
			} else {
				if ( !$this->getUser()->isAllowed( 'deletelogentry' ) ) {
					$this->hidePrimary = false;
					$this->hideComment = false;
					$this->hideUser = false;
				}
			}
*/
			if ( !$this->getUser()->isAllowed( 'suppressrevision' ) ) {
				$this->hideRestricted = false;
			}

			if ( $this->mPage->tokenOk( $request ) ) {
				# Some browsers will not report any submit button
				# if the user hits enter in the comment box.
				# The unmarked state will be assumed to be a save,
				# if the form seems otherwise complete.
				wfDebug( __METHOD__ . ": Passed token check.\n" );
			} else {
				# Page might be a hack attempt posted from
				# an external site. Preview instead of saving.
				wfDebug( __METHOD__ . ": Failed token check; forcing preview\n" );
				$this->submit = false;
			}
		} else {
			# Not a posted form? Start with nothing.
			wfDebug( __METHOD__ . ": Not a posted form.\n" );
			$tempId = intval( $par );
			if ( $tempId > 0 ) {
				$this->hId = $tempId;
				$this->row = RevisionCommentSupplementHistory::getRow( $tempId );
				if ( $this->row ) {
					$this->exist = true;
					$this->suppress = RevisionCommentSupplementHistory::isHidden(
						'restricted', $this->row->rcsh_hidden
					);
					if ( $this->suppress ) {
						$canHide = $this->getUser()->isAllowed( 'suppressrevision' );
					}
					$this->rId = $this->row->rcsh_rev_id;
					$this->hidePrimary = RevisionCommentSupplementHistory::isHidden(
						'supplement', $this->row->rcsh_hidden
					);
					$this->hideComment = RevisionCommentSupplementHistory::isHidden(
						'reason', $this->row->rcsh_hidden
					);
					$this->hideUser = RevisionCommentSupplementHistory::isHidden(
						'user', $this->row->rcsh_hidden
					);
					$this->hideRestricted = $this->suppress;
					$this->hideRow = RevisionCommentSupplementHistory::isHidden(
						'row', $this->row->rcsh_hidden
					);
				}
			} else {
				$this->hId = '';
				$this->error = 'hId';
			}
			$this->submit = false;
		}
	}

	# from Article::confirmDelete in Article.php
	function createForm() {
		$action = $this->getTitle()->getLocalURL( array( 'action' => 'submit' ) );
		$form = Xml::openElement(
				'form',
				array(
					'method' => 'post',
					'action' => $action,
					'id' => 'hidesupplementarycommenthistory'
				)
			) .
			"\n";

		$form .= "<div>" .
			Xml::checkLabel(
				$this->msg( 'revcs-hidehistory-supplement' )->plain(),
				'hideSupplement', 'hideSupplement', $this->hidePrimary
			) . "</div>\n";

		$form .= "<div>" .
			Xml::checkLabel(
				$this->msg( 'revcs-hidehistory-reason' )->plain(),
				'hideReason', 'hideReason', $this->hideComment
			) . "</div>\n";

		$form .= "<div>" .
			Xml::checkLabel(
				$this->msg( 'revcs-hidehistory-user' )->plain(),
				'wpHideUser', 'wpHideUser', $this->hideUser
			) . "</div>\n";

		$form .= "<div><b>" .
			Xml::checkLabel(
				$this->msg( 'revcs-hidehistory-suppress' )->parse(),
				'wpHideRestricted', 'wpHideRestricted', $this->hideRestricted
			) . "</b></div>\n";

		$form .= "<div>" .
			Xml::checkLabel(
				$this->msg( 'revcs-hidehistory-row' )->plain(),
				'hideRow', 'hideRow', $this->hideRow
			) . "</div>\n";

		if ( $this->canHide ) {
			$form .= "<div>" .
				Xml::label( $this->msg( 'revdelete-log' )->plain(), 'wpRevDeleteReasonList' ) .
			Xml::listDropDown(
					'wpRevDeleteReasonList',
					$this->msg( 'revdelete-reason-dropdown' )->plain(),
					$this->msg( 'revdelete-reasonotherlist' )->plain(),
					$this->listReason,
					'wpReasonDropDown'
				) . "</div>\n";

			$form .= "<div>" .
				Xml::inputLabel(
					$this->msg( 'revdelete-otherreason' )->plain(),
					'wpReason', 'wpReason', 60, $this->otherReason,
					array( 'maxlength' => '255', 'spellcheck' => 'true', 'accesskey' => 'r' )
				) . "</div>\n";

			if ( $this->exist ) {
				$form .= "<div>" .
					Xml::submitButton(
						$this->msg( 'revcs-hidehistory-submit' )->plain(),
						array( 'id' => 'submit', 'name' => 'submit', 'accesskey' => 's' )
					) .
					"</div>\n";

			$form .= "<div>" .
				Xml::input(
					'hID', false,
					$this->hId,
					array( 'id' => 'hID', 'type' => 'hidden' )
				) .
				Xml::input(
					'wpEditToken', false,
					$this->getUser()->getEditToken(),
					array( 'id' => 'wpEditToken', 'type' => 'hidden' )
				) .
				"</div>\n";
			}
		}

		$form .= Xml::closeElement( 'form' );
		$form = Xml::fieldset( $this->msg( 'revcs-hidehistory-legend' )->plain(), $form );

		if ( $this->getUser()->isAllowed( 'editinterface' ) ) {
			$title = Title::makeTitle( NS_MEDIAWIKI, 'revdelete-reason-dropdown' );
			$link = Linker::link(
				$title,
				$this->msg( 'delete-edit-reasonlist' ),
				array(),
				array( 'action' => 'edit' )
			);
			$form .= '<p class="mw-delete-editreasons">' . $link . '</p>';
		}

		return $form;
	}

	/**
	 * @return string HTML
	 */
	function getRevisionCommentSupplementHistory() {
		$s = "\n" . '<div class="revcs-history-show">';

		if ( !$this->exist ) {
			$s .= "\n<p>" . $this->msg( 'revcs-alert-nohistory' )->escaped() . "</p>";
			$s .= "\n</div>\n";

			return $s;
		}

		$supplement = '';
		if ( $this->row->rcsh_supplement && ( $this->row->rcsh_supplement != '*' ) ) {
			$supplement = Linker::formatComment( $this->row->rcsh_supplement );
		}
		$reason = '';
		if ( $this->row->rcsh_reason && ( $this->row->rcsh_reason != '*' ) ) {
			$reason = Linker::formatComment( $this->row->rcsh_reason );
		}
		$s .= "\n<ul>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-revision-id' )
				->rawParams(
					$this->row->rcsh_rev_id,
					Linker::linkKnown(
						$this->mPage->getTitleFor( 'Log' ),
						$this->msg( 'revcs-show-loglinktext' )->plain(),
						array(),
						array(
							'page' => "Special:RevisionCommentSupplement/{$this->row->rcsh_rev_id}"
						)
					) . ' | ' .
					Linker::linkKnown(
						$this->mPage->getTitleFor(
							'RevisionCommentSupplement', 'edit/' . $this->row->rcsh_rev_id
						),
						$this->msg( 'revcs-show-editlinktext' )->plain()
					) . ' | ' .
					Linker::linkKnown(
						$this->mPage->getTitleFor(
							'RevisionCommentSupplement', 'delete/' . $this->row->rcsh_rev_id
						),
						$this->msg( 'revcs-show-deletelinktext' )->plain()
					)
				)
				->escaped() .
			"</li>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-history-id' )
				->rawParams(
					$this->row->rcsh_id,
					Linker::linkKnown(
						$this->mPage->getTitleFor(
							'RevisionCommentSupplementList', 'history/' . $this->row->rcsh_rev_id
						),
						$this->msg( 'revcs-show-historylinktext' )->plain()
					)
				)
				->escaped() .
			"</li>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-timestamp' )
				->rawParams(
					$this->getLanguage()->userTimeAndDate(
						$this->row->rcsh_timestamp,
						$this->getUser()
					),
					$this->row->rcsh_timestamp
				)
				->escaped() .
			"</li>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-user' )
				->rawParams(
					Linker::userLink( $this->row->rcsh_user, $this->row->rcsh_user_text ),
					Linker::userToolLinks( $this->row->rcsh_user, $this->row->rcsh_user_text ),
					$this->row->rcsh_user
				)
				->escaped() .
			"</li>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-supplement-raw' )
				->rawParams( htmlspecialchars( $this->row->rcsh_supplement ) )->escaped() .
			"</li>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-supplement-parsed' )->rawParams( $supplement )->escaped() .
			"</li>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-reason' )->rawParams( $reason )->escaped() .
			"</li>\n";
		$s .= "<li>" .
			$this->msg( 'revcs-show-revision' )
				->rawParams( $this->mPage->showRevisionHistoryLine( $this->row->rcsh_rev_id ) )
				->escaped() .
			"</li>\n";
		$s .= "</ul>";

		$s .= "\n</div>\n";

		return $s;
	}

	function hideRevisionCommentSupplementHistory() {
		$reason = '';
		if ( $this->listReason == 'other' ) {
			$reason = $this->otherReason;
		} elseif ( $this->otherReason != '' ) {
			// Entry from drop down menu + additional comment
			$reason = $this->getLanguage()->truncateForDatabase(
				$this->listReason . $this->msg( 'colon-separator' )->plain() . $this->otherReason,
				255
			);
		} else {
			$reason = $this->listReason;
		}

		$hide = 0;
		if ( $this->hidePrimary ) {
			$hide |= RevisionCommentSupplementHistory::HIDDEN_SUPPLEMENT;
		}
		if ( $this->hideComment ) {
			$hide |= RevisionCommentSupplementHistory::HIDDEN_REASON;
		}
		if ( $this->hideUser ) {
			$hide |= RevisionCommentSupplementHistory::HIDDEN_USER;
		}
		if ( $this->hideRestricted ) {
			$hide |= RevisionCommentSupplementHistory::HIDDEN_RESTRICTED;
		}
		if ( $this->hideRow ) {
			$hide |= RevisionCommentSupplementHistory::HIDDEN_ROW;
		}

		if ( $hide == $this->row->rcsh_hidden ) {
			return '<p class="error">' .
				$this->msg( 'revcs-error-hidehistory-hidden-same' )->escaped() .
				'</p>';
		}

		if ( $hide == RevisionCommentSupplementHistory::HIDDEN_RESTRICTED ) {
			return '<p class="error">' .
				$this->msg( 'revcs-error-hidehistory-hidden-restricted-only' )->escaped() .
				'</p>';
		}

		if ( RevisionCommentSupplementHistory::hide(
			$this->hId,
			$this->getUser(),
			$reason,
			$hide
		) ) {
			$this->getOutput()->setPageTitle( $this->msg( 'actioncomplete' )->plain() );
			return '<p class="success">' .
				$this->msg( 'revcs-hidehistory-success' )->escaped() .
				'</p>';
		} else {
			$this->getOutput()->setPageTitle( $this->msg( 'actionfailed' )->plain() );
			return '<p class="error">' .
				$this->msg( 'revcs-hidehistory-failure' )->escaped() .
				'</p>';
		}
	}
}
