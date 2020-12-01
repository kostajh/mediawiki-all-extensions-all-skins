<?php

namespace BlueSpice\ExtendedStatistics;

use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\ExtensionAttributeBasedRegistry;
use Config;
use MediaWiki\MediaWikiServices;

class DataCollectorFactory {
	/**
	 *
	 * @var DataCollector[]
	 */
	protected $collectors = [];
	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var ExtensionAttributeBasedRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @param ExtensionAttributeBasedRegistry $registry
	 * @param Config $config
	 */
	public function __construct( ExtensionAttributeBasedRegistry $registry,
		Config $config ) {
		$this->config = $config;
		$this->registry = $registry;
	}

	/**
	 *
	 * @param Snapshot $snapshot
	 * @return DataCollector[]
	 */
	public function getCollectors( Snapshot $snapshot ) {
		$collectors = [];
		foreach ( $this->registry->getAllKeys() as $name ) {
			$collector = $this->getCollector( $name, $snapshot );
			if ( !$collector ) {
				continue;
			}
			$collectors[$name] = $collector;
		}
		return $collectors;
	}

	/**
	 *
	 * @param string $name
	 * @param Snapshot $snapshot
	 * @return DataCollector|false
	 */
	public function getCollector( $name, Snapshot $snapshot ) {
		if ( isset( $this->collectors[$name] ) ) {
			return $this->collectors[$name];
		}
		$callback = $this->registry->getValue( $name, false );
		if ( !$callback ) {
			return false;
		}
		$collector = call_user_func_array( $callback, [
			$name,
			MediaWikiServices::getInstance(),
			$snapshot,
			$this->config
		] );
		$this->collectors[$name] = $collector;
		return $this->collectors[$name];
	}
}
