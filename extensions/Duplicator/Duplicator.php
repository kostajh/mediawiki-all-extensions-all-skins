<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Special page which creates independent copies of articles, retaining
 * separate histories
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @file
 * @ingroup Extensions
 * @author Rob Church <robchur@gmail.com>
 * @author Vitaliy Filippov <vitalif@mail.ru>
 */

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Duplicator',
	'version' => '1.4.0',
	'author' => array('Rob Church', 'Vitaliy Filippov'),
	'url' => 'https://www.mediawiki.org/wiki/Extension:Duplicator',
	'descriptionmsg' => 'duplicator-desc',
	'license-name' => 'GPL-2.0-or-later',
);

$dir = dirname(__FILE__) . '/';
$wgMessagesDirs['Duplicator'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['DuplicatorAlias'] = $dir . 'Duplicator.alias.php';
$wgAutoloadClasses['SpecialDuplicator'] = $dir . 'Duplicator.page.php';
$wgSpecialPages['Duplicator'] = 'SpecialDuplicator';

$wgHooks['SidebarBeforeOutput'][] = 'efDuplicatorNavigation';
$wgHooks['SkinTemplateToolboxEnd'][] = 'efDuplicatorToolbox';

/**
 * User permissions
 */
$wgGroupPermissions['user']['duplicate'] = true;
$wgAvailableRights[] = 'duplicate';

/**
 * At most this number of recent revisions will be duplicated
 */
$wgDuplicatorRevisionLimit = 250;

/**
 * Build the link to be shown in the toolbox if appropriate
 * @param Skin $skin
 * @param array &$sidebar
 */
function efDuplicatorNavigation( Skin $skin, array &$sidebar ) {
	if ( !$skin->getTitle()->inNamespaces( [ NS_MAIN, NS_TALK ] ) ) {
		return;
	}

	$permissionManager = \MediaWiki\MediaWikiServices::getInstance()->getPermissionManager();
	if ( !$permissionManager->userHasRight( $skin->getUser(), 'duplicate' ) ) {
		return;
	}

	$toolbox = &$sidebar['TOOLBOX'];
	$insert = [
		'duplicator' => [
			'text' => $skin->msg( 'duplicator-toolbox' )->text(),
			'href' => $skin->makeSpecialUrl( 'Duplicator', 'source=' . wfUrlEncode( $skin->thispage ) ),
		]
	];
	if ( isset( $toolbox['permalink'] ) ) {
		$toolbox = wfArrayInsertAfter( $toolbox, $insert, 'permalink' );
	} else {
		$toolbox += $insert;
	}
}

/**
 * Output the toolbox link if available
 */
function efDuplicatorToolbox( &$monobook ) {
	if ( isset( $monobook->data['nav_urls']['duplicator'] ) ) {

		if ( $monobook->data['nav_urls']['duplicator']['href'] == '' ) {
			?><li id="t-isduplicator"><?php echo $monobook->msg( 'duplicator-toolbox' ); ?></li><?php
		} else {
			?><li id="t-duplicator"><?php
				?><a href="<?php echo htmlspecialchars( $monobook->data['nav_urls']['duplicator']['href'] ) ?>"><?php
					echo $monobook->msg( 'duplicator-toolbox' );
				?></a><?php
			?></li><?php
		}
	}
	return true;
}
