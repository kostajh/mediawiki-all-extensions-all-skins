( function ( mw, $ ) {
	var i, dropdowns = mw.config.get( 'wgShowMeDropdownIDs' );

	function showMe( id ) {
		var dropdown, options, i;
		dropdown = document.getElementById( id );
		if ( dropdown === null ) {
			return;
		} else {
			options = dropdown.options;
		}
		$( '#mw-content-text .' + options[ 0 ].value ).show();
		for ( i = 1; i < options.length; i++ ) {
			$( '#mw-content-text .' + options[ i ].value ).hide();
		}
		$( '#mw-content-text #' + id ).change( function () {
			for ( i = 0; i < options.length; i++ ) {
				if ( options[ i ].value === this.value ) {
					$( '#mw-content-text .' + this.value ).show();
				} else {
					$( '#mw-content-text .' + options[ i ].value ).hide();
				}
			}
		} );
	}

	for ( i = 0; i < dropdowns.length; i++ ) {
		showMe( dropdowns[ i ] );
	}
}( mediaWiki, jQuery ) );
