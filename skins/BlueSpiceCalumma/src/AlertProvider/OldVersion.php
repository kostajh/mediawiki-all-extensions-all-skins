<?php

namespace BlueSpice\Calumma\AlertProvider;

use BlueSpice\AlertProviderBase;
use BlueSpice\Calumma\Controls\VersionPager;
use BlueSpice\IAlertProvider;
use MediaWiki\MediaWikiServices;
use Message;

class OldVersion extends AlertProviderBase {

	/**
	 *
	 * @var int
	 */
	protected $oldId = -1;

	/**
	 *
	 * @return string
	 */
	public function getHTML() {
		$this->initOldId();

		if ( $this->isOldVersion() ) {
			$message = Message::newFromKey( 'bs-calumma-alert-oldpage' );
			$message->params( $this->skin->getTitle()->getArticleID() );

			$versionPagerHTML = $this->buildVersionPager();

			return $message->parse() . $versionPagerHTML;
		}

		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function getType() {
		return IAlertProvider::TYPE_INFO;
	}

	/**
	 *
	 * @return bool
	 */
	private function isOldVersion() {
		if ( $this->oldId < 1 ) {
			return false;
		}
		$currentRevId = $this->skin->getTitle()->getLatestRevID();
		if ( $this->oldId === $currentRevId ) {
			return false;
		}
		$revision = MediaWikiServices::getInstance()->getRevisionLookup()->getRevisionById(
			$this->oldId
		);
		if ( !$revision ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	protected function buildVersionPager() {
		$services = MediaWikiServices::getInstance();
		$revisionLookup = $services->getRevisionLookup();
		$userLang = $this->skin->getLanguage();
		$title = $this->skin->getTitle();

		$versionPager = new VersionPager( $revisionLookup, $userLang, $title, $this->oldId );

		return $versionPager->getHtml();
	}

	/**
	 * Resolves the currently displayed "oldid" from the "old revision view" URL parameters
	 */
	private function initOldId() {
		$this->oldId = $this->skin->getRequest()->getInt( 'oldid', -1 );

		if ( $this->oldId !== -1 ) {
			$direction = $this->skin->getRequest()->getVal( 'direction', '' );
			if ( $direction === 'next' ) {
				$this->oldId = $this->skin->getTitle()->getNextRevisionID( $this->oldId );
			}
			if ( $direction === 'prev' ) {
				$this->oldId = $this->skin->getTitle()->getPreviousRevisionID( $this->oldId );
			}
		}
	}
}
