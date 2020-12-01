$( document ).ready( function (){
	$( '#bs-extendedfilelist-grid' ).on( 'click', '.bs-thumb-link.image', function( e ){
		e.preventDefault();

		efmmv = new mediaWiki.mmv.MultimediaViewerBootstrap();
		efmmv.click(
			this,
			e,
			mw.Title.newFromText( $(this).attr('data-bs-title'), 6 ),
			true
		);

	});
});