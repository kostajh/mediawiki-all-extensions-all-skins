<?php

namespace MediaWiki\Extension\LDAPProvider\Hook;

use DatabaseUpdater;

class LoadExtensionSchemaUpdates {

	/**
	 *
	 * @var DatabaseUpdater
	 */
	protected $updater = null;

	/**
	 * @param DatabaseUpdater $updater object
	 * @return bool
	 */
	public static function callback( DatabaseUpdater $updater ) {
		$className = static::class;
		$hookHandler = new $className(
			$updater
		);
		return $hookHandler->process();
	}

	/**
	 * @param DatabaseUpdater $updater object
	 */
	public function __construct( DatabaseUpdater $updater ) {
		$this->updater = $updater;
	}

	/**
	 * Process the needed updates for the different DB types
	 */
	public function process() {
		$base = dirname( dirname( __DIR__ ) );
		switch ( $this->updater->getDB()->getType() ) {
			case 'mysql':
			case 'sqlite':
				$this->updater->addExtensionTable(
					'ldap_domains', "$base/schema/ldap-mysql.sql"
				);
				break;
			case 'postgres':
				$this->updater->addExtensionTable(
					'ldap_domains', "$base/schema/ldap-postgres.sql"
				);
				break;
		}
	}
}
