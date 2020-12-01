<?php

/**
 * Implements Extension:DeleteOwn for the MediaWiki software.
 * Copyright (C) 2014-2015  Tyler Romeo <tylerromeo@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @ingroup Extensions
 * @file
 *
 * @author Tyler Romeo (Parent5446) <tylerromeo@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 */

// Ensure that the script cannot be executed outside of MediaWiki.
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

// Display extension properties on MediaWiki.
$wgExtensionCredits['other'][] = [
	'path' => __FILE__,
	'name' => 'DeleteOwn',
	'descriptionmsg' => 'deleteown-desc',
	'version' => '1.2.0',
	'author' => [
		'Tyler Romeo',
		'...'
	],
	'url' => 'https://www.mediawiki.org/wiki/Extension:DeleteOwn',
	'license-name' => 'GPL-3.0-or-later'
];

// Register extension messages and other localisation.
$wgMessagesDirs['DeleteOwn'] = __DIR__ . '/i18n';

/**
 * The expiry for when a page can no longer be deleted by its author.
 *
 * Can be a single integer value, which is applied to all pages,
 * or can be an array of namespaces mapped to individual expiry values.
 * An expiry of 0 (or not specifying a namespace key) disables deletion
 * and an expiry of INF disables the expiry.
 */
$wgDeleteOwnExpiry = INF;

$wgAvailableRights[] = 'deleteown';

$wgAutoloadClasses['ExtDeleteOwn'] = __DIR__ . '/ExtDeleteOwn.php';

$wgHooks['TitleQuickPermissions'][] = 'ExtDeleteOwn::onTitleQuickPermissions';
