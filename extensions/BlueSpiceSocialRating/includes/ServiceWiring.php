<?php

use MediaWiki\MediaWikiServices;

return [

	'BSRatingFactoryEntity' => function ( MediaWikiServices $services ) {
		return new \BlueSpice\Social\Rating\RatingFactory\Entity(
			$services->getService( 'BSRatingRegistry' ),
			$services->getService( 'BSRatingConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];
