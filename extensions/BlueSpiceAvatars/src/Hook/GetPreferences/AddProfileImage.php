<?php

namespace BlueSpice\Avatars\Hook\GetPreferences;

use BlueSpice\Avatars\Html\FormField\UserImage;
use BlueSpice\Hook\GetPreferences;

class AddProfileImage extends GetPreferences {

	protected function doProcess() {
		$this->preferences['bs-avatars-profileimage'] = [
			'section' => 'personal/info',
			'class' => UserImage::class,
			'cssclass' => 'bs-avatars-userimage-pref'
		];
		return true;
	}

}
