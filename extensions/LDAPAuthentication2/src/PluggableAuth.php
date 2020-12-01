<?php

namespace MediaWiki\Extension\LDAPAuthentication2;

use Exception;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Extension\LDAPProvider\ClientConfig;
use MediaWiki\Extension\LDAPProvider\ClientFactory;
use MediaWiki\Extension\LDAPProvider\LDAPNoDomainConfigException as NoDomain;
use MediaWiki\Extension\LDAPProvider\UserDomainStore;
use MediaWiki\MediaWikiServices;
use PluggableAuth as PluggableAuthBase;
use PluggableAuthLogin;
use User;

class PluggableAuth extends PluggableAuthBase {

	const DOMAIN_SESSION_KEY = 'ldap-authentication-selected-domain';

	/**
	 * Authenticates against LDAP
	 * @param int &$id not used
	 * @param string &$username set to username
	 * @param string &$realname set to real name
	 * @param string &$email set to email
	 * @param string &$errorMessage any errors
	 * @return bool false on failure
	 * @SuppressWarnings( UnusedFormalParameter )
	 * @SuppressWarnings( ShortVariable )
	 */
	public function authenticate( &$id, &$username, &$realname, &$email, &$errorMessage ) {
		if ( method_exists( MediaWikiServices::class, 'getAuthManager' ) ) {
			// MediaWiki 1.35+
			$authManager = MediaWikiServices::getInstance()->getAuthManager();
		} else {
			$authManager = AuthManager::singleton();
		}
		$extraLoginFields = $authManager->getAuthenticationSessionData(
			PluggableAuthLogin::EXTRALOGINFIELDS_SESSION_KEY
		);

		$domain = $extraLoginFields[ExtraLoginFields::DOMAIN];
		$username = $extraLoginFields[ExtraLoginFields::USERNAME];
		$password = $extraLoginFields[ExtraLoginFields::PASSWORD];

		$config = Config::newInstance();

		/* This is a workaround: As "PluggableAuthUserAuthorization" hook is
		 * being called before PluggableAuth::saveExtraAttributes (see below)
		 * we can not rely on LdapProvider\UserDomainStore here. Further
		 * complicating things, we can not persist the domain here, as the
		 * user id may be null (first login)
		 */
		$authManager->setAuthenticationSessionData(
			static::DOMAIN_SESSION_KEY,
			$domain
		);

		if ( $domain === ExtraLoginFields::DOMAIN_VALUE_LOCAL ) {
			if ( !$config->get( "AllowLocalLogin" ) ) {
				$errorMessage = wfMessage( 'ldapauthentication2-no-local-login' )->plain();
				return false;
			}
			// Validate local user the mediawiki way
			if ( $this->checkLocalPassword( $username, $password ) ) {
				return true;
			}

			$errorMessage = wfMessage( 'ldapauthentication2-error-local-authentication-failed' )->plain();
			return false;
		}

		$ldapClient = null;
		try {
			$ldapClient = ClientFactory::getInstance()->getForDomain( $domain );
		} catch ( NoDomain $e ) {
			$errorMessage = wfMessage( 'ldapauthentication2-no-domain-chosen' )->plain();
			return false;
		}

		if ( !$ldapClient->canBindAs( $username, $password ) ) {
			$errorMessage =
				wfMessage(
					'ldapauthentication2-error-authentication-failed', $domain
				)->text();
			return false;
		}
		try {
			$result = $ldapClient->getUserInfo( $username );
			$username = $result[$ldapClient->getConfig( ClientConfig::USERINFO_USERNAME_ATTR )];
			$realname = $result[$ldapClient->getConfig( ClientConfig::USERINFO_REALNAME_ATTR )];
			// maybe there are no emails stored in LDAP, this prevents php notices:
			$email = $result[$ldapClient->getConfig( ClientConfig::USERINFO_EMAIL_ATTR )] ?? '';
		} catch ( Exception $ex ) {
			$errorMessage =
				wfMessage(
					'ldapauthentication2-error-authentication-failed-userinfo',
					$domain
				)->text();

			wfDebugLog( 'LDAPAuthentication2', "Error fetching userinfo: {$ex->getMessage()}" );
			wfDebugLog( 'LDAPAuthentication2', $ex->getTraceAsString() );

			return false;
		}

		/**
		 * this is a feature after updating wikis which used strtolower on usernames.
		 * to use it, set this in LocalSettings.php:
		 * $LDAPAuthentication2UsernameNormalizer = 'strtolower';
		 */
		$normalizer = $config->get( "UsernameNormalizer" );
		if ( !empty( $normalizer ) && is_callable( $normalizer ) ) {
			$username = call_user_func( $normalizer, $username );
		}

		return true;
	}

	/**
	 * @param User &$user to log out
	 */
	public function deauthenticate( User &$user ) {
		// Nothing to do, really
		$user = null;
	}

	/**
	 * @param int $userId for user
	 */
	public function saveExtraAttributes( $userId ) {
		if ( method_exists( MediaWikiServices::class, 'getAuthManager' ) ) {
			// MediaWiki 1.35+
			$authManager = MediaWikiServices::getInstance()->getAuthManager();
		} else {
			$authManager = AuthManager::singleton();
		}
		$domain = $authManager->getAuthenticationSessionData(
			static::DOMAIN_SESSION_KEY
		);

		/**
		 * This can happen, when user account creation was initiated by a foreign source
		 * (e.g Auth_remoteuser). There is no way of knowing the domain at this point.
		 * This can also not be a local login attempt as it would be catched in `authenticate`.
		 */
		if ( $domain === null ) {
			return;
		}
		$userDomainStore = new UserDomainStore(
			MediaWikiServices::getInstance()->getDBLoadBalancer()
		);

		$userDomainStore->setDomainForUser(
			\User::newFromId( $userId ),
			$domain
		);
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	private function checkLocalPassword( $username, $password ) {
		$user = User::newFromName( $username );
		$services = MediaWikiServices::getInstance();
		$passwordFactory = $services->getPasswordFactory();

		$dbr = $services->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$row = $dbr->selectRow( 'user', 'user_password', [ 'user_name' => $user->getName() ] );
		$passwordInDB = $passwordFactory->newFromCiphertext( $row->user_password );

		return $passwordInDB->verify( $password );
	}
}
