<?php
/**
 * Provides parser functions to retrieve/set useful page level information.
 *
 * @author   Jean-Lou Dupont (http://www.bluecortex.com)
 * @author   Stephan Gambke
 *
 * @defgroup PageTools PageTools
 *
 * Features:
 * *********
 *
 * {{#pageincategory: 'category' }}
 *    returns 'true' if the current page is categorised with 'category'
 *
 * {{#pagenumcategories:}}
 *    returns the number of categories found for the current page.
 *
 * {{#pagecategory: 'index' }}
 *    returns the category title indexed with 'index'
 *
 * {{#pagetitle: new title name}}
 *
 * {{#pagetitleadd: text to be added to the title name}}
 *
 * {{#pagesubtitle: text to be added to the page's subtitle }}
 *
 * <deflate>text  to be    deflated</deflate>
 *    replaces all accumulations of whitespace (incl. newlines and tabs) by one single whitespace
 *
 * HISTORY:
 * -- Version 1.0: initial availability
 * -- Version 1.1: Added 'pagetitle': to modify the page's title.
 *                 Added 'pagetitleadd': to add to the current page title.
 *                 Added 'pagesubtitle': to modify the page's subtitle
 * -- Version 2.0: Complete overhaul for compatibility to MW 1.22+.
 * -- Version 2.1.0: Internationalisation format migrated to JSON.
 */

/**
 * The main file of the PageTools extension
 *
 * @copyright (C) 2013, Jean-Lou Dupont, Stephan Gambke
 * @license GPL-3.0-or-later
 *
 * This file is part of the MediaWiki extension PageTools.
 * The PageTools extension is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The PageTools extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       PageTools
 */
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'PageTools' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['PageTools'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['PageToolsMagic'] = __DIR__ . '/PageTools.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for the PageTools extension. ' .
		'Please use wfLoadExtension() instead, ' .
		'see https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the PageTools extension requires MediaWiki 1.29+' );
}
