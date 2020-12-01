<?php
/**
 * Hook handler for MediaWiki hook ArticleDeleteComplete
 */
namespace BlueSpice\Social\ArticleActions\Hook\ArticleDeleteComplete;

use BlueSpice\Hook\ArticleDeleteComplete;
use BlueSpice\Social\ArticleActions\Extension;

class CreateArticleDeleteEntity extends ArticleDeleteComplete {

	protected function skipProcessing() {
		$title = $this->wikipage->getTitle();
		if ( !Extension::isTrackedNamespace( $title->getNamespace() ) ) {
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
		$title = $this->wikipage->getTitle();

		$entityFactory = $this->getServices()->getService(
			'BSEntityFactory'
		);
		$entity = $entityFactory->newFromObject( (object)[
			'type' => 'articledelete',
			'ownerid' => $this->user->getId(),
			'summary' => $this->reason,
			'titletext' => $title->getText(),
			'namespace' => $title->getNamespace(),
		] );

		if ( !$entity ) {
			// do not fatal - here is something wrong very bad!
			return true;
		}

		// TODO: Status check
		$status = $entity->save();
		return true;
	}

}
