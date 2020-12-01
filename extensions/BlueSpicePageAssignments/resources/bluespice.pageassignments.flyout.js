(function( mw, $, bs, undefined ) {
	bs.util.registerNamespace( 'bs.pageassignments' );

	var userCanAssign = mw.config.get( 'bsgPageAssignmentsCanAssign' );
	bs.pageassignments.flyoutCallback = function( $body ) {
		var dfd = $.Deferred();
		Ext.create( 'BS.PageAssignments.flyout.Base', {
			renderTo: $body[0],
			userCanAssign: !!userCanAssign
		} );
		dfd.resolve();

		return dfd.promise();
	};

})( mediaWiki, jQuery, blueSpice );
