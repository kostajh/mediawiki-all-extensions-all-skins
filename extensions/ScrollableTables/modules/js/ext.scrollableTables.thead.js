( function () {
	var d = document.querySelectorAll( 'tr.scrollabletable' );
	for ( var i = d.length - 1; i >= 0; i-- ) {
		var row = d.item( i ),
			// first find the table tag
			table = row.parentNode;
		// skip over a body
		if ( table.tagName.toLowerCase() !== 'table' ) {
			table = table.parentNode;
		}
		// if we do not already have a thead, move the header row to the thead
		if ( !table.tHead ) {
			// remove first row and add it to a newly created thead
			row.parentNode.removeChild( row );
			var newHead = document.createElement( 'thead' );
			newHead.classList.add( 'scrollableTable' );
			newHead.appendChild( row );
			row.style.display = 'table-header-group';
			table.tHead = newHead;
			for ( j = 0; j < table.tBodies.length; j++ ) {
				if ( table.style.height ) {
					table.tBodies[ j ].style.height = table.style.height;
				}
				table.tBodies[ j ].classList.add( 'scrollableTable' );
			}
		}
	}
}() );
