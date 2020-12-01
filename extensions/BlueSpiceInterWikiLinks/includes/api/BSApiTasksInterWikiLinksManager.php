<?php
/**
 * Provides the Interwiki links manager tasks api for BlueSpice.
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
 * @author     Patric Wirth
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

use MediaWiki\MediaWikiServices;

/**
 * InterWikiLinksManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksInterWikiLinksManager extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * must have this name: /BlueSpiceFoundation/includes/api/BSApiTasksBase.php
	 * @var array
	 */
	protected $aTasks = [
		'editInterWikiLink' => [
			'examples' => [
				[
					'prefix' => 'mywiki',
					'url' => 'http://some.wiki.com/$1'
				],
				[
					'oldPrefix' => 'old_name',
					'prefix' => 'new_name',
					'url' => 'http://some.wiki.com/$1'
				]
			],
			'params' => [
				'oldPrefix' => [
					'desc' => 'Old prefix',
					'type' => 'string',
					'required' => false,
					'default' => ''
				],
				'url' => [
					'desc' => 'Url of the wiki',
					'type' => 'string',
					'required' => true
				],
				'prefix' => [
					'desc' => 'Prefix to set',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'removeInterWikiLink' => [
			'examples' => [
				[
					'prefix' => 'mywiki'
				]
			],
			'params' => [
				'prefix' => [
					'desc' => 'Prefix to remove',
					'type' => 'string',
					'required' => true
				]
			]
		]
	];

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'editInterWikiLink' => [ 'wikiadmin' ],
			'removeInterWikiLink' => [ 'wikiadmin' ]
		];
	}

	/**
	 * Creates or edits an interwiki link.
	 * @param stdClass $taskData
	 * @return stdClass Standard tasks API return
	 */
	protected function task_editInterWikiLink( $taskData ) {
		$return = $this->makeStandardReturn();
		$oPrefix = null;

		$oldPrefix = isset( $taskData->oldPrefix )
			? (string)$taskData->oldPrefix
			: '';
		$url = isset( $taskData->url )
			? (string)$taskData->url
			: '';
		$prefix = isset( $taskData->prefix )
			? (string)$taskData->prefix
			: '';

		if ( !empty( $oldPrefix ) && !$this->interWikiLinkExists( $oldPrefix ) ) {
			$return->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nooldpfx' )->plain()
			];
		} elseif ( !empty( $prefix )
			&& $this->interWikiLinkExists( $prefix )
			&& $prefix !== $oldPrefix
			) {
			$return->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-pfxexists' )->plain()
			];
		}
		if ( !empty( $return->errors ) ) {
			return $return;
		}

		if ( !$oPrefix && empty( $url ) ) {
			$return->errors[] = [
				'id' => 'iwediturl',
				'message' => wfMessage( 'bs-interwikilinks-nourl' )->plain()
			];
		}
		if ( !$oPrefix && empty( $prefix ) ) {
			$return->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nopfx' )->plain()
			];
		}
		if ( !empty( $url ) ) {
			$validationResult = BsValidator::isValid(
				'Url',
				$url,
				[ 'fullResponse' => true ]
				);
			if ( $validationResult->getErrorCode() ) {
				$return->errors[] = [
					'id' => 'iwediturl',
					'message' => $validationResult->getI18N()
				];
			}
			if ( strpos( $url, ' ' ) ) {
				$return->errors[] = [
					'id' => 'iwediturl',
					'message' => wfMessage(
						'bs-interwikilinks-invalid-url-spc'
					)->plain()
				];
			}
		}
		if ( !empty( $prefix ) ) {
			if ( strlen( $prefix ) > 32 ) {
				$return->errors[] = [
					'id' => 'iweditprefix',
					'message' => wfMessage(
						'bs-interwikilinks-pfxtoolong'
					)->plain()
				];
			}

			foreach ( [ ' ', '"', '&', ':' ] as $sInvalidChar ) {
				if ( substr_count( $prefix, $sInvalidChar ) === 0 ) {
					continue;
				}
				// TODO (PW 19.02.2016): Return the invalid char(s)
				$return->errors[] = [
					'id' => 'iweditprefix',
					'message' => wfMessage(
						'bs-interwikilinks-invalid-pfx-spc'
					)->plain()
				];
				break;
			}
		}

		if ( !empty( $return->errors ) ) {
			return $return;
		}

		$oDB = $this->getDB( DB_MASTER );
		$table = 'interwiki';
		$conditions = [ 'iw_local' => '0' ];
		$values = [
			'iw_prefix' => $prefix,
			'iw_url' => $url,
			'iw_api' => '',
			'iw_wikiid' => ''
		];

		if ( empty( $oldPrefix ) ) {
			$oDB->insert(
				$table,
				array_merge( $conditions, $values ),
				__METHOD__
			);
			$return->success = true;
			$return->message = wfMessage(
				'bs-interwikilinks-link-created'
			)->plain();

			\BlueSpice\InterWikiLinks\Extension::purgeTitles( $prefix );
			MediaWikiServices::getInstance()->getInterwikiLookup()->invalidateCache( $prefix );
			return $return;
		}

		$conditions['iw_prefix'] = $oldPrefix;
		$oDB->update(
			$table,
			$values,
			$conditions,
			__METHOD__
		);
		$return->success = true;
		$return->message = wfMessage(
			'bs-interwikilinks-link-edited'
		)->plain();

		\BlueSpice\InterWikiLinks\Extension::purgeTitles( $oldPrefix );
		MediaWikiServices::getInstance()->getInterwikiLookup()->invalidateCache( $oldPrefix );

		return $return;
	}

	/**
	 * Creates or edits an interwiki link.
	 * @param stdClass $taskData
	 * @return stdClass Standard tasks API return
	 */
	protected function task_removeInterWikiLink( $taskData ) {
		$return = $this->makeStandardReturn();

		$prefix = isset( $taskData->prefix )
			? addslashes( $taskData->prefix )
			: '';

		if ( empty( $prefix ) ) {
			$return->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nopfx' )->plain()
			];
			return $return;
		}

		if ( !$this->interWikiLinkExists( $prefix ) ) {
			$return->errors[] = [
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nooldpfx' )->plain()
			];
			return $return;
		}

		$return->success = (bool)$this->getDB( DB_MASTER )->delete(
			'interwiki',
			[ 'iw_prefix' => $prefix ],
			__METHOD__
		);

		if ( $return->success ) {
			$return->message = wfMessage(
				'bs-interwikilinks-link-deleted'
			)->plain();
		}

		// Make sure to invalidate as much as possible!
		\BlueSpice\InterWikiLinks\Extension::purgeTitles( $prefix );
		MediaWikiServices::getInstance()->getInterwikiLookup()->invalidateCache( $prefix );
		return $return;
	}

	/**
	 *
	 * @param string $prefix
	 * @return bool
	 */
	protected function interWikiLinkExists( $prefix ) {
		return MediaWikiServices::getInstance()->getInterwikiLookup()->isValidInterwiki( $prefix );
	}
}
