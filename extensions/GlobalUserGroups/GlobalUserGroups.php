<?php
/**
 * GlobalUserGroups - adds specified user groups as to all 'user_groups' tables in a wiki family ($wgLocalDatabases)
 *
 * @package MediaWiki
 * @subpackage Extensions
 *
 * @author Tim Weyer (SVG) <svg@tim-weyer.org>
 *
 * @copyright Copyright (C) 2011 by Tim Weyer
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if (!defined('MEDIAWIKI')){
	echo ('THIS IS NOT VALID ENTRY POINT.'); exit (1);
}

$wgExtensionFunctions[] = 'efGlobalUserGroupsEMWT';

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'GlobalUserGroups',
	'url' => 'https://www.mediawiki.org/wiki/Extension:GlobalUserGroups',
	'author' => 'Tim Weyer',
	'descriptionmsg' => 'globalusergroups-desc',
	'version' => '1.1.1',
	'license-name' => 'GPL-2.0-or-later',
);

// Use extra translations for various user group names
$wgGlobalUserGroupsUseEMWT = true;

// Translations
$wgMessagesDirs['GlobalUserGroups'] = __DIR__ . '/i18n/core';
$wgMessagesDirs['GlobalUserGroups.groups'] = __DIR__ . '/i18n/groups';

// Hooks
$wgHooks['UserGroupsChanged'][] = 'efManageGlobalUserGroups';

/**
 * @param $user User
 * @param $addgroup
 * @param $removegroup
 * @return bool
 */
function efManageGlobalUserGroups($user, $addgroup, $removegroup) {
	global $wgGlobalUserGroups, $wgLocalDatabases;

	# Remove groups in all local databases if there is anything to remove
	if (!empty($removegroup)) {
		$global_removeable = array_intersect($removegroup, $wgGlobalUserGroups);

		if (!empty($global_removeable)) {
			foreach ( $wgLocalDatabases as $wikiID ) {
				$db = wfGetDB( DB_MASTER, array(), $wikiID );
				foreach ( $global_removeable as $group ) {
					# delete from all local databases
					$db->delete('user_groups', array(
						'ug_user' => $user->getId(),
						'ug_group' => $group),
						'GlobalUserGroups::removeGroup'
					);
				}
			}
		}
	}

	# Add groups in all local databases if there is anything to add
	if (!empty($addgroup)) {
		$global_addable = array_intersect($addgroup, $wgGlobalUserGroups);

		if (!empty($global_addable)) {
			foreach ( $wgLocalDatabases as $wikiID ) {
				$db = wfGetDB( DB_MASTER, array(), $wikiID );
				foreach ( $global_addable as $group ) {
					# insert into all local databases
					$db->insert('user_groups', array(
						'ug_user' => $user->getId(),
						'ug_group' => $group),
						'GlobalUserGroups::addGroup',
						'IGNORE'
					);
				}
			 }
		}
	}

	return true;
}

function efGlobalUserGroupsEMWT() {
	global $wgGlobalUserGroupsUseEMWT, $wgExtensionMessagesFiles, $wgMessagesDirs;

	if ( $wgGlobalUserGroupsUseEMWT ) {
		$wgMessagesDirs['GlobalUserGroupsExtras'] = __DIR__ . '/i18n/groups';
	}
}
