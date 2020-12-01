<?php

namespace MediaWiki\Extension\LDAPGroups\Hook\UserLoadAfterLoadFromSession;

use MediaWiki\Extension\LDAPGroups\Config;
use MediaWiki\Extension\LDAPGroups\GroupSyncProcess;
use MediaWiki\Extension\LDAPProvider\Hook\UserLoadAfterLoadFromSession;

class SyncUserGroupsAfterLoadSession extends UserLoadAfterLoadFromSession {

	protected $sessionDataKey = 'ldap-group-sync-last';

	/**
	 * @return bool
	 * @throws \ConfigException
	 */
	protected function doSync() {
		$process = new GroupSyncProcess(
			$this->user,
			$this->domainConfig,
			$this->ldapClient,
			$this->config->get( 'LDAPGroupsSyncMechanismRegistry' )
		);

		$process->run();

		return true;
	}

	/**
	 *
	 * @return string
	 */
	protected function getDomainConfigSection() {
		return Config::DOMAINCONFIG_SECTION;
	}
}
