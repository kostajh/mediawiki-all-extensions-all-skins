<?php

namespace BlueSpice\ExtendedStatistics\ExtendedSearch\Job;

use BlueSpice\ExtendedStatistics\Entity\Collection;
use BlueSpice\ExtendedStatistics\Entity\Snapshot as Entity;
use BS\ExtendedSearch\Source\Job\UpdateTitleBase;
use MediaWiki\MediaWikiServices;
use SpecialPage;

class Snapshot extends UpdateTitleBase {

	protected $sSourceKey = 'extended_statistics';

	protected function doRun() {
		$oDP = $this->getSource()->getDocumentProvider();
		$factory = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );
		$entity = $factory->newFromObject( (object)$this->params['entity'] );
		if ( !$entity ) {
			// possible data corruption caused by unknown error
			// return here to keep the job queue running!
			return;
		}
		foreach ( $entity->get( Entity::ATTR_COLLECTION ) as $key => $collection ) {
			if ( !$collection instanceof Collection || !$collection->exists() ) {
				continue;
			}
			$id = $entity->get( Collection::ATTR_ID, 0 );
			$type = $collection->get( Collection::ATTR_TYPE, '' );
			$title = SpecialPage::getTitleFor(
				'ExtendedStatisticsSnapshots',
				"$id-$type-$key"
			);
			$aDC = $oDP->getDataConfig( $title->getCanonicalURL(), $collection, $title );
			$this->getSource()->addDocumentsToIndex( [ $aDC ] );
		}

		return $aDC;
	}

	/**
	 *
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( 'updateCollectionIndex', $title, $params );
	}

	/**
	 *
	 * @return Collection
	 */
	protected function getDocumentProviderSource() {
		return MediaWikiServices::getInstance()->getService( 'BSEntityFactory' )->newFromObject(
			(object)$this->params['entity']
		);
	}
}
