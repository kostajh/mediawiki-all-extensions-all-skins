<?php

class CreditsSourceHooks {
	/**
	 * LoadExtensionSchemaUpdates hook
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function loadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$schema = $updater->getDB()->getType();

		if ( $schema === 'sqlite' ) {
			# MediaWiki can handle the translation
			$schema = 'mysql';
		}

		$updater->addExtensionUpdate( [
			'addTable',
			'revsrc',
			__DIR__ . "/../schema/$schema/CreditsSource.sql",
			true
		] );

		$updater->addExtensionUpdate( [
			'addTable',
			'swsite',
			__DIR__ . "/../schema/$schema/swsite.sql",
			true
		] );
	}
}
