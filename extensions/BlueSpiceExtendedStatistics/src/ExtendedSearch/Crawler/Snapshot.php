<?php

namespace BlueSpice\ExtendedStatistics\ExtendedSearch\Crawler;

use BlueSpice\Data\ReaderParams;
use BlueSpice\ExtendedStatistics\Data\Snapshot\Record;
use BlueSpice\ExtendedStatistics\Data\Snapshot\Store;
use BlueSpice\ExtendedStatistics\Entity\Snapshot as Entity;
use MediaWiki\MediaWikiServices;
use SpecialPage;

class Snapshot extends \BS\ExtendedSearch\Source\Crawler\Base {
	protected $sJobClass = "\\BlueSpice\\ExtendedStatistics\\ExtendedSearch\\Job\\Snapshot";

	protected $entities = [];

	public function crawl() {
		$store = new Store();
		$result = $store->getReader()->read( new ReaderParams( [
			ReaderParams::PARAM_LIMIT => ReaderParams::LIMIT_INFINITE,
		] ) );
		$factory = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );
		foreach ( $result->getRecords() as $record ) {
			$entity = $factory->newFromObject( (object)[
				Entity::ATTR_TYPE => Entity::TYPE,
				Entity::ATTR_ID => $record->get( Record::ID )
			] );

			if ( !$entity instanceof Entity || !$entity->exists() ) {
				continue;
			}
			$title = SpecialPage::getTitleFor(
				'ExtendedStatisticsSnapshots',
				$entity->get( Entity::ATTR_ID, 0 )
			);

			$this->addToJobQueue( $title, [ 'entity' => [
				Entity::ATTR_TYPE => $entity->get( Entity::ATTR_TYPE ),
				Entity::ATTR_ID => $entity->get( Entity::ATTR_ID ),
			] ] );
		}
	}

}
