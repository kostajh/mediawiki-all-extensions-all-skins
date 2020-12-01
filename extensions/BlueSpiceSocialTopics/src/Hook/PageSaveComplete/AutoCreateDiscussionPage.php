<?php

namespace BlueSpice\Social\Topics\Hook\PageSaveComplete;

use BlueSpice\Hook\PageSaveComplete;

class AutoCreateDiscussionPage extends PageSaveComplete {

	protected function skipProcessing() {
		if ( !$this->getConfig()->get( 'SocialTopicsTalkPageAutoCreate' ) ) {
			return true;
		}
		if ( !$this->wikiPage->getTitle() ) {
			return true;
		}
		if ( $this->wikiPage->getTitle()->getNamespace() === NS_SOCIALENTITY ) {
			return true;
		}
		if ( $this->wikiPage->getTitle()->isTalkPage() ) {
			return true;
		}
		if ( !$this->wikiPage->getTitle()->exists() ) {
			return true;
		}
		if ( !$this->wikiPage->getTitle()->getTalkPage() ) {
			return true;
		}
		if ( $this->wikiPage->getTitle()->getTalkPage()->exists() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$status = \BlueSpice\Social\Topics\Extension::createDiscussionPage(
			$this->wikiPage->getTitle()->getTalkPage(),
			$this->user
		);
		return true;
	}
}
