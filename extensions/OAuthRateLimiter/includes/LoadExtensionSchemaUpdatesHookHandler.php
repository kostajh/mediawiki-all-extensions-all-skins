<?php

namespace MediaWiki\Extension\OAuthRateLimiter;

use DatabaseUpdater;
use MediaWiki\Extensions\OAuth\Backend\Utils;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class LoadExtensionSchemaUpdatesHookHandler implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @param DatabaseUpdater $updater
	 * @return bool|void
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		if ( !Utils::isCentralWiki() ) {
			// no tables to add
			return true;
		}

		$dbType = $updater->getDB()->getType();

		if ( $dbType === 'mysql' ) {
			$updater->addExtensionTable(
				'oauth_ratelimit_client_tier',
				dirname( __DIR__ ) . '/schema/tables-generated.sql'
			);
		} elseif ( $dbType === 'sqlite' ) {
			$updater->addExtensionTable(
				'oauth_ratelimit_client_tier',
				dirname( __DIR__ ) . '/schema/sqlite/tables-generated.sql'
			);
		}
	}
}
