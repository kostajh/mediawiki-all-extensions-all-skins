/* make oversize tables scrollable */
$( document ).ready( function() {
	if( $(window).width() > 1201 ) { return; }

	var doctables = document.getElementsByTagName( 'table' );

	for( var i = 0; i < doctables.length; i++ ) {
			var headers = doctables[i].getElementsByTagName( 'th' );
			var cells = doctables[i].getElementsByTagName( 'td' );

			if( headers.length > 0 ){
				doctables[i].className += " hw-responsive-data-table";
			}
			else{
				doctables[i].className += " hw-responsive-gallery-table";
			}
	}
	$('table.hw-responsive-data-table').wrap('<div class="hw-responsive-table-scrollable"></div>');

	var $docimages = $( '#content img' );

	$docimages.each( function( index, element ){
			element.className +=  ' hw-responsive-image-resize ';
	});
});

