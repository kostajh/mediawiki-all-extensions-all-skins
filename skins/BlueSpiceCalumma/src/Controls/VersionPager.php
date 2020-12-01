<?php

namespace BlueSpice\Calumma\Controls;

use MediaWiki\Storage\RevisionRecord;
use Message;

class VersionPager extends TemplateControl {

	/**
	 *
	 * @var \Language
	 */
	protected $userLang = null;

	/**
	 *
	 * @var \MediaWiki\Storage\RevisionLookup
	 */
	protected $revisionLookup = null;

	/**
	 *
	 * @var \Title
	 */
	protected $title = null;

	/**
	 *
	 * @var int
	 */
	protected $oldId = -1;

	/**
	 *
	 * @var RevisionRecord
	 */
	protected $currentRevision = null;

	/**
	 * @param \MediaWiki\Storage\RevisionLookup $revisionLookup
	 * @param \Language $userLang
	 * @param \Title $title
	 * @param int $oldId
	 */
	public function __construct( $revisionLookup, $userLang, $title, $oldId ) {
		$this->revisionLookup = $revisionLookup;
		$this->userLang = $userLang;
		$this->title = $title;
		$this->oldId = $oldId;
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplatePathName() {
		return 'Calumma.Controls.VersionPager';
	}

	/**
	 *
	 * @return array
	 */
	protected function getTemplateArgs() {
		$this->initCurrentRevision();
		$prevHref = $this->makePrevHref();
		$nextHref = $this->makeNextHref();

		$args = [
			'info' => $this->makeInfoHTML()
		];

		if ( $prevHref !== '' ) {
			$args['prev'] = [
				'href' => $prevHref,
				'title' => Message::newFromKey( 'bs-calumma-version-pager-prev-tooltip' )->plain()
			];
		}

		if ( $nextHref !== '' ) {
			$args['next'] = [
				'href' => $nextHref,
				'title' => Message::newFromKey( 'bs-calumma-version-pager-next-tooltip' )->plain()
			];
		}

		return $args;
	}

	/**
	 *
	 * @return string
	 */
	protected function makeInfoHTML() {
		$user = $this->currentRevision->getUser();
		$timestamp = $this->currentRevision->getTimestamp();

		$userName = $user->getName();
		$formattedTimestamp = $this->userLang->timeanddate( $timestamp, true );

		$message = Message::newFromKey( 'bs-calumma-version-pager-info-text' );
		$message->params( $formattedTimestamp, $userName );
		return $message->parse();
	}

	private function initCurrentRevision() {
		$this->currentRevision = $this->revisionLookup->getRevisionById( $this->oldId );
	}

	private function makePrevHref() {
		$prevOldId = $this->title->getPreviousRevisionID( $this->oldId );
		if ( $prevOldId ) {
			return $this->title->getLinkURL( [ 'oldid' => $prevOldId ] );
		}
		return '';
	}

	private function makeNextHref() {
		$nextOldId = $this->title->getNextRevisionID( $this->oldId );
		if ( $nextOldId ) {
			return $this->title->getLinkURL( [ 'oldid' => $nextOldId ] );
		}
		return '';
	}

}
