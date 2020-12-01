<?php

/**
 * Various utility functions for the Site Settings extension.
 *
 * @author Yaron Koren
 */
class SSUtils {

	static function initializeSite() {
		global $wgSiteSettingsSetupMode;

		// If we're in "setup mode", the site_settings DB table most
		// likely doesn't exist yet - don't use it.
		if ( $wgSiteSettingsSetupMode ) {
			return;
		}

		Hooks::run( 'SiteSettingsInitializeSiteBegin' );

		$siteSettings = SiteSettings::newFromDatabase();
		if ( ! is_null( $siteSettings ) ) {
			$siteSettings->load();
		} else {
			$siteSettings = new SiteSettings();
		}

		Hooks::run( 'SiteSettingsInitializeSiteEnd' );
	}

	static function setUser( $user, $s ) {
		if ( self::currentSiteIsMainSite() ) return true;
		$dbr = wfGetDB( DB_REPLICA );
		// Just overwrite whatever was there before.
		$s = $dbr->selectRow( 'user', '*', array( 'user_id' => $user->mId ), __METHOD__ );
		return true;
	}

	public static function getDBForReading() {
		global $wgSiteSettingsDB;
		$db = wfGetDB( DB_REPLICA, [], $wgSiteSettingsDB );
		Hooks::run( 'SiteSettingsGetDB', array( &$db ) );
		return $db;
	}

	public static function getDBForWriting() {
		global $wgSiteSettingsDB;
		$db = wfGetDB( DB_MASTER, [], $wgSiteSettingsDB );
		Hooks::run( 'SiteSettingsGetDB', array( &$db ) );
		return $db;
	}

	public static function saveSiteValuesToDB( $valuesToUpdate ) {
		$db = self::getDBForWriting();
		$conds = array();
		Hooks::run( 'SiteSettingsDBConditions', array( &$conds ) );
		$db->update( 'site_settings', $valuesToUpdate, $conds );
	}

	static function addTopSiteSettingsLink( &$personal_urls, &$title, $skinTemplate ) {
		$site_settings_vals = null;
		$cur_url = $title->getLocalURL();
		$user = $skinTemplate->getUser();

		if ( $user->isAllowed( 'sitesettings' ) ) {
			$ss = SpecialPage::getTitleFor( 'SiteSettings' );
			$href = $ss->getLocalURL();
			$site_settings_vals = array(
				'text' => wfMessage( 'sitesettings' )->text(),
				'href' => $href,
				'active' => ( $href == $cur_url )
			);
		}
		if ( $site_settings_vals != null ) {
			// Find the location of the "Preferences" link,
			// and add the "Site settings" link right before it.
			// This is a "key-safe" splice - it preserves both the
			// keys and the values of the array, by editing them
			// separately and then rebuilding the array.
			// Based on the example at
			// http://us2.php.net/manual/en/function.array-splice.php#31234
			$tab_keys = array_keys( $personal_urls );
			$tab_values = array_values( $personal_urls );
			$prefs_location = array_search( 'preferences', $tab_keys );
			// If this didn't work for whatever reason, set the
			// location index to -1, so the link shows up at the end.
			if ( $prefs_location == NULL ) {
				$prefs_location = -1;
			}
			array_splice( $tab_keys, $prefs_location, 0, 'sitesettings' );
			array_splice( $tab_values, $prefs_location, 0, array( $site_settings_vals ) );
			$prefs_location++;
			$personal_urls = array();
			for ( $i = 0; $i < count( $tab_keys ); $i++ ) {
				$personal_urls[$tab_keys[$i]] = $tab_values[$i];
			}
		}
		return true;
	}

	/*
	 * For private sites, if user is blocked from editing, block them from
	 * reading as well.
	 */
	static function blockFromReading( $user, &$rights ) {
		global $wgGroupPermissions;
		if ( $wgGroupPermissions['*']['read'] == false ) {
			if ( $user->isBlocked() ) {
				foreach ( $rights as $i => $right ) {
					if ( $right == 'read' ) {
						unset( $rights[$i] );
						break;
					}
				}
			}
		}
		return true;
	}

	public static function describeDBSchema( $updater ) {
		$continueWithUpdate = true;
		Hooks::run( 'SiteSettingsCreateTableBefore', array( &$continueWithUpdate ) );
		if ( !$continueWithUpdate ) {
			return true;
		}

		$dir = dirname( __FILE__ );
		//$updater->addExtensionUpdate( array( 'addTable', 'site_settings', "$dir/SiteSettings.sql", true ) );
		$updater->addExtensionTable( 'site_settings', "$dir/sql/SiteSettings.sql", true );
		Hooks::run( 'SiteSettingsCreateTableBefore', array( $updater ) );
		return true;
	}

}
