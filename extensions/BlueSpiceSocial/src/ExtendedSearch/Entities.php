<?php

namespace BlueSpice\Social\ExtendedSearch;

use BlueSpice\EntityFactory;
use Entity;
use MediaWiki\MediaWikiServices;

class Entities extends \BS\ExtendedSearch\Source\DecoratorBase {

	/**
	 * @param \BS\ExtendedSearch\Source\Base $base
	 * @return Entities
	 */
	public static function create( $base ) {
		return new self( $base );
	}

	/**
	 *
	 * @return Entity
	 */
	public function getCrawler() {
		return new \BlueSpice\Social\ExtendedSearch\Crawler\Entity( $this->getConfig() );
	}

	/**
	 *
	 * @return Entity
	 */
	public function getDocumentProvider() {
		return new \BlueSpice\Social\ExtendedSearch\DocumentProvider\Entity(
			$this->oDecoratedSource->getDocumentProvider()
		);
	}

	/**
	 *
	 * @return Entity
	 */
	public function getMappingProvider() {
		return new \BlueSpice\Social\ExtendedSearch\MappingProvider\Entity(
			$this->oDecoratedSource->getMappingProvider()
		);
	}

	/**
	 *
	 * @return Entity
	 */
	public function getUpdater() {
		return new \BlueSpice\Social\ExtendedSearch\Updater\Entity( $this->oDecoratedSource );
	}

	/**
	 *
	 * @return Formatter\EntityFormatter
	 */
	public function getFormatter() {
		/** @var EntityFactory $entityFactory */
		$entityFactory = MediaWikiServices::getInstance()->getService( 'BSEntityFactory' );
		return new Formatter\EntityFormatter( $this, $entityFactory );
	}

	/**
	 * @return bool
	 */
	public function isSortable() {
		return false;
	}
}
