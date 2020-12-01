<?php

namespace BlueSpice\WatchList\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddWatchListTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:watchlist',
			'type' => 'tag',
			'name' => 'watchlist',
			'desc' => $this->msg( 'bs-watchlist-tag-watchlist-desc' )->text(),
			'code' => '<bs:watchlist />',
			'previewable' => false,
			'helplink' => $this->getHelpLink()
		];

		return true;
	}

	/**
	 *
	 * @return string
	 */
	private function getHelpLink() {
		return $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceWatchList' )->getUrl();
	}

}
