(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.pagereaders.flyout' );

	bs.pagereaders.flyout.makeItems = function() {
		return {
			centerLeft: [
				Ext.create( 'BS.Readers.grid.PageReaders', {} )
			]
		};
	};

})( mediaWiki, jQuery, blueSpice );
