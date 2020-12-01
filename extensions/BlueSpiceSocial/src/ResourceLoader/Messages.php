<?php
/**
 * ResourceLoader class for BlueSpiceSocialMessages resource module for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Patric Wirth
 * @package    BlueSpiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */
namespace BlueSpice\Social\ResourceLoader;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Social\EntityConfig;
use MediaWiki\MediaWikiServices;

class Messages extends \ResourceLoaderModule {
	/**
	 * Get the messages needed for this module.
	 *
	 * To get a JSON blob with messages, use MessageBlobStore::get()
	 *
	 * @return array List of message keys. Keys may occur more than once
	 */
	public function getMessages() {
		$messages = $msgKeys = [];
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		$configFactory = MediaWikiServices::getInstance()->getService( 'BSEntityConfigFactory' );

		foreach ( $registry->getAllKeys() as $type ) {
			$config = $configFactory->newFromType( $type );
			if ( !$config ) {
				continue;
			}
			if ( !$config instanceof EntityConfig ) {
				continue;
			}
			if ( !empty( $config->get( 'HeaderMessageKey' ) ) ) {
				$messages[] = $config->get( 'HeaderMessageKey' );
			}
			if ( !empty( $config->get( 'HeaderMessageKeyCreateNew' ) ) ) {
				$messages[] = $config->get( 'HeaderMessageKeyCreateNew' );
			}
			if ( !empty( $config->get( 'TypeMessageKey' ) ) ) {
				$messages[] = $config->get( 'TypeMessageKey' );
			}
			if ( empty( $config->get( 'VarMessageKeys' ) ) ) {
				continue;
			}
			$msgKeys = array_merge(
				$msgKeys,
				$config->get( 'VarMessageKeys' )
			);
		}
		$msgKeys = array_merge(
			$msgKeys,
			$this->getLegacyMessageKeys()
		);
		foreach ( $msgKeys as $varName => $msgKey ) {
			if ( empty( $msgKey ) ) {
				continue;
			}
			$messages[] = $msgKey;
		}
		return $messages;
	}

	/**
	 * DEPRECATED
	 * @deprecated since version 3.2
	 * @return array
	 */
	private function getLegacyMessageKeys() {
		if ( empty( $GLOBALS['wgHooks']['BSSocialModuleDepths'] ) ) {
			return [];
		}
		wfDebugLog( 'bluespice-deprecations', __METHOD__, 'private' );
		$aConfig = $aScripts = $aStyles = $aVarMsgKeys = [];
		MediaWikiServices::getInstance()->getHookContainer()->run( 'BSSocialModuleDepths', [
			// deprecated
			null,
			// deprecated
			null,
			&$aConfig,
			&$aScripts,
			&$aStyles,
			&$aVarMsgKeys,
		] );
		return $aVarMsgKeys;
	}

	/**
	 * Get target(s) for the module, eg ['desktop'] or ['desktop', 'mobile']
	 *
	 * @return array Array of strings
	 */
	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}
}
