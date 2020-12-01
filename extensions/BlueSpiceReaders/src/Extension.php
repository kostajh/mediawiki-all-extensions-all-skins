<?php
/**
 * Readers for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpiceReaders
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\Readers;

use IContextSource;
use MediaWiki\MediaWikiServices;
use Title;

/**
 * Readers extension
 * @package BlueSpiceReaders
 */
class Extension extends \BlueSpice\Extension {

	/**
	 * Checks if the PageReaders segment should be added to the flyout
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function pageReadersFlyoutCheckPermissions( IContextSource $context ) {
		$currentTitle = $context->getTitle();
		if ( self::flyoutCheckPermissions( $currentTitle ) ) {
			return MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan(
					'viewreaders',
					$context->getUser(),
					$context->getTitle()
				);
		}
		return false;
	}

	/**
	 * Checks if the RevisionReaders segment should be added to the flyout
	 *
	 * @param IContextSource $context
	 * @return bool
	 */
	public static function revisionReadersFlyoutCheckPermissions( IContextSource $context ) {
		$currentTitle = $context->getTitle();
		if ( self::flyoutCheckPermissions( $currentTitle ) ) {
			return MediaWikiServices::getInstance()
				->getPermissionManager()
				->userCan(
					'viewrevisionreaders',
					$context->getUser(),
					$context->getTitle()
				);
		}
		return false;
	}

	/**
	 * @param Title $currentTitle
	 * @return bool
	 */
	protected static function flyoutCheckPermissions( $currentTitle ) {
		if ( $currentTitle->isSpecialPage() ) {
			return false;
		}

		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'bsg' );
		$excludeNS = $config->get( 'ReadersNamespaceBlacklist' );
		return !in_array( $currentTitle->getNamespace(), $excludeNS );
	}
}
