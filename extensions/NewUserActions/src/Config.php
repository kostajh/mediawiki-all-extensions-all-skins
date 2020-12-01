<?php

namespace MediaWiki\Extensions\NewUserActions;

class Config {

	/**
	 * Gets the text of a MediaWiki message, localized in content language.
	 *
	 * @param string $name The message text
	 * @return string
	 */
	public static function getLocalisedMessage( $name ) {
		return \wfMessage( $name )->inContentLanguage()->text();
	}

	/**
	 * Gets the edit flags to use for revisions created by the extension.
	 *
	 * @return int
	 */
	public static function getEditFlags() {
		global $wgNewUserActionsMinorEdit, $wgNewUserActionsSuppressRC;

		$flags = 0;

		if ( $wgNewUserActionsMinorEdit ) {
			$flags = $flags | EDIT_MINOR;
		}

		if ( $wgNewUserActionsSuppressRC ) {
			$flags = $flags | EDIT_SUPPRESS_RC;
		}

		return $flags;
	}

}
