<?php // HidePrefix.php //

/*
	------------------------------------------------------------------------------------------------
	HidePrefix, a MediaWiki extension for hiding prefix in links and page titles.
	Copyright (C) 2012 Van de Bugger.

	This program is free software: you can redistribute it and/or modify it under the terms
	of the GNU Affero General Public License as published by the Free Software Foundation,
	either version 3 of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License along with this
	program.  If not, see <https://www.gnu.org/licenses/>.
	------------------------------------------------------------------------------------------------
*/

if ( ! defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}; // if

$extHidePrefixDir = defined( __DIR__ ) ? __DIR__ : dirname( __FILE__ ) ;

global $wgAutoloadClasses;
$wgAutoloadClasses[ 'HidePrefix' ] = $extHidePrefixDir . '/HidePrefix.class.php';

global $wgHooks;
$wgHooks[ 'LinkBegin'         ][] = 'HidePrefix::onLinkBegin';
$wgHooks[ 'BeforePageDisplay' ][] = 'HidePrefix::onBeforePageDisplay';

global $wgMessagesDirs;
$wgMessagesDirs['HidePrefix'] = __DIR__ . '/i18n';

global $wgExtensionCredits;
$wgExtensionCredits[ 'other' ][] = array(
	'path'    => __FILE__,
	'name'    => 'HidePrefix',
	'license-name' => 'AGPL-3.0-or-later',
	'version' => '0.0.1+',
	'author'  => array( '[https://www.mediawiki.org/wiki/User:Van_de_Bugger Van de Bugger]' ),
	'url'     => 'https://www.mediawiki.org/wiki/Extension:HidePrefix',
	'descriptionmsg'  => 'hideprefix-desc',
);

unset( $extHidePrefixDir );

// end of file //
