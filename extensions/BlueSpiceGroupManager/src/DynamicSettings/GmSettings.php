<?php

namespace BlueSpice\GroupManager\DynamicSettings;

use BlueSpice\DynamicSettings\BSConfigDirSettingsFile;

class GmSettings extends BSConfigDirSettingsFile {

	/**
	 *
	 * @inheritDoc
	 */
	protected function getFilename() {
		return 'gm-settings.php';
	}
}
