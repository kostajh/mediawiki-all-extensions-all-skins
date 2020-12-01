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

class ViewRevisionCommentSupplementDelete extends ContextSource {

	public $rId = '', $missrId = '', $otherReason = '', $listReason = 'other';
	public $formtype, $submit = false, $error = '', $exist = false;
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

		$out->setPageTitle( $this->msg( 'revcs-delete-heading' )->plain() );

		if ( !$user->isAllowed( 'supplementcomment-restricted' ) ) {
			throw new PermissionsError( 'supplementcomment-restricted' );
		}

		$this->importFormData( $this->mParam );

		$output = '';
		if ( $this->error ) {
			if ( $this->error == 'rId' ) {
				$output = $this->msg( 'revcs-alert-revision-id', $this->missrId )->escaped();
			} else {
				$output = $this->msg( 'revcs-error-unexpected' )->escaped();
			}
		} elseif( $this->submit && $this->exist ) {
			$output = $this->deleteRevisionCommentSupplement();
		}

		$form = '';
		if ( $this->submit || $this->exist ) {
			$form = $this->createForm();
		}
		$get = '';
		if ( $this->rId ) {
			$get = $this->mPage->getRevisionCommentSupplement( $this->rId );
		}

		$out->addHTML( $this->msg( 'revcs-delete-desc' )->parseAsBlock() );
		$out->addHTML( $output );
		$out->addHTML( $form );
		$out->addHTML( $get );

		return true;
	}

	# from EditPage::importFormData in EditPage.php
	/**
	 * This function collects the form data and uses it to populate various member variables.
	 *
	 * @param $par string
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

			$tempId = $request->getInt( 'rID' );
			if ( $tempId > 0 ) {
				$this->rId = $tempId;
				$this->exist = RevisionCommentSupplement::isExistRow( $tempId );
			} else {
				$this->rId = '';
				$this->missrId = $request->getText( 'rID' );
				$this->error = 'rId';
			}

			$this->submit = $request->getCheck( 'submit' );

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
				$this->rId = $tempId;
				$this->exist = RevisionCommentSupplement::isExistRow( $tempId );
			} else {
				$this->rId = '';
				$this->error = 'rId';
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
					'id' => 'deletesupplementarycomment'
				)
			) .
			"\n";

		$form .= "<div>" .
			Xml::label( $this->msg( 'deletecomment' )->plain(), 'wpDeleteReasonList' ) .
			Xml::listDropDown(
				'wpDeleteReasonList',
				$this->msg( 'deletereason-dropdown' )->plain(),
				$this->msg( 'deletereasonotherlist' )->plain(),
				$this->listReason,
				'wpReasonDropDown'
			) . "</div>\n";

		$form .= "<div>" .
			Xml::inputLabel(
				$this->msg( 'deleteotherreason' )->plain(),
				'wpReason', 'wpReason', 60, $this->otherReason,
				array( 'maxlength' => '255', 'spellcheck' => 'true', 'accesskey' => 'r' )
			) . "</div>\n";

		if ( $this->exist ) {
			$form .= "<div>" .
				Xml::submitButton(
					$this->msg( 'revcs-delete-submit' )->plain(),
					array( 'id' => 'submit', 'name' => 'submit', 'accesskey' => 's' )
				) .
				"</div>\n";

		$form .= "<div>" .
			Xml::input(
				'rID', false,
				$this->rId,
				array( 'id' => 'rID', 'type' => 'hidden' )
			) .
			Xml::input(
				'wpEditToken', false,
				$this->getUser()->getEditToken(),
				array( 'id' => 'wpEditToken', 'type' => 'hidden' )
			) .
			"</div>\n";
		}

		$form .= Xml::closeElement( 'form' );
		$form = Xml::fieldset( $this->msg( 'revcs-delete-legend' )->plain(), $form );

		if ( $this->getUser()->isAllowed( 'editinterface' ) ) {
			$title = Title::makeTitle( NS_MEDIAWIKI, 'deletereason-dropdown' );
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

	function deleteRevisionCommentSupplement() {
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

		RevisionCommentSupplement::delete( $this->rId, $this->getUser(), $reason );

		$this->exist = RevisionCommentSupplement::isExistRow( $this->rId );
		if ( $this->exist ) {
			$this->getOutput()->setPageTitle( $this->msg( 'actionfailed' )->plain() );
			return '<p class="error">' . $this->msg( 'revcs-delete-failure' )->escaped() . '</p>';
		} else {
			$this->getOutput()->setPageTitle( $this->msg( 'actioncomplete' )->plain() );
			return '<p class="success">' .
				$this->msg( 'revcs-delete-success' )->escaped() .
				'</p>';
		}
	}
}
