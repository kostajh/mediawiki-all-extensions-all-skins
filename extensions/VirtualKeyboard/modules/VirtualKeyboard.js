( function ( mw, $ ) {
	mw.hook( 've.activationComplete' ).add( function () {
		var vkclass = mw.config.get( 'wgVirtualKeyboardClassName' );
		if ( vkclass ) {
			$(".ve-ce-documentNode").attr('onclick', vkclass.concat( '.attachInput(this)' ) );
		} else {
			/* Easy mode - not working yet */
			$(".ve-ce-branchNode").addClass( 'keyboardInput' );
		}
		if ( vkclass === 'IFrameVirtualKeyboard' ) {
			/* Move the div for the iframe keyboard out of the invisible content area */
			/* TODO - Need to move this back after VE deactivated */
			$( "#virtual-keyboard-iframe" ).appendTo( ".ve-ce-surface" );
		}
	} );
}( mediaWiki, jQuery ) );