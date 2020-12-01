<?php

namespace BlueSpice\Social\Profile\Hook\GetPreferences;

class AddShowProfileInfoEmail extends \BlueSpice\Hook\GetPreferences {

	protected function skipProcessing() {
		$factory = $this->getServices()->getService(
			'BSSocialProfileFieldsFactory'
		);
		$email = $factory->factory( 'social-profile-email', $this->user );
		if ( !$email ) {
			return true;
		}
		return false;
	}

	protected function doProcess() {
		$this->preferences['bs-social-profile-infoshowemail'] = [
			'section' => 'personal/email',
			'type' => 'check',
			'label-message' => 'bs-social-profile-infoshowemail',
		];
		return true;
	}

}
