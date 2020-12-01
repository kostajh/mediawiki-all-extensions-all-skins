/**
 * Basic concept stolen from Special:Block.
 */
( function () {
	$( function () {
		'use strict';
		var $moveAlso = $( '#mw-input-sameformove' ),
			$moveCheck = $( '#mw-input-move' ).closest( 'tr' ),
			$moveExpiry = $( '#mw-input-wpmoveexpiry' ).closest( 'tr' );

		function updateMove( instant ) {
			console.log( 'trigger' );
			if ( $moveAlso[ 0 ].checked ) {
				$moveExpiry.goOut( instant );
				$moveCheck.goOut( instant );
			} else {
				$moveExpiry.goIn( instant );
				$moveCheck.goIn( instant );
			}
		}

		if ( $moveAlso.length ) {
			// Bind function so they're checked whenever stuff changes
			$moveAlso.on( 'click', updateMove );

			// Call them now to set initial state
			updateMove( /* instant= */ true );
		}
	} );
}() );
