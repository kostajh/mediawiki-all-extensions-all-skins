<?php

namespace BlueSpice\Bookshelf\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddBookmetaTag extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:bookmeta',
			'type' => 'tag',
			'name' => 'bookmeta',
			'desc' => \Message::newFromKey( 'bs-bookshelf-tag-bookmeta-desc' )->text(),
			'code' => '<bs:bookmeta/>',
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
<bs:bookmeta
	title="Installation manual"
	subtitle="BlueSpice pro"
	author1="Hallo Welt!"
	version="1.0"
/>
EOF;
	}
}
