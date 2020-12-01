<?php

namespace BlueSpice\Social\Data\Entity;

use BlueSpice\EntityFactory;
use BlueSpice\Social\Entity;
use BS\ExtendedSearch\Backend;
use BS\ExtendedSearch\Data\PrimaryDataProvider as SearchPrimaryDataProvider;
use IContextSource;
use MediaWiki\MediaWikiServices;
use User;

class PrimaryDataProvider extends SearchPrimaryDataProvider {

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @var IContextSource
	 */
	protected $context = null;

	/**
	 *
	 * @param Backend $searchBackend
	 * @param Schema $schema
	 * @param EntityFactory $factory
	 * @param IContextSource $context
	 */
	public function __construct( Backend $searchBackend, Schema $schema, EntityFactory $factory,
		IContextSource $context ) {
		parent::__construct( $searchBackend, $schema );

		$this->context = $context;
		$this->factory = $factory;
	}

	/**
	 *
	 * @param \Elastica\Result $row
	 * @return null
	 */
	protected function appendRowToData( \Elastica\Result $row ) {
		$record = new Record( $row );
		$entity = $this->factory->newFromObject( $record->getData() );
		if ( !$entity instanceof Entity ) {
			return;
		}
		$user = $this->context->getUser();
		if ( !$user ) {
			return;
		}

		if ( !$this->isSystemUser( $user ) ) {
			if ( !$entity->userCan( 'read', $user )->isOK() ) {
				return;
			}
		}
		$this->data[] = $record;
	}

	/**
	 *
	 * @param User $user
	 * @return bool
	 */
	protected function isSystemUser( User $user ) {
		return MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->isMaintenanceUser( $user );
	}

	/**
	 *
	 * @return string
	 */
	protected function getTypeName() {
		return "entitydata";
	}

	/**
	 *
	 * @return string
	 */
	protected function getIndexType() {
		return "socialentity";
	}

}
