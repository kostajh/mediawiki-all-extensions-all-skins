<?php
/**
 * GoToShell MediaWiki extension.
 *
 * Written by Leucosticte
 * https://www.mediawiki.org/wiki/User:Leucosticte
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
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Extensions
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'GoToShell' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['GoToShell'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['GoToShellAlias'] = __DIR__ . '/GoToShell.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for GoToShell extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the GoToShell extension requires MediaWiki 1.29+' );
}
