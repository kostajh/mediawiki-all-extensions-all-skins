<?php

namespace BlueSpice\TagCloud\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class TagCloud extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:tagcloud';
		$descriptor->type = 'tag';
		$descriptor->name = 'tagcloud';
		$descriptor->desc = wfMessage( 'bs-tagcloud-tag-tagcloud-desc' )->escaped();
		$descriptor->code = '<bs:tagcloud />';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'tagCloudCommand';
		$descriptor->examples = [
			[
				'code' => '<bs:tagcloud showcount="true" />'
			]
		];
		$descriptor->helplink = $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceTagCloud' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}
