/**
 * Front-end scripting core for the MarkAsHelpful MediaWiki extension
 *
 * @author Rob Moen, 2011
 */

( function () {

	var mah = mw.markAsHelpful = {
		loadedItems: [],
		// Class of element(s) to apply MarkAsHelpful to.
		selector: '[class^="markashelpful"]',

		init: function () {
			var props, thisItem;

			$( mah.selector ).each( function ( i, el ) {
				props = mah.getItemProperties( $( el ) );
				// Create an item reference to place in the loaded items array.
				thisItem = props.type + props.item;

				// Load once per type+id because user can copy / paste element on the talk page
				// and load the same item many times.
				if ( $.inArray( thisItem, mah.loadedItems ) === -1 ) {
					mah.loadedItems.push( thisItem );
					mah.loadItem( $( el ) );
				}
			} );
		},

		/**
		 * Return object of item properties
		 *
		 * @param $item
		 */
		getItemProperties: function ( $item ) {
			var tag, props;

			tag = $item.attr( 'class' );
			// Item properties are stored in classname to prevent parser from stripping
			// out non html 5 objects. (eg. data-markashelpful-item)
			props = {
				// item id
				item: tag.split( '-' )[ 2 ],
				// item type (eg, mbresponse)
				type: tag.split( '-' )[ 1 ]
			};
			return props;
		},

		/**
		 * Load the current state of the MarkAsHelpful item
		 *
		 * @param $item
		 */
		loadItem: function ( $item ) {
			var props, request;

			props = mah.getItemProperties( $item );
			request = {
				format: 'json',
				action: 'getmarkashelpfulitem',
				item: props.item,
				type: props.type,
				page: mw.config.get( 'wgPageName' )
			};

			$.ajax( {
				type: 'POST',
				url: mw.util.wikiScript( 'api' ),
				cache: false,
				data: request,
				success: function ( data ) {
					var $content;

					if ( data.getmarkashelpfulitem &&
						data.getmarkashelpfulitem.result === 'success' &&
						data.getmarkashelpfulitem.formatted
					) {
						$content = $( data.getmarkashelpfulitem.formatted );
						$item.html( $content );
					}
					// else: Failure, do nothing to the item for now else
				},
				error: function () {
					// Failure, do nothing to the item for now
				},
				dataType: 'json'
			} );

		},

		/**
		 * API call to mark or unmark an item as helpful.
		 *
		 * @param $clicked
		 * @param action
		 */
		markItem: function ( $clicked, action ) {
			var $item, props, clientData, request;

			$item = $clicked.parent().parent();
			props = mah.getItemProperties( $item );
			clientData = $.client.profile();
			props.mahaction = action;
			request = $.extend( {
				action: 'markashelpful',
				format: 'json',
				page: mw.config.get( 'wgPageName' ),
				useragent: clientData.name + '/' + clientData.versionNumber,
				system: clientData.platform,
				token: mw.user.tokens.get( 'csrfToken' )
			}, props );

			$.ajax( {
				type: 'post',
				url: mw.util.wikiScript( 'api' ),
				data: request,
				success: function () {
					mah.loadItem( $item );
				},
				dataType: 'json'
			} );
		}
	};

	// Some live events for the different modes

	$( document ).ready( function () {

		/**
		 * Click Event for marking an item as helpful.
		 */
		$( document ).on( 'click', '.markashelpful-mark', function () {
			mah.markItem( $( this ), 'mark' );
		} );

		/**
		 * Click Event for removing helpful status from an item.
		 */
		$( document ).on( 'click', '.markashelpful-undo', function () {
			mah.markItem( $( this ), 'unmark' );
		} );

		// Initialize MarkAsHelpful
		mah.init();
	} );

}() );
