<?php

namespace BlueSpice\WhoIsOnline\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddPopUpTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:whoisonlinepopup',
			'type' => 'tag',
			'name' => 'whoisonlinepopup',
			'desc' => $this->msg(
				'bs-whoisonline-tag-whoisonlinepopup-desc'
			)->text(),
			'code' => '<bs:whoisonlinepopup />',
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
			->getExtension( 'BlueSpiceWhoIsOnline' )->getUrl();
	}

	/**
	 *
	 * @return string
	 */
	protected function getExampleCode() {
		return '<bs:whoisonlinepopup anchortext="Online users" />';
	}
}
