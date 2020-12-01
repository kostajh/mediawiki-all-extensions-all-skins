<?php

namespace BlueSpice\Bookshelf\Content;

use BlueSpice\Bookshelf\BookEditData;
use MWException;
use ParserOptions;
use ParserOutput;
use TextContent;
use Title;

class BookContent extends TextContent {
	/**
	 * @param string $text
	 * @param string $model_id
	 * @throws MWException
	 */
	public function __construct( $text, $model_id = 'book' ) {
		parent::__construct( $text, $model_id );
	}

	/**
	 * @param Title $title
	 * @param int $revId
	 * @param ParserOptions $options
	 * @param bool $generateHtml
	 * @param ParserOutput &$output
	 */
	protected function fillParserOutput(
		Title $title, $revId, ParserOptions $options, $generateHtml, ParserOutput &$output
	) {
		$bookEditData = BookEditData::newFromTitleAndRequest(
			$title, new \WebRequest()
		);
		$output->addJsConfigVars( 'bsBookshelfData', $bookEditData->getBookData() );
		$output->addModules( 'ext.bluespice.bookshelf.view' );
		$output->setText( \Html::element( 'div', [ 'id' => 'bs-bookshelf-view' ] ) );
	}
}
