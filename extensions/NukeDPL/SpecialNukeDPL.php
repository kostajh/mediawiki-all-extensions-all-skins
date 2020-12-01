<?php

use MediaWiki\MediaWikiServices;

class SpecialNukeDPL extends SpecialPage {

	function __construct() {
		parent::__construct( 'NukeDPL', 'nukedpl' );
	}

	public function doesWrites() {
		return true;
	}

	function execute( $parser = null ) {
		$user = $this->getUser();
		if ( !$this->userCanExecute( $user ) ) {
			$this->displayRestrictionError();
		}

		$this->setHeaders();
		$this->outputHeader();

		$request = $this->getRequest();
		if ( $request->wasPosted() ) {
			$ids = $request->getArray( 'ids' );
			$query = $request->getText( 'query' );
			$reason = $request->getText( 'reason', $this->msg( 'nukedpl-defaultreason', $query )->inContentLanguage()->text() );
			if ( $query ) {
				$this->deleteForm( $query, $reason );
			} else {
				$this->doDelete( $ids, $reason );
			}
		} else {
			$this->queryForm();
		}
	}

	function queryForm() {
		$title = $this->getPageTitle();
		$output = $this->getOutput();

		$output->addWikiTextAsInterface( $this->msg( 'nukedpl-intro' )->text() );

		$output->addHTML( Xml::element( 'form', [ 'action' => $title->getLocalURL( 'action=submit' ), 'method' => 'post' ], null ) );
		$output->addHTML( Xml::element( 'textarea', [ 'name' => 'query', 'style' => 'width: 100%; height: 50em;' ], $this->msg( 'nukedpl-defaulttext' )->text() ) );
		$output->addHTML( Xml::element( 'br' ) );
		$output->addHTML( Xml::element( 'input', [ 'type' => 'submit', 'value' => $this->msg( 'nukedpl-candidatelist' )->text() ] ) );
		$output->addHTML( '</form>' );
	}

	function deleteForm( $query, $reason ) {
		$title = $this->getPageTitle();
		$output = $this->getOutput();

		$pages = $this->getPages( $query );
		$count = count( $pages );
		if ( $count == 0 ) {
			$output->addWikiTextAsInterface( $this->msg( 'nukedpl-nopages', $query )->text() );
			return $this->queryForm();
		}

		$output->addWikiTextAsInterface( $this->msg( 'nukedpl-list', $count, $query )->text() );

		$output->addHTML( Xml::element( 'form', [ 'action' => $title->getLocalURL( 'action=delete' ), 'method' => 'post' ], null ) );
		$output->addHTML( Xml::element( 'input', [ 'type' => 'submit', 'value' => $this->msg( 'nukedpl-nuke' )->text() ] ) );
		$output->addHTML( Xml::element( 'div', [], $this->msg( 'deletecomment' )->text() ) );
		$output->addHTML( Xml::element( 'input', [ 'name' => 'reason', 'value' => $reason, 'size' => 60 ] ) );
		$output->addHTML( '<ol>' );
		foreach ( $pages as $page ) {
			$page = Title::newFromText( $page );
			if ( $page and $page->isKnown() ) {
				$output->addHTML( '<li>' );
				$output->addHTML( Xml::element( 'input', [ 'type' => 'checkbox', 'name' => 'ids[]', 'value' => $page->getArticleID(), 'checked' => 'checked' ] ) );
				$output->addHTML( Linker::linkKnown( $page ) );
				$output->addHTML( '</li>' );
			}
		}
		$output->addHTML( '</ol>' );
		$output->addHTML( Xml::element( 'input', [ 'type' => 'submit', 'value' => $this->msg( 'nukedpl-nuke' )->text() ] ) );
		$output->addHTML( '</form>' );
	}

	function getPages( $query ) {
		$user = $this->getUser();
		$title = $this->getPageTitle();
		$query = trim( $query ) . PHP_EOL . "format=,$$$%PAGE%$$$,,"; // Enclose each title with $$$
		$parser = MediaWikiServices::getInstance()->getParser();
		$parserOptions = ParserOptions::newFromUser( $user );
		$parserOutput = $parser->parse( '<dpl>' . $query . '</dpl>', $title, $parserOptions, false, true );
		preg_match_all( '|\$\$\$(.+)\$\$\$|U', $parserOutput->getText(), $matches ); // Extract the titles from the output
		return $matches[1]; // Return an array of titles
	}

	function doDelete( $ids, $reason ) {
		foreach ( $ids as $id ) {
			$article = Article::newFromID( $id );
			$article->doDelete( $reason );
		}
	}

	protected function getGroupName() {
		return 'pagetools';
	}
}
