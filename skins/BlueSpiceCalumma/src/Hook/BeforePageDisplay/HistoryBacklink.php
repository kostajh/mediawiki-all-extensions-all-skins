<?php

namespace BlueSpice\Calumma\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;

class HistoryBacklink extends BeforePageDisplay {
	protected function skipProcessing() {
		$action = $this->out->getRequest()->getText( 'action' );
		if ( $action === 'history' ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		$this->out->addBacklinkSubtitle( $this->out->getTitle() );
		return true;
	}
}
