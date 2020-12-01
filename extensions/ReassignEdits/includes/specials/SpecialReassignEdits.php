<?php
/**
 * ReassignEdits
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author: Tim 'SVG' Weyer <SVG@Wikiunity.com>
 *
 * @copyright Copyright (C) 2011 Tim Weyer, Wikiunity
 * @license GPL-2.0-or-later
 */

class SpecialReassignEdits extends SpecialPage {
	public function __construct() {
		parent::__construct( 'ReassignEdits', 'reassignedits' );
	}

	public function doesWrites() {
		return true;
	}

	/**
	 * Show the special page
	 *
	 * @param mixed $par Parameter passed to the page
	 * @throws UserBlockedError
	 */
	public function execute( $par ) {
		global $wgContLang;

		$this->setHeaders();
		$out = $this->getOutput();
		$out->addWikiMsg( 'reassignedits-summary' );

		// If the user doesn't have 'reassignedits' permission, display an error
		$user = $this->getUser();
		if ( !$user->isAllowed( 'reassignedits' ) ) {
			$this->displayRestrictionError();
			return;
		}

		// Show a message if the database is in read-only mode
		$this->checkReadOnly();

		// If user is blocked, they don't need to access this page
		if ( $user->getBlock() ) {
			throw new UserBlockedError( $user->getBlock() );
		}

		$request = $this->getRequest();
		$oldnamePar = trim( str_replace( '_', ' ', $request->getText( 'oldusername', $par ) ) );
		$oldusername = Title::makeTitle( NS_USER, $oldnamePar );
		// Force uppercase of newusername, otherwise wikis with wgCapitalLinks=false can create lc usernames
		$newusername = Title::makeTitleSafe( NS_USER, $wgContLang->ucfirst( $request->getText( 'newusername' ) ) );
		$oun = is_object( $oldusername ) ? $oldusername->getText() : '';
		$nun = is_object( $newusername ) ? $newusername->getText() : '';
		$token = $user->getEditToken();

		$updatelogging_user = $request->getBool( 'updatelogginguser', !$request->wasPosted() );
		$updatelogging_title = $request->getCheck( 'updateloggingtitle' );

		$out->addHTML(
			Xml::openElement( 'form', [
				'method' => 'post',
				'action' => $this->getPageTitle()->getLocalUrl(),
				'id' => 'reassignedits'
			] ) .
				Xml::openElement( 'fieldset' ) .
				Xml::element( 'legend', null, $this->msg( 'reassignedits' )->text() ) .
				Xml::openElement( 'table', [ 'id' => 'mw-reassignedits-table' ] ) .
				"<tr>
				<td class='mw-label'>" .
				Xml::label( $this->msg( 'reassignedits-old' )->text(), 'oldusername' ) .
				"</td>
				<td class='mw-input'>" .
				Xml::input( 'oldusername', 20, $oun, [ 'type' => 'text', 'tabindex' => '1' ] ) . ' ' .
				"</td>
			</tr>
			<tr>
				<td class='mw-label'>" .
				Xml::label( $this->msg( 'reassignedits-new' )->text(), 'newusername' ) .
				"</td>
				<td class='mw-input'>" .
				Xml::input( 'newusername', 20, $nun, [ 'type' => 'text', 'tabindex' => '2' ] ) .
				"</td>
			</tr>"
		);
		$out->addHTML( "
			<tr>
				<td>&#160;
				</td>
				<td class='mw-input'>" .
				Xml::checkLabel( $this->msg( 'reassignedits-updatelog-user' )->text(),
					'updatelogginguser', 'updatelogginguser', $updatelogging_user,
					[ 'tabindex' => '3' ] ) .
				"</td>
			</tr>"
		);
		$out->addHTML( "
			<tr>
				<td>&#160;
				</td>
				<td class='mw-input'>" .
				Xml::checkLabel(
					$this->msg( 'reassignedits-updatelog-title' )->text(), 'updateloggingtitle',
					'updateloggingtitle', $updatelogging_title, [ 'tabindex' => '4' ] ) .
				"</td>
			</tr>"
		);
		$out->addHTML( "
			<tr>
				<td>&#160;
				</td>
				<td class='mw-submit'>" .
				Xml::submitButton( $this->msg( 'reassignedits-submit' )->text(),
					[ 'name' => 'submit', 'tabindex' => '5', 'id' => 'submit' ] ) .
				"</td>
			</tr>" .
			Xml::closeElement( 'table' ) .
			Xml::closeElement( 'fieldset' ) .
			Html::hidden( 'token', $token ) .
			Xml::closeElement( 'form' ) . "\n"
		);

		if ( $request->getText( 'token' ) === '' ) {
			// They probably haven't even submitted the form, so don't go further
			return;
		} elseif ( !$request->wasPosted() || !$user->matchEditToken( $request->getVal( 'token' ) ) ) {
			$this->outputWikiText( "<div class=\"errorbox\">" .
				$this->msg( 'reassignedits-error-request' )->text() . "</div>" );
			return;
		} elseif ( !is_object( $oldusername ) ) {
			$this->outputWikiText(
				"<div class=\"errorbox\">"
					. $this->msg( 'reassignedits-error-invalid',
						"<nowiki>" . $request->getText( 'oldusername' ) . "</nowiki>" )->text()
					. "</div>"
			);
			return;
		} elseif ( !is_object( $newusername ) ) {
			$this->outputWikiText(
				"<div class=\"errorbox\">"
					. $this->msg( 'reassignedits-error-invalid',
						"<nowiki>" . $request->getText( 'newusername' ) . "</nowiki>" )->text()
					. "</div>"
			);
			return;
		}

		// Get usernames by id
		$newuser = User::newFromName( $newusername->getText() );

		// It won't be an object if for instance "|" is supplied as a value
		if ( !is_string( $oldusername->getText() ) ) {
			$this->outputWikiText( "<div class=\"errorbox\">" . $this->msg( 'reassignedits-error-invalid',
				"<nowiki>" . $oldusername->getText() . "</nowiki>" )->text() . "</div>" );
			return;
		}
		if ( !is_object( $newuser ) ) {
			$this->outputWikiText( "<div class=\"errorbox\">" . $this->msg( 'reassignedits-error-invalid',
				"<nowiki>" . $newusername->getText() . "</nowiki>" )->text() . "</div>" );
			return;
		}

		$settings = [];
		// Update user in logging table if checkbox is true
		$settings['updatelogginguser'] = $request->getCheck( 'updatelogginguser' );
		// Update title in logging table if checkbox is true
		$settings['updatelogginguser'] = $request->getCheck( 'updateloggingtitle' );

		// Do the heavy lifting...
		$reassign = new ReassignEditsSQL(
			$oldusername->getText(),
			$newusername->getText(),
			$settings
		);
		if ( !$reassign->reassign() ) {
			return;
		}

		// Output success message
		$this->outputWikiText( "<div class=\"successbox\">" . $this->msg( 'reassignedits-success',
			"<nowiki>" . $oldusername->getText() . "</nowiki>", "<nowiki>" . $newusername->getText() . "</nowiki>" )->text() .
			"</div><br style=\"clear:both\" />" );
	}

	/**
	 * @param string $wikitext
	 */
	private function outputWikiText( $wikitext ) {
		$output = $this->getOutput();
		if ( method_exists( $output, 'addWikiTextAsInterface' ) ) {
			// MW 1.32+
			$output->addWikiTextAsInterface( $wikitext );
		} else {
			$output->addWikiText( $wikitext );
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function getGroupName() {
		return 'users';
	}
}
