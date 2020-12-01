<?php

namespace MediaWiki\Extensions\NewUserActions\Actions;

use ContentHandler;
use MediaWiki\Extensions\NewUserActions\Config;
use RuntimeException;
use Title;
use User;
use WikiPage;

class CreateNewUserPage extends CreateWikiPage {

	public function run() {
		$this->setPageProperties();

		if ( $this->content !== "" ) {
			// Don't create the page if content can't be found
			// (ie template doesn't exist).
			parent::run();
		}
	}

	///
	/// Set page properties: editor, title, content and edit summary
	///

	protected function setPageProperties() {
		$this->setEditor();
		$this->setTitle();
		$this->setContent();
		$this->setFlags();
		$this->setEditSummary();
	}

	protected function setEditor() {
		try {
			$this->editor = $this->fetchEditor();
		} catch ( RuntimeException $ex ) {
			// TODO: consider some reporting to Sentry/Logstash
			// (but currently MediaWiki doesn't have such report feature)
			wfDebug( __METHOD__ . ": Can't fetch editor.\n" );
			return;
		}
	}

	protected function setTitle() {
		$this->title = $this->user->getUserPage();
	}

	protected function setContent() {
		$template = Config::getLocalisedMessage( 'newuseractions-userpage-template' );
		$this->content = $this->fetchPageContent( $template );
	}

	protected function setFlags() {
		$this->flags = $this->flags | Config::getEditFlags();
	}

	protected function setEditSummary() {
		$this->editSummary = Config::getLocalisedMessage( 'newuseractions-userpage-summary' );
	}

	///
	/// Helper methods
	///

	/**
	 * Gets the editor to use, adding it to the database if necessary.
	 *
	 * @return User
	 */
	static function fetchEditor() {
		// Create a user object for the editing user and add it to the
		// database if it is not there already
		$editorName = Config::getLocalisedMessage( 'newuseractions-editor' );
		$editor = User::newFromName( $editorName );

		if ( $editor === false ) {
			throw new RuntimeException( "The editor username is invalid." );
		}

		if ( !$editor->isLoggedIn() ) {
			$editor->addToDatabase();
		}

		return $editor;
	}

	/**
	 * Fetches page content.
	 *
	 * @param string $pageTitle The page title
	 * @param string The page content or "" if the page isn't found
	 */
	private function fetchPageContent( $pageTitle ) {
		$title = Title::newFromText( $pageTitle );
		if ( $title === null ) {
			wfDebug( __METHOD__ . ": '$pageTitle' is not a valid title.\n" );
			return "";
		}

		$page = WikiPage::factory( $title );
		$content = $page->getContent();
		return ContentHandler::getContentText( $content );
	}

}
