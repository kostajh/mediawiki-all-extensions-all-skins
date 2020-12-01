<?php
namespace BlueSpice\Social\Topics\Hook\GetDoubleUnderscoreIDs;

class AddNoDiscussion extends \BlueSpice\Hook\GetDoubleUnderscoreIDs {

	protected function doProcess() {
		$this->doubleUnderscoreIDs[] = 'bs_nodiscussion';
		return true;
	}
}
