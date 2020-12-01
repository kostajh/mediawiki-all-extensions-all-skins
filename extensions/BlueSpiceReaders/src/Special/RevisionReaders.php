<?php
namespace BlueSpice\Readers\Special;

use Html;
use Title;

class RevisionReaders extends Readers {

	public function __construct() {
		parent::__construct( 'RevisionReaders', 'viewrevisionreaders' );
	}

	/**
	 * @inheritDoc
	 */
	protected function pageReaders( Title $requestedTitle ) {
		if ( !$requestedTitle->exists() ) {
			return null;
		}
		$this->getOutput()->addModules( [ 'ext.bluespice.readers.specialrevisionreaders' ] );
		$this->getOutput()->setPageTitle(
			wfMessage( 'revisionreaders', $requestedTitle->getFullText() )->text()
		);
		$this->getOutput()->addJsConfigVars(
			"bsRevisionReadersPageId", $requestedTitle->getArticleID()
		);

		return Html::element( 'div', [
			'id' => 'bs-readers-grid'
		] );
	}

	/**
	 * @inheritDoc
	 */
	protected function readByUser( Title $requestedTitle ) {
		return $this->pageReaders( $requestedTitle );
	}
}
