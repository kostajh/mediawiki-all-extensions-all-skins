<?php

namespace BlueSpice\ExtendedStatistics\Data\Entity\Collection;

use BlueSpice\Data\FieldType;
use BlueSpice\Data\Record;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\Entity\Collection;
use BS\ExtendedSearch\Backend;
use BS\ExtendedSearch\Data\PrimaryDataProvider as SearchDataProvider;
use IContextSource;
use MediaWiki\MediaWikiServices;
use User;

class PrimaryDataProvider extends SearchDataProvider {

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
		$data = $row->{$this->getTypeName()};
		foreach ( $this->schema as $fieldname => $fieldDefinition ) {
			if ( !isset( $data[$fieldname] ) ) {
				continue;
			}
			if ( !$fieldDefinition[Schema::TYPE] === FieldType::DATE ) {
				continue;
			}
			$data[$fieldname] = $this->normalizeTS( $data[$fieldname] );
		}
		unset( $data[Collection::ATTR_ID] );

		$record = new Record( (object)$data );
		$entity = $this->factory->newFromObject( (object)$data );
		if ( !$entity instanceof Collection ) {
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
	 * @param string $ts
	 * @return string
	 */
	protected function normalizeTS( $ts ) {
		return preg_replace(
			"#([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})Z#",
			"$1$2$3$4$5$6",
			$ts
		);
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
		return "collectiondata";
	}

	/**
	 *
	 * @return string
	 */
	protected function getIndexType() {
		return "extended_statistics";
	}

}
