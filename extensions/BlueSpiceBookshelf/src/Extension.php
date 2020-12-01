<?php

namespace BlueSpice\Bookshelf;

use MWDebug;

/**
 * Bookshelf extension for BlueSpice
 *
 * Enables BlueSpice to manage and export hierarchical collections of articles
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
 * For further information visit http://bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Sebastian Ulbricht
 * @package    BlueSpiceBookshelf
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

class Extension extends \BlueSpice\Extension {

	/**
	 * extension.json callback
	 */
	public static function onRegistration() {
		//phpcs:disable
		global $wgExtraNamespaces, $bsgSystemNamespaces;
		//phpcs:enable
		if ( !defined( 'NS_BOOK' ) ) {
			define( 'NS_BOOK', 1504 );
			$wgExtraNamespaces[NS_BOOK] = 'Book';
			$bsgSystemNamespaces[1504] = 'NS_BOOK';
		}

		if ( !defined( 'NS_BOOK_TALK' ) ) {
			define( 'NS_BOOK_TALK', 1505 );
			$wgExtraNamespaces[NS_BOOK_TALK] = 'Book_talk';
			$bsgSystemNamespaces[1505] = 'NS_BOOK_TALK';
		}

		static::checkLegacy();
	}

	/**
	 * Check legacy configs and set if necessary
	 */
	public static function checkLegacy() {
		// Apply deprecated globals => will override new ones if set!
		if ( isset( $GLOBALS['bsgBookShelfUIDefaultCoverImage'] ) ) {
			static::logGlobalDeprecation(
				'bsgBookShelfUIDefaultCoverImage',
				'bsgBookShelfDefaultCoverImage'
			);
			$GLOBALS['bsgBookShelfDefaultCoverImage'] =
				$GLOBALS['bsgBookShelfUIDefaultCoverImage'];
		}
		if ( isset( $GLOBALS['BookShelfUIShowChapterNavigationPagerBeforeContent'] ) ) {
			static::logGlobalDeprecation(
				'BookShelfUIShowChapterNavigationPagerBeforeContent',
				'BookShelfShowChapterNavigationPagerBeforeContent'
			);
			$GLOBALS['BookShelfShowChapterNavigationPagerBeforeContent'] =
				$GLOBALS['BookShelfUIShowChapterNavigationPagerBeforeContent'];
		}
		if ( isset( $GLOBALS['BookShelfUIShowChapterNavigationPagerAfterContent'] ) ) {
			static::logGlobalDeprecation(
				'BookShelfUIShowChapterNavigationPagerAfterContent',
				'BookShelfShowChapterNavigationPagerAfterContent'
			);
			$GLOBALS['BookShelfShowChapterNavigationPagerAfterContent'] =
				$GLOBALS['BookShelfUIShowChapterNavigationPagerAfterContent'];
		}

		$registy = \ExtensionRegistry::getInstance()->getAttribute(
			'BlueSpiceBookshelfUIMassAddHandlerRegistry'
		);
		if ( !empty( $registy ) ) {
			// Add legacy extension attribute values
			$GLOBALS['bsgExtensionAttributeRegistryOverrides']
			['BlueSpiceBookshelfMassAddHandlerRegistry'] = [
				'merge' => $registy
			];
		}
	}

	/**
	 * @param string $old
	 * @param string $new
	 */
	protected static function logGlobalDeprecation( $old, $new ) {
		MWDebug::warning( "Using $old is deprecated, use $new instead" );
	}
}
