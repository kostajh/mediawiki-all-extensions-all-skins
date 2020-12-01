<?php

namespace BlueSpice\CountThings\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class CountFiles extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:countfiles';
		$descriptor->type = 'tag';
		$descriptor->name = 'countfiles';
		$descriptor->desc = wfMessage( 'bs-countthings-tag-countfiles-desc' )->escaped();
		$descriptor->code = '<bs:countfiles />';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'countFilesCommand';
		$descriptor->helplink = $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceCountThings' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}
