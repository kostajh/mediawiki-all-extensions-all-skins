<?php

namespace BlueSpice\EchoConnector\Hook\EchoGetBundleRules;

use BlueSpice\EchoConnector\Hook\EchoGetBundleRules;

class GetBundleRules extends EchoGetBundleRules {

	protected function doProcess() {
		$this->bundleString = $this->event->getType();
		$title = $this->event->getTitle();
		if ( $title instanceof \Title ) {
			$this->bundleString .= '-' . $title->getNamespace() . '-' . $title->getDBkey();
		}

		return true;
	}

}
