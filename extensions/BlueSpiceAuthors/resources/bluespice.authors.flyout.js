(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.authors.flyout' );

	bs.authors.flyout.makeItems = function() {
		return {
			centerRight: [
				Ext.create( 'BS.Authors.grid.Authors', {} )
			]
		}
	};

})( mediaWiki, jQuery, blueSpice );
