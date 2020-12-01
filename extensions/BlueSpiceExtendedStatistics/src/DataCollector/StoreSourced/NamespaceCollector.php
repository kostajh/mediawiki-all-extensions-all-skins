<?php

namespace BlueSpice\ExtendedStatistics\DataCollector\StoreSourced;

use BlueSpice\Data\IStore;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use Config;
use MediaWiki\MediaWikiServices;
use MWNamespace;

abstract class NamespaceCollector extends SnapshotDiffCollector {

	/**
	 *
	 * @var array
	 */
	protected $namespaces = null;

	/**
	 * Helper method
	 *
	 * @param Snapshot $snapshot
	 * @param MediaWikiServices $services
	 * @return array
	 */
	public static function getNamespaces( Snapshot $snapshot, MediaWikiServices $services ) {
		$version = $snapshot->getConfig()->get( 'Version' );
		if ( version_compare( $version, '1.34', '>=' ) ) {
			$namespaces = $services->getNamespaceInfo()->getCanonicalNamespaces();
		} else {
			$namespaces = MWNamespace::getCanonicalNamespaces();
		}
		foreach ( $namespaces as $idx => $canonical ) {
			if ( $idx >= 0 ) {
				continue;
			}
			unset( $namespaces[$idx] );
		}
		return $namespaces;
	}

	/**
	 *
	 * @param string $type
	 * @param Snapshot $snapshot
	 * @param Config $config
	 * @param EntityFactory $factory
	 * @param IStore $store
	 * @param SnapshotFactory $snapshotFactory
	 * @param array $namespaces
	 */
	protected function __construct( $type, Snapshot $snapshot, Config $config,
		EntityFactory $factory, IStore $store, SnapshotFactory $snapshotFactory,
		array $namespaces ) {
		parent::__construct( $type, $snapshot, $config, $factory, $store, $snapshotFactory );
		$this->namespaces = $namespaces;
	}
}
