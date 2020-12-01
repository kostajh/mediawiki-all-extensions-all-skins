<?php

namespace BlueSpice\Social\Topics\Hook\PageSaveComplete;

use BlueSpice\Hook\PageSaveComplete;
use BlueSpice\Social\Topics\Entity\Discussion;

class AutoCreateDiscussionEntity extends PageSaveComplete {

	protected function skipProcessing() {
		if ( !$this->wikiPage->getTitle() ) {
			return true;
		}
		if ( !$this->wikiPage->getTitle()->isTalkPage() ) {
			return true;
		}
		if ( $this->wikiPage->getTitle()->getNamespace() === NS_SOCIALENTITY_TALK ) {
			return true;
		}
		if ( !$this->wikiPage->getTitle()->exists() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$factory = $this->getServices()->getService(
			'BSSocialDiscussionEntityFactory'
		);

		$entity = $factory->newFromDiscussionTitle(
			$this->wikiPage->getTitle()
		);
		if ( !$entity instanceof Discussion ) {
			return true;
		}
		if ( !$entity->exists() ) {
			$status = $entity->save();
		}
		$entity->invalidateCache();
		return true;
	}
}
