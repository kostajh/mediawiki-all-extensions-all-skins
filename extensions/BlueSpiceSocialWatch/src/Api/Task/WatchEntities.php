<?php
/**
 * Provides the base api for BlueSpiceSocialWatch.
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
 * @package    BluespiceSocial
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */
namespace BlueSpice\Social\Watch\Api\Task;

use BlueSpice\Api\Response\Standard;
use BlueSpice\Social\Entity;

/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpiceSocial
 */
class WatchEntities extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'editWatch',
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'editWatch' => [ 'read' ],
		];
	}

	/**
	 *
	 * @param \stdClass $vTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_editWatch( $vTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$this->checkPermissions();

		if ( empty( $vTaskData->id ) ) {
			$vTaskData->id = 0;
		}
		if ( empty( $vTaskData->watch ) ) {
			$vTaskData->watch = false;
		}

		if ( $this->getUser()->isAnon() ) {
			return $oResult;
		}
		$oEntity = $this->getServices()->getService( 'BSEntityFactory' )->newFromID(
			$vTaskData->id,
			NS_SOCIALENTITY
		);
		if ( !$oEntity instanceof Entity || !$oEntity->exists() ) {
			return $oResult;
		}
		if ( !$oEntity->getConfig()->get( 'IsWatchable' ) ) {
			return $oResult;
		}
		if ( !$oEntity->userCan() ) {
			return $oResult;
		}

		try {
			if ( !$vTaskData->watch ) {
				$this->getServices()->getWatchedItemStore()->removeWatch(
					$this->getUser(),
					$oEntity->getTitle()
				);
			} else {
				$this->getServices()->getWatchedItemStore()->addWatch(
					$this->getUser(),
					$oEntity->getTitle()
				);
			}
		} catch ( \Exception $e ) {
			$oResult->message = $e->getMessage();
			return $oResult;
		}

		$oEntity->invalidateCache();
		$oResult->success = true;
		$oResult->payload['entity'] = \FormatJson::encode( $oEntity );
		$oResult->payload['entityconfig'][$oEntity->get( Entity::ATTR_TYPE )]
			= \FormatJson::encode( $oEntity->getConfig() );

		$renderer = $oEntity->getRenderer( $this->getContext() );
		if ( empty( $vTaskData->outputtype ) ) {
			$oResult->payload['view'] = $renderer->render();
		} else {
			$oResult->payload['view'] = $renderer->render(
				$vTaskData->outputtype
			);
		}
		return $oResult;
	}
}
