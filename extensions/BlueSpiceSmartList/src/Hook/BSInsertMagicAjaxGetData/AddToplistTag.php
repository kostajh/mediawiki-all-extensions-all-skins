<?php

namespace BlueSpice\SmartList\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddToplistTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:toplist',
			'type' => 'tag',
			'name' => 'toplist',
			'desc' => $this->msg( 'bs-smartlist-tag-toplist-desc' )->text(),
			'code' => '<bs:toplist />',
			'mwvecommand' => 'topListCommand',
			'previewable' => false,
			'examples' => [ [
				'code' => '<bs:toplist count="4" cat="Wiki" period="month" />'
			] ],
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
			->getExtension( 'BlueSpiceSmartList' )->getUrl();
	}

}
