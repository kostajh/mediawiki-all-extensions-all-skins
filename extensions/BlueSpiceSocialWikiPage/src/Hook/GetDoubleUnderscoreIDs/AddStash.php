<?php
namespace BlueSpice\Social\WikiPage\Hook\GetDoubleUnderscoreIDs;

class AddStash extends \BlueSpice\Hook\GetDoubleUnderscoreIDs {

	protected function doProcess() {
		$this->doubleUnderscoreIDs[] = 'bs_nostash';
		return true;
	}
}
