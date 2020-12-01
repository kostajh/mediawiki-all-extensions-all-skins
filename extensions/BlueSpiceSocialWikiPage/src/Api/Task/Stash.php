<?php

namespace BlueSpice\Social\WikiPage\Api\Task;

use BlueSpice\Api\Response\Standard;
use BlueSpice\Social\WikiPage\Entity\Stash as Entity;
use MediaWiki\MediaWikiServices;

/**
 * Api base class for simple tasks in BlueSpice
 * @package BlueSpiceSocial
 */
class Stash extends \BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = [
		'addFiles',
		'removeFiles',
	];

	/**
	 *
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return [
			'addFiles' => [ 'read', 'edit' ],
			'removeFiles' => [ 'read', 'edit' ],
		];
	}

	/**
	 *
	 * @param \stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	public function task_addFiles( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		if ( !$taskData->files ) {
			return $result;
		}

		$entity = $this->getEntityFactory()->newFromObject(
			$taskData
		);
		if ( !$entity instanceof Entity ) {
			return $result;
		}

		$action = $entity->exists()
			? 'edit'
			: 'create';
		if ( $action == 'edit' && !$entity->userIsOwner( $this->getUser() ) ) {
			$action = 'editothers';
		}

		$status = $entity->userCan( $action, $this->getUser() );
		if ( !$status->isOK() ) {
			$result->message = $status->getWikiText();
			return $result;
		}
		$entity->setValuesByObject( $taskData );

		$helper = $this->getWikitextLinksHelper(
			$entity->get( Entity::ATTR_TEXT, '' )
		);
		$files = [];
		foreach ( $taskData->files as $fileName ) {
			$files[] = \Title::makeTitle( NS_FILE, $fileName );
		}

		$wikitext = $helper->getFileLinksHelper()->addTargets(
			$files,
			false
		)->getWikitext();

		$entity->set(
			Entity::ATTR_TEXT,
			$wikitext
		);

		$result->payload['entity'] = \FormatJson::encode( $entity );
		$result->payload['actions'] = $entity->getActions(
			[],
			$this->getUser()
		);
		$result->payload['entityconfig'][$entity->get( Entity::ATTR_TYPE )]
			= \FormatJson::encode( $entity->getConfig() );

		$renderer = $entity->getRenderer( $this->getContext() );
		$outputType = empty( $taskData->outputtype )
			? 'Default'
			: $taskData->outputtype;

		$result->payload['view'] = $renderer->startEditor()->render(
			$outputType,
			// nocache and hope for the best!
			true
		);
		$result->success = true;

		return $result;
	}

	/**
	 *
	 * @param \stdClass $taskData
	 * @param array $params
	 * @return Standard
	 */
	public function task_removeFiles( $taskData, $params ) {
		$result = $this->makeStandardReturn();
		$this->checkPermissions();

		if ( !$taskData->files ) {
			return $result;
		}
		$entity = $this->getEntityFactory()->newFromObject(
			$taskData
		);
		if ( !$entity instanceof Entity ) {
			return $result;
		}

		$action = $entity->exists()
			? 'edit'
			: 'create';
		if ( $action == 'edit' && !$entity->userIsOwner( $this->getUser() ) ) {
			$action = 'editothers';
		}

		$status = $entity->userCan( $action, $this->getUser() );
		if ( !$status->isOK() ) {
			$result->message = $status->getWikiText();
			return $result;
		}
		$entity->setValuesByObject( $taskData );

		$helper = $this->getWikitextLinksHelper(
			$entity->get( Entity::ATTR_TEXT, '' )
		);
		$files = [];
		foreach ( $taskData->files as $fileName ) {
			$files[] = \Title::newFromText( $fileName, NS_FILE );
		}
		$wikitext = $helper->getFileLinksHelper()->removeTargets(
			$files,
			true
		)->getWikitext();

		$entity->set(
			Entity::ATTR_TEXT,
			$wikitext
		);

		$result->payload['entity'] = \FormatJson::encode( $entity );
		$result->payload['actions'] = $entity->getActions(
			[],
			$this->getUser()
		);
		$result->payload['entityconfig'][$entity->get( Entity::ATTR_TYPE )]
			= \FormatJson::encode( $entity->getConfig() );

		$renderer = $entity->getRenderer( $this->getContext() );
		$outputType = empty( $taskData->outputtype )
			? 'Default'
			: $taskData->outputtype;

		$result->payload['view'] = $renderer->startEditor()->render(
			$outputType,
			// nocache and hope for the best!
			true
		);
		$result->success = true;
		return $result;
	}

	/**
	 *
	 * @return \BlueSpice\EntityFactory
	 */
	protected function getEntityFactory() {
		return $this->getServices()->getService( 'BSEntityFactory' );
	}

	/**
	 *
	 * @param string $wikitext
	 * @return \BlueSpice\Utility\WikiTextLinksHelper
	 */
	protected function getWikitextLinksHelper( $wikitext ) {
		return $this->getServices()->getService( 'BSUtilityFactory' )
			->getWikiTextLinksHelper( $wikitext );
	}

	/**
	 *
	 * @return MediaWikiServices
	 */
	protected function getServices() {
		return MediaWikiServices::getInstance();
	}
}
