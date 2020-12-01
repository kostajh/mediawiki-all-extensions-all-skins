<?php

class SpecialTimeMachine extends SpecialPage {

	public function __construct() {
		parent::__construct( 'TimeMachine' );
	}

	/**
	 * @param Parser $parser
	 */
	public function execute( $parser ) {
		$request = $this->getRequest();
		$date = $request->getCookie( 'timemachine-date', null, date( 'Y-m-d' ) );
		if ( $request->wasPosted() ) {
			$date = $request->getVal( 'date' );
			$response = $request->response();
			$response->setCookie( 'timemachine-date', $date );
		}
		$output = $this->getOutput();
		$output->enableOOUI();
		$output->addHTML( '
			<p>' . wfMessage( 'timemachine-p1' )->escaped() . '</p>
			<form method="post">
			<input type="date" name="date" value="' . $date . '" />
			<button type="submit" class="mw-ui-button mw-ui-progressive">' . wfMessage( 'timemachine-button1' )->escaped() . '</button>
			</form>
			<p>' . wfMessage( 'timemachine-p2' )->escaped() . '</p>
			<p>' . wfMessage( 'timemachine-p3' )->escaped() . '</p>
			<form method="post">
			<input type="hidden" name="date" value="" />
			<button type="submit" class="mw-ui-button">' . wfMessage( 'timemachine-button2' )->escaped() . '</button>
			</form>
		' );
		$this->setHeaders();
	}

	/**
	 * This method redirects to the first revision before the time set by the user in Special:TimeMachine
	 * It would be better if instead of redirecting it changed the request on the fly, but I haven't found
	 * a way yet.
	 *
	 * @param Title &$title
	 * @param \Article &$article
	 * @param OutputPage &$output
	 * @param User &$user
	 * @param \WebRequest $request
	 * @param \MediaWiki $mediaWiki
	 */
	public static function onBeforeInitialize( &$title, &$article, &$output, &$user, $request, $mediaWiki ) {
		if ( $request->getVal( 'action', 'view' ) != 'view' ) {
			return;
		}

		$date = $request->getCookie( 'timemachine-date' );
		if ( !$date || $request->getBool( 'oldid' ) ) {
			return;
		}

		$dbr = wfGetDB( DB_REPLICA );

		$rev_timestamp = wfTimestamp( TS_UNIX, $date . ' 00:00:00' );
		$rev_page = $title->getArticleID();

		$rev_id = $dbr->selectField(
			'revision',
			'rev_id',
			[ 'rev_page' => $rev_page, 'rev_timestamp < ' . $dbr->timestamp( $rev_timestamp ) ],
			__METHOD__,
			[ 'ORDER BY' => 'rev_timestamp DESC', 'LIMIT' => 1 ]
		);

		// The page doesn't exist yet
		if ( !$rev_id ) {
			return;
		}

		// Redirect to the old revision of the page
		$rev = Revision::newFromId( $rev_id );
		$url = $rev->getTitle()->getLocalURL( [ 'oldid' => $rev_id ] );
		$output->redirect( $url );
	}
}
