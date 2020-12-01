<?php

/**
 * SiteSettings class - holds the settings set within the page
 * Special:SiteSettings.
 *
 * @author Yaron Koren
 */
class SiteSettings {

	static function newFromDatabase() {
		$db = SSUtils::getDBForWriting();

		if ( !$db->tableExists( 'site_settings' ) ) {
			return null;
		}

		$conds = array();
		Hooks::run( 'SiteSettingsNewFromDBBegin', array (&$conds ) );
		$row = $db->selectRow( 'site_settings', self::selectClause(), $conds );
		if ( !empty( $row ) ) {
			// Back to real DB, if the DB changed.
			global $wgDBname;
			$db->selectDB( $wgDBname );
			$ss = new SiteSettings();
			$ss->createFromRow( $row );
			return $ss;
		}

		$returnData = null;

		Hooks::run( 'SiteSettingsNewFromDBNoData', array ( &$returnData ) );

		// If there's still no data after all that, then most likely
		// the site_settings DB table is empty - create a row for it.
		$ss = new SiteSettings();
		$ss->create();
		return $ss;
	}

	function createFromRow( $row ) {
		foreach ( $row as $var => $val ) {
			$this->$var = $val;
			SSStore::addValue( $var, $val );
		}
	}

	function refreshData() {
		global $wgProfiling;

		$wgProfiling = false;

		// Repeat of code from newFromDatabase()
		$db = SSUtils::getDBForWriting();

		$conds = array();
		Hooks::run( 'SiteSettingsNewFromDBBegin', array (&$conds ) );
		$row = $db->selectRow( 'site_settings', self::selectClause(), $conds );
		if ( !empty( $row ) ) {
			// Back to real DB, if the DB changed.
			global $wgDBname;
			$db->selectDB( $wgDBname );
			$this->createFromRow( $row );
		}
	}

	static function getFields() {
		global $wgRightsText, $wgRightsUrl, $wgDefaultSkin;

		$allFields = array(
			'main' => array(
				'name' => array( false, null ),
				'namespace' => array( false, null ),
				'language_code' => array( false, null ),
				'hours_timezone_offset' => array( false, 0),
				'use_american_dates' => array( true, true ),
				'use_24_hour_time' => array( true, false ),
				'show_page_counters' => array( true, true ),
				'use_subpages' => array( true, false ),
				'allow_external_images' => array( true, false ),
				'allow_lowercase_page_names' => array( true, false ),
				'copyright_text' => array( false, $wgRightsText ),
				'copyright_url' => array( false, $wgRightsUrl ),
			),
			'skin' => array(
				'default_skin' => array( false, $wgDefaultSkin ),
				'background_color' => array( false, null ),
				'sidebar_color' => array( false, null ),
				'sidebar_border_color' => array( false, null ),
			),
			'logo' => array(
				'logo_file' => array( false, null ),
				'favicon_file' => array( false, null ),
			),
			'privacy' => array(
				'viewing_policy_id' => array( false, 1 ),
				'registration_policy_id' => array( false, 1 ),
				'editing_policy_id' => array( false, 2 ),
			),
		);

		Hooks::run( 'SiteSettingsGetFields', array( &$allFields ) );

		return $allFields;
	}

	static function selectClause() {
		$allFieldNames = array();
		foreach ( self::getFields() as $area => $fields ) {
			$allFieldNames = array_merge( $allFieldNames, array_keys( $fields ) );
		}
		$select_clause = implode( ', ', $allFieldNames );
		$select_clause = str_replace( 'created_at', 'UNIX_TIMESTAMP(created_at) AS created_at', $select_clause );
		return $select_clause;
	}

	function updateFromQuery( $query ) {
		foreach ( self::getFields() as $area => $fields ) {
			foreach ( $fields as $fieldName => $fieldVals ) {
				$isBoolean = $fieldVals[0];
				if ( $isBoolean) {
					$this->$fieldName = array_key_exists( $fieldName, $query ) ? 1 : 0;
				} else {
					if ( array_key_exists( $fieldName, $query ) ) {
						$this->$fieldName = $query[$fieldName];
					}
				}
			}
		}
	}

	function getImagesSubirectoryName() {
		$imagesSubdirectory = '';
		Hooks::run( 'SiteSettingsGetImagesSubdirectoryName', array( $this, &$imagesSubdirectory ) );
		return $imagesSubdirectory;
	}

	function setLogo( $logo_name, $tmp_file_name ) {
		global $IP;

		if ( ! is_uploaded_file( $tmp_file_name ) ) {
			return "File failed to upload; unknown error.";
		}
		$logo_dir = $IP . "/skins/common/images/logos/" . $this->getImagesSubirectoryName();
		// If this directory doesn't already exist, create it.
		if ( ! file_exists( $logo_dir ) )
			mkdir( $logo_dir );
		$new_file_name = $logo_dir . "/" . $logo_name;
		if ( ! move_uploaded_file( $tmp_file_name, $new_file_name ) ) {
			return "Unable to move temp file.";
		}

		SSUtils::saveSiteValuesToDB( array( 'logo_file' => $logo_name ) );
	}

	function removeLogo() {
		SSUtils::saveSiteValuesToDB( array( 'logo_file' => '' ) );
	}

	function setFavicon( $favicon_name, $tmp_file_name ) {
		global $IP;

		if ( ! is_uploaded_file( $tmp_file_name ) ) {
			return "File failed to upload; unknown error.";
		}
		$favicon_dir = $IP . "/skins/common/images/favicon/" . $this->getImagesSubirectoryName();

		// If this directory doesn't already exist, create it.
		if ( ! file_exists( $favicon_dir ) ) {
			mkdir( $favicon_dir );
		}
		$new_file_name = $favicon_dir . "/" . $favicon_name;
		if ( ! move_uploaded_file( $tmp_file_name, $new_file_name ) ) {
			return "Unable to move temp file.";
		}

		SSUtils::saveSiteValuesToDB( array( 'favicon_file' => $favicon_name ) );
	}

	function removeFavicon() {
		SSUtils::saveSiteValuesToDB( array( 'favicon_file' => '' ) );
	}

	function create() {
		global $wgSitename, $wgLanguageCode;

		$fieldsAndStartingValues = array();
		foreach ( self::getFields() as $area => $fields ) {
			foreach ( $fields as $fieldName => $fieldVals ) {
				$defaultValue = $fieldVals[1];
				$fieldsAndStartingValues[$fieldName] = $defaultValue;
			}
		}
		$fieldsAndStartingValues['name'] = $wgSitename;
		$fieldsAndStartingValues['namespace'] = $wgSitename;
		$fieldsAndStartingValues['language_code'] = $wgLanguageCode;

		Hooks::run( 'SiteSettingsCreateStartingValues', array( $this, &$fieldsAndStartingValues ) );

		$db = SSUtils::getDBForWriting();
		$db->insert( 'site_settings', $fieldsAndStartingValues );
	}

	function load() {
		global $wgSitename, $wgLocalInterwiki, $wgMetaNamespace;
		global $wgContLang, $wgLanguageCode;
		global $wgLocalTZoffset, $wgAmericanDates;
		global $wgDisableCounters, $wgAllowExternalImages, $wgCapitalLinks;
		global $wgRightsText, $wgRightsUrl;
		global $wgNamespacesWithSubpages;

		Hooks::run( 'SiteSettingsLoadSettingsStart', array( $this ) );

		$i = 0;
		$wgSitename = $this->name;
		$wgLocalInterwiki = $wgSitename;

		$wgLanguageCode = $this->language_code;
		$wgContLang = Language::factory( $wgLanguageCode );

		$wgLocalTZoffset = $this->hours_timezone_offset * 60;
		$wgAmericanDates = $this->use_american_dates;
		$wgDisableCounters = ! $this->show_page_counters;
		if ( $this->use_subpages ) {
			$wgNamespacesWithSubpages = array_fill( 0, 200, true );
		}
		$wgAllowExternalImages = $this->allow_external_images;
		$wgCapitalLinks = ! $this->allow_lowercase_page_names;
		$wgRightsText = $this->copyright_text;
		$wgRightsUrl = $this->copyright_url;
		$this->setAppearanceSettings();
		$this->setPermissions();

		Hooks::run( 'SiteSettingsLoadSettingsEnd', array( $this ) );

		return true;
	}

	function getPrivacyLevel() {
		if ( $this->viewing_policy_id == 1 ) {
			$text = 'public';
		} else {
			$text = 'private';
		}
		Hooks::run( 'SiteSettingsGetPrivacyLevel', array( $this, &$text ) );
		return $text;
	}

	function setPermissions() {
		global $wgGroupPermissions;

		// 'skipcaptcha' is used for both editing and registration
		$wgGroupPermissions['*']['skipcaptcha'] = false;
		//$wgGroupPermissions['autoconfirmed']['skipcaptcha'] = false;
		if ( $this->editing_policy_id == 2 ) { // 'Open with CAPTCHA'
		} elseif ( $this->editing_policy_id == 3 ) { // Closed
			$wgGroupPermissions['*']['edit'] = false;
		} elseif ( $this->editing_policy_id == 4 ) { // Very closed
			$wgGroupPermissions['*']['edit'] = false;
			$wgGroupPermissions['user']['edit'] = false;
			$wgGroupPermissions['sysop']['edit'] = true;
			$wgGroupPermissions['bureaucrat']['edit'] = true;
		}

		// No need to handle 'Public' - that's the default.
		if ( $this->getPrivacyLevel() == 'private' ) {
			$wgGroupPermissions['*']['read'] = false;
		}

		// No need to handle 'Open' - that's the default
		if ( $this->registration_policy_id == 2 ) { // Closed
			$wgGroupPermissions['*']['createaccount'] = false;
		}
	}

	function setAppearanceSettings() {
		$inline_css = '';
		if ( $this->background_color != '' ) {
			$inline_css .=<<<END
body {
	background: #{$this->background_color};
	background-color: #{$this->background_color};
}
#mw-page-base {
	background-color: #{$this->background_color};
	background-image: none;
}

END;
		}
		if ( $this->sidebar_color != '' ) {
			$inline_css .= ".pBody {background-color: #{$this->sidebar_color};} ";
		}
		if ( $this->sidebar_border_color != '' ) {
			$inline_css .= ".pBody {border-color: #{$this->sidebar_border_color};} ";
		}
		if ( $inline_css != '' ) {
			global $wgOut;
			$wgOut->addScript("<style type=\"text/css\">$inline_css</style>\n");
		}
		$logo_filename = str_replace( ' ', '%20', $this->logo_file ); // regular spaces aren't handled correctly by Firefox
		global $wgLogo;
		if ( $logo_filename != null ) {
			$wgLogo = "$wgScriptPath/skins/common/images/logos/" . $logo_filename;
		}
		$favicon_filename = str_replace( ' ', '%20', $this->favicon_file ); // regular spaces aren't handled correctly by Firefox
		global $wgFavicon;
		if ( $favicon_filename != null ) {
			$wgFavicon = "$wgScriptPath/skins/common/images/favicon/" . $favicon_filename;
		}
	}

	function saveSettingsForTab( $tabName ) {
		$valuesToUpdate = array();
		$allFields = self::getFields();
		foreach ( $allFields[$tabName] as $fieldName => $fieldVals ) {
			$valuesToUpdate[$fieldName] = $this->$fieldName;
		}

		$errorMessage = null;
		Hooks::run( 'SiteSettingsSaveTabSettings', array( $tabName, &$valuesToUpdate, &$errorMessage ) );
		if ( !is_null( $errorMessage ) ) {
			return $errorMessage;
		}

		SSUtils::saveSiteValuesToDB( $valuesToUpdate );
	}

}
