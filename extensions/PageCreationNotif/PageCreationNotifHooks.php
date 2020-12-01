<?php

/**
 * Static class for hooks handled by the Page Creation Notification extension.
 *
 * @since 0.1
 *
 * @file PageCreationNotifHooks.php
 * @ingroup PageCreationNotif
 *
 * @license GPL-3.0-or-later
 * @author Nischay Nahata < nischayn22@gmail.com >
 */
final class PageCreationNotifHooks {

	/**
	 * Register hooks depending on version
	 */
	public static function registerExtension() {
		global $wgHooks;
		if ( class_exists( MediaWiki\HookContainer\HookContainer::class ) ) {
			// MW 1.35+
			$wgHooks['PageSaveComplete'][] = 'PageCreationNotifHooks::onPageContentInsertComplete';
		} else {
			$wgHooks['PageContentInsertComplete'][] = 'PageCreationNotifHooks::onPageContentInsertComplete';
		}
	}

	/**
	 * Adds the preferences of Page Creation Notification to the list of available ones.
	 *
	 * @since 0.1
	 *
	 * @param User $user
	 * @param array &$preferences
	 *
	 * @return true
	 */
	public static function onGetPreferences( User $user, array &$preferences ) {
		$preferences['page_creation_notif'] = [
			'type' => 'check',
			'label-message' => 'page-creation-notification',
			'section' => 'personal/email'
		];
		return true;
	}

	/**
	 * Called just before saving user preferences/options and update the pcn_users table.
	 *
	 * @since 0.1
	 *
	 * @param User $user
	 * @param array &$options
	 *
	 * @return true
	 */
	public static function onUserSaveOptions( User $user, array &$options ) {
		$dbw = wfGetDB( DB_MASTER );

		if ( $user && isset( $options['page_creation_notif'] ) ) {
			$dbw->replace(
				'pcn_users',
				'pcn_user_id',
				[
					'pcn_user_id' => $user->getId(),
					'pcn_notify' => $options['page_creation_notif'] ? 1 : 0
				],
				__METHOD__
			);
		}

		return true;
	}

	/**
	 * Schema update to set up the needed database tables.
	 *
	 * @since 0.1
	 *
	 * @param DatabaseUpdater|null $updater
	 *
	 * @return true
	 */
	public static function onSchemaUpdate( $updater = null ) {
		global $wgDBtype;

		if ( $wgDBtype == 'mysql' ) {
			$updater->addExtensionUpdate( [
				'addTable',
				'pcn_users',
				__DIR__ . '/PageCreationNotif.sql',
				true
			] );
		}

		return true;
	}

	/**
	 * Called just after a new WikiPage is created.
	 *
	 * @param WikiPage $wikiPage
	 * @param User $user
	 *
	 * @return bool
	 * @since 0.1
	 */
	public static function onPageContentInsertComplete( $wikiPage, $user ) {
		PageCreationNotifEmailer::notifyOnNewWikiPage( $wikiPage, $user );

		return true;
	}

}
