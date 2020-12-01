<?php

/**
 * Provides readers-users-store api for BlueSpice.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

use BlueSpice\DynamicFileDispatcher\Params;
use BlueSpice\DynamicFileDispatcher\UserProfileImage;
use MediaWiki\MediaWikiServices;

/**
 * GroupManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiReadersUsersStore extends BSApiExtJSStoreBase {

	/**
	 *
	 * @param string $sQuery
	 * @return \stdClass[]
	 */
	protected function makeData( $sQuery = '' ) {
		$oTitle = Title::newFromText( $sQuery );

		if ( $oTitle == null || !$oTitle->exists() ) {
			return [];
		}

		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
			'bs_readers',
			'*',
			[
				'readers_page_id' => $oTitle->getArticleID()
			],
			__METHOD__,
			[
				'ORDER BY' => 'readers_ts DESC'
			]
		);

		$aUsers = [];
		if ( $oDbr->numRows( $res ) > 0 ) {
			foreach ( $res as $row ) {
				$oUser = User::newFromId( (int)$row->readers_user_id );
				$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
				$params = [
					Params::MODULE => UserProfileImage::MODULE_NAME,
					UserProfileImage::USERNAME => $oUser->getName(),
					UserProfileImage::WIDTH => 50,
					UserProfileImage::HEIGHT => 50,
				];

				$dfdUrlBuilder = MediaWikiServices::getInstance()
					->getService( 'BSDynamicFileDispatcherUrlBuilder' );
				$sImage = $dfdUrlBuilder->build( new Params( $params ) );

				$oSpecialReaders = SpecialPage::getTitleFor( 'Readers', $oTitle->getPrefixedText() );

				$aTmpUser = [];
				$aTmpUser[ 'user_image' ] = $sImage;
				$aTmpUser[ 'user_name' ] = $oUser->getName();
				$aTmpUser[ 'user_page' ] = $oTitle->getLocalURL();
				// TODO: Implement good "real_name" handling
				$aTmpUser[ 'user_page_link' ] = Linker::link( $oTitle, $oTitle->getText() . ' ' );
				$aTmpUser[ 'user_readers' ] = $oSpecialReaders->getLocalURL();
				$aTmpUser[ 'user_readers_link' ] = Linker::link(
					$oSpecialReaders,
					'',
					[
						'class' => 'icon-bookmarks'
					]
				);

				$aTmpUser[ 'user_ts' ] = $this->getLanguage()->userAdjust( $row->readers_ts );
				$aTmpUser[ 'user_date' ] = $this->getLanguage()->timeanddate( $row->readers_ts, true );

				$aUsers[] = (object)$aTmpUser;
			}
		}

		return $aUsers;
	}
}
