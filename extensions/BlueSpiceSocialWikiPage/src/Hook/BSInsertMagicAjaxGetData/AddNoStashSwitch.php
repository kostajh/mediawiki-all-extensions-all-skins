<?php

namespace BlueSpice\Social\WikiPage\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddNoStashSwitch extends BSInsertMagicAjaxGetData {

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return $this->type !== 'switches';
	}

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->response->result[] = (object)[
			'id' => 'bs:nostash',
			'type' => 'switch',
			'name' => 'NOSTASH',
			'desc' => $this->msg(
				'bs-socialwikipage-switch-nostash-description'
			)->plain(),
			'code' => $this->getCode(),
			'previewable' => false,
			'helplink' => $this->getHelpLink(),
		];
		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getCode() {
		return '__NOSTASH__';
	}

	/**
	 *
	 * @return string
	 */
	protected function getHelpLink() {
		return $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceSocialWikiPage' )->getUrl();
	}
}
