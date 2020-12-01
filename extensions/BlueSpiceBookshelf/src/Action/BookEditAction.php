<?php

namespace BlueSpice\Bookshelf\Action;

use BlueSpice\Bookshelf\BookEditData;
use EditAction;
use Message;
use Title;

class BookEditAction extends EditAction {
	/** @var string|bool */
	protected $bookTitle = false;

	public function show() {
		$this->useTransactionalTimeLimit();

		$out = $this->getOutput();
		$out->setRobotPolicy( 'noindex,nofollow' );
		$out->addBacklinkSubtitle( $this->getTitle() );

		$bookEditData = BookEditData::newFromTitleAndRequest(
			$this->getTitle(), $this->getRequest()
		);

		$out->setPageTitle(
			Message::newFromKey(
				'bs-bookshelf-edit-title'
			)->params( $bookEditData->getBookTitle() )
		);

		$returnTo = $this->getRequest()->getText( 'returnto', false );
		$returnToTitle = Title::newFromText( $returnTo );
		if ( $returnTo && $returnToTitle instanceof Title ) {
			$out->addBacklinkSubtitle( $returnToTitle );
		}

		$data = $bookEditData->getBookData();
		$out->addJsConfigVars( 'bsBookshelfData', $data );

		$out->addModules( [
			'ext.bluespice.bookshelf.styles',
			'ext.bluespice.bookshelf.editor'
		] );

		$out->addHTML( \Html::element( 'div', [ 'id' => 'bs-bookshelf-editorpanel' ] ) );
	}
}
