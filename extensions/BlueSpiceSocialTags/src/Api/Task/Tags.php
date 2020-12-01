<?php
/**
 * Provides the base api for BlueSpiceSocialTags.
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
namespace BlueSpice\Social\Tags\Api\Task;

use BlueSpice\Api\Response\Standard;
use BlueSpice\Social\Entity;
use FormatJson;
use MediaWiki\MediaWikiServices;
use Title;

/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpiceSocial
 */
class Tags extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'editTags',
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'editTags' => [ 'edit' ],
		];
	}

	/**
	 *
	 * @param \stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	public function task_editTags( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		if ( empty( $taskData->id ) ) {
			$taskData->id = 0;
		}
		if ( empty( $taskData->tags ) ) {
			$taskData->tags = [];
		}
		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromID(
			$taskData->{Entity::ATTR_ID},
			$taskData->{Entity::ATTR_TYPE}
		);
		if ( !$entity instanceof Entity || !$entity->exists() ) {
			return $result;
		}

		$status = $entity->userCan(
			'tag',
			$this->getUser()
		);
		if ( !$status->isOK() ) {
			$result->message = $status->getHTML();
			return $result;
		}

		$data = $entity->getFullData();
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		foreach ( $taskData->tags as $key => $tag ) {
			if ( in_array( $tag, $data['tags'] ) ) {
				continue;
			}
			$title = Title::newFromText( $tag );
			if ( !$title ) {
				unset( $taskData->tags[$key] );
				continue;
			}
			if ( !$pm->userCan( 'read', $this->getUser(), $title ) ) {
				unset( $taskData->tags[$key] );
			}
		}

		foreach ( $data['tags'] as $key => $tag ) {
			if ( in_array( $tag, $taskData->tags ) ) {
				continue;
			}
			$title = Title::newFromText( $tag );
			if ( !$title ) {
				continue;
			}
			if ( $pm->userCan( 'read', $this->getUser(), $title ) ) {
				continue;
			}
			// user can not change tags, he is not allowed to read
			$taskData->tags[] = $tag;
		}

		if ( empty( $taskData->tags ) ) {
			// force emptytags :(
			$entity->tags = [];
		}
		$entity->set( "tags", $taskData->tags );
		$entity->setUnsavedChanges();
		$status = $entity->save( $this->getUser() );
		if ( $status->isGood() ) {
			$result->success = true;
		} else {
			$result->message = $status->getHTML();
		}
		$renderer = $entity->getRenderer( $this->getContext() );
		$result->payload['entity'] = FormatJson::encode( $entity );
		$result->payload['entityconfig'][$entity->get( Entity::ATTR_TYPE )]
			= FormatJson::encode( $entity->getConfig() );
		if ( empty( $taskData->outputtype ) ) {
			$result->payload['view'] = $renderer->render();
		} else {
			$result->payload['view'] = $renderer->render(
				$taskData->outputtype
			);
		}
		return $result;
	}

}
