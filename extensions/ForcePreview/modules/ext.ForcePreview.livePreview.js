$( function () {
	'use strict';

	var preview = OO.ui.infuse( $( '#wpPreviewWidget' ) ),
		save = OO.ui.infuse( $( '#wpSaveWidget' ) ),
		newPage = mw.config.get( 'wgArticleId' ) === 0,
		message;

	// This logic was lifted from EditPage::getSaveButtonLabel,
	// which is sadly a private function
	if ( mw.config.get( 'wgEditSubmitButtonLabelPublish' ) ) {
		message = newPage ? 'publishpage' : 'publishchanges';
	} else {
		message = newPage ? 'savearticle' : 'savechanges';
	}

	preview.on( 'click', function enableSave() {
		save.setFlags( { primary: true, progressive: true } )
			.setDisabled( false )
			// Messages that can be used here (see above):
			// * publishpage
			// * publishchanges
			// * savearticle
			// * savechanges
			.setLabel( OO.ui.msg( message ) );

		preview.setFlags( { primary: false, progressive: false } )
			.off( 'click', enableSave );
	} );
} );
