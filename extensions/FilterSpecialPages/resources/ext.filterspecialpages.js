( function ( $, mw ) {
	$.extend( $.expr[':'], {
		'containsi': function ( elem, i, match, array )
		{
			return ( elem.textContent || elem.innerText || '' ).toLowerCase()
					.indexOf( ( match[3] || "" ).toLowerCase() ) >= 0;
		}
	} );
	var filterInput = new OO.ui.TextInputWidget( {
		"placeholder": mw.message( 'filterspecialpages-hint-label' ).text(),
		"id": "filterspecialpages",
		"name": "filterspecialpages"
	} );
	$( '#bodyContent' ).prepend( filterInput.$element );
	filterInput.focus();
	filterInput.on( 'change', function ( value ) {
		if ( value.length === 0 ) {
			$( '#bodyContent li' ).show();
		} else {
			//display hide all li's where text not in
			$( '#bodyContent li:not(:containsi(' + value + '))' ).hide();
			$( '#bodyContent li:containsi(' + value + ')' ).show();
		}
		//hide empty sections, show non empty sections
		$( '.mw-specialpages-list ul, .mw-specialpages-notes ul' ).each( function () {
			if ( $( this ).children( ':visible' ).length === 0 ) {
				$( this ).parent().prev().hide();
			} else {
				$( this ).parent().prev().show();
			}
		} );

	} );

	filterInput.$element.find("input").keyup( function ( e ) {
		if ( e.keyCode == 27 ) { // escape key maps to keycode `27`
			var selection = window.getSelection().toString();
			if ( selection === filterInput.getValue() ) {
				//Cursor to end
				filterInput.focus();
				var tmpStr = filterInput.getValue();
				filterInput.setValue( '' );
				filterInput.setValue( tmpStr );
			} else {
				//select all text
				filterInput.select();
			}
		}
	} );

	filterInput.on( 'enter', function ( e ) {
		var visibleLinks = $( '#bodyContent li:containsi(' + filterInput.getValue() + ')' );
		if ( visibleLinks.size() === 1 ) {
			window.location.href = visibleLinks.find( "a" ).attr( "href" );
		}
	} );

}( jQuery, mediaWiki ) );
