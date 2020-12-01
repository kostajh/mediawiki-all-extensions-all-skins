<?php
/**
 * BlueSpiceSocial base extension for BlueSpice
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
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @subpackage BlueSpiceSocialWikiPage
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\WikiPage;

class Extension extends \BlueSpice\Extension {

	/**
	 *
	 */
	public static function onRegistration() {
		$GLOBALS['bsgSocialWikiPageTimelineAfterContentNamespaceBlackList'] = array_merge(
			$GLOBALS['bsgSocialWikiPageTimelineAfterContentNamespaceBlackList'],
			[
				NS_MEDIA,
				NS_MEDIAWIKI,
				NS_SPECIAL,
				NS_USER,
				NS_SOCIALENTITY
			]
		);
	}

	/**
	 *
	 * @param int $iNamespace
	 * @return bool
	 */
	public static function isTrackedNamespace( $iNamespace = 0 ) {
		return !in_array( $iNamespace, [
			NS_SOCIALENTITY,
			NS_USER,
			NS_MEDIAWIKI,
			NS_FILE
		] );
	}

}
