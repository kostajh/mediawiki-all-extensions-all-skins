( function( mw, $, bs, d, undefined ) {

	$( d ).on( 'click', '#n-recentchanges', function( e ) {
		var data = $( this ).data();

		$( this ).dynamicGraphicalList({
			title: data.flyoutTitle,
			intro: data.flyoutIntro,
			body: function( $elem, $body ) {
				var dfd = $.Deferred();
				mw.loader.using( data.triggerRlDeps ).done( function() {
					bs.util
						.runCallback( data.triggerCallback, [ $body, data ], $elem )
						.done( function() {
							dfd.resolve();
						});
				});
				return dfd.promise();
			},
			direction: 'east'
		}).toggle();

		e.defaultPrevented = true;
	});

})( mediaWiki, jQuery, blueSpice, document );
