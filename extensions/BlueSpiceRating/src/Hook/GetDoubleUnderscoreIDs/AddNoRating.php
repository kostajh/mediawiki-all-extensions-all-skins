<?php
namespace BlueSpice\Rating\Hook\GetDoubleUnderscoreIDs;

class AddNoRating extends \BlueSpice\Hook\GetDoubleUnderscoreIDs {

	protected function doProcess() {
		$this->doubleUnderscoreIDs[] = 'bs_norating';
		return true;
	}
}
