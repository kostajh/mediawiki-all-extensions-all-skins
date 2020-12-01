<?php
/**
 * Avatars Extension for BlueSpice
 *
 * Provide generic and individual user images
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
 * @author     Marc Reymann <reymann@hallowelt.com>
 * @package    BlueSpiceAvatars
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Avatars;

use MediaWiki\MediaWikiServices;

class Extension extends \BlueSpice\Extension {

	/**
	 * DEPRECATED - Use new \BlueSpice\Avatars\Generator()->getAvatarFile()
	 * instead
	 * Gets Avatar file from user ID
	 * @deprecated since version 3.0.0
	 * @param int $iUserId
	 * @return boolean|\File
	 */
	public static function getAvatarFile( $iUserId ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$avatarGenerator = new \BlueSpice\Avatars\Generator( $config );
		return $avatarGenerator->getAvatarFile( \User::newFromId( $iUserId ) );
	}

	/**
	 * Clears a user's UserImage setting
	 * @param User $oUser
	 */
	public static function unsetUserImage( $oUser ) {
		if ( $oUser->getOption( 'bs-avatars-profileimage' ) ) {
			$oUser->setOption( 'bs-avatars-profileimage', false );
			$oUser->saveSettings();
			$oUser->invalidateCache();
		}
	}

	/**
	 * DEPRECATED - Use new \BlueSpice\Avatars\Generator()->generate() instead
	 * Generate an avatar image
	 * @deprecated since version 3.0.0
	 * @param User $oUser
	 * @return string Relative URL to avatar image
	 */
	public function generateAvatar( $oUser, $aParams = [], $bOverwrite = false ) {
		wfDeprecated( __METHOD__, "3.0.0" );
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$avatarGenerator = new \BlueSpice\Avatars\Generator( $config );

		if ( $bOverwrite ) {
			$aParams[\BlueSpice\Avatars\Generator::PARAM_OVERWRITE] = true;
		}
		return $avatarGenerator->generate( $oUser, $aParams );
	}
}
