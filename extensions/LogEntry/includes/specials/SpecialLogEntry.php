<?php
/**
 * Special Page for the LogEntry extension
 *
 * @file
 * @ingroup Extensions
 */

class SpecialLogEntry extends UnlistedSpecialPage {

	public function __construct() {
		// Register the special page as unlisted
		parent::__construct( 'LogEntry' );
	}

	/**
	 * @param string|null $par
	 */
	public function execute( $par ) {
		global $wgRequest, $wgOut;
		global $egLogEntryUserName, $egLogEntryTimeStamp;

		// Begin output
		$this->setHeaders();

		// Get page
		$page = $wgRequest->getText( 'page' );

		// Check that the form was submitted
		if ( $wgRequest->wasPosted() ) {
			// Check token
			if ( !$this->getUser()->matchEditToken( $wgRequest->getText( 'token' ) ) ) {
				// Alert of invalid page
				$wgOut->addWikiMsg( 'logentry-invalidtoken' );
				return;
			}

			// Get title
			$title = Title::newFromText( $page );

			$userCan = false;

			// Check permissions
			if ( $title ) {
				if ( class_exists( 'MediaWiki\Permissions\PermissionManager' ) ) {
					// MW 1.33+
					$userCan = MediaWikiServices::getInstance()
						->getPermissionManagar()
						->userCan( 'edit', $this->getUser(), $title );
				} else {
					$userCan = $title->userCan( 'edit' );
				}
			}

			if ( $userCan ) {
				// Get article
				$article = new Article( $title, 0 );

				// Build new line
				$newLine = '*';
				if ( $egLogEntryUserName ) {
					$newLine .= ' ' . $this->getUser()->getName();
				}
				if ( $egLogEntryTimeStamp ) {
					$newLine .= ' ' . gmdate( 'H:i' );
				}
				$newLine .= $this->msg( 'colon-separator' )->inContentLanguage()->text() .
						str_replace( "\n", '<br />',
						trim( htmlspecialchars( $wgRequest->getText( 'line' ) ) ) );

				// Get content without logentry tag in it
				$contentObj = $article->getContentObject();
				$content = ContentHandler::getContentText( $contentObj );

				// Detect section date
				$contentLines = explode( "\n", $content );

				// Build heading
				$heading = sprintf( '== %s ==', gmdate( 'F j' ) );

				// Find line of first section
				$sectionLine = false;
				foreach ( $contentLines as $i => $contentLine ) {
					// Look for == starting at the first character
					if ( strpos( $contentLine, '==' ) === 0 ) {
						$sectionLine = $i;
						break;
					}
				}

				// Assemble final output
				$output = '';
				if ( $sectionLine !== false ) {
					// Lines up to section
					$preLines = array_slice( $contentLines, 0, $sectionLine );

					// Lines after section
					$postLines = array_slice( $contentLines, $sectionLine + 1 );

					// Output Lines
					$outputLines = [];

					if ( trim( $contentLines[$sectionLine] ) == $heading ) {
						// Top section is current
						$outputLines = array_merge(
							$preLines,
							[
								$contentLines[$sectionLine],
								$newLine
							],
							$postLines
						);
					} else {
						// Top section is old
						$outputLines = array_merge(
							$preLines,
							[
								$heading,
								$newLine,
								$contentLines[$sectionLine]
							],
							$postLines
						);
					}
					$output = implode( "\n", $outputLines );
				} else {
					// There is no section, make one
					$output = sprintf( "%s\n%s\n%s", $content, $heading, $newLine );
				}

				// Edit article
				$article->quickEdit( $output );

				// Redirect
				$wgOut->redirect( $title->getPrefixedURL() );
			}
		}
		// Alert of invalid page
		$wgOut->addWikiMsg( 'logentry-invalidpage', $page );
	}
}
