/*!
 * JavaScript functions for embedding jQuery controls
 * into translation notification form.
 *
 * @author Amir E. Aharoni
 * @copyright Copyright © 2012 Amir E. Aharoni
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
( function ( $, mw ) {
	'use strict';

	var previewId = 'translation-notification-preview',
		sendId = 'translationnotifications-send-notification-button';

	$( document ).ready( function () {
		// Show a datepicker.
		// Based on UploadWizard.
		$( '#mw-input-wpDeadlineDate' ).datepicker( {
			dateFormat: 'yy-mm-dd',
			constrainInput: false,
			showOn: 'focus',
			changeMonth: true,
			changeYear: true,
			showAnim: false,
			showButtonPanel: true,
			minDate: new Date()
		} )
			.data( 'open', 0 )
			.click( function () {
				var $this = $( this );
				if ( $this.data( 'open' ) === 0 ) {
					$this.data( 'open', 1 ).datepicker( 'show' );
				} else {
					$this.data( 'open', 0 ).datepicker( 'hide' );
				}
			} );

		// Attach the language autocomplete widget.
		$( '#wpUserLanguage' ).multiselectautocomplete( { inputbox: '#mw-input-wpLanguagesToNotify' } );

		/**
		 * Notification preview
		 */
		// Initially disable the send button to force at least one preview
		$( '#' + sendId )
			.prop( 'disabled', true );

		// Add the preview button
		$( '#notifytranslators-form' ).after(
			$( '<button>' )
				.text( mw.msg( 'translationnotifications-preview-notification-button' ) )
				.click( function () {
					var fullText,
						uri = new mw.Uri(),
						priority = '',
						deadline = '',
						translatablePage = $( '#mw-input-tpage :selected' ).text(),
						userName = mw.user.getName(),
						$priority = $( '#mw-input-wpPriority :selected' ),
						deadlineDate = $( '#mw-input-wpDeadlineDate' ).val();

					// Enable the send button after the first preview
					$( '#' + sendId )
						.prop( 'disabled', false );

					uri.path = mw.config.get( 'wgScript' );
					uri.query = {
						title: 'Special:Translate',
						group: 'page-' + translatablePage
					};

					if ( $priority.val() !== 'unset' ) {
						priority = mw.msg( 'translationnotifications-email-priority', $priority.text() );
					}

					if ( deadlineDate !== '' ) {
						deadline = mw.msg( 'translationnotifications-email-deadline', deadlineDate );
					}

					fullText = mw.message( 'translationnotifications-talkpage-body',
						userName,
						userName,
						mw.msg( 'translationnotifications-generic-languages' ),
						translatablePage,
						'[' + uri.toString() + ' ' + translatablePage + ']',
						priority,
						deadline,
						$( '#mw-input-wpNotificationText' ).val(),
						1 // it's just an example, so provide one language
					);

					new mw.Api().parse( fullText.escaped().replace( /\n{3,}/g, '\n\n' ) )
						.done( function ( parsedNotification ) {
							$( '#' + previewId )
								.html( parsedNotification )
								.show();
						} );
				} ),
			$( '<div>' )
				.hide()
				.attr( { id: previewId } )
		);
	} );
}( jQuery, mediaWiki ) );
