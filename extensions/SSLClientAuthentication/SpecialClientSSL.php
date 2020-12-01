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
 * Special page for registering client SSL certificates
 * with a user account.
 *
 * @author Tyler Romeo <tylerromeo@gmail.com>
 */
class SpecialClientSSL extends FormSpecialPage {
	/**
	 * First try and load a certificate from the currently
	 * logged in user. If that does not exist, try loading
	 * from the session.
	 */
	public function __construct() {
		parent::__construct( 'ClientSSL' );

		$certTable = SSLCertificateTable::singleton();

		// See if user has a registered certificate.
		$user_id = $this->getUser()->getId();
		$sslcert = $certTable->selectRow( null, array( 'user_id' => $user_id ) );

		// If not, SSL auth is disabled, so try loading from session.
		$this->enabled = (bool) $sslcert;
		if( $sslcert === false ) {
			$sslcert = $certTable->newFromSession();
			if( $sslcert !== false ) {
				$sslcert->setField( 'user_id', $user_id );
			}
		}

		$this->sslcert = $sslcert;
	}

	/**
	 * If not using HTTPS, redirect to HTTPS. Otherwise continue
	 * with FormSpecialPage::execute.
	 *
	 * @param $par Not used
	 */
	function execute( $par ) {
		if( WebRequest::detectProtocol() !== 'https' ) {
			$title = $this->getFullTitle();
			$url = $title->getFullURL( false, false, PROTO_HTTPS );
			$this->getOutput()->redirect( $url );
		} else {
			parent::execute( $par );
		}
	}

	/**
	 * Make sure the user is logged in and that the constructor
	 * found an SSL certificate somewhere.
	 *
	 * @param $user Current user
	 * @throw UserNotLoggedIn if user is not logged in
	 * @throw ErrorPageError if no certificate was found
	 */
	function checkExecutePermissions( User $user ) {
		if( $user->isAnon() ) {
			throw new UserNotLoggedIn();
		} elseif( $this->sslcert === false ) {
			// No certificate exists whatsoever, so we can't do anything.
			throw new ErrorPageError( 'sslauth-invalidrequest-title', 'sslauth-invalidrequest' );
		}
	}

	/**
	 * Get the description for this page, depending on whether
	 * the user has client SSL authentication enabled or not.
	 *
	 * @return Message object
	 */
	function getDescription() {
		// Give grep a chance to find the usages: sslauth-title-enable, sslauth-title-diable
		$action = $this->enabled ? 'disable' : 'enable';
		return $this->msg( "sslauth-title-$action" );
	}

	/**
	 * Get the form fields for Special:ClientSSL. This includes
	 * primarily info fields about the current certificate.
	 *
	 * @return Array of fields
	 */
	function getFormFields() {
		return array(
			'CommonName' => array(
				'type' => 'info',
				'default' => $this->sslcert->getField( 'cn', 'N/A' ),
				'label-message' => 'sslauth-cn'
			),
			'Email' => array(
				'type' => 'info',
				'default' => $this->sslcert->getField( 'email', 'N/A' ),
				'label-message' => 'sslauth-email'
			),
			'Serial' => array(
				'type' => 'info',
				'default' => $this->sslcert->getField( 'serial', 'N/A' ),
				'label-message' => 'sslauth-serial'
			),
		);
	}

	/**
	 * Change the label for the submit button to Enable/Disable,
	 * depending on the current state.
	 *
	 * @param $form HTMLForm object
	 */
	function alterForm( HTMLForm $form ) {
		// Give grep a chance to find the usages: sslauth-enable, sslauth-disable
		$action = $this->enabled ? 'disable' : 'enable';
		$form->setSubmitTextMsg( "sslauth-$action" );
	}

	/**
	 * Enable/disable client SSL authentication for the user.
	 * The certificate has already been validated by the
	 * constructor, so we are good to go.
	 *
	 * @param $fields POST fields submitted to the form
	 * @return True for success, false on failure
	 */
	function onSubmit( array $fields ) {
		if( $this->enabled ) {
			return $this->sslcert->remove();
		} else {
			return $this->sslcert->save( __METHOD__ );
		}
	}

	/**
	 * Display a brief success message.
	 */
	function onSuccess() {
		// Give grep a chance to find the usages: sslauth-response-enable, sslauth-response-disable
		$action = $this->enabled ? 'disable' : 'enable';
		$this->getOutput()->addWikiMsg( "sslauth-response-$action" );
	}
}
