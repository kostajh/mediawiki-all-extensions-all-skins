(function( mw, $, d, undefined ) {
	$( d ).on( 'click', '#ca-watch, #ca-unwatch', function( e ) {
		var $this = $(this);
		var currentPage = mw.config.get( 'wgPageName' );
		mw.loader.using( 'mediawiki.api' ).done( function() {
			var api = new mw.Api();
			if( $this.attr( 'id' ) === 'ca-watch' ) {
				api.watch( currentPage ).done( function() {
					$this.attr( 'id', 'ca-unwatch' );
					$this.attr( 'title', mw.message( 'bs-calumma-page-unwatch-title' ).plain() );

					$this.find( 'span' ).html( mw.message( 'bs-calumma-page-unwatch-text' ).text() );

					$this.find( 'i' ).attr( 'class', 'bs-icon-star-full' );
				} );
			}
			if( $this.attr( 'id' ) === 'ca-unwatch' ) {
				api.unwatch( currentPage ).done( function() {
					$this.attr( 'id', 'ca-watch' );
					$this.attr( 'title', mw.message( 'bs-calumma-page-watch-title' ).plain() );

					$this.find( 'span' ).html( mw.message( 'bs-calumma-page-watch-text' ).text() );

					$this.find( 'i' ).attr( 'class', 'bs-icon-star-empty' );
				} );
			}
		} );
		e.defaultPrevented = true;
		return false;
	} );
} )( mediaWiki, jQuery, document );