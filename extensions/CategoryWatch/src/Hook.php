<?php
/**
 * Hooks for CategoryWatch extension
 *
 * Copyright (C) 2017, 2018  NicheWork, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Mark A. Hershberger <mah@nichework.com>
 */
namespace CategoryWatch;

use Category;
use EchoEvent;
use MediaWiki\MediaWikiServices;
use Title;
use User;
use WatchedItemStore;
use WikiPage;

class Hook {
	/**
	 * Explain bundling
	 *
	 * @param Event $event to bundle
	 * @param string &$bundleString to use
	 */
	public static function onEchoGetBundleRules( EchoEvent $event, &$bundleString ) {
		wfDebugLog( 'CategoryWatch', __METHOD__ );
		switch ( $event->getType() ) {
			case 'categorywatch-add':
			case 'categorywatch-remove':
				$bundleString = 'categorywatch';
				break;
		}
	}

	/**
	 * Define the CategoryWatch notifications
	 *
	 * @param array &$notifications assoc array of notification types
	 * @param array &$notificationCategories assoc array describing
	 *        categories
	 * @param array &$icons assoc array of icons we define
	 */
	public static function onBeforeCreateEchoEvent(
		array &$notifications, array &$notificationCategories, array &$icons
	) {
		wfDebugLog( 'CategoryWatch', __METHOD__ );
		$icons['categorywatch']['path'] = 'CategoryWatch/assets/catwatch.svg';

		$notifications['categorywatch-add'] = [
			'bundle' => [
				'web' => true,
				'email' => true,
				'expandable' => true,
			],
			'title-message' => 'categorywatch-add-title',
			'category' => 'categorywatch',
			'group' => 'neutral',
			'user-locators' => [ 'CategoryWatch\\Hook::userLocater' ],
			'user-filters' => [ 'CategoryWatch\\Hook::userFilter' ],
			'presentation-model' => 'CategoryWatch\\EchoEventPresentationModel',
		];

		$notifications['categorywatch-remove'] = [
			'bundle' => [
				'web' => true,
				'email' => true,
				'expandable' => true,
			],
			'title-message' => 'categorywatch-remove-title',
			'category' => 'categorywatch',
			'group' => 'neutral',
			'user-locators' => [ 'CategoryWatch\\Hook::userLocater' ],
			'user-filters' => [ 'CategoryWatch\\Hook::userFilter' ],
			'presentation-model' => 'CategoryWatch\\EchoEventPresentationModel',
		];

		$notificationCategories['categorywatch'] = [
			'priority' => 2,
			'tooltip' => 'echo-pref-tooltip-categorywatch'
		];
	}

	/**
	 * Internal compatibility function
	 * @return WatchedItemStore
	 */
	private static function getWatchedItemStore() {
		wfDebugLog( 'CategoryWatch', __METHOD__ );
		if ( method_exists( 'WatchedItemStore', 'getDefaultInstance' ) ) {
			return WatchedItemStore::getDefaultInstance();
		} else {
			return MediaWikiServices::getInstance()->getWatchedItemStore();
		}
	}

	/**
	 * Hook for page being added to a category.
	 *
	 * @param Category $cat that page is being add to
	 * @param WikiPage $page that is being added
	 */
	public static function onCategoryAfterPageAdded(
		Category $cat, WikiPage $page
	) {
		wfDebugLog( 'CategoryWatch', __METHOD__ );

		$store = self::getWatchedItemStore();

		# Is anyone watching the category?
		if ( $store->countWatchers( $cat->getTitle() ) > 0 ) {
			# Send them a notification!
			$user = User::newFromId( $page->getUser() );

			EchoEvent::create( [
				'type' => 'categorywatch-add',
				'title' => $cat->getTitle(),
				'agent' => $user,
				'extra' => [
					'pageid' => $page->getId(),
					'revid' => $page->getRevision()->getId(),
				],
			] );
		}
		$watchers = [];
		foreach ( self::getWatchers( $cat->getTitle() ) as $watcher ) {
			if ( $watcher->getOption( 'catwatch-watch-pages' ) ) {
				$watchers[] = $watcher;
			}
		}
		self::addUserBatchForWatch( $watchers, $cat->getTitle() );
	}

	/**
	 * Preferences for catwatch
	 *
	 * @param User $user User whose preferences are being modified
	 * @param array &$preferences Preferences description array, to be fed to an HTMLForm object
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$preferences['categorywatch-page-watch'] = [
			'type' => 'toggle',
			'label-message' => 'categorywatch-page-watch-pref',
			'section' => 'watchlist/advancedwatchlist'
		];
	}

	/**
	 * Mirror of WatchedItemStore::addWatchBatchForUser
	 *
	 * @param array $watchers list of users
	 * @param Title $target title to add them to
	 * @return bool
	 */
	private static function addUserBatchForWatch( array $watchers, Title $target ) {
		if ( wfReadOnly() ) {
			return false;
		}

		if ( !$watchers ) {
			return true;
		}

		$rows = [];
		foreach ( $watchers as $user ) {
			$rows[] = [
				'wl_user' => $user->getId(),
				'wl_namespace' => $target->getNamespace(),
				'wl_title' => $target->getDBkey(),
				'wl_notificationtimestamp' => null,
			];
			// WatchedItemStore instantiates a WatchedItem instance
			// with the HashBagOStuff here, but it shouldn't be needed
			// on small non-farm wikis. See
			// https://gerrit.wikimedia.org/r/#/c/319255/2/includes/WatchedItemStore.php
		}

		$dbw = wfGetDB( DB_MASTER );
		foreach ( array_chunk( $rows, 100 ) as $toInsert ) {
			// Use INSERT IGNORE to avoid overwriting the notification timestamp
			// if there's already an entry for this page
			$dbw->insert( 'watchlist', $toInsert, __METHOD__, 'IGNORE' );
		}
		// WatchedItemStore instantiates a WatchedItem instance with
		// the HashBagOStuff here, but it shouldn't be needed on small
		// non-farm wikis.
		// https://gerrit.wikimedia.org/r/#/c/319255/2/includes/WatchedItemStore.php

		return true;
	}

	/**
	 * Hook for page being taken out of a category.
	 *
	 * @param Category $cat that page is being removed from
	 * @param WikiPage $page that is being removed
	 * @param int $pageID Page ID that this happened in. (not given pre 1.27ish)
	 * @see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Hooks/CategoryAfterPageRemoved
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public static function onCategoryAfterPageRemoved(
		Category $cat, WikiPage $page, $pageID = 0
	) {
		wfDebugLog( 'CategoryWatch', __METHOD__ );
		# Is anyone watching the category?
		if (
			self::getWatchedItemStore()
			->countWatchers( $cat->getTitle() ) > 0
		) {
			# Send them a notification!
			$user = User::newFromId( $page->getUser() );

			$revId = null;
			$rev = $page->getRevision();
			if ( $rev ) {
				$revId = $rev->getId();
			}
			EchoEvent::create( [
				'type' => 'categorywatch-remove',
				'title' => $cat->getTitle(),
				'agent' => $user,
				'extra' => [
					'pageid' => $page->getId(),
					'revid' => $revId
				],
			] );
		}
	}

	/**
	 * Find the watchers for a title
	 *
	 * @param Title $target to check
	 *
	 * @return array
	 */
	private static function getWatchers( Title $target ) {
		$dbr = wfGetDB( DB_REPLICA );
		$return = $dbr->selectFieldValues(
			'watchlist',
			'wl_user',
			[
				'wl_namespace' => $target->getNamespace(),
				'wl_title' => $target->getDBkey(),
			],
			__METHOD__
		);

		return array_map( function ( $userID ) {
			return User::newFromID( $userID );
		}, $return );
	}

	/**
	 * Get users that should be notified for this event.
	 *
	 * @param EchoEvent $event to be looked at
	 * @return array
	 */
	public static function userLocater( EchoEvent $event ) {
		wfDebugLog( 'CategoryWatch', __METHOD__ );
		return self::getWatchers( $event->getTitle() );
	}

	/**
	 * Filter out the person performing the action
	 *
	 * @param EchoEvent $event to be looked at
	 * @return array
	 */
	public static function userFilter( EchoEvent $event ) {
		wfDebugLog( 'CategoryWatch', __METHOD__ );
		return [ $event->getAgent() ];
	}
}
