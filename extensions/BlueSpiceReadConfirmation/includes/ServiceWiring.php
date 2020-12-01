<?php

use MediaWiki\MediaWikiServices;

return [
	'BSReadConfirmationMechanismFactory' => function ( MediaWikiServices $services ) {
		$mechanismCallback = $services->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ReadConfirmationMechanism' );

		return new \BlueSpice\ReadConfirmation\MechanismFactory( $mechanismCallback );
	},
];
