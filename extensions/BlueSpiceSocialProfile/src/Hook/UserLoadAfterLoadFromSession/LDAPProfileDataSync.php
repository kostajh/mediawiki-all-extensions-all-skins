<?php

namespace BlueSpice\Social\Profile\Hook\UserLoadAfterLoadFromSession;

use BlueSpice\Social\Profile\CustomFieldsFactory;
use BlueSpice\Social\Profile\ProfileFactory;
use BlueSpice\Social\Profile\UserInfoSyncProcess;
use Config;
use IContextSource;
use MediaWiki\Extension\LDAPUserInfo\Hook\UserLoadAfterLoadFromSession\SyncUserInfoAfterLoadSession;
use User;

class LDAPProfileDataSync extends SyncUserInfoAfterLoadSession {

	/**
	 *
	 * @var string
	 */
	protected $sessionDataKey = 'ldap-userprofile-sync-last';

	/**
	 *
	 * @var ProfileFactory
	 */
	protected $profileFactory = null;

	/**
	 *
	 * @var CustomFieldsFactory
	 */
	protected $fieldFactory = null;

	/**
	 *
	 * @param IContextSource $context we're operating in
	 * @param Config $config accessor
	 * @param User $user we're talking about
	 * @param ProfileFactory $profileFactory
	 * @param CustomFieldsFactory $fieldFactory
	 */
	public function __construct( IContextSource $context, Config $config, User $user,
		ProfileFactory $profileFactory, CustomFieldsFactory $fieldFactory ) {
		parent::__construct( $context, $config, $user );

		$this->profileFactory = $profileFactory;
		$this->fieldFactory = $fieldFactory;
	}

	/**
	 * @return bool
	 * @throws \ConfigException
	 */
	protected function doSync() {
		$this->user->clearInstanceCache();
		$this->user->loadFromDatabase();

		$process = new UserInfoSyncProcess(
			$this->user,
			$this->domainConfig,
			$this->ldapClient,
			$this->profileFactory,
			$this->fieldFactory
		);

		$process->run();

		return true;
	}

}
