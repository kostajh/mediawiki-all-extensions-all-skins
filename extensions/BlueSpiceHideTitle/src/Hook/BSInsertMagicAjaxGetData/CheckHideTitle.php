<?php

namespace BlueSpice\HideTitle\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class CheckHideTitle extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'switches';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:countarticles';
		$descriptor->type = 'switch';
		$descriptor->name = 'HIDETITLE';
		$descriptor->desc = wfMessage( 'bs-countthings-tag-countarticles-desc' )->plain();
		$descriptor->code = '__HIDETITLE__';
		$descriptor->previewable = false;
		$descriptor->helplink = $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceHideTitle' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}
}
