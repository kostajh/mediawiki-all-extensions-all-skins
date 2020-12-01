/*
 * Javascript for the mediawiki.action.edit.watchlistExpiry module.
 */
( function () {
	'use strict';

	/*
	 * Toggle the watchlist-expiry dropdown's disabled state according to the
	 * selected state of the watchthis checkbox.
	 */
	$( function () {
		var watchThisWidget, watchlistExpiryWidget,
			$watchThis = $( '#wpWatchthisWidget' ),
			$expiry = $( '#wpWatchlistExpiryWidget' );

		if ( $watchThis.length && $expiry.length ) {

			watchThisWidget = OO.ui.infuse( $watchThis );
			watchlistExpiryWidget = OO.ui.infuse( $expiry );
			// Set initial state to match the watchthis checkbox.
			watchlistExpiryWidget.setDisabled( !watchThisWidget.isSelected() );

			// Change state on every change of the watchthis checkbox.
			watchThisWidget.on( 'change', function ( enabled ) {
				watchlistExpiryWidget.setDisabled( !enabled );
			} );
		}
	} );

}() );
