<?php

namespace BlueSpice\ArticleInfo\Hook\BsAdapterAjaxPingResult;

use BlueSpice\Hook\BsAdapterAjaxPingResult;
use MediaWiki\MediaWikiServices;
use Title;

class HandleArticleInfo extends BsAdapterAjaxPingResult {

	/**
	 *
	 * @var Title
	 */
	protected $title = null;

	protected function skipProcessing() {
		if ( $this->reference !== 'ArticleInfo' ) {
			return true;
		}

		$this->title = Title::newFromId( $this->articleId );
		if ( $this->title === null ) {
			return true;
		}

		if ( !$this->title->exists() ) {
			return true;
		}

		if ( !\MediaWiki\MediaWikiServices::getInstance()
			->getPermissionManager()
			->userCan( 'read', $this->getContext()->getUser(), $this->title ) ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$this->singleResults['success'] = true;

		if ( $this->params[0] !== 'checkRevision' ) {
			return true;
		}

		$this->singleResults['newRevision'] = false;
		if ( $this->providedRevisionUpToDate() ) {
			return true;
		}
		if ( $this->currentUserIsSaving() ) {
			return true;
		}
		$this->singleResults['newRevision'] = true;
		$this->singleResults['checkRevisionView'] = $this->msg(
			'bs-articleinfo-newrevision-info-text-reload',
			$this->title->getPrefixedText()
		)->parse();

		return true;
	}

	protected function providedRevisionUpToDate() {
		return $this->revisionId === $this->title->getLatestRevID();
	}

	protected function currentUserIsSaving() {
		if ( $this->params[1] !== 'edit' ) {
			return false;
		}

		$user = $this->getContext()->getUser();
		if ( $user->isAnon() ) {
			return false;
		}
		$revision = MediaWikiServices::getInstance()->getRevisionLookup()->getRevisionById(
			$this->revisionId
		);
		if ( $revision->getUser()->getName() !== $user->getName() ) {
			return false;
		}

		return true;
	}

}
