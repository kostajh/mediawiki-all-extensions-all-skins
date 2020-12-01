<?php
namespace BlueSpice\HideTitle\Hook\GetDoubleUnderscoreIDs;

class AddHideTitle extends \BlueSpice\Hook\GetDoubleUnderscoreIDs {

	protected function doProcess() {
		$this->doubleUnderscoreIDs[] = 'bs_hidetitle';
		return true;
	}
}
