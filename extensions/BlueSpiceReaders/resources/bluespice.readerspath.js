( function( mw, $, bs, undefined ) {
	Ext.onReady( function() {
		Ext.create( 'BS.Readers.panel.Path', {
			renderTo: 'bs-readerspath-grid'
		} );
	} );
} )( mediaWiki, jQuery, blueSpice );
