<?php

use MediaWiki\Shell\Shell;

class SpecialGoToShell extends SpecialPage {
	public function __construct() {
		parent::__construct( 'GoToShell' );
	}

	/**
	 * @param string $par
	 */
	public function execute( $par ) {
		global $wgGoToShellCommand;
		$user = $this->getUser();
		// Missing handbasket, er, permission
		if ( !$user->isAllowed( 'gotoshell' ) ) {
			// Shell no, this user won't go
			throw new PermissionsError( null, [ [
				'gotoshell-notallowed' ] ] );
		}
		$this->setHeaders();
		$this->outputWikiText( "<big>'''" . wfMessage( 'gotoshell-command' )
			. "'''</big><br>$wgGoToShellCommand<br><br>"
			. "<big>'''" . wfMessage( 'gotoshell-result' ) . "'''</big><br>" );
		// To shell with this user!
		// Some shell commands (ex. mediawiki maintenance scripts) might write to both stdout and
		// stderr. Capture and output both, with errors before normal output for greater visibility.
		if ( Shell::isDisabled() ) {
			$this->outputWikiText( wfMessage( 'gotoshell-disabled' ) );
		} else {
			$result = Shell::command( [] )
				->unsafeParams( $wgGoToShellCommand )
				->restrict( Shell::RESTRICT_NONE )
				->execute();

			$this->processOutput( $result->getStderr() );
			$this->processOutput( $result->getStdout() );
		}
	}

	/**
	 * @param ?string $rawOutput
	 * @throws MWException
	 */
	protected function processOutput( $rawOutput ) {
		if ( $rawOutput ) {
			foreach ( explode( "\n", $rawOutput ) as $line ) {
				$this->outputWikiText( $line );
			}
		}
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
		return 'other';
	}
}
