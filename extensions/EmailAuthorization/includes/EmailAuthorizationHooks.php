<?php

/*
 * Copyright (c) 2017 The MITRE Corporation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

class EmailAuthorizationHooks {

	public static function loadExtensionSchemaUpdates( $updater ) {
		$dir = $GLOBALS['wgExtensionDirectory'] . DIRECTORY_SEPARATOR .
			'EmailAuthorization' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
		$updater->addExtensionTable( 'emailauth',
			$dir . 'EmailAuth.sql', true );
		$updater->addExtensionTable( 'emailrequest',
			$dir . 'EmailRequest.sql', true );
		return true;
	}

	public static function authorize( $user, &$authorized ) {
		$authorized = EmailAuthorization::isEmailAuthorized( $user->mEmail );
		return $authorized;
	}

	public static function onRegistration() {
		$GLOBALS['wgHooks']['SpecialPage_initList'][] = function ( &$list ) {
			if ( !$GLOBALS['wgEmailAuthorization_EnableRequests'] ) {
				unset( $list['EmailAuthorizationRequest'] );
			}
		};
	}

	public static function onBeforeCreateEchoEvent( &$notifications,
		&$notificationCategories, &$icons ) {
		$notificationCategories['emailauthorization-notification-category'] = [
			'priority' => 3
		];

		$notifications['emailauthorization-account-request'] = [
			'category' => 'emailauthorization-notification-category',
			'group' => 'positive',
			'section' => 'alert',
			'presentation-model' => EchoEAPresentationModel::class,
			'user-locators' => [ 'EmailAuthorizationHooks::locateBureaucrats' ]
		];
	}

	public static function locateBureaucrats( $event ) {
		$db = wfGetDB( DB_REPLICA );
		$res = $db->select(
			[ 'user_groups', 'user' ],
			[ 'ug_user', 'ug_expiry' ],
			[ 'ug_group' => 'bureaucrat' ],
			__METHOD__,
			[],
			[ 'user' => [ 'INNER JOIN', [ 'ug_user = user_id' ] ] ]
		);
		$users = [];
		foreach ( $res as $row ) {
			$id = $row->ug_user;
			$user = User::newFromId( $id );
			$expiry = $row->ug_expiry;
			if ( !$expiry || wfTimestampNow() < $expiry ) {
				$users[$id] = $user;
			}
		}
		return $users;
	}
}
