<?php
/**
 * Hooks for ListSignup
 *
 * @file
 * @ingroup Extensions
 */
class ListSignupHooks {

	/**
	 * LoadExtensionSchemaUpdates hook
	 *
	 * @param DatabaseUpdater|null $updater
	 *
	 * @return bool
	 */
	public static function onLoadExtensionSchemaUpdates( $updater = null ) {
		$updater->addExtensionTable( 'list_signup', __DIR__ . '/../sql/ListSignup.sql', true );

		return true;
	}
}
