<?php

namespace BlueSpice\Social\Privacy\Job;

use MediaWiki\MediaWikiServices;

class DeleteEntity extends \Job {

	/**
	 *
	 * @param \Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params ) {
		parent::__construct( "privacyDeleteEntity", $title, $params );
	}

	/**
	 *
	 */
	public function run() {
		$entity = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )
			->newFromSourceTitle( $this->title );
		$entity->set( \BlueSpice\Entity::ATTR_OWNER_ID, $this->params['deletedUserId'] );

		$serviceUser = MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
		$entity->save( $serviceUser );
	}
}
