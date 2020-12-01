<?php

namespace BlueSpice\Social\Hook\GetPreferences;

use BlueSpice\Hook\GetPreferences;

/**
 * Adds the user setting bs-social-datedisplaymode
 */
class AddTimeDisplayMode extends GetPreferences {

	protected function doProcess() {
		$this->preferences['bs-social-datedisplaymode'] = [
			'type' => 'radio',
			'label-message' => 'bs-social-prof-datedisplaymode',
			'section' => 'rendering/social',
			'options' => [
				wfMessage( 'bs-social-pref-datedisplaymode-mode-age' )->plain()
					=> 'age',
				wfMessage( 'bs-social-pref-datedisplaymode-mode-mw' )->plain()
					=> 'mw',
			]
		];

		return true;
	}
}
