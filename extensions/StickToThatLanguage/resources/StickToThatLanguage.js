/**
 * JavaScript for the StickToThatLanguage extension
 *
 * @since 0.1
 * @file
 * @ingroup STTLanguage
 *
 * @licence GNU GPL v2+
 * @author H. Snater
 */

( function ( $, mw ) {
	'use strict';

	$( function () {
		// Get user preferred languages in the DOM:
		var $topLanguages = $( '.sttl-toplang' );

		if ( !$topLanguages.length ) {
			// No 'more languages' if there is no languages displayed at all
			return;
		}

		// Place linked separator to have other languages collapse below top 10 languages
		$topLanguages
			// Remove non-JS exclusive class
			.removeClass( 'sttl-lasttoplang' )
			.detach()
			.appendTo(
				$( '<ul>' )
					.prependTo( '#p-lang .body' )
					// Put top languages into its own ul and append link to show more languages
					.after(
						$( '<h6>' )
							.append(
								$( '<span>' )
									.addClass( 'ui-icon ui-icon-triangle-1-e' )
							)
							.append(
								$( '<a>' )
									.addClass( 'sttl-languages-more-link' )
									.text( mw.msg( 'sttl-languages-more-link' ) )
									.attr( 'href', '#' )
									.on( 'click', function ( e ) {
										e.preventDefault();
										$( '#p-lang .body h6 span' )
											.toggleClass( 'ui-icon-triangle-1-e' )
											.toggleClass( 'ui-icon-triangle-1-s' );
										$( '#p-lang .body ul' ).eq( 1 ).slideToggle();
									} )
							)
					)
			);

		// class style initially hides "more" languages
		$( '#p-lang .body ul' ).eq( 1 ).addClass( 'sttl-languages-more' );

	} );

}( jQuery, mediaWiki ) );
