<?php

namespace BlueSpice\ExtendedStatistics\ExtendedSearch;

use BlueSpice\ExtendedStatistics\ExtendedSearch\Crawler\Snapshot as Crawler;
use BlueSpice\ExtendedStatistics\ExtendedSearch\DocumentProvider\Collection as DocumentProvider;
use BlueSpice\ExtendedStatistics\ExtendedSearch\MappingProvider\Collection as MappingProvider;
use BlueSpice\ExtendedStatistics\ExtendedSearch\Updater\Snapshot as Updater;

class Snapshots extends \BS\ExtendedSearch\Source\DecoratorBase {

	/**
	 * @param \BS\ExtendedSearch\Source\Base $base
	 * @return Entities
	 */
	public static function create( $base ) {
		return new self( $base );
	}

	/**
	 *
	 * @return Crawler
	 */
	public function getCrawler() {
		return new Crawler( $this->getConfig() );
	}

	/**
	 *
	 * @return DocumentProvider
	 */
	public function getDocumentProvider() {
		return new DocumentProvider(
			$this->oDecoratedSource->getDocumentProvider()
		);
	}

	/**
	 *
	 * @return MappingProvider
	 */
	public function getMappingProvider() {
		return new MappingProvider(
			$this->oDecoratedSource->getMappingProvider()
		);
	}

	/**
	 *
	 * @return Updater
	 */
	public function getUpdater() {
		return new Updater( $this->oDecoratedSource );
	}

	/**
	 * @return bool
	 */
	public function isSortable() {
		return false;
	}
}
