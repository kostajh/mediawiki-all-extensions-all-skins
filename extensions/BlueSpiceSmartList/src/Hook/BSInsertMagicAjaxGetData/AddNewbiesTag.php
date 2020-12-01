<?php

namespace BlueSpice\SmartList\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddNewbiesTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:newbies',
			'type' => 'tag',
			'name' => 'newbies',
			'desc' => $this->msg( 'bs-smartlist-tag-newbies-desc' )->text(),
			'code' => '<bs:newbies />',
			'mwvecommand' => 'newbiesCommand',
			'previewable' => false,
			'examples' => [ [
				'code' => '<bs:newbies count="3" />'
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
