<?php

namespace BlueSpice\Bookshelf\Special;

use BlueSpice\Bookshelf\BookEditData;
use BlueSpice\Special\ManagerBase;
use MWException;
use SpecialPage;
use Title;

class BookshelfBookEditor extends ManagerBase {
	/** @var BookEditData */
	protected $bookEditData;

	public function __construct() {
		parent::__construct( 'BookshelfBookEditor' );
	}

	/**
	 *
	 * @param string $bookTitle
	 * @return void
	 */
	public function execute( $bookTitle ) {
		try {
			$this->bookEditData = BookEditData::newFromNameRequest( $bookTitle, $this->getRequest() );
		} catch ( MWException $ex ) {
			$this->getOutput()->addWikiTextAsContent( $ex->getMessage() );
			return;
		}

		parent::execute( $this->bookEditData->getBookTitle() );

		if ( $this->bookEditData->getTitle() instanceof Title ) {
			$this->getOutput()->redirect(
				$this->bookEditData->getTitle()->getLocalURL( [
					'action' => 'edit',
					'returnto' => SpecialPage::getTitleFor( 'BookshelfBookManager' )->getPrefixedDBkey()
				] )
			);
			return;
		}

		$this->getOutput()->setPageTitle(
			wfMessage( 'bs-bookshelfui-editor-title', $this->bookEditData->getBookTitle() )->plain()
		);
		$this->getOutput()->addBacklinkSubtitle( SpecialPage::getTitleFor( 'BookshelfBookManager' ) );

		$data = $this->bookEditData->getBookData();
		$this->getOutput()->addJsConfigVars( 'bsBookshelfData', $data );
	}

	/**
	 * @return string ID of the HTML element being added
	 */
	protected function getId() {
		return 'bs-bookshelf-editorpanel';
	}

	/**
	 * @return array
	 */
	protected function getModules() {
		return [
			'ext.bluespice.bookshelf.styles',
			'ext.bluespice.bookshelf.editor'
		];
	}
}
