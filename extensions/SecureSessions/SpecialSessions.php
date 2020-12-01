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

/**
 * Special page that allows a user to view the currently running sessions
 * on his/her account, and to close all of those sessions if wanted.
 *
 * @author Tyler Romeo <tylerromeo@gmail.com>
 */
class SpecialSessions extends FormSpecialPage {
	/**
	 * Set the name of the special page.
	 */
	function __construct() {
		parent::__construct( 'Sessions' );
	}

	/**
	 * Get the title of the page.
	 *
	 * @return string
	 */
	function getDescription() {
		return $this->msg( 'securesessions-sessions' )->text();
	}

	/**
	 * Make sure anonymous users don't use this page, as anonymous
	 * users cannot log out their other sessions.
	 *
	 * @param User $user Current user attempting the request
	 * @throws UserNotLoggedIn If user is not logged in
	 */
	function checkExecutePermissions( User $user ) {
		if ( $user->isAnon() ) {
			throw new UserNotLoggedIn;
		}
	}

	/**
	 * Get an HTMLForm field array containing information about
	 * the open sessions of a user.
	 *
	 * @return array Session information
	 */
	function getFormFields() {
		global $wgMemc;
		$a = array();
		$memcKey = wfMemcKey( $this->getUser()->getId(), 'sessions' );
		$sessions = $wgMemc->get( $memcKey );

		// Use a separate ID variable because the cache array may have
		// missing indexes and whatnot from logged out sessions.
		$id = 1;
		foreach ( array_filter( (array)$sessions ) as $sId => $session ) {
			$timestamp = new MWTimestamp( $session['time'] );

			// Make a table describing the session.
			$description = Html::rawElement( 'table', array(),
				Html::rawElement( 'tr', array(),
					Html::element( 'td', array( 'class' => 'mw-label' ), $this->msg( 'securesessions-sessions-ip' )->text() ) .
					Html::element( 'td', array( 'class' => 'mw-input' ), $session['ip'] )
				) .
				Html::rawElement( 'tr', array(),
					Html::element( 'td', array( 'class' => 'mw-label' ), $this->msg( 'securesessions-sessions-activity' )->text() ) .
					Html::element( 'td', array( 'class' => 'mw-input' ), $timestamp->getHumanTimestamp() )
				)
			);

			// Add session to the array.
			$a[] = array(
				'type' => 'info',
				'label' => $_SESSION['id'] == $sId ? "*$id" : "$id",
				'raw' => true,
				'default' => $description
			);

			$id++;
		}

		return $a;
	}

	/**
	 * Set the submit button text on the form to an appropriate message.
	 *
	 * Basically a copy of FormSpecialPage::getForm, except the annoying
	 * default message keys are changed.
	 *
	 * @return HTMLForm
	 */
	function getForm() {
		$this->fields = $this->getFormFields();

		$form = new HTMLForm( $this->fields, $this->getContext() );
		$form->setSubmitCallback( array( $this, 'onSubmit' ) );
		$form->setWrapperLegendMsg( 'securesessions-sessions-legend' );
		$form->addHeaderText(
			$this->msg( 'securesessions-sessions-text' )->parseAsBlock() );

		$params = array_diff_key(
			$this->getRequest()->getQueryValues(), array( 'title' => null ) );
		$form->addHiddenField( 'redirectparams', wfArrayToCGI( $params ) );

		$form->setMessagePrefix( 'securesessions' );
		$form->setSubmitTextMsg( 'securesessions-sessions-clear' );

		Hooks::run( "SpecialSessionsBeforeFormDisplay", array( &$form ) );
		return $form;
	}

	/**
	 * Reset the session cache and change the user's token so that
	 * all other sessions are logged out.
	 *
	 * @param array $data Form data (unused)
	 * @return bool true
	 */
	function onSubmit( array $data ) {
		global $wgMemc;
		$request = $this->getRequest();
		$user = $this->getUser();

		// Reset the session cache to just this session.
		$memcKey = wfMemcKey( $user->getId(), 'sessions' );
		$sessions = array( $_SESSION['id'] => array(
			'ip' => $request->getIP(),
			'time' => wfTimestampNow()
		) );
		$wgMemc->set( $memcKey, $sessions );

		// Reset the user token to log out all other sessions.
		$user->setToken();
		$user->saveSettings();
		$user->setCookies();

		return true;
	}

	/**
	 * Redirect the user to this page so that the session
	 * information is updated.
	 */
	function onSuccess() {
		$this->getOutput()->redirect( $this->getPageTitle()->getFullURL() );
	}
}
