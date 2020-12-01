( function ( $ ) {
	var dialog;

	function click_body() {
		if ( !dialog ) { return; }

		// Must destroy the dialog, so that its resizing is not "remembered":
		dialog.dialog( 'destroy' );
		dialog.remove();
		dialog = null;
	}

	function showPopup( html, base ) {
		$( 'body' ).append( '<div id="pagepopup-dialog" style="display:none"/>' );
		dialog = $( '#pagepopup-dialog' );
		dialog.html( html );
		dialog.dialog( { draggable: false,
			position: { my: 'center top', at: 'center bottom', of: base },
			dialogClass: 'pagepopup-dialog'
		} );
		dialog.find( '.mw-pagepopup' ).click( initEvents );
		dialog.click( function ( event ) {
			event.stopPropagation();
		} );
		$( 'body' ).on( 'click', click_body );
	}

	function initEvents( event ) {
		click_body();
		$( 'body' ).off( 'click', click_body );
		var href = $( this ).attr( 'href' ) || $( this ).children( 'a' ).attr( 'href' ),
			self = this;
		$.get( href, { action: 'render' }, function ( data ) {
			showPopup( data, self );
		} );
		event.preventDefault(); // don't follow the link
		event.stopPropagation(); // if the link is inside <span> with class="mw-pagepopup"
	}

	$( function () {
		$( '.mw-pagepopup' ).on( 'click', initEvents );
	} );
}( jQuery ) );
