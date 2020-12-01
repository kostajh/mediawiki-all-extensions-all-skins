<?php
/**
 * Provides the group manager tasks api for BlueSpice.
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

/**
 * GroupManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksGroupManager extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * must have this name: /BlueSpiceFoundation/includes/api/BSApiTasksBase.php
	 * @var array
	 */
	protected $aTasks = [
		'addGroup' => [
			'examples' => [
				[
					'group' => 'Some name'
				]
			],
			'params' => [
				'group' => [
					'desc' => 'Group name',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'editGroup' => [
			'examples' => [
				[
					'group' => 'Some name',
					'newGroup' => 'New name'
				]
			],
			'params' => [
				'group' => [
					'desc' => 'Old group name',
					'type' => 'string',
					'required' => true
				],
				'newGroup' => [
					'desc' => 'New group name',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'removeGroup' => [
			'examples' => [
				[
					'group' => 'Some name'
				]
			],
			'params' => [
				'group' => [
					'desc' => 'Group name',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'removeGroups' => [
			'examples' => [
				[
					'group' => [ 'Group 1', 'Group 2', 'Group 3' ]
				]
			],
			'params' => [
				'groups' => [
					'desc' => 'Array containing group names',
					'type' => 'array',
					'required' => true
				]
			]
		],
	];

	/**
	 *
	 * @param stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	protected function task_addGroup( $taskData, $params ) {
		// TODO SU (04.07.11 11:40): global are used here because they have to be changed
		global $wgGroupPermissions, $wgAdditionalGroups;

		$return = $this->makeStandardReturn();

		$group = isset( $taskData->group )
			? (string)$taskData->group
			: '';
		if ( empty( $group ) ) {
			$return->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $return;
		}
		if ( array_key_exists( $group, $wgAdditionalGroups ) ) {
			$return->message = wfMessage(
				'bs-groupmanager-grpexists'
			)->plain();
			return $return;
		}

		if ( !isset( $wgGroupPermissions[ $group ] ) ) {
			$wgAdditionalGroups[ $group ] = true;
			$res = \BlueSpice\GroupManager\Extension::saveData();
			if ( $res[ 'success' ] === true ) {
				// Create a log entry for the creation of the group
				$title = SpecialPage::getTitleFor( 'WikiAdmin' );
				$user = RequestContext::getMain()->getUser();
				$logger = new ManualLogEntry( 'bs-group-manager', 'create' );
				$logger->setPerformer( $user );
				$logger->setTarget( $title );
				$logger->setParameters( [
					'4::group' => $group
				] );
				$logger->insert();

				$return->success = true;
				$return->message = wfMessage( 'bs-groupmanager-grpadded' )->plain();
			} else {
				$return->success = false;
				$return->message = $res['message'];
			}
		}

		return $return;
	}

	/**
	 *
	 * @param stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	protected function task_editGroup( $taskData, $params ) {
		global $wgAdditionalGroups;

		$return = $this->makeStandardReturn();
		$group = isset( $taskData->group )
			? (string)$taskData->group
			: '';
		$newGroup = isset( $taskData->newGroup )
			? (string)$taskData->newGroup
			: '';
		if ( empty( $group ) || empty( $newGroup ) || $group == $newGroup ) {
			$return->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $return;
		}
		if ( !isset( $wgAdditionalGroups[$group] ) ) {
			// If group is not in $wgAdditionalGroups, it's a system group and mustn't be renamed.
			$return->message = wfMessage(
				'bs-groupmanager-grpedited'
			)->plain();
			return $return;
		}

		$nameErrors = \BlueSpice\GroupManager\Extension::getNameErrors( $newGroup );
		if ( !empty( $nameErrors ) ) {
			$return->success = false;
			$return->message = $nameErrors['message'];
			return $return;
		}

		// Copy the data of the old group to the group with the new name and then delete the old group
		$wgAdditionalGroups[$group] = false;
		$wgAdditionalGroups[$newGroup] = true;

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->update(
			'user_groups',
			[
				'ug_group' => $newGroup
			],
			[
				'ug_group' => $group
			]
		);

		if ( $res === false ) {
			$return->message = wfMessage(
				'bs-groupmanager-removegroup-message-unknown'
			)->plain();
			return $return;
		}

		$return->success = true;

		$result = \BlueSpice\GroupManager\Extension::saveData();

		// Backwards compatibility
		$result = array_merge(
			(array)$return,
			$result
		);

		$this->getServices()->getHookContainer()->run( "BSGroupManagerGroupNameChanged", [
			$group,
			$newGroup,
			&$result
		] );

		if ( $result['success'] === false ) {
			return (object)$result;
		}
		$result['message'] = wfMessage( 'bs-groupmanager-grpedited' )->plain();

		// Create a log entry for the change of the group
		$title = SpecialPage::getTitleFor( 'WikiAdmin' );
		$user = RequestContext::getMain()->getUser();
		$logger = new ManualLogEntry( 'bs-group-manager', 'modify' );
		$logger->setPerformer( $user );
		$logger->setTarget( $title );
		$logger->setParameters( [
				'4::group' => $group,
				'5::newGroup' => $newGroup
		] );
		$logger->insert();

		return (object)$result;
	}

	/**
	 *
	 * @param stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	protected function task_removeGroups( $taskData, $params ) {
		$return = $this->makeStandardReturn();
		$groups = isset( $taskData->groups )
			? $taskData->groups
			: [];
		if ( !is_array( $groups ) || empty( $groups ) ) {
			$return->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $return;
		}
		$fails = [];
		foreach ( $groups as $group ) {
			$return->payload[$group] = $this->task_removeGroup(
				(object)[ 'group' => $group ],
				[]
			);
			$return->payload_count++;
			if ( isset( $return->payload[$group]->success ) ) {
				continue;
			}
			$fails[] = $group;
		}

		if ( !empty( $fails ) ) {
			$return->success = false;
			$errorList = Xml::openElement( 'ul' );
			foreach ( $fails as $group ) {
				$errorList .= Xml::element( 'li', [], $group );
			}
			$errorList .= Xml::closeElement( 'ul' );
			$return->message = wfMessage(
				'bs-groupmanager-removegroup-message-failure',
				count( $fails ),
				$errorList
			)->parse();
		} else {
			$return->success = true;
			$return->message = wfMessage(
				'bs-groupmanager-grpremoved'
			)->plain();
		}
		return $return;
	}

	/**
	 *
	 * @param stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	protected function task_removeGroup( $taskData, $params ) {
		global $wgAdditionalGroups;
		$return = $this->makeStandardReturn();

		$group = isset( $taskData->group )
			? (string)$taskData->group
			: '';
		if ( empty( $group ) ) {
			$return->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $return;
		}
		if ( !isset( $wgAdditionalGroups[$group] ) ) {
			$return->message = wfMessage(
				'bs-groupmanager-msgnotremovable'
			)->plain();
			return $return;
		}

		$wgAdditionalGroups[$group] = false;
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->delete(
			'user_groups',
			[
				'ug_group' => $group
			]
		);
		if ( $res === false ) {
			$return->message = wfMessage(
				'bs-groupmanager-removegroup-message-unknown'
			)->plain();
			return $return;
		}

		$result = \BlueSpice\GroupManager\Extension::saveData();
		// Backwards compatibility
		$result = array_merge(
			(array)$return,
			$result
		);

		$this->getServices()->getHookContainer()->run( "BSGroupManagerGroupDeleted", [
			$group,
			&$result
		] );
		if ( $result['success'] === false ) {
			return (object)$result;
		}
		$result['message'] = wfMessage( 'bs-groupmanager-grpremoved' )->plain();

		// Create a log entry for the removal of the group
		$title = SpecialPage::getTitleFor( 'WikiAdmin' );
		$user = RequestContext::getMain()->getUser();
		$logger = new ManualLogEntry( 'bs-group-manager', 'remove' );
		$logger->setPerformer( $user );
		$logger->setTarget( $title );
		$logger->setParameters( [
				'4::group' => $group
		] );
		$logger->insert();

		return (object)$result;
	}

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'addGroup' => [ 'wikiadmin' ],
			'editGroup' => [ 'wikiadmin' ],
			'removeGroup' => [ 'wikiadmin' ],
			'removeGroups' => [ 'wikiadmin' ],
		];
	}

}
