<?php
/**
 * MediaWiki extension to allow client SSL authentication.
 * Copyright (C) 2012 Tyler Romeo <tylerromeo@gmail.com>

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Extension information
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Client SSL Authentication',
	'author' => 'Tyler Romeo',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SSLClientAuthentication',
	'descriptionmsg' => 'sslauth-desc',
	'version' => '0.6.0',
);

// Configuration variables

/**
 * Enable client SSL Authentication.
 */
$wgEnableClientSSL = true;

/**
 * Enforce a policy that the CN (Common Name) value on
 * client certificates be equal to the username.
 */
$wgClientSSLEnforceName = false;

/**
 * Enforce a policy that the email value on client
 * certificates be equal to the user's stored email.
 */
$wgClientSSLEnforceEmail = false;

/**
 * Only allow users to use SSL certificates associated
 * with their account.
 *
 * If a user uses HTTPS but gives a client certificate
 * other than the one registered with there account:
 * * If this is true, log them out.
 * * If this is false, accept the certificate, but don't use it to authenticate.
 */
$wgClientSSLStrictAuth = true;

// Hooks and classes
$wgClientSSLDir = __DIR__ . '/';
$wgAutoloadClasses['SSLCertificateTable'] = $wgClientSSLDir . 'SSLClientCertificate.php';
$wgAutoloadClasses['SSLCertificate'] = $wgClientSSLDir . 'SSLClientCertificate.php';
$wgAutoloadClasses['SpecialClientSSL'] = $wgClientSSLDir . 'SpecialClientSSL.php';

$wgSpecialPages['ClientSSL'] = 'SpecialClientSSL';

$wgHooks['GetPreferences'][] = 'efClientSSLAddPrefsLink';
$wgHooks['UserLoadFromSession'][] = 'efClientSSLCheckSession';
$wgHooks['AbortLogin'][] = 'efClientSSLAbortLogin';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'efClientSSLSchemaUpdates';

$wgMessagesDirs['ClientSSL'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['ClientSSLAlias'] = $wgClientSSLDir . 'ClientSSLAuth.alias.php';

/**
 * Check the session for a client SSL certificate and perform
 * the appropriate action.
 *
 * If there is no certificate and $wgClientSSLAllowEmptyCertificates
 * is true, then log out the user. If the user is logged in already
 * but is using an SSL certificate registered to another user, and
 * $wgClientSSLStrictAuth is set, also log them out. Finally, if the
 * current user is logged out and they provide a valid, registered
 * SSL certificate, log them in.
 *
 * @param User $user Current user object
 * @param &$result Load from session result
 */
function efClientSSLCheckSession( User $user, &$result ) {
	global $wgEnableClientSSL, $wgClientSSLAllowEmptyCertificates, $wgClientSSLStrictAuth;
	if( !$wgEnableClientSSL || WebRequest::detectProtocol() !== 'https' ) {
		// Client SSL Auth not enabled. Cut out early.
		return true;
	}

	$certTable = SSLCertificateTable::singleton();
	$sslcert = $certTable->newFromSession();
	if( $sslcert === false ) {
		// No certificate. Do nothing.
	} elseif(
		$wgClientSSLStrictAuth &&
		$user->isLoggedIn() &&
		$sslcert->verify()->isGood() &&
		$sslcert->getField( 'user_id' ) !== $user->getId()
	) {
		// Certificate is valid, but it belongs to another user, and
		// strict authentication is enabled.
		$result = false;
	} elseif( $sslcert->verify()->isGood() ) {
		// Certificate loaded. Now set cookies so that cookie authentication
		// will succeed (this is a hack, this hook should be passing a reference).
		$user = User::newFromId( $sslcert->getField( 'user_id' ) );
		$user->setCookies( null, true );
	}

	return true;
}

/**
 * Detect if user is logging in with SSL certificate that does
 * not belong to them.
 *
 * In the UserLoadFromSession hook, if $wgClientSSLStrictAuth is set
 * and a user is using a certificate that does not belong to them,
 * they will be automatically logged out. However, this only happens
 * for logged in users. So if a user is using a certificate that does
 * not belong to them, and then logs in, they will be mysteriously auto-
 * logged out. This function raises an error on login to prevent that.
 *
 * @param $user User being authenticated
 * @param $password Password provided (not used)
 * @param &$retval Login result
 */
function efClientSSLAbortLogin( $user, $password, &$retval ) {
	global $wgEnableClientSSL, $wgClientSSLAllowEmptyCertificates, $wgClientSSLStrictAuth;
	if( !$wgEnableClientSSL || WebRequest::detectProtocol() !== 'https' ) {
		// Client SSL Auth not enabled. Cut out early.
		return true;
	}

	$sslcert = SSLCertificateTable::singleton()->newFromSession();
	if(
		$wgClientSSLStrictAuth &&
		$user->isLoggedIn() &&
		$sslcert !== false &&
		$sslcert->verify()->isGood() &&
		$sslcert->getField( 'user_id' ) !== $user->getId()
	 ) {
		// Merely a UI hack. The user is using a certificate that does
		// not belong to them. If we do not tell them this, they will be
		// mysteriously and automatically logged out.
		$retval = LoginForm::WRONG_PLUGIN_PASS;
		return false;
	}

	return true;
}

/**
 * Add a link to Special:Preferences so the user can reach
 * Special:ClientSSL easily.
 *
 * @param $user User object
 * @param &$preferences Array of preferences for HTMLForm
 */
function efClientSSLAddPrefsLink( User $user, array &$preferences ) {
	$link = Linker::link(
		SpecialPage::getTitleFor( 'ClientSSL' ),
		wfMessage( 'sslauth-prefs-linktospecial' )->escaped()
	);

	$preferences['clientssl'] = array(
		'type' => 'info',
		'raw' => true,
		'default' => $link,
		'label-message' => 'sslauth-prefs-name',
		'section' => 'personal/info'
	);

	return true;
}

/**
 * Add the necessary schema changes for this extension
 * when update.php is run.
 *
 * @param $updater Updater object
 */
function efClientSSLSchemaUpdates( $updater ) {
	$updater->addExtensionTable( 'sslcerts', dirname( __FILE__ ) . '/sslauth.sql' );
	return true;
}
