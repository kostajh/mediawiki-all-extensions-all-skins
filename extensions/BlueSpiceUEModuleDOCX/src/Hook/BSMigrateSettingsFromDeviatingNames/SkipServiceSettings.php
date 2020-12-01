<?php

namespace BlueSpice\UEModuleDOCX\Hook\BSMigrateSettingsFromDeviatingNames;

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

	/**
	 * @return array
	 */
	protected function getSkipSettings() {
		return [
			'MW::UEModuleDOCX::DOCXServiceURL',
			'MW::UEModuleDOCX::DOCXServiceSecret',
			'MW::UEModuleDOCX::DefaultTemplate',
			'MW::UEModuleDOCX::TemplatePath',
		];
	}
}
