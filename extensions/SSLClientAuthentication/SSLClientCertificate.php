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

/**
 * The table of SSL certificates in the database.
 *
 * @author Tyler Romeo <tylerromeo@gmail.com>
 */
class SSLCertificateTable extends ORMTable {
	public function __construct() {
		$this->fieldPrefix = 'ssl_';
	}

	function getName() {
		return 'sslcerts';
	}

	function getRowClass() {
		return 'SSLCertificate';
	}

	function getFields() {
		return array(
			'id' => 'id',
			'issuer' => 'string',
			'serial' => 'string',
			'user_id' => 'id',
			'cn' => 'string',
			'email' => 'string',
			'certificate' => 'string'
		);
	}

	function getDefaults() {
		return array(
			'user_id' => 0,
		);
	}

	/**
	 * Attempt to make a new row object from the session.
	 *
	 * Check if a certificate exists and that is has not
	 * expired. Then attempt to find the certificate in the
	 * database. If it's not in the database, make a new
	 * row and return it.
	 *
	 * @return SSLCertificate on success; false on failure
	 */
	function newFromSession() {
		if( WebRequest::detectProtocol() !== 'https' ) {
			// Not using HTTPS.
			return false;
		} elseif( !isset( $_SERVER['SSL_CLIENT_M_SERIAL'] ) ) {
			// No certificate.
			return false;
		}

		$info = array();
		$verify = $_SERVER['SSL_CLIENT_VERIFY'];
		$expiry = $_SERVER['SSL_CLIENT_V_REMAIN'];
		$info['serial'] = $_SERVER['SSL_CLIENT_M_SERIAL'];
		$info['cn'] = $_SERVER['SSL_CLIENT_S_DN_CN'];
		$info['email'] = isset( $_SERVER['SSL_CLIENT_S_DN_Email'] ) ? $_SERVER['SSL_CLIENT_S_DN_Email'] : '';
		$info['certificate'] = $_SERVER['SSL_CLIENT_CERT'];

		if( !intval( $expiry ) || $verify != 'SUCCESS' ) {
			// Invalid serial or certificate or expired.
			return false;
		}

		// Try getting from database.
		$obj = $this->selectRow( null, $info );

		if( $obj === false ) {
			// No entry in database. Make a new one.
			$obj = $this->newRow( $info );
		}

		return $obj;
	}
}

/**
 * Row object representing an SSL certificate associated with a user.
 *
 * @author Tyler Romeo <tylerromeo@gmail.com>
 */
class SSLCertificate extends ORMRow {
	/**
	 * Check the validity of the current certificate.
	 *
	 * First check if the certificate is actually associated with
	 * a user. Then check if, depending on configuration options,
	 * if the common name and email match the username and email
	 * of the user.
	 *
	 * @return Status object
	 */
	public function verify() {
		global $wgClientSSLEnforceName, $wgClientSSLEnforceEmail, $wgClientSSLEnforceCA;
		global $wgClientSSLCA, $wgClientSSLCAFile;

		$status = new Status;

		if( !$this->hasField( 'user_id' ) ) {
			$status->fatal( 'sslauth-authfailed' );
			return $status;
		}

		$user = User::newFromId( $this->getField( 'user_id', 0 ) );

		if( !$user->loadFromId() ) {
			$status->fatal( 'sslauth-invaliduser' );
			return $status;
		}

		if( $wgClientSSLEnforceName && $user->getName() != $this->getField( 'cn' ) ) {
			$status->fatal( 'sslauth-cnmismatch', $user->getName(), $this->getField( 'cn' ) );
		}

		if( $wgClientSSLEnforceEmail && $user->getEmail() != $this->getField( 'email' ) ) {
			$status->fatal( 'sslauth-cnmismatch', $user->getEmail(), $this->getField( 'email' ) );
		}

		return $status;
	}
}
