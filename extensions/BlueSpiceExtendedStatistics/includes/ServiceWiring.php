<?php

use BlueSpice\ExtendedStatistics\DataCollectorFactory;
use BlueSpice\ExtendedStatistics\DiagramFactory;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\MediaWikiServices;

return [

	'BSExtendedStatisticsDataCollectorFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceExtendedStatisticsSnapshotDataCollectorRegistry'
		);
		return new DataCollectorFactory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSExtendedStatisticsSnapshotFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		return new SnapshotFactory(
			$registry,
			$services->getService( 'BSEntityConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSExtendedStatisticsDiagramFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceExtendedStatisticsDiagramRegistry'
		);
		return new DiagramFactory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];
