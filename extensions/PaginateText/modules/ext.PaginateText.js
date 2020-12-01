( document ).ready( function ( $ ) {
	$.ready( '#paginate-text' ).textpager( {
		controlArrows: '.controls',
		controlPages: '.controls .tp-pager',
		controlPagesContent: 'li'
	}
	);
} );
