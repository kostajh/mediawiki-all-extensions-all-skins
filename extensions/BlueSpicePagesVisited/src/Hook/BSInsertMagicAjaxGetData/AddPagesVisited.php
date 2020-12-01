<?php

namespace BlueSpice\PagesVisited\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddPagesVisited extends BSInsertMagicAjaxGetData {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:pagesvisited',
			'type' => 'tag',
			'name' => 'pagesvisited',
			'desc' => $this->msg(
				'bs-pagesvisited-tag-pagesvisited-desc'
			)->escaped(),
			'code' => '<bs:pagesvisited />',
			'previewable' => false,
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
			->getExtension( 'BlueSpicePagesVisited' )->getUrl();
	}

	/**
	 *
	 * @return string
	 */
	protected function getExampleCode() {
		return '<bs:pagesvisited count="7" maxtitlelength="40" />';
	}
}
