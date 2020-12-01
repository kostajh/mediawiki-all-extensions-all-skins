<?php

namespace BlueSpice\CountThings\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class CountUsers extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:countusers';
		$descriptor->type = 'tag';
		$descriptor->name = 'countusers';
		$descriptor->desc = wfMessage( 'bs-countthings-tag-countusers-desc' )->escaped();
		$descriptor->code = '<bs:countusers />';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'countUsersCommand';
		$descriptor->helplink = $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceCountThings' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}
