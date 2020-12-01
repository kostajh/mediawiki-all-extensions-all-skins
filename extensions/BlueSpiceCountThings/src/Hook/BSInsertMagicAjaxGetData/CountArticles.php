<?php

namespace BlueSpice\CountThings\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class CountArticles extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:countarticles';
		$descriptor->type = 'tag';
		$descriptor->name = 'countarticles';
		$descriptor->desc = wfMessage( 'bs-countthings-tag-countarticles-desc' )->escaped();
		$descriptor->code = '<bs:countarticles />';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'countArticlesCommand';
		$descriptor->helplink = $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceCountThings' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}
