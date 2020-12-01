( function () {
	function addpx( p1, p2 ) {
		return ( Number( p1.replace( 'px', '' ) ) + Number( p2.replace( 'px', '' ) ) ) + 'px';
	}
	function subpx( p1, p2 ) {
		return ( Number( p1.replace( 'px', '' ) ) - Number( p2.replace( 'px', '' ) ) ) + 'px';
	}
	function headerListener() {
		// this function is to adjust the header widths to match those of the first row of the table
		var d = document.querySelectorAll( 'tr.scrollabletable' );
		// for all such tables
		for ( var i = d.length - 1; i >= 0; i-- ) {
			var row = d.item( i ),
				// we searched for the header row, so go upward to find the actual table
				table = row.parentNode;
			while ( table && table.tagName.toLowerCase() !== 'table' ) {
				table = table.parentNode;
			}
			if ( !table || table.tBodies.length <= 0 || table.tBodies[ 0 ].rows.length <= 0 ) {
				continue;
			}
			// find first row in the body to use as a template for header widths
			if ( table.tBodies.length === 0 || table.tBodies[ 0 ].rows.length === 0 ) {
				continue;
			}
			var cells = table.tBodies[ 0 ].rows[ 0 ].cells;
			// look at each cell in the body first row,
			// and make the corresponding header cell have the same width
			for ( var j = 0; j < cells.length; j++ ) {
				var c = cells[ j ],
					// first we get the computed width of the cell
					s = window.getComputedStyle( c ).width,
					// the actual width includes padding, so first we remove that
					p = addpx( window.getComputedStyle( c ).paddingLeft, window.getComputedStyle( c ).paddingRight ),
					w = subpx( s, p ),
					// sortable tables have extra padding on the right for the sort symbol in the header.  So we
					// leave room for that too.
					xp = subpx( window.getComputedStyle( row.cells[ j ] ).paddingRight, window.getComputedStyle( row.cells[ j ] ).paddingLeft );
				w = subpx( w, xp );
				row.cells[ j ].style.width = w;
			}
		}
	}
	// aggressively set the widths
	headerListener();
	// check for resize events and re-set the widths
	window.addEventListener( 'resize', headerListener, false );
	// and finally re-set the widths in 100ms in order to fix it after sortable code is run too.
	setTimeout( headerListener, 100 );
}() );
