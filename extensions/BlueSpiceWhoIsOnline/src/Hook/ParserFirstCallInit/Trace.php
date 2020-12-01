<?php

namespace BlueSpice\WhoIsOnline\Hook\ParserFirstCallInit;

class Trace extends \BlueSpice\Hook\ParserFirstCallInit {

	/**
	 *
	 * @return bool
	 */
	protected function doProcess() {
		$this->getServices()->getService( 'BSWhoIsOnlineTracer' )->trace(
			$this->getContext()
		);
		return true;
	}

}
