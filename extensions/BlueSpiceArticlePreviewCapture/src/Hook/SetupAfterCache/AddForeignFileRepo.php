<?php

namespace BlueSpice\ArticlePreviewCapture\Hook\SetupAfterCache;

class AddForeignFileRepo extends \BlueSpice\Hook\SetupAfterCache {

	/**
	 * @return bool
	 */
	protected function doProcess() {
		global $wgForeignFileRepos;
		$wgForeignFileRepos[] = [
			'class' => 'FileRepo',
			'backend' => 'FileRepo',
			'name' => 'ArticlePreviewCapture',
			'directory' => BS_DATA_DIR . '/ArticlePreviewCapture/',
			'hashLevels' => 0,
			'url' => BS_DATA_PATH . '/ArticlePreviewCapture',
			'scriptDirUrl' => $GLOBALS['wgScriptPath'],
		];

		return true;
	}

}
