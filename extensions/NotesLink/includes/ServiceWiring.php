<?php

use MediaWiki\MediaWikiServices;

return [

	'NotesLinkNDLFactory' => function ( MediaWikiServices $services ) {
		return new \NotesLink\NDLFactory(
			$services->getConfigFactory()->makeConfig( 'noteslink' )
		);
	},

];
