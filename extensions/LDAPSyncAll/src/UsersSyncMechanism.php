<?php
namespace LDAPSyncAll;

use Config;
use Exception;
use IContextSource;
use LoadBalancer;
use MediaWiki\Extension\LDAPGroups\Config as LDAPGroupsConfig;
use MediaWiki\Extension\LDAPGroups\GroupSyncProcess;
use MediaWiki\Extension\LDAPProvider\ClientFactory;
use MediaWiki\Extension\LDAPProvider\DomainConfigFactory;
use MediaWiki\Extension\LDAPUserInfo\Config as LDAPUserInfoConfig;
use MediaWiki\Extension\LDAPUserInfo\UserInfoSyncProcess;
use Psr\Log\LoggerInterface;
use SpecialBlock;
use Status;
use User;
use UserGroupMembership;

class UsersSyncMechanism {

	/**
	 *
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * @var LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @var Status
	 */
	protected $status = null;

	/**
	 * @var array null
	 */
	protected $LDAPGroupsSyncMechanismRegistry = null;

	/**
	 * @var array null
	 */
	protected $LDAPUserInfoModifierRegistry = null;

	/**
	 * @var array
	 */
	protected $excludedUsernames = [];

	/**
	 * @var array
	 */
	protected $excludedGroups = [];

	/**
	 * @var string[]
	 */
	protected $domains;

	/**
	 * @var IContextSource
	 */
	protected $context;

	/**
	 * @var int
	 */
	public $disabledUsersCount = 0;

	/**
	 * @var int
	 */
	public $disabledUsersFailsCount = 0;

	/**
	 * @var int
	 */
	public $addedUsersCount = 0;

	/**
	 * @var int
	 */
	public $addedUsersFailsCount = 0;

	/**
	 * UsersSyncMechanism constructor.
	 * @param string[] $domains
	 * @param array $LDAPGroupsSyncMechanismRegistry
	 * @param array $LDAPUserInfoModifierRegistry
	 * @param array $excludedUsernames
	 * @param array $excludedGroups
	 * @param LoggerInterface $logger
	 * @param LoadBalancer $loadBalancer
	 * @param IContextSource $context
	 */
	public function __construct(
		array $domains,
		$LDAPGroupsSyncMechanismRegistry,
		$LDAPUserInfoModifierRegistry,
		$excludedUsernames,
		$excludedGroups,
		LoggerInterface $logger,
		LoadBalancer $loadBalancer,
		IContextSource $context
	) {
		$this->domains = $domains;
		$this->LDAPGroupsSyncMechanismRegistry = $LDAPGroupsSyncMechanismRegistry;
		$this->LDAPUserInfoModifierRegistry = $LDAPUserInfoModifierRegistry;
		$this->excludedUsernames = $excludedUsernames;
		$this->excludedGroups = $excludedGroups;
		$this->logger = $logger;
		$this->loadBalancer = $loadBalancer;
		$this->context = $context;
	}

	/**
	 * @return Status
	 */
	public function sync() {
		$this->status = \Status::newGood();
		$this->doSync();
		$this->logger->alert(
			'LDAPSyncAll completed. 
			{addedUsersCount} users added; 
			{disabledUsersCount} users disabled;
			{addedUsersFailsCount} users failed to add;
			{disabledUsersFailsCount} users failed to disable;',
			[
				'addedUsersCount' => $this->addedUsersCount,
				'disabledUsersCount' => $this->disabledUsersCount,
				'addedUsersFailsCount' => $this->addedUsersFailsCount,
				'disabledUsersFailsCount' => $this->disabledUsersFailsCount
			]
		);
		return $this->status;
	}

	protected function doSync() {
		$localUsers = $this->getUsersFromDB();
		$ldapUsers = [];
		try {
			foreach ( $this->domains as $domain ) {
				$ldapUsers += $this->getUsersFromLDAP( $domain );
			}
		} catch ( Exception $exception ) {
			$this->logger->error(
				'Fetching users from LDAP has been failed. Message: {message}',
				[
					'message' => $exception->getMessage()
				]
			);
			return;
		}

		foreach ( $localUsers as $username => $user ) {
			if ( !array_key_exists( $username, $ldapUsers ) ) {
				$this->disableUser( $user );
			}
		}

		foreach ( $ldapUsers as $ldapUsername => $ldapUser ) {
			if ( !array_key_exists( $ldapUsername, $localUsers ) ) {
				$this->addUser(
					$ldapUser['user_name'],
					$ldapUser['domain'],
					$ldapUser['email'],
					$ldapUser['user_real_name']
				);
			}
		}
	}

	/**
	 * @param string $domain
	 * @return array
	 */
	protected function getUsersFromLDAP( $domain ) {
		$memberOf = '';
		$authConfig = DomainConfigFactory::getInstance()
			->factory( $domain, 'authorization' );
		if ( $authConfig instanceof Config && $authConfig->has( 'rules' ) ) {
			$rules = $authConfig->get( 'rules' );
			if (
				array_key_exists( 'groups', $rules ) &&
				array_key_exists( 'required', $rules['groups'] )
			) {
				if ( count( $rules['groups']['required'] ) > 0 ) {
					$memberOf = '(|';
					foreach ( $rules['groups']['required'] as $group ) {
						$memberOf .= '(memberOf=' . $group . ')';
					}
					$memberOf .= ')';
				}
			}
		}

		$ldapUsersDirty = ClientFactory::getInstance()
			->getForDomain( $domain )
			->search( "(&(objectClass=User)(objectCategory=Person){$memberOf})" );

		$ldapUsers = [];
		foreach ( $ldapUsersDirty as $user ) {
			if ( $user['samaccountname'][0] ) {
				$ldapUsers[User::getCanonicalName( $user['samaccountname'][0] )] = [
					'user_name' => $user['samaccountname'][0],
					'user_real_name' => $user['cn'][0],
					'user_email' => $user['userprincipalname'][0],
					'domain' => $domain
				];
			}
		}

		return $ldapUsers;
	}

	/**
	 * @return array
	 */
	protected function getUsersFromDB() {
		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$result = $dbr->select(
			[ 'user', 'ipblocks' ],
			[ 'user_id', 'user_name', 'ipb_expiry' ],
			[ 'ipb_expiry is null' ],
			__FUNCTION__,
			[],
			[
				'ipblocks' => [ 'LEFT JOIN', 'user_id = ipb_user' ]
			]
		);

		$users = [];

		foreach ( $result as $row ) {
			if ( in_array( $row->user_name, $this->excludedUsernames ) ) {
				continue;
			}
			$user = User::newFromId( $row->user_id );
			if ( !is_object( $user ) ) {
				continue;
			}
			$users[$row->user_name] = $user;
		}

		return $users;
	}

	/**
	 * @param string $username
	 * @param string $domain
	 * @param string $email
	 * @param string $realName
	 */
	protected function addUser( $username, $domain, $email = '', $realName = '' ) {
		try {
			$user = User::newFromName( $username );
			if ( $user->isBlocked() ) {
				// TODO: enable?
			} else {
				$user->addToDatabase();

				$user->setRealName( $realName );
				$user->setEmail( $email );
				$user->saveSettings();

				$this->addedUsersCount++;
			}
		} catch ( Exception $exception ) {
			$this->addedUsersFailsCount++;
			$this->logger->alert(
				'User `{username}` creation error {message}',
				[
					'username' => $username,
					'message' => $exception->getMessage()
				]
			);
		}
		$this->syncUserInfo( $user, $domain );
		$this->syncUserGroups( $user, $domain );
	}

	/**
	 * @param User $user
	 * @param string $domain
	 */
	protected function syncUserGroups( User $user, $domain ) {
		$client = ClientFactory::getInstance()->getForDomain( $domain );
		$domainConfig = DomainConfigFactory::getInstance()
			->factory( $domain, LDAPGroupsConfig::DOMAINCONFIG_SECTION );
		$process = new GroupSyncProcess(
			$user,
			$domainConfig,
			$client,
			$this->LDAPGroupsSyncMechanismRegistry
		);
		$process->run();
	}

	/**
	 * @param User $user
	 * @param string $domain
	 */
	protected function syncUserInfo( User $user, $domain ) {
		$process = new UserInfoSyncProcess(
			$user,
			DomainConfigFactory::getInstance()
				->factory(
					$domain,
					LDAPUserInfoConfig::DOMAINCONFIG_SECTION
				),
			ClientFactory::getInstance()->getForDomain( $domain ),
			$this->LDAPUserInfoModifierRegistry
		);
		$process->run();
	}

	/**
	 * @param User $user
	 */
	protected function disableUser( User $user ) {
		foreach ( $this->excludedGroups as $group ) {
			if ( UserGroupMembership::getMembership( $user->getId(), $group ) ) {
				return;
			}
		}

		$data = [
			'PreviousTarget' => $user->getName(),
			'Target' => $user->getName(),
			'Reason' => [
				wfMessage( 'user-is-not-in-ad-block-reason' )->plain()
			],
			'Expiry' => 'infinity',
			'HardBlock' => false,
			'CreateAccount' => false,
			'AutoBlock' => true,
			'DisableEmail' => false,
			'HideUser' => false,
			'DisableUTEdit' => true,
			'Reblock' => false,
			'Watch' => false,
			'Confirm' => '',
			'Tags' => [ 'ldap' ],
		];

		if ( SpecialBlock::processForm( $data, $this->context ) ) {
			$this->disabledUsersCount++;
			$this->logger->debug(
				'User {username} has been disabled by UsersSyncMechanism',
				[
					'username' => $user->getName()
				]
			);
		} else {
			$this->disabledUsersFailsCount++;
		}
	}
}
