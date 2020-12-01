<?php

namespace BlueSpice\Avatars\Hook\SetupAfterCache;

class AddForeignFileRepo extends \BlueSpice\Hook\SetupAfterCache {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		global $wgForeignFileRepos;
		$wgForeignFileRepos[] = [
			'class' => 'FileRepo',
			'name' => 'Avatars',
			'backend' => 'Avatars-backend',
			'directory' => BS_DATA_DIR . '/Avatars/',
			'hashLevels' => 0,
			'url' => BS_DATA_PATH . '/Avatars',
			'scriptDirUrl' => $GLOBALS['wgScriptPath']
		];

		return true;
	}

}
