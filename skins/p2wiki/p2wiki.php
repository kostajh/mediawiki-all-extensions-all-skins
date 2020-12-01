<?php
/**
 * @author automattic http://wordpress.org/extend/themes/profile/automattic
 * @author Daniel Friesen (https://mediawiki.org/wiki/User:Dantman) <mediawiki@danielfriesen.name>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * To install place the p2wiki folder into skins/ and add this line to your LocalSettings.php
 * require_once("$IP/skins/p2wiki/p2wiki.php");
 */

if ( function_exists( 'wfLoadSkin' ) ) {
	wfLoadSkin( 'p2wiki' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['p2wiki'] = __DIR__ . '/i18n';
	/*wfWarn(
		'Deprecated PHP entry point used for p2wiki skin. Please use wfLoadSkin instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the p2wiki skin requires MediaWiki 1.25+' );
}
