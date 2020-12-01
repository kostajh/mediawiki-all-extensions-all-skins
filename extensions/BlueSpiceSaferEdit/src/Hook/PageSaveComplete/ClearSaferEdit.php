<?php

namespace BlueSpice\SaferEdit\Hook\PageSaveComplete;

use BlueSpice\Hook\PageSaveComplete;

class ClearSaferEdit extends PageSaveComplete {

	protected function doProcess() {
		$seManager = $this->getServices()->getService( 'BSSaferEditManager' );
		$title = $this->wikiPage->getTitle();
		return $seManager->doClearSaferEdit( $this->user, $title );
	}
}
