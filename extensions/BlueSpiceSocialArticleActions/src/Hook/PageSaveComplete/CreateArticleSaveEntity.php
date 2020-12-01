<?php
namespace BlueSpice\Social\ArticleActions\Hook\PageSaveComplete;

use BlueSpice\Hook\PageSaveComplete;

class CreateArticleSaveEntity extends PageSaveComplete {
	protected function skipProcessing() {
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
		$tracked = \BlueSpice\Social\ArticleActions\Extension::isTrackedNamespace(
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
		if ( $title->isNewPage() ) {
			return true;
		}

		return false;
	}

	protected function doProcess() {
		$title = $this->wikiPage->getTitle();

		$entityFactory = $this->getServices()->getService(
			'BSEntityFactory'
		);
		$entity = $entityFactory->newFromObject( (object)[
			'ownerid' => $this->user->getId(),
			'wikipageid' => $title->getArticleID(),
			'titletext' => $title->getText(),
			'namespace' => $title->getNamespace(),
			'revisionid' => $this->revisionRecord->getId(),
			'type' => 'articlesave',
		] );
		if ( !$entity ) {
			// do not fatal - here is something wrong very bad! :(
			return true;
		}

		// TODO: Status check
		$status = $entity->save();
		return true;
	}
}
