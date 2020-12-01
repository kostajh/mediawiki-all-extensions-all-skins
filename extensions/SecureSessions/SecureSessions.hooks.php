<?php

/**
 * SecureSessions extension for MediaWiki
 * (c) 2012 Tyler Romeo <tylerromeo@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 * @author Tyler Romeo <tylerromeo@gmail.com>
 * @copyright (c) 2012 by Tyler Romeo
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 3.0 or later
 */

use Wikimedia\IPUtils;

/**
 * Implements hooks functions for the SecureSessions
 * extension.
 *
 * @author Tyler Romeo <tylerromeo@gmail.com>
 */
class SecureSessions extends ContextSource {
	/**
	 * Whether restricting sessions to IP is forced (true), disabled (false),
	 * or left up to the user (null).
	 */
	private $ip = false;

	/**
	 * Whether restricting sessions to the User Agent is forced (true),
	 * disabled (false), or left up to the user (null).
	 */
	private $userAgent = false;

	/**
	 * Whether a user logging in does nothing (false), auto-logs out all other
	 * sessions for that user (true), or if the user can choose (null).
	 */
	private $oneSession = false;

	/**
	 * Process boolean values of $wgEnhancedSessionAuth and register
	 * the necessary hooks for the extension.
	 */
	public static function setup() {
		global $wgEnhancedSessionAuth, $wgSessionCycleId;
		$request = RequestContext::getMain()->getRequest();

		// Regenerate session ID to avoid fixation, but don't trash
		// the old session immediately in case there are some asynchronous
		// requests still using it.
		if ( $wgSessionCycleId && session_id() != '' && $request->getSessionData( 'wsExpiry' ) === null ) {
			// Set obsolete and expiration time.
			$data = $_SESSION;
			$request->setSessionData( 'wsObsolete', true );
			$request->setSessionData( 'wsExpiry', time() + 5 );

			// Close the session, change IDs, and start a new session.
			// Don't bother with session_regenerate_id(), because we
			// need to write and close the old session to make sure
			// the expiry data saves.
			session_write_close();
			session_id( MWCryptRand::generateHex( 32 ) );
			session_start();
			$_SESSION = $data;
		}

		// Boolean values are translated to constant options.
		if ( $wgEnhancedSessionAuth === true ) {
			$options = array( 'useragent' => true, 'ip' => true, 'singlesession' => null );
		} elseif ( $wgEnhancedSessionAuth === false ) {
			$options = array();
		} else {
			$options = $wgEnhancedSessionAuth;
		}

		// Instantiate and register hooks.
		if ( $options instanceof self ) {
			$clPropAuth = $options;
		} else {
			$clPropAuth = new self( $options );
		}

		Hooks::register( 'PersonalUrls', $clPropAuth );
		Hooks::register( 'GetPreferences', $clPropAuth );
		Hooks::register( 'AbortLogin', $clPropAuth );
		Hooks::register( 'UserLoginForm', $clPropAuth );
		Hooks::register( 'UserLoadFromSession', $clPropAuth );
		Hooks::register( 'UserSetCookies', $clPropAuth );
		Hooks::register( 'UserLogout', $clPropAuth );
	}

	/**
	 * Store the options in the object.
	 *
	 * @param array $options Authentication options
	 */
	public function __construct( array $options ) {
		if ( array_key_exists( 'ip', $options ) ) {
			$this->ip = $options['ip'];
		}
		if ( array_key_exists( 'useragent', $options ) ) {
			$this->userAgent = $options['useragent'];
		}
		if ( array_key_exists( 'singlesession', $options ) ) {
			$this->oneSession = $options['singlesession'];
		}
	}

	/**
	 * Check if the user is allowed to log in from this country.
	 *
	 * @param User $user User logging in
	 * @param string $password Password given
	 * @param int &$retval Result of the login
	 * @return bool
	 */
	public function onAbortLogin( User $user, $password, &$retval, &$msg ) {
		$request = $this->getRequest();

		// Location checking.
		$forwardedFor = $request->getHeader( 'X-Forwarded-For' );
		if( $forwardedFor ) {
			$ip = trim( array_pop( explode( ',', $forwardedFor ) ) );
		} else {
			$ip = $request->getIP();
		}

		$actualLocation = $this->getLocation( $ip );
		$expectedLocation = $user->getOption( 'securesessions-country', 0 );

		// Do weak comparison because if user doesn't have the option set,
		// a default of 0 will be returned.
		if( $actualLocation[1] != $expectedLocation ) {
			$msg = 'securesessions-disallowedcountry';
			return false;
		}

		// Tor check
		if(
			is_callable( array( 'TorExitNodes', 'isExitNode' ) ) &&
			$user->getOption( 'securesessions-tor' ) &&
			TorExitNodes::isExitNode( $ip )
		) {
			$msg = 'securesessions-disallowedtor';
			return false;
		}

		return true;
	}

	/**
	 * If the user requested certain restrictions on the session, also set cookies for
	 * those restrictions. That way if the user's session expires, but it is resumed
	 * through Cookie Token authentication (a.k.a., the Remember Me box), those preferences
	 * are preserved.
	 *
	 * @param User $user User whose cookies are being set
	 * @param array &$session Session variables to be set
	 * @param array &$cookies Cookies to be set
	 * @return bool true
	 */
	public function onUserSetCookies( User $user, array &$session, array &$cookies ) {
		global $wgMemc;
		$request = $this->getRequest();

		if (
			$this->oneSession === true ||
			$this->oneSession === null &&
			$request->getCheck( 'wpLimitSessionOne' )
		) {
			$this->updateSessionCache( $user, true );
			// Either one IP per session is enforced, or the user requested it.
			$user->setToken();
			$user->saveSettings();
			// Make sure the new token is stored in session.
			$session['wsToken'] = $user->getToken();
		} else {
			$this->updateSessionCache( $user );
		}

		// Store the user agent and IP address if needed.
		if (
			$this->userAgent === true ||
			$this->userAgent === null &&
			( $request->getCheck( 'wpLimitSessionUA' ) || $request->getCookie( 'RestrictUA' ) )
		) {
			// Only set cookie if user checked Remember Me.
			if ( $cookies['Token'] !== false ) {
				$cookies['RestrictUA'] = true;
			}
			$session['wsUserAgent'] = $_SERVER['HTTP_USER_AGENT'];
		}

		if (
			$this->ip === true ||
			$this->ip === null &&
			( $request->getCheck( 'wpLimitSessionIP' ) || $request->getCookie( 'RestrictIP' ) )
		) {
			// Only set cookie if user checked Remember Me.
			if ( $cookies['Token'] !== false ) {
				$cookies['RestrictIP'] = true;
			}
			$session['wsIPAddress'] = $request->getIP();
		}

		return true;
	}

	/**
	 * When the user logs out, remove the cookies restricting the session and remove the
	 * IP address and User Agent session info.
	 *
	 * @param User &$user The user logging out
	 * @return bool true
	 */
	public function onUserLogout( User &$user ) {
		$request = $this->getRequest();
		$response = $request->response();

		$this->deleteSessionCache( $user );

		$response->setcookie( 'RestrictIP', '', time() - 86400 );
		$response->setcookie( 'RestrictUA', '', time() - 86400 );
		$request->setSessionData( 'wsUserAgent', null );
		$request->setSessionData( 'wsIPAddress', null );

		if ( $request->getCookie( 'forceHTTPS' ) && WebRequest::detectProtocol() === 'https' ) {
			$response->header( "Strict-Transport-Security: max-age=0" );
		}

		return true;
	}

	/**
	 * If required, check if the user's user agent and IP address matches
	 * the values stored in the session.
	 *
	 * @param User $loadingUser User object
	 * @param bool &$result Result of session authentication
	 * @return bool true
	 */
	public function onUserLoadFromSession( User $loadingUser, &$result ) {
		$request = $this->getRequest();
		$user = User::newFromId( $request->getCookie( 'UserID' ) );
		$userAgent = $request->getSessionData( 'wsUserAgent' );
		$ip = $request->getSessionData( 'wsIPAddress' );

		// Check if session has expired.
		if ( $request->getSessionData( 'wsObsolete' ) && $request->getSessionData( 'wsExpiry' ) < time() ) {
			// Remove all session variables, delete the session cookie, and destroy the session.
			session_unset();
			$request->response()->setcookie( session_name(), '', 0, '' );
			session_destroy();

			$result = false;
			return true;
		}

		// Inital checks to if cookies are invalid.
		if ( $user->isAnon() || $user->getName() !== $request->getCookie( 'UserName' ) ) {
			$result = false;
			return true;
		}

		// Cookies are valid, now check if user is attempting to resume an old session (token is valid, but no session).
		if (
			$request->getSessionData( 'wsUserID' ) === null &&
			$user->getToken( false ) === $request->getCookie( 'Token' )
		) {
			// The user opted to remember their session, and the session is currently not attached
			// to a user, so set the necessary session data so that the checks below will pass.
			$user->setCookies();
			$request->setSessionData( 'wsUserID', $user->getID() );
			$request->setSessionData( 'wsUserName', $user->getName() );
			$request->setSessionData( 'wsToken', $user->getToken() );
		}

		// Basically do the same thing as User::loadFromSession, except more strict (rather
		// than using the session as a fallback for cookies, make sure they match).
		if (
			$request->getCookie( 'UserID' ) != $request->getSessionData( 'wsUserID' ) ||
			$user->getName() !== $request->getSessionData( 'wsUserName' ) ||
			$user->getToken( false ) !== $request->getSessionData( 'wsToken' ) ||
			( $this->userAgent === true || $userAgent ) && $_SERVER['HTTP_USER_AGENT'] !== $userAgent ||
			( $this->ip === true || $ip ) && $request->getIP() !== $ip
		) {
			$result = false;
			return true;
		}

		$this->updateSessionCache( $user );

		// Add strict transport security if on HTTPS. No need to worry about redirecting when on
		// HTTP, because MediaWiki::main() will handle that.
		if ( $request->getCookie( 'forceHTTPS' ) && WebRequest::detectProtocol() === 'https' ) {
			// Calculate max age for the header based on an estimation of how long it will be
			// until the session expires.
			$maxage = 0;
			if ( $request->getCookie( 'wsToken' ) !== null ) {
				// Cookie token, which mean even after the PHP session ends the user might still be
				// logged in.
				$maxage = 60 * 60 * 24 * 30;
			} else {
				// No token cookie, so the session is limited to whenever the PHP session expires.
				$maxage = ini_get('session.gc_maxlifetime');
				// If ini_get fails, fall back to PHP's default value.
				if ( $maxage === false ) {
					// http://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime
					$maxage = 1440;
				}
			}

			// Set the actual header.
			$request->response()->header( "Strict-Transport-Security: max-age=$maxage" );
		}

		return true;
	}

	/**
	 * For the three types of session authentication, the configuration
	 * can be set to null, meaning the option is left up to the user.
	 * If anything is left up to the user, add a checkbox for it.
	 *
	 * @param QuickTemplate $template Template for the login form
	 * @return bool true
	 */
	public function onUserLoginForm( $template ) {
		$extrafield = '';

		if ( $this->ip === null ) {
			$extrafield .= $this->makeCheckRow( 'wpLimitSessionIP', $this->msg( 'securesessions-iprestrict' ) );
		}
		if ( $this->userAgent === null ) {
			$extrafield .= $this->makeCheckRow( 'wpLimitSessionUA', $this->msg( 'securesessions-uarestrict' ) );
		}
		if ( $this->oneSession === null ) {
			$extrafield .= $this->makeCheckRow( 'wpLimitSessionOne', $this->msg( 'securesessions-ipsession' ) );
		}

		// Add everything after the existing extra fields.
		if ( isset( $template->data['extrafields'] ) ) {
			$extrafield = $template->data['extrafields'] . $extrafield;
		}
		$template->set( 'extrafields', $extrafield );

		return true;
	}

	/**
	 * Add a preference so the user can restrict what country his/her
	 * account can log in from.
	 *
	 * @param User $user User whose preferences are being gotten
	 * @param array &$preferences Array of preference descriptors
	 * @return bool true
	 */
	public function onGetPreferences( User $user, array &$preferences ) {
		global $wgLang;

		if( is_callable( array( 'CountryNames', 'getNames' ) ) ) {
			$countries = CountryNames::getNames( $wgLang->getCode() );

			$preferences['securesessions-country'] = array(
				'type' => 'select',
				'label-message' => 'securesessions-prefs-country',
				'section' => 'personal/info',
				'options' => array_flip( $countries ),
				'default' => $user->getOption( 'securesessions-country', 0 )
			);
		}

		if( is_callable( array( 'TorExitNodes', 'isExitNode' ) ) ) {
			$preferences['securesessions-tor'] = array(
				'type' => 'check',
				'label-message' => 'securesessions-prefs-tor',
				'section' => 'personal/info',
			);
		}

		return true;
	}

	/**
	 * Add a personal URL for the open sessions page
	 *
	 * @param array &$personal_urls Array of personal URLs
	 * @param Title $title Current title
	 * @return bool true
	 */
	public function onPersonalUrls( array &$personal_urls, Title $title ) {
		global $wgMemc;
		if ( $this->getUser()->isLoggedIn() ) {
			$memcKey = wfMemcKey( $this->getUser()->getId(), 'sessions' );
			$personal_urls['sessions'] = array(
				'text' => $this->msg( 'securesessions-personalurl')
						->numParams( count( (array) $wgMemc->get( $memcKey ) ) ),
				'href' => SpecialPage::getTitleFor( 'Sessions' )->getLocalURL(),
				'active' => $title->equals( SpecialPage::getTitleFor( 'Sessions' ) )
			);
		}
		return true;
	}

	/**
	 * Convenience function to make a table row for Special:Userlogin
	 * containing a checkbox with a certain message.
	 *
	 * @param string $name Value for the 'name' of the checkbox
	 * @param Message $value Label for the checkbox
	 * @return string HTML for the checkbox
	 */
	private function makeCheckRow( $name, Message $value ) {
		return Html::rawElement( 'tr', array(),
			Html::element( 'td', array(), '' ) .
			Html::rawElement( 'td', array( 'class' => 'mw-input' ),
				Html::input( $name, '', 'checkbox' ) .
				Html::element( 'label', array( 'for' => $name ), $value )
			)
		);
	}

	/**
	 * Update the cached array of sessions for the given user. Use the current
	 * session data to update the IP address, etc.
	 *
	 * @param User $user User the session belongs to
	 */
	private function updateSessionCache( User $user, $deleteOthers = false ) {
		global $wgMemc;
		$memcKey = wfMemcKey( $user->getId(), 'sessions' );
		$request = $this->getRequest();
		$sessions = $wgMemc->get( $memcKey );

		if ( $request->getSessionData( 'id' ) !== null ) {
			$id = $request->getSessionData( 'id' );
		} elseif ( !$deleteOthers && is_array( $sessions ) ) {
			end( $sessions );
			list( $id, $val ) = each( $sessions );
			$id++;
			$this->getRequest()->setSessionData( 'id', $id );
		} else {
			$id = 0;
		}

		if ( $deleteOthers || !is_array( $sessions ) ) {
			$sessions = array();
		}

		$sessions[$id] = array(
			'ip' => $request->getIP(),
			'time' => wfTimestampNow()
		);
		$wgMemc->set( $memcKey, $sessions );
	}

	/**
	 * Delete the current session from the session cache for the given
	 * user.
	 *
	 * @param User $user User the session belongs to
	 */
	private function deleteSessionCache( User $user ) {
		global $wgMemc;
		$memcKey = wfMemcKey( $user->getId(), 'sessions' );
		$request = $this->getRequest();

		if ( $request->getSessionData( 'id' ) === null ) {
			return;
		}

		$sessions = $wgMemc->get( $memcKey );
		$sessions[$request->getSessionData( 'id' )] = null;
		$wgMemc->set( $memcKey, $sessions );
	}

	/**
	 * Get the continent and country where an IP address is located.
	 *
	 * @param string $ip IP address
	 * @return bool|array Continent and country or false on failure
	 */
	public function getLocation( $ip ) {
		static $db = null;
		static $dbv6 = null;
		$country = false;
		for( $i = 0, $found = false; !$found; $i++ ) { switch( $i ) {
			case 0:
				if( !function_exists( 'apache_note' ) ) {
					break;
				}
				$ipUsed = apache_note( 'GEOIP_ADDR' );
				$continent = apache_note( 'GEOIP_CONTINENT_CODE' );
				$country = apache_note( 'GEOIP_COUNTRY_CODE' );
				$found = $ip === $ipUsed;
				break;
			case 1:
				$ipUsed = getenv( 'GEOIP_ADDR' );
				$continent = getenv( 'GEOIP_CONTINENT_NAME' );
				$country = getenv( 'GEOIP_COUNTRY_NAME' );
				$found = $ip === $ipUsed;
				break;
			case 2:
				if(
					!function_exists( "geoip_db_avail" ) ||
					!geoip_db_avail( GEOIP_COUNTRY_EDITION )
				) {
					break;
				}
				$continent = geoip_continent_code_by_name( $ip );
				$country = geoip_country_code_by_name( $ip );
				$found = $country !== false;
				break;
			case 3:
				$v6 = IPUtils::isIPv6( $ip );
				$dbname = $v6 ? 'dbv6' : 'db';
				if( $$dbname === null ) {
					if( function_exists( 'shmop_open' ) ) {
						geoip_load_shared_mem( __DIR__ . "/geoip/GeoIP.$dbname.dat" );
						$$dbname = geoip_open( __DIR__ . "/geoip/GeoIP.$dbname.dat", GEOIP_SHARED_MEMORY );
					} else {
						$$dbname = geoip_open( __DIR__ . "/geoip/GeoIP.$dbname.dat", GEOIP_STANDARD );
					}
				}

				$continent = true;
				if( $v6 ) {
					$country = geoip_country_code_by_addr_v6( $$dbname, $ip );
				} else {
					$country = geoip_country_code_by_addr( $$dbname, $ip );
				}
				$found = $country !== false;
				break;
			default:
				return false;
		} }

		return array( $continent, $country );
	}
}
