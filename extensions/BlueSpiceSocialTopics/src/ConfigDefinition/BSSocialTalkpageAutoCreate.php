<?php

namespace BlueSpice\Social\Topics\ConfigDefinition;

class BSSocialTalkpageAutoCreate extends \BlueSpice\ConfigDefinition\BooleanSetting {

	/**
	 *
	 * @return array
	 */
	public function getPaths() {
		return [
			static::MAIN_PATH_FEATURE . '/' . static::FEATURE_CONTENT_STRUCTURING . '/BlueSpiceSocialTopics',
			static::MAIN_PATH_EXTENSION . '/BlueSpiceSocialTopics/' . static::FEATURE_CONTENT_STRUCTURING,
			static::MAIN_PATH_PACKAGE . '/' . static::PACKAGE_PRO . '/BlueSpiceSocialTopics',
		];
	}

	/**
	 *
	 * @return string
	 */
	public function getLabelMessageKey() {
		return 'bs-socialtopics-toc-entalkpageautocreate';
	}

}
