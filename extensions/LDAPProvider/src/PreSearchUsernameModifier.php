<?php

namespace MediaWiki\Extension\LDAPProvider;

abstract class PreSearchUsernameModifier implements IPreSearchUsernameModifier {

	/**
	 *
	 * @return IPreSearchUsernameModifier
	 */
	public static function newInstance() {
		return new static();
	}
}
