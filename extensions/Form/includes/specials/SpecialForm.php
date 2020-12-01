<?php
/**
 * Form extension -- Use a form-based interface to start new articles
 * Copyright 2007 Vinismo, Inc. (http://vinismo.com/)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file
 * @ingroup Extensions
 * @author Evan Prodromou <evan@vinismo.com>
 */

class SpecialForm extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'Form', 'createpage' );
	}

	/**
	 * Return an array of subpages that this special page will accept.
	 *
	 * @return string[] subpages
	 */
	public function getSubpagesForPrefixSearch() {
		$forms = $this->getAllForms();
		if ( count( $forms ) ) {
			$retVal = $forms;
			sort( $retVal );
			return $retVal;
		}
		return [];
	}

	/**
	 * Show the special page
	 *
	 * @param string|int|null $par Parameter passed to the page
	 */
	public function execute( $par ) {
		$out = $this->getOutput();
		$request = $this->getRequest();

		$this->setHeaders();

		# Must have a name, like Special:Form/Nameofform
		if ( !$par ) {
			# Instead of an error, show a list of available forms
			$forms = $this->getAllForms();
			if ( count( $forms ) ) {
				$out->addWikiMsg( 'form-pick-form' );
				foreach ( $forms as $form ) {
					$out->addWikiTextAsInterface( "* [[Special:Form/{$form}|{$form}]]\n" );
				}
				return;
			} else {
				$out->showErrorPage( 'form-no-name', 'form-no-name-text' );
				return;
			}
		}

		$form = $this->loadForm( $par );

		# Bad form
		if ( !$form ) {
			$out->showErrorPage( 'form-bad-name', 'form-bad-name-text' );
			return;
		}

		if (
			$request->wasPosted() &&
			$this->getUser()->matchEditToken( $request->getVal( 'wpEditToken' ) )
		) {
			# POST is to create an article
			$this->createArticle( $form );
		} else {
			# GET (HEAD?) is to show the form
			$this->showForm( $form );
		}
	}

	/**
	 * Pull all the forms from the database
	 * @todo Should this be in the Form class instead? I'm not sure... --ashley, 12 November 2017
	 *
	 * @return array Array of form names suitable to be used on Special:Form
	 */
	private function getAllForms() {
		$dbr = wfGetDB( DB_REPLICA );
		// It Works On My Setup™
		// Sucks for you, users of alternative DBMSes.
		// I genuinely tried using $dbr->anyString() and $dbr->buildLike() here,
		// but I kept getting fatals as $dbr->anyString() returns a LikeMatch
		// object which apparently refuses to properly convert to a string. Fun!
		// --ashley, 12 November 2017
		$patternMsg = $this->msg( 'form-pattern' )->inContentLanguage();
		$like = 'LIKE ' . $dbr->addQuotes( $patternMsg->params( '%' )->text() );
		$res = $dbr->select(
			'page',
			'page_title',
			[
				'page_namespace' => NS_MEDIAWIKI,
				'page_title ' . $like
			],
			__METHOD__
		);
		$forms = [];
		if ( $dbr->numRows( $res ) === 0 ) {
			return $forms;
		}
		// can't reuse $patternMsg here because $1 will have been replaced by %
		// and that sucks :-(
		$strippable = str_replace( '$1', '', $this->msg( 'form-pattern' )->inContentLanguage()->text() );
		foreach ( $res as $row ) {
			// $row->page_title is "Test-form" for [[MediaWiki:Test-form]]
			// but the Special:Form entry is [[Special:Form/Test]] so we'll
			// need to strip out the -form suffix or whatever...
			$forms[] = str_replace( $strippable, '', $row->page_title );
		}
		return $forms;
	}

	/**
	 * Load and parse a form article from the DB
	 *
	 * @param string $name Form name
	 * @return null|Form New Form object on success, null when there is no form with the given name
	 */
	private function loadForm( $name ) {
		$nt = Title::makeTitleSafe( NS_MEDIAWIKI, $this->msg( 'form-pattern', $name )->inContentLanguage()->text() );

		# article exists?
		if ( !$nt || $nt->getArticleID() == 0 ) {
			return null;
		}

		$page = new WikiPage( $nt );
		$text = $page->getContent()->getNativeData();

		# Form constructor does the parsing
		return new Form( $name, $text );
	}

	private function showForm( $form, $errMsg = null ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		$self = $this->getPageTitle( $form->name );

		$out->setPageTitle( $form->title );

		if ( $form->instructions !== null ) {
			$out->addHTML(
				Xml::openElement( 'div', [ 'class' => 'instructions' ] ) .
				$out->parseAsInterface( $form->instructions ) .
				Xml::closeElement( 'div' ) .
				Xml::element( 'br' )
			);
		}

		if ( $errMsg !== null ) {
			$out->addHTML(
				Xml::openElement( 'div', [ 'class' => 'error' ] ) .
				$out->parseAsInterface( $errMsg ) .
				Xml::closeElement( 'div' ) .
				Xml::element( 'br' )
			);
		}

		$out->addHTML(
			Xml::openElement( 'form', [
					'method' => 'post',
					'action' => $self->getLocalURL()
				]
			)
		);

		foreach ( $form->fields as $field ) {
			$out->addHTML(
				$field->render( $request->getText( $field->name ) ) .
				Xml::element( 'br' ) . "\n"
			);
		}

		# CAPTCHA enabled?
		if ( $this->useCaptcha() ) {
			$out->addHTML( $this->getCaptcha() );
		}

		$out->addHTML(
			Html::hidden( 'wpEditToken', $user->getEditToken() ) .
			Xml::submitButton( $this->msg( 'form-save' )->text() )
		);

		$out->addHTML( Xml::closeElement( 'form' ) );
	}

	/**
	 * Attempt to create the page from the given form data
	 * Checks that all mandatory fields have a value as well as CAPTCHA (if enabled), etc.
	 *
	 * @param Form $form
	 */
	private function createArticle( $form ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		# Check ordinary CAPTCHA
		if ( $this->useCaptcha() && !ConfirmEditHooks::getInstance()->passCaptchaFromRequest( $request, $user ) ) {
			$msg = $this->msg( 'form-captcha-error' )->plain();
			$this->showForm( $form, $msg );
			return;
		}

		# Check for required fields
		$missedFields = [];

		foreach ( $form->fields as $name => $field ) {
			$value = $request->getText( $name );
			if (
				$field->isOptionTrue( 'required' ) &&
				( $value === null || strlen( $value ) == 0 )
			) {
				$missedFields[] = $field->label;
			}
		}

		# On error, show the form again with some error text.
		$missedFieldsCount = count( $missedFields );
		if ( $missedFieldsCount > 0 ) {
			$msg = $this->msg( 'form-required-field-error', $this->getLanguage()->listToText( $missedFields ), $missedFieldsCount )->parse();
			$this->showForm( $form, $msg );
			return;
		}

		# First, we make sure we have all the titles
		$nt = [];

		for ( $i = 0; $i < count( $form->template ); $i++ ) {
			$namePattern = $form->namePattern[$i];
			$template = $form->template[$i];

			if ( !$namePattern || !$template ) {
				$out->showErrorPage( 'form-index-mismatch-title', 'form-index-mismatch', [ $i ] );
				return;
			}

			wfDebug( __METHOD__ . ": for index '$i', namePattern = '$namePattern' and template = '$template'.\n" );

			$title = $this->makeTitle( $form, $namePattern );

			$nt[$i] = Title::newFromText( $title );

			if ( !$nt[$i] ) {
				$out->showErrorPage( 'form-bad-page-name', 'form-bad-page-name-text', [ $title ] );
				return;
			}

			if ( $nt[$i]->getArticleID() != 0 ) {
				$out->showErrorPage( 'form-article-exists', 'form-article-exists', [ $title ] );
				return;
			}
		}

		# At this point, all $nt titles should be valid, although we're subject to race conditions.
		for ( $i = 0; $i < count( $form->template ); $i++ ) {
			$template = $form->template[$i];

			$text = "{{subst:$template";

			foreach ( $form->fields as $name => $field ) {
				# FIXME: strip/escape template-related chars (|, =, }})
				$text .= "|$name=" . $request->getText( $name );
			}

			$text .= '}}';

			if ( !$this->checkSave( $nt[$i], $text ) ) {
				# Just break here; output already sent
				return;
			}

			$title = $nt[$i]->getPrefixedText();

			wfDebug( __METHOD__ . ": saving article with index '$i' and title '$title'\n" );

			$page = WikiPage::factory( $nt[$i] );

			$status = $page->doEditContent(
				ContentHandler::makeContent( $text, $page->getTitle() ),
				$this->msg( 'form-save-summary', $form->name )->text(),
				EDIT_NEW
			);

			if ( $status === false || is_object( $status ) && !$status->isOK() ) {
				$out->showErrorPage( 'form-save-error', 'form-save-error-text', [ $title ] );
				# Don't continue
				return;
			}
		}

		# Redirect to the first article
		if ( $nt && $nt[0] ) {
			$out->redirect( $nt[0]->getFullURL() );
		}
	}

	/**
	 * @param Form $form
	 * @param string $pattern RegEx pattern
	 * @return string
	 */
	private function makeTitle( $form, $pattern ) {
		$request = $this->getRequest();

		$title = $pattern;

		foreach ( $form->fields as $name => $field ) {
			$title = preg_replace(
				"/{{\{$name\}}}/",
				$request->getText( $name ),
				$title
			);
		}

		return $title;
	}

	/**
	 * Had to crib some checks from EditPage.php, since they're not done in Article.php
	 *
	 * @param Title $nt
	 * @param string $text
	 * @return bool True if all checks passed, otherwise false
	 */
	private function checkSave( $nt, $text ) {
		global $wgSpamRegex, $wgFilterCallback, $wgMaxArticleSize;

		$out = $this->getOutput();
		$user = $this->getUser();
		$matches = [];
		$errorText = '';
		$editSummary = '';

		$editPage = new FakeEditPage( $nt );

		# FIXME: more specific errors, copied from EditPage.php
		if ( $wgSpamRegex && preg_match( $wgSpamRegex, $text, $matches ) ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		} elseif ( $wgFilterCallback && $wgFilterCallback( $nt, $text, 0 ) ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		} elseif ( !Hooks::run( 'EditFilter', [ $editPage, $text, 0, &$errorText, $editSummary ] ) ) {
			# Hooks usually print their own error
			return false;
		} elseif ( $errorText != '' ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		} elseif ( $user->isBlockedFrom( $nt, false ) ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		} elseif ( (int)( strlen( $text ) / 1024 ) > $wgMaxArticleSize ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		} elseif ( !$user->isAllowed( 'edit' ) ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		} elseif ( wfReadOnly() ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		} elseif ( $user->pingLimiter() ) {
			$out->showErrorPage( 'form-save-error', 'form-save-error-text' );
			return false;
		}

		return true;
	}

	/**
	 * @return bool True if CAPTCHA should be used, false otherwise
	 */
	private function useCaptcha() {
		global $wgCaptchaClass, $wgCaptchaTriggers;

		return $wgCaptchaClass &&
			isset( $wgCaptchaTriggers['form'] ) &&
			$wgCaptchaTriggers['form'] &&
			!$this->getUser()->isAllowed( 'skipcaptcha' );
	}

	/**
	 * @return string CAPTCHA form HTML
	 */
	private function getCaptcha() {
		// NOTE: make sure we have a session. May be required for CAPTCHAs to work.
		\MediaWiki\Session\SessionManager::getGlobalSession()->persist();

		$captcha = ConfirmEditHooks::getInstance();
		$captcha->setTrigger( 'form' );
		$captcha->setAction( 'createpageviaform' );

		$formInformation = $captcha->getFormInformation();
		$formMetainfo = $formInformation;
		unset( $formMetainfo['html'] );
		$captcha->addFormInformationToOutput( $this->getOutput(), $formMetainfo );

		return '<div class="captcha">' .
			$formInformation['html'] .
			"</div>\n";
	}
}
