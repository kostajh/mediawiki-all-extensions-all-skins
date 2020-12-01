<?php

namespace MediaWiki\Extension\ExtJSBase;

class Config extends \GlobalVarConfig {
	const THEME = 'Theme';

	/**
	 *
	 * @return \Config
	 */
	public static function newInstance() {
		return new self( 'egExtJSBase' );
	}
}
