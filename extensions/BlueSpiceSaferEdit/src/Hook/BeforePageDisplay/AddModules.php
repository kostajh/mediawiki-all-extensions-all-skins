<?php

namespace BlueSpice\SaferEdit\Hook\BeforePageDisplay;

use BlueSpice\Hook\BeforePageDisplay;
use BlueSpice\SaferEdit\SaferEditManager;

class AddModules extends BeforePageDisplay {

	/**
	 * @var SaferEditManager
	 */
	protected $seManager;

	protected function doProcess() {
		$this->seManager = $this->getServices()->getService( 'BSSaferEditManager' );

		$isEditMode = $this->isEditMode();
		$shouldShowWarning = $this->shouldShowWarning();
		if ( $isEditMode || $shouldShowWarning ) {
			$this->out->addModules( 'ext.bluespice.saferedit.init' );
			$this->out->addJsConfigVars( 'bsSaferEditIsEditMode', $isEditMode );
			$this->out->addJsConfigVars( 'bsSaferEditDisplayWarning', $shouldShowWarning );
		}

		return true;
	}

	private function isEditMode() {
		$result = false;
		$this->seManager->askEnvironmentalCheckers( 'isEditMode', $result );
		return $result;
	}

	private function shouldShowWarning() {
		$result = false;
		$this->seManager->askEnvironmentalCheckers( 'shouldShowWarning', $result );
		return $result;
	}
}
