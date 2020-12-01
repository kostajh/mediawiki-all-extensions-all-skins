<?php

namespace BlueSpice\WhoIsOnline\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddCountTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:whoisonlinecount',
			'type' => 'tag',
			'name' => 'whoisonlinecount',
			'desc' => $this->msg(
				'bs-whoisonline-tag-whoisonlinecount-desc'
			)->text(),
			'code' => '<bs:whoisonlinecount />',
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
}
