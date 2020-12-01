<?php
/***
 *
 * OnlyRecentRecentChanges extension for MediaWiki
 *
 * The behaviour of the recent changes view is changed
 * so that any changed article is only listed once.
 * Older changes are not listed any longer.
 *
 * Manual and homepage:
 *
 * https://www.mediawiki.org/wiki/Extension:OnlyRecentRecentChanges
 *
 * Installation:
 *
 * cd $IP/extensions
 * git clone https://github.com/Wikinaut/MediaWiki-extension-OnlyRecentRecentChanges.git OnlyRecentRecentChanges
 *
 * add the following to LocalSettings.php:
 * require_once( "$IP/extensions/OnlyRecentRecentChanges/OnlyRecentRecentChanges.php" );
 *
 * @file
 * @ingroup Extensions
 * @author Thomas Gries
 * @license GPL v2
 * @license MIT
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 **/

if( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'OnlyRecentRecentChanges',
	'descriptionmsg' => 'onlyrecentrecentchanges-desc',
	'version' => '1.4.0',
	'author' => 'Thomas Gries',
	'url' => 'https://www.mediawiki.org/wiki/Extension:OnlyRecentRecentChanges',
);

$dir = dirname( __FILE__ );
$wgMessagesDirs['OnlyRecentRecentChanges'] = __DIR__ . '/i18n';
$wgHooks['GetPreferences'][] = 'onGetPreferences';
$wgHooks['ChangesListSpecialPageQuery'][] = 'onChangesListSpecialPageQuery';

# New option and its default value
$wgDefaultUserOptions['onlyrecentrecentchanges-show-only-recent-change'] = true;

// see https://www.mediawiki.org/wiki/Manual:Hooks/ChangesListSpecialPageQuery
function onChangesListSpecialPageQuery( $name, &$tables, &$fields, &$conds, &$query_options, &$join_conds, $opts  ) {
	if ( RequestContext::getMain()->getUser()
		->getOption( 'onlyrecentrecentchanges-show-only-recent-change' ) ) {
		$dbr = wfGetDB( DB_REPLICA );

		if ( !in_array( 'page', $tables) ) array_unshift( $tables, 'page' );

		$conds[] = $dbr->makeList(
			array(
				'page_latest = rc_this_oldid',
				'rc_log_action != "" '
			),
			LIST_OR
		);

		$join_conds['page'] = array(
			'LEFT JOIN',
			'page_latest = rc_this_oldid'
		);

	}
	return true;
}

/**
 * @param $user User
 * @param $preferences array
 * @return bool
 */
function onGetPreferences( $user, &$preferences ) {
	$preferences['onlyrecentrecentchanges-show-only-recent-change'] = array(
		'section' => 'rc/advancedrc',
		'type' => 'toggle',
		'label-message' => 'onlyrecentrecentchanges-option'
	);
	return true;
}

