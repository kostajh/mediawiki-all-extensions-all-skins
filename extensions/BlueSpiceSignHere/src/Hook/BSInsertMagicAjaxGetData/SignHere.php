<?php

namespace BlueSpice\SignHere\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class SignHere extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:signhere';
		$descriptor->type = 'tag';
		$descriptor->name = 'signhere';
		$descriptor->desc = wfMessage( 'bs-signhere-tag-signhere-desc' )->escaped();
		$descriptor->code = '<bs:signhere />';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'signHereCommand';
		$descriptor->helplink = $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceSignHere' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}
