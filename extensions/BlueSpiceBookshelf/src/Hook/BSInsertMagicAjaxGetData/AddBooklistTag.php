<?php

namespace BlueSpice\Bookshelf\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddBooklistTag extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:booklist',
			'type' => 'tag',
			'name' => 'booklist',
			'desc' => \Message::newFromKey( 'bs-bookshelf-tag-booklist-desc' )->text(),
			'code' => '<bs:booklist filter="someMeta:Val"/>',
			'examples' => [
				[ 'code' => $this->getExampleCode() ]
			],
			'helplink' => $this->getHelpLink()
		];

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getHelpLink() {
		return $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceBookshelf' )->getUrl();
	}

	/**
	 *
	 * @return string
	 */
	protected function getExampleCode() {
		return '<bs:booklist filter="title:handbook|responsible:Testuser" />';
	}
}
