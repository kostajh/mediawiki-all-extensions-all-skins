<?php

namespace BlueSpice\Bookshelf\Hook\BSBookshelfGetBookData;

use BlueSpice\Bookshelf\Hook\BSBookshelfGetBookData;

class RunLegacyHook extends BSBookshelfGetBookData {

	protected function doProcess() {
		$this->getServices()->getHookContainer()->run( 'BSBookshelfBookUI', [
			null, $this->getContext()->getOutput(), $this->bookData
		] );
	}
}
