<?php
/**
 * A special page holding a form that allows an administrator to edit site
 * settings.
 *
 * @author Yaron Koren
 */

class SpecialSiteSettings extends SpecialPage {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct( 'SiteSettings' );
	}

	public function doesWrites() {
		return true;
	}

	function execute( $query ) {
		$user = $this->getUser();
		$out = $this->getOutput();

		$this->setHeaders();

		// Permission check
		if( !$user->isAllowed( 'sitesettings' ) ) {
			throw new PermissionsError( 'sitesettings' );
		}

		$this->doSpecialSiteSettings();
	}

	function optionsHTML( $group_name, $options_array, $cur_value ) {
		$text = "";
		foreach ( $options_array as $option ) {
			$attrs = array();
			if ( $option->getID() == $cur_value ) {
				$attrs['checked'] = true;
			}
			$optionText = $option->getName() . ' - '. $option->getDescription();
			Hooks::run( 'SiteSettingsPrivacyOptionDisplay',
				array( $group_name, $option, &$attrs, &$optionText ) );
			$radioHTML = Html::input( $group_name, $option->getID(), 'radio', $attrs );
			$text .= "\t" . Html::rawElement( 'label', null, $radioHTML . ' ' . $optionText );
			$text .= "<br />\n";
		}

		Hooks::run( 'SiteSettingsPrivacyOptionsDisplay',
			array( $group_name, &$text ) );

		return $text;
	}

	public static function printTab( $tabInfo ) {
		$text =<<<END
	<fieldset id="mw-prefsection-{$tabInfo['id']}">
	<legend>{$tabInfo['title']}</legend>
{$tabInfo['body']}
	</fieldset>

END;
		return $text;
	}

	function mainTabBody( $siteSettings ) {
		global $wgLanguageCode;

		// Code based on dropdown menu from SpecialPreferences.php.
		$languages = Language::fetchLanguageNames( null, 'mw' );
		if ( !array_key_exists( $wgLanguageCode, $languages ) ) {
			$languages[$siteSettings->language_code] = $siteSettings->language_code;
		}
		ksort( $languages );
		// Default to English, if language can't be found in list.
		$selectedLang = isset( $languages[$siteSettings->language_code] ) ? $siteSettings->language_code : $wgLanguageCode;
		$options = "\n";
		foreach( $languages as $code => $name ) {
			$selected = ( $code == $selectedLang );
			$options .= Xml::option( "$code - $name", $code, $selected ) . "\n";
		}
		$american_dates_checkbox = Xml::check( 'use_american_dates', $siteSettings->use_american_dates );
		$show_page_counters_checkbox = Xml::check( 'show_page_counters', $siteSettings->show_page_counters );
		$use_subpages_checkbox = Xml::check( 'use_subpages', $siteSettings->use_subpages );
		$allow_external_images_checkbox = Xml::check( 'allow_external_images', $siteSettings->allow_external_images );
		$allow_lowercase_page_names_checkbox = Xml::check( 'allow_lowercase_page_names', $siteSettings->allow_lowercase_page_names );

		$name_label = wfMessage( 'sitesettings-sitename' )->text();
		$language_label = wfMessage( 'yourlanguage')->text();
		$namespace_label = wfMessage( 'sitesettings-sitenamespace' )->text();
		$american_dates_label = wfMessage( 'sitesettings-americandates' )->text();
		$show_page_counters_label = wfMessage( 'sitesettings-showpageviews' )->text();
		$use_subpages_label = wfMessage( 'sitesettings-usesubpages' )->text();
		$allow_external_images_label = wfMessage( 'sitesettings-allowexternalimages' )->text();
		$allow_lowercase_page_names_label = wfMessage( 'sitesettings-allowlowercasepagenames' )->text();
		$copyright_desc_label = wfMessage( 'sitesettings-copyrightdesc' )->text();
		$copyright_url_label = wfMessage( 'sitesettings-copyrighturl' )->text();
		$text =<<<END
	<p><label>$name_label
	<input type="text" name="name" value="{$siteSettings->name}" size="50"/>
	</label></p>
	<p>
	<label>$language_label
	<select name='language_code' id='language_code'>$options</select>
	</label></p>
	<p><label>$namespace_label
	<input type="text" name="namespace" value="{$siteSettings->namespace}" size="50"/>
	</label></p>

END;

		$timezone_label = wfMessage( 'sitesettings-timezone' )->text();
		$gmt_time = time() - date("Z");
		$gmt_time_str = date("H:i", $gmt_time);
		$gmt_time_str2 = date("h:i A", $gmt_time);
		$current_gmt_time_label = wfMessage( 'sitesettings-current-gmt', $gmt_time_str, $gmt_time_str2 )->text();
		$text .=<<<END
	<p><label>$timezone_label
	<input type="text" name="hours_timezone_offset" value="{$siteSettings->hours_timezone_offset}" size="3"/>
	$current_gmt_time_label</label></p>
	<p><label>$american_dates_checkbox $american_dates_label</label><br />
	<label>$show_page_counters_checkbox $show_page_counters_label</label><br />
	<label>$use_subpages_checkbox $use_subpages_label</label><br />
	<label>$allow_external_images_checkbox $allow_external_images_label</label><br />
	<label>$allow_lowercase_page_names_checkbox $allow_lowercase_page_names_label</label><br />
	</p>
	<p>
	<label>$copyright_desc_label <input type="text" name="copyright_text" value="{$siteSettings->copyright_text}" size="50" /></label>
	</p>
	<p>
	<label>$copyright_url_label <input type="text" name="copyright_url" value="{$siteSettings->copyright_url}" size="50" /></label>
	</p>

END;

		Hooks::run( 'SiteSettingsMainTab', array( $siteSettings, &$text ) );
		$update_label = wfMessage( 'sitesettings-update' )->text();
		$text .=<<<END
	<p><input type="Submit" name="update" value="$update_label" id="prefsubmit"></p>

END;

		return $text;
	}

	function privacyTabBody( $siteSettings ) {
		// Viewing policy
		$viewing_policies = array(
			SSFieldLevel::create( 1, wfMessage( 'sitesettings-public' )->text(), wfMessage( 'sitesettings-publicdesc' )->text() ),
			SSFieldLevel::create( 2, wfMessage( 'sitesettings-private' )->text(), wfMessage( 'sitesettings-privatedesc' )->text() ),
			SSFieldLevel::create( 3, wfMessage( 'sitesettings-veryprivate' )->text(), wfMessage( 'sitesettings-veryprivatedesc' )->text() )
		);
		$text = "\t" . Html::element( 'h2', null, wfMessage( 'sitesettings-viewingpolicy' )->text() ) . "\n";
		$text .= $this->optionsHTML( "viewing_policy_id", $viewing_policies, $siteSettings->viewing_policy_id );

		// Registration policy
		$registration_policies = array(
			SSFieldLevel::create( 1, wfMessage( 'sitesettings-openreg' )->text(), wfMessage( 'sitesettings-openregdesc' )->text() ),
			SSFieldLevel::create( 2, wfMessage( 'sitesettings-closedreg' )->text(), wfMessage( 'sitesettings-closedregdesc' )->text() ),
		);
		$text .= "\t" . Html::element( 'h2', null, wfMessage( 'sitesettings-registrationpolicy' )->text() ) . "\n";
		$text .= $this->optionsHTML( "registration_policy_id", $registration_policies, $siteSettings->registration_policy_id );

		// Editing policy
		$editing_policies = array(
			SSFieldLevel::create( 2, wfMessage( 'sitesettings-openediting')->text(), wfMessage( 'sitesettings-openeditingdesc' )->text() ),
			SSFieldLevel::create( 3, wfMessage( 'sitesettings-closedediting')->text(), wfMessage( 'sitesettings-closededitingdesc' )->text() ),
			SSFieldLevel::create( 4, wfMessage( 'sitesettings-veryclosedediting')->text(), wfMessage( 'sitesettings-veryclosededitingdesc' )->text() ),
		);
		$text .= "\t" . Html::element( 'h2', null, wfMessage( 'sitesettings-editingpolicy' )->text() ) . "\n";
		$text .= $this->optionsHTML( "editing_policy_id", $editing_policies, $siteSettings->editing_policy_id );

		Hooks::run( 'SiteSettingsPrivacyTab', array( $siteSettings, &$text ) );

		$update_label = wfMessage( 'sitesettings-update' )->text();
		$text .=<<<END
	<p><input type="Submit" name="update-privacy" value="$update_label" id="prefsubmit"></p>

END;

		return $text;
	}

	function skinTabBody( $siteSettings ) {
		global $wgDefaultSkin;

		$validSkinNames = Skin::getSkinNames();
		# Sort by UI skin name. First though need to update validSkinNames as sometimes
		# the skinkey & UI skinname differ (e.g. "standard" skinkey is "Classic" in the UI).
		foreach ( $validSkinNames as $skinkey => & $skinname ) {
			if ( isset( $skinNames[$skinkey] ) ) {
				$skinname = $skinNames[$skinkey];
			}
		}
		asort( $validSkinNames );
		// Default to $wgDefaultSkin, if skin isn't set.
		$selectedSkin = isset( $siteSettings->default_skin ) ? $siteSettings->default_skin : $wgDefaultSkin;
		$default_skin_label = wfMessage('prefs-skin')->text();
		$text =<<<END
	<h2><label for="default_skin">$default_skin_label</label></h2>
	<p>

END;
		$mptitle = Title::newMainPage();
		foreach ( $validSkinNames as $skinkey => $sn ) {
			$previewtext = wfMessage( 'skin-preview' )->text();
			$attrs = array( 'id' => $skinkey );
			if ( $skinkey == $selectedSkin ) {
				$attrs['checked'] = true;
			}
			$text .= Html::input( 'default_skin', $skinkey, 'radio', $attrs ) . ' ';
			$text .= Html::element( 'label', array( 'for' => $skinkey), $sn ) . ' ';
			$mplink = $mptitle->getLocalURL( "useskin=$skinkey" );
			$text .= Html::element( 'a',
				array( 'target' => '_blank',
					'href' => $mplink,
				),
				$previewtext
			) . "<br />\n";
		}
		$text .= "</p>\n";
		Hooks::run( 'SiteSettingsSkinTab', array( $siteSettings, &$text ) );

		$update_label = wfMessage( 'sitesettings-update' )->text();
		$text .=<<<END
	<p><input type="Submit" name="update-appearance" value="$update_label" id="prefsubmit"></p>
END;

		$text .=<<<END
	<h2><label for="reset-user-skins">Reset all users' skins</label></h2>
	<p style="font-style: italic">The following button will change all your current users' skins to whatever the default skin currently is; please be sure that you want to make this change, because it cannot be undone:</p>
	<p><input type="Submit" name="reset-user-skins" value="Reset all skins" id="prefsubmit"></p>

END;

		return $text;

	}

	static function printFaviconSection( $siteSettings, $favicon_error_msg ) {
		$text = Html::element( 'h2', null, wfMessage( 'sitesettings-faviconheader' )->text() ) . "\n";
		$current_favicon = $siteSettings->favicon_file;
		if ( $current_favicon ) {
			global $wgFavicon;
			$current_favicon_label = wfMessage( 'sitesettings-currentfavicon' )->text();
			$removeFaviconButton = Html::input(
				'remove_favicon',
				wfMessage( 'sitesettings-removefavicon' )->text(),
				'submit'
			);
			$change_favicon_label = wfMessage( 'sitesettings-changefavicon' )->text();
			$text .=<<<END
	<p>$current_favicon_label <a href="$wgFavicon">$current_favicon</a></p>
	<p>$removeFaviconButton</p>
	<p>$change_favicon_label <input type="file" name="favicon_file" size="25" /> <font color="red">$favicon_error_msg</font></p>

END;
		} else {
			$upload_favicon_label = wfMessage( 'sitesettings-uploadfavicon' )->text();
			$text .= "<p>" . wfMessage( 'sitesettings-nofavicon' )->text() . "</p>\n";
			$text .=<<<END
	<p>$upload_favicon_label <input type="file" name="favicon_file" size="25" /> <font color="red">$favicon_error_msg</font></p>

END;
		}
		$upload_label = wfMessage( 'uploadbtn' )->text();
		$text .=<<<END
	<p><input type="Submit" name="upload_favicon" value="$upload_label"></p>

END;
		Hooks::run( 'SiteSettingsFaviconInput', array( $siteSettings, &$text ) );
		return $text;
	}

	function logoTabBody( $siteSettings, $logo_error_msg, $favicon_error_msg ) {
		$text = "\t" . Html::element( 'h2', null, wfMessage( 'sitesettings-sitelogo' )->text() ) . "\n";

		$current_logo = $siteSettings->logo_file;
		if ( $current_logo ) {
			global $wgLogo;
			$current_logo_label = wfMessage( 'sitesettings-currentlogo' )->text();
			$remove_logo_label = wfMessage( 'sitesettings-removelogo' )->text();
			$change_logo_label = wfMessage( 'sitesettings-changelogo' )->text();
			$text .=<<<END
	<p>$current_logo_label <a href="$wgLogo">$current_logo</a></p>
	<p><input type="Submit" name="remove_logo" value="$remove_logo_label"></p>
	<p>$change_logo_label <input type="file" name="logo_file" size="25" /> <font color="red">$logo_error_msg</font></p>

END;
		} else {
			$upload_logo_label = wfMessage( 'sitesettings-uploadlogo' )->text();
			$text .= "<p>" . wfMessage( 'sitesettings-nologo' )->text() . "</p>\n";
			$text .=<<<END
	<p>$upload_logo_label <input type="file" name="logo_file" size="25" /> <font color="red">$logo_error_msg</font></p>

END;
		}
		$upload_label = wfMessage( 'uploadbtn' )->text();
		$text .=<<<END
	<p><input type="Submit" name="upload_logo" value="$upload_label"></p>

END;
		$text .= self::printFaviconSection( $siteSettings, $favicon_error_msg );

		return $text;
	}

	/**
	 * Provides output to the user for a result of UploadBase::verifyUpload().
	 * Based heavily on SpecialUpload::processVerificationError().
	 *
	 * @param array $details Result of UploadBase::verifyUpload
	 * @throws MWException
	 */
	function displayVerificationError( $details ) {
		switch ( $details['status'] ) {
			/** Statuses that only require name changing **/
			case UploadBase::MIN_LENGTH_PARTNAME:
				return $this->msg( 'minlength1' )->escaped();
			case UploadBase::ILLEGAL_FILENAME:
				return $this->msg( 'illegalfilename', $details['filtered'] )->parse();
			case UploadBase::FILENAME_TOO_LONG:
				return $this->msg( 'filename-toolong' )->escaped();
			case UploadBase::FILETYPE_MISSING:
				return $this->msg( 'filetype-missing' )->parse();
			case UploadBase::WINDOWS_NONASCII_FILENAME:
				return $this->msg( 'windows-nonascii-filename' )->parse();

			/** Statuses that require reuploading **/
			case UploadBase::EMPTY_FILE:
				return $this->msg( 'emptyfile' )->escaped();
			case UploadBase::FILE_TOO_LARGE:
				return $this->msg( 'largefileserver' )->escaped();
			case UploadBase::FILETYPE_BADTYPE:
				$msg = $this->msg( 'filetype-banned-type' );
				if ( isset( $details['blacklistedExt'] ) ) {
					$msg->params( $this->getLanguage()->commaList( $details['blacklistedExt'] ) );
				} else {
					$msg->params( $details['finalExt'] );
				}
				$extensions = array_unique( $this->getConfig()->get( 'FileExtensions' ) );
				$msg->params( $this->getLanguage()->commaList( $extensions ),
					count( $extensions ) );

				// Add PLURAL support for the first parameter. This results
				// in a bit unlogical parameter sequence, but does not break
				// old translations
				if ( isset( $details['blacklistedExt'] ) ) {
					$msg->params( count( $details['blacklistedExt'] ) );
				} else {
					$msg->params( 1 );
				}

				return $msg->parse();
			case UploadBase::VERIFICATION_ERROR:
				unset( $details['status'] );
				$code = array_shift( $details['details'] );
				return $this->msg( $code, $details['details'] )->parse();
			case UploadBase::HOOK_ABORTED:
				if ( is_array( $details['error'] ) ) { # allow hooks to return error details in an array
					$args = $details['error'];
					$error = array_shift( $args );
				} else {
					$error = $details['error'];
					$args = null;
				}

				return $this->msg( $error, $args )->parse();
			default:
				return "Upload error: unknown value `{$details['status']}`";
		}
	}

	function doSpecialSiteSettings() {
		$out = $this->getOutput();
		$request = $this->getRequest();

		// add CSS and JS
		$out->addModules( 'ext.sitesettings.main' );
		$out->addModules( 'mediawiki.special.preferences' );

		// initialize variables
		$siteSettings = new SiteSettings();

		$logo_error_msg = false;
		$favicon_error_msg = false;
		$text = "";

		if ( $request->getCheck( 'update' ) ) {
			$siteSettings->updateFromQuery( $_POST );
			$res = $siteSettings->saveSettingsForTab( 'main' );
			if ( is_null( $res ) ) {
				$text .= '<div class="successbox">' . wfMessage( 'sitesettings-updated' )->text() . "</div>\n";
			} else {
				$text .= '<div class="errorbox">' . $res . "</div>\n";
			}
			$siteSettings = SiteSettings::newFromDatabase();
		} elseif ( $request->getCheck( 'update-services' ) ) {
			$siteSettings->updateFromQuery( $_POST );
			$siteSettings->saveSettingsForTab( 'web services' );
			$siteSettings = SiteSettings::newFromDatabase();
			$text .= '<div class="successbox">' . wfMessage( 'sitesettings-updated' )->text() . "</div>\n";
		} elseif ( $request->getCheck( 'update-privacy' ) ) {
			$siteSettings->updateFromQuery( $_POST );
			$siteSettings->saveSettingsForTab( 'privacy' );
			$siteSettings = SiteSettings::newFromDatabase();
			$text .= '<div class="successbox">' . wfMessage( 'sitesettings-updated' )->text() . "</div>\n";
		} elseif ( $request->getCheck( 'update-appearance' ) ) {
			$siteSettings->updateFromQuery( $_POST );
			$siteSettings->saveSettingsForTab( 'skin' );
			$siteSettings = SiteSettings::newFromDatabase();
			$text .= '<div class="successbox">' . wfMessage( 'sitesettings-updated' )->text() . "</div>\n";
		} elseif ( $request->getCheck( 'reset-user-skins' ) ) {
			// based on /maintenance/userOptions.inc
			$siteSettings = SiteSettings::newFromDatabase();
			$result = $dbr->select( 'user',
				array( 'user_id' ),
				array(),
				__METHOD__
			);
			while( $id = $dbr->fetchObject( $result ) ) {
				$user = User::newFromId( $id->user_id );
				$user->setOption( 'skin', $siteSettings->default_skin );
				$user->saveSettings();
			}
			$text .= '<div class="successbox">' . wfMessage( 'sitesettings-userskinsreset', $siteSettings->default_skin )->text() . "</div>\n";
		} elseif ( $request->getCheck( 'upload_logo' ) ) {
			$webRequestUpload = $request->getUpload( 'logo_file' );
			$filename = $webRequestUpload->getName();
			$uploader = new UploadFromFile();
			$uploader->initialize( $filename, $webRequestUpload );
			$details = $uploader->verifyUpload();
			if ( $details['status'] == UploadBase::OK ) {
				$logo_error_msg = $siteSettings->setLogo( $filename, $webRequestUpload->getTempName() );
				$text .= '<div class="successbox">' . wfMessage( 'sitesettings-logouploaded' )->text() . "</div>\n";
			} else {
				$logo_error_msg = $this->displayVerificationError( $details );
			}
			$siteSettings = SiteSettings::newFromDatabase();
		} elseif ( $request->getCheck( 'remove_logo' ) ) {
			$siteSettings->removeLogo();
			$siteSettings = SiteSettings::newFromDatabase();
			$text .= '<div class="successbox">' . wfMessage( 'sitesettings-logoremoved' )->text() . "</div>\n";
		} elseif ( $request->getCheck( 'upload_favicon' ) ) {
			$webRequestUpload = $request->getUpload( 'favicon_file' );
			$filename = $webRequestUpload->getName();
			$uploader = new UploadFromFile();
			$uploader->initialize( $filename, $webRequestUpload );
			$details = $uploader->verifyUpload();
			if ( $details['status'] == UploadBase::OK ) {
				$favicon_error_msg = $siteSettings->setFavicon( $filename, $webRequestUpload->getTempName() );
				$text .= '<div class="successbox">' . wfMessage( 'sitesettings-faviconuploaded' )->text() . "</div>\n";
			} else {
				$favicon_error_msg = $this->displayVerificationError( $details );
			}
			$siteSettings = SiteSettings::newFromDatabase();
		} elseif ( $request->getCheck( 'remove_favicon' ) ) {
			$siteSettings->removeFavicon();
			$siteSettings = SiteSettings::newFromDatabase();
			$text .= '<div class="successbox">' . wfMessage( 'sitesettings-faviconremoved' )->text() . "</div>\n";
		} else {
			// Let other extensions handle information saves
			$siteSettings = SiteSettings::newFromDatabase();
			Hooks::run( 'SiteSettingsUpdate', array( &$siteSettings, &$text ) );
		}

		$allTabInfo = array(
			array(
				// Total @HACK - why is the ID for this tab
				// 'personal', instead of 'main'? Because the
				// JS for Special:Preferences, which is being
				// used here, assumes that the first tab will
				// be called 'personal' when making it the
				// default selected tab.
				'id' => 'personal',
				'title' => wfMessage( 'sitesettings-maintab' )->text(),
				'body' => $this->mainTabBody( $siteSettings )
			),
			array(
				'id' => 'privacy',
				'title' => wfMessage( 'sitesettings-privacytab' )->text(),
				'body' => $this->privacyTabBody( $siteSettings )
			),
			array(
				'id' => 'skin',
				'title' => wfMessage('prefs-skin')->text(),
				'body' => $this->skinTabBody( $siteSettings )
			),
			array(
				'id' => 'logo',
				'title' => wfMessage( 'sitesettings-sitelogo' )->text(),
				'body' => $this->logoTabBody( $siteSettings, $logo_error_msg, $favicon_error_msg )
			)
		);

		$text .= '<ul id="preftoc" role="tablist">' . "\n";
		foreach ( $allTabInfo as $i => $tabInfo ) {
			$tabID = $tabInfo['id'];

			$linkHTML = Html::element( 'a', array(
				'id' => "preftab-$tabID",
				'role' => 'tab',
				'href' => "#mw-prefsection-$tabID",
				'aria-controls' => "mw-prefsection-$tabID",
				'aria-selected' => ( $i == 0 ) ? 'true' : 'false',
				'tabindex' => ( $i == 0 ) ? 0 : -1
			), $tabInfo['title'] );
			$liAttrs = array( 'role' => 'presentation' );
			if ( $i == 0 ) {
				$liAttrs['class'] = 'selected';
			}
			$text .= Html::rawElement( 'li', $liAttrs, $linkHTML );
		}

		$text .=<<<END
	</ul>
	<form enctype="multipart/form-data" action="" method="post" class="visualClear" id="mw-prefs-form">
	<div id="preferences">

END;
		// Print the tabs!
		foreach ( $allTabInfo as $i => $tabInfo ) {
			$text .= $this->printTab( $tabInfo );
		}
		Hooks::run( 'SiteSettingsTabs', array( &$text, $siteSettings ) );
		$text .=<<<END
	</div>
	</form>

END;

		$out->addHTML( $text );
		$out->addHTML( Html::rawElement( 'div', array( 'class' => "prefcache" ),
			wfMessage( 'clearyourcache', 'parseinline' )->text() )
		);
	}

}