<?php

/**
 * GroupManager Extension for BlueSpice
 *
 * Administration interface for adding, editing and deleting usergroups.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2018 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

namespace BlueSpice\GroupManager;

use BlueSpice\DynamicSettingsManager;

class Extension extends \BlueSpice\Extension {

	/**
	 * saves all groupspecific data to a config file
	 * @return array the json answer
	 */
	public static function saveData() {
		global $wgAdditionalGroups;
		$saveContent = "<?php\n\$GLOBALS['wgAdditionalGroups'] = [];\n\n";
		foreach ( $wgAdditionalGroups as $group => $value ) {
			$nameErrors = self::getNameErrors( $group );
			if ( !empty( $nameErrors ) ) {
				return $nameErrors;
			} else {
				if ( $value !== false ) {
					$saveContent .= "\$GLOBALS['wgAdditionalGroups']['{$group}'] = [];\n";
					self::checkI18N( $group );
				} else {
					self::checkI18N( $group, $value );
				}
			}
		}

		$saveContent .= "\n\$GLOBALS['wgGroupPermissions'] = "
			. "array_merge(\$GLOBALS['wgGroupPermissions'], \$GLOBALS['wgAdditionalGroups']);";

		$dynamicSettingsManager = DynamicSettingsManager::factory();
		$status = $dynamicSettingsManager->persist( 'GroupManager', $saveContent );
		$res = $status->isGood();
		if ( $res ) {
			return [
				'success' => true,
				'message' => \wfMessage( 'bs-groupmanager-grpadded' )->plain()
			];
		} else {
			return [
				'success' => false,
				'message' => wfMessage(
					'bs-groupmanager-write-config-file-error',
					'gm-settings.php'
				)
			];
		}
	}

	/**
	 *
	 * @param string $name
	 * @return array
	 */
	public static function getNameErrors( $name ) {
		$invalidChars = [];
		$name = trim( $name );
		if ( substr_count( $name, '\'' ) > 0 ) {
			$invalidChars[] = '\'';
		}
		if ( substr_count( $name, '"' ) > 0 ) {
			$invalidChars[] = '"';
		}
		if ( !empty( $invalidChars ) ) {
			return [
				'success' => false,
				'message' => \wfMessage( 'bs-groupmanager-invalid-name' )
					->numParams( count( $invalidChars ) )
					->params( implode( ',', $invalidChars ) )
					->text()
			];
		} elseif ( preg_match( "/^[0-9]+$/", $name ) ) {
			return [
				'success' => false,
				'message' => \wfMessage( 'bs-groupmanager-invalid-name-numeric' )->plain()
			];
		} elseif ( strlen( $name ) > 255 ) {
			return [
				'success' => false,
				'message' => \wfMessage( 'bs-groupmanager-invalid-name-length' )->plain()
			];
		}
		return [];
	}

	/**
	 *
	 * @param string $group
	 * @param bool $value
	 */
	public static function checkI18N( $group, $value = true ) {
		$title = \Title::newFromText( 'group-' . $group, NS_MEDIAWIKI );
		$user = \RequestContext::getMain()->getUser();

		if ( $value === false ) {
			if ( $title->exists() ) {
				\WikiPage::factory( $title )->doDeleteArticleReal(
					'Group does not exist anymore',
					$user
				);
			}
		} else {
			if ( !$title->exists() ) {
				\WikiPage::factory( $title )->doEditContent(
					\ContentHandler::makeContent( $group, $title ),
					'',
					EDIT_NEW,
					false,
					$user
				);
			}
		}
	}

}
