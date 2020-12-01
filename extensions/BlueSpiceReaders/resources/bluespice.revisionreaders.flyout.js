(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.revisionreaders.flyout' );

	bs.revisionreaders.flyout.makeItems = function() {
		return {
			centerRight: [
				Ext.create( 'BS.Readers.grid.RevisionReaders', {} )
			]
		};
	};

})( mediaWiki, jQuery, blueSpice );
