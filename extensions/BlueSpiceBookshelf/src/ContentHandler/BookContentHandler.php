<?php

namespace BlueSpice\Bookshelf\ContentHandler;

use BlueSpice\Bookshelf\Action\BookEditAction;
use BlueSpice\Bookshelf\Action\BookEditSourceAction;
use BlueSpice\Bookshelf\Content\BookContent;
use TextContentHandler;

class BookContentHandler extends TextContentHandler {
	/**
	 * @param string $modelId
	 */
	public function __construct( $modelId = 'book' ) {
		parent::__construct( $modelId, [ CONTENT_FORMAT_TEXT ] );
	}

	/**
	 * @return string
	 */
	protected function getContentClass() {
		return BookContent::class;
	}

	/**
	 * @return array
	 */
	public function getActionOverrides() {
		return [
			'edit' => BookEditAction::class,
			'editbooksource' => BookEditSourceAction::class,
		];
	}

}
