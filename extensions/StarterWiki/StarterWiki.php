<?php
/**
 * StarterWiki
 *
 * @file
 * @ingroup Extensions
 * @author Daniel Friesen (http://danf.ca/mw/)
 * @license GPL-2.0-or-later
 * @link https://www.mediawiki.org/wiki/Extension:StarterWiki Documentation
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is an extension to the MediaWiki package and cannot be run standalone." );
}

$wgExtensionCredits['other'][] = [
	'path' => __FILE__,
	'name' => 'StarterWiki',
	'url' => 'https://www.mediawiki.org/wiki/Extension:StarterWiki',
	'version' => '1.3.0',
	'author' => "[http://danf.ca/mw/ Daniel Friesen]",
	'descriptionmsg' => 'starterwiki-desc'
];

## i18n
$wgMessagesDirs['StarterWiki'] = __DIR__ . '/i18n';

## A list of pages to convert titles and override when cloning the database.
## Key is the ns:title on starter, value is the ns:title to use on the other wiki.
$wgStarterWikiPageAliases = [
	'0:Main_Page/Starter' => '0:Main_Page'
];

## Array of namespaces to exclude from cloning.
$wgStarterWikiOmitNamespaces = [
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_TALK,
	# Don't clone userpages, users may not be the same.
	NS_USER,
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_USER_TALK,
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_PROJECT_TALK,
	# Don't clone image pages, files are not cloned.
	NS_FILE,
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_FILE_TALK,
	# Don't clone messages, a shared system should be used.
	NS_MEDIAWIKI,
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_MEDIAWIKI_TALK,
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_TEMPLATE_TALK,
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_HELP_TALK,
	# Don't clone talkpages, discussions shouldn't be cloned.
	NS_CATEGORY_TALK,
];
