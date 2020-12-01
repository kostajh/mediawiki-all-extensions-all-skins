<?php

namespace BlueSpice\UEModulePDF\Hook\BSMigrateSettingsFromDeviatingNames;

class SkipServiceSettings extends \BlueSpice\Hook\BSMigrateSettingsFromDeviatingNames {

	protected function skipProcessing() {
		if ( in_array( $this->oldName, $this->getSkipSettings() ) ) {
			return false;
		}
		return true;
	}

	protected function doProcess() {
		$this->skip = true;
	}

	protected function getSkipSettings() {
		return [
			'MW::UEModulePDF::DefaultTemplate',
			'MW::UEModulePDF::PdfServiceURL',
			'MW::UEModulePDF::TemplatePath'
		];
	}
}
