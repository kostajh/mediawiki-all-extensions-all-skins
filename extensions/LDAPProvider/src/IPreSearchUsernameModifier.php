<?php

namespace MediaWiki\Extension\LDAPProvider;

interface IPreSearchUsernameModifier {

	/**
	 *
	 * @param string $username
	 * @return string
	 */
	public function modify( $username );
}
