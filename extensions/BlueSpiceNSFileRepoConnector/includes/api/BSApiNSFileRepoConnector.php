<?php
/**
 * Provides the api for NSFileRepoConnector.
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
 * @package    BluespiceWebDAV
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

use BlueSpice\Api\Response\Standard;

/**
 * Api class for NSFileRepoConnector
 * @package BluespiceWebDAV
 */
class BSApiNSFileRepoConnector extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'getPossibleNamespaces'
	];

	/**
	 *
	 * @var array
	 */
	protected $aReadTasks = [ 'getPossibleNamespaces' ];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'getPossibleNamespaces' => [ 'read' ]
		];
	}

	/**
	 * Returns the bsic description for this module
	 * @return type
	 */
	public function getDescription() {
		return [
			'BSApiNSFileRepoConnector: API for various ajax calls related to the NSFileRepoConnector'
		];
	}

	/**
	 *
	 * @param \stdClass $sTaskData
	 * @param array $aParams
	 * @return Standard
	 */
	public function task_getPossibleNamespaces( $sTaskData, $aParams ) {
		$oReturn = static::makeStandardReturn();
		$oReturn->success = true;
		$oReturn->payload =
			NSFileRepoConnectorNamespaceHelper::getPossibleNamespaces();
		$oReturn->payload_count = count( $oReturn->payload );
		return $oReturn;
	}

	/**
	 *
	 * @return bool
	 */
	public function isWriteMode() {
		return false;
	}

}
