<?php

namespace BlueSpice\SmartList\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class AddSmartListTag extends BSInsertMagicAjaxGetData {

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
			'id' => 'bs:smartlist',
			'type' => 'tag',
			'name' => 'smartlist',
			'desc' => $this->msg( 'bs-smartlist-tag-smartlist-desc' )->text(),
			'code' => '<bs:smartlist />',
			'mwvecommand' => 'smartListCommand',
			'previewable' => false,
			'examples' => [ [
				'label' => $this->msg( 'bs-smartlist-tag-smartlist-example-rc' )->plain(),
				'code' => '<bs:smartlist new="true" count="7" ns="104" trim="false" />'
			], [
				'label' => $this->msg( 'bs-smartlist-tag-smartlist-example-whatlinkshere' )->plain(),
				'code' => '<bs:smartlist mode="whatlinkshere" target="ARTICLENAME" />'
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
