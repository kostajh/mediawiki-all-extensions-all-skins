<?php

namespace BlueSpice\Bookshelf\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddBookshelfTag extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:bookshelf',
			'type' => 'tag',
			'name' => 'bookshelf',
			'desc' => \Message::newFromKey( 'bs-bookshelf-tag-bookshelf-desc' )->text(),
			'code' => '<bs:bookshelf src="ARTICLE" />',
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
		return <<<EOF
<bs:bookshelf
	src="Book:Installation manual"
	width="200"
	height="100"
	float="left"
/>
EOF;
	}
}
