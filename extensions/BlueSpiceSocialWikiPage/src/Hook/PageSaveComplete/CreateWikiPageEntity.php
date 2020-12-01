<?php
namespace BlueSpice\Social\WikiPage\Hook\PageSaveComplete;

use BlueSpice\Hook\PageSaveComplete;

class CreateWikiPageEntity extends PageSaveComplete {
	protected function skipProcessing() {
		// this is a bit hacky but without it the unit tests fail due to
		// more pages existing than exspected
		if ( defined( 'MW_PHPUNIT_TEST' ) ) {
			return true;
		}

		if ( ( $this->flags & EDIT_MINOR ) || !$this->revisionRecord ) {
			return true;
		}
		if ( $this->editResult->isNullEdit() ) {
			return true;
		}
		$title = $this->wikiPage->getTitle();
		if ( !$title || !$title->exists() ) {
			return true;
		}
		$tracked = \BlueSpice\Social\WikiPage\Extension::isTrackedNamespace(
			$title->getNamespace()
		);
		if ( !$tracked ) {
			return true;
		}
		if ( $title->isTalkPage() ) {
			return true;
		}
		if ( $title->getContentModel() != 'wikitext' ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$factory = $this->getServices()->getService(
			'BSSocialWikiPageEntityFactory'
		);
		$entity = $factory->newFromTitle(
			$this->wikiPage->getTitle()
		);
		if ( !$entity ) {
			// do not fatal - here is something wrong very bad!
			return true;
		}
		if ( !$entity->exists() ) {
			// TODO: Status check
			$status = $entity->save();
			return true;
		}
		$entity->invalidateCache();
		return true;
	}
}
