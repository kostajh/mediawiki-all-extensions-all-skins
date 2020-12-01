( function( d, $, mw ) {
	$( d ).ready( function(){
		/* scroll to jumpmark on load */
		var hash = window.location.hash;

		mw.hook( 'bs.calumma.scroll.to.jumpmark.before' ).fire( hash, null );

		var clearance = $( '#content' ).position().top;

		if ( hash !== '' ) {
			var jumpmarkId = hash.replace( '#', '' );

			var jumpmark = d.getElementById( jumpmarkId );
			if ( !jumpmark ) {
				return;
			}

			var position = $( jumpmark ).position().top;

			$( 'body, html').animate(
				{
					scrollTop: position - clearance
				},
				100
			);
		};

		/* scroll to jumpmark on click (TOC, imagemap, ...).
		 * accroding to syntax for jumpmarks at
		 * https://www.mediawiki.org/wiki/Help:Links/en#Internal_links
		 * there is no id and class used for jumpmarks
		 * (see: "Link to an anchor on the same page")
		 */
		$( '#bodyContent' ).on( 'click',
			'#mw-content-text a:not( [id] ):not( [class] ),	#mw-content-text map area',
			function( e ) {

			var hash = this.hash;

			mw.hook( 'bs.calumma.scroll.to.jumpmark.before' ).fire( hash, e );

			if( this.pathname + this.search !== mw.util.getUrl() ) {
				return;
			}

			if ( hash !== '' ) {
				var jumpmarkId = hash.replace( '#', '' );

				var jumpmark = d.getElementById( jumpmarkId );
				if ( !jumpmark ) {
					return;
				}

				var position = $( jumpmark ).position().top;
				if ( position === 0 ) { //hidden element (e.g. bootstrap nav-tabs)
					return;
				}

				$( 'body, html').animate(
					{
						scrollTop: position - clearance
					},
					400
				);
			}
		});
	});
}) ( document, jQuery, mediaWiki);
