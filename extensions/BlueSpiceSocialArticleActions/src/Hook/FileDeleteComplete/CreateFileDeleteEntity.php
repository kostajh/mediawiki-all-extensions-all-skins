<?php
/**
 * Hook handler for MediaWiki hook FileDeleteComplete
 */
namespace BlueSpice\Social\ArticleActions\Hook\FileDeleteComplete;

use BlueSpice\Hook\FileDeleteComplete;

class CreateFileDeleteEntity extends FileDeleteComplete {

	protected function skipProcessing() {
		if ( !$this->file || !$this->file->isLocal() ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$entityFactory = $this->getServices()->getService(
			'BSEntityFactory'
		);
		$entity = $entityFactory->newFromObject( (object)[
			'ownerid' => $this->file->getUser( 'id' ),
			'summary' => $this->reason,
			'titletext' => $this->file->getTitle()->getText(),
			'namespace' => $this->file->getTitle(),
			'filename' => $this->file->getName(),
			'filetimestamp' => $this->file->getTimestamp(),
			'type' => 'filedelete',
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
