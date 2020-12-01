<?php

namespace BlueSpice\Authors\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class Authors extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'switches';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:authors';
		$descriptor->type = 'switch';
		$descriptor->name = 'NOAUTHORS';
		$descriptor->desc = $this->msg( 'bs-authors-switch-description' )->plain();
		$descriptor->code = '__NOAUTHORS__';
		$descriptor->previewable = false;
		$this->response->result[] = $descriptor;

		return true;
	}

}
