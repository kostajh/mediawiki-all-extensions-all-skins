<?php
/**
 * File defining the settings for the MultiMaps extension.
 * More info can be found at https://www.mediawiki.org/wiki/Extension:MultiMaps/Configuration
 *
 *						  NOTICE:
 * Changing one of these settings can be done by copieng or cutting it,
 * and placing it in LocalSettings.php, AFTER the inclusion of MultiMaps.
 *
 * @file Settings.php
 * @ingroup MultiMaps
 * @author Pavel Astakhov <pastakhov@yandex.ru>
 */

// Check to see if we are being called as an extension or directly
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is an extension to MediaWiki and thus not a valid entry point.' );
}

// Default settings

// Array of String. Array containing all the mapping services that will be made available to the user.
// Firs value - default service, which will be used if the service is not in the parameters
// Values may be a valid name of class based on class BaseMapService
$egMultiMaps_MapServices = [
	'Leaflet',
	'Google',
	'Yandex',
];

// Integer. The default zoom of a map. This value will only be used when the
// user does not provide one.
$egMultiMaps_DefaultZoom = 14;

// TODO description
$egMultiMaps_SeparatorItems = ';';
$egMultiMaps_DelimiterParam = '~';
$egMultiMaps_OptionsSeparator = ',';
$egMultiMaps_CoordinatesSeparator = ':';

// Integer or string. The default width and height of a map. These values will
// only be used when the user does not provide them.
$egMultiMaps_Width = 'auto';
$egMultiMaps_Height = '350px';

// Boolean. If true, allow specify an icon for the marker from the directory
$egMultiMaps_IconAllowFromDirectory = false;
// String. The URL base path to the directory containing icons for markers
$egMultiMaps_IconPath = "$wgScriptPath/mapicons";

// TODO
// $egMultiMaps_GoogleApiKey = false;
// $egMultiMaps_YandexApiKey = false;
