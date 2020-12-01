jQuery( document ).ready( function( $ ) {

	/* Lazy load ext.jquery.fixedheadertable */
	if ( $( 'table.fixedheadertable' ).length ) {
		mw.loader.using( 'ext.jquery.fixedheadertable', function() {
			$( 'table.fixedheadertable' ).fixedHeaderTable();
		});
	}
	
} );