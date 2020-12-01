<?php

namespace MediaWiki\Extensions\EmailAuth;

use MediaWiki\Auth\AuthenticationRequest;

class Hooks {
	public static function onAuthChangeFormFields(
		array $requests, array $fieldInfo, array &$formDescriptor, $action
	) {
		if ( AuthenticationRequest::getRequestByClass( $requests,
			EmailAuthAuthenticationRequest::class )
		) {
			$formDescriptor['token'] += [
				'size' => 6,
				'autofocus' => true,
				'persistent' => false,
				'autocomplete' => false,
				'spellcheck' => false,
			];
		}
	}
}
