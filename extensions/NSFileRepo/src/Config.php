<?php

namespace NSFileRepo;

class Config extends \GlobalVarConfig {
	const CONFIG_SKIP_TALK = 'SkipTalk';
	const CONFIG_THRESHOLD = 'NamespaceThreshold';
	const CONFIG_BLACKLIST = 'NamespaceBlacklist';

	public function __construct() {
		parent::__construct( 'egNSFileRepo' );
	}
}
