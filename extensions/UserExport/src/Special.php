<?php
/**
 * Main file for the UserExport extension.
 *
 * Copyright (C) 2009  Rodrigo Sampaio Primo
 * Copyright (C) 2020  Mark A. Hershberger
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */
namespace MediaWiki\Extension\UserExport;

use HTMLForm;
use MediaWiki\MediaWikiServices;
use PermissionsError;
use RequestContext;
use SpecialPage;

/**
 * Special page class for the User Export extension
 *
 * Allows sysops to export the username and user email to a CSV file
 *
 * @addtogroup Extensions
 * @author Rodrigo Sampaio Primo <rodrigo@utopia.org.br>
 */
class Special extends SpecialPage {
	public function __construct() {
		parent::__construct( 'UserExport', 'userexport' );
		$this->mContext = RequestContext::getMain();
	}

	public function execute( $par ) :void {
		$this->setHeaders();
		$user = $this->getUser();
		$request = $this->getRequest();
		$permMgr = MediaWikiServices::getInstance()->getPermissionManager();

		if ( !$permMgr->userHasRight( $user, 'userexport' ) ) {
			throw new PermissionsError( 'userexport' );
		}

		if ( $request->getText( 'exportusers' ) ) {
			$token = $request->getVal( 'token' );
			if ( $token === null || !$user->matchEditToken( $token ) ) {
				$this->getOutput()->addHtml(
					"<span style=\"color: red;\">"
					. wfMessage( 'userexport-badtoken' )->escaped()
					. "</span><br />\n"
				);
			} else {
				$this->exportUsers();
			}
		}

		$htmlForm = HTMLForm::factory( 'ooui', [], $this->getContext() );
		$htmlForm
			->addHiddenField( 'token', $user->getEditToken() )
			->addHiddenField( 'exportusers', '1' )
			->addHeaderText( wfMessage( 'userexport-description' )->text(), null )
			->setAction( $this->getPageTitle()->getLocalUrl() )
			->setId( 'userexportform' )
			->setSubmitText( wfMessage( 'userexport-submit' )->text() )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * Function to query the database and generate the CVS file
	 *
	 * @return bool Always returns true - throws exceptions on failure.
	 */
	private function exportUsers() {
		$filePath = tempnam( sys_get_temp_dir(), '' );
		$file = fopen( $filePath, 'w' );

		$db = wfGetDB( DB_MASTER );
		$users = $db->select( 'user', [ 'user_name', 'user_email' ] );

		fputcsv( $file, [ 'login', 'email' ] );

		while ( $user = $db->fetchObject( $users ) ) {
			fputcsv( $file, [ $user->user_name, $user->user_email ] );
		}

		fclose( $file );

		header( "Pragma:  no-cache" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( "Cache-Control: public" );
		header( "Content-Description: File Transfer" );
		header( "Content-type: text/csv" );
		header( "Content-Transfer-Encoding: binary" );
		header( "Content-Disposition: attachment; filename=\"mediawiki_users.csv\"" );
		header( "Content-Length: " . filesize( $filePath ) );
		header( "Accept-Ranges: bytes" );

		readfile( $filePath );
		unlink( $filePath );
		die;
	}

	protected function getGroupName() {
		return 'users';
	}
}
