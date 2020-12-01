<?php
/**
 * Provides the smartlist api for BlueSpice.
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
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

class BSApiTasksSmartList extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'getMostVisitedPages' => [
			'examples' => [
				[
					'count' => 100,
					'period' => 'week'
				]
			],
			'params' => [
				'count' => [
					'desc' => '',
					'type' => 'integer',
					'required' => false,
					'default' => 10
				],
				'period' => [
					'desc' => 'Period for which to retrieve pages (week, month)',
					'type' => 'string',
					'required' => false,
					'default' => 'alltime'
				]
			]
		],
		'getMostEditedPages' => [
			'examples' => [
				[
					'count' => 100,
					'period' => 'week'
				]
			],
			'params' => [
				'count' => [
					'desc' => '',
					'type' => 'integer',
					'required' => false,
					'default' => 10
				],
				'period' => [
					'desc' => 'Period for which to retrieve pages (week, month)',
					'type' => 'string',
					'required' => false,
					'default' => 'alltime'
				]
			]
		],
		'getMostActivePortlet' => [
			'examples' => [
				[
					'count' => 100,
					'period' => 30
				]
			],
			'params' => [
				'count' => [
					'desc' => '',
					'type' => 'integer',
					'required' => false,
					'default' => 10
				],
				'portletperiod' => [
					'desc' => 'Period for which to retrieve pages in days (only 7 and 30)',
					'type' => 'integer',
					'required' => false,
					'default' => 0
				]
			]
		],
		'getYourEditsPortlet' => [
			'examples' => [
				[
					'count' => 100
				]
			],
			'params' => [
				'count' => [
					'desc' => '',
					'type' => 'integer',
					'required' => false,
					'default' => 10
				]
			]
		]
	];

	/**
	 * Methods that can be executed even when the wiki is in read-mode, as
	 * they do not alter the state/content of the wiki
	 * @var array
	 */
	protected $aReadTasks = [
		'getMostVisitedPages',
	];

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'getMostVisitedPages' => [ 'read' ],
			'getMostEditedPages' => [ 'read' ],
			'getMostActivePortlet' => [ 'read' ],
			'getYourEditsPortlet' => [ 'read' ],
		];
	}

	/**
	 * Delivers a list of most visited pages
	 * in the shoutbox.
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_getMostVisitedPages( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$iCount = isset( $oTaskData->count )
			? (int)$oTaskData->count
			: 10;

		$sTime = isset( $oTaskData->period )
			? $oTaskData->period
			: 'alltime';

		try {
			$sContent =
				$this->getServices()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getToplist(
					'',
					[
						'count' => $iCount,
						'portletperiod' => $sTime
					],
					null
				);
			$oReturn->success = true;
		} catch ( Exception $e ) {
			$oErrorListView = new ViewTagErrorList();
			$oErrorListView->addItem( new ViewTagError( $e->getMessage() ) );
			$sContent = $oErrorListView->execute();
			$oReturn->success = false;
		}

		$oReturn->payload['html'] = $sContent;

		return $oReturn;
	}

	/**
	 * Delivers a list of most edited pages
	 * in the shoutbox.
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_getMostEditedPages( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$iCount = isset( $oTaskData->count )
			? (int)$oTaskData->count
			: 10;

		$sTime = isset( $oTaskData->period )
			? $oTaskData->period
			: 'alltime';

		$oReturn->payload['html'] = $this->getServices()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getEditedPages( $iCount, $sTime );

		$oReturn->success = true;
		return $oReturn;
	}

	/**
	 * Delivers a list of most active pages
	 * in the shoutbox.
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_getMostActivePortlet( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$iCount = isset( $oTaskData->count )
			? (int)$oTaskData->count
			: 10;

		$sTime = isset( $oTaskData->portletperiod )
			? (int)$oTaskData->portletperiod
			: 0;

		$oReturn->payload['html'] = $this->getServices()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getActivePortlet( $iCount, $sTime );

		$oReturn->success = true;
		return $oReturn;
	}

	/**
	 * Delivers a list of most edit portlets
	 * in the shoutbox.
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_getYourEditsPortlet( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$iCount = isset( $oTaskData->count )
			? (int)$oTaskData->count
			: 10;

		$oReturn->payload['html'] = $this->getServices()
				->getService( 'BSExtensionFactory' )
				->getExtension( 'BlueSpiceSmartList' )
				->getYourEdits( $iCount );

		$oReturn->success = true;
		return $oReturn;
	}

	public function needsToken() {
		return parent::needsToken();
	}
}
