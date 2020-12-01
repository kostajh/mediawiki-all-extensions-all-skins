<?php

namespace BlueSpice\CountThings\Hook\BSInsertMagicAjaxGetData;

use BlueSpice\InsertMagic\Hook\BSInsertMagicAjaxGetData;

class CountCharacters extends BSInsertMagicAjaxGetData {

	protected function skipProcessing() {
		return $this->type !== 'tags';
	}

	protected function doProcess() {
		$descriptor = new \stdClass();
		$descriptor->id = 'bs:countcharacters';
		$descriptor->type = 'tag';
		$descriptor->name = 'countcharacters';
		$descriptor->desc = wfMessage( 'bs-countthings-tag-countcharacters-desc' )->escaped();
		$descriptor->code = '<bs:countcharacters>ARTICLENAME</bs:countcharacters>';
		$descriptor->previewable = false;
		$descriptor->mwvecommand = 'countCharactersCommand';
		$descriptor->examples = [
			[
				'label' => wfMessage( 'bs-countthings-tag-countcharacters-example-1' )->escaped(),
				'code' => '<bs:countcharacters mode="words">ARTICLENAME</bs:countcharacters>'
			],
			[
				'label' => wfMessage( 'bs-countthings-tag-countcharacters-example-2' )->escaped(),
				'code' => '<bs:countcharacters mode="chars">ARTICLENAME</bs:countcharacters>'
			],
		];
		$descriptor->helplink = $this->getServices()->getService( 'BSExtensionFactory' )
			->getExtension( 'BlueSpiceCountThings' )->getUrl();
		$this->response->result[] = $descriptor;

		return true;
	}

}
